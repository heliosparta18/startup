<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifikasiResepRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diotorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sanitasi input sebelum divalidasi (pencegahan serangan XSS).
     */
    protected function prepareForValidation(): void
    {
        $catatan = $this->input('catatan');
        $alasan = $this->input('alasan');

        $this->merge([
            'catatan' => $catatan ? strip_tags(trim((string) $catatan)) : null,
            'alasan' => $alasan ? strip_tags(trim((string) $alasan)) : null,
        ]);
    }

    /**
     * Aturan validasi telaah dan verifikasi resep dokter oleh apoteker.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:disetujui,ditolak'],
            'nomor_sip' => ['nullable', 'string', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'alasan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan kesalahan khusus bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Keputusan verifikasi resep (disetujui atau ditolak) wajib dipilih.',
            'action.in' => 'Keputusan verifikasi hanya boleh bernilai disetujui atau ditolak.',
        ];
    }
}
