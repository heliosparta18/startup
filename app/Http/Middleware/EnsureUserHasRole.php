<?php

namespace App\Http\Middleware;

use App\Models\PeranClientPengguna;
use App\Support\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->peran === 'super_admin') {
            return $next($request);
        }

        $context = app(TenantContext::class);
        if (! $context->check()) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Konteks cabang tidak terdefinisi.');
        }

        $hasRole = PeranClientPengguna::where('id_pengguna', $user->id_pengguna)
            ->where('id_client', $context->id())
            ->whereIn('peran', $roles)
            ->where('aktif', true)
            ->exists();

        if (! $hasRole) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda tidak memiliki peran legal di cabang ini.');
        }

        return $next($request);
    }
}