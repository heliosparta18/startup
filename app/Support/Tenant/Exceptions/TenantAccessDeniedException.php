<?php

namespace App\Support\Tenant\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantAccessDeniedException extends HttpException
{
    public function __construct(string $detail = '')
    {
        $message = $detail !== ''
            ? "Akses ditolak: {$detail}"
            : 'Anda tidak memiliki hak otorisasi untuk mengakses portal cabang apotek ini.';

        parent::__construct(403, $message);
    }
}
