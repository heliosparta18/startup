<?php

namespace App\Modules\Payments\Services;

use App\Models\Pesanan;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;

class FakeQrisGateway implements PaymentGatewayInterface
{
    /**
     * @return array{gateway: string, qris_string: string, qr_image_url: string, expired_at: string, amount: float}
     */
    public function generateQris(Pesanan $pesanan): array
    {
        $nmid = (string) config('services.qris.nmid', 'ID1020000012345');
        $amount = $pesanan->total_harga;
        $qrisString = QrisEmvcoBuilder::build($nmid, $pesanan->nomor_pesanan, (float) $amount);

        return [
            'gateway' => 'fake_qris_sandbox',
            'qris_string' => $qrisString,
            'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='.urlencode($qrisString),
            'expired_at' => now()->addMinutes(15)->toIso8601String(),
            'amount' => (float) $amount,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) config('services.qris.webhook_secret', 'omnipharma_secret_key');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
