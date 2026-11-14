<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Modules\Ordering\Actions\CancelOrderAction;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Tampilkan halaman pembayaran QRIS dinamis sandbox (EMVCo).
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
     * Pelacakan stepper status pesanan secara real-time via WebSocket Reverb.
     */
    public function track(string $client, string $id, TenantContext $tenantContext): View
    {
        $pesanan = Pesanan::withoutTenancy()->with(['items.obat.kategori', 'pembayaran', 'client'])->find($id);

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
}
