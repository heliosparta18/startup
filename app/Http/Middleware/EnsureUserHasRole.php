<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforcement server-side untuk konteks (admin/tenant). Menu tersembunyi bukan
 * authorization; setiap route internal tetap ditolak 403 bila role tidak cocok.
 * Kontrak role penuh (policy + tenant membership) menyusul Modul 4–5.
 */
final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Guest ditangani middleware auth; di sini fokus pada role/status.
        if ($user === null) {
            abort(403);
        }

        // Cek status aktif
        if (! $user->isActive()) {
            abort(403);
        }

        $allowed = false;
        foreach ($roles as $rString) {
            $splitRoles = explode(',', str_replace('|', ',', $rString));
            foreach ($splitRoles as $role) {
                $role = trim($role);
                if ($user->hasRole($role)) {
                    $allowed = true;
                    break 2;
                }
            }
        }

        if (! $allowed) {
            abort(403);
        }

        return $next($request);
    }
}
