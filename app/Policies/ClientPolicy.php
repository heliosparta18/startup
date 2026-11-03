<?php

namespace App\Policies;

use App\Models\Client;
use App\Support\Tenant\TenantContext;
use Illuminate\Contracts\Auth\Authenticatable;

class ClientPolicy
{
    public function view(?Authenticatable $user, Client $client): bool
    {
        return $client->aktif;
    }

    public function operate(?Authenticatable $user, Client $client): bool
    {
        if (! $client->aktif) {
            return false;
        }

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return true;
        }

        $context = app(TenantContext::class);

        if ($context->check() && $context->id() !== $client->id_client) {
            return false;
        }

        return true;
    }
}
