<?php

namespace App\Support\Tenant;

use App\Models\Client;
use RuntimeException;

class TenantContext
{
    protected ?Client $client = null;

    public function set(Client $client): void
    {
        $this->client = $client;
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
            throw new RuntimeException('Tenant context belum diinisialisasi untuk request ini.');
        }

        return $this->client->id_client;
    }

    public function name(): ?string
    {
        return $this->client?->nama_client;
    }

    public function clear(): void
    {
        $this->client = null;
    }
}
