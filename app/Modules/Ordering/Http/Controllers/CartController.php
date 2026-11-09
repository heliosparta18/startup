<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ordering\Services\CartService;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Tampilkan keranjang belanja Redis pasien.
     */
    public function index(string $client, Request $request, CartService $cartService, TenantContext $tenantContext): View
    {
        $clientId = $tenantContext->id();
        $sessionId = $this->resolveCustomerSession($request);
        $items = $cartService->getItems($sessionId, $clientId);
        $total = $cartService->getTotal($sessionId, $clientId);

        return view('customer.cart', [
            'client' => $client,
            'items' => $items,
            'total' => $total,
        ]);
    }

    /**
     * Tambah item obat ke dalam keranjang Redis.
     */
    public function add(string $client, Request $request, CartService $cartService, TenantContext $tenantContext): JsonResponse|RedirectResponse
    {
        $clientId = $tenantContext->id();
        $sessionId = $this->resolveCustomerSession($request);
        $obatId = (string) $request->input('id_obat');
        $qty = (int) $request->input('jumlah', 1);

        $cartService->addItem($sessionId, $clientId, $obatId, $qty);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Obat berhasil ditambahkan ke keranjang.',
            ]);
        }

        return redirect()->route('customer.cart', ['client' => $client]);
    }

    /**
     * Ubah kuantitas obat di dalam keranjang.
     */
    public function update(string $client, Request $request, CartService $cartService, TenantContext $tenantContext): JsonResponse
    {
        $clientId = $tenantContext->id();
        $sessionId = $this->resolveCustomerSession($request);
        $obatId = (string) $request->input('id_obat');
        $qty = (int) $request->input('jumlah', 1);

        $cartService->updateItem($sessionId, $clientId, $obatId, $qty);

        return response()->json(['success' => true]);
    }

    /**
     * Hapus item obat dari keranjang Redis.
     */
    public function remove(string $client, Request $request, CartService $cartService, TenantContext $tenantContext): JsonResponse
    {
        $clientId = $tenantContext->id();
        $sessionId = $this->resolveCustomerSession($request);
        $obatId = (string) $request->input('id_obat');

        $cartService->removeItem($sessionId, $clientId, $obatId);

        return response()->json(['success' => true]);
    }

    /**
     * Dapatkan token sesi pelanggan unik.
     */
    protected function resolveCustomerSession(Request $request): string
    {
        $cookie = $request->cookie('omnipharma_customer_session') ?? $request->cookie('omni_session');
        if (is_string($cookie) && $cookie !== '') {
            return $cookie;
        }

        $sessionId = $request->session()->getId();

        return $sessionId !== '' ? $sessionId : 'guest-'.md5((string) $request->ip());
    }
}
