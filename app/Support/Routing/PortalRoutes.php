<?php

namespace App\Support\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

/**
 * Definisi TUNGGAL grup route per portal (prefix + name + middleware). Dipakai oleh
 * routes/{customer,tenant,admin}.php (via bootstrap/app.php) DAN oleh file route milik modul
 * (app/Modules/{Modul}/routes/{portal}.php), sehingga semua route satu portal selalu memakai
 * lapis keamanan yang sama.
 */
final class PortalRoutes
{
    /**
     * Portal pelanggan (publik, anonim).
     * Melayani katalog obat, detail sediaan, dan unggah resep digital (Mockup Layar 1).
     */
    public static function customer(Closure|string $routes): void
    {
        Route::middleware('web')
            ->prefix('klien/{client}')
            ->name('customer.')
            ->group($routes);
    }

    /**
     * Portal operator tenant / apoteker cabang (internal).
     * Melayani verifikasi resep dokter dan pemantauan stok batch FEFO (Mockup Layar 2).
     */
    public static function tenant(Closure|string $routes): void
    {
        Route::middleware(['web', 'auth', 'verified', 'role:apoteker'])
            ->prefix('cabang/{client}')
            ->name('tenant.')
            ->group($routes);
    }

    /**
     * Portal super admin pengelola apotek pusat (internal).
     * Melayani executive dashboard agregasi penjualan dan tutup periode (Mockup Layar 3-4).
     */
    public static function admin(Closure|string $routes): void
    {
        Route::middleware(['web', 'auth', 'verified', 'role:super_admin'])
            ->prefix('admin')
            ->name('admin.')
            ->group($routes);
    }
}
