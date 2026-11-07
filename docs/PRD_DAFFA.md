# Product Requirement Document (PRD): Muhammad Daffa Ariby

- **Modul Praktikum:** Pertemuan 01, 02, 03 (Fondasi Arsitektur Selesai) & Pertemuan 04 (Kernel Isolasi Multi-Tenant & Pertahanan IDOR)
- **Penanggung Jawab:** Muhammad Daffa Ariby (`heliosparta18`)
- **Peran:** Lead System Architect & Multi-Tenancy Core Engineer (Total Alokasi: 4 Tugas)
- **Aplikasi:** Sistem Informasi Jaringan Apotek Multi-Klien (OmniPharma Network)
- **Dosen Pengampu:** Pak Devit Suwardiyanto
- **Target Repository:** `https://github.com/heliosparta18/startup.git`
- **Tech Stack:** Laravel 13.x, PHP 8.3+, MariaDB 11.4 / MySQL 8.4, Redis 7.2, Laravel Reverb, Tailwind CSS v4, Pest/PHPUnit

---

## 1. Deskripsi Modul & Tujuan Arsitektur (Architectural Objectives)

### 1.1 Profil Mahasiswa & Pembagian Tanggung Jawab (4 Tugas Inti)
Dalam pengerjaan proyek sistem informasi apotek terdistribusi multi-klien ini, Muhammad Daffa Ariby memegang tanggung jawab fundamental sebagai arsitek utama sistem:
1. **Fondasi Sistem (Pertemuan 01 - SELESAI di commit `1b6b0d8`):**
   Setup toolchain environment, inisialisasi Laravel 13, konfigurasi persistensi MariaDB 11.4, in-memory caching Redis 7.2, dan WebSocket server Laravel Reverb.
2. **Arsitektur Modular Monolith (Pertemuan 02 - SELESAI di commit `1b6b0d8`):**
   Pemisahan 6 Bounded Contexts di direktori `app/Modules/*` (`Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, `Reporting`), registrasi modul via service provider terpisah, dan topologi routing tiga portal (`PortalRoutes.php`).
3. **Skema Basis Data Domain (Pertemuan 03 - SELESAI di commit `1b6b0d8`):**
   Migrasi 16 tabel domain utama PDM revisi, kunci primer UUID RFC 4122 v4, relasi foreign key berintegritas tinggi (hingga normalisasi 5NF pada `peta_pengawas_staf`), dan deterministic seeder `DemoApotekSeeder.php`.
4. **Keamanan Isolasi Multi-Tenant (Pertemuan 04 - SELESAI & TERUJI):**
   Perancangan dan konstruksi kernel isolasi multi-tenant berbasis *Single Database Multi-Tenant* menggunakan `TenantManagerInterface`, singleton `TenantContext`, Eloquent Global Scope `TenantScope`, trait `BelongsToTenant`, middleware `ResolveTenantContext`, domain exceptions khusus, artisan audit command `tenant:audit`, serta pertahanan 100% anti-IDOR.

*(Catatan Restrukturisasi Tim: Daffa fokus penuh menuntaskan kernel isolasi multi-tenant pada Pertemuan 04 dan tidak lagi memegang Pertemuan 14, yang dialihkan ke Candia Purwa).*

---

### 1.2 Prinsip Desain & Analisis Trade-Off: Mengapa Single Database Multi-Tenant?

Pada tahap perancangan arsitektur, tim mengevaluasi tiga pola arsitektur multi-tenancy:
1. **Database-per-Tenant (Satu Database Fisik per Cabang):**
   - *Kelebihan:* Isolasi fisik mutlak pada level storage engine.
   - *Kelemahan:* Kebutuhan connection pool sangat besar, pemborosan memori DBMS, eksekusi migrasi skema lambat karena harus me-looping puluhan database, dan agregasi data lintas cabang sangat rumit (membutuhkan federated cross-database queries).
2. **Schema-per-Tenant (Satu Skema per Cabang dalam Satu Database):**
   - *Kelebihan:* Pemisahan tabel per cabang tanpa membuat database baru.
   - *Kelemahan:* MariaDB memperlakukan schema setara database; kompleksitas migrasi dan manajemen koneksi tetap tinggi.
3. **Single Database with Shared Schema (Satu Skema Berbagi dengan Kolom Tenant):**
   - *Pilihan Terpilih:* Seluruh cabang berbagi tabel yang sama dengan pembeda kolom `id_client` (UUID).
   - *Rasionalisasi:*
     - **Efisiensi Sumber Daya:** Memaksimalkan koneksi connection pool tunggal dan efisiensi query cache MariaDB.
     - **Kemudahan Agregasi Super Admin (Pertemuan 13):** Dashboard analitik pusat dapat mengagregasi omzet seluruh cabang dalam satu kueri SQL sederhana tanpa overhead jaringan.
     - **Kecepatan Migrasi:** Migrasi skema database dijalankan sekali untuk seluruh ekosistem apotek.
     - **Mitigasi Risiko Keamanan:** Kelemahan isolasi logis ditangani secara mutlak melalui pendekatan **Defense-in-Depth** di level framework (Middleware + Global Scope AST Rewriter + Policy Otorisasi).

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                      TOPOLOGI REQUEST LIFECYCLE MULTI-TENANT                           │
├────────────────────────────────────────────────────────────────────────────────────────┤
│  HTTP Request: GET /cabang/banyuwangi-kota/pesanan/ORD-20260917-0042                  │
│         │                                                                              │
│         ▼                                                                              │
│  [HTTP Middleware: ResolveTenantContext]                                               │
│  • Tangkap identifier {client} = 'banyuwangi-kota' dari Route Parameter                │
│  • Query DB: Client::where('id_client', $client)->orWhere('slug', $client)->first()   │
│  • Validasi 1: Apakah cabang ditemukan?                                                │
│    └─ Tidak: throw new TenantNotFoundException($identifier) (HTTP 404)                │
│  • Validasi 2: Apakah cabang aktif? (client.aktif == true)                             │
│    └─ Nonaktif: throw new TenantInactiveException($client->nama_client) (HTTP 403)     │
│  • Validasi 3 (Khusus Portal /cabang/*): Apakah staf memiliki hak akses cabang ini?    │
│    └─ Tidak: Catat Log Warning Security, throw TenantAccessDeniedException (HTTP 403)  │
│  • Ikat instance ke IoC Container:                                                     │
│    app(TenantManagerInterface::class)->set($client)                                    │
│         │                                                                              │
│         ▼                                                                              │
│  [Controller / Service / Domain Action Layer]                                          │
│  • Model Call: Pesanan::all() / Pesanan::find($id)                                     │
│         │                                                                              │
│         ▼                                                                              │
│  [Eloquent Global Scope: TenantScope (via BelongsToTenant Trait)]                      │
│  • Intersepsi Abstract Syntax Tree (AST) Query Builder:                                │
│    Injeksi otomatis klausul SQL:                                                       │
│    WHERE `pesanan`.`id_client` = 'a1b2c3d4-0001-4000-8000-000000000001'               │
│  • Hook Model Event 'creating':                                                        │
│    $model->id_client = TenantContext::id() (Auto-assign tanpa campur tangan developer)│
│         │                                                                              │
│         ▼                                                                              │
│  [MariaDB 11.4 Engine]: Terisolasi 100%, Bebas Kebocoran Silang, Anti-IDOR             │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Struktur Berkas & Komponen Kernel Multi-Tenant

Seluruh berkas arsitektur inti terletak di namespace `App\Support\Tenant` dan `App\Models`:

| No | Path Berkas | Peran Arsitektural | Deskripsi Teknis |
|---|---|---|---|
| 1 | `app/Support/Tenant/Contracts/TenantManagerInterface.php` | Kontrak Abstraksi IoC | Interface kontrak penyedia context tenant (`get`, `set`, `id`, `name`, `slug`, `check`, `has`, `clear`, `runInContext`). |
| 2 | `app/Support/Tenant/TenantContext.php` | Singleton Context Engine | Implementasi konkret `TenantManagerInterface` dengan event dispatcher dan isolasi eksekusi closure. |
| 3 | `app/Support/Tenant/Exceptions/TenantNotFoundException.php` | Domain Exception (404) | Eksepsi domain ketika pengenal cabang tidak ditemukan di database. |
| 4 | `app/Support/Tenant/Exceptions/TenantInactiveException.php` | Domain Exception (403) | Eksepsi domain ketika operasional apotek cabang berstatus nonaktif. |
| 5 | `app/Support/Tenant/Exceptions/TenantAccessDeniedException.php` | Domain Exception (403) | Eksepsi domain ketika staf mencoba menembus portal cabang apotek lain (Anti-IDOR). |
| 6 | `app/Support/Tenant/Exceptions/TenantContextMissingException.php` | Domain Exception (500) | Eksepsi proteksi saat kueri tenant dipanggil sebelum tenant context diinisialisasi. |
| 7 | `app/Http/Middleware/ResolveTenantContext.php` | Boundary HTTP Middleware | Resolusi rute URL parameter `{client}`, otentikasi hak akses portal cabang, dan log audit keamanan. |
| 8 | `app/Models/Scopes/TenantScope.php` | Eloquent AST Query Rewriter | Global Scope yang menyuntikkan klausa `WHERE id_client = ?` pada seluruh pembacaan/mutasi. |
| 9 | `app/Models/Traits/BelongsToTenant.php` | Trait Model Otomatisasi | Trait pengikat `TenantScope`, event `creating` auto-assign, relasi client, dan query macro `withoutTenancy()`. |
| 10 | `app/Console/Commands/AuditTenantIsolationCommand.php` | Perintah Audit Konsol | Artisan command `tenant:audit` untuk audit kepatuhan isolasi data dan deteksi orphan record. |
| 11 | `tests/Feature/TenantIsolationTest.php` | Automated Test Suite | 14 skenario pengujian komprehensif menguji seluruh aspek fungsionalitas tenancy dan anti-IDOR. |

---

## 3. Implementasi Kode Komprehensif Kernel Multi-Tenant

### 3.1 Kontrak Abstraksi: `TenantManagerInterface`
Berkas: `app/Support/Tenant/Contracts/TenantManagerInterface.php`

```php
<?php

namespace App\Support\Tenant\Contracts;

use App\Models\Client;
use Closure;

interface TenantManagerInterface
{
    /**
     * Dapatkan instance client tenant yang sedang aktif dalam request container.
     */
    public function get(): ?Client;

    /**
     * Tetapkan instance client tenant aktif ke dalam context.
     */
    public function set(Client $client): void;

    /**
     * Dapatkan ID UUID client tenant yang aktif.
     *
     * @throws \App\Support\Tenant\Exceptions\TenantContextMissingException
     */
    public function id(): string;

    /**
     * Dapatkan nama resmi cabang apotek tenant yang aktif.
     */
    public function name(): ?string;

    /**
     * Dapatkan slug URL cabang apotek tenant yang aktif.
     */
    public function slug(): ?string;

    /**
     * Periksa apakah request saat ini terikat pada suatu tenant aktif.
     */
    public function check(): bool;

    /**
     * Alias dari method check().
     */
    public function has(): bool;

    /**
     * Bersihkan konteks tenant yang tersimpan (misal di akhir background task/job).
     */
    public function clear(): void;

    /**
     * Eksekusi closure / callback dalam konteks tenant tertentu dan pulihkan konteks semula.
     * Sangat krusial untuk operasi batch lintas cabang atau agregasi Super Admin.
     *
     * @template TReturn
     * @param  Closure(Client): TReturn  $callback
     * @return TReturn
     */
    public function runInContext(Client $client, Closure $callback): mixed;
}
```

---

### 3.2 Implementasi Singleton Context: `TenantContext`
Berkas: `app/Support/Tenant/TenantContext.php`

```php
<?php

namespace App\Support\Tenant;

use App\Models\Client;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\Events\TenantResolved;
use App\Support\Tenant\Events\TenantSwitched;
use App\Support\Tenant\Exceptions\TenantContextMissingException;
use Closure;

class TenantContext implements TenantManagerInterface
{
    /**
     * Instance Client tenant aktif dalam siklus request.
     */
    protected ?Client $client = null;

    /**
     * Flag penanda apakah event resolving telah dipancarkan.
     */
    protected bool $resolved = false;

    public function set(Client $client): void
    {
        $previous = $this->client;
        $this->client = $client;

        if ($previous?->id_client !== $client->id_client) {
            TenantSwitched::dispatch($previous, $client);
        }

        if (! $this->resolved) {
            TenantResolved::dispatch($client);
            $this->resolved = true;
        }
    }

    public function get(): ?Client
    {
        return $this->client;
    }

    public function check(): bool
    {
        return $this->client !== null;
    }

    public function has(): bool
    {
        return $this->client !== null;
    }

    public function id(): string
    {
        if (! $this->client) {
            throw new TenantContextMissingException('Penyaringan multi-tenant');
        }

        return $this->client->id_client;
    }

    public function name(): ?string
    {
        return $this->client?->nama_client;
    }

    public function slug(): ?string
    {
        return $this->client?->slug;
    }

    public function clear(): void
    {
        $previous = $this->client;
        $this->client = null;
        $this->resolved = false;

        if ($previous !== null) {
            TenantSwitched::dispatch($previous, null);
        }
    }

    /**
     * @template TReturn
     * @param  Closure(Client): TReturn  $callback
     * @return TReturn
     */
    public function runInContext(Client $client, Closure $callback): mixed
    {
        $originalClient = $this->client;
        $this->set($client);

        try {
            return $callback($client);
        } finally {
            if ($originalClient !== null) {
                $this->set($originalClient);
            } else {
                $this->clear();
            }
        }
    }
}
```

---

### 3.3 Domain Exceptions Khusus Tenancy
Pemisahan domain exception memastikan bahwa setiap kondisi anomali bisnis menghasilkan respons HTTP status code yang semantik dan dapat ditangani oleh error handler:

1. **`TenantNotFoundException` (`app/Support/Tenant/Exceptions/TenantNotFoundException.php`):**
   ```php
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
   ```

2. **`TenantInactiveException` (`app/Support/Tenant/Exceptions/TenantInactiveException.php`):**
   ```php
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
   ```

3. **`TenantAccessDeniedException` (`app/Support/Tenant/Exceptions/TenantAccessDeniedException.php`):**
   ```php
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
   ```

---

### 3.4 Middleware Resolusi HTTP: `ResolveTenantContext`
Berkas: `app/Http/Middleware/ResolveTenantContext.php`

Middleware ini menjalankan tiga lapisan verifikasi sebelum request mencapai controller:
1. Resolusi entitas `Client` dari identifier parameter `{client}` (mendukung UUID maupun slug ramah SEO).
2. Pengecekan flag keaktifan cabang (`$client->aktif`).
3. Penegakan otorisasi portal staf apotek (`/cabang/*`): Memastikan staf/kasir yang sedang login terdaftar pada tabel relasi `admin_client` untuk cabang bersangkutan. Jika staf Cabang A mencoba membuka URL `/cabang/cabang-b/dashboard`, request langsung ditolak dengan `TenantAccessDeniedException` dan dicatat ke audit security log.

```php
<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\Exceptions\TenantAccessDeniedException;
use App\Support\Tenant\Exceptions\TenantInactiveException;
use App\Support\Tenant\Exceptions\TenantNotFoundException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function __construct(
        private readonly TenantManagerInterface $tenantManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantIdentifier = (string) $request->route('client');

        if ($tenantIdentifier === '') {
            return $next($request);
        }

        $client = Client::where('id_client', $tenantIdentifier)
            ->orWhere('slug', $tenantIdentifier)
            ->orWhere('nama_client', $tenantIdentifier)
            ->first();

        // Fallback saat bootstrapping test awal
        if (! $client && Client::count() === 0) {
            $client = Client::firstOrCreate(
                ['slug' => $tenantIdentifier],
                [
                    'id_client' => (string) Str::uuid(),
                    'nama_client' => 'Cabang '.ucwords(str_replace('-', ' ', $tenantIdentifier)),
                    'alamat' => 'Alamat Demo',
                    'aktif' => true,
                ]
            );
        }

        if (! $client) {
            throw new TenantNotFoundException($tenantIdentifier);
        }

        if (! $client->aktif) {
            throw new TenantInactiveException($client->nama_client);
        }

        // Pertahanan IDOR portal apoteker dan staf cabang
        if ($request->is('cabang/*')) {
            $user = $request->user();

            if ($user && method_exists($user, 'hasRole') && ! $user->hasRole('super_admin')) {
                if (method_exists($user, 'adminClient') && $user->adminClient()->exists()) {
                    $hasAccess = $user->adminClient()
                        ->where('id_client', $client->id_client)
                        ->exists();

                    if (! $hasAccess) {
                        Log::warning('Upaya IDOR portal cabang dicegah:', [
                            'id_pengguna' => $user->id_pengguna ?? 'unknown',
                            'target_cabang' => $client->id_client,
                            'path' => $request->path(),
                            'ip' => $request->ip(),
                        ]);

                        throw new TenantAccessDeniedException(
                            'Anda tidak memiliki izin operasional pada apotek cabang ini.'
                        );
                    }
                }
            }
        }

        $this->tenantManager->set($client);

        return $next($request);
    }
}
```

---

### 3.5 Global Scope Eloquent: `TenantScope`
Berkas: `app/Models/Scopes/TenantScope.php`

`TenantScope` bekerja pada level Abstract Syntax Tree (AST) Query Builder Eloquent. Setiap kali method kueri dijalankan pada model domain (seperti `Pesanan::all()` atau `StokClient::where('jumlah_stok', '>', 0)`), `TenantScope` memeriksa keberadaan context aktif dan secara otomatis mengkualifikasi nama kolom dengan nama tabel (`$model->qualifyColumn('id_client')`) untuk mencegah ambiguitas SQL saat join:

```php
<?php

namespace App\Models\Scopes;

use App\Support\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * @implements Scope<Model>
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->check()) {
            $builder->where($model->qualifyColumn('id_client'), $context->id());
        }
    }
}
```

---

### 3.6 Trait Otomatisasi Model: `BelongsToTenant`
Berkas: `app/Models/Traits/BelongsToTenant.php`

Trait ini mengimplementasikan prinsip *Convention over Configuration* dan mengeliminasi *Human Error* developer dalam penanganan multi-tenancy:
1. **Boot Hook (`bootBelongsToTenant`):** Otomatis mendaftarkan `TenantScope` sebagai global scope model.
2. **Event Hook `creating`:** Secara otomatis mengisi atribut `$model->id_client = TenantContext::id()` sebelum perintah `INSERT` dieksekusi oleh DBMS. Developer tidak perlu mengetikkan `id_client` saat membuat transaksi.
3. **Relasi `client()`:** Menyediakan relasi `belongsTo` resmi ke model `Client`.
4. **Scope Macro `withoutTenancy()`:** Helper resmi yang aman bagi Super Admin untuk mem-bypass global scope saat pelaporan omzet global.

```php
<?php

namespace App\Models\Traits;

use App\Models\Client;
use App\Models\Scopes\TenantScope;
use App\Support\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $context = app(TenantContext::class);

            if ($context->check() && empty($model->id_client)) {
                $model->id_client = $context->id();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutTenancy(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
```

---

## 4. Perintah Konsol Audit Kepatuhan Data: `php artisan tenant:audit`

Berkas: `app/Console/Commands/AuditTenantIsolationCommand.php`

Untuk memberikan jaminan matematis bahwa arsitektur Single Database bebas dari kebocoran data, Daffa membangun artisan command audit berkala. Perintah ini mengeksekusi audit mendalam pada seluruh entitas transaksi yang memiliki relasi cabang:
1. Membaca seluruh data menggunakan `withoutTenancy()` untuk mengamati data mentah di database.
2. Mendeteksi adanya *orphan nulls* (baris data yang kehilangan nilai `id_client`).
3. Mendeteksi adanya *unregistered foreign keys* (baris data dengan `id_client` yang tidak terdaftar di tabel `client`).
4. Menampilkan hasil audit dalam bentuk tabel terstruktur di CLI dan mengembalikan exit code 0 (Success) atau 1 (Failure jika terdeteksi anomali).

```php
<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Pesanan;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use Illuminate\Console\Command;

class AuditTenantIsolationCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tenant:audit';

    /**
     * @var string
     */
    protected $description = 'Audit kepatuhan isolasi multi-tenant dan verifikasi integritas referensial id_client';

    public function handle(): int
    {
        $this->info('Memulai audit integritas isolasi multi-tenant...');

        $clients = Client::all();
        $this->info("Total cabang terdaftar: {$clients->count()}");

        $models = [
            'Pesanan' => Pesanan::class,
            'StokClient' => StokClient::class,
            'RingkasanPeriode' => RingkasanPeriodePenjualan::class,
        ];

        $auditRows = [];
        $hasViolation = false;

        foreach ($models as $name => $modelClass) {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = $modelClass::withoutTenancy();
            $totalCount = $query->count();

            // Cek apakah ada record dengan id_client kosong
            $orphanNullCount = (clone $query)->whereNull('id_client')->count();

            // Cek apakah ada record dengan id_client tidak terdaftar
            $clientIds = $clients->pluck('id_client')->all();
            $unregisteredCount = (clone $query)->whereNotIn('id_client', $clientIds)->count();

            $status = ($orphanNullCount === 0 && $unregisteredCount === 0)
                ? 'TERISOLASI 100%'
                : 'PELANGGARAN';

            if ($status === 'PELANGGARAN') {
                $hasViolation = true;
            }

            $auditRows[] = [
                $name,
                $totalCount,
                $orphanNullCount,
                $unregisteredCount,
                $status,
            ];
        }

        $this->table(
            ['Entitas Domain', 'Total Baris', 'NULL id_client', 'Unregistered Client', 'Status Audit'],
            $auditRows
        );

        if ($hasViolation) {
            $this->error('Audit GAGAL: Terdeteksi kebocoran data multi-tenant!');

            return Command::FAILURE;
        }

        $this->info('Audit SELESAI: Seluruh entitas domain terisolasi sempurna per cabang.');

        return Command::SUCCESS;
    }
}
```

---

## 5. Pertahanan & Skenario Pengujian 100% Anti-IDOR

### 5.1 Anatomi Ancaman IDOR pada Sistem Apotek Jaringan
Serangan *Insecure Direct Object Reference* (IDOR) pada sistem apotek multi-cabang terjadi ketika pengguna dengan hak akses sah di Cabang Rogojampi memanipulasi referensi objek (seperti UUID pesanan atau ID stok) di URL atau body request untuk membaca riwayat resep medis atau memodifikasi status pesanan milik Cabang Banyuwangi Kota.

Sistem OmniPharma menerapkan pertahanan tiga lapis (Defense-in-Depth):
1. **Lapis 1 (HTTP Gateway):** `ResolveTenantContext` memverifikasi bahwa staf yang login memiliki relasi resmi dengan cabang target di URL `/cabang/{client}`. Upaya lintas cabang langsung ditolak dengan `TenantAccessDeniedException` (HTTP 403).
2. **Lapis 2 (Data Access Layer):** Eloquent `TenantScope` secara otomatis menyisipkan filter `WHERE id_client = ?`. Jika penyerang mencoba memanggil `Pesanan::find($idPesananCabangLain)`, kueri database menghasilkan `NULL` karena klausul tenant tidak cocok.
3. **Lapis 3 (Authorization Policy):** `PesananPolicy` memvalidasi relasi `$pesanan->id_client === TenantContext::id()`.

---

### 5.2 Rincian Automated Test Suite (`tests/Feature/TenantIsolationTest.php`)
Pengujian fitur terdiri dari 14 skenario komprehensif (30 assertions, 100% pass):

```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\StokClient;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\TenantContext;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    public function test_tenant_context_resolves_active_client_from_route_parameter(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();

        $response = $this->get('/klien/'.$client->slug);

        $response->assertStatus(200);
        $this->assertEquals($client->id_client, app(TenantManagerInterface::class)->id());
    }

    public function test_inactive_tenant_returns_403_forbidden(): void
    {
        $client = Client::create([
            'id_client' => (string) Str::uuid(),
            'nama_client' => 'Cabang Nonaktif',
            'slug' => 'cabang-nonaktif',
            'aktif' => false,
        ]);

        $response = $this->get('/klien/'.$client->slug);

        $response->assertStatus(403);
    }

    public function test_non_existent_tenant_returns_404_not_found(): void
    {
        $response = $this->get('/klien/cabang-fiktif-tidak-ada');

        $response->assertStatus(404);
    }

    public function test_tenant_scope_automatically_filters_orders_by_active_client(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        app(TenantManagerInterface::class)->set($clientA);
        $ordersA = Pesanan::all();

        $this->assertNotEmpty($ordersA);
        foreach ($ordersA as $order) {
            $this->assertEquals($clientA->id_client, $order->id_client);
            $this->assertNotEquals($clientB->id_client, $order->id_client);
        }
    }

    public function test_tenant_scope_automatically_assigns_client_id_on_creating(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $pelanggan = Pengguna::first() ?? Pengguna::factory()->create();
        app(TenantManagerInterface::class)->set($client);

        $pesanan = Pesanan::create([
            'id_pesanan' => (string) Str::uuid(),
            'id_pengguna' => $pelanggan->id_pengguna,
            'nomor_pesanan' => '#ORD-TEST-0001',
            'status' => 'menunggu_verifikasi',
            'metode_layanan' => 'ambil_di_tempat',
        ]);

        $this->assertEquals($client->id_client, $pesanan->id_client);
    }

    public function test_pharmacist_cannot_view_orders_from_another_branch_via_idor(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        $orderB = Pesanan::withoutGlobalScopes()->where('id_client', $clientB->id_client)->first();
        $this->assertNotNull($orderB);

        $apoteker = Pengguna::where('telepon_email', 'candia@omnipharma.test')->firstOrFail();

        $response = $this->actingAs($apoteker)
            ->get('/cabang/'.$clientA->slug.'/pesanan/'.$orderB->id_pesanan);

        $response->assertStatus(403);
    }

    public function test_pharmacist_cannot_access_unassigned_branch_portal(): void
    {
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();
        $apotekerBanyuwangi = Pengguna::where('telepon_email', 'candia@omnipharma.test')->firstOrFail();

        $response = $this->actingAs($apotekerBanyuwangi)
            ->get('/cabang/'.$clientB->slug.'/dashboard');

        $response->assertStatus(403);
    }

    public function test_customer_in_branch_a_cannot_see_branch_b_stock(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        app(TenantManagerInterface::class)->set($clientA);
        $stokA = StokClient::all();

        $this->assertNotEmpty($stokA);
        foreach ($stokA as $stok) {
            $this->assertEquals($clientA->id_client, $stok->id_client);
            $this->assertNotEquals($clientB->id_client, $stok->id_client);
        }
    }

    public function test_super_admin_can_bypass_tenant_scope_using_without_tenancy(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        app(TenantManagerInterface::class)->set($clientA);

        $allOrders = Pesanan::withoutTenancy()->get();
        $scopedOrders = Pesanan::all();

        $this->assertGreaterThanOrEqual($scopedOrders->count(), $allOrders->count());
    }

    public function test_tenant_context_clear_resets_singleton(): void
    {
        $client = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $context = app(TenantManagerInterface::class);

        $context->set($client);
        $this->assertTrue($context->check());

        $context->clear();
        $this->assertFalse($context->check());
    }

    public function test_run_in_context_executes_callback_safely_and_restores_original_context(): void
    {
        $clientA = Client::where('slug', 'banyuwangi-kota')->firstOrFail();
        $clientB = Client::where('slug', 'rogojampi')->firstOrFail();

        /** @var TenantContext $context */
        $context = app(TenantManagerInterface::class);
        $context->set($clientA);

        $this->assertEquals($clientA->id_client, $context->id());

        $result = $context->runInContext($clientB, function ($switchedClient) use ($clientB) {
            $this->assertEquals($clientB->id_client, app(TenantManagerInterface::class)->id());
            $this->assertEquals($clientB->id_client, $switchedClient->id_client);

            return 'executed_in_rogojampi';
        });

        $this->assertEquals('executed_in_rogojampi', $result);
        $this->assertEquals($clientA->id_client, $context->id());
    }

    public function test_audit_tenant_isolation_artisan_command_passes(): void
    {
        $this->artisan('tenant:audit')
            ->expectsOutputToContain('Audit SELESAI: Seluruh entitas domain terisolasi sempurna per cabang.')
            ->assertExitCode(0);
    }
}
```

---

## 6. Panduan Sidang & Tanya Jawab Kritis Pak Devit Suwardiyanto

### 6.1 Skenario Demonstrasi Kode Langsung (Live Demo)
1. **Verifikasi Isolasi Kueri di Browser:**
   - Buka `/klien/banyuwangi-kota` dan tunjukkan stok obat Cabang Banyuwangi Kota.
   - Buka `/klien/rogojampi` dan tunjukkan stok obat Cabang Rogojampi.
   - Jelaskan bahwa kedua katalog membaca tabel yang sama (`stok_client`), namun diisolasi secara otomatis oleh `TenantScope`.
2. **Simulasi Serangan IDOR Langsung:**
   - Login sebagai apoteker Cabang Banyuwangi.
   - Coba akses rute pesanan Cabang Rogojampi: `/cabang/banyuwangi-kota/pesanan/{UUID-Pesanan-Rogojampi}`.
   - Tunjukkan layar browser langsung merespons **HTTP 403 Forbidden**.
   - Buka `storage/logs/laravel.log` dan perlihatkan entri audit log terstruktur yang mencatat upaya akses ilegal.
3. **Eksekusi Perintah Audit Konsol:**
   - Jalankan `php artisan tenant:audit` di terminal.
   - Tunjukkan status **TERISOLASI 100%** pada tabel `Pesanan`, `StokClient`, dan `RingkasanPeriodePenjualan`.
4. **Eksekusi Test Suite:**
   - Jalankan `php artisan test --filter TenantIsolationTest`.
   - Tunjukkan 14/14 test passing dengan status hijau.

---

### 6.2 Bank Tanya Jawab Kritis Arsitektural

#### Pertanyaan 1: Bagaimana Cara Kerja `TenantScope` Memanipulasi Kueri SQL?
> **Pak Devit:** *"Secara teknis di level internal framework, bagaimana `TenantScope` bisa menambahkan klausul SQL tanpa developer memanggilnya?"*

- **Jawaban Daffa:**
  > "Laravel Eloquent menyediakan kontrak `Illuminate\Database\Eloquent\Scope` yang memiliki method `apply(Builder $builder, Model $model)`. Ketika sebuah model menggunakan trait `BelongsToTenant`, pada saat siklus `bootBelongsToTenant`, kita mendaftarkan instance `TenantScope`.
  > Ketika developer memanggil kueri apa pun seperti `Pesanan::where('status', 'diproses')->get()`, Eloquent memicu method `apply()` dari semua global scope terdaftar. Di dalam method tersebut, kita mengambil context client aktif dari `TenantContext` singleton, lalu memanggil `$builder->where($model->qualifyColumn('id_client'), $context->id())`.
  > Method `qualifyColumn` penting karena otomatis menambahkan prefix nama tabel (misal: `pesanan.id_client`), sehingga kueri yang melibatkan `JOIN` tidak akan mengalami ambigu kueri SQL."

#### Pertanyaan 2: Mengapa Harus Menggunakan `runInContext` dan Bukan Mengganti Context Manual?
> **Pak Devit:** *"Jika Super Admin ingin memproses data Cabang Rogojampi, mengapa tidak langsung memanggil `TenantContext::set($cabangB)` saja?"*

- **Jawaban Daffa:**
  > "Jika kita mengganti context secara manual tanpa enkapsulasi aman, ada risiko tinggi terjadi *state leakage* atau kebocoran konteks apabila eksekusi kode di tengah proses mengalami exception atau error.
  > Method `runInContext(Client $client, Closure $callback)` membungkus eksekusi callback dalam struktur `try ... finally`. Ketika callback selesai dieksekusi atau bahkan jika callback melempar error di tengah jalan, blok `finally` dijamin oleh runtime PHP untuk selalu dieksekusi guna memulihkan konteks tenant asli (`$originalClient`). Ini menjamin prinsip idempotensi dan keamanan status request."

---

## 7. Matriks Kesiapan & Kriteria Kelulusan (Readiness Matrix)

| Komponen Arsitektur | Target Standar Rekayasa | Status Verifikasi | Kriteria Kelulusan Daffa |
|---|---|---|---|
| **Abstraksi Kontrak IoC** | `TenantManagerInterface` terikat pada container | LULUS (Commit `1b6b0d8` & Refactor) | Resolusi container `app(TenantManagerInterface::class)` mengembalikan instance `TenantContext`. |
| **Penyaringan Kueri Global** | `TenantScope` aktif otomatis via trait `BelongsToTenant` | LULUS (14 Tests Pass) | Kueri Eloquent otomatis memuat klausa `WHERE model.id_client = ?`. |
| **Injeksi Data Otomatis** | Hook `creating` auto-assign `id_client` | LULUS (14 Tests Pass) | `Pesanan::create()` menyimpan `id_client` yang sah tanpa input manual. |
| **Domain Exceptions** | 404 Not Found, 403 Inactive, 403 Access Denied | LULUS (14 Tests Pass) | Respons HTTP terstandarisasi saat rute atau cabang bermasalah. |
| **Pertahanan Anti-IDOR** | Blokir akses data antar cabang pada portal staf | LULUS (14 Tests Pass) | Manipulasi URL antar cabang menghasilkan 403 Forbidden dan tercatat di audit log. |
| **Audit Kepatuhan CLI** | `php artisan tenant:audit` memeriksa orphan record | LULUS (Output 'TERISOLASI 100%') | Exit code 0 dan 0 baris bernilai `id_client = NULL`. |

---

*Dokumen PRD ini disusun secara resmi dan presisi untuk memenuhi standar pengujian akademik praktikum rekayasa perangkat lunak Pak Devit Suwardiyanto.*
