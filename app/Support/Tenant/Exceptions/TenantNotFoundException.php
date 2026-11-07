<?php

namespace App\Support\Tenant\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantNotFoundException extends HttpException
{
    public function __construct(string $identifier = '')
    {
        $message = $identifier !== ''
            ? "Apotek cabang dengan pengenal '{$identifier}' tidak ditemukan dalam jaringan OmniPharma."
            : 'Apotek cabang yang diminta tidak ditemukan.';

        parent::__construct(404, $message);
    }
}
