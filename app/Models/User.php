<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property CarbonImmutable|Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property string|null $role
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role', 'status'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
/**
 * @property string $id_pengguna
 */
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Cache slug cabang per request untuk menghindari query duplikat ke pengguna/admin_client.
     */
    protected ?string $cachedClientSlug = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Dapatkan label tampilan role pengguna yang ramah pengguna.
     * Default ke 'Customer' jika belum ditetapkan atau bernilai null.
     */
    public function roleLabel(): string
    {
        return match (strtolower((string) ($this->role ?? ''))) {
            'super_admin', 'superadmin' => 'Super Admin',
            'admin' => 'Administrator',
            'apoteker' => 'Apoteker',
            'tenant' => 'Operator Apotek',
            'staf_klien', 'staff', 'staf' => 'Staf Cabang',
            'kasir' => 'Kasir',
            'pelanggan', 'customer' => 'Customer',
            default => 'Customer',
        };
    }

    /**
     * Apakah user memegang role tertentu. Kontrak role interim Modul 2
     * (kolom `role`); diformalkan menjadi relasi pada Modul 4–5.
     */
    public function hasRole(string $role): bool
    {
        return ($this->role === $role
            || ($role === 'apoteker' && $this->role === 'tenant')
            || ($role === 'pelanggan' && $this->role === 'customer')
            || ($role === 'admin' && in_array($this->role, ['super_admin', 'kasir', 'staf_klien'], true))
            || ($role === 'kasir' && in_array($this->role, ['admin', 'staf_klien'], true))
            || ($role === 'tenant' && $this->role === 'apoteker')
            || ($role === 'customer' && $this->role === 'pelanggan'))
            && $this->isActive();
    }

    /**
     * Tentukan URL dashboard utama sesuai peran pengguna terautentikasi.
     */
    public function homeRoute(): string
    {
        if ($this->hasRole('super_admin') || $this->role === 'super_admin') {
            return route('admin.dashboard');
        }

        if ($this->hasRole('apoteker') || in_array($this->role, ['apoteker', 'tenant'], true)) {
            return route('tenant.dashboard', ['client' => $this->getClientSlug()]);
        }

        if ($this->hasRole('kasir') || in_array($this->role, ['admin', 'kasir', 'staf_klien'], true)) {
            return route('kasir.dashboard');
        }

        return route('dashboard');
    }

    /**
     * Dapatkan daftar navigasi cepat pada dropdown Profile dan halaman /settings/profile sesuai role.
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     href: string,
     *     section: string,
     *     primary: bool,
     *     icon: string
     * }>
     */
    public function profileActions(?string $preferredBranch = null): array
    {
        $assignedBranch = $this->getClientSlug();
        $adminBranch = ($preferredBranch !== null && trim($preferredBranch) !== '')
            ? trim($preferredBranch)
            : $assignedBranch;

        if ($this->hasRole('super_admin') || $this->role === 'super_admin') {
            return [
                [
                    'key' => 'admin-dashboard',
                    'label' => 'Executive Dashboard Pusat',
                    'description' => 'Ringkasan omzet jaringan, cabang, dan tutup periode.',
                    'href' => route('admin.dashboard'),
                    'section' => 'portal',
                    'primary' => true,
                    'icon' => 'dashboard',
                ],
                [
                    'key' => 'admin-cabang',
                    'label' => 'CRUD Cabang & Pengaturan',
                    'description' => 'Tambah, ubah jam operasional, radius, atau nonaktifkan cabang.',
                    'href' => route('admin.cabang.pengaturan', ['client' => $adminBranch]),
                    'section' => 'crud',
                    'primary' => false,
                    'icon' => 'branch',
                ],
                [
                    'key' => 'admin-obat',
                    'label' => 'CRUD Master Obat & Stok',
                    'description' => 'Kelola katalog obat master dan alokasi stok tiap cabang.',
                    'href' => route('admin.obat.index'),
                    'section' => 'crud',
                    'primary' => false,
                    'icon' => 'medicine',
                ],
                [
                    'key' => 'admin-staf',
                    'label' => 'CRUD Staf, Role & Hak Akses',
                    'description' => 'Buat akun, atur role cabang, status aktif, dan pengawas APJ (5NF).',
                    'href' => route('admin.cabang.staf', ['client' => $adminBranch]),
                    'section' => 'crud',
                    'primary' => false,
                    'icon' => 'users',
                ],
                [
                    'key' => 'admin-ekspor',
                    'label' => 'Ekspor Laporan & Tutup Periode',
                    'description' => 'Unduh laporan penjualan konsolidasi CSV/PDF lintas cabang.',
                    'href' => route('admin.laporan.ekspor'),
                    'section' => 'crud',
                    'primary' => false,
                    'icon' => 'report',
                ],
                [
                    'key' => 'admin-cek-apoteker',
                    'label' => 'Cek Operasional Apoteker',
                    'description' => 'Pantau APJ aktif, antrean resep, batch FEFO, dan loket cabang.',
                    'href' => route('admin.apoteker.index', ['client' => $adminBranch]),
                    'section' => 'audit',
                    'primary' => false,
                    'icon' => 'apoteker',
                ],
                [
                    'key' => 'admin-monitoring',
                    'label' => 'Monitoring Progres Obat',
                    'description' => 'Awasi status peracikan obat dan nomor antrean live.',
                    'href' => route('admin.progres.index'),
                    'section' => 'audit',
                    'primary' => false,
                    'icon' => 'activity',
                ],
                [
                    'key' => 'admin-ledger',
                    'label' => 'Audit Kasir & Ledger Cabang',
                    'description' => 'Periksa pelunasan tunai, QRIS, dan kasir verifikator.',
                    'href' => route('admin.kasir.ledger'),
                    'section' => 'audit',
                    'primary' => false,
                    'icon' => 'ledger',
                ],
                [
                    'key' => 'katalog-belanja',
                    'label' => 'Katalog & Belanja',
                    'description' => 'Lihat tampilan etalase dan katalog obat publik.',
                    'href' => route('dashboard'),
                    'section' => 'pelanggan',
                    'primary' => false,
                    'icon' => 'catalog',
                ],
            ];
        }

        if ($this->hasRole('apoteker') || in_array($this->role, ['apoteker', 'tenant'], true)) {
            return [
                [
                    'key' => 'apoteker-dashboard',
                    'label' => 'Dashboard Apoteker',
                    'description' => 'Portal utama telaah resep dan kendali apotek cabang.',
                    'href' => route('tenant.dashboard', ['client' => $assignedBranch]),
                    'section' => 'portal',
                    'primary' => true,
                    'icon' => 'apoteker',
                ],
                [
                    'key' => 'apoteker-resep',
                    'label' => 'Telaah Resep Dokter',
                    'description' => 'Verifikasi resep pasien, nomor SIP, dan persetujuan obat keras.',
                    'href' => route('tenant.resep.antrean', ['client' => $assignedBranch]),
                    'section' => 'portal',
                    'primary' => false,
                    'icon' => 'prescription',
                ],
                [
                    'key' => 'apoteker-stok',
                    'label' => 'Stok & Batch FEFO',
                    'description' => 'Kelola stok cabang, tanggal expired, dan karantina batch.',
                    'href' => route('tenant.stok.fefo', ['client' => $assignedBranch]),
                    'section' => 'portal',
                    'primary' => false,
                    'icon' => 'box',
                ],
                [
                    'key' => 'apoteker-antrean',
                    'label' => 'Antrean & Walk-In Cabang',
                    'description' => 'Panggil antrean loket dan ubah progres racikan obat.',
                    'href' => route('tenant.antrean.index', ['client' => $assignedBranch]),
                    'section' => 'portal',
                    'primary' => false,
                    'icon' => 'queue',
                ],
                [
                    'key' => 'apoteker-pasien',
                    'label' => 'Rekam Pasien Cabang',
                    'description' => 'Lihat profil serta riwayat pesanan pasien di cabang ini.',
                    'href' => route('tenant.pasien.index', ['client' => $assignedBranch]),
                    'section' => 'portal',
                    'primary' => false,
                    'icon' => 'users',
                ],
                [
                    'key' => 'apoteker-laporan',
                    'label' => 'Laporan Penjualan Cabang',
                    'description' => 'Rekap penjualan cabang dan tutup periode harian.',
                    'href' => route('tenant.laporan.index', ['client' => $assignedBranch]),
                    'section' => 'portal',
                    'primary' => false,
                    'icon' => 'report',
                ],
                [
                    'key' => 'katalog-belanja',
                    'label' => 'Katalog & Belanja',
                    'description' => 'Buka halaman etalase katalog obat utama.',
                    'href' => route('dashboard'),
                    'section' => 'pelanggan',
                    'primary' => false,
                    'icon' => 'catalog',
                ],
            ];
        }

        if ($this->hasRole('kasir') || in_array($this->role, ['admin', 'kasir', 'staf_klien'], true)) {
            return [
                [
                    'key' => 'kasir-dashboard',
                    'label' => 'Dashboard Kasir & POS Loket',
                    'description' => 'Catat penjualan walk-in, terima pembayaran, dan cetak struk.',
                    'href' => route('kasir.dashboard'),
                    'section' => 'portal',
                    'primary' => true,
                    'icon' => 'pos',
                ],
                [
                    'key' => 'kasir-ledger',
                    'label' => 'Buku Ledger Kasir',
                    'description' => 'Audit pembayaran lunas, menunggu, dan ringkasan harian cabang.',
                    'href' => route('kasir.ledger'),
                    'section' => 'portal',
                    'primary' => false,
                    'icon' => 'ledger',
                ],
                [
                    'key' => 'katalog-belanja',
                    'label' => 'Katalog & Belanja',
                    'description' => 'Buka halaman etalase katalog obat utama.',
                    'href' => route('dashboard'),
                    'section' => 'pelanggan',
                    'primary' => false,
                    'icon' => 'catalog',
                ],
            ];
        }

        $keranjangHref = Route::has('keranjang')
            ? route('keranjang')
            : route('customer.cart', ['client' => $adminBranch]);
        $riwayatHref = Route::has('pesanan.riwayat')
            ? route('pesanan.riwayat')
            : route('customer.order.history', ['client' => $adminBranch]);
        $resepHref = Route::has('resep.form')
            ? route('resep.form')
            : route('dashboard').'#unggah-resep';

        return [
            [
                'key' => 'pelanggan-katalog',
                'label' => 'Katalog & Dashboard',
                'description' => 'Jelajahi katalog obat, promo, dan pilihan cabang apotek.',
                'href' => route('dashboard'),
                'section' => 'pelanggan',
                'primary' => true,
                'icon' => 'catalog',
            ],
            [
                'key' => 'pelanggan-keranjang',
                'label' => 'Keranjang Belanja',
                'description' => 'Kelola obat di keranjang sebelum melanjutkan checkout.',
                'href' => $keranjangHref,
                'section' => 'pelanggan',
                'primary' => false,
                'icon' => 'cart',
            ],
            [
                'key' => 'pelanggan-riwayat',
                'label' => 'Riwayat Pesanan',
                'description' => 'Pantau status bayar QRIS, antrean racikan, dan invoice.',
                'href' => $riwayatHref,
                'section' => 'pelanggan',
                'primary' => false,
                'icon' => 'history',
            ],
            [
                'key' => 'pelanggan-resep',
                'label' => 'Unggah Resep Dokter',
                'description' => 'Kirim foto resep dokter untuk ditelaah oleh apoteker.',
                'href' => $resepHref,
                'section' => 'pelanggan',
                'primary' => false,
                'icon' => 'prescription',
            ],
        ];
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isTenantOperator(): bool
    {
        return $this->hasRole('tenant');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'aktif';
    }

    /**
     * Dapatkan slug client cabang yang diasosiasikan dengan pengguna (khusus apoteker/staf).
     */
    public function getClientSlug(): string
    {
        if ($this->cachedClientSlug !== null) {
            return $this->cachedClientSlug;
        }

        if ($this->email === 'dewi@omnipharma.test' || $this->email === 'apoteker.rogojampi@omnipharma.test' || str_contains($this->name, 'apt. Dinda Ayu Shafira')) {
            return $this->cachedClientSlug = 'rogojampi';
        }

        $normalizedRole = strtolower((string) $this->role);
        if (in_array($normalizedRole, ['pelanggan', 'customer'], true)) {
            return $this->cachedClientSlug = 'banyuwangi-kota';
        }

        try {
            $pengguna = Pengguna::where('telepon_email', $this->email)->first();
            if ($pengguna) {
                $admin = AdminClient::where('id_pengguna', $pengguna->id_pengguna)->with('client')->first();
                if ($admin && $admin->client && ! empty($admin->client->slug)) {
                    return $this->cachedClientSlug = $admin->client->slug;
                }
                $peran = PeranClientPengguna::where('id_pengguna', $pengguna->id_pengguna)->with('client')->first();
                if ($peran && $peran->client && ! empty($peran->client->slug)) {
                    return $this->cachedClientSlug = $peran->client->slug;
                }
            }
        } catch (\Throwable $e) {
            // fallback bila database belum termigrasi lengkap
        }

        return $this->cachedClientSlug = 'banyuwangi-kota';
    }

    /**
     * Dapatkan nama resmi cabang apotek pengguna.
     */
    public function getClientName(): string
    {
        return $this->getClientSlug() === 'rogojampi' ? 'Cabang Rogojampi' : 'Cabang Banyuwangi Kota';
    }
}
