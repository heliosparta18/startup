<?php

namespace App\Modules\Catalog\Services;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CustomerSessionService
{
    public const COOKIE_NAME = 'omnipharma_customer_session';

    public const BRANCH_COOKIE_NAME = 'omnipharma_branch_pref';

    public function resolveSession(Request $request): string
    {
        $token = $request->cookie(self::COOKIE_NAME);

        if (! is_string($token) || strlen($token) !== 32) {
            $token = Str::random(32);
            Cookie::queue(self::COOKIE_NAME, $token, 60 * 24 * 30); // Simpan 30 hari
        }

        return $token;
    }

    public function getActiveBranch(Request $request, ?string $urlClient = null): ?Client
    {
        if ($urlClient) {
            $client = Client::findByIdentifier($urlClient);
            if ($client) {
                $this->setActiveBranch($client->id_client);

                return $client;
            }
        }

        $savedBranchId = $request->cookie(self::BRANCH_COOKIE_NAME);
        if ($savedBranchId && is_string($savedBranchId)) {
            $client = Client::where('id_client', $savedBranchId)->where('aktif', true)->first();
            if ($client) {
                return $client;
            }
        }

        return $this->getNearestBranch();
    }

    public function setActiveBranch(string $clientId): void
    {
        Cookie::queue(self::BRANCH_COOKIE_NAME, $clientId, 60 * 24 * 30);
    }

    public function getNearestBranch(?float $latitude = null, ?float $longitude = null): ?Client
    {
        // Fallback default: Cabang utama Banyuwangi Kota atau cabang aktif pertama di database
        return Client::where('slug', 'banyuwangi-kota')->where('aktif', true)->first()
            ?? Client::where('aktif', true)->first();
    }
}
