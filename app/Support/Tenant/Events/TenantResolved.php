<?php

namespace App\Support\Tenant\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;

class TenantResolved
{
    use Dispatchable;

    public function __construct(
        public readonly Client $client
    ) {}
}
