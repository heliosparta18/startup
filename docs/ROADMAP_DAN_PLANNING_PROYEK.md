# Roadmap Arsitektur & Perencanaan Rekayasa Sistem: Muhammad Daffa Ariby

- **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi
- **Dosen Pengampu:** Pak Devit Suwardiyanto
- **Mahasiswa / Arsitek:** Muhammad Daffa Ariby (`heliosparta18`)
- **Peran:** Lead System Architect & Multi-Tenancy Core Engineer
- **Alokasi Tanggung Jawab:** Eksklusif 4 Tugas Inti (Pertemuan 01, 02, 03, dan 04)
- **Basis Teknologi:** Laravel 13.x, PHP 8.3+, MariaDB 11.4 / MySQL 8.4, Redis 7.2, Laravel Reverb, Tailwind CSS v4
- **Repository:** `https://github.com/heliosparta18/startup.git`
- **Fokus Domain:** Pondasi Arsitektur Modular Monolith, Topologi Routing Tiga Portal, Skema Basis Data Relasional UUID 5NF, dan Kernel Isolasi Multi-Tenant Anti-IDOR

---

## 1. Ringkasan Eksekutif & Batasan Ruang Lingkup Arsitektur

Dokumen perencanaan ini berfokus eksklusif pada lingkup tanggung jawab rekayasa perangkat lunak yang diemban oleh Muhammad Daffa Ariby. Sesuai penugasan mata kuliah praktikum bersama Pak Devit Suwardiyanto, Daffa bertanggung jawab merancang, mengonstruksi, dan memverifikasi pondasi arsitektur sistem informasi apotek jaringan multi-klien (OmniPharma Network).

Beban kerja teknis Daffa mencakup 4 milestone fundamental:
1. **Pertemuan 01: Setup Environment, Toolchain, & Verifikasi Runtime**
   Inisialisasi stack enterprise (Laravel 13, MariaDB 11.4, Redis 7.2, Laravel Reverb), konfigurasi koneksi persistensi dan caching, serta penyusunan baseline pengujian sistem.
2. **Pertemuan 02: Arsitektur Modular Monolith & Topologi Tiga Portal**
   Pemisahan domain menjadi 6 Bounded Contexts modular (`Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, `Reporting`), registrasi decoupled service providers, dan pengaturan topologi routing tiga portal (`/admin`, `/cabang/{client}`, `/klien/{client}`).
3. **Pertemuan 03: Basis Data Domain Relasional UUID & Seeder Deterministik**
   Konstruksi 16 tabel domain utama PDM revisi dengan kunci primer UUID RFC 4122 v4, relasi foreign key berintegritas tinggi (hingga normalisasi 5NF untuk pengawasan kefarmasian), seeder data deterministik multi-cabang, serta automated database test suite.
4. **Pertemuan 04: Kernel Isolasi Multi-Tenant & Pertahanan IDOR 100%**
   Konstruksi kernel multi-tenant berbasis Single Database menggunakan pola IoC Container (`TenantManagerInterface`, `TenantContext`), Global Eloquent Scope (`TenantScope`), Trait `BelongsToTenant`, Middleware HTTP `ResolveTenantContext`, domain exceptions, artisan audit command `tenant:audit`, dan penegakan otorisasi anti-IDOR (*Insecure Direct Object Reference*).

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│               ARSITEKTUR MULTI-TENANT SINGLE DATABASE OMNIPHARMA                       │
├────────────────────────────────────────────────────────────────────────────────────────┤
│  HTTP Request: GET /cabang/banyuwangi-kota/pesanan/ORD-20260917-0042                  │
│        │                                                                               │
│        ▼                                                                               │
│  [Middleware: ResolveTenantContext]                                                    │
│  • Tangkap route param {client} = 'banyuwangi-kota'                                    │
│  • Validasi keberadaan entitas Client di MariaDB 11.4                                  │
│    └─ Gagal: Lempar TenantNotFoundException (HTTP 404)                                 │
│  • Validasi status keaktifan cabang (client.aktif == true)                             │
│    └─ Nonaktif: Lempar TenantInactiveException (HTTP 403)                              │
│  • Validasi hak otorisasi staf cabang pada portal /cabang/*                            │
│    └─ Bukan staf cabang: Lempar TenantAccessDeniedException (HTTP 403)                 │
│  • Ikat instance ke IoC Container via TenantManagerInterface:                          │
│    app(TenantManagerInterface::class)->set($client)                                    │
│        │                                                                               │
│        ▼                                                                               │
│  [Controller / Domain Action Layer]                                                    │
│  • Pemanggilan Eloquent Model: Pesanan::find($idPesanan)                               │
│        │                                                                               │
│        ▼                                                                               │
│  [Eloquent Global Scope: TenantScope (via BelongsToTenant Trait)]                      │
│  • Manipulasi Abstract Syntax Tree (AST) SQL Builder secara otomatis:                  │
│    SELECT * FROM `pesanan`                                                             │
│    WHERE `pesanan`.`id_pesanan` = '...'                                                │
│      AND `pesanan`.`id_client` = 'a1b2c3d4-0001-4000-8000-000000000001'               │
│  • Pada saat creating / insert:                                                        │
│    $pesanan->id_client otomatis diisi dari TenantContext::id() tanpa input manual     │
│        │                                                                               │
│        ▼                                                                               │
│  [Basis Data MariaDB 11.4]: Zero Cross-Tenant Data Leakage, 100% Anti-IDOR            │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian Teknis Mendalam Modul Pertemuan (P01 s/d P04)

### 2.1 Pertemuan 01: Setup Environment, Toolchain, & Verification Harness
- **Status:** SELESAI (Tervalidasi di Commit `1b6b0d8`)
- **Ruang Lingkup & Fokus Rekayasa:**
  * Penyediaan runtime development PHP 8.3 dengan ekstensi wajib: `pdo_mysql`, `redis`, `bcmath`, `mbstring`, `pcntl`.
  * Konfigurasi koneksi ganda pada database MariaDB 11.4: database aplikasi utama `startup_db` dan database pengujian `startup_db_test`.
  * Konfigurasi Redis 7.2 untuk cache store dan queue driver, menjamin latensi rendah untuk operasi asinkron transaksi.
  * Inisialisasi service broadcasting Laravel Reverb pada port 8080 dengan transport WebSocket native.
  * Standarisasi linting dan static analysis menggunakan Laravel Pint (PSR-12 standard) dan PHPStan level 5.
- **Artefak Bukti Deliverable:**
  * Direktori verifikasi: `evidence/pertemuan-01/`
  * Berkas konfigurasi: `.env.example`, `config/database.php`, `config/reverb.php`, `config/cache.php`.
  * Status eksekusi test: Seluruh koneksi database dan cache terverifikasi melalui automated test suite fondasi.

### 2.2 Pertemuan 02: Arsitektur Modular Monolith & Topologi Tiga Portal
- **Status:** SELESAI (Tervalidasi di Commit `1b6b0d8`)
- **Ruang Lingkup & Fokus Rekayasa:**
  * Dekomposisi arsitektur menjadi pola Modular Monolith dengan membagi logika bisnis apotek ke dalam 6 Bounded Contexts independen di `app/Modules/`:
    1. `Admin`: Manajemen staf, peran pengguna, dan konfigurasi platform.
    2. `Catalog`: Master obat, sediaan farmasi, kategori, dan ketersediaan stok fisik per cabang.
    3. `Ordering`: Keranjang belanja, checkout transaksi atomik, dan pelacakan status pesanan.
    4. `Payments`: Integrasi gateway pembayaran, pembuatan QRIS dinamis, dan penanganan webhook settlement.
    5. `Prescription`: Alur unggah resep dokter, telaah administratif SIP apoteker, dan validasi klinis.
    6. `Reporting`: Agregasi omzet, penutupan periode akuntansi kas, dan ekspor laporan keuangan.
  * Registrasi dinamis modul melalui `app/Modules/ModuleServiceProvider.php`.
  * Desain topologi routing tiga portal melalui `app/Support/Routing/PortalRoutes.php`:
    1. **Portal Super Admin (`/admin/*`):** Manajemen lintas cabang dan rekapitulasi data global.
    2. **Portal Operasional Cabang (`/cabang/{client}/*`):** Akses kasir, apoteker, dan staf inventori terikat cabang.
    3. **Portal Pasien / Pelanggan (`/klien/{client}/*`):** Akses katalog obat, keranjang belanja, dan status order.
  * Pemisahan layout blade terpisah untuk menjamin modularitas tampilan dan isolasi stylesheet.
- **Artefak Bukti Deliverable:**
  * Direktori verifikasi: `evidence/pertemuan-02/`
  * Berkas inti: `app/Modules/ModuleServiceProvider.php`, `app/Support/Routing/PortalRoutes.php`, `resources/views/layouts/`.

### 2.3 Pertemuan 03: Basis Data Domain Relasional UUID & Seeder Deterministik
- **Status:** SELESAI (Tervalidasi di Commit `1b6b0d8`)
- **Ruang Lingkup & Fokus Rekayasa:**
  * Migrasi skema database 16 tabel domain utama PDM revisi:
    - Entitas Master: `client`, `pengguna`, `kategori_obat`, `obat`.
    - Entitas Inventori & Cabang: `stok_client`, `admin_client`, `peran_client_pengguna`.
    - Entitas Regulasi Kefarmasian: `peta_pengawas_staf` (relasi pengawasan Apoteker Penanggung Jawab / APJ terhadap asisten apoteker berderajat 5NF sesuai Permenkes 73/2016).
    - Entitas Transaksi: `pesanan`, `item_pesanan`, `resep`, `pembayaran`.
    - Entitas Finansial & Audit: `ringkasan_periode_penjualan`, `permintaan_retur`, `log_audit`.
  * Standarisasi Primary Key: Menggunakan UUID RFC 4122 v4 bertipe `char(36)` di seluruh tabel. Ini mencegah enumerasi ID berurutan (*predictable sequential auto-increment*) dan mempersiapkan arsitektur untuk replikasi basis data multi-node di masa depan.
  * Penegakan Foreign Key & Constraint: Relasi `ON DELETE RESTRICT` pada tabel finansial dan audit untuk mencegah hilangnya jejak transaksi akuntansi.
  * Seeder Deterministik `database/seeders/DemoApotekSeeder.php`:
    - Mengisi data master obat dengan sediaan lengkap (tablet, sirup, salep, kapsul) beserta penggolongan obat (bebas, bebas terbatas, keras).
    - Menyediakan 2 cabang apotek nyata: Cabang Banyuwangi Kota (`slug: banyuwangi-kota`) dan Cabang Rogojampi (`slug: rogojampi`).
    - Menyediakan akun demo terstruktur: Super Admin, Staf Cabang A, Staf Cabang B, Apoteker APJ, dan Pasien Pengguna.
- **Artefak Bukti Deliverable:**
  * Direktori verifikasi: `evidence/pertemuan-03/`
  * Berkas migrasi di `database/migrations/*`
  * Berkas seeder: `database/seeders/DemoApotekSeeder.php`
  * Berkas pengujian: `tests/Feature/ApotekFoundationTest.php`, `tests/Feature/ApotekDatabaseConstraintTest.php`.

### 2.4 Pertemuan 04: Kernel Isolasi Multi-Tenant & Pertahanan IDOR 100%
- **Status:** SELESAI & LULUS VERIFIKASI PENUH (14 Automated Tests Passing)
- **Ruang Lingkup & Fokus Rekayasa:**
  * Konstruksi abstraksi arsitektur multi-tenancy:
    1. **`TenantManagerInterface` (`app/Support/Tenant/Contracts/TenantManagerInterface.php`):**
       Kontrak IoC untuk manajemen tenant yang menyediakan method: `set()`, `get()`, `id()`, `name()`, `slug()`, `check()`, `has()`, `clear()`, dan `runInContext()`.
    2. **`TenantContext` (`app/Support/Tenant/TenantContext.php`):**
       Implementasi konkret singleton scoped per-request. Dilengkapi mekanisme event dispatching (`TenantResolved`, `TenantSwitched`) dan isolation wrapper `runInContext(\Closure $callback)` yang menjamin konteks tenant asli dipulihkan kembali melalui blok `finally`.
    3. **Domain Exceptions Khusus:**
       - `TenantNotFoundException`: Melempar HTTP 404 ketika slug/UUID cabang tidak terdaftar di sistem.
       - `TenantInactiveException`: Melempar HTTP 403 ketika cabang terdaftar namun berstatus `aktif = false`.
       - `TenantAccessDeniedException`: Melempar HTTP 403 ketika staf mencoba mengakses portal cabang yang bukan hak wewenangnya (pencegahan IDOR portal).
       - `TenantContextMissingException`: Menolak operasi data berelasi tenant jika konteks tenant belum diinisialisasi.
    4. **Middleware `ResolveTenantContext` (`app/Http/Middleware/ResolveTenantContext.php`):**
       Mencegat request masuk, mengekstraksi parameter `{client}` dari URL, melakukan resolusi ke database MariaDB, memvalidasi status keaktifan, memvalidasi hak akses staf jika mengakses rute `/cabang/*`, dan mengikat model `Client` ke `TenantManagerInterface`. Dilengkapi logging audit peringatan keamanan jika terdeteksi indikasi serangan IDOR.
    5. **Eloquent Global Scope `TenantScope` (`app/Models/Scopes/TenantScope.php`):**
       Secara otomatis menyuntikkan klausa `WHERE model.id_client = TenantContext::id()` ke dalam setiap kueri `SELECT`, `UPDATE`, dan `DELETE` pada model domain yang mengimplementasikan multi-tenancy.
    6. **Trait `BelongsToTenant` (`app/Models/Traits/BelongsToTenant.php`):**
       Mengikat `TenantScope` secara otomatis ke model dan menangani Eloquent hook `creating` untuk menginjeksi atribut `id_client` dari konteks aktif tanpa campur tangan manual developer. Menyediakan helper query `withoutTenancy()` untuk kebutuhan bypass resmi Super Admin.
    7. **Artisan Audit Command `tenant:audit` (`app/Console/Commands/AuditTenantIsolationCommand.php`):**
       Perintah konsol CLI untuk melakukan audit kepatuhan isolasi data multi-tenant secara komprehensif. Memeriksa ketiadaan baris data bernilai `id_client = NULL` (orphan nulls) dan memverifikasi integritas referensial seluruh record terhadap tabel cabang terdaftar.
    8. **Test Suite Komprehensif `tests/Feature/TenantIsolationTest.php`:**
       14 skenario pengujian unit dan fitur yang membuktikan 100% kekebalan terhadap serangan IDOR, isolasi kueri antar-cabang, penanganan cabang nonaktif, eksekusi command audit, dan isolasi konteks eksekusi callback.
- **Artefak Bukti Deliverable:**
  * Berkas kernel:
    - `app/Support/Tenant/Contracts/TenantManagerInterface.php`
    - `app/Support/Tenant/TenantContext.php`
    - `app/Support/Tenant/Exceptions/TenantNotFoundException.php`
    - `app/Support/Tenant/Exceptions/TenantInactiveException.php`
    - `app/Support/Tenant/Exceptions/TenantAccessDeniedException.php`
    - `app/Support/Tenant/Exceptions/TenantContextMissingException.php`
    - `app/Http/Middleware/ResolveTenantContext.php`
    - `app/Models/Scopes/TenantScope.php`
    - `app/Models/Traits/BelongsToTenant.php`
    - `app/Console/Commands/AuditTenantIsolationCommand.php`
  * Berkas pengujian: `tests/Feature/TenantIsolationTest.php` (Lulus 14/14 tests, 30 assertions).

---

## 3. Matriks Milestone, Deliverables, & Kriteria Kelulusan (Exit Criteria)

| Milestone / Pertemuan | Deliverables Utama | Indikator Keberhasilan Teknis | Kriteria Kelulusan Pak Devit |
|---|---|---|---|
| **Pertemuan 01: Setup Environment & Toolchain** | Runtime PHP 8.3, MariaDB 11.4, Redis 7.2, Reverb, `.env`, Test Suite Fondasi | Koneksi database, cache Redis, dan artisan CLI berjalan lancar tanpa error environment. | Lulus pengujian koneksi database dan cache via automated tests. |
| **Pertemuan 02: Modular Monolith & Routing Topology** | 6 Bounded Contexts di `app/Modules/*`, `ModuleServiceProvider.php`, `PortalRoutes.php`, 3 Layout Portal | Struktur folder terisolasi, service provider terdaftar, pemisahan rute admin, staf, dan pelanggan berjalan presisi. | Routing ketiga portal (`/admin`, `/cabang/{client}`, `/klien/{client}`) merespons kode HTTP yang valid. |
| **Pertemuan 03: Basis Data Domain UUID & Seeder** | 16 Tabel domain PDM revisi, kunci primer UUID v4, seeder 2 cabang, relasi 5NF `peta_pengawas_staf` | Skema database terstruktur rapi dengan integritas foreign key, migrasi lancar, dan seeder deterministik siap pakai. | Eksekusi `php artisan migrate:fresh --seed` berhasil tanpa kegagalan constraint. |
| **Pertemuan 04: Kernel Isolasi Multi-Tenant & Anti-IDOR** | `TenantManagerInterface`, `TenantContext`, `TenantScope`, `BelongsToTenant`, `ResolveTenantContext`, `tenant:audit` | Kueri otomatis terisolasi `WHERE id_client = ?`, manipulasi URL antar-cabang menghasilkan 403 Forbidden, 0 orphan records. | Eksekusi `php artisan tenant:audit` berstatus 'TERISOLASI 100%' dan `TenantIsolationTest` lulus 14/14. |

---

## 4. Standar Operasional Prosedur (SOP) Git & Version Control

Untuk memastikan rekam jejak kerja Muhammad Daffa Ariby kredibel dan dapat dipertanggungjawabkan dalam audit git repositori, seluruh proses pengembangan mematuhi aturan berikut:

### 4.1 Tata Cara Penamaan Branch
1. **Pola Fitur Mahasiswa:** `feat/daffa-tenant-context`
2. **Pola Kurikulum Praktikum:** `feature/pertemuan-04-multi-tenant`

### 4.2 Standar Pesan Commit (Commit Message Guidelines)
- Pesan commit wajib ditulis dalam bahasa Indonesia teknis yang ringkas atau format Conventional Commits.
- Menjelaskan esensi perubahan logika secara jujur dan faktual.
- Dilarang keras menggunakan kalimat promosi AI atau jargon yang tidak merefleksikan perubahan nyata pada file.

Contoh pesan commit resmi Daffa:
- `buat tenant context dan kontrak tenant manager interface`
- `tambah exception domain khusus penanganan status tenant`
- `implementasi middleware resolusi tenant dan proteksi idor`
- `pasang tenant scope global dan trait belongs to tenant`
- `buat artisan command tenant audit untuk verifikasi kebocoran data`
- `tambah test suite isolasi tenant dan pembuktian blokir idor`

---

## 5. Panduan Sidang & Demonstrasi Kode di Hadapan Pak Devit Suwardiyanto

Pak Devit Suwardiyanto menguji penguasaan arsitektur perangkat lunak dengan pendekatan berbasis kode nyata, integritas basis data, dan pertahanan terhadap celah keamanan aplikasi. Panduan ini menjadi referensi kesiapan Daffa saat mempresentasikan sistem.

### 5.1 Alur Demonstrasi Kode Langsung (Live Demo Protocol)

#### Tahap 1: Pembuktian Isolasi Data Multi-Tenant di Browser
1. Buka dua tab peramban berdampingan:
   - Tab 1: Akses portal pelanggan Cabang Banyuwangi Kota di `/klien/banyuwangi-kota`.
   - Tab 2: Akses portal pelanggan Cabang Rogojampi di `/klien/rogojampi`.
2. Tunjukkan bahwa daftar persediaan obat di tabel `stok_client` yang ditampilkan di Tab 1 berbeda dengan Tab 2.
3. Jelaskan kepada Pak Devit:
   > "Data kedua cabang ini tersimpan dalam satu tabel fisik MariaDB yang sama, namun data yang diterima pengguna diisolasi secara otomatis oleh `TenantScope` pada tingkat Abstract Syntax Tree query builder."

#### Tahap 2: Simulasi Penetrasi Serangan IDOR (Insecure Direct Object Reference)
1. Salin UUID salah satu pesanan milik Cabang Banyuwangi Kota (misal: ID `#ORD-BWI-0001`).
2. Login sebagai staf Cabang Rogojampi di `/cabang/rogojampi/dashboard`.
3. Manipulasi URL browser secara manual untuk mengakses pesanan cabang lain:
   `GET /cabang/rogojampi/pesanan/{UUID-Pesanan-Banyuwangi}`
4. Tunjukkan layar browser: Sistem langsung menolak permintaan dengan **HTTP 403 Forbidden**!
5. Buka berkas `storage/logs/laravel.log`: Tunjukkan entri log terstruktur yang mencatat upaya akses ilegal IDOR beserta IP address dan identitas pengguna.
6. Jelaskan kepada Pak Devit:
   > "Serangan manipulasi ID pesanan digagalkan ganda oleh kombinasi `ResolveTenantContext`, `TenantScope`, dan `PesananPolicy`. Apoteker cabang Rogojampi tidak dapat membaca maupun memodifikasi pesanan cabang lain meskipun mengetahui UUID pesanan tersebut."

#### Tahap 3: Eksekusi Perintah Audit Konsol (`tenant:audit`)
1. Buka terminal proyek dan jalankan perintah:
   ```bash
   php artisan tenant:audit
   ```
2. Tunjukkan tabel output yang dihasilkan di hadapan Pak Devit:
   - Total cabang terdaftar.
   - Pengecekan entitas `Pesanan`, `StokClient`, dan `RingkasanPeriodePenjualan`.
   - Jumlah `NULL id_client` bernilai 0.
   - Jumlah `Unregistered Client` bernilai 0.
   - Status Audit: **TERISOLASI 100%**.

#### Tahap 4: Eksekusi Otomatis Test Suite
1. Jalankan test suite multi-tenancy di terminal:
   ```bash
   php artisan test --filter TenantIsolationTest
   ```
2. Tunjukkan 14 indikator hijau (PASS) yang menguji:
   - Resolusi context dari rute aktif.
   - Penolakan tenant nonaktif (HTTP 403).
   - Penolakan tenant tidak terdaftar (HTTP 404).
   - Filter query otomatis pesanan dan stok.
   - Injeksi otomatis `id_client` saat pembuatan record.
   - Blokir IDOR apoteker antar cabang.
   - Blokir akses staf ke portal cabang lain.
   - Eksekusi callback terisolasi via `runInContext()`.
   - Lulusnya audit kepatuhan isolasi CLI.

---

### 5.2 Bank Pertanyaan Kritis Pak Devit Suwardiyanto & Strategi Jawaban Otoritatif

#### Pertanyaan 1: Mengapa Single Database dan Bukan Database-per-Tenant?
> **Pak Devit:** *"Kenapa kelompok kalian memilih arsitektur Single Database Multi-Tenant dan bukan Database-per-Tenant? Bukankah Database-per-Tenant jauh lebih aman karena databasenya terpisah fisik?"*

- **Jawaban Daffa:**
  > "Izin menjawab, Pak Devit. Pemilihan arsitektur didasarkan pada analisis trade-off rekayasa sistem apotek OmniPharma:
  > 1. **Beban Overhead Koneksi Basis Data:** Pada sistem apotek dengan puluhan hingga ratusan cabang, skema Database-per-Tenant memerlukan pemeliharaan connection pool terpisah yang membebani memori server MariaDB. Single Database jauh lebih efisien dalam konsumsi sumber daya komputasi.
  > 2. **Kebutuhan Agregasi Finansial Global Super Admin:** Super Admin pusat membutuhkan dashboard rekonsiliasi omzet lintas cabang secara real-time. Dengan Single Database, agregasi SQL dapat dieksekusi secara instan pada tabel `ringkasan_periode_penjualan` tanpa overhead cross-database federated queries.
  > 3. **Mitigasi Keamanan:** Risiko isolasi logis kami tangani melalui strategi Defense-in-Depth di level framework: `ResolveTenantContext` memfilter rute di boundary HTTP, `TenantScope` membatasi query di boundary ORM, dan `PesananPolicy` memvalidasi otorisasi di boundary aplikasi. Sehingga keamanannya setara dengan pemisahan database tanpa mengorbankan performa."

#### Pertanyaan 2: Bagaimana Mencegah Rekan Tim Lupa Menambahkan Klausa `WHERE id_client = ?`?
> **Pak Devit:** *"Bagaimana Anda menjamin programmer lain di tim Anda tidak lupa menambahkan filter cabang saat mereka menulis kueri transaksi baru?"*

- **Jawaban Daffa:**
  > "Itulah alasan utama kami tidak mengandalkan filter manual pada controller, melainkan membangun **Eloquent Global Scope** melalui trait `BelongsToTenant`:
  > 1. Setiap model domain yang menggunakan trait `BelongsToTenant` secara otomatis mendaftarkan `TenantScope`. Laravel secara otomatis memodifikasi Abstract Syntax Tree kueri Eloquent, sehingga seluruh method kueri seperti `all()`, `get()`, `find()`, maupun kueri relasi otomatis ditempeli klausa `WHERE id_client = ?`.
  > 2. Pada saat penambahan baris data baru, event hook `creating` pada trait secara otomatis menyuntikkan `$model->id_client = app(TenantManagerInterface::class)->id()`. Dengan mekanisme ini, developer lain tidak perlu dan tidak bisa mengosongkan atau salah mengisi `id_client`, sehingga human error dapat dicegah 100%."

#### Pertanyaan 3: Bagaimana Jika Super Admin Perlu Mengakses Data Seluruh Cabang?
> **Pak Devit:** *"Jika semua kueri ditempeli `WHERE id_client = ?`, bagaimana Super Admin pusat bisa melihat rekapitulasi data seluruh cabang tanpa terhalang isolasi tenant?"*

- **Jawaban Daffa:**
  > "Kami menyediakan dua mekanisme resmi yang terkontrol:
  > 1. **Macro `withoutTenancy()` pada Trait `BelongsToTenant`:** Method ini memanfaatkan fitur native Eloquent `withoutGlobalScope(TenantScope::class)` untuk menonaktifkan filter cabang secara eksplisit hanya pada kueri analitik Super Admin.
  > 2. **Method `runInContext(Client $client, Closure $callback)` pada `TenantManagerInterface`:** Method ini memungkinkan sistem berpindah konteks tenant secara aman dan sementara untuk mengeksekusi operasi tertentu, lalu mengembalikan konteks ke kondisi semula melalui blok `finally`, sehingga tidak merusak status request saat ini."

---

*Dokumen roadmap dan perencanaan kerja ini berstatus final dan menjadi panduan arsitektur resmi bagi Muhammad Daffa Ariby dalam penyelesaian tugas praktikum pengembangan sistem informasi.*
