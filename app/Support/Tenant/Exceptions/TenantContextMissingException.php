<?php

namespace App\Support\Tenant\Exceptions;

use RuntimeException;

class TenantContextMissingException extends RuntimeException
{
    public function __construct(string $operation = 'Operasi database')
    {
        parent::__construct(
            "{$operation} membutuhkan konteks tenant aktif, tetapi TenantContext belum diinisialisasi untuk siklus request ini."
        );
    }
}
