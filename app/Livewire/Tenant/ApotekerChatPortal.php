<?php

namespace App\Livewire\Tenant;

use App\Models\Client;
use App\Models\Konsultasi;
use App\Models\Pengguna;
use App\Models\PesanKonsultasi;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ApotekerChatPortal extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;

    public ?string $activeKonsultasiId = null;

    public string $searchQuery = '';

    public string $balasanTeks = '';

    /** @var mixed */
    public $lampiranBalasan = null;

    public ?string $activePhotoZoom = null;

    public function boot(): void
    {
        $user = auth()->user();
        if (! $user || (! $user->hasRole('apoteker') && ! in_array($user->role, ['apoteker', 'tenant', 'super_admin'], true))) {
            abort(403, 'Akses ditolak. Fitur ini khusus untuk Apoteker dan Staf Berwenang.');
        }
    }

    #[On('open-apoteker-chat')]
    public function openChat(): void
    {
        $this->isOpen = true;

        if (! $this->activeKonsultasiId) {
            $latest = $this->getConsultationsQuery()->first();
            if ($latest) {
                $this->selectKonsultasi($latest->id_konsultasi);
            }
        }
    }

    public function closeChat(): void
    {
        $this->isOpen = false;
        $this->activePhotoZoom = null;
    }

    public function selectKonsultasi(string $id): void
    {
        $konsultasi = $this->getConsultationsQuery()->where('id_konsultasi', $id)->first();
        if (! $konsultasi) {
            return;
        }

        $this->activeKonsultasiId = $konsultasi->id_konsultasi;

        // Tandai pesan pasien yang belum dibaca sebagai terbaca
        PesanKonsultasi::where('id_konsultasi', $konsultasi->id_konsultasi)
            ->where('pengirim_tipe', 'pasien')
            ->whereNull('dibaca_pada')
            ->update(['dibaca_pada' => now()]);
    }

    public function updatedLampiranBalasan(): void
    {
        $this->validate([
            'lampiranBalasan' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ], [
            'lampiranBalasan.mimes' => 'Format berkas harus gambar PNG atau JPG.',
            'lampiranBalasan.max' => 'Ukuran berkas gambar maksimal 5MB.',
        ]);
    }

    public function kirimBalasan(): void
    {
        if (! $this->activeKonsultasiId) {
            $this->addError('balasanTeks', 'Sesi konsultasi tidak ditemukan atau sudah ditutup.');

            return;
        }

        $konsultasi = $this->getConsultationsQuery()
            ->where('id_konsultasi', $this->activeKonsultasiId)
            ->where('status', 'aktif')
            ->first();

        if (! $konsultasi) {
            $this->addError('balasanTeks', 'Sesi konsultasi tidak ditemukan atau sudah ditutup.');

            return;
        }

        $rules = [
            'balasanTeks' => 'nullable|string|max:2000',
        ];

        if (! $this->lampiranBalasan) {
            $rules['balasanTeks'] = 'required|string|max:2000';
        }

        if ($this->lampiranBalasan) {
            $rules['lampiranBalasan'] = 'file|mimes:jpeg,png,jpg|max:5120';
        }

        $this->validate($rules, [
            'balasanTeks.required' => 'Ketik pesan balasan untuk pasien.',
            'lampiranBalasan.mimes' => 'Format berkas harus gambar PNG atau JPG.',
            'lampiranBalasan.max' => 'Ukuran berkas gambar maksimal 5MB.',
        ]);

        $lampiranUrl = null;
        $lampiranTipe = null;

        if ($this->lampiranBalasan) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mimeType = finfo_file($finfo, $this->lampiranBalasan->getRealPath());
                finfo_close($finfo);

                if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
                    $this->addError('lampiranBalasan', 'Format gambar tidak sah. Harus PNG atau JPG.');

                    return;
                }
            }

            $path = $this->lampiranBalasan->store('chat-attachments', 'public');
            $lampiranUrl = '/storage/'.$path;
            $lampiranTipe = $mimeType ?? $this->lampiranBalasan->getMimeType();
        }

        $penggunaId = $this->resolvePenggunaId();
        $pesan = trim($this->balasanTeks);

        PesanKonsultasi::create([
            'id_konsultasi' => $this->activeKonsultasiId,
            'id_pengguna' => $penggunaId,
            'pengirim_tipe' => 'apoteker',
            'pesan' => $pesan !== '' ? $pesan : 'Lampiran informasi resep dari Apoteker',
            'lampiran_url' => $lampiranUrl,
            'lampiran_tipe' => $lampiranTipe,
            'dibuat_pada' => now(),
        ]);

        Konsultasi::where('id_konsultasi', $this->activeKonsultasiId)->update([
            'pesan_terakhir' => $pesan !== '' ? $pesan : 'Apoteker mengirim foto lampiran',
            'pesan_terakhir_pada' => now(),
            'diperbarui_pada' => now(),
        ]);

        $this->balasanTeks = '';
        $this->lampiranBalasan = null;
    }

    public function batalLampiran(): void
    {
        $this->lampiranBalasan = null;
    }

    public function selesaiSesi(string $id): void
    {
        $konsultasi = $this->getConsultationsQuery()->where('id_konsultasi', $id)->first();
        if (! $konsultasi) {
            return;
        }

        $konsultasi->update([
            'status' => 'selesai',
            'diperbarui_pada' => now(),
        ]);
    }

    public function zoomPhoto(?string $url = null): void
    {
        $this->activePhotoZoom = $url;
    }

    public function render(): View
    {
        /** @var Collection<int, Konsultasi> $consultations */
        $consultations = $this->getConsultationsQuery()->get();

        $activeKonsultasi = null;
        $messages = collect();

        if ($this->activeKonsultasiId) {
            $activeKonsultasi = $this->getConsultationsQuery()->with(['client', 'pesan'])->find($this->activeKonsultasiId);
            if ($activeKonsultasi) {
                $messages = $activeKonsultasi->pesan;
            }
        }

        $branchName = 'Semua Cabang';
        $user = auth()->user();
        if ($user) {
            $client = Client::where('slug', $user->getClientSlug())->first();
            if ($client) {
                $branchName = $client->nama_client;
            }
        }

        return view('livewire.tenant.apoteker-chat-portal', [
            'consultations' => $consultations,
            'activeKonsultasi' => $activeKonsultasi,
            'messages' => $messages,
            'branchName' => $branchName,
        ]);
    }

    private function getConsultationsQuery(): Builder
    {
        $query = Konsultasi::with(['client', 'pengguna']);

        $user = auth()->user();
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (! $user->hasRole('super_admin')) {
            $branchSlug = $user->getClientSlug();
            $client = Client::where('slug', $branchSlug)
                ->orWhere('id_client', $branchSlug)
                ->first();

            if ($client) {
                $query->where('id_client', $client->id_client);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (trim($this->searchQuery) !== '') {
            $query->where('nama_pasien', 'like', '%'.trim($this->searchQuery).'%');
        }

        return $query->orderBy('pesan_terakhir_pada', 'desc');
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
}
