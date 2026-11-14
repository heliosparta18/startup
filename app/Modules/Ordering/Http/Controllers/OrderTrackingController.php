<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Modules\Ordering\Actions\CancelOrderAction;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\Services\FakeQrisGateway;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Tampilkan halaman pembayaran QRIS dinamis (Midtrans Sandbox / EMVCo fallback).
     */
    public function payment(string $client, string $id, PaymentGatewayInterface $gateway, TenantContext $tenantContext): View
    {
        $pesanan = Pesanan::withoutTenancy()->with(['items.obat.kategori', 'pembayaran', 'client'])->find($id);

        if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
            abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
        }

        $qris = $gateway->generateQris($pesanan);

        return view('customer.payment', [
            'client' => $client,
            'pesanan' => $pesanan,
            'qris' => $qris,
        ]);
    }

    /**
     * Cek status pembayaran QRIS (Midtrans Sandbox Status API dengan fallback EMVCo lokal).
     */
    public function checkStatus(
        string $client,
        string $id,
        Request $request,
        PaymentGatewayInterface $gateway,
        TenantContext $tenantContext
    ): JsonResponse {
        $pesanan = Pesanan::withoutTenancy()->find($id);

        if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $trackUrl = $request->is('klien/*') || ! Route::has('pesanan.track')
            ? route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan])
            : route('pesanan.track', ['id' => $pesanan->id_pesanan]);

        if (in_array($pesanan->status, ['diproses', 'siap_diambil', 'dikirim', 'selesai'], true)) {
            return response()->json([
                'paid' => true,
                'status' => $pesanan->status,
                'message' => 'Pembayaran sudah terverifikasi lunas.',
                'track_url' => $trackUrl,
            ]);
        }

        if ($pesanan->status === 'dibatalkan') {
            return response()->json([
                'paid' => false,
                'cancelled' => true,
                'status' => 'dibatalkan',
                'message' => 'Pesanan ini telah dibatalkan.',
            ]);
        }

        $serverKey = trim((string) config('services.midtrans.server_key', ''));
        $midtransOrderId = (string) (Cache::get("midtrans_order_id:{$pesanan->id_pesanan}") ?? ltrim((string) $pesanan->nomor_pesanan, '#'));

        if ($serverKey !== '' && $gateway instanceof FakeQrisGateway) {
            $midtransStatus = $gateway->checkTransactionStatus($midtransOrderId);
            $txStatus = strtolower((string) ($midtransStatus['transaction_status'] ?? 'pending'));

            if (in_array($txStatus, ['settlement', 'capture'], true)) {
                $this->settlePesananAtomically($pesanan->id_pesanan, $midtransStatus);

                return response()->json([
                    'paid' => true,
                    'source' => 'midtrans_sandbox',
                    'midtrans_status' => $txStatus,
                    'status' => 'diproses',
                    'message' => 'Pembayaran berhasil.',
                    'track_url' => $trackUrl,
                ]);
            }

            if (in_array($txStatus, ['expire', 'cancel', 'deny'], true)) {
                return response()->json([
                    'paid' => false,
                    'expired' => true,
                    'midtrans_status' => $txStatus,
                    'status' => $pesanan->status,
                    'message' => 'Waktu pembayaran habis. Silakan batalkan dan pesan ulang.',
                ]);
            }

            return response()->json([
                'paid' => false,
                'midtrans_status' => $midtransStatus !== null ? $txStatus : 'pending',
                'status' => $pesanan->status,
                'message' => 'Belum ada pembayaran masuk.',
            ]);
        }

        if ($request->boolean('auto_timer') && ! $request->boolean('timer_expired')) {
            return response()->json([
                'paid' => false,
                'status' => $pesanan->status,
                'message' => 'Belum ada pembayaran masuk.',
            ]);
        }

        // Fallback mode lokal / tanpa server key Midtrans
        $this->settlePesananAtomically($pesanan->id_pesanan, [
            'order_number' => $pesanan->nomor_pesanan,
            'status' => 'PAID',
            'source' => 'local_qris_check',
        ]);

        return response()->json([
            'paid' => true,
            'source' => 'local_qris_check',
            'status' => 'diproses',
            'message' => 'Status pembayaran terkonfirmasi lunas.',
            'track_url' => $trackUrl,
        ]);
    }

    /**
     * Tampilkan riwayat pesanan pelanggan saat ini (termasuk akses ke invoice dan pembayaran).
     */
    public function history(string $client, Request $request): View
    {
        $sessionToken = $request->cookie('omnipharma_customer_session')
            ?? $request->cookie('omni_session')
            ?? $request->session()->getId();

        $user = $request->user();
        $penggunaId = null;

        if ($user) {
            $penggunaId = Pengguna::where('telepon_email', $user->email)->value('id_pengguna');
        }

        $orders = collect();

        if ($penggunaId || (is_string($sessionToken) && $sessionToken !== '')) {
            $orders = Pesanan::withoutTenancy()
                ->with(['items.obat', 'pembayaran', 'client'])
                ->where(function ($query) use ($penggunaId, $sessionToken) {
                    if ($penggunaId) {
                        $query->where('id_pengguna', $penggunaId);
                    }
                    if (is_string($sessionToken) && $sessionToken !== '') {
                        if ($penggunaId) {
                            $query->orWhere('token_sesi', $sessionToken);
                        } else {
                            $query->where('token_sesi', $sessionToken);
                        }
                    }
                })
                ->orderByDesc('dibuat_pada')
                ->limit(30)
                ->get();
        }

        return view('customer.order-history', [
            'client' => $client,
            'orders' => $orders,
        ]);
    }

    /**
     * Batalkan pesanan yang belum lunas dan kembalikan stok fisik ke cabang.
     */
    public function cancel(string $client, string $id, Request $request, CancelOrderAction $cancelOrderAction, TenantContext $tenantContext): RedirectResponse
    {
        $pesanan = Pesanan::withoutTenancy()->with('items')->find($id);

        if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
            abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
        }

        $cancelOrderAction->execute($pesanan, 'Dibatalkan oleh pelanggan');

        $redirect = $request->is('klien/*')
            ? redirect()->route('customer.order.payment', ['client' => $client, 'id' => $pesanan->id_pesanan])
            : redirect()->route('pembayaran.show', ['id' => $pesanan->id_pesanan]);

        return $redirect->with('status', 'Pesanan berhasil dibatalkan dan stok telah dikembalikan ke cabang.');
    }

    /**
     * Pelacakan stepper status pesanan dan invoice resmi secara real-time via WebSocket Reverb.
     */
    public function track(string $client, string $id, TenantContext $tenantContext): View
    {
        $pesanan = Pesanan::withoutTenancy()->with(['items.obat.kategori', 'pembayaran', 'client', 'pengguna'])->find($id);

        if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
            abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
        }

        $antreanBerjalan = Cache::get("antrean_berjalan:{$pesanan->id_client}", 'A-001');

        $nomorSaya = (int) preg_replace('/\D/', '', (string) ($pesanan->nomor_antrean ?? '1'));
        $nomorAktif = (int) preg_replace('/\D/', '', (string) $antreanBerjalan);
        $sisaAntrean = max(0, $nomorSaya - $nomorAktif);

        return view('customer.order-track', [
            'client' => $client,
            'pesanan' => $pesanan,
            'antreanBerjalan' => $antreanBerjalan,
            'sisaAntrean' => $sisaAntrean,
        ]);
    }

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    private function settlePesananAtomically(string $pesananId, array $rawPayload): void
    {
        DB::transaction(function () use ($pesananId, $rawPayload) {
            $locked = Pesanan::withoutTenancy()->where('id_pesanan', $pesananId)->lockForUpdate()->first();
            if (! $locked || $locked->status !== 'menunggu_pembayaran') {
                return;
            }

            $locked->update(['status' => 'diproses']);

            Pembayaran::create([
                'id_pembayaran' => (string) Str::uuid(),
                'id_pesanan' => $locked->id_pesanan,
                'metode_pembayaran' => 'QRIS',
                'jumlah' => $locked->total_harga,
                'status' => 'lunas',
                'url_lampiran' => json_encode($rawPayload) ?: null,
                'dibuat_pada' => now(),
            ]);

            OrderStatusUpdated::dispatch($locked);
        });
    }
}
