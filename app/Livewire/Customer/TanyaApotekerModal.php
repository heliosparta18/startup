<?php

namespace App\Livewire\Customer;

use App\Models\Client;
use App\Models\Konsultasi;
use App\Models\Pengguna;
use App\Models\PesanKonsultasi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class TanyaApotekerModal extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;

    public bool $isVerified = false;

    public string $nama_pasien = '';

    public string $selected_client_id = '';

    public ?string $konsultasiId = null;

    public string $pesanBaru = '';

    /** @var mixed */
    public $lampiran = null;

    public ?string $activePhotoZoom = null;

    public function mount(?string $clientId = null): void
    {
        $this->resolveDefaultClient($clientId);

        if (auth()->check() && empty($this->nama_pasien)) {
            $this->nama_pasien = (string) auth()->user()->name;
        }

        $this->checkForActiveConsultation();
    }

    #[On('open-tanya-apoteker')]
    public function openModal(?string $clientId = null): void
    {
        $this->isOpen = true;

        if ($clientId) {
            $client = Client::where('aktif', true)
                ->where(function ($q) use ($clientId) {
                    $q->where('id_client', $clientId)
                        ->orWhere('slug', $clientId);
                })->first();

            if ($client) {
                $this->selected_client_id = $client->id_client;
            }
        }

        if (empty($this->selected_client_id)) {
            $this->resolveDefaultClient();
        }

        if (auth()->check() && empty($this->nama_pasien)) {
            $this->nama_pasien = (string) auth()->user()->name;
        }

        $this->checkForActiveConsultation();
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->activePhotoZoom = null;
    }

    public function resetSesi(): void
    {
        $this->konsultasiId = null;
        $this->isVerified = false;
        $this->pesanBaru = '';
        $this->lampiran = null;
    }

    public function mulaiKonsultasi(): void
    {
        $this->validate([
            'nama_pasien' => 'required|string|min:2|max:150',
            'selected_client_id' => [
                'required',
                'string',
                Rule::exists('client', 'id_client')->where('aktif', true),
            ],
        ], [
            'nama_pasien.required' => 'Nama lengkap wajib diisi untuk memulai konsultasi.',
            'nama_pasien.min' => 'Nama pasien minimal 2 karakter.',
            'selected_client_id.required' => 'Pilih cabang apotek tujuan konsultasi.',
            'selected_client_id.exists' => 'Cabang apotek yang dipilih tidak aktif atau tidak valid.',
        ]);

        $penggunaId = $this->resolvePenggunaId();
        $token = $this->resolveSessionToken();

        $starterMessage = 'Halo, saya butuh bantuan konsultasi obat';

        $konsultasi = Konsultasi::create([
            'id_client' => $this->selected_client_id,
            'id_pengguna' => $penggunaId,
            'token_sesi' => $token,
            'nama_pasien' => trim($this->nama_pasien),
            'status' => 'aktif',
            'pesan_terakhir' => $starterMessage,
            'pesan_terakhir_pada' => now(),
        ]);

        PesanKonsultasi::create([
            'id_konsultasi' => $konsultasi->id_konsultasi,
            'id_pengguna' => $penggunaId,
            'pengirim_tipe' => 'pasien',
            'pesan' => $starterMessage,
            'lampiran_url' => null,
            'lampiran_tipe' => null,
            'dibuat_pada' => now(),
        ]);

        $this->konsultasiId = $konsultasi->id_konsultasi;
        $this->isVerified = true;
    }

    public function updatedLampiran(): void
    {
        $this->validate([
            'lampiran' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ], [
            'lampiran.mimes' => 'Format berkas harus gambar PNG atau JPG.',
            'lampiran.max' => 'Ukuran berkas gambar maksimal 5MB.',
        ]);
    }

    public function kirimPesan(): void
    {
        if (! $this->konsultasiId) {
            $this->addError('pesanBaru', 'Sesi konsultasi tidak ditemukan atau sudah berakhir.');

            return;
        }

        $penggunaId = $this->resolvePenggunaId();
        $token = $this->resolveSessionToken();
        $konsultasi = Konsultasi::where('id_konsultasi', $this->konsultasiId)
            ->where('status', 'aktif')
            ->where(function ($q) use ($penggunaId, $token) {
                if ($penggunaId) {
                    $q->where('id_pengguna', $penggunaId);
                } else {
                    $q->where('token_sesi', $token);
                }
            })->first();

        if (! $konsultasi) {
            $this->addError('pesanBaru', 'Sesi konsultasi tidak ditemukan atau sudah berakhir.');

            return;
        }

        $rules = [
            'pesanBaru' => 'nullable|string|max:2000',
        ];

        if (! $this->lampiran) {
            $rules['pesanBaru'] = 'required|string|max:2000';
        }

        if ($this->lampiran) {
            $rules['lampiran'] = 'file|mimes:jpeg,png,jpg|max:5120';
        }

        $this->validate($rules, [
            'pesanBaru.required' => 'Ketik pesan atau lampirkan foto resep.',
            'lampiran.mimes' => 'Format gambar harus PNG atau JPG.',
            'lampiran.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        $lampiranUrl = null;
        $lampiranTipe = null;

        if ($this->lampiran) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mimeType = finfo_file($finfo, $this->lampiran->getRealPath());
                finfo_close($finfo);

                if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
                    $this->addError('lampiran', 'Berkas gambar tidak sah. Format harus PNG atau JPG.');

                    return;
                }
            }

            $path = $this->lampiran->store('chat-attachments', 'public');
            $lampiranUrl = '/storage/'.$path;
            $lampiranTipe = $mimeType ?? $this->lampiran->getMimeType();
        }

        $pesanTeks = trim($this->pesanBaru);

        PesanKonsultasi::create([
            'id_konsultasi' => $this->konsultasiId,
            'id_pengguna' => $penggunaId,
            'pengirim_tipe' => 'pasien',
            'pesan' => $pesanTeks !== '' ? $pesanTeks : 'Lampiran foto resep obat',
            'lampiran_url' => $lampiranUrl,
            'lampiran_tipe' => $lampiranTipe,
            'dibuat_pada' => now(),
        ]);

        Konsultasi::where('id_konsultasi', $this->konsultasiId)->update([
            'pesan_terakhir' => $pesanTeks !== '' ? $pesanTeks : 'Mengirim foto lampiran resep',
            'pesan_terakhir_pada' => now(),
            'diperbarui_pada' => now(),
        ]);

        $this->pesanBaru = '';
        $this->lampiran = null;
    }

    public function batalLampiran(): void
    {
        $this->lampiran = null;
    }

    public function zoomPhoto(?string $url = null): void
    {
        $this->activePhotoZoom = $url;
    }

    public function selesaiKonsultasi(): void
    {
        if ($this->konsultasiId) {
            $penggunaId = $this->resolvePenggunaId();
            $token = $this->resolveSessionToken();
            Konsultasi::where('id_konsultasi', $this->konsultasiId)
                ->where(function ($q) use ($penggunaId, $token) {
                    if ($penggunaId) {
                        $q->where('id_pengguna', $penggunaId);
                    } else {
                        $q->where('token_sesi', $token);
                    }
                })
                ->update([
                    'status' => 'selesai',
                    'diperbarui_pada' => now(),
                ]);
        }

        $this->konsultasiId = null;
        $this->isVerified = false;
        $this->pesanBaru = '';
        $this->lampiran = null;
    }

    public function render(): View
    {
        /** @var Collection<int, Client> $branches */
        $branches = Client::where('aktif', true)->orderBy('nama_client')->get();

        $konsultasi = null;
        $messages = collect();

        if ($this->konsultasiId) {
            $penggunaId = $this->resolvePenggunaId();
            $token = $this->resolveSessionToken();
            $konsultasi = Konsultasi::with(['client', 'pesan'])
                ->where('id_konsultasi', $this->konsultasiId)
                ->where(function ($q) use ($penggunaId, $token) {
                    if ($penggunaId) {
                        $q->where('id_pengguna', $penggunaId);
                    } else {
                        $q->where('token_sesi', $token);
                    }
                })->first();

            if ($konsultasi) {
                $messages = $konsultasi->pesan;
            } else {
                $this->konsultasiId = null;
                $this->isVerified = false;
            }
        } elseif ($this->isVerified) {
            $this->isVerified = false;
        }

        return view('livewire.customer.tanya-apoteker-modal', [
            'branches' => $branches,
            'konsultasi' => $konsultasi,
            'messages' => $messages,
        ]);
    }

    private function resolveDefaultClient(?string $preferredId = null): void
    {
        if ($preferredId) {
            $client = Client::where('aktif', true)
                ->where(function ($q) use ($preferredId) {
                    $q->where('id_client', $preferredId)
                        ->orWhere('slug', $preferredId);
                })->first();

            if ($client) {
                $this->selected_client_id = $client->id_client;

                return;
            }
        }

        $sessionSlug = session('active_branch') ?? session('active_client');
        if (is_string($sessionSlug) && $sessionSlug !== '') {
            $client = Client::where('aktif', true)->where('slug', $sessionSlug)->first();
            if ($client) {
                $this->selected_client_id = $client->id_client;

                return;
            }
        }

        $firstClient = Client::where('aktif', true)->orderBy('nama_client')->first();
        if ($firstClient) {
            $this->selected_client_id = $firstClient->id_client;
        }
    }

    private function checkForActiveConsultation(): void
    {
        $penggunaId = $this->resolvePenggunaId();
        $token = $this->resolveSessionToken();

        $active = null;

        if ($penggunaId) {
            $active = Konsultasi::where('id_pengguna', $penggunaId)
                ->where('status', 'aktif')
                ->latest('pesan_terakhir_pada')
                ->first();
        }

        if (! $active && $token) {
            $active = Konsultasi::where('token_sesi', $token)
                ->where('status', 'aktif')
                ->latest('pesan_terakhir_pada')
                ->first();
        }

        if ($active) {
            $this->konsultasiId = $active->id_konsultasi;
            $this->selected_client_id = $active->id_client;
            $this->nama_pasien = $active->nama_pasien;
            $this->isVerified = true;
        }
    }

    private function resolvePenggunaId(): ?string
    {
        if (! auth()->check()) {
            return null;
        }

        $user = auth()->user();
        $userPenggunaId = $user->getAttribute('id_pengguna');
        if (is_string($userPenggunaId) && $userPenggunaId !== '') {
            return $userPenggunaId;
        }

        $email = $user->getAttribute('email');
        if (is_string($email) && $email !== '') {
            $pengguna = Pengguna::where('telepon_email', $email)->first();

            return $pengguna?->id_pengguna;
        }

        return null;
    }

    private function resolveSessionToken(): string
    {
        $cookie = request()->cookie('omnipharma_customer_session') ?? request()->cookie('omni_session');
        if (is_string($cookie) && $cookie !== '') {
            return $cookie;
        }

        return session()->getId() ?: 'guest-token';
    }
}
