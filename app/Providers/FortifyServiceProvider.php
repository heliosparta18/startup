<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract
            {
                public function toResponse($request)
                {
                    $user = $request->user();
                    $redirectUrl = route('dashboard');

                    if ($user?->hasRole('super_admin') || $user?->role === 'super_admin') {
                        $redirectUrl = route('admin.dashboard');
                    } elseif ($user?->hasRole('apoteker') || $user?->role === 'apoteker') {
                        $clientSlug = ($user->email === 'dewi@omnipharma.test' || $user->email === 'dinda@omnipharma.test') ? 'rogojampi' : 'banyuwangi-kota';
                        $redirectUrl = route('tenant.dashboard', ['client' => $clientSlug]);
                    }

                    $intended = session()->get('url.intended');
                    if ($intended && (
                        url($intended) === url('/')
                        || $intended === '/'
                        || str_contains($intended, '/klien/')
                    )) {
                        session()->forget('url.intended');
                    }

                    session()->flash('welcome_user', $user->name);

                    return $request->wantsJson()
                        ? response()->json(['two_factor' => false, 'redirect' => $redirectUrl])
                        : redirect()->intended($redirectUrl);
                }
            };
        });

        $this->app->singleton(RegisterResponseContract::class, function () {
            return new class implements RegisterResponseContract
            {
                public function toResponse($request)
                {
                    auth()->logout();
                    session()->flash('status', 'Pendaftaran akun berhasil! Silakan masuk menggunakan email dan kata sandi Anda.');

                    return $request->wantsJson()
                        ? response()->json(['redirect' => route('login')])
                        : redirect()->route('login');
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::authenticateUsing(function (Request $request) {
            $identifier = trim((string) $request->input('email', ''));
            $password = (string) $request->input('password', '');

            if ($identifier === '' || $password === '') {
                return null;
            }

            $normalized = strtolower($identifier);
            $user = User::whereRaw('LOWER(email) = ?', [$normalized])->first();

            if (! $user && ! str_contains($normalized, '@')) {
                $user = User::whereRaw('LOWER(email) = ?', [$normalized.'@omnipharma.test'])->first();
            }

            if (! $user) {
                $user = User::whereRaw('LOWER(name) = ?', [$normalized])->first();
            }

            if ($user && $user->isActive() && Hash::check($password, $user->password)) {
                return $user;
            }

            return null;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages::auth.login'));
        Fortify::verifyEmailView(fn () => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(fn () => view('pages::auth.register'));
        Fortify::resetPasswordView(fn () => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages::auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}
