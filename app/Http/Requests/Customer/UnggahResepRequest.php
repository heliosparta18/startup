<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UnggahResepRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diotorisasi untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi unggah foto resep dokter.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_dokter' => ['nullable', 'string', 'max:150'],
            'namaDokter' => ['nullable', 'string', 'max:150'],
            'sip_dokter' => ['nullable', 'string', 'max:100'],
            'sipDokter' => ['nullable', 'string', 'max:100'],
            'nama_pasien' => ['nullable', 'string', 'max:150'],
            'namaPasien' => ['nullable', 'string', 'max:150'],
            'umurPasien' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'foto_resep' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg',
                'max:5120', // Maksimal 5MB
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        // Pemeriksaan Magic Bytes untuk memastikan file benar-benar gambar asli
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        if ($finfo !== false) {
                            $mimeType = finfo_file($finfo, $value->getRealPath());
                            finfo_close($finfo);

                            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
                            if (! in_array($mimeType, $allowedMimes, true)) {
                                $fail('Berkas resep tidak sah: Magic bytes tidak cocok dengan berkas gambar asli.');
                            }
                        }
                    }
                },
            ],
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
            'foto_resep.mimes' => 'Format file foto resep harus berupa JPG, JPEG, atau PNG.',
            'foto_resep.max' => 'Ukuran file foto resep maksimal 5MB.',
        ];
    }
}
