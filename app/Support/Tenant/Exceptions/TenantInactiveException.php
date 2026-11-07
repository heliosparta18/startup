<?php

namespace App\Support\Tenant\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantInactiveException extends HttpException
{
    public function __construct(string $tenantName = '')
    {
        $message = $tenantName !== ''
            ? "Operasional apotek cabang '{$tenantName}' sedang dinonaktifkan sementara oleh manajemen."
            : 'Apotek cabang sedang tidak beroperasi.';

        parent::__construct(403, $message);
    }
}
