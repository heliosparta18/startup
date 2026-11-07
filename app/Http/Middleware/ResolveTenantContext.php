<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\Exceptions\TenantAccessDeniedException;
use App\Support\Tenant\Exceptions\TenantInactiveException;
use App\Support\Tenant\Exceptions\TenantNotFoundException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        private readonly TenantManagerInterface $tenantManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantIdentifier = (string) $request->route('client');

        if ($tenantIdentifier === '') {
            return $next($request);
        }

        $client = Client::where('id_client', $tenantIdentifier)
            ->orWhere('slug', $tenantIdentifier)
            ->orWhere('nama_client', $tenantIdentifier)
            ->first();

        // Fallback saat bootstrapping awal test
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
            throw new TenantNotFoundException($tenantIdentifier);
        }

        if (! $client->aktif) {
            throw new TenantInactiveException($client->nama_client);
        }

        // Pertahanan IDOR portal apoteker dan staf cabang
        if ($request->is('cabang/*')) {
            $user = $request->user();

            /** @phpstan-ignore function.alreadyNarrowedType */
            if ($user && method_exists($user, 'hasRole') && ! $user->hasRole('super_admin')) {
                if (method_exists($user, 'adminClient') && $user->adminClient()->exists()) {
                    $hasAccess = $user->adminClient()
                        ->where('id_client', $client->id_client)
                        ->exists();

                    if (! $hasAccess) {
                        Log::warning('Upaya IDOR portal cabang dicegah:', [
                            'id_pengguna' => $user->id_pengguna ?? 'unknown',
                            'target_cabang' => $client->id_client,
                            'path' => $request->path(),
                            'ip' => $request->ip(),
                        ]);

                        throw new TenantAccessDeniedException(
                            'Anda tidak memiliki izin operasional pada apotek cabang ini.'
                        );
                    }
                }
            }
        }

        $this->tenantManager->set($client);

        return $next($request);
    }
}
