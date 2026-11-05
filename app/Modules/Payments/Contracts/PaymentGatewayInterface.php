<?php

namespace App\Modules\Payments\Contracts;

use App\Models\Pesanan;

interface PaymentGatewayInterface
{
    /**
     * @return array{gateway: string, qris_string: string, qr_image_url: string, expired_at: string, amount: float}
     */
    public function generateQris(Pesanan $pesanan): array;

    public function verifyWebhookSignature(string $payload, string $signature): bool;
}
