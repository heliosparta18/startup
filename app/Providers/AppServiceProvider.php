<?php

namespace App\Providers;

use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class, fn () => new TenantContext);
        $this->app->scoped(TenantManagerInterface::class, fn () => $this->app->make(TenantContext::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        if ($this->app->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTP_CF_VISITOR']) && str_contains($_SERVER['HTTP_CF_VISITOR'], 'https'))) {
            URL::forceScheme('https');
        }

        // 1. Rate limiter standar API umum (60 request/menit)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()->id_pengguna ?? $request->ip());
        });

        // 2. Proteksi brute-force saluran otentikasi broadcast Reverb (30 request/menit)
        RateLimiter::for('broadcasting', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // 3. Proteksi lonjakan transaksi pesanan & verifikasi resep (15 request/menit)
        RateLimiter::for('transaksi', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()->id_pengguna ?? $request->ip());
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
