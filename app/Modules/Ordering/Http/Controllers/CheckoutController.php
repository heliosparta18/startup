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
use Illuminate\Support\Str;
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
            return $this->redirectToCart($request, $client)
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
            return $this->redirectToCart($request, $client)
                ->with('error', 'Transaksi checkout Anda sedang diproses. Mohon tunggu beberapa detik.');
        }

        try {
            $items = $cartService->getItems($sessionId, $clientId);

            if (empty($items)) {
                Cache::forget($lockKey);

                return $this->redirectToCart($request, $client)
                    ->with('error', 'Keranjang belanja Anda kosong.');
            }

            $user = $request->user();
            $userId = null;

            if ($user) {
                $pengguna = Pengguna::where('telepon_email', $user->email)->first();

                if (! $pengguna) {
                    $pengguna = Pengguna::create([
                        'id_pengguna' => (string) Str::uuid(),
                        'id_client' => $clientId,
                        'nama_lengkap' => $user->name,
                        'telepon_email' => $user->email,
                        'kata_sandi_hash' => $user->password,
                        'peran' => 'pelanggan',
                    ]);
                }

                $userId = $pengguna->id_pengguna;
            }

            $metodeLayanan = (string) $request->input('metode_layanan', 'click_collect');
            $slotWaktu = $request->input('slot_waktu');
            $catatan = trim((string) $request->input('catatan', ''));

            if ($metodeLayanan === 'delivery') {
                $isRogojampi = str_contains(strtolower($client), 'rogojampi');
                $originLat = $isRogojampi ? -8.3069 : -8.2192;
                $originLng = $isRogojampi ? 114.2896 : 114.3691;

                $jarakKm = round(max(0.5, min(25.0, (float) $request->input('jarak_km', 2.0))), 1);
                $ongkir = $jarakKm <= 2.0 ? 5000 : (int) (5000 + ceil($jarakKm - 2.0) * 2500);

                $rawKoordinat = trim((string) $request->input('koordinat_tujuan', ''));
                $linkMaps = trim(str_replace(['[', ']'], '', (string) $request->input('link_maps', '')));
                $koordinat = null;

                if (preg_match('/^(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)$/', $rawKoordinat, $m)) {
                    $koordinat = round((float) $m[1], 5) . ',' . round((float) $m[2], 5);
                } elseif ($linkMaps !== '') {
                    foreach ([
                        '/@(-?\d+\.\d+),(-?\d+\.\d+)/',
                        '/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/',
                        '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',
                        '/(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)/',
                    ] as $pattern) {
                        if (preg_match($pattern, $linkMaps, $m)) {
                            $lat = (float) $m[1];
                            $lng = (float) $m[2];
                            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                                $koordinat = round($lat, 5) . ',' . round($lng, 5);
                                break;
                            }
                        }
                    }
                }

                $alamat = trim(str_replace(['[', ']'], ['(', ')'], (string) $request->input('alamat_pengantaran', '')));

                if ($koordinat !== null) {
                    $mapsRouteUrl = "https://www.google.com/maps/dir/?api=1&origin={$originLat},{$originLng}&destination={$koordinat}&travelmode=driving";
                } elseif (preg_match('#^https?://#i', $linkMaps)) {
                    $mapsRouteUrl = $linkMaps;
                } elseif ($alamat !== '') {
                    $mapsRouteUrl = "https://www.google.com/maps/dir/?api=1&origin={$originLat},{$originLng}&destination=" . urlencode($alamat) . "&travelmode=driving";
                } else {
                    $koordinat = round($originLat + 0.008 * $jarakKm, 5) . ',' . round($originLng + 0.005 * $jarakKm, 5);
                    $mapsRouteUrl = "https://www.google.com/maps/dir/?api=1&origin={$originLat},{$originLng}&destination={$koordinat}&travelmode=driving";
                }

                $slotWaktu = "Kurir Apotek ({$jarakKm} km)";

                $prefix = "[ONGKIR:{$ongkir}][JARAK:{$jarakKm}]";
                if ($koordinat !== null) {
                    $prefix .= "[KOORDINAT:{$koordinat}]";
                }
                $prefix .= "[MAPS:{$mapsRouteUrl}]";
                if ($alamat !== '') {
                    $prefix .= "[ALAMAT:{$alamat}]";
                }

                $catatan = $catatan !== '' ? "{$prefix} {$catatan}" : $prefix;
            }

            $pesanan = $createOrderAction->execute(
                $clientId,
                $userId,
                $items,
                $metodeLayanan,
                $sessionId,
                $slotWaktu,
                null,
                $catatan !== '' ? $catatan : null
            );
            $cartService->clear($sessionId, $clientId);

            Cache::forget($lockKey);

            if ($request->is('klien/*')) {
                return redirect()->route('customer.order.payment', [
                    'client' => $client,
                    'id' => $pesanan->id_pesanan,
                ]);
            }

            return redirect()->route('pembayaran.show', [
                'id' => $pesanan->id_pesanan,
            ]);

        } catch (Throwable $e) {
            Cache::forget($lockKey);

            $redirect = $request->is('klien/*')
                ? redirect()->route('customer.checkout', ['client' => $client])
                : redirect()->route('checkout.index');

            return $redirect->with('error', $e->getMessage());
        }
    }

    /**
     * Arahkan kembali ke keranjang sesuai konteks URL (legacy klien/* atau route singkat).
     */
    private function redirectToCart(Request $request, string $client): RedirectResponse
    {
        return $request->is('klien/*')
            ? redirect()->route('customer.cart', ['client' => $client])
            : redirect()->route('keranjang');
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
