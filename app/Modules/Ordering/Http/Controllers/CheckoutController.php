<?php

namespace App\Modules\Ordering\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Services\CartService;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Tampilkan formulir konfirmasi checkout transaksi atomik.
     */
    public function index(string $client, Request $request, CartService $cartService, TenantContext $tenantContext): View|RedirectResponse
    {
        $clientId = $tenantContext->id();
        $sessionId = $this->resolveCustomerSession($request);
        $items = $cartService->getItems($sessionId, $clientId);

        if (empty($items)) {
            return redirect()->route('customer.cart', ['client' => $client])
                ->with('error', 'Keranjang belanja Anda masih kosong.');
        }

        $total = $cartService->getTotal($sessionId, $clientId);
        $clientModel = $tenantContext->get();

        return view('customer.checkout', [
            'client' => $client,
            'clientModel' => $clientModel,
            'items' => $items,
            'total' => $total,
        ]);
    }

    /**
     * Eksekusi checkout atomik dengan pessimistic locking dan proteksi idempotensi.
     */
    public function process(
        string $client,
        Request $request,
        CartService $cartService,
        CreateOrderAction $createOrderAction,
        TenantContext $tenantContext
    ): RedirectResponse {
        $clientId = $tenantContext->id();
        $sessionId = $this->resolveCustomerSession($request);

        // Proteksi Idempotensi Double-Submit Checkout: 30 detik lock
        $lockKey = "checkout_lock:{$sessionId}:{$clientId}";
        if (! Cache::add($lockKey, true, 30)) {
            return redirect()->route('customer.cart', ['client' => $client])
                ->with('error', 'Transaksi checkout Anda sedang diproses. Mohon tunggu beberapa detik.');
        }

        try {
            $items = $cartService->getItems($sessionId, $clientId);

            if (empty($items)) {
                Cache::forget($lockKey);

                return redirect()->route('customer.cart', ['client' => $client])
                    ->with('error', 'Keranjang belanja Anda kosong.');
            }

            $user = $request->user();
            $userId = $user ? Pengguna::where('telepon_email', $user->email)->value('id_pengguna') : null;
            $metodeLayanan = (string) $request->input('metode_layanan', 'click_collect');

            $pesanan = $createOrderAction->execute($clientId, $userId, $items, $metodeLayanan, $sessionId);
            $cartService->clear($sessionId, $clientId);

            Cache::forget($lockKey);

            return redirect()->route('customer.order.payment', [
                'client' => $client,
                'id' => $pesanan->id_pesanan,
            ]);

        } catch (Throwable $e) {
            Cache::forget($lockKey);

            return redirect()->route('customer.checkout', ['client' => $client])
                ->with('error', $e->getMessage());
        }
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
