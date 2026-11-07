<?php

namespace App\Providers;

use App\Support\Tenant\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class, fn () => new TenantContext);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // 1. Rate limiter standar API umum (60 request/menit)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id_pengguna ?? $request->ip());
        });

        // 2. Proteksi brute-force saluran otentikasi broadcast Reverb (30 request/menit)
        RateLimiter::for('broadcasting', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // 3. Proteksi lonjakan transaksi pesanan & verifikasi resep (15 request/menit)
        RateLimiter::for('transaksi', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id_pengguna ?? $request->ip());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
