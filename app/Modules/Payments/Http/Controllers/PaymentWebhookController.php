<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
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

        if (! $gateway->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Webhook QRIS ditolak: Signature HMAC tidak cocok.');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        /** @var array{order_number?: string, status?: string} $data */
        $data = (array) $request->json()->all();
        $orderNumber = $data['order_number'] ?? null;
        $status = $data['status'] ?? null;

        if ($status !== 'PAID' || ! $orderNumber) {
            return response()->json(['message' => 'Ignored'], 200);
        }

        return DB::transaction(function () use ($orderNumber, $data) {
            $pesanan = Pesanan::withoutTenancy()
                ->where('nomor_pesanan', $orderNumber)
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

            Log::info("Pesanan {$orderNumber} berhasil dilunasi via Webhook QRIS.");

            return response()->json([
                'success' => true,
                'message' => 'Settlement processed successfully',
                'order_number' => $orderNumber,
                'status' => 'diproses',
            ], 200);
        });
    }
}
