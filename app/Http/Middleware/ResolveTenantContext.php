<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Support\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantIdentifier = $request->route('client');

        if (! $tenantIdentifier) {
            return $next($request);
        }

        $client = Client::where('id_client', $tenantIdentifier)
            ->orWhere('slug', $tenantIdentifier)
            ->orWhere('nama_client', $tenantIdentifier)
            ->first();

        if (! $client && Client::count() === 0) {
            $client = Client::firstOrCreate(
                ['slug' => $tenantIdentifier],
                [
                    'id_client' => (string) Str::uuid(),
                    'nama_client' => 'Cabang '.ucwords(str_replace('-', ' ', $tenantIdentifier)),
                    'alamat' => 'Alamat Demo',
                    'aktif' => true,
                ]
            );
        }

        if (! $client) {
            abort(Response::HTTP_NOT_FOUND, 'Apotek cabang tidak ditemukan.');
        }

        if (! $client->aktif) {
            abort(Response::HTTP_FORBIDDEN, 'Apotek cabang sedang dinonaktifkan.');
        }

        if ($request->is('cabang/*')) {
            $user = $request->user();

            if ($user && method_exists($user, 'hasRole') && ! $user->hasRole('super_admin')) {
                if (method_exists($user, 'adminClient') && $user->adminClient()->exists()) {
                    $hasAccess = $user->adminClient()
                        ->where('id_client', $client->id_client)
                        ->exists();

                    if (! $hasAccess) {
                        abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki hak akses operasional pada cabang apotek ini.');
                    }
                }
            }
        }

        $this->tenantContext->set($client);

        return $next($request);
    }
}
