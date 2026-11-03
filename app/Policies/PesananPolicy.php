<?php

namespace App\Policies;

use App\Models\Pesanan;
use App\Support\Tenant\TenantContext;
use Illuminate\Contracts\Auth\Authenticatable;

class PesananPolicy
{
    public function view(?Authenticatable $user, Pesanan $pesanan): bool
    {
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return true;
        }

        $context = app(TenantContext::class);

        if ($context->check() && $pesanan->id_client !== $context->id()) {
            return false;
        }

        return true;
    }

    public function verify(?Authenticatable $user, Pesanan $pesanan): bool
    {
        return $this->view($user, $pesanan);
    }
}
