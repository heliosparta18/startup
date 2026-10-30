<?php

namespace App\Modules\Prescription;

use App\Modules\ModuleServiceProvider;

/**
 * Modul: Prescription (alias `prescription`).
 * Bounded Context: Unggah resep dokter digital, verifikasi legalitas SIP dokter
 * dan kesesuaian dosis oleh apoteker cabang, workflow telaah resep (#ORD-20260917-0042).
 *
 * Menggantikan modul legacy Kitchen untuk domain Apotek Multi-Klien (OmniPharma).
 */
final class PrescriptionServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        // Container bindings untuk prescription validation & workflow
    }

    protected function moduleAlias(): string
    {
        return 'prescription';
    }
}
