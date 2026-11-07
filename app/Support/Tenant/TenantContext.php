<?php

namespace App\Support\Tenant;

use App\Models\Client;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\Events\TenantResolved;
use App\Support\Tenant\Events\TenantSwitched;
use App\Support\Tenant\Exceptions\TenantContextMissingException;
use Closure;

class TenantContext implements TenantManagerInterface
{
    /**
     * Instance Client tenant aktif dalam siklus request.
     */
    protected ?Client $client = null;

    /**
     * Flag penanda apakah event resolving telah dipancarkan.
     */
    protected bool $resolved = false;

    public function set(Client $client): void
    {
        $previous = $this->client;
        $this->client = $client;

        if ($previous?->id_client !== $client->id_client) {
            TenantSwitched::dispatch($previous, $client);
        }

        if (! $this->resolved) {
            TenantResolved::dispatch($client);
            $this->resolved = true;
        }
    }

    public function get(): ?Client
    {
        return $this->client;
    }

    public function check(): bool
    {
        return $this->client !== null;
    }

    public function has(): bool
    {
        return $this->client !== null;
    }

    public function id(): string
    {
        if (! $this->client) {
            throw new TenantContextMissingException('Penyaringan multi-tenant');
        }

        return $this->client->id_client;
    }

    public function name(): ?string
    {
        return $this->client?->nama_client;
    }

    public function slug(): ?string
    {
        return $this->client?->slug;
    }

    public function clear(): void
    {
        $previous = $this->client;
        $this->client = null;
        $this->resolved = false;

        if ($previous !== null) {
            TenantSwitched::dispatch($previous, null);
        }
    }

    /**
     * @template TReturn
     *
     * @param  Closure(Client): TReturn  $callback
     * @return TReturn
     */
    public function runInContext(Client $client, Closure $callback): mixed
    {
        $originalClient = $this->client;
        $this->set($client);

        try {
            return $callback($client);
        } finally {
            if ($originalClient !== null) {
                $this->set($originalClient);
            } else {
                $this->clear();
            }
        }
    }
}
