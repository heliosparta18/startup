<?php

namespace App\Modules\Payments\Services;

use App\Models\Pesanan;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FakeQrisGateway implements PaymentGatewayInterface
{
    /**
     * Hasilkan transaksi QRIS dinamis melalui Midtrans Sandbox Core API (fallback EMVCo lokal jika offline/testing).
     *
     * @return array{
     *     gateway: string,
     *     qris_string: string,
     *     qr_image_url: string,
     *     expired_at: string,
     *     expires_at_ts: int,
     *     remaining_seconds: int,
     *     amount: float,
     *     merchant_id?: string,
     *     transaction_id?: string,
     *     midtrans_order_id?: string,
     *     acquirer?: string,
     *     simulator_url?: string
     * }
     */
    public function generateQris(Pesanan $pesanan): array
    {
        $amount = (float) $pesanan->total_harga;
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        $expiry = $this->resolveOrderExpiry($pesanan);
        $remainingSeconds = max(0, (int) now()->diffInSeconds($expiry, false));

        if ($serverKey !== '' && $pesanan->status === 'menunggu_pembayaran') {
            $cacheKey = "midtrans_qris:{$pesanan->id_pesanan}";
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && ! empty($cached['qr_image_url'])) {
                $cached['expired_at'] = $expiry->toIso8601String();
                $cached['expires_at_ts'] = $expiry->timestamp;
                $cached['remaining_seconds'] = $remainingSeconds;

                return $cached;
            }

            $midtransData = $this->chargeMidtransQris($pesanan, $serverKey);
            if ($midtransData !== null) {
                $midtransData['expired_at'] = $expiry->toIso8601String();
                $midtransData['expires_at_ts'] = $expiry->timestamp;
                $midtransData['remaining_seconds'] = $remainingSeconds;

                Cache::put($cacheKey, $midtransData, now()->addMinutes(30));
                Cache::put("midtrans_order_id:{$pesanan->id_pesanan}", $midtransData['midtrans_order_id'] ?? ltrim((string) $pesanan->nomor_pesanan, '#'), now()->addHours(24));

                return $midtransData;
            }
        }

        return $this->buildLocalFallbackQris($pesanan, $amount, $expiry);
    }

    /**
     * Panggil Midtrans Core API v2/charge dengan metode QRIS.
     *
     * @return array<string, mixed>|null
     */
    private function chargeMidtransQris(Pesanan $pesanan, string $serverKey): ?array
    {
        $baseUrl = $this->midtransBaseUrl();
        $midtransOrderId = ltrim((string) $pesanan->nomor_pesanan, '#');
        $grossAmount = max(1, (int) round((float) $pesanan->total_harga));

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(12)
                ->post("{$baseUrl}/v2/charge", [
                    'payment_type' => 'qris',
                    'transaction_details' => [
                        'order_id' => $midtransOrderId,
                        'gross_amount' => $grossAmount,
                    ],
                    'qris' => [
                        'acquirer' => 'gopay',
                    ],
                    'custom_expiry' => [
                        'expiry_duration' => 30,
                        'unit' => 'minute',
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Midtrans QRIS Sandbox charge gagal', [
                    'order_id' => $midtransOrderId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            /** @var array<string, mixed> $data */
            $data = (array) $response->json();
            $qrString = (string) ($data['qr_string'] ?? '');

            $qrImageUrl = null;
            foreach ((array) ($data['actions'] ?? []) as $action) {
                if (is_array($action) && ($action['name'] ?? '') === 'generate-qr-code' && ! empty($action['url'])) {
                    $qrImageUrl = (string) $action['url'];
                    break;
                }
            }

            if (! $qrImageUrl) {
                foreach ((array) ($data['actions'] ?? []) as $action) {
                    if (is_array($action) && ! empty($action['url'])) {
                        $qrImageUrl = (string) $action['url'];
                        break;
                    }
                }
            }

            if ($qrString === '' && ! $qrImageUrl) {
                return null;
            }

            if (! $qrImageUrl) {
                $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='.urlencode($qrString);
            }

            return [
                'gateway' => 'midtrans_qris_sandbox',
                'merchant_id' => (string) ($data['merchant_id'] ?? config('services.midtrans.merchant_id', 'G484050115')),
                'transaction_id' => (string) ($data['transaction_id'] ?? ''),
                'midtrans_order_id' => (string) ($data['order_id'] ?? $midtransOrderId),
                'acquirer' => (string) ($data['acquirer'] ?? 'gopay'),
                'simulator_url' => 'https://simulator.sandbox.midtrans.com/v2/qris/index',
                'qris_string' => $qrString,
                'qr_image_url' => $qrImageUrl,
                'expired_at' => (string) ($data['expiry_time'] ?? now()->addMinutes(30)->toIso8601String()),
                'amount' => (float) $grossAmount,
            ];
        } catch (\Throwable $e) {
            Log::warning('Koneksi Midtrans QRIS Sandbox bermasalah, beralih ke fallback EMVCo lokal: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Cek status transaksi langsung ke Midtrans Status API.
     *
     * @return array<string, mixed>|null
     */
    public function checkTransactionStatus(string $midtransOrderId): ?array
    {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        if ($serverKey === '' || $midtransOrderId === '') {
            return null;
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->timeout(10)
                ->get($this->midtransBaseUrl().'/v2/'.urlencode($midtransOrderId).'/status');

            if (! $response->successful()) {
                return null;
            }

            /** @var array<string, mixed> $json */
            $json = (array) $response->json();

            return $json;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengecek status transaksi Midtrans: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Verifikasi signature_key pada HTTP Notification dari Midtrans.
     *
     * @param  array<string, mixed>  $data
     */
    public function verifyMidtransSignature(array $data): bool
    {
        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        $orderId = (string) ($data['order_id'] ?? '');
        $statusCode = (string) ($data['status_code'] ?? '');
        $grossAmount = (string) ($data['gross_amount'] ?? '');
        $signatureKey = (string) ($data['signature_key'] ?? '');

        if ($serverKey === '' || $orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signatureKey);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) config('services.qris.webhook_secret', 'omnipharma_secret_key');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    private function midtransBaseUrl(): string
    {
        return config('services.midtrans.is_production', false)
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    private function resolveOrderExpiry(Pesanan $pesanan): CarbonInterface
    {
        $cacheKey = "qris_expiry_ts:{$pesanan->id_pesanan}";
        $orderDeadline = $pesanan->dibuat_pada ? $pesanan->dibuat_pada->copy()->addMinutes(30) : null;
        $cachedTs = Cache::get($cacheKey);

        if ($cachedTs !== null && is_numeric($cachedTs)) {
            $cachedExpiry = Carbon::createFromTimestamp((int) $cachedTs);
            if ($orderDeadline && $orderDeadline->isFuture() && $orderDeadline->lt($cachedExpiry)) {
                Cache::put($cacheKey, $orderDeadline->timestamp, now()->addHours(24));

                return $orderDeadline;
            }

            return $cachedExpiry;
        }

        $expiry = ($orderDeadline && $orderDeadline->isFuture())
            ? $orderDeadline
            : now()->addMinutes(30);

        Cache::put($cacheKey, $expiry->timestamp, now()->addHours(24));

        return $expiry;
    }

    /**
     * @return array{gateway: string, qris_string: string, qr_image_url: string, expired_at: string, expires_at_ts: int, remaining_seconds: int, amount: float}
     */
    private function buildLocalFallbackQris(Pesanan $pesanan, float $amount, ?CarbonInterface $expiry = null): array
    {
        $expiry ??= $this->resolveOrderExpiry($pesanan);
        $remainingSeconds = max(0, (int) now()->diffInSeconds($expiry, false));
        $nmid = (string) config('services.qris.nmid', 'ID1020000012345');
        $qrisString = QrisEmvcoBuilder::build($nmid, $pesanan->nomor_pesanan, $amount);

        return [
            'gateway' => 'fake_qris_sandbox',
            'qris_string' => $qrisString,
            'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='.urlencode($qrisString),
            'expired_at' => $expiry->toIso8601String(),
            'expires_at_ts' => $expiry->timestamp,
            'remaining_seconds' => $remainingSeconds,
            'amount' => $amount,
        ];
    }
}
