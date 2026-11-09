<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Modules\Ordering\Actions\CancelOrderAction;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Tampilkan halaman pembayaran QRIS dinamis sandbox (EMVCo).
     */
    public function payment(string $client, string $id, PaymentGatewayInterface $gateway, TenantContext $tenantContext): View
    {
        $pesanan = Pesanan::withoutTenancy()->with(['items', 'pembayaran'])->find($id);

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
    public function cancel(string $client, string $id, CancelOrderAction $cancelOrderAction, TenantContext $tenantContext): RedirectResponse
    {
        $pesanan = Pesanan::withoutTenancy()->with('items')->find($id);

        if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
            abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
        }

        $cancelOrderAction->execute($pesanan, 'Dibatalkan oleh pelanggan');

        return redirect()->route('customer.order.payment', ['client' => $client, 'id' => $pesanan->id_pesanan])
            ->with('status', 'Pesanan berhasil dibatalkan dan stok telah dikembalikan ke cabang.');
    }

    /**
     * Pelacakan stepper status pesanan secara real-time via WebSocket Reverb.
     */
    public function track(string $client, string $id, TenantContext $tenantContext): View
    {
        $pesanan = Pesanan::withoutTenancy()->with(['items', 'pembayaran'])->find($id);

        if (! $pesanan || $pesanan->id_client !== $tenantContext->id()) {
            abort(404, 'Pesanan tidak ditemukan pada cabang apotek ini.');
        }

        return view('customer.order-track', [
            'client' => $client,
            'pesanan' => $pesanan,
        ]);
    }
}
