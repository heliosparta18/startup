<?php

namespace App\Http\Middleware;

use App\Models\Client;
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

        if ($user === null) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Pengguna belum terautentikasi.');
        }

        if (method_exists($user, 'isActive') && ! $user->isActive()) {
            abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Akun tidak aktif.');
        }

        // Super admin memiliki hak akses penuh
        if ($user->peran === 'super_admin' || (method_exists($user, 'hasRole') && $user->hasRole('super_admin'))) {
            return $next($request);
        }

        $parsedRoles = [];
        foreach ($roles as $rString) {
            $splitRoles = explode(',', str_replace('|', ',', $rString));
            foreach ($splitRoles as $role) {
                $parsedRoles[] = trim($role);
            }
        }

        // Resolusi context tenant jika request berada pada rute cabang
        $context = app(TenantContext::class);
        $clientId = null;

        if ($context->check()) {
            $clientId = $context->id();
        } else {
            $clientParam = $request->route('client');
            if (is_string($clientParam)) {
                $clientModel = Client::where('id_client', $clientParam)
                    ->orWhere('slug', $clientParam)
                    ->first();

                if ($clientModel) {
                    $context->set($clientModel);
                    $clientId = $clientModel->id_client;
                }
            }
        }

        if ($clientId) {
            $hasTenantRole = PeranClientPengguna::where('id_pengguna', $user->id_pengguna ?? $user->id)
                ->where('id_client', $clientId)
                ->whereIn('peran', $parsedRoles)
                ->exists();

            if ($hasTenantRole) {
                return $next($request);
            }
        }

        // Fallback pengecekan role global pada model user/pengguna
        if (method_exists($user, 'hasRole')) {
            foreach ($parsedRoles as $role) {
                if ($user->hasRole($role)) {
                    return $next($request);
                }
            }
        }

        abort(Response::HTTP_FORBIDDEN, 'Akses ditolak: Anda tidak memiliki peran legal untuk aksi ini.');
    }
}
