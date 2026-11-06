<?php

namespace App\Modules\Catalog\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CustomerSessionService
{
    public const COOKIE_NAME = 'omnipharma_customer_session';

    public function resolveSession(Request $request): string
    {
        $token = $request->cookie(self::COOKIE_NAME);

        if (! is_string($token) || strlen($token) !== 32) {
            $token = Str::random(32);
            Cookie::queue(self::COOKIE_NAME, $token, 60 * 24 * 30); // Simpan 30 hari
        }

        return $token;
    }
}
