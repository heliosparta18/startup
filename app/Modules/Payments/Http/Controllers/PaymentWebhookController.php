<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Services\FakeQrisGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, PaymentGatewayInterface $gateway): JsonResponse
    {
        $signature = (string) $request->header('X-Signature', '');
        $payload = (string) $request->getContent();
        /** @var array<string, mixed> $data */
        $data = (array) $request->json()->all();

        $isMidtransWebhook = isset($data['signature_key']) && isset($data['order_id']);
        $isAuthorized = false;

        if ($isMidtransWebhook && $gateway instanceof FakeQrisGateway) {
            $isAuthorized = $gateway->verifyMidtransSignature($data);
        } elseif ($signature !== '') {
            $isAuthorized = $gateway->verifyWebhookSignature($payload, $signature);
        }

        if (! $isAuthorized) {
            Log::warning('Webhook QRIS ditolak: Signature HMAC/Midtrans tidak cocok.');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if ($isMidtransWebhook) {
            $orderNumber = (string) ($data['order_id'] ?? '');
            $txStatus = strtolower((string) ($data['transaction_status'] ?? ''));
            $isPaid = in_array($txStatus, ['settlement', 'capture'], true);
        } else {
            $orderNumber = (string) ($data['order_number'] ?? '');
            $isPaid = ($data['status'] ?? null) === 'PAID';
        }

        if (! $isPaid || $orderNumber === '') {
            return response()->json(['message' => 'Ignored'], 200);
        }

        $cleanOrderNumber = ltrim($orderNumber, '#');

        return DB::transaction(function () use ($orderNumber, $cleanOrderNumber, $data) {
            $pesanan = Pesanan::withoutTenancy()
                ->whereIn('nomor_pesanan', [$orderNumber, '#'.$cleanOrderNumber, $cleanOrderNumber])
                ->lockForUpdate()
                ->first();

            if (! $pesanan) {
                return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
            }

            // Idempotency: Jika sudah diproses atau lunas, return 200 tanpa mutasi ulang
            if ($pesanan->status !== 'menunggu_pembayaran') {
                return response()->json(['message' => 'Pesanan sudah diproses sebelumnya (Idempotent)'], 200);
            }

            $pesanan->update([
                'status' => 'diproses',
            ]);

            Pembayaran::create([
                'id_pembayaran' => (string) Str::uuid(),
                'id_pesanan' => $pesanan->id_pesanan,
                'metode_pembayaran' => 'QRIS',
                'jumlah' => $pesanan->total_harga,
                'status' => 'lunas',
                'url_lampiran' => json_encode($data) ?: null,
                'dibuat_pada' => now(),
            ]);

            OrderStatusUpdated::dispatch($pesanan);

            Log::info("Pesanan {$pesanan->nomor_pesanan} berhasil dilunasi via Webhook QRIS.");

            return response()->json([
                'success' => true,
                'message' => 'Settlement processed successfully',
                'order_number' => $pesanan->nomor_pesanan,
                'status' => 'diproses',
            ], 200);
        });
    }
}
