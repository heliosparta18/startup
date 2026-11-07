<?php

namespace App\Support\Tenant\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;

class TenantSwitched
{
    use Dispatchable;

    public function __construct(
        public readonly ?Client $previousClient,
        public readonly ?Client $currentClient
    ) {}
}
