# Panduan Teknis Internal & Rekapitulasi Pengembangan Sistem — OmniPharma Network

- **Judul Dokumen:** Panduan Teknis Lengkap Tugas Candia Purwa & Ringkasan Aktivitas Tim Pengembang
- **Proyek:** **OmniPharma Network** — Sistem Informasi Apotek Multi-Klien (O2O Multi-Tenant Modular Monolith)
- **Repositori Git:** `https://github.com/heliosparta18/startup.git` (`D:\projek-pdevit\startup`)
- **Baseline Verifikasi Kode:** Branch `feat/candi-upload-foto-resep` pada commit `c39e0871b7bfdb9fd7c1bde098b4e422ee68be9d` (*Merge pull request #32*, identik dengan `origin/main` per 23 November 2026)
- **Stack Terverifikasi:** PHP `^8.3` (teruji `8.4.25`), Laravel Framework `^13.17`, Livewire `^4.1`, Flux `^2.13.1`, Laravel Fortify `^1.37.2`, Laravel Reverb `^1.0`, Tailwind CSS `^4.0.7`, Vite `^8.0.0`, PHPUnit `^12.5.23`, Larastan/PHPStan `^3.9` (Level 7), Laravel Pint `^1.27`
- **Mata Kuliah / Dosen Pengampu:** Proyek Praktikum Pengembangan Sistem Informasi — Pak Devit Suwardiyanto
- **Sasaran Pembaca:** Candia Purwa (`candiapurwa-sudo`) dan seluruh tim pengembang inti (Muhammad Daffa Ariby, Guntur Afandi, Arya Dwi Mahendra)

> **Prinsip Verifikasi Dokumen (Zero Unverified Assumptions):** Seluruh jalur berkas (*file path*), nomor baris, nama kelas/method/kolom, rute HTTP/WebSocket, kunci konfigurasi, hash commit, nomor Pull Request, dan hasil pengujian di dalam panduan ini diverifikasi langsung dari kode sumber aktual, migrasi basis data, skrip test, dan riwayat git pada commit `c39e087`. Setiap perbedaan antara PRD/dokumentasi lama dengan implementasi aktual di kodebase dicatat secara eksplisit dan jujur sebagai temuan audit teknis.

---

## Daftar Isi Terstruktur

### BAGIAN I: PANDUAN TEKNIS LENGKAP TUGAS CANDIA PURWA & ARSITEKTUR INTI
- **Bab 1.** Ringkasan Eksekutif, Identitas Proyek OmniPharma, & Peta Tanggung Jawab Candia Purwa
  - 1.1 Konteks Sistem Informasi Apotek Multi-Klien (OmniPharma Network)
  - 1.2 Posisi Candia Purwa dalam Struktur Tim (4 - 4 - 4 - 3)
  - 1.3 Klarifikasi Identitas Git, Email Kontributor, dan Akun Demo Seeder
  - 1.4 Peta Tanggung Jawab Inti (M12-RT, M13, M14) dan Ekspansi Pasca-M14
- **Bab 2.** Arsitektur Modular Monolith Laravel 13, Kernel Multi-Tenancy, & Skema PDM UUID
  - 2.1 Bootstrapping Aplikasi dan Registrasi 6 Bounded Contexts
  - 2.2 Topologi Routing 4 Portal melalui `PortalRoutes` dan RBAC `EnsureUserHasRole`
  - 2.3 Kernel Multi-Tenancy (`TenantContext`, `TenantScope`, `BelongsToTenant`, `ResolveTenantContext`)
  - 2.4 Kebijakan Otorisasi (`ClientPolicy`, `PesananPolicy`) dan Audit CLI `tenant:audit`
  - 2.5 Relasi Tabel Inti PDM UUID yang Bersinggungan dengan Tugas Candi
- **Bab 3.** Pilar 1 (M12-RT) — Mesin Real-Time Laravel Reverb, Private Channels, & Echo Tracking
  - 3.1 Arsitektur Server WebSocket Laravel Reverb (`config/reverb.php`, `config/broadcasting.php`, `.env.example`)
  - 3.2 Inisialisasi Klien JavaScript (`resources/js/echo.js` dan `resources/js/app.js`)
  - 3.3 Kontrak Event `OrderStatusUpdated` dan 6 Titik Pemanggilan Terverifikasi
  - 3.4 Analisis Gap Teknis pada Event `IncomingOrderPlaced`
  - 3.5 Otorisasi Saluran Privat di `routes/channels.php` (User, Pemilik Pesanan, Cookie Tamu, Staf Cabang)
  - 3.6 Bedah Teknis Antarmuka `resources/views/customer/order-track.blade.php` & `OrderTrackingController`
- **Bab 4.** Pilar 2 (M13) — Analitik Penjualan, Snapshot Immutability ADR-0008, & Ekspor Laporan
  - 4.1 Fondasi Snapshot Akuntansi pada `RingkasanPeriodePenjualan` & Migrasi `2026_09_21_000105`
  - 4.2 Agregasi Metrik Dinamis dari `item_pesanan` pada `SalesReportService`
  - 4.3 Mekanisme Penutupan Periode Terkunci pada `CloseSalesPeriodAction` (ADR-0008)
  - 4.4 Layanan Ekspor CSV & Proteksi Formula Injection pada `SalesReportExportService`
  - 4.5 Rute dan Otorisasi Pelaporan di Portal Tenant (`routes/tenant.php`) dan Super Admin (`routes/admin.php`)
  - 4.6 Antarmuka `admin/laporan-penjualan.blade.php` dan Audit Gap Ekspor PDF (`laporan-penjualan-template.blade.php`)
- **Bab 5.** Pilar 3 (M14) — Security Hardening, Validasi Magic Bytes, Sanitasi XSS, & Quality Gate
  - 5.1 Middleware Global `SecurityHeadersMiddleware` dan Konfigurasi Content-Security-Policy (Dev vs Prod)
  - 5.2 Manajemen Rate Limiter (`AppServiceProvider` vs Throttle Aktual `routes/web.php` & `FortifyServiceProvider`)
  - 5.3 Validasi *Magic Bytes* Berkas Resep dengan `finfo_file()` pada `UnggahResepRequest`
  - 5.4 Sanitasi Input Anti-XSS pada `VerifikasiResepRequest`
  - 5.5 Audit Suite Pengujian Inti Candi dan Eksekusi Quality Gate (Lokal vs CI MariaDB/Redis)
- **Bab 6.** Pilar 4 (Kontribusi Pasca-M14: PR #13, #19–#22, #25–#26, #31–#32)
  - 6.1 Fitur Drag-and-Drop & Browse File Foto Resep (`customer/home.blade.php`, `routes/customer.php`) — PR #13
  - 6.2 Riwayat Pesanan Responsif & Filter Cabang Aktif (`OrderTrackingController::history`, `order-history.blade.php`) — PR #19–#22
  - 6.3 Ekspansi Kolom Profil `phone`/`address` & Kompatibilitas Accessor (`2026_11_18_000001`, `User`, `ProfileValidationRules`, Livewire Settings) — PR #25–#26
  - 6.4 Penandaan Visual "Stok Habis (Terkunci)" pada Kartu Katalog (`catalog-product-card.blade.php`) — PR #25
  - 6.5 Slider Mobile 3-in-1 Interaktif Landing Page & `redirect_to` (`welcome.blade.php`, `routes/web.php`) — PR #31–#32
- **Bab 7.** Audit Kritis Deviasi Dokumentasi vs Kode Aktual, Backlog Perbaikan, & Panduan Demo
  - 7.1 Daftar Lengkap 7+ Deviasi PRD/Dokumentasi terhadap Kode Sumber Aktual
  - 7.2 Backlog Teknis & Checklist Perbaikan Prioritas untuk Candi
  - 7.3 Panduan Demo Teknis 4 Layar, Perintah Operasional, & Daftar Kredensial Akun
  - 7.4 Bank Tanya-Jawab Teknis Sidang & Audit untuk Candia Purwa

### BAGIAN II: RINGKASAN LENGKAP AKTIVITAS TIM & INVENTARIS KODEBASE
- **Bab 8.** Metodologi Audit Git, Statistik Kontributor, Rekapitulasi PR, & Pipeline CI/CD
  - 8.1 Metodologi Inspeksi & Ruang Lingkup Branch (`origin/main` vs `--all`)
  - 8.2 Pemetaan Identitas Penulis Git (`git shortlog`) & Statistik 203/207 Commit
  - 8.3 Rekapitulasi 29 Merged Pull Request dan 3 Open Pull Request (#1, #2, #8)
  - 8.4 Konfigurasi Pipeline CI/CD (`.github/workflows/tests.yml`, `dependabot.yml`, `.gitignore`, `.gitattributes`)
- **Bab 9.** Ringkasan Kronologis & Teknis Pekerjaan Seluruh Anggota Tim
  - 9.1 Muhammad Daffa Ariby (`heliosparta18` / `Daffa` — 66 Commits)
  - 9.2 Guntur Afandi (`Guntur Afandi` / `Callme Zettx` / `callmezext` — 72 `main` / 74 `--all` Commits)
  - 9.3 Candia Purwa (`unknown` / `candiapurwa-sudo` — 43 Commits Lengkap dalam 4 Fase)
  - 9.4 Arya Dwi Mahendra (`Miinggg67` — 22 `main` / 23 `--all` Commits)
  - 9.5 `dependabot[bot]` & Analisis Status 3 Pull Request Terbuka (#1, #2, #8)
- **Bab 10.** Inventaris Lengkap Kodebase Terkini (Baseline Commit `c39e087`)
  - 10.1 Inventaris 20 Model Eloquent (`app/Models/*`) + Scope & Trait
  - 10.2 Inventaris 20 File Migrasi Basis Data (`database/migrations/*`)
  - 10.3 Inventaris 6 Modul Domain (`app/Modules/*`), Controller, Service, Action, Event, & Support
  - 10.4 Inventaris Seluruh Route Files (`routes/*.php`), Middleware, FormRequest, Livewire, & Frontend Assets
  - 10.5 Inventaris 19 Factory, 4 Seeder, Dataset GoApotik (42 & 2.424 Produk), & Skrip Otomasi
  - 10.6 Inventaris Lengkap 37 File Pengujian (`240` Test Methods)
  - 10.7 Inventaris Dependensi Paket & Konfigurasi Quality Gate (`composer.json`, `package.json`, `phpstan.neon`, `phpunit.xml`)
- **Bab 11.** Audit Keselarasan Lintas-Dokumentasi & Architecture Decision Records (ADR 0001–0008)
  - 11.1 Ringkasan Teknis ADR-0001 hingga ADR-0008 & Status Kepatuhannya
  - 11.2 Audit Sinkronisasi Dokumen Root, PRD Anggota, RTM, dan Roadmap
  - 11.3 Pemetaan Arsip Kurikulum Praktikum (`modul_praktikum/*` & `references/*`) terhadap Implementasi Nyata
- **Bab 12.** Matriks Kesiapan Tim, Risiko Teknis Lintas-Modul, & Rencana Kerja Sinkronisasi
  - 12.1 Matriks Kesiapan Lintas-Domain (Baseline `c39e087`)
  - 12.2 Matriks Ketergantungan Lintas-Modul & Infrastruktur Eksternal
  - 12.3 Risiko Teknis Lintas-Modul yang Wajib Diperhatikan Tim
  - 12.4 Rencana Kerja Sinkronisasi Berikutnya (Untuk Candi & Seluruh Tim)
  - 12.5 Checklist Verifikasi Teknis Pra-Presentasi Lintas-Tim

---

# BAGIAN I: PANDUAN TEKNIS LENGKAP TUGAS CANDIA PURWA & ARSITEKTUR INTI

---

## Bab 1. Ringkasan Eksekutif, Identitas Proyek OmniPharma, & Peta Tanggung Jawab Candia Purwa

### 1.1 Konteks Sistem Informasi Apotek Multi-Klien (OmniPharma Network)

**OmniPharma Network** adalah platform sistem informasi apotek jaringan multi-cabang (*multi-client online-to-offline pharmacy*) yang dibangun menggunakan arsitektur **Modular Monolith** di atas **Laravel 13** (`laravel/framework: ^13.17` di `composer.json:15`), **PHP `^8.3`** (diverifikasi berjalan pada runtime PHP `8.4.25`), **Livewire `^4.1`**, **Flux `^2.13.1`**, **Laravel Fortify `^1.37.2`**, **Laravel Reverb `^1.0`**, **Tailwind CSS `^4.0.7`**, dan **Vite `^8.0.0`**. Proyek ini dikelola dalam satu repositori terpadu (`https://github.com/heliosparta18/startup.git`) sesuai keputusan arsitektur **ADR-0001**, menggunakan basis data relasional terpadu **MariaDB/MySQL** (dengan **SQLite `:memory:`** untuk eksekusi PHPUnit lokal sesuai `phpunit.xml:25-26`) serta **Redis** untuk penyimpanan keranjang belanja, *locking* antrean, sesi, dan cache operasional.

Secara bisnis, jaringan OmniPharma menghubungkan dua cabang apotek resmi dalam data deterministik bawaan (`database/seeders/DemoApotekSeeder.php:29-47`):
1. **Cabang Banyuwangi Kota** (`id_client`: `a1b2c3d4-0001-4000-8000-000000000001`, `slug`: `banyuwangi-kota`, alamat `Jl. Ahmad Yani No. 45, Banyuwangi Kota`).
2. **Cabang Rogojampi** (`id_client`: `a1b2c3d4-0002-4000-8000-000000000002`, `slug`: `rogojampi`, alamat `Jl. Raya Rogojampi No. 12, Rogojampi`).

Seluruh alur layanan apotek dipecah menjadi **6 Bounded Contexts** (`Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, `Reporting`) dan diekspos melalui **4 Portal Terproteksi** (`customer`, `tenant`, `kasir`, `admin`), dari penelusuran katalog multi-cabang hingga rekonsiliasi laporan penjualan terkunci permanen.

### 1.2 Posisi Candia Purwa dalam Struktur Tim (4 - 4 - 4 - 3)

Berdasarkan `README.md:28-35`, `docs/PRD_CANDI.md:5-30`, dan `docs/PRD_DAFFA.md:26`, proyek praktikum 14 pertemuan ini dibagi dengan komposisi beban **4 - 4 - 4 - 3** di bawah bimbingan Pak Devit Suwardiyanto:
- **Muhammad Daffa Ariby** (`heliosparta18`): **4 Tugas Inti (M01–M04)** — Setup toolchain, Modular Monolith 6 bounded context, skema PDM UUID 16 tabel awal, dan Kernel Multi-Tenancy anti-IDOR. (Catatan di `docs/PRD_DAFFA.md:26`: tugas M14 dialihkan dari Daffa ke Candi agar Daffa fokus menuntaskan M04).
- **Arya Dwi Mahendra** (`Miinggg67`): **4 Tugas Inti (M05–M07, M12)** — Manajemen staf & hierarki pengawasan APJ 5NF, sesi pelanggan *opaque token*, katalog Livewire, verifikasi resep dokter, dan rotasi stok FEFO H-30/H-7.
- **Guntur Afandi** (`callmezext` / `Callme Zettx`): **4 Tugas Inti (M08–M11)** — Keranjang belanja Redis multi-tenant, checkout atomik `lockForUpdate`, kalkulasi ongkir kurir Maps, gateway QRIS (Midtrans Sandbox + EMVCo fallback), webhook HMAC, portal Kasir, serta autentikasi Firebase Google & Email OTP.
- **Candia Purwa** (`candiapurwa-sudo`): **3 Pilar Inti (M12-RT, M13, M14)** — *Real-Time Infrastructure* (Laravel Reverb & Echo order tracking), *Financial Analytics & Reporting* (ADR-0008 snapshot immutability, ekspor CSV anti formula injection, template PDF), serta *Security Hardening & Quality Assurance* (CSP/security headers, rate limiting, validasi magic bytes unggah resep, sanitasi XSS verifikasi resep, dan automated E2E/security tests).

Di luar 3 pilar kurikulum awal tersebut, riwayat git membuktikan Candi juga menjadi kontributor aktif pada **4 gelombang pengembangan UX & integrasi pasca-M14** (PR #13, PR #19–#22, PR #25–#26, serta PR #31–#32) yang langsung menyentuh pengalaman pengguna akhir di beranda (`welcome.blade.php`), katalog (`customer/home.blade.php`, `catalog-product-card.blade.php`), riwayat pesanan (`order-history.blade.php`), dan pengaturan profil (`⚡profile.blade.php`, `⚡security.blade.php`).

### 1.3 Klarifikasi Identitas Git, Email Kontributor, dan Akun Demo Seeder

Salah satu sumber kebingungan paling sering saat membaca log repositori atau demo aplikasi adalah perbedaan antara **identitas author Git** dengan **akun demo di dalam basis data (`DemoApotekSeeder.php`)**. Tabel berikut memetakan identitas Candia Purwa secara presisi agar tidak terjadi kesalahan atribusi saat audit atau presentasi:

| Jenis Identitas | Nilai Terverifikasi di Sistem | Lokasi / Sumber Kebenaran | Keterangan Teknis |
|---|---|---|---|
| **Git Local Author** | `unknown <candia.purwa@gmail.com>` | `C:/Users/asus2/.gitconfig` + `git log` (31 non-merge + 3 local merge = 34 commit) | Terjadi karena `git config user.email` sudah terpasang (`candia.purwa@gmail.com`), namun `git config user.name` lokal **belum diatur** (*unset*). |
| **GitHub PR Merge Author** | `candiapurwa-sudo <candia.purwa@gmail.com>` | `gh auth status` + `git log` (9 Merge PR commits: #13, #19, #20, #21, #22, #25, #26, #31, #32) | Akun GitHub aktif di mesin pengembang melalui Git Credential Manager HTTPS. |
| **Akun Seeder Apoteker (Legacy Test)** | `candia@omnipharma.test` (`apt. Candia Purwa, S.Farm`, peran `apoteker`) | `database/seeders/DemoApotekSeeder.php:222-250` | Akun *backward-compatibility* untuk test suite `TenantIsolationTest.php:130,141` pada Cabang Banyuwangi Kota. |
| **Akun Seeder Pelanggan (Demo Pasien)** | `candi@gmail.com` (`Candi Chier`, peran `pelanggan`) | `database/seeders/DemoApotekSeeder.php:293-303`, `AKUN_LOGIN.md:25` | Akun demo pasien umum; nama `Candi Chier (88 th)` juga dipakai pada data dummy resep `DemoApotekSeeder.php:474` dan nilai default `unggahForm` di `customer/home.blade.php:476`. |

> **Rekomendasi Kebersihan Git Lokal:** Sebelum membuat commit baru dari mesin lokal ini, jalankan `git config --local user.name "candiapurwa-sudo"` agar commit selanjutnya tidak tercatat sebagai `unknown`.

### 1.4 Peta Tanggung Jawab Inti (M12-RT, M13, M14) dan Ekspansi Pasca-M14

Secara kronologis dan struktural, pekerjaan Candia Purwa terbagi menjadi **4 Pilar Teknis Utama**:

| Pilar | Cakupan Modul / PR | Berkas Utama Terverifikasi | Status Aktual di `c39e087` |
|---|---|---|---|
| **Pilar 1: M12-RT (Real-Time WebSockets)** | Setup Reverb, Event Broadcast, Otorisasi Channel Privat, Echo Order Tracker | `config/reverb.php`, `config/broadcasting.php`, `.env.example`, `resources/js/echo.js`, `OrderStatusUpdated.php`, `IncomingOrderPlaced.php`, `routes/channels.php`, `resources/views/customer/order-track.blade.php` | **Aktif** (6 titik dispatch `OrderStatusUpdated`); **Catatan Gap:** `IncomingOrderPlaced` belum di-dispatch |
| **Pilar 2: M13 (Analitik & Pelaporan ADR-0008)** | Agregasi Penjualan, Penutupan Periode Terkunci, Ekspor CSV Aman Injeksi, Template PDF | `RingkasanPeriodePenjualan.php`, `SalesReportService.php`, `CloseSalesPeriodAction.php`, `SalesReportExportService.php`, `routes/tenant.php:517-560`, `routes/admin.php:97-169`, `admin/laporan-penjualan.blade.php`, `admin/pdf/laporan-penjualan-template.blade.php` | **Aktif** untuk agregasi, kunci periode, & ekspor CSV; **Catatan Gap:** Template PDF belum punya driver/endpoint render |
| **Pilar 3: M14 (Security Hardening & QA)** | Security Headers & CSP, Rate Limiter, Validasi Magic Bytes Upload Resep, Sanitasi XSS Verifikasi Resep, Automated Tests | `SecurityHeadersMiddleware.php`, `bootstrap/app.php:33`, `AppServiceProvider.php:39-52`, `UnggahResepRequest.php`, `VerifikasiResepRequest.php`, `ReverbOrderStatusBroadcastTest.php`, `SalesReportAndPeriodLockTest.php`, `SecurityHardeningTest.php`, `EndToEndOrderFlowTest.php` | **Aktif** pada headers, magic bytes, XSS FormRequest; **Catatan Gap:** Limiter `api`/`broadcasting`/`transaksi` belum di-bind ke route |
| **Pilar 4: Ekspansi Pasca-M14 (PR #13, #19–#22, #25–#26, #31–#32)** | Drag-drop upload resep, riwayat pesanan responsif & filter cabang, profil `phone`/`address`, lock card stok habis, slider mobile landing | `customer/home.blade.php`, `routes/customer.php`, `OrderTrackingController.php`, `order-history.blade.php`, `2026_11_18_000001_add_phone_and_address_to_users_table.php`, `User.php`, `ProfileValidationRules.php`, `⚡profile.blade.php`, `⚡security.blade.php`, `catalog-product-card.blade.php`, `welcome.blade.php`, `routes/web.php` | **Aktif & Ter-merge penuh** di `main` |

---

## Bab 2. Arsitektur Modular Monolith Laravel 13, Kernel Multi-Tenancy, & Skema PDM UUID

### 2.1 Bootstrapping Aplikasi dan Registrasi 6 Bounded Contexts

Seluruh tanggung jawab Candi beroperasi di atas fondasi arsitektur **Modular Monolith** yang ditetapkan oleh **ADR-0006** (`docs/adr/0006-modular-monolith.md`). Dalam pola ini, aplikasi tetap dijalankan sebagai satu unit deployment Laravel 13 dengan satu basis data MariaDB/MySQL transaksional (ACID), namun kode domain dipecah menjadi enam irisan vertikal (*vertical slices*) di `app/Modules/`:

1. `Admin` (`App\Modules\Admin\AdminServiceProvider`) — Administrasi cabang, manajemen staf, peran lokal, serta hierarki pengawasan APJ.
2. `Catalog` (`App\Modules\Catalog\CatalogServiceProvider`) — Master obat, kategori/subkategori, sinkronisasi katalog GoApotik, sesi tamu, dan inventori stok FEFO.
3. `Ordering` (`App\Modules\Ordering\OrderingServiceProvider`) — Keranjang Redis, checkout atomik, pelacakan pesanan, riwayat pesanan, dan **event broadcasting Reverb (`OrderStatusUpdated`, `IncomingOrderPlaced`)**.
4. `Payments` (`App\Modules\Payments\PaymentsServiceProvider`) — Abstraksi `PaymentGatewayInterface`, gateway Midtrans Sandbox + EMVCo QRIS, dan `PaymentWebhookController`.
5. `Prescription` (`App\Modules\Prescription\PrescriptionServiceProvider`) — Logika bisnis penelaahan resep (`VerifyPrescriptionAction`).
6. `Reporting` (`App\Modules\Reporting\ReportingServiceProvider`) — **Domain inti M13 Candi**: `SalesReportService`, `SalesReportExportService`, dan `CloseSalesPeriodAction`.

Keenam provider modul didaftarkan secara eksplisit di `bootstrap/providers.php:12-22`:
```php
return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AdminServiceProvider::class,
    CatalogServiceProvider::class,
    OrderingServiceProvider::class,
    PaymentsServiceProvider::class,
    PrescriptionServiceProvider::class,
    ReportingServiceProvider::class,
];
```

Setiap provider modul mewarisi kelas abstrak `App\Modules\ModuleServiceProvider` (`app/Modules/ModuleServiceProvider.php:22-52`). Pada metode `boot()`, `ModuleServiceProvider` melakukan tiga registrasi otomatis berdasarkan `moduleAlias()` masing-masing modul:
- `loadViewsFrom($path.'/resources/views', $this->moduleAlias())` (baris 33) sehingga view modul dapat dipanggil melalui `view('{alias}::...')`.
- `Livewire::addNamespace(namespace: $this->moduleAlias(), viewPath: $path.'/resources/views/livewire')` (baris 35-38) untuk komponen `<livewire:{alias}::nama-komponen />`.
- Pemindaian `glob($path.'/routes/*.php')` dan pemuatan rute lewat `loadRoutesFrom($routeFile)` (baris 40-42).

Konvensi ini dijaga secara otomatis oleh `tests/Feature/ModuleConventionTest.php` (5 test methods, 191 assertions) yang memverifikasi bahwa setiap modul tidak keluar dari batas *namespace*, sementara model Eloquent (`app/Models/`), middleware HTTP (`app/Http/Middleware/`), FormRequest (`app/Http/Requests/`), dan komponen layout utama tetap berada di *shared kernel* Laravel.

### 2.2 Topologi Routing 4 Portal melalui `PortalRoutes` dan RBAC `EnsureUserHasRole`

Di `bootstrap/app.php:13-28`, Laravel mendaftarkan empat berkas rute inti (`web.php`, `api.php`, `console.php`, `channels.php`), lalu memuat empat file rute portal melalui kelas tunggal `App\Support\Routing\PortalRoutes` (`app/Support/Routing/PortalRoutes.php:14-62`):

| Portal | Metode `PortalRoutes` | Prefix URL | Name Prefix | Pipeline Middleware | Berkas Rute |
|---|---|---|---|---|---|
| **Pelanggan (Publik)** | `PortalRoutes::customer()` (`baris 20-26`) | `klien/{client}` | `customer.` | `['web', 'tenant']` | `routes/customer.php` (250 baris) |
| **Apoteker Cabang (Tenant)** | `PortalRoutes::tenant()` (`baris 32-38`) | `cabang/{client}` | `tenant.` | `['web', 'auth', 'verified', 'role:apoteker', 'tenant']` | `routes/tenant.php` (560 baris) |
| **Kasir & Loket** | `PortalRoutes::kasir()` (`baris 43-49`) | `kasir` | `kasir.` | `['web', 'auth', 'verified', 'role:kasir']` | `routes/kasir.php` (401 baris) |
| **Super Admin Pusat** | `PortalRoutes::admin()` (`baris 55-61`) | `admin` | `admin.` | `['web', 'auth', 'verified', 'role:super_admin']` | `routes/admin.php` (616 baris) |

Middleware alias `role` dipetakan ke `App\Http\Middleware\EnsureUserHasRole` (`bootstrap/app.php:36`, `app/Http/Middleware/EnsureUserHasRole.php:14-48`) yang memverifikasi:
1. Pengguna sudah terautentikasi (`$request->user() !== null`, jika null langsung `abort(403)` pada baris 21-23).
2. Status akun aktif (`$user->isActive()`, yaitu `status === 'active' || status === 'aktif'` di `User.php:445-448`, jika nonaktif `abort(403)` pada baris 26-28).
3. Peran cocok lewat `$user->hasRole($role)` (`User.php:138-148`) dengan dukungan argumen jamak dipisah koma atau pipa (`explode(',', str_replace('|', ',', $rString))` di `EnsureUserHasRole.php:32`), mendukung ekuivalensi kompatibilitas (`apoteker` ↔ `tenant`, `pelanggan` ↔ `customer`, `admin` ↔ `super_admin`/`kasir`/`staf_klien`, `kasir` ↔ `admin`/`staf_klien`), dan langsung memutus eksekusi dengan **HTTP 403 Forbidden** jika gagal.

Alur lengkap pipeline HTTP dari browser hingga database dapat digambarkan secara arsitektural sebagai berikut:

```text
[HTTP Request: GET/POST /klien/{client}/... atau /cabang/{client}/... atau /admin/...]
       │
       ▼
[1. Global Middleware: SecurityHeadersMiddleware] (bootstrap/app.php:33)
       │ • Menyuntikkan X-Frame-Options, nosniff, Referrer-Policy, COOP, CSP (Reverb/Firebase)
       ▼
[2. Portal Middleware: web -> auth -> verified -> role:{portal}] (PortalRoutes.php)
       │ • EnsureUserHasRole: cek user !== null, isActive(), hasRole() -> 403 jika ditolak
       ▼
[3. Tenant Boundary: ResolveTenantContext] (alias 'tenant', khusus customer & tenant)
       │ • Resolusi {client} via id_client / slug / nama_client (atau fallback session/user)
       │ • TenantNotFoundException (404) | TenantInactiveException (403)
       │ • Pertahanan IDOR /cabang/* (cek admin_client / peran_client_pengguna) -> 403 + Log::warning
       │ • Ikat instance Client ke IoC scoped TenantManagerInterface::set($client)
       ▼
[4. Controller / Action / Livewire Component]
       │ • Query Eloquent otomatis terfilter oleh TenantScope (WHERE id_client = ?)
       │ • Bypass sadar hanya untuk Super Admin / Reporting / Channels via ::withoutTenancy()
       ▼
[5. Persistensi MariaDB / MySQL + Redis + Reverb RFC 6455]
```

Selain rute ber-prefix portal di atas, `routes/web.php:142-185` juga menyediakan rute ringkas (*short routes*) di bawah middleware `['tenant']` seperti `/keranjang`, `/checkout`, `/pembayaran/{id}`, `/lacak-pesanan/{id}`, dan `/riwayat-pesanan`. Di sinilah peran `ResolveTenantContext::syncRouteClientParameter()` (`ResolveTenantContext.php:122-162`) menjadi sangat krusial: ketika rute ringkas tidak memiliki segmen `{client}` di URL, middleware tetap menyuntikkan parameter `client` sesuai urutan parameter signature controller melalui `ReflectionParameter` (`signatureParameters()`) agar parameter posisional `(string $client, string $id)` di `OrderTrackingController` tidak pernah tertukar!

### 2.3 Kernel Multi-Tenancy (`TenantContext`, `TenantScope`, `BelongsToTenant`, `ResolveTenantContext`)

Tugas Candi (terutama `OrderStatusUpdated`, `SalesReportService`, `CloseSalesPeriodAction`, `SalesReportExportService`, dan `OrderTrackingController::history`) sangat bergantung pada cara kerja kernel multi-tenancy Single-Database yang dibangun di Pertemuan 04. Kontrak resminya didefinisikan oleh `App\Support\Tenant\Contracts\TenantManagerInterface` (`app/Support/Tenant/Contracts/TenantManagerInterface.php:8-62`):

| Metode Kontrak | Tipe Return | Fungsi & Perilaku Terverifikasi (`TenantContext.php`) |
|---|---|---|
| `get()` | `?Client` | Mengembalikan model `Client` tenant aktif pada siklus request, atau `null` jika belum di-set (`baris 39-42`). |
| `set(Client $client)` | `void` | Menyimpan tenant aktif; memancarkan `TenantSwitched` bila berpindah cabang dan `TenantResolved` saat resolusi pertama (`baris 24-37`). |
| `id()` | `string` | Mengembalikan UUID `$this->client->id_client`; melempar `TenantContextMissingException` jika `$this->client === null` (`baris 54-61`). |
| `name()` / `slug()` | `?string` | Mengembalikan `$this->client?->nama_client` dan `$this->client?->slug` (`baris 63-71`). |
| `check()` / `has()` | `bool` | Memeriksa apakah konteks tenant aktif saat ini terisi (`baris 44-52`). |
| `clear()` | `void` | Menghapus konteks aktif (`$this->client = null; $this->resolved = false;`) dan memancarkan `TenantSwitched($previous, null)` (`baris 73-82`). |
| `runInContext(Client, Closure)` | `mixed` | Menjalankan callback dalam konteks cabang target lalu memulihkan konteks asal (atau `clear()`) di dalam blok `finally` (`baris 90-104`). |

Empat kelas *domain exception* khusus melengkapi kontrak tersebut:
- `TenantNotFoundException` (`HttpException` status **404**, `app/Support/Tenant/Exceptions/TenantNotFoundException.php:7-17`) — dilempar ketika `{client}` tidak cocok dengan `id_client`, `slug`, maupun `nama_client`.
- `TenantInactiveException` (`HttpException` status **403**, `TenantInactiveException.php:7-17`) — dilempar ketika cabang terdaftar memiliki `aktif === false`.
- `TenantAccessDeniedException` (`HttpException` status **403**, `TenantAccessDeniedException.php:7-17`) — dilempar saat akses silang portal `/cabang/*` oleh staf cabang lain atau saat payload `creating` memalsukan `id_client` berbeda di `BelongsToTenant`.
- `TenantContextMissingException` (`RuntimeException`, `TenantContextMissingException.php:7-15`) — dilempar oleh `TenantContext::id()` jika dipanggil tanpa konteks aktif.

1. **Kontrak & Singleton Scoped (`app/Providers/AppServiceProvider.php:24-25` & `app/Support/Tenant/TenantContext.php:12-105`):**
   `TenantContext` didaftarkan sebagai `scoped` singleton di IoC Container Laravel (terikat ke `TenantManagerInterface`), sehingga state cabang aktif hanya hidup sepanjang satu siklus HTTP request/job dan otomatis bersih antar request di worker/Octane. Metode kuncinya mencakup `set(Client)`, `get()`, `check()`, `has()`, `id()` (melempar `TenantContextMissingException` jika belum di-set), `clear()`, serta `runInContext(Client $client, Closure $callback)` yang memulihkan konteks asal di blok `finally`.
2. **Middleware HTTP Boundary (`app/Http/Middleware/ResolveTenantContext.php:19-163`):**
   - Menangkap parameter rute `client` (atau fallback dari input `client`, session `active_branch` / `active_client`, `user->getClientSlug()`, atau default `'banyuwangi-kota'`).
   - Melakukan lookup di tabel `client` berdasarkan `id_client` (UUID), `slug`, atau `nama_client`.
   - Jika tidak ditemukan -> lempar `TenantNotFoundException` (HTTP 404). Jika `client.aktif == false` -> lempar `TenantInactiveException` (HTTP 403).
   - **Pertahanan IDOR `/cabang/*` (baris 66-111):** Jika request masuk ke portal apoteker `/cabang/*` dan pengguna bukan `super_admin`, middleware memeriksa kepemilikan cabang di `admin_client` atau `peran_client_pengguna` (serta fallback khusus email Rogojampi `dewi@omnipharma.test` / `apoteker.rogojampi@omnipharma.test` / `dinda@omnipharma.test`). Akses silang cabang dicatat ke `Log::warning('Upaya IDOR portal cabang dicegah:', ...)` dan ditolak dengan `TenantAccessDeniedException` (HTTP 403).
3. **Eloquent Global Scope & Trait (`TenantScope.php` & `BelongsToTenant.php`):**
   - Tiga model transaksi/inventori/pelaporan menggunakan trait `BelongsToTenant`: **`Pesanan`**, **`StokClient`**, dan **`RingkasanPeriodePenjualan`**.
   - `TenantScope::apply()` (`TenantScope.php:18-27`) otomatis menyuntikkan `WHERE {table}.id_client = ?` setiap kali `TenantContext::check()` bernilai `true`.
   - `BelongsToTenant::bootBelongsToTenant()` (`BelongsToTenant.php:20-40`) memasang hook `creating`: jika `id_client` kosong saat insert, otomatis diisi dari `$context->id()`; jika diisi nilai `id_client` berbeda dari konteks aktif, sistem melempar `TenantAccessDeniedException` (*payload tampering protection*).
   - **Implikasi Penting bagi Kode Candi:** Karena `TenantScope` aktif pada portal `/klien/{client}` maupun `/cabang/{client}`, query lintas-cabang atau agregasi oleh Super Admin/pelaporan/order tracking wajib memakai **`::withoutGlobalScopes()`** atau **`::withoutTenancy()`** secara sadar dan disertai pengecekan otorisasi eksplisit! Contoh nyatanya terlihat pada `SalesReportService::getSummary()` (`Pesanan::withoutGlobalScopes()->where('id_client', $clientId)`), `CloseSalesPeriodAction::execute()`, `OrderTrackingController::history()`, serta otorisasi `routes/channels.php:13`.

### 2.4 Kebijakan Otorisasi (`ClientPolicy`, `PesananPolicy`) dan Audit CLI `tenant:audit`

Selain boundary middleware dan Eloquent scope, lapisan otorisasi model dilengkapi dua Policy dan satu perintah Artisan audit:
- **`App\Policies\ClientPolicy` (`app/Policies/ClientPolicy.php:9-34`):**
  `view()` memeriksa `$client->aktif`; `operate()` mengizinkan `super_admin` kapan pun cabang aktif, sedangkan operator cabang ditolak jika `$context->check() && $context->id() !== $client->id_client`.
- **`App\Policies\PesananPolicy` (`app/Policies/PesananPolicy.php:9-30`):**
  `view()` dan `verify()` mengizinkan `super_admin`, atau memastikan `$pesanan->id_client === $context->id()`. Policy ini dipanggil langsung lewat `Gate::authorize('view', $pesanan)` di `routes/tenant.php:509` pada endpoint `GET /cabang/{client}/pesanan/{id}` (`tenant.pesanan.detail`).
- **`App\Console\Commands\AuditTenantIsolationCommand` (`php artisan tenant:audit`, `app/Console/Commands/AuditTenantIsolationCommand.php:13-85`):**
  Memeriksa ketiga entitas `BelongsToTenant` (`Pesanan`, `StokClient`, `RingkasanPeriodePenjualan`) dengan `withoutTenancy()` untuk memastikan **0 baris `id_client IS NULL`** dan **0 baris `id_client NOT IN (clients)`**. Jika ditemukan pelanggaran, command keluar dengan `Command::FAILURE` (exit code 1).

### 2.5 Relasi Tabel Inti PDM UUID yang Bersinggungan dengan Tugas Candi

Seluruh tabel domain di OmniPharma memakai **UUID (`char(36)`)** sebagai Primary Key dan Foreign Key (**ADR-0007**). Bagi pekerjaan Candia Purwa, entitas berikut merupakan fondasi data utama:

| Entitas / Tabel | Primary Key | Kolom Kunci Terkait Tugas Candi | Catatan Relasi & Constraint |
|---|---|---|---|
| `client` | `id_client` (UUID) | `nama_client`, `slug`, `alamat`, `radius_km`, `jam_buka`, `aktif` | Root tenant; direferensikan `pesanan`, `stok_client`, `ringkasan_periode_penjualan`. |
| `users` | `id` (BigInt) | `name`, `email`, `role`, `status`, `phone`, `address`, `two_factor_*` | Tabel autentikasi Fortify/Laravel; kolom `phone` & `address` ditambahkan Candi di migrasi `2026_11_18_000001`. |
| `pengguna` | `id_pengguna` (UUID) | `nama_lengkap`, `peran`, `telepon_email` (unique), `aktif` | Disinkronkan dengan `users` berdasarkan `telepon_email === users.email`. |
| `pesanan` | `id_pesanan` (UUID) | `id_pengguna`, `id_client`, `nomor_pesanan` (unique), `status`, `metode_layanan`, `token_sesi`, `slot_waktu`, `nomor_antrean`, `catatan_pesanan`, `dibuat_pada` | Catatan: **tidak ada kolom `total_harga` atau `status_pesanan` di DB**; `total_harga` adalah accessor model (`Pesanan.php:141-144`) dari `items->sum('subtotal') + ongkos_kirim`. |
| `item_pesanan` | `id_item` (UUID) | `id_pesanan`, `id_obat`, `kuantitas`, `nama_obat`, `harga_satuan`, `subtotal` | Snapshot harga & nama obat (**ADR-0008**); sumber agregasi `SalesReportService`, `CloseSalesPeriodAction`, dan `SalesReportExportService`. |
| `resep` | `id_resep` (UUID) | `id_pesanan`, `url_gambar`, `status_verifikasi`, `nama_dokter`, `sip_dokter`, `nama_pasien`, `catatan`, `sip_valid`, `dosis_valid`, `catatan_apoteker`, `diverifikasi_oleh` | Unggahan foto disimpan di disk `public/resep` (`/storage/resep/...`) dengan validasi *magic bytes* oleh `UnggahResepRequest`; verifikasi disanitasi oleh `VerifikasiResepRequest`. |
| `pembayaran` | `id_pembayaran` (UUID) | `id_pesanan`, `metode_pembayaran`, `jumlah`, `url_lampiran`, `status`, `diverifikasi_oleh` | Pelunasan memicu `OrderStatusUpdated::dispatch()` di `OrderTrackingController` & `PaymentWebhookController`. |
| `ringkasan_periode_penjualan` | `id_ringkasan` (UUID) | `id_client`, `nama_client`, `total_obat_terjual`, `periode_mulai`, `periode_selesai`, `total_pesanan`, `total_pendapatan`, `total_retur`, `status_terkunci`, `dibuat_pada` | Constraint unik `uq_ringkasan_client_periode` pada `(id_client, periode_mulai, periode_selesai)` mencegah penutupan buku ganda (**ADR-0008**). |
| `log_audit` | `id_audit` (UUID) | `id_aktor`, `aksi`, `nama_entitas`, `id_entitas`, `payload` (JSON), `dibuat_pada` | Append-only audit trail; dicatat pada aksi `TELAAH_RESEP` di `routes/tenant.php:388-401`. |

---

## Bab 3. Pilar 1 (M12-RT) — Mesin Real-Time Laravel Reverb, Private Channels, & Echo Tracking

### 3.1 Arsitektur Server WebSocket Laravel Reverb (`config/reverb.php`, `config/broadcasting.php`, `.env.example`)

Sebelum M12-RT diimplementasikan (commit `026aa90` pada 7 Nov 2026), aplikasi belum memiliki infrastruktur WebSocket *full-duplex* berbasis **RFC 6455**. Candi menambahkan `laravel/reverb: ^1.0` (`composer.json:16`), konfigurasi server di `config/reverb.php` (87 baris), serta koneksi broadcaster `reverb` di `config/broadcasting.php:33-47`.

Parameter penting pada `config/reverb.php`:
- **Default Server (`baris 11`):** `'default' => env('REVERB_SERVER', 'reverb')`.
- **Server Binding (`baris 21-29`):**
  - `'host' => env('REVERB_SERVER_HOST', '0.0.0.0')`
  - `'port' => env('REVERB_SERVER_PORT', 8080)`
  - `'hostname' => env('REVERB_HOST', 'localhost')`
  - `'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000)`
- **Horizontal Scaling Opsional via Redis (`baris 30-42`):** `'scaling.enabled' => env('REVERB_SCALING_ENABLED', false)` dengan channel `'reverb'` yang membaca konfigurasi `REDIS_HOST`, `REDIS_PORT`, `REDIS_DB`.
- **Konfigurasi Aplikasi Reverb (`baris 59-83`):** Mengambil `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_APP_ID`, `allowed_origins => ['*']`, `ping_interval => 60`, `activity_timeout => 30`, `max_message_size => 10_000`, serta fitur opsional `rate_limiting` bawaan server Reverb (`enabled => false`, `max_attempts => 60`, `decay_seconds => 60`).

Pada `.env.example:36,68-79`:
- `BROADCAST_CONNECTION=reverb` (sedangkan di `phpunit.xml:22`, `BROADCAST_CONNECTION=null` agar test unit/fitur tidak gagal saat daemon Reverb tidak dijalankan).
- Variabel client Vite diturunkan langsung dari konfigurasi server:
  `VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"`, `VITE_REVERB_HOST="${REVERB_HOST}"`, `VITE_REVERB_PORT="${REVERB_PORT}"`, `VITE_REVERB_SCHEME="${REVERB_SCHEME}"`.

- Client Echo (`resources/js/echo.js`, 18 baris) & integrasi aset (`resources/js/app.js`, 8 baris) menghubungkan browser pelanggan ke server WebSocket tersebut.
- Ringkasan pemetaan parameter lingkungan `.env.example:36,68-79` terhadap `config/reverb.php` dan `config/broadcasting.php`:

| Variabel `.env.example` | Nilai Default `.env.example` | Konsumsi Server (`config/reverb.php` / `broadcasting.php`) | Konsumsi Browser (`resources/js/echo.js`) |
|---|---|---|---|
| `BROADCAST_CONNECTION` | `reverb` | `broadcasting.default` (`config/broadcasting.php:18`) | — |
| `REVERB_APP_ID` / `KEY` / `SECRET` | *(kosong, wajib diisi lokal)* | `apps.apps[0]` & `connections.reverb` | `VITE_REVERB_APP_KEY` sebagai kunci otentikasi Pusher protocol |
| `REVERB_HOST` | `localhost` | `servers.reverb.hostname` & `connections.reverb.options.host` | `VITE_REVERB_HOST` (`wsHost`) |
| `REVERB_PORT` | `8080` | `apps.apps[0].options.port` (`8080`) | `VITE_REVERB_PORT` (`wsPort` / `wssPort`) |
| `REVERB_SCHEME` | `http` | `useTLS = (REVERB_SCHEME === 'https')` | `forceTLS = (VITE_REVERB_SCHEME === 'https')` |
| `REVERB_SERVER_HOST` / `PORT` | `0.0.0.0` / `8080` | Binding socket daemon `php artisan reverb:start` | — |

Alur komunikasi real-time end-to-end M12-RT saat status pesanan berubah:

```text
[Pelanggan: order-track.blade.php]           [Laravel Backend + Reverb :8080]          [Aktor: Apoteker / Kasir / Midtrans]
   │                                                  │                                         │
   ├─► 1. Init Alpine omniOrderTrackerData()          │                                         │
   │   Echo.private('order.' + id_pesanan)            │                                         │
   │   ──► POST /broadcasting/auth ──────────────────►│                                         │
   │       (cek user->id_pengguna / cookie            │                                         │
   │        omnipharma_customer_session di            │                                         │
   │        routes/channels.php:12-33)                │                                         │
   │◄── 200 Auth Signature ───────────────────────────┤                                         │
   │   (Subscribed ke private-order.{id})             │                                         │
   │                                                  │                                         │
   │                                                  │◄─ 2. Update Status / Pelunasan ─────────┤
   │                                                  │   (Webhook QRIS / Kasir / Apoteker)     │
   │                                                  │   DB::transaction -> Pesanan->update()  │
   │                                                  │   OrderStatusUpdated::dispatch($pesanan)│
   │                                                  │   broadcastAs() = 'OrderStatusUpdated'  │
   │                                                  │   broadcastOn() = private-order.{id},   │
   │                                                  │                   private-tenant.{id}   │
   │◄── 3. Push Frame WS RFC 6455 (<50ms) ────────────┤                                         │
   │   .listen('.OrderStatusUpdated', (e) => {        │                                         │
   │     this.currentStatus = e.status_pesanan ||     │                                         │
   │                          e.status;               │                                         │
   │   })                                             │                                         │
   │   Stepper UI maju otomatis tanpa reload!         │                                         │
```

### 3.2 Inisialisasi Klien JavaScript (`resources/js/echo.js` dan `resources/js/app.js`)

Di sisi frontend, `resources/js/app.js:7` mengimpor `./echo` sebelum `./firebase-auth`. Berkas `resources/js/echo.js` (18 baris) menggunakan paket `laravel-echo` (`^2.4.0`) dan `pusher-js` (`^8.6.0`) dari `package.json:14,16`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} else {
    window.Echo = null;
}
```

**Desain Defensive Fail-Safe:** Penjaga `if (import.meta.env.VITE_REVERB_APP_KEY)` sangat penting karena jika developer/penguji belum mengisi `REVERB_APP_KEY` di `.env` sebelum menjalankan `npm run build`, `window.Echo` tidak melempar *uncaught exception* yang merusak Alpine.js/Livewire di halaman lain—melainkan diset ke `null` dan dicek ulang oleh `if (window.Echo)` di `order-track.blade.php:39`.

### 3.3 Kontrak Event `OrderStatusUpdated` dan 6 Titik Pemanggilan Terverifikasi

Kelas `App\Modules\Ordering\Events\OrderStatusUpdated` (`app/Modules/Ordering/Events/OrderStatusUpdated.php:12-47`) mengimplementasikan kontrak **`ShouldBroadcastNow`** (bukan `ShouldBroadcast`), sehingga event langsung disiarkan secara sinkron ke server Reverb pada detik transaksi commit tanpa menunggu antrean queue worker terpisah.

```php
// app/Modules/Ordering/Events/OrderStatusUpdated.php (baris 12-47)
class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Pesanan $pesanan) {}

    public function broadcastOn(): array
    {
        // Cukup 'order.' dan 'tenant.', Laravel otomatis menambahkan prefix 'private-'
        return [
            new PrivateChannel('order.'.$this->pesanan->id_pesanan),
            new PrivateChannel('tenant.'.$this->pesanan->id_client),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        $status = $this->pesanan->status ?? $this->pesanan->status_pesanan;

        return [
            'id_pesanan' => $this->pesanan->id_pesanan,
            'nomor_pesanan' => $this->pesanan->nomor_pesanan,
            'status_pesanan' => $status,
            'status' => $status,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
```

Kontrak siaran `OrderStatusUpdated`:
1. **Saluran Siaran (`broadcastOn()`, baris 18-25):**
   - `new PrivateChannel('order.'.$this->pesanan->id_pesanan)`
   - `new PrivateChannel('tenant.'.$this->pesanan->id_client)`
   *(Catatan teknis commit `62fe550`: Laravel secara otomatis menambahkan prefiks `private-` pada objek `PrivateChannel`, sehingga nama wire channel menjadi `private-order.{id}` dan `private-tenant.{id}`. Mengisi `'private-order.'` ke konstruktor `PrivateChannel` akan menghasilkan prefiks ganda `private-private-order.` — hal yang telah diperbaiki Candi pada commit `62fe550`!)*
2. **Nama Event (`broadcastAs()`, baris 27-30):**
   Mengembalikan `'OrderStatusUpdated'`. Di Laravel Echo klien, event dengan `broadcastAs()` khusus **wajib** didengarkan memakai awalan titik (`.OrderStatusUpdated`), seperti di `order-track.blade.php:41`.
3. **Payload JSON (`broadcastWith()`, baris 35-46):**
   Kedua kunci `status_pesanan` dan `status` dikirim bersamaan demi kompatibilitas penuh antara skema kolom DB `pesanan.status` dan kontrak frontend/test `status_pesanan`.

**6 Titik Pemanggilan Nyata `OrderStatusUpdated` di Kodebase (Terverifikasi):**

| No | Lokasi File & Baris | Konteks Aksi Bisnis | Penanganan Jika Reverb Offline |
|---|---|---|---|
| 1 | `app/Modules/Ordering/Http/Controllers/OrderTrackingController.php:367` | `settlePesananAtomically()` — Pelunasan QRIS via endpoint cek status (`POST .../check-status` atau `/pesanan/{id}/cek-status`) | Bergantung pada `BROADCAST_CONNECTION` (log/reverb). |
| 2 | `app/Modules/Payments/Http/Controllers/PaymentWebhookController.php:89` | `handle()` — Callback webhook pelunasan QRIS (`POST /api/webhooks/qris`) dengan verifikasi signature HMAC/Midtrans & `lockForUpdate` | Langsung `OrderStatusUpdated::dispatch($pesanan)`. |
| 3 | `routes/kasir.php:190` | `POST /kasir/transaksi-walkin` — Kasir membuat transaksi walk-in tunai/QRIS di loket cabang | Dibungkus `try { OrderStatusUpdated::dispatch($pesanan); } catch (Throwable) {}` agar transaksi POS tidak gagal bila Reverb mati. |
| 4 | `routes/kasir.php:266` | `POST /kasir/pesanan/{id}/lunasi` — Kasir melunasi pesanan `menunggu_pembayaran` di loket cabang | Dibungkus `try ... catch (Throwable)` dengan `$pesanan->fresh() ?? $pesanan`. |
| 5 | `routes/tenant.php:117` | `POST /cabang/{client}/pesanan/{id}/progres-status` — Apoteker mengubah status (`diproses`, `siap_diambil`, `dikirim`, `selesai`, dll.) di halaman antrean | Memanggil `event(new OrderStatusUpdated($pesanan))`. |
| 6 | `routes/tenant.php:443` | `POST /cabang/{client}/resep/{id}/verifikasi` — Apoteker menyetujui (`diproses`) atau menolak (`dibatalkan`) resep dokter | Memanggil `event(new OrderStatusUpdated($updatedPesanan))` setelah transaksi DB selesai. |

### 3.4 Analisis Gap Teknis pada Event `IncomingOrderPlaced`

Kelas `App\Modules\Ordering\Events\IncomingOrderPlaced` (`app/Modules/Ordering/Events/IncomingOrderPlaced.php:12-49`, dibuat oleh Candi pada commit `1852240`) mengimplementasikan `ShouldBroadcastNow` ke saluran `PrivateChannel('tenant.'.$this->pesanan->id_client)`. Namun audit langsung pada seluruh berkas `app/` dan `routes/` membuktikan **dua temuan teknis penting**:

1. **Belum Pernah Di-Dispatch:** Pencarian simbol `IncomingOrderPlaced` di seluruh `app/` dan `routes/` hanya menemukan definisi kelasnya sendiri. Event ini **tidak** di-dispatch di `CreateOrderAction.php`, `CheckoutController.php`, maupun `routes/customer.php:116` (`resep.upload`), dan juga belum ada listener Echo pada `resources/views/tenant/dashboard.blade.php` atau `tenant/antrean.blade.php`.
2. **Ketidakcocokan Nama Properti di `broadcastWith()` (`baris 38-47`):**
   - Baris 44 membaca `$this->pesanan->wajib_resep` (kolom `wajib_resep` tidak ada di tabel `pesanan`; flag tersebut ada di `kategori_obat.wajib_resep` atau relasi `$pesanan->resep !== null`).
   - Baris 45 membaca `$this->pesanan->status_pesanan` padahal kolom nyata pada tabel `pesanan` adalah `status` (lihat `OrderStatusUpdated.php:37` yang sudah menggunakan fallback `$this->pesanan->status ?? $this->pesanan->status_pesanan`).

Apabila fitur notifikasi *real-time* antrean masuk ke dasbor apoteker akan diaktifkan penuh, `IncomingOrderPlaced::broadcastWith()` perlu diselaraskan (`$this->pesanan->status ?? $this->pesanan->status_pesanan`, dan `$this->pesanan->resep()->exists()`) lalu di-dispatch di `CreateOrderAction` / `resep.upload` dan disubscribe di view tenant via `window.Echo.private('tenant.' + clientId)`.

### 3.5 Otorisasi Saluran Privat di `routes/channels.php`

Berkas `routes/channels.php` (53 baris) mengamankan saluran WebSocket Reverb terhadap *eavesdropping* lintas pelanggan maupun lintas cabang (termasuk dukungan akses tamu anonim yang ditambahkan bersama commit `d3bb1b7`):

```php
// routes/channels.php (baris 12-53)
Broadcast::channel('order.{id_pesanan}', function (?User $user, string $idPesanan) {
    $pesanan = Pesanan::withoutGlobalScopes()->where('id_pesanan', $idPesanan)->first();
    if (! $pesanan) {
        return false;
    }
    if ($user) {
        $userEmail = $user->email;
        $idPengguna = $user->id_pengguna ?? ($userEmail ? Pengguna::where('telepon_email', $userEmail)->value('id_pengguna') : null);
        if ($idPengguna && $pesanan->id_pengguna === $idPengguna) {
            return true;
        }
    }
    $sessionToken = request()->cookie('omnipharma_customer_session');
    if ($sessionToken && $pesanan->token_sesi === $sessionToken) {
        return true;
    }
    return false;
});
```

1. **Saluran Default User (`baris 8-10`):**
   `App.Models.User.{id}` memeriksa `(int) $user->id === (int) $id`.
2. **Saluran Pelacakan Pesanan `order.{id_pesanan}` (`baris 12-33`):**
   - Parameter `?User $user` bersifat *nullable* agar pelanggan tamu (*guest*) tetap bisa terotorisasi melalui endpoint `/broadcasting/auth`.
   - Mengambil pesanan dengan `Pesanan::withoutGlobalScopes()->where('id_pesanan', $idPesanan)->first()` karena request `/broadcasting/auth` tidak membawa parameter rute `{client}` (`TenantContext` kosong).
   - **Jalur 1 — Pengguna Terautentikasi (`baris 19-25`):** Mengambil `$idPengguna` dari `$user->id_pengguna` atau lookup `Pengguna::where('telepon_email', $userEmail)->value('id_pengguna')`, lalu mencocokkan `$pesanan->id_pengguna === $idPengguna`.
   - **Jalur 2 — Pelanggan Tamu/Anonim (`baris 27-30`):** Membaca cookie `request()->cookie('omnipharma_customer_session')` dan mencocokkannya dengan `$pesanan->token_sesi` (kolom yang ditambahkan oleh migrasi `2026_11_09_000001`).
   - Jika keduanya tidak cocok -> mengembalikan `false` (HTTP 403 dari `/broadcasting/auth`).
3. **Saluran Dasbor Apoteker Cabang `tenant.{id_client}` (`baris 35-53`):**
   - Wajib terautentikasi (`User $user`, non-null).
   - Mengizinkan `super_admin` atau `apoteker` via `$user->hasRole('super_admin') || $user->hasRole('apoteker')` (baris 36-38).
   - Lookup cadangan pada model `Pengguna` by `$user->email`: mengizinkan jika `$pengguna->peran === 'super_admin'`, atau memeriksa relasi penugasan aktif `$pengguna->peranClients()->where('id_client', $idClient)->where('aktif', true)->exists()` (baris 40-50).
   - **Catatan Audit Keamanan:** Perhatikan bahwa kondisi baris 36 (`$user->hasRole('apoteker')`) mengembalikan `true` sebelum mengecek `$idClient` di baris 46! Artinya di level channel Reverb `tenant.{id_client}`, apoteker cabang A secara teoretis bisa lolos otorisasi channel `tenant.{id_cabang_B}` jika hanya mengandalkan baris 36—meskipun di sisi HTTP portal `/cabang/{client}` sudah diblokir ketat oleh `ResolveTenantContext.php:66-111`. Ini menjadi catatan *hardening* lanjutan pada Bab 7.

### 3.6 Bedah Teknis Antarmuka `resources/views/customer/order-track.blade.php` & `OrderTrackingController`

Halaman pelacakan real-time (`resources/views/customer/order-track.blade.php`, 364 baris) dilayani oleh `OrderTrackingController::track()` (`app/Modules/Ordering/Http/Controllers/OrderTrackingController.php:263-294`) melalui dua rute:
- `GET /klien/{client}/order/{id}/track` (`customer.order.track`, `routes/customer.php:249`)
- `GET /lacak-pesanan/{id}` (`pesanan.track`) dan `GET /pesanan/{id}/lacak` (`pesanan.lacak`, `routes/web.php:179-180`)

**Alur Lengkap 5 Metode pada `OrderTrackingController` (`app/Modules/Ordering/Http/Controllers/OrderTrackingController.php`, 372 baris):**
1. **`payment()` (`baris 28-55`):** Mengambil pesanan beserta relasi `items.obat.kategori`, `pembayaran`, dan `client`, memblokir akses IDOR silang cabang (`/klien/*`) dengan 404, memanggil `ensureCustomerOrderAccess($pesanan, $request, false)`, menyinkronkan `TenantContext`, lalu menghasilkan QRIS dinamis melalui `$gateway->generateQris($pesanan)` untuk view `customer.payment`.
2. **`checkStatus()` (`baris 60-166`):** Endpoint JSON polling/konfirmasi pembayaran QRIS:
   - Jika status sudah masuk `['menunggu_verifikasi', 'diproses', 'siap_diambil', 'dikirim', 'selesai']` -> langsung mengembalikan `paid: true` + `$trackUrl` (baris 88-95).
   - Jika `status === 'dibatalkan'` -> mengembalikan `paid: false, cancelled: true` (baris 97-104).
   - Jika `config('services.midtrans.server_key')` terisi dan `$gateway instanceof FakeQrisGateway` -> memanggil Midtrans Sandbox Status API `checkTransactionStatus($midtransOrderId)`; saat status `settlement`/`capture`, memanggil `settlePesananAtomically()` (baris 109-124).
   - Pada fallback mode lokal tanpa server key Midtrans: jika dipanggil otomatis oleh timer (`auto_timer=1` & belum `timer_expired`), tidak otomatis melunaskan; hanya melunaskan atomik saat user menekan tombol konfirmasi manual (baris 144-165).
3. **`settlePesananAtomically()` (`baris 334-371`):** Mengunci record `pesanan` via `lockForUpdate()`, mengecek status masih `'menunggu_pembayaran'` (idempotensi), mempertahankan status `'menunggu_verifikasi'` bila pesanan memiliki resep berstatus `'menunggu'` (atau `'diproses'` jika tidak ada resep tertunda), membuat baris `Pembayaran` lunas, lalu memancarkan `OrderStatusUpdated::dispatch($locked)` pada baris 367.
4. **`cancel()` (`baris 232-258`):** Memastikan kepemilikan via `ensureCustomerOrderAccess($pesanan, $request, true)` (mutasi tamu wajib memiliki `token_sesi` cocok), lalu mengeksekusi `CancelOrderAction` untuk membatalkan pesanan dan mengembalikan stok fisik cabang.
5. **`track()` (`baris 263-294`):**
   - Eager loading pesanan lintas-tenancy untuk mendukung link langsung `/lacak-pesanan/{id}`: `Pesanan::withoutTenancy()->with(['items.obat.kategori', 'pembayaran', 'client', 'pengguna'])->find($id)` (baris 265).
   - Jika rute berada di bawah `/klien/*` dan `$pesanan->id_client !== $tenantContext->id()` -> `abort(404)` (pertahanan IDOR lintas cabang, baris 271-273).
   - Memanggil `$this->ensureCustomerOrderAccess($pesanan, $request, false)` (baris 275, definisi di baris 300-329): bagi user ber-role `pelanggan`, mencocokkan kepemilikan `id_pengguna` atau `token_sesi` dari cookie `omnipharma_customer_session` / `omni_session` / session ID Laravel (abort 403 jika milik pelanggan lain).
   - Sinkronisasi `TenantContext` bila diakses lewat short route `/lacak-pesanan/{id}` (baris 277-280).
   - Menghitung metrik antrean virtual dari Cache (`antrean_berjalan:{$pesanan->id_client}`, default `'A-001'`), mengekstrak digit numerik `nomor_antrean` pasien dikurangi antrean berjalan menjadi `$sisaAntrean = max(0, $nomorSaya - $nomorAktif)` (baris 282-286).

**Troubleshooting Operasional WebSocket Reverb (`M12-RT`):**
- *Gejala 1 — Koneksi WS gagal di browser (`ERR_CONNECTION_REFUSED` ke `:8080`):* Pastikan `php artisan reverb:start --host=0.0.0.0 --port=8080` aktif dan nilai `REVERB_APP_KEY` di `.env` sudah terkompilasi ke bundle Vite (`npm run build` / `npm run dev`), karena `resources/js/echo.js:6` hanya menginisialisasi `Echo` jika `VITE_REVERB_APP_KEY` tidak kosong.
- *Gejala 2 — `/broadcasting/auth` mengembalikan 403 untuk pelanggan tamu:* Pastikan cookie `omnipharma_customer_session` terpasang di browser (`routes/customer.php:21-37`) dan nilainya identik dengan kolom `pesanan.token_sesi`.
- *Gejala 3 — Event terkirim di log Reverb tetapi listener UI tidak bereaksi:* Pastikan di sisi JS memakai titik di depan nama event (`.listen('.OrderStatusUpdated', ...)`), karena `OrderStatusUpdated::broadcastAs()` mengembalikan `'OrderStatusUpdated'` (tanpa namespace PHP) dan di `PrivateChannel` cukup ditulis `'order.'` tanpa prefiks `'private-'`.

**Arsitektur Frontend `order-track.blade.php`:**
1. **Komponen Alpine `window.omniOrderTrackerData(config)` (baris 29-56):**
   ```javascript
   window.omniOrderTrackerData = function(config) {
       return {
           currentStatus: config.initialStatus,
           steps: [
               { key: 'menunggu_pembayaran', label: 'Menunggu Bayar' },
               { key: 'diproses', label: 'Diproses Apotek' },
               { key: 'siap_diambil', label: config.thirdStepLabel || 'Siap Diambil' },
               { key: 'selesai', label: 'Selesai' }
           ],
           init() {
               if (window.Echo) {
                   window.Echo.private('order.' + config.orderId)
                       .listen('.OrderStatusUpdated', (e) => {
                           this.currentStatus = e.status_pesanan || e.status;
                       });
               }
           },
           isPassed(statusKey) {
               const flow = ['menunggu_pembayaran', 'diproses', 'siap_diambil', 'selesai'];
               const normalized = this.currentStatus === 'dikirim' ? 'siap_diambil' : this.currentStatus;
               return flow.indexOf(normalized) > flow.indexOf(statusKey);
           },
           isCurrent(statusKey) {
               const normalized = this.currentStatus === 'dikirim' ? 'siap_diambil' : this.currentStatus;
               return normalized === statusKey;
           }
       };
   };
   ```
   - Menerima `orderId`, `initialStatus`, dan `thirdStepLabel` (`'Siap / Dikirim'` untuk delivery, `'Siap Diambil'` untuk click & collect).
   - Pada `init()`, jika `window.Echo` tersedia, berlangganan `window.Echo.private('order.' + config.orderId).listen('.OrderStatusUpdated', ...)` (baris 39-44).
   - Method `isPassed(statusKey)` & `isCurrent(statusKey)` (baris 46-54) memetakan alur 4 tahap `['menunggu_pembayaran', 'diproses', 'siap_diambil', 'selesai']` serta menormalisasi status `'dikirim'` ke langkah ke-3 (`'siap_diambil'`).
2. **Banner Peringatan Belum Lunas (baris 101-111):** Muncul dinamis saat `currentStatus === 'menunggu_pembayaran'` dengan tautan balik ke `$paymentUrl`.
3. **Kartu Tiket Antrean Virtual (baris 117-167):** Menampilkan badge nomor antrean (`A-001`, dll.), `Antrean Berjalan`, `Sisa Giliran` (`Giliran Anda` bila `<= 0`), `Estimasi Siap` (`~max(5, sisa * 5) Menit`), dan `slot_waktu`.
4. **Stepper Progres 4-Tahap Real-Time (baris 170-201):** Responsif vertikal di mobile dan horizontal di desktop (`sm:flex-row`) dengan transisi visual saat `currentStatus` berubah via WebSocket tanpa reload.
5. **Panel Rute Kurir Google Maps (baris 204-247):** Ditampilkan khusus jika `metode_layanan === 'delivery' && $pesanan->maps_route_url`, menyertakan badge jarak (`jarak_km`), tombol buka rute Google Maps, dan iframe embed peta `maps.google.com/maps?q=...&output=embed`.
6. **Kartu Invoice Resmi Apotek (baris 251-361):** Sticky di desktop, mendukung cetak bersih (`print:hidden` pada kontrol navigasi), menampilkan snapshot `item_pesanan`, subtotal obat, ongkir kurir (`GRATIS` jika `0`), total pembayaran, `catatan_bersih`, dan tombol `Cetak Invoice` (`window.print()`).

---

## Bab 4. Pilar 2 (M13) — Analitik Penjualan, Snapshot Immutability ADR-0008, & Ekspor Laporan

### 4.1 Fondasi Snapshot Akuntansi pada `RingkasanPeriodePenjualan` & Migrasi `2026_09_21_000105`

Dalam bisnis apotek, harga obat di tabel master `obat` dan nama gerai di `client` dapat berubah sewaktu-waktu. Sesuai **ADR-0008** (`docs/adr/0008-controlled-denormalization-and-price-snapshot.md`), sistem menerapkan dua lapis *Controlled Denormalization & Snapshot Immutability*:
1. Lapisan transaksi (`item_pesanan`) menyimpan snapshot `nama_obat`, `harga_satuan`, dan `subtotal` saat pesanan dibuat.
2. Lapisan agregasi periode (`ringkasan_periode_penjualan`) membekukan rekapitulasi cabang pada rentang tanggal tertentu sehingga laporan akuntansi yang telah ditutup kebal terhadap fluktuasi harga atau mutasi data di masa depan.

Skema fisik tabel `ringkasan_periode_penjualan` di `database/migrations/2026_09_21_000105_create_apotek_reporting_and_audit_tables.php:15-30`:
- `id_ringkasan` (UUID PK), `id_client` (FK UUID -> `client.id_client` cascadeOnDelete)
- `nama_client` (`string(150)` — snapshot nama cabang saat penutupan buku)
- `total_obat_terjual` (`string(50)` default `'0'`)
- `periode_mulai` (`date`), `periode_selesai` (`date`)
- `total_pesanan` (`integer`), `total_pendapatan` (`decimal(15, 2)`), `total_retur` (`integer`)
- `status_terkunci` (`boolean` default `false`), `dibuat_pada` (`timestamp`)
- **Constraint Unik Database (baris 29):** `$table->unique(['id_client', 'periode_mulai', 'periode_selesai'], 'uq_ringkasan_client_periode')`.

Model `App\Models\RingkasanPeriodePenjualan` (`app/Models/RingkasanPeriodePenjualan.php`, 87 baris) memakai trait `BelongsToTenant`, `HasFactory`, `HasUuids`, `$timestamps = false`, casts `decimal:2` dan `boolean`, serta `scopeTerkunci()` (baris 83-86) untuk memfilter `where('status_terkunci', true)`.

### 4.2 Agregasi Metrik Dinamis dari `item_pesanan` pada `SalesReportService`

Pada `app/Modules/Reporting/Services/SalesReportService.php` (59 baris), Candi menyelesaikan masalah penting (tercatat pada commit `42be2fa`): tabel `pesanan` tidak menyimpan kolom fisik `total_harga`, karena harga aktual per item berada di `item_pesanan.subtotal`. Berikut implementasi terverifikasinya:

```php
// app/Modules/Reporting/Services/SalesReportService.php (baris 16-58)
public function getSummary(string $clientId, string $periodeMulai, string $periodeSelesai): array
{
    $orders = Pesanan::withoutGlobalScopes()
        ->where('id_client', $clientId)
        ->whereIn('status', ['diproses', 'siap_diambil', 'selesai'])
        ->whereBetween('dibuat_pada', [$periodeMulai.' 00:00:00', $periodeSelesai.' 23:59:59'])
        ->get();

    $orderIds = $orders->pluck('id_pesanan');

    $itemsByOrder = DB::table('item_pesanan')
        ->whereIn('id_pesanan', $orderIds)
        ->get()
        ->groupBy('id_pesanan');

    $totalPendapatan = 0;
    $totalObatTerjual = 0;

    foreach ($orders as $order) {
        $orderItems = $itemsByOrder->get($order->id_pesanan, collect());
        $subtotal = (float) $orderItems->sum('subtotal');
        $kuantitas = (int) $orderItems->sum('kuantitas');

        $order->setAttribute('total_harga', $subtotal);
        $totalPendapatan += $subtotal;
        $totalObatTerjual += $kuantitas;
    }

    $totalPesanan = $orders->count();

    return [
        'total_pendapatan' => (float) $totalPendapatan,
        'total_omzet' => (float) $totalPendapatan,
        'total_pesanan' => $totalPesanan,
        'total_transaksi' => $totalPesanan,
        'total_obat_terjual' => $totalObatTerjual,
        'rata_rata_transaksi' => $totalPesanan > 0 ? (float) ($totalPendapatan / $totalPesanan) : 0,
        'daftar_pesanan' => $orders,
    ];
}
```

Alur langkahnya:
1. Mengambil seluruh pesanan cabang pada rentang `dibuat_pada` (`00:00:00` s/d `23:59:59`) dengan status sah **`['diproses', 'siap_diambil', 'selesai']`** via `Pesanan::withoutGlobalScopes()` (baris 18-22). *(Pesanan `menunggu_pembayaran`, `menunggu_verifikasi`, atau `dibatalkan` dikecualikan dari omzet akuntansi.)*
2. Mengambil seluruh baris `item_pesanan` yang berelasi menggunakan `DB::table('item_pesanan')->whereIn('id_pesanan', $orderIds)->get()->groupBy('id_pesanan')` (baris 27-30) — menghindari *N+1 query*.
3. Untuk setiap `$order`, menghitung `$subtotal = (float) $orderItems->sum('subtotal')` dan `$kuantitas = (int) $orderItems->sum('kuantitas')`, lalu menyematkan `$order->setAttribute('total_harga', $subtotal)` (baris 35-45) agar objek model di `daftar_pesanan` kompatibel langsung dengan view Blade & template PDF.
4. Mengembalikan array metrik terstruktur (baris 49-57) yang menjaga pembagian nol (`$totalPesanan > 0 ? ... : 0`).

> **Catatan Audit Teknis:** `SalesReportService::getSummary()` mengagregasi `item_pesanan.subtotal` (omzet penjualan produk obat) dan **tidak** memasukkan komponen `[ONGKIR:xxx]` dari `pesanan.catatan_pesanan`. Ini konsisten antara `SalesReportService`, `CloseSalesPeriodAction`, dan `SalesReportExportService`.

### 4.3 Mekanisme Penutupan Periode Terkunci pada `CloseSalesPeriodAction` (ADR-0008)

Kelas `App\Modules\Reporting\Actions\CloseSalesPeriodAction` (`app/Modules/Reporting/Actions/CloseSalesPeriodAction.php`, 65 baris) mengeksekusi penutupan buku secara atomik di dalam `DB::transaction()` (baris 16-63):

```php
// app/Modules/Reporting/Actions/CloseSalesPeriodAction.php (baris 14-64)
public function execute(string $clientId, string $periodeMulai, string $periodeSelesai): RingkasanPeriodePenjualan
{
    return DB::transaction(function () use ($clientId, $periodeMulai, $periodeSelesai) {
        $existing = RingkasanPeriodePenjualan::where('id_client', $clientId)
            ->where('periode_mulai', $periodeMulai)
            ->where('periode_selesai', $periodeSelesai)
            ->first();

        if ($existing && $existing->status_terkunci) {
            throw new RuntimeException('Periode penjualan ini sudah pernah ditutup dan berstatus terkunci.');
        }

        $client = Client::withoutGlobalScopes()->where('id_client', $clientId)->first();
        $namaClient = $client ? $client->nama_client : 'Cabang Apotek';

        $orders = Pesanan::withoutGlobalScopes()
            ->where('id_client', $clientId)
            ->whereIn('status', ['diproses', 'siap_diambil', 'selesai'])
            ->whereBetween('dibuat_pada', [$periodeMulai.' 00:00:00', $periodeSelesai.' 23:59:59'])
            ->get();

        $orderIds = $orders->pluck('id_pesanan');

        $items = DB::table('item_pesanan')
            ->whereIn('id_pesanan', $orderIds)
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_pendapatan, COALESCE(SUM(kuantitas), 0) as total_obat_terjual')
            ->first();

        return RingkasanPeriodePenjualan::updateOrCreate(
            ['id_client' => $clientId, 'periode_mulai' => $periodeMulai, 'periode_selesai' => $periodeSelesai],
            [
                'id_ringkasan' => (string) Str::uuid(),
                'nama_client' => $namaClient,
                'total_obat_terjual' => (int) ($items->total_obat_terjual ?? 0),
                'total_pesanan' => $orders->count(),
                'total_pendapatan' => (float) ($items->total_pendapatan ?? 0),
                'total_retur' => 0,
                'status_terkunci' => true,
                'dibuat_pada' => now(),
            ]
        );
    });
}
```

Rincian langkah penutupan periode pada kode di atas:
1. **Idempotency & Immutability Guard (baris 17-24):**
   Mencari `RingkasanPeriodePenjualan` dengan `(id_client, periode_mulai, periode_selesai)`. Jika record sudah ada dan `$existing->status_terkunci` bernilai `true`, langsung melempar `RuntimeException('Periode penjualan ini sudah pernah ditutup dan berstatus terkunci.')`.
2. **Snapshot Identitas Cabang (baris 26-27):**
   Mengambil `Client::withoutGlobalScopes()->where('id_client', $clientId)->first()` untuk membekukan `$namaClient` saat penutupan buku.
3. **Agregasi Level SQL (baris 29-44):**
   Mengambil pesanan berstatus `['diproses', 'siap_diambil', 'selesai']` pada rentang `dibuat_pada`, lalu menjalankan query agregasi `SUM(subtotal)` dan `SUM(kuantitas)` di `item_pesanan` dengan `COALESCE(..., 0)` agar aman ketika periode memiliki 0 pesanan.
4. **Persistensi Snapshot Terkunci (baris 46-62):**
   Menyimpan via `RingkasanPeriodePenjualan::updateOrCreate(...)` dengan `'status_terkunci' => true`, `'total_retur' => 0`, dan `'dibuat_pada' => now()`. Kombinasi pengecekan aplikasi + constraint unik DB `uq_ringkasan_client_periode` memastikan periode yang sama tidak pernah dapat dikunci ulang atau ditimpa.

### 4.4 Layanan Ekspor CSV & Proteksi Formula Injection pada `SalesReportExportService`

Kelas `App\Modules\Reporting\Services\SalesReportExportService` (`app/Modules/Reporting/Services/SalesReportExportService.php`, 74 baris) menyediakan metode:
1. **`exportCsv(string $clientId, string $mulai, string $selesai): StreamedResponse` (baris 14-61):**
   - Menghasilkan header HTTP `Content-Type: text/csv; charset=utf-8` dan `Content-Disposition: attachment; filename="laporan-penjualan-{$clientId}-{$mulai}-{$selesai}.csv"`.
   - Menulis langsung ke `php://output` (streaming hemat memori tanpa menyimpan file sementara di disk).
   - Header kolom CSV (baris 25): `['Nomor Pesanan', 'Tanggal', 'Metode Layanan', 'Status', 'Total Harga (Rp)']`.
   - Mengambil seluruh pesanan cabang pada rentang tanggal `dibuat_pada` dan melakukan agregasi subtotal per `id_pesanan` via `DB::table('item_pesanan')->groupBy('id_pesanan')->pluck('total', 'id_pesanan')`.
2. **`sanitizeForCsv(string $value): string` (baris 66-73):**
   ```php
   public function sanitizeForCsv(string $value): string
   {
       if (in_array(substr($value, 0, 1), ['=', '+', '-', '@'])) {
           return "'".$value;
       }

       return $value;
   }
   ```
   Jika sel diawali karakter pemicu eksekusi formula/DDE di Microsoft Excel atau LibreOffice Calc (`=`, `+`, `-`, `@`), fungsi otomatis menambahkan awalan apostrof `'` sehingga sel diperlakukan sebagai teks murni. Contoh keluaran proteksi:
   - Input `=cmd|'/C calc'!A0` -> Output `'=cmd|'/C calc'!A0`
   - Input `=SUM(A1:A10)` -> Output `'=SUM(A1:A10)`
   - Input `+12345` -> Output `'+12345`
   - Input `-100` -> Output `'-100`
   - Input `@payload` -> Output `'@payload`
   - Input aman `ORD-20261107-001` -> Output tetap `ORD-20261107-001`
   *(Catatan pengayaan di Bab 7: `sanitizeForCsv` baru diterapkan pada `$order->nomor_pesanan` di baris 41 dan belum meng-cover karakter `\t` / `\r` atau field `metode`/`status`, walau kedua field tersebut terkontrol enum internal.)*

### 4.5 Rute dan Otorisasi Pelaporan di Portal Tenant (`routes/tenant.php`) dan Super Admin (`routes/admin.php`)

Fitur pelaporan Candi diekspos pada dua portal internal yang dilindungi `auth`, `verified`, dan `role`:

1. **Portal Apoteker Cabang (`routes/tenant.php:517-560`, prefix `/cabang/{client}`, middleware `role:apoteker` + `tenant`):**
   - `GET /cabang/{client}/laporan` (`tenant.laporan.index`, baris 517-544):
     - Default rentang tanggal: `now()->startOfMonth()->toDateString()` s/d `now()->toDateString()`.
     - Jika `?export=csv`, langsung mengembalikan `StreamedResponse` dari `SalesReportExportService::exportCsv()`.
     - Jika tidak, memanggil `SalesReportService::getSummary()`, mengecek flag terkunci pada `RingkasanPeriodePenjualan`, dan me-render view `admin.laporan-penjualan`.
   - `POST /cabang/{client}/laporan/tutup-periode` (`tenant.laporan.tutup-periode`, baris 546-560):
     - Memanggil `CloseSalesPeriodAction::execute()`, mengembalikan flash `success` atau `error` jika `RuntimeException` dilempar (periode sudah terkunci).
2. **Portal Super Admin Pusat (`routes/admin.php:97-169`, prefix `/admin`, middleware `role:super_admin`):**
   - `POST /admin/tutup-periode` (`admin.periode.tutup`, baris 97-141):
     - Dapat menutup satu cabang spesifik (`id_client`) atau seluruh cabang aktif sekaligus, mengagregasi `omzet_terkunci` dan `total_pesanan` lalu mengembalikan respons JSON terstruktur (`status_terkunci: true`).
   - `GET /admin/ekspor-laporan` (`admin.laporan.ekspor`, baris 143-169):
     - Jika query berisi `download` atau `format=csv`, langsung melakukan streaming `exportCsv()`. Jika tidak, mengembalikan JSON metadata berisi `download_url` dan `filename`.
   - Selain itu, `GET /admin/dashboard` (`routes/admin.php:29-95`) juga menampilkan daftar `RingkasanPeriodePenjualan` serta tombol cepat tutup periode & ekspor laporan konsolidasi.

### 4.6 Antarmuka `admin/laporan-penjualan.blade.php` dan Audit Gap Ekspor PDF (`laporan-penjualan-template.blade.php`)

- **Antarmuka Analitik (`resources/views/admin/laporan-penjualan.blade.php`, 178 baris):**
  Menggunakan layout `<x-layouts.tenant>` (dan menampilkan tautan balik `&larr; Pusat Admin` khusus jika pengguna adalah `super_admin` di baris 24-28). Menyediakan filter tanggal `periode_mulai` s/d `periode_selesai`, 3 kartu KPI (`Total Pendapatan Penjualan`, `Total Volume Pesanan`, `Rata-rata Keranjang (AOV)`), indikator status `PERIODE TERKUNCI (IMMUTABLE)` vs `PERIODE TERBUKA (OPEN)`, tombol `Ekspor CSV` (`?export=csv`), tombol `Tutup & Kunci Periode Buku` dengan konfirmasi JS, serta daftar rincian transaksi yang responsif (*mobile card view* `md:hidden` + *desktop table* `hidden md:block`).
- **Template Cetak PDF (`resources/views/admin/pdf/laporan-penjualan-template.blade.php`, 73 baris) — Bedah Struktur & Temuan Gap Nyata:**
  Berkas template HTML/CSS formal siap cetak A4 tersedia lengkap dengan tabel 6 kolom (`No`, `Nomor Pesanan`, `Waktu Transaksi`, `Layanan`, `Status`, `Total Transaksi`), badge hijau `.badge-success`, blok ringkasan `tfoot` (`$totalPendapatan ?? $totalOmzet ?? 0`), serta footer pengesahan digital ADR-0008. Variabel Blade yang diharapkan template ini adalah: `$periodeMulai`, `$periodeSelesai`, `$orders` (masing-masing item memiliki `nomor_pesanan`, `dibuat_pada`, `metode_layanan`, `status`/`status_pesanan`, `total_harga` hasil injeksi `SalesReportService::getSummary()`), dan `$totalPendapatan` / `$totalOmzet`. **Namun**, inspeksi langsung terhadap `composer.json`, `SalesReportExportService.php`, `routes/tenant.php`, dan `routes/admin.php` membuktikan bahwa:
  1. Tidak ada library PDF (`barryvdh/laravel-dompdf` maupun `mpdf`/`snappy`) yang terpasang di `composer.json`.
  2. `SalesReportExportService` hanya memiliki metode `exportCsv()` dan `sanitizeForCsv()`, belum memiliki `exportPdf()`.
  3. Tidak ada rute yang me-render `admin.pdf.laporan-penjualan-template` (pencarian `laporan-penjualan-template` di seluruh `app/` dan `routes/` menghasilkan 0 kecocokan).
  4. Baris 74 di `admin/laporan-penjualan.blade.php` mengakses `$ringkasanTerkunci->dikunci_pada ?? $ringkasanTerkunci->created_at`, padahal di `routes/tenant.php:531-535` variabel `$ringkasanTerkunci` dikirim sebagai **`bool`** (`->exists()`), bukan model Eloquent! Karena `$ringkasanTerkunci` bertipe boolean `true` saat terkunci, di PHP ekspresi `$ringkasanTerkunci->dikunci_pada ?? ...` masih berjalan tanpa crash (mengembalikan `null` lalu `Carbon::parse(null)` = waktu sekarang), namun tanggal penguncian yang tampil di badge UI bukan timestamp aktual `dibuat_pada` dari DB! Temuan halus ini sangat berharga untuk diperbaiki Candi.

Alur lengkap M13 dari query transaksi hingga kunci periode buku & ekspor aman:

```text
[Request GET /cabang/{client}/laporan]
   ├─► ?export=csv ──► SalesReportExportService::exportCsv($clientId, $mulai, $selesai)
   │                      ├─► Query Pesanan::withoutGlobalScopes() + SUM(item_pesanan.subtotal)
   │                      ├─► sanitizeForCsv($nomorPesanan): awalan '=', '+', '-', '@' -> prefiks "'"
   │                      └─► StreamedResponse (text/csv; charset=utf-8) langsung ke php://output
   └─► Tampilan HTML ─► SalesReportService::getSummary($clientId, $mulai, $selesai)
                          ├─► Filter status IN ('diproses', 'siap_diambil', 'selesai')
                          ├─► Agregasi SUM(subtotal) & SUM(kuantitas) dari item_pesanan
                          └─► Render resources/views/admin/laporan-penjualan.blade.php

[Request POST /cabang/{client}/laporan/tutup-periode atau POST /admin/tutup-periode]
   └─► CloseSalesPeriodAction::execute($clientId, $mulai, $selesai) inside DB::transaction()
          ├─► Cek RingkasanPeriodePenjualan existing -> jika status_terkunci == true -> throw RuntimeException
          ├─► Hitung COALESCE(SUM(subtotal), 0) & COALESCE(SUM(kuantitas), 0) dari item_pesanan
          └─► updateOrCreate dengan status_terkunci = true + DB unique constraint uq_ringkasan_client_periode
```

---

## Bab 5. Pilar 3 (M14) — Security Hardening, Validasi Magic Bytes, Sanitasi XSS, & Quality Gate

### 5.1 Middleware Global `SecurityHeadersMiddleware` dan Konfigurasi Content-Security-Policy (Dev vs Prod)

Pada commit `fb13c90` (8 Nov 2026), Candi mengimplementasikan `App\Http\Middleware\SecurityHeadersMiddleware` (`app/Http/Middleware/SecurityHeadersMiddleware.php`, 56 baris) dan mendaftarkannya secara **global** untuk seluruh respons HTTP Laravel di `bootstrap/app.php:33`:
```php
$middleware->append(SecurityHeadersMiddleware::class);
```

Enam header keamanan statis + satu kebijakan CSP dinamis disuntikkan ke setiap respons:

| Header HTTP | Nilai yang Ditetapkan | Fungsi Perlindungan |
|---|---|---|
| `X-Frame-Options` | `DENY` | Mencegah serangan *Clickjacking* (halaman tidak dapat disematkan di `<iframe>` situs eksternal). |
| `X-Content-Type-Options` | `nosniff` | Memaksa browser mematuhi MIME type asli server dan menolak *MIME sniffing*. |
| `X-XSS-Protection` | `1; mode=block` | Mengaktifkan filter XSS lawas pada browser kompatibel. |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Mencegah kebocoran query string/UUID pasien pada header `Referer` ke domain luar. |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Membatasi akses perangkat keras kamera, mikrofon, dan geolokasi dari konteks frame/script tak sah. |
| `Cross-Origin-Opener-Policy` | `same-origin-allow-popups` | Isolasi proses tab browser sambil tetap mengizinkan popup OAuth Google/Firebase berkomunikasi dengan jendela pembuka (`FirebaseAuthTest.php`). |
| `Content-Security-Policy` | Kebijakan terkurasi per environment | Membatasi asal eksekusi skrip, gaya, font, gambar, frame, dan koneksi WebSocket/API. |

**Mekanisme CSP Dinamis (`SecurityHeadersMiddleware.php:37-52`):**
- Memeriksa `$isLocal = app()->isLocal() || config('app.debug');`.
- Jika lokal/debug: mengizinkan koneksi loopback dengan port aktif (`http://localhost:{port}`, `http://127.0.0.1:{port}`) dan Vite HMR dev server (`http/ws://localhost:5173`, `http/ws://127.0.0.1:5173`).
- Direktif `connect-src` secara eksplisit membuka:
  - `'self'`
  - WebSocket Laravel Reverb: `ws://127.0.0.1:8080 wss://127.0.0.1:8080 ws://localhost:8080 wss://localhost:8080`
  - Firebase & Google OAuth: `https://*.googleapis.com https://*.firebaseio.com https://*.firebaseapp.com https://*.firebase.google.com https://*.google.com https://accounts.google.com`
- Direktif `script-src` mengizinkan `'self' 'unsafe-inline' 'unsafe-eval'` (kebutuhan Alpine.js/Livewire), `https://cdn.jsdelivr.net`, `https://apis.google.com`, `https://www.gstatic.com`.
- Direktif `frame-src` mengizinkan `'self' https://*.firebaseapp.com https://accounts.google.com`. *(Catatan untuk tampilan `order-track.blade.php:237` yang memakai `<iframe src="https://maps.google.com/maps?...">`: domain `maps.google.com` belum masuk `frame-src`, sehingga di browser ketat iframe embed Maps berpotensi diblokir oleh CSP `frame-src` jika tidak ditambahkan `https://maps.google.com https://www.google.com` — catat di Bab 7!)*

Ringkasan lengkap perbedaan direktif CSP antara mode **Development/Debug (`$isLocal = true`)** dan **Production (`$isLocal = false`)** pada `SecurityHeadersMiddleware.php:37-52`:

| Direktif CSP | Mode Produksi (`APP_DEBUG=false`) | Tambahan Saat Lokal/Debug (`app()->isLocal() \|\| config('app.debug')`) |
|---|---|---|
| `default-src` | `'self'` | — |
| `script-src` | `'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://apis.google.com https://www.gstatic.com` | `http://localhost:5173 http://127.0.0.1:5173` (Vite HMR) |
| `style-src` | `'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com` | `http://localhost:5173 http://127.0.0.1:5173` |
| `font-src` | `'self' https://fonts.bunny.net https://fonts.gstatic.com data:` | — |
| `img-src` | `'self' data: https:` | `http://localhost:{port} http://127.0.0.1:{port}` |
| `connect-src` | `'self' ws://127.0.0.1:8080 wss://127.0.0.1:8080 ws://localhost:8080 wss://localhost:8080` + Firebase/Google Auth (`*.googleapis.com`, `*.firebaseio.com`, `*.firebaseapp.com`, `*.firebase.google.com`, `*.google.com`, `accounts.google.com`) | `http://localhost:{port}`, `http://127.0.0.1:{port}`, plus Vite `http/ws://localhost:5173` & `127.0.0.1:5173` |
| `frame-src` | `'self' https://*.firebaseapp.com https://accounts.google.com` | — |

### 5.2 Manajemen Rate Limiter (`AppServiceProvider` vs Throttle Aktual `routes/web.php` & `FortifyServiceProvider`)

Pada commit `30cc5ad`, Candi mendaftarkan 3 definisi `RateLimiter` di `app/Providers/AppServiceProvider.php:39-52`:

1. **`api` (baris 40-42):** `Limit::perMinute(60)->by($request->user()->id_pengguna ?? $request->ip())`
2. **`broadcasting` (baris 45-47):** `Limit::perMinute(30)->by($request->ip())`
3. **`transaksi` (baris 50-52):** `Limit::perMinute(15)->by($request->user()->id_pengguna ?? $request->ip())`

**Audit Pemakaian Aktual Rate Limiter di Seluruh Kodebase:**
- `SecurityHardeningTest.php:36-41` memverifikasi bahwa ketiga limiter (`api`, `broadcasting`, `transaksi`) telah terdaftar di facade `RateLimiter::limiter(...)`.
- **Namun**, hasil pencarian `throttle:` di seluruh `routes/*.php` dan `app/**/*.php` menunjukkan bahwa **ketiga named limiter tersebut belum ditempelkan sebagai middleware pada rute mana pun** (seperti `/api/search-obat`, `/api/webhooks/qris`, `resep.upload`, `checkout.process`, atau channel broadcasting).
- Sementara itu, rate limiter yang **benar-benar aktif terpasang pada rute HTTP** saat ini adalah:
  - `throttle:5,1` pada `POST /auth/send-otp` (`routes/web.php:187-189`)
  - `throttle:10,1` pada `POST /auth/verify-otp` dan `POST /auth/register-otp` (`routes/web.php:191-194`)
  - Limiter bawaan Fortify (`two-factor` 5/mnt, `login` 5/mnt, `passkeys` 10/mnt) pada `app/Providers/FortifyServiceProvider.php:168-187`.

Selain itu, catatan kecil PHPStan/bug potensial pada `AppServiceProvider.php:41,51`: `$request->user()->id_pengguna ?? $request->ip()` mengandalkan perilaku nullsafe/properti dinamis pada object `User` (karena di tabel `users` PK-nya adalah `id`, bukan `id_pengguna`), sehingga saat `$request->user()` terautentikasi tanpa atribut `id_pengguna` ter-set, `??` jatuh kembali ke `$request->ip()`. Mengubahnya menjadi `$request->user()?->id ?? $request->ip()` akan jauh lebih akurat untuk pengguna `User`.

### 5.3 Validasi *Magic Bytes* Berkas Resep dengan `finfo_file()` pada `UnggahResepRequest`

Penyerang dapat mengganti ekstensi skrip web shell PHP (`.php`) menjadi `.jpg` untuk mengelabui validasi ekstensi sederhana (*MIME/extension spoofing*). Untuk menutup celah tersebut, `App\Http\Requests\Customer\UnggahResepRequest` (`app/Http/Requests/Customer/UnggahResepRequest.php`, 70 baris) dihubungkan langsung ke endpoint `POST /klien/{client}/resep/unggah` (`routes/customer.php:116`).

```php
// app/Http/Requests/Customer/UnggahResepRequest.php (baris 34-55)
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
```

Mekanisme validasi berlapis pada `UnggahResepRequest::rules()` (baris 23-56):
1. Aturan metadata opsional pasien/dokter (`nama_dokter`/`namaDokter` maks 150, `sip_dokter`/`sipDokter` maks 100, `nama_pasien`/`namaPasien` maks 150, `umurPasien` maks 50, `catatan` maks 500) — menerima gaya *snake_case* maupun *camelCase* demi kompatibilitas form lama & JSON API.
2. Aturan berkas `foto_resep`: `['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:5120', Closure]` (maksimal 5 MB; `nullable` dipertahankan agar alur E2E/form cepat tanpa lampiran foto tetap berjalan dengan fallback `/images/resep-sample.jpg` di `routes/customer.php:130-132`).
3. **Inspeksi *Magic Bytes* (`baris 39-53`):** Ketika file `malicious.jpg` berisi `<?php echo 'hacked'; ?>` dikirimkan, `finfo_file()` mendeteksi MIME aktual `text/x-php` atau `text/plain` dari *header bytes* berkas dan langsung menggagalkan permintaan dengan HTTP `422 Unprocessable Entity` (teruji di `SecurityHardeningTest.php:65-80`). Pola pertahanan `finfo_file()` yang dipelopori pada FormRequest Candi ini juga diadopsi pada fitur Livewire *Tanya Apoteker* oleh Daffa (`app/Livewire/Customer/TanyaApotekerModal.php:193-204` dan `app/Livewire/Tenant/ApotekerChatPortal.php:129-140`).

### 5.4 Sanitasi Input Anti-XSS pada `VerifikasiResepRequest`

Untuk mencegah serangan *Stored Cross-Site Scripting* (XSS) ketika apoteker mengisi catatan telaah atau alasan penolakan resep yang nantinya dibaca kembali pada riwayat/invoice atau dasbor audit, `App\Http\Requests\Tenant\VerifikasiResepRequest` (`app/Http/Requests/Tenant/VerifikasiResepRequest.php`, 59 baris) dihubungkan pada `POST /cabang/{client}/resep/{id}/verifikasi` (`routes/tenant.php:344`).

```php
// app/Http/Requests/Tenant/VerifikasiResepRequest.php (baris 21-45)
protected function prepareForValidation(): void
{
    $catatan = $this->input('catatan');
    $alasan = $this->input('alasan');

    $this->merge([
        'catatan' => $catatan ? strip_tags(trim((string) $catatan)) : null,
        'alasan' => $alasan ? strip_tags(trim((string) $alasan)) : null,
    ]);
}

public function rules(): array
{
    return [
        'action' => ['required', 'string', 'in:disetujui,ditolak'],
        'nomor_sip' => ['nullable', 'string', 'max:100'],
        'catatan' => ['nullable', 'string', 'max:500'],
        'alasan' => ['nullable', 'string', 'max:500'],
    ];
}
```

Ringkasan perilaku `VerifikasiResepRequest`:
1. **`prepareForValidation()` (baris 21-30):** Sebelum aturan validasi dijalankan, nilai input `catatan` dan `alasan` dibersihkan memakai `strip_tags(trim((string) ...))` dan di-merge kembali ke request (atau `null` jika kosong).
2. **`rules()` (baris 37-45):** Memastikan `action` wajib bernilai `'disetujui'` atau `'ditolak'` (mengakomodasi integrasi modal apoteker di `tenant/verifikasi-resep.blade.php`), `nomor_sip` maks 100 karakter, dan teks tersanitasi maksimal 500 karakter.
3. **`messages()` (baris 52-58):** Menyediakan pesan kesalahan validasi berbahasa Indonesia yang jelas untuk field `action`.

### 5.5 Audit Suite Pengujian Inti Candi dan Eksekusi Quality Gate (Lokal vs CI MariaDB/Redis)

Terdapat **4 berkas test utama** yang ditulis/dikembangkan langsung oleh Candi (ditambah kontribusi test pada `CartCheckoutPaymentTest`, `GoApotikCatalogIntegrationTest`, `ProfileUpdateTest`, dan `RoleCrudAndKasirPortalTest`):
1. `tests/Feature/ReverbOrderStatusBroadcastTest.php` (49 baris, **2 test methods, 8 assertions**):
   - `test_order_status_updated_broadcasts_to_correct_channels()` (baris 15-32): Memverifikasi `OrderStatusUpdated::dispatch($pesanan)` menyiarkan tepat ke `private-order.{$pesanan->id_pesanan}` (index 0) dan `private-tenant.{$pesanan->id_client}` (index 1).
   - `test_order_status_updated_broadcast_payload_structure()` (baris 34-48): Memverifikasi struktur payload `broadcastWith()` memiliki `id_pesanan`, `nomor_pesanan`, `status_pesanan`, dan `updated_at`.
2. `tests/Feature/SalesReportAndPeriodLockTest.php` (117 baris, **3 test methods, 12 assertions**):
   - `test_close_period_locks_record_permanently_and_prevents_duplicate()` (baris 21-41): Menguji `CloseSalesPeriodAction::execute()` menyimpan `status_terkunci = 1` dan melempar `RuntimeException` saat dicoba tutup ulang pada rentang tanggal yang sama (ADR-0008).
   - `test_sales_report_service_calculates_summary_correctly()` (baris 43-105): Menguji 2 pesanan (status `selesai` subtotal Rp 50.000 qty 2, dan `diproses` subtotal Rp 75.000 qty 3) menghasilkan `total_pendapatan = 125000`, `total_pesanan = 2`, `total_obat_terjual = 5`, `rata_rata_transaksi = 62500`.
   - `test_csv_export_mitigates_formula_injection_characters()` (baris 107-116): Menguji `sanitizeForCsv()` memberi prefiks `'` untuk `=SUM(A1:A10)`, `+12345`, `-100`, `@payload`, namun membiarkan `ORD-20261107-001` tetap utuh.
3. `tests/Feature/SecurityHardeningTest.php` (81 baris, **5 test methods, 23 assertions**):
   - `test_security_headers_are_present_on_http_responses()` (baris 15-25): Memeriksa 6 header keamanan di endpoint `/up`.
   - `test_content_security_policy_allows_reverb_websocket_connection()` (baris 27-34): Memastikan `ws://127.0.0.1:8080` dan `wss://127.0.0.1:8080` diizinkan di CSP.
   - `test_rate_limiters_are_properly_configured()` (baris 36-41): Memverifikasi definisi `RateLimiter::limiter('api')`, `'broadcasting'`, dan `'transaksi'`.
   - `test_prescription_upload_with_real_image_file_passes_magic_bytes_validation()` (baris 43-63): Menguji `UploadedFile::fake()->image('resep-asli.jpg', 600, 800)` lolos 200 OK ke `customer.resep.upload` dengan `Storage::fake('public')`.
   - `test_prescription_upload_with_spoofed_file_fails_magic_bytes_validation()` (baris 65-80): Menguji `UploadedFile::fake()->createWithContent('malicious.jpg', "<?php echo 'hacked'; ?>")` ditolak HTTP `422` pada field `foto_resep`.
4. `tests/Feature/EndToEndOrderFlowTest.php` (122 baris, **1 test method**):
   - `test_complete_end_to_end_order_and_period_closing_flow()` (baris 32-121): Menguji skenario E2E 5 tahap: seeding `DemoApotekSeeder` + stok `StokClient` 50 unit -> `POST customer.resep.upload` -> `CartService::addItem()` & `getItems()` Redis -> `CreateOrderAction::execute()` (`status: menunggu_pembayaran`, `token_sesi`) -> `POST /api/webhooks/qris` dengan header `X-Signature` HMAC-SHA256 + `Event::assertDispatched(OrderStatusUpdated::class)` + status menjadi `'diproses'` -> `actingAs($superAdminUser)` + `CloseSalesPeriodAction::execute()` menghasilkan `status_terkunci = true`.

Selain 4 berkas utama di atas, fitur-fitur yang dibangun dan disempurnakan oleh Candia Purwa juga diverifikasi secara langsung oleh kasus uji pada suite lintas-modul berikut:

| Berkas Test Lintas-Modul | Nama Method `test_*` Terkait Tugas Candi | Aspek Candi yang Diverifikasi |
|---|---|---|
| `tests/Feature/CartCheckoutPaymentTest.php` | `test_order_history_page_renders_customer_orders_and_clean_profile_dropdown` | Halaman `order-history.blade.php` & dropdown profil bersih. |
| `tests/Feature/CartCheckoutPaymentTest.php` | `test_order_history_scopes_to_active_branch_and_track_resolves_cross_session_branch` | Filter `OrderTrackingController::history()` per cabang aktif + `?cabang=semua` + cross-branch `track()`. |
| `tests/Feature/CartCheckoutPaymentTest.php` | `test_qris_settlement_preserves_menunggu_verifikasi_when_order_has_pending_prescription` | `settlePesananAtomically()` + dispatch `OrderStatusUpdated` tetap menahan status `menunggu_verifikasi` jika resep belum ditelaah. |
| `tests/Feature/CustomerPrescriptionUploadAndQueueTest.php` | `test_customer_prescription_upload_rejects_oversized_file`, `test_customer_prescription_upload_rejects_invalid_file_type` | Aturan `max:5120` & validasi tipe file pada `UnggahResepRequest`. |
| `tests/Feature/CustomerPrescriptionUploadAndQueueTest.php` | `test_pharmacist_verification_dispatches_order_status_updated_event` | `POST /cabang/{client}/resep/{id}/verifikasi` (`VerifikasiResepRequest`) memancarkan `OrderStatusUpdated`. |
| `tests/Feature/QueueAndStatusProgressionTest.php` | `test_pharmacist_can_advance_order_status_and_dispatches_reverb_event`, `test_customer_can_view_order_tracking_with_virtual_queue` | Endpoint progresi status tenant memancarkan `OrderStatusUpdated` & `order-track.blade.php` merender antrean virtual. |
| `tests/Feature/GoApotikCatalogIntegrationTest.php` | `test_out_of_stock_medicine_displays_with_locked_green_appearance_and_notice` | Lock UI `Stok Habis` & `(Terkunci)` pada `catalog-product-card.blade.php`. |
| `tests/Feature/Settings/ProfileUpdateTest.php` | `test_profile_phone_and_address_can_be_updated`, `test_profile_phone_and_address_are_trimmed_and_sanitized` | Kolom `phone` & `address` + sanitasi `strip_tags(trim(...))` di `⚡profile.blade.php`. |
| `tests/Feature/RoleCrudAndKasirPortalTest.php` | `test_apoteker_can_manage_fefo_stock_isolated_patients_and_branch_report`, `test_dashboard_and_profile_adapt_buttons_for_each_role_and_super_admin_can_audit_apoteker` | Laporan cabang `/cabang/{client}/laporan` & aksi profil per peran. |
| `tests/Feature/ApotekDatabaseConstraintTest.php` | `test_duplicate_sales_period_summary_for_same_client_and_dates_is_rejected` | Constraint DB `uq_ringkasan_client_periode` pada `ringkasan_periode_penjualan` (ADR-0008). |

**Hasil Eksekusi Langsung Quality Gate di Lingkungan Lokal (`D:\projek-pdevit\startup`, 26 November 2026):**

| Perintah Quality Gate | Status Aktual | Output Terverifikasi | Catatan Lingkungan |
|---|---|---|---|
| `php -d memory_limit=1G vendor/bin/phpstan analyse --no-progress` | **PASSED** | `0 errors` (PHPStan/Larastan Level 7) | Mencakup `app/`, `bootstrap/app.php`, `config/`, `database/`, `routes/`. |
| `php vendor/bin/pint --test` | **PASSED** | `passed` (standar preset `laravel` PSR-12) | Dijalankan single-process di Windows. |
| `php vendor/bin/phpunit` (7 suite inti) | **PASSED** | `53 tests, 53 passed, 356 assertions` (12.710 ms) | Mencakup `ReverbOrderStatusBroadcastTest` (2), `SalesReportAndPeriodLockTest` (3), `SecurityHardeningTest` (5), `CustomerPrescriptionUploadAndQueueTest` (15), `TenantIsolationTest` (15), `ModularFoundationTest` (8), `ModuleConventionTest` (5). |
| `php vendor/bin/phpunit tests/Feature/EndToEndOrderFlowTest.php` | **ERROR (Lokal tanpa Redis)** | `No connection could be made because the target machine actively refused it` | `CartService::addItem()` memanggil `Redis::hincrby()` ke `127.0.0.1:6379`; daemon Redis lokal Windows tidak menyala. Di CI (`.github/workflows/tests.yml`), service `redis:7` aktif sehingga test ini lulus. |
| `php artisan tenant:audit` | **ERROR (Lokal tanpa MariaDB)** | `SQLSTATE[HY000] [2002]` ke `127.0.0.1:3306` db `kantin` | Layanan MariaDB lokal sedang tidak aktif; logika `tenant:audit` tetap lulus pada SQLite `:memory:` di `TenantIsolationTest::test_audit_tenant_isolation_artisan_command_passes`. |

---

## Bab 6. Pilar 4 (Kontribusi Pasca-M14: PR #13, #19–#22, #25–#26, #31–#32)

### 6.1 Fitur Drag-and-Drop & Browse File Foto Resep (`customer/home.blade.php`, `routes/customer.php`) — PR #13

Pada fase awal, fitur unggah resep pelanggan masih menggunakan formulir metadata teks tanpa unggahan file foto nyata. Pada **11 November 2026** (commit `7228628` -> `7470bb1` -> PR #13 `ece1f2d`), Candi melengkapi fitur ini secara *end-to-end*:

1. **Antarmuka Dropzone & Pratinjau (`resources/views/customer/home.blade.php:354-416` & `447-551`):**
   - Menambahkan komponen *dropzone* interaktif berbasis Alpine.js di dalam modal `#unggahResep`: mendukung klik `Browse File` (`$refs.fileInput.click()`) maupun seret-dan-lepas (`@dragover.prevent`, `@dragleave.prevent`, `@drop.prevent="handleFileDrop($event)"`).
   - State Alpine pada `customerCatalog`: `selectedFile`, `filePreview`, `fileName`, `fileSize`, `isDragging`.
   - Validasi sisi klien di `setFile(file)` (baris 490-510): memfilter tipe `['image/jpeg', 'image/png', 'image/jpg']`, membatasi ukuran maksimal `5 * 1024 * 1024` bytes (5 MB), lalu membaca pratinjau gambar via `FileReader::readAsDataURL()`.
   - Pratinjau gambar dilengkapi tombol `Ganti` dan `Hapus` (`removeFile()` yang mereset `$refs.fileInput.value = ''`), serta pengiriman payload `FormData` dengan kunci `foto_resep` ke `route('customer.resep.upload')` (baris 520-538).
2. **Penyimpanan Storage Server (`routes/customer.php:116-132, 230-231`):**
   - Menggunakan `UnggahResepRequest` yang memvalidasi *magic bytes* `finfo_file()`.
   - Jika berkas valid diunggah (`$request->hasFile('foto_resep')`), menyimpan berkas ke disk publik (`$file->store('resep', 'public')`), menghasilkan URL `/storage/resep/...`, serta mengembalikan `url_gambar` dan `nama_berkas` pada respons JSON.
3. **Pengujian Otomatis (`tests/Feature/SecurityHardeningTest.php:43-80`):**
   - Menambahkan 2 kasus uji: `test_prescription_upload_with_real_image_file_passes_magic_bytes_validation` dan `test_prescription_upload_with_spoofed_file_fails_magic_bytes_validation` menggunakan `Storage::fake('public')`.

### 6.2 Riwayat Pesanan Responsif & Filter Cabang Aktif (`OrderTrackingController::history`, `order-history.blade.php`) — PR #19–#22

Pada **17 November 2026**, Candi mengirim rangkaian PR #19 (`6049fa6` -> `3efaadd`), PR #20 (`1c42e42` -> `d57a9c7`), PR #21 (`c495448` -> `2f02397`), dan PR #22 (`b547652` -> `66c26d0`) untuk merombak halaman riwayat pesanan pelanggan:

1. **Logika Filter Cabang Aktif di `OrderTrackingController::history()` (`OrderTrackingController.php:171-227`):**
   - Mengambil `sessionToken` (`omnipharma_customer_session` -> `omni_session` -> session ID) dan `penggunaId` dari `$request->user()?->email`.
   - Membangun query dasar `Pesanan::withoutTenancy()->with(['items.obat', 'pembayaran', 'client'])` yang mencocokkan `id_pengguna` ATAU `token_sesi`.
   - Menghitung `$otherBranchesCount` (jumlah pesanan milik pelanggan/sesi tersebut di cabang selain cabang aktif `$activeClientId`, baris 207-209).
   - Secara default (`$branchScope = $request->query('cabang', 'aktif')`), memfilter `where('id_client', $activeClientId)` kecuali pengguna memilih `?cabang=semua` (baris 211-213).
2. **Antarmuka Tokopedia-Style & Responsivitas Mobile (`resources/views/customer/order-history.blade.php`, 315 baris):**
   - **Empty State Cerdas (baris 36-69):** Jika cabang aktif belum memiliki pesanan namun `$otherBranchesCount > 0`, sistem menampilkan jumlah pesanan di cabang lain beserta tombol cepat `Beralih ke Cabang ...` (POST `branch.switch`) dan tautan `Tampilkan Riwayat Semua Cabang` (`?cabang=semua`).
   - **Status Bar Filter Cabang (baris 72-96):** Menampilkan ruang lingkup cabang aktif dan tautan cepat beralih antara cabang aktif vs semua cabang.
   - **Tab Filter Status Cepat Alpine.js (baris 98-167):** Pill filter horizontal (*scrollable* tanpa scrollbar) untuk `Semua`, `Menunggu Pembayaran`, `Diproses`, `Selesai`, dan `Dibatalkan` lengkap dengan hitungan dinamis per status.
   - **Kartu Pesanan dengan Thumbnail Produk (baris 170-310):** Menampilkan `#nomor_pesanan`, badge nomor antrean, timestamp WIB, nama cabang, thumbnail produk pertama (`url_gambar` dengan fallback `/images/placeholder-product.svg`), indikator `+N produk lainnya`, total pembayaran, serta tombol `Bayar QRIS` (bila belum lunas) dan `Lihat Invoice & Lacak` dengan tinggi minimum `44px`.
3. **Verifikasi Test (`tests/Feature/CartCheckoutPaymentTest.php`):**
   Menambahkan pengujian `test_order_history_scopes_to_active_branch_and_track_resolves_cross_session_branch` (beserta seeding stok fisik pada commit `b547652`) dan merapikan styling Pint (`c495448`).

### 6.3 Ekspansi Kolom Profil `phone`/`address` & Kompatibilitas Accessor — PR #25–#26

Pada **18–19 November 2026** (commit `75f4882` -> `7ef7f1a` -> PR #25 `b9c92f1`, lalu `3a4af30` -> PR #26 `255ad95`), Candi memperluas profil pengguna agar mendukung nomor telepon/WhatsApp dan alamat lengkap pengiriman:

1. **Migrasi Skema (`database/migrations/2026_11_18_000001_add_phone_and_address_to_users_table.php`, 42 baris):**
   Menambahkan kolom `phone` (`string(50)`, nullable, after `email`) dan `address` (`text`, nullable, after `phone`) secara idempoten dengan `Schema::hasColumn()` pada metode `up()` dan `down()`.
2. **Kompatibilitas Accessor & Mutator di `App\Models\User` (`app/Models/User.php:40, 68-102`):**
   - Menambahkan `'phone'`, `'address'`, `'telepon'`, `'no_telp'`, `'alamat'` ke atribut `#[Fillable(...)]`.
   - Mendefinisikan accessor/mutator `telepon`, `no_telp` (keduanya membaca/menulis `$this->attributes['phone']`), dan `alamat` (membaca/menulis `$this->attributes['address']`) agar kode modul lain yang memakai penamaan Bahasa Indonesia tetap sinkron dengan kolom standar `phone`/`address`.
3. **Aturan Validasi & Sinkronisasi Auth (`app/Concerns/ProfileValidationRules.php:16-72`, `FirebaseAuthSessionController.php:161-166`):**
   - `ProfileValidationRules` menambahkan `phoneRules()` (`nullable|string|max:30`) dan `addressRules()` (`nullable|string|max:500`).
   - `FirebaseAuthSessionController::saveCompleteProfile()` menyimpan input `telepon` dan `alamat` ke kolom `phone` dan `address` pada model `User`.
4. **Pembaruan Komponen Settings Livewire Volt (`⚡profile.blade.php` & `⚡security.blade.php`):**
   - `resources/views/pages/settings/⚡profile.blade.php:17-54, 130-145`: Menambahkan input `Nomor Telepon / WhatsApp` dan textarea `Alamat Lengkap`, melakukan sanitasi `strip_tags(trim(...))` sebelum validasi (`baris 37-40`), dan menampilkan toast sukses Flux.
   - `resources/views/pages/settings/⚡security.blade.php:174-347`: Merapikan tata letak kartu Kata Sandi, Two-Factor Authentication, dan Passkeys secara responsif.
   - Pengujian: `ProfileUpdateTest.php` menambah `test_profile_phone_and_address_can_be_updated` & `test_profile_phone_and_address_are_trimmed_and_sanitized`, serta commit `3a4af30` (PR #26) menyelaraskan assertion menu profil Super Admin di `RoleCrudAndKasirPortalTest.php`.
5. **Pemetaan Pintasan Menu Profil Adaptif (`User::profileActions()`, `app/Models/User.php:183-433`):**
   Komponen profil dan navigasi menggunakan `profileActions(?string $preferredBranch = null)` untuk menyusun tautan sesuai peran secara dinamis:
   - `super_admin` (`baris 190-274`): 9 item menu terbagi menjadi `portal` (`Executive Dashboard Pusat`), `crud` (`CRUD Cabang & Pengaturan`, `CRUD Master Obat & Stok`, `CRUD Staf, Role & Hak Akses`, `Ekspor Laporan & Tutup Periode`), `audit` (`Cek Operasional Apoteker`, `Monitoring Progres Obat`, `Audit Kasir & Ledger Cabang`), dan `pelanggan` (`Katalog & Belanja`).
   - `apoteker` / `tenant` (`baris 276-351`): 8 item menu (`Dashboard Apoteker`, `Telaah Resep Dokter`, `Stok & Batch FEFO`, `Antrean & Walk-In Cabang`, `Rekam Pasien Cabang`, `Laporan Penjualan Cabang`, `SOP & Bantuan Regulasi`, `Katalog & Belanja`).
   - `kasir` / `admin` / `staf_klien` (`baris 353-383`): 3 item menu (`Dashboard Kasir & POS Loket`, `Buku Ledger Kasir`, `Katalog & Belanja`).
   - `pelanggan` / default (`baris 385-433`): 4 item menu (`Katalog & Dashboard`, `Keranjang Belanja`, `Riwayat Pesanan`, `Unggah Resep Dokter`).

### 6.4 Penandaan Visual "Stok Habis (Terkunci)" pada Kartu Katalog (`catalog-product-card.blade.php`) — PR #25

Dalam commit `75f4882` (PR #25) bersama pembaruan `customer/home.blade.php` dan `GoApotikCatalogIntegrationTest.php`, Candi merancang tampilan khusus produk yang stoknya kosong di cabang aktif pada `resources/views/customer/partials/catalog-product-card.blade.php` (176 baris):
- Kartu produk memakai batas hijau emerald beraksen terkunci (`bg-emerald-50/35 border-2 border-emerald-400/80`) dan badge `Stok Habis` dengan ikon gembok di sudut kiri atas (baris 34-43).
- Gambar packshot diberi efek `opacity-75 grayscale-[20%]` dan overlay ikon gembok di tengah (`bg-emerald-950/20 backdrop-blur-[0.5px]`, baris 54-68).
- Banner status menampilkan `Stok habis di cabang ini (Terkunci)` bila tersedia di cabang lain (`$altBranch`), atau `Stok habis saat ini (Terkunci)` bila kosong di semua cabang (baris 92-105).
- Tombol beli diganti tombol `disabled` bertuliskan `Stok Habis` (`cursor-not-allowed`, `aria-disabled="true"`), disertai tombol CTA `Beralih ke {Nama Cabang Alternatif}` (`wire:click="switchBranch('{{ $altBranch->slug }}')"`) jika tersedia di cabang lain (baris 140-174).
- Teruji di `GoApotikCatalogIntegrationTest::test_out_of_stock_medicine_displays_with_locked_green_appearance_and_notice`.

### 6.5 Slider Mobile 3-in-1 Interaktif Landing Page & `redirect_to` (`welcome.blade.php`, `routes/web.php`) — PR #31–#32

Pada **22–23 November 2026**, Candi menyelesaikan gelombang kontribusi terbarunya (`60e2f7d`, `acb3013` -> PR #31 `d85aa92`, `bb079a1` -> PR #32 `c39e087`) untuk menyempurnakan halaman sambutan (`resources/views/welcome.blade.php`, 1.309 baris):
1. **Slider Mobile 3-in-1 (`welcome.blade.php:378-600+`):**
   - Pada viewport `< lg` (`lg:hidden`), 3 bagian panjang yang sebelumnya memaksa pengguna ponsel melakukan scroll jauh ke bawah disatukan menjadi **Kotak 3-in-1 Slide Interaktif**:
     1. `1. Cabang` (Pemilihan Cabang Banyuwangi Kota & Rogojampi, baris 489-574)
     2. `2. Alur Pasien` (Grid 2x2 langkah pelayanan 01–04, baris 579-600+)
     3. `3. Standar` (Standar pelayanan kefarmasian)
   - State & metode mesin slider Alpine.js pada `welcome.blade.php:379-450`:
     - **State reaktif:** `mainSlide: 0`, `totalSlides: 3`, `autoTimer: null`, `resumeTimer: null`, `isPaused: false`, `touchStartX: 0`, `touchEndX: 0`.
     - **Siklus otomatis:** `init()` memanggil `startAuto()` yang menjalankan `setInterval(..., 4500)` dan memajukan `(mainSlide + 1) % totalSlides` hanya jika `!isPaused`.
     - **Kontrol interaksi:** `pause()` menghentikan rotasi saat hover/touch dan membersihkan `resumeTimer`; `resume()` mengembalikan `isPaused = false` saat mouse keluar; `resumeDebounced()` menunda pengaktifan ulang auto-slide selama `4500ms` setelah pengguna menekan tab (`goTo(i)`), panah (`prev()`/`next()`), atau mengusap layar (`handleTouchEnd()`).
     - **Deteksi sentuhan pasif:** `@touchstart.passive="handleTouchStart($event)"` dan `@touchend.passive="handleTouchEnd($event)"` menghitung selisih `screenX` dengan ambang geser `40px` tanpa memblokir *vertical page scroll* browser mobile.
2. **Dukungan `redirect_to` pada Pergantian Cabang (`routes/web.php:78-80`):**
   ```php
   if ($request->filled('redirect_to')) {
       return redirect($request->input('redirect_to'));
   }
   ```
   Penambahan 3 baris ini oleh Candi pada commit `60e2f7d` memungkinkan formulir pemilihan cabang di `welcome.blade.php:505-558` (saat user sudah login) menyimpan cabang pilihan ke session (`active_branch` & `active_client`) lalu langsung mengarahkan pengguna ke `/dashboard` alih-alih `back()` ke halaman landing.

Ringkasan lengkap state dan alur fungsi Alpine `customerCatalog` pada `resources/views/customer/home.blade.php:439-574`:

| Properti / Method Alpine | Baris | Peran Teknis |
|---|---|---|
| `selectedFile`, `filePreview`, `fileName`, `fileSize`, `isDragging` | `447-451` | Menyimpan objek `File` terpilih, hasil `FileReader` data URL, nama/ukuran MB, serta status visual *drag-over* dropzone. |
| `handleFileChange(event)` & `handleFileDrop(event)` | `480-489` | Menangkap berkas dari `<input type="file" x-ref="fileInput">` maupun event `@drop.prevent` lalu meneruskan ke `setFile()`. |
| `setFile(file)` | `490-510` | Validasi MIME client-side (`image/jpeg`, `image/png`, `image/jpg`), batas ukuran `5 * 1024 * 1024` bytes, dan pembacaan pratinjau asinkron. |
| `removeFile()` | `511-519` | Mengosongkan state pratinjau dan mereset `$refs.fileInput.value = ''` agar file yang sama bisa dipilih kembali. |
| `unggahResep()` | `520-551` | Menyusun `FormData` (`nama_dokter`, `sip_dokter`, `nama_pasien`, `catatan`, dan opsional `foto_resep`), mengirim POST JSON dengan `X-CSRF-TOKEN` ke `customer.resep.upload`, menutup modal, dan menampilkan toast sukses 5 detik. |
| `liveSearchHome()` & `beliLangsung()` | `452-472, 552-573` | Melakukan autocomplete `/api/search-obat` (minimal 2 karakter) serta quick-add ke keranjang Redis (`customer.cart.add`) dengan event `cart-updated`. |

---

## Bab 7. Audit Kritis Deviasi Dokumentasi vs Kode Aktual, Backlog Perbaikan, & Panduan Demo

### 7.1 Daftar Lengkap 7+ Deviasi PRD/Dokumentasi terhadap Kode Sumber Aktual

Agar tim dan Candia Purwa tidak terjebak asumsi lama saat sidang/demo atau pengembangan lanjutan, berikut adalah hasil audit silang antara dokumen lama (`README.md`, `docs/PRD_CANDI.md`, `docs/REQUIREMENTS_TRACEABILITY.md`, `README_TUGAS_ANGGOTA_DAN_BACKLOG.md`) dengan kondisi nyata repositori per commit `c39e087`:

| No | Klaim di Dokumentasi / PRD Lama | Fakta Terverifikasi di Kode Sumber Aktual (`c39e087`) | Dampak Teknis & Risiko |
|---|---|---|---|
| 1 | **`PRD_CANDI.md:167-168`:** Menulis `new PrivateChannel('private-order.' . $id)` dan `Broadcast::channel('private-order.{id}', ...)` | `OrderStatusUpdated.php:22-23`, `IncomingOrderPlaced.php:28`, `routes/channels.php:12,35`, dan `order-track.blade.php:40` memakai `'order.'` dan `'tenant.'` (tanpa prefiks `'private-'` di argumen PHP/Echo karena Laravel otomatis menambahkan `'private-'`). | Sudah benar di kode aktual (`62fe550`); dokumentasi `PRD_CANDI.md` saja yang belum diperbarui. |
| 2 | **`README.md:35` & `PRD_CANDI.md:77`:** Menyebut event `IncomingOrderPlaced` sebagai notifikasi real-time ke dasbor apoteker. | Berkas `IncomingOrderPlaced.php` ada, tetapi **tidak pernah di-dispatch** di controller/action manapun, belum ada listener Echo di `tenant/dashboard.blade.php`, dan `broadcastWith()` memakai kolom tidak valid (`wajib_resep`, `status_pesanan`). | Jika dosen menguji notifikasi pesanan baru secara *real-time* di layar apoteker tanpa refresh, layar apoteker belum otomatis memperbarui daftar pesanan baru. |
| 3 | **`README.md:99` & `PRD_CANDI.md:51`:** Menyebut pembatasan laju (*RateLimiter*) `api`, `broadcasting`, dan `transaksi` aktif melindungi transaksi/upload. | `AppServiceProvider.php:40-52` mendefinisikan ketiganya (diuji di `SecurityHardeningTest.php:36-41`), tetapi **belum dipasang sebagai middleware `throttle:api` / `throttle:transaksi` / `throttle:broadcasting` pada rute**. Yang aktif hanya `throttle:5,1` & `throttle:10,1` pada Email OTP (`routes/web.php:187,191`) dan limiter Fortify. | Endpoint upload resep (`resep.upload`), checkout, webhook, dan search obat belum ter-throttle oleh limiter tersebut. |
| 4 | **`PRD_CANDI.md:79,85` & `README.md:35`:** Menyebut ekspor laporan PDF dan CSV/XLSX. | `SalesReportExportService.php` hanya mengimplementasikan `exportCsv()`. Template `resources/views/admin/pdf/laporan-penjualan-template.blade.php` tersedia, tetapi **belum ada package PDF** (`dompdf`/`snappy`) di `composer.json` maupun endpoint controller yang me-rendernya. | Tombol ekspor saat ini hanya menghasilkan CSV; ekspor PDF belum tersedia sebagai unduhan PDF biner. |
| 5 | **`admin/laporan-penjualan.blade.php:74` vs `routes/tenant.php:531-535`:** Tampilan membaca timestamp `$ringkasanTerkunci->dikunci_pada ?? $ringkasanTerkunci->created_at`. | Di `routes/tenant.php:531-535`, `$ringkasanTerkunci` dihasilkan dengan `->exists()` (bertipe **`bool`**), bukan instance model `RingkasanPeriodePenjualan`. | Saat periode terkunci, badge menampilkan `now()` alih-alih waktu asli penguncian (`dibuat_pada`) di basis data. |
| 6 | **`PRD_CANDI.md:14` & `REQUIREMENTS_TRACEABILITY.md:28`:** Menyebut `VerifyPrescriptionAction` dipakai pada alur verifikasi resep. | `routes/tenant.php:344-456` menggunakan `VerifikasiResepRequest` lalu menjalankan transaksi `DB::transaction()` secara inline (dengan `LogAudit` dan pemulihan stok saat `ditolak`), sementara `VerifyPrescriptionAction.php` hanya dipanggil oleh `PrescriptionVerificationTest.php`. | Tidak ada bug pada runtime, tetapi ada duplikasi logika verifikasi resep antara closure `routes/tenant.php` dan `VerifyPrescriptionAction`. |
| 7 | **`README.md:17-24`, `PANDUAN_PRESENTASI...:6,21`, `REQUIREMENTS_TRACEABILITY.md:38,46`:** Mengklaim 18 model, 17 migrasi, 196 test, 1.576 produk GoApotik, dan file `StaffHierarchy5NFTest.php` / `TenantContextIsolationTest.php` / `TUGAS_DAFFA.md`. | Kodebase aktual pada `c39e087` memiliki **20 model**, **20 migrasi**, **37 file test (`240` test methods)**, **`2.424` produk** di `scraped_goapotik_lengkap.json`, dan nama file test aktual adalah `TenantIsolationTest.php`, `ApotekFoundationTest.php`, `RoleCrudAndKasirPortalTest.php` (`TUGAS_DAFFA.md` sudah dihapus). | Dokumen presentasi & README perlu disinkronkan dengan metrik aktual agar tidak terjadi inkonsistensi saat presentasi. |
| 8 | **`SecurityHeadersMiddleware.php:51` vs `order-track.blade.php:237`:** `order-track.blade.php` menggunakan `<iframe src="https://maps.google.com/maps?...">`. | Direktif `frame-src` pada `SecurityHeadersMiddleware.php:51` hanya mengizinkan `'self' https://*.firebaseapp.com https://accounts.google.com`. | Pada browser dengan penegakan CSP ketat, embed peta Google Maps di halaman tracking berisiko diblokir jika `https://maps.google.com` tidak ditambahkan ke `frame-src`. |
| 9 | **`AppServiceProvider.php:41,51`:** Key rate limiter memakai `$request->user()->id_pengguna ?? $request->ip()`. | Model autentikasi `User` menggunakan primary key `id`, bukan kolom `id_pengguna`. | Saat `$request->user()` login, `$request->user()->id_pengguna` bernilai null sehingga limiter selalu fallback ke IP. |

### 7.2 Backlog Teknis & Checklist Perbaikan Prioritas untuk Candi

Berikut daftar pekerjaan perbaikan bernilai tinggi (*high-impact*) yang direkomendasikan bagi Candia Purwa apabila akan merilis patch penyempurnaan sebelum/sesudah presentasi:

1. **[Prioritas Tinggi — Security] Pasang Named Rate Limiter ke Rute Kritis:**
   - Tambahkan `->middleware('throttle:transaksi')` pada `customer.resep.upload` (`routes/customer.php:116`), `customer.checkout.process` (`routes/customer.php:243`), dan `checkout.store` (`routes/web.php:173`).
   - Tambahkan `->middleware('throttle:api')` pada `api.search.obat` (`routes/web.php:85`) dan `webhooks.qris` (`routes/api.php:6`).
   - Perbaiki `AppServiceProvider.php:41,51` dari `$request->user()->id_pengguna` menjadi `$request->user()?->getAuthIdentifier() ?? $request->ip()`.
2. **[Prioritas Tinggi — Real-Time & Security] Perketat Channel `tenant.{id_client}` & Aktifkan `IncomingOrderPlaced`:**
   - Di `routes/channels.php:36`, pisahkan pengecekan `super_admin` (boleh semua cabang) dengan `apoteker` (wajib memverifikasi cabang pada `admin_client` / `peran_client_pengguna` untuk `$idClient`).
   - Di `IncomingOrderPlaced.php:44-45`, ganti `wajib_resep` menjadi `$this->pesanan->resep()->exists()` dan `status_pesanan` menjadi `$this->pesanan->status ?? $this->pesanan->status_pesanan`, lalu dispatch event saat checkout/upload resep berhasil.
3. **[Prioritas Sedang — Reporting] Perbaiki Timestamp `ringkasanTerkunci` & Hubungkan Template PDF:**
   - Di `routes/tenant.php:531-535`, ganti `->exists()` dengan `->first()` (atau kirim modelnya) dan ubah `admin/laporan-penjualan.blade.php:74` agar membaca `$ringkasanTerkunci->dibuat_pada`.
   - Di `SalesReportExportService::sanitizeForCsv()`, tambahkan pengecekan awalan `\t` dan `\r` selain `['=', '+', '-', '@']`, dan pertimbangkan pemasangan `barryvdh/laravel-dompdf` (atau endpoint preview cetak HTML `@media print`) untuk me-render `admin/pdf/laporan-penjualan-template.blade.php`.
4. **[Prioritas Sedang — CSP & Open Redirect Defense]:**
   - Tambahkan `https://maps.google.com` ke direktif `frame-src` pada `SecurityHeadersMiddleware.php:51` agar iframe peta di `order-track.blade.php:237` selalu tampil bersih.
   - Di `routes/web.php:78-80` (`branch.switch`), validasi `redirect_to` dengan `str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, url('/'))` untuk mencegah potensi *Open Redirect*.

**Panduan Ringkas Perbaikan Kode (Target Patch Referensi):**
- **Perbaikan `$ringkasanTerkunci` di `routes/tenant.php:531-543`:**
  ```php
  $ringkasanTerkunci = RingkasanPeriodePenjualan::where('id_client', $clientId)
      ->where('periode_mulai', $mulai)
      ->where('periode_selesai', $selesai)
      ->where('status_terkunci', true)
      ->first(); // mengembalikan ?RingkasanPeriodePenjualan (truthy di @if($ringkasanTerkunci))
  ```
  Lalu pada `resources/views/admin/laporan-penjualan.blade.php:74`, baca `$ringkasanTerkunci->dibuat_pada` secara eksplisit.
- **Perbaikan Otorisasi Channel `tenant.{id_client}` di `routes/channels.php:35-53`:**
  ```php
  Broadcast::channel('tenant.{id_client}', function (User $user, string $idClient) {
      if ($user->hasRole('super_admin')) {
          return true;
      }
      $pengguna = Pengguna::where('telepon_email', $user->email)->first();
      if (! $pengguna) {
          return false;
      }
      return $pengguna->adminClient()->where('id_client', $idClient)->exists()
          || $pengguna->peranClients()->where('id_client', $idClient)->exists();
  });
  ```
  *(Menghapus bypass global `$user->hasRole('apoteker')` tanpa verifikasi `$idClient` agar apoteker cabang A tidak bisa subscribe ke channel WebSocket cabang B.)*

### 7.3 Panduan Demo Teknis 4 Layar, Perintah Operasional, & Daftar Kredensial Akun

**Perintah Menjalankan Lingkungan Demo Lengkap:**
```powershell
# 1. Pastikan MariaDB (port 3306) & Redis (port 6379) aktif, lalu seed data demo
php artisan migrate:fresh --seed

# 2. Pastikan storage symlink & asset Vite terbaru tersedia
php artisan storage:link
npm run build

# 3. Jalankan 2 server utama (atau gunakan `composer dev`)
php artisan serve --port=8088
php artisan reverb:start --host=0.0.0.0 --port=8080
```

**Kredensial Akun Demo Terverifikasi (`DemoApotekSeeder.php` & `AKUN_LOGIN.md`, seluruh password: `password`):**

| Peran | Email Login | Cabang | URL Dashboard | Kegunaan Demo |
|---|---|---|---|---|
| **Super Admin** | `daffa@omnipharma.test` | Kantor Pusat (Semua Cabang) | `/admin/dashboard` | Tutup periode buku ADR-0008, ekspor CSV, monitoring live, audit apoteker |
| **Apoteker BWI** | `bambang@omnipharma.test` | Banyuwangi Kota | `/cabang/banyuwangi-kota/dashboard` | Verifikasi resep SIP, progresi status antrean (memicu Reverb), FEFO |
| **Apoteker Rogojampi** | `dewi@omnipharma.test` / `dinda@omnipharma.test` | Rogojampi | `/cabang/rogojampi/dashboard` | Uji isolasi multi-tenant & blokir IDOR lintas cabang |
| **Apoteker Legacy Test** | `candia@omnipharma.test` | Banyuwangi Kota | `/cabang/banyuwangi-kota/dashboard` | Dipakai oleh `TenantIsolationTest.php` |
| **Kasir Cabang** | `kasir.bwi@omnipharma.test` | Banyuwangi Kota | `/kasir/dashboard` | POS walk-in, pelunasan pesanan (memicu Reverb), cetak struk |
| **Pelanggan Utama** | `gunturafandy5@gmail.com` | Default `banyuwangi-kota` | `/dashboard` | Belanja, upload resep browse/drag-drop, checkout, bayar QRIS, lacak real-time |
| **Pelanggan Alternatif** | `candi@gmail.com` (`Candi Chier`) | Default `banyuwangi-kota` | `/dashboard` | Akun pasien alternatif & profil telepon/alamat |

**Skenario Demonstrasi Pilar Candi (3 Menit):**
1. **Demo Real-Time Tracking (M12-RT):** Buka halaman lacak pesanan `/klien/banyuwangi-kota/order/{id}/track` pada Tab 1 (pelanggan) dan `/cabang/banyuwangi-kota/antrean` pada Tab 2 (login `bambang@omnipharma.test`). Klik tombol ubah status ke `Siap Diambil` di Tab 2 — tunjukkan *stepper* di Tab 1 berpindah seketika via koneksi WebSocket `ws://127.0.0.1:8080` (`OrderStatusUpdated` pada channel `private-order.{id}`) tanpa refresh halaman.
2. **Demo Analitik & Kunci Snapshot (M13):** Buka `/cabang/banyuwangi-kota/laporan` (atau `/admin/dashboard`), tunjukkan agregasi omzet dari `item_pesanan`, klik **Tutup & Kunci Periode Buku**, tunjukkan badge berubah menjadi `PERIODE TERKUNCI (IMMUTABLE)` dan percobaan ulang ditolak `CloseSalesPeriodAction`. Unduh CSV lalu buka dengan text editor untuk menunjukkan karakter `=`, `+`, `-`, `@` otomatis diawali `'`.
3. **Demo Security & Pasca-M14 (M14+):** Unggah foto resep di `/klien/banyuwangi-kota` (tunjukkan drag-drop + pratinjau), tunjukkan `SecurityHeadersMiddleware` di DevTools Network tab (`X-Frame-Options: DENY`, `Content-Security-Policy`), buka `/klien/banyuwangi-kota/riwayat-pesanan` (filter cabang aktif + tab status), dan jalankan `php vendor/bin/phpunit tests/Feature/ReverbOrderStatusBroadcastTest.php tests/Feature/SalesReportAndPeriodLockTest.php tests/Feature/SecurityHardeningTest.php` di terminal.

### 7.4 Bank Tanya-Jawab Teknis Sidang & Audit untuk Candia Purwa

Bagian ini merangkum pertanyaan teknis kritis yang paling mungkin diajukan oleh dosen pengampu (Pak Devit Suwardiyanto) atau auditor kode beserta jawaban berbasis bukti langsung di repositori:

1. **T: Mengapa `OrderStatusUpdated` memakai `ShouldBroadcastNow`, bukan `ShouldBroadcast` biasa?**
   - **J:** Karena status pelunasan QRIS atau progresi racikan obat membutuhkan pembaruan *real-time* di layar pelanggan dalam hitungan milidetik (`< 50ms`) bahkan pada lingkungan demo di mana `php artisan queue:work` tidak selalu dijalankan paralel. `ShouldBroadcastNow` mengirim payload langsung ke server Reverb `:8080` pada saat `event(...)` / `::dispatch(...)` dieksekusi, sementara untuk transaksi kasir POS di `routes/kasir.php:190,266`, pemanggilan dibungkus `try ... catch (Throwable)` agar transaksi luring kasir tidak gagal apabila daemon Reverb sedang offline.
2. **T: Bagaimana mencegah pelanggan A mengintip status pesanan pelanggan B di WebSocket Reverb, termasuk untuk pelanggan tamu (*guest*)?**
   - **J:** Saluran `order.{id_pesanan}` menggunakan `PrivateChannel` (`OrderStatusUpdated.php:22`) sehingga Pusher/Echo klien wajib melewati otorisasi `POST /broadcasting/auth` yang diatur di `routes/channels.php:12-33`. Jika pengguna login, sistem memverifikasi `$pesanan->id_pengguna === $idPengguna` (resolusi dari `users.email` ke `pengguna.id_pengguna`). Jika pengguna belum login (*guest*), callback `?User $user` memeriksa kecocokan antara cookie HTTP `omnipharma_customer_session` dengan kolom `pesanan.token_sesi`. Jika keduanya gagal, `/broadcasting/auth` mengembalikan `403`.
3. **T: Di `SalesReportService`, mengapa agregasi omzet dihitung dari tabel `item_pesanan`, bukan langsung `SUM(total_harga)` di tabel `pesanan`?**
   - **J:** Berdasarkan migrasi `2026_09_21_000103`, tabel `pesanan` tidak memiliki kolom fisik `total_harga` (`total_harga` pada `Pesanan.php:141-144` adalah accessor runtime yang menjumlahkan `items->sum('subtotal') + ongkos_kirim`). Jika query SQL memanggil `SUM(total_harga)` langsung ke `pesanan`, query akan gagal dengan *unknown column*. Mengagregasi `item_pesanan.subtotal` dan `item_pesanan.kuantitas` di `SalesReportService.php:27-45` serta `CloseSalesPeriodAction.php:37-40` (selaras **ADR-0008**) menjamin perhitungan omzet selalu mengambil snapshot harga historis saat transaksi dibuat dan tahan terhadap perubahan harga obat di tabel master `obat`.
4. **T: Bagaimana aplikasi menjamin berkas foto resep yang diunggah pelanggan bukan skrip PHP tersamar?**
   - **J:** `UnggahResepRequest` (`routes/customer.php:116`) tidak hanya memeriksa ekstensi/MIME header HTTP (`mimes:jpeg,png,jpg`, `max:5120`), melainkan membuka *stream file* sebenarnya dengan `finfo_open(FILEINFO_MIME_TYPE)` dan `finfo_file($finfo, $value->getRealPath())` (`UnggahResepRequest.php:42-51`). Berkas teks PHP yang diganti nama menjadi `malicious.jpg` tetap terdeteksi sebagai `text/x-php` atau `text/plain` dan ditolak dengan HTTP `422` (terbukti di `SecurityHardeningTest.php:65-80`).
5. **T: Apa bedanya penutupan periode buku pada tabel `ringkasan_periode_penjualan` dengan sekadar query ulang `SalesReportService`?**
   - **J:** `SalesReportService` menghitung agregasi *live* sementara periode masih terbuka (*OPEN*). Ketika `CloseSalesPeriodAction` dieksekusi, sistem membekukan snapshot `nama_client`, `total_obat_terjual`, `total_pesanan`, dan `total_pendapatan` ke tabel `ringkasan_periode_penjualan` dengan `status_terkunci = true` dan constraint unik `uq_ringkasan_client_periode` pada `(id_client, periode_mulai, periode_selesai)`. Percobaan menutup periode yang sama kedua kalinya ditolak dengan `RuntimeException` di level aplikasi dan ditolak constraint DB di level MariaDB/SQLite.
6. **T: Mengapa saat menjalankan `php vendor/bin/phpunit` penuh di Windows tanpa Redis aktif, `EndToEndOrderFlowTest` gagal, sedangkan di CI GitHub Actions hijau?**
   - **J:** Karena `phpunit.xml` mengkonfigurasi `DB_CONNECTION=sqlite` (`:memory:`), `CACHE_STORE=array`, dan `SESSION_DRIVER=array`, namun `CartService.php:23,54` memanggil facade `Illuminate\Support\Facades\Redis` (`Redis::hincrby`, `Redis::hgetall`) secara langsung ke `127.0.0.1:6379`. Di workflow CI `.github/workflows/tests.yml:95-104`, Docker container `redis:7` dan `mariadb:11.4` dijalankan otomatis sebagai *service container*, sehingga seluruh test termasuk `EndToEndOrderFlowTest` dan `ServiceAndCatalogAnimationVerificationTest` dapat berjalan penuh.

---

# BAGIAN II: RINGKASAN LENGKAP AKTIVITAS TIM & INVENTARIS KODEBASE

---

## Bab 8. Metodologi Audit Git, Statistik Kontributor, Rekapitulasi PR, & Pipeline CI/CD

### 8.1 Metodologi Inspeksi & Ruang Lingkup Branch (`origin/main` vs `--all`)

Seluruh rekapitulasi aktivitas tim pada Bagian II disusun langsung melalui inspeksi *read-only* terhadap repositori `D:\projek-pdevit\startup` (`git log`, `git shortlog`, `git show`, `git branch -a -vv`, `git ls-remote origin`, dan `gh pr list --state all`) serta pembacaan langsung terhadap berkas kode sumber pada baseline commit **`c39e0871b7bfdb9fd7c1bde098b4e422ee68be9d`** (*Merge pull request #32*).

Dua cakupan referensi Git dibedakan secara eksplisit agar perhitungan commit selalu konsisten:
1. **`origin/main` (dan `HEAD` lokal di `feat/candi-upload-foto-resep`):**
   - Rentang waktu: **`2026-10-30T08:18:23+07:00`** (`84028e5`) hingga **`2026-11-23T03:15:34+07:00`** (`c39e087`).
   - Total commit: **203 commits** (terdiri dari **169 non-merge commits** dan **34 merge commits**).
2. **`--all` (seluruh referensi lokal yang ter-fetch):**
   - Total commit: **207 commits** (terdiri dari **173 non-merge commits** dan **34 merge commits**).
   - Selisih **4 non-merge commits** berasal dari 3 branch fitur yang belum di-merge ke `main` (PR #1, PR #2, dan PR #8):
     - `2740d8d` (`dependabot[bot]`, PR #1 `dependabot/github_actions/github-actions-0982b03464`)
     - `6d7e4ae` & `8f4cb34` (`callmezext`, PR #2 `feature/firebase-auth-login`)
     - `b599624` (`Miinggg67`, PR #8 `feat/arya-catalog-fefo`)

**Topologi Branch Lokal vs Remote (`git branch -a -vv`):**

| Branch Lokal | Commit Tip | Status Upstream | Keterangan |
|---|---|---|---|
| `feat/candi-upload-foto-resep` (Aktif / `HEAD`) | `c39e087` | `origin/feat/candi-upload-foto-resep` (`bb079a1`, `[ahead 2]`) | Identik dengan `origin/main`; maju 2 merge commit (`d85aa92` & `c39e087`). |
| `main` | `198e118` | `origin/main` (`c39e087`, `[behind 24]`) | Branch lokal `main` belum di-pull sejak 19 Nov 2026. |
| `feat/candi-reverb-tracking` | `5516ab8` | *(tanpa upstream)* | Branch fase M12-RT Candi; sejajar dengan `origin/feat/candi-reverb-tracking`. |
| `feat/candi-sales-laporan` | `5516ab8` | *(tanpa upstream)* | Branch fase M13 Candi; sejajar dengan `origin/feat/candi-sales-laporan`. |
| `feat/candi-security-e2e` | `5516ab8` | *(tanpa upstream)* | Branch fase M14 Candi; sejajar dengan `origin/feat/candi-security-e2e`. |

### 8.2 Pemetaan Identitas Penulis Git (`git shortlog`) & Statistik 203/207 Commit

Berdasarkan `git shortlog -sne origin/main` dan `git shortlog -sne --no-merges origin/main`, beberapa anggota tim menggunakan lebih dari satu `user.name` Git antara commit lokal dan *merge button* di GitHub. Pengelompokan per email penulis memberikan angka kontribusi yang akurat:

| Anggota Tim | Variasi Nama Author di `git log` | Email Git Terverifikasi | Login GitHub | Non-Merge (`main` / `--all`) | Merge Commits | Total (`main` / `--all`) |
|---|---|---|---|---|---|---|
| **Guntur Afandi** | `Guntur Afandi`, `Callme Zettx`, `callmezext` | `gunturafandy5@gmail.com` | `callmezext` | 65 / 67 | 7 | **72 / 74** |
| **Muhammad Daffa Ariby** | `heliosparta18`, `Daffa` | `heliosparta18@gmail.com` | `heliosparta18` | 55 / 55 | 11 | **66 / 66** |
| **Candia Purwa** | `unknown`, `candiapurwa-sudo` | `candia.purwa@gmail.com` | `candiapurwa-sudo` | 31 / 31 | 12 (3 lokal + 9 PR) | **43 / 43** |
| **Arya Dwi Mahendra** | `Miinggg67` | `arya13767@gmail.com` | `Miinggg67` | 18 / 19 | 4 | **22 / 23** |
| **`dependabot[bot]`** | `dependabot[bot]` | `49699333+dependabot[bot]@users.noreply.github.com` | `app/dependabot` | 0 / 1 | 0 | **0 / 1** |
| **Total** | — | — | — | **169 / 173** | **34** | **203 / 207** |

Rincian per baris `git shortlog -sne origin/main`:
- `68  Guntur Afandi <gunturafandy5@gmail.com>` (64 non-merge, 4 merge)
- `55  heliosparta18 <heliosparta18@gmail.com>` (52 non-merge, 3 merge)
- `34  unknown <candia.purwa@gmail.com>` (31 non-merge, 3 local merge)
- `22  Miinggg67 <arya13767@gmail.com>` (18 non-merge, 4 merge)
- `11  Daffa <heliosparta18@gmail.com>` (3 non-merge, 8 merge)
- ` 9  candiapurwa-sudo <candia.purwa@gmail.com>` (0 non-merge, 9 GitHub PR merge)
- ` 4  Callme Zettx <gunturafandy5@gmail.com>` (1 non-merge, 3 merge)

### 8.3 Rekapitulasi 29 Merged Pull Request dan 3 Open Pull Request (#1, #2, #8)

Pemeriksaan via `gh pr list --state all --limit 50` dan `git log --merges` mencatat **32 Pull Request** (#1 s/d #32): **29 PR berstatus `MERGED`** ke `main` dan **3 PR berstatus `OPEN`** (#1, #2, #8).

| PR | Status | Author GitHub | Merge Commit | Source Branch | Judul Pull Request |
|---|---|---|---|---|---|
| **#1** | `OPEN` | `app/dependabot` | `—` (`2740d8d`) | `dependabot/github_actions/github-actions-0982b03464` | `build(deps): bump the github-actions group across 1 directory with 2 updates` |
| **#2** | `OPEN` | `callmezext` | `—` (`6d7e4ae`, `8f4cb34`) | `feature/firebase-auth-login` | `Feature/firebase auth login` |
| **#3** | `MERGED` | `callmezext` | `96cb27e` | `feat/guntur-katalog-mobile` | `rapikan layout katalog obat dan perbaiki tampilan mobile` |
| **#4** | `MERGED` | `heliosparta18` | `a43a02a` | `feature/pertemuan-04-multi-tenant` | `feat: implementasi multi-tenant pertemuan 04` |
| **#5** | `MERGED` | `callmezext` | `420ec12` | `feat/guntur-checkout-qris` | `Feat/guntur checkout qris` |
| **#6** | `MERGED` | `Miinggg67` | `68a2bb6` | `feat/arya-catalog-fefo` | `Feat/arya catalog fefo` |
| **#7** | `MERGED` | `Miinggg67` | `7a0f9fe` *(squash/rebase)* | `feat/arya-admin-session` | `Feat/arya admin session` |
| **#8** | `OPEN` | `Miinggg67` | `—` (`b599624`) | `feat/arya-catalog-fefo` | `selesaikan antarmuka dan logika modul operasional apotek arya m05-m12` |
| **#9** | `MERGED` | `heliosparta18` | `3739687` | `ui_prototype` | `prototype sementara` |
| **#10** | `MERGED` | `Miinggg67` | `b0a8bd4` | `feat/roles-dashboard` | `feat: redesign dashboard multi-peran sesuai tema hijau dan standar SRS apotek` |
| **#11** | `MERGED` | `Miinggg67` | `e44272f` | `feat/status-progress` | `Feat/status progress` |
| **#12** | `MERGED` | `Miinggg67` | `e44272f` | `feat/antrean` | `Feat/antrean` *(berbagi merge commit `e44272f` dengan PR #11)* |
| **#13** | `MERGED` | `candiapurwa-sudo` | `ece1f2d` | `feat/candi-upload-foto-resep` | `Feat/candi upload foto resep` |
| **#14** | `MERGED` | `heliosparta18` | `a34b35c` | `feat/katalog` | `Feat/katalog` |
| **#15** | `MERGED` | `heliosparta18` | `7f7bd5a` | `feat/fix-button` | `fix` |
| **#16** | `MERGED` | `heliosparta18` | `190436d` | `endpoint_apotekercust` | `Endpoint apotekercust` |
| **#17** | `MERGED` | `heliosparta18` | `ab60ffd` | `feat/katalog` | `rute tombol dasboard` |
| **#18** | `MERGED` | `Miinggg67` | `c726924` | `feat/ceklis-bugfix` | `bug fix fitur ceklis` |
| **#19** | `MERGED` | `candiapurwa-sudo` | `3efaadd` | `feat/candi-upload-foto-resep` | `feat(history): responsif riwayat pesanan dan thumbnail gambar produk` |
| **#20** | `MERGED` | `candiapurwa-sudo` | `d57a9c7` | `feat/candi-upload-foto-resep` | `fix(history): filter riwayat per cabang aktif` |
| **#21** | `MERGED` | `candiapurwa-sudo` | `2f02397` | `feat/candi-upload-foto-resep` | `style: fix pint formatting in CartCheckoutPaymentTest` |
| **#22** | `MERGED` | `candiapurwa-sudo` | `66c26d0` | `feat/candi-upload-foto-resep` | `test: seed stock before creating order in order history test` |
| **#23** | `MERGED` | `Miinggg67` | `7df7345` | `feat/bugfix-burger-layout` | `fix(ui): tampilkan burger menu di desktop, rapikan drawer dan dropdown profil` |
| **#24** | `MERGED` | `Miinggg67` | `fc7c953` | `feat/bugfix-burger-layout-lagi` | `bug fix layout sama burger` |
| **#25** | `MERGED` | `candiapurwa-sudo` | `b9c92f1` | `feat/candi-upload-foto-resep` | `Feat/candi upload foto resep` |
| **#26** | `MERGED` | `candiapurwa-sudo` | `255ad95` | `feat/candi-upload-foto-resep` | `fix(tests): sesuaikan assertion menu profil super admin` |
| **#27** | `MERGED` | `heliosparta18` | `1dbd231` | `feat/animation-ui` | `animasi ui cust` |
| **#28** | `MERGED` | `heliosparta18` | `abe5c8d` | `feat/animation-ui` | `animasi navbar` |
| **#29** | `MERGED` | `heliosparta18` | `a207224` | `feat/tanya-apoteker` | `Feat/tanya apoteker` |
| **#30** | `MERGED` | `heliosparta18` | `fb9410f` | `feat/tanya-apoteker` | `fix wirepoll` |
| **#31** | `MERGED` | `candiapurwa-sudo` | `d85aa92` | `feat/candi-upload-foto-resep` | `Feat/candi upload foto resep` *(smart auto-slide)* |
| **#32** | `MERGED` | `candiapurwa-sudo` | `c39e087` | `feat/candi-upload-foto-resep` | `feat: satukan cabang, alur, dan standar pelayanan menjadi slider` |

### 8.4 Konfigurasi Pipeline CI/CD (`.github/workflows/tests.yml`, `dependabot.yml`, `.gitignore`, `.gitattributes`)

Integrasi berkelanjutan proyek dijaga melalui `.github/workflows/tests.yml` (81 baris):
- **Trigger (`baris 3-6`):** `push` ke branch `main` dan setiap `pull_request`.
- **Service Containers (`baris 15-40`):**
  - `mariadb:11.4` (`MARIADB_DATABASE: kantin_test`, `MARIADB_USER: kantin`, port `3306`, healthcheck `healthcheck.sh --connect --innodb_initialized` tiap 10s, timeout 5s, retries 10).
  - `redis:7` (port `6379`, healthcheck `redis-cli ping` tiap 10s, timeout 5s, retries 10).
- **Environment CI (`baris 42-55`):** `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=kantin_test`, `DB_USERNAME=kantin`, `REDIS_CLIENT=phpredis`, `REDIS_HOST=127.0.0.1`, `REDIS_PORT=6379`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `BROADCAST_CONNECTION=log`.
- **Toolchain & Steps (`baris 57-81`):**
  1. `actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1` (`v7.0.1`, `persist-credentials: false`).
  2. `shivammathur/setup-php@f3e473d116dcccaddc5834248c87452386958240` (`v2`, PHP `8.3`, ekstensi `pdo_mysql, redis, mbstring, bcmath, ctype, curl, fileinfo, openssl, tokenizer, xml`, `composer:v2`).
  3. `actions/setup-node@820762786026740c76f36085b0efc47a31fe5020` (`v7.0.0`, Node `22`).
  4. `composer setup` (`composer install`, `.env`, `key:generate`, `migrate --force`, `npm install`, `npm run build`).
  5. `composer ci:check` (`@php artisan config:clear --ansi`, `pint --parallel --test`, `phpstan analyse`, `php artisan test`).
- **Kebersihan Repositori (`.gitignore` 43 baris & `.gitattributes` 30 baris):**
  - `.gitignore` mengecualikan berkas sensitif (`.env`, `.env.backup`, `.env.production`, `.env.testing`, `/storage/*.key`, `/auth.json`), artefak dependensi/build (`/vendor`, `/node_modules`, `/public/hot`, `/public/storage`, `/public/fonts-manifest.dev.json`, `/public/vendor/livewire`, `/.phpunit.cache`, `.phpunit.result.cache`), direktori IDE/OS (`.fleet`, `.idea`, `.nova`, `.vscode`, `.zed`, `.DS_Store`, `Homestead.*`, `npm-debug.log`, `yarn-error.log`), berkas besar `/references/PDM revisi.pdf`, serta seluruh direktori/berkas agen AI (`.agent/`, `.agents/`, `.claude/`, `.cursor/`, `.copilot/`, `.gemini/`, `skills/`, `*.skill`, `.cursorrules`, `.copilot-instructions.md`, `CLAUDE.md`, `AGENTS.md`, `GEMINI.md`).
  - `.gitattributes` menegakkan akhir baris LF (`* text=auto eol=lf`), menandai format biner (`*.pdf`, `*.docx`, `*.png`, `*.jpg`, `*.jpeg`, `*.webp`, `*.woff2`, dll.), menandai `composer.lock`/`package-lock.json` sebagai `linguist-generated=true`, dan menandai `references/*`, `modul_praktikum/*`, serta `evidence/*` sebagai `linguist-documentation=true`.
  - `.git/hooks/` hanya berisi skrip contoh `.sample` (tidak ada hook lokal aktif); `.github/dependabot.yml` menjadwalkan pembaruan mingguan untuk ekosistem `github-actions`.
  - Berkas sensitif yang terkonfirmasi ada secara lokal dan **di-ignore** (wajib tidak di-`git add -f`): `.env` (1.508 bytes), `storage/app/public/resep/`, `storage/logs/laravel.log`, dan `storage/framework/cache/goapotik_catalog*`. (Catatan: `resources/js/firebase-auth.js` memang di-track git karena hanya memuat konfigurasi klien Web SDK publik `ennchant-rogter`, tanpa private key server.)

---

## Bab 9. Ringkasan Kronologis & Teknis Pekerjaan Seluruh Anggota Tim

### 9.1 Muhammad Daffa Ariby (`heliosparta18` / `Daffa` — 66 Commits)

Muhammad Daffa Ariby berperan sebagai **Lead Architect, Core Multi-Tenancy Engineer, & Integrator Fitur Konsultasi/UI**:

1. **Inisialisasi Repositori & Baseline Fondasi (30 Okt 2026):**
   - `84028e5` (*initial setup* Laravel 13 + Livewire starter kit, 142 berkas, CI `.github/workflows/tests.yml`, `phpstan.neon`, `pint.json`, ADR 0001–0005, dokumen `references/`).
   - `1b6b0d8` (`feat: complete Pertemuan 1 s/d 3 Apotek Multi-Klien implementation`, 201 berkas, +7.507 baris): membangun seluruh kerangka 6 modul `ModuleServiceProvider`, `PortalRoutes`, `EnsureUserHasRole`, 16 entitas PDM UUID, 6 migrasi awal (`2026_09_21_000101`..`000105` + `2026_08_16_000001`), 16 factory, `DemoApotekSeeder`, ADR 0006–0008, `docs/architecture.md`, `docs/data-dictionary.md`, serta 4 suite fondasi (`ApotekDatabaseConstraintTest`, `ApotekFoundationTest`, `ModularFoundationTest`, `ModuleConventionTest`).
2. **Kernel Multi-Tenancy & Anti-IDOR (3–7 Nov 2026):**
   - `866e6bf`, `ced8b60`, `3104b0a`, `30338c3` -> PR #4 `a43a02a`, ditambah `b751c0d`, `e77670b`, `db0191f` dan `f88a082`: membangun `TenantContext`, `TenantManagerInterface`, `TenantScope`, `BelongsToTenant`, `ResolveTenantContext`, `ClientPolicy`, `PesananPolicy`, 4 exception domain tenant, `AuditTenantIsolationCommand` (`tenant:audit`), migrasi `2026_09_22_000101_add_slug_to_client_table.php`, serta `TenantIsolationTest` (15 tests).
3. **Pembersihan Legacy Kantin & Penyelarasan CSP/Gitignore (8–11 Nov 2026):**
   - `3f7a5b8`, `3021cc8`, `48319dd`: menghapus dokumen status lama dan model/seeder legacy kantin (`Canteen`, `Menu`, `DiningTable`, `DemoCanteenSeeder`, -1.014 baris).
   - `20ba36e` & `32a5471`: mengizinkan Vite dev server di CSP lokal dan mengamankan `.gitignore` untuk folder AI.
4. **Katalog Awal GoApotik, Integrasi E2E Resep ke Apoteker, & Unified Multi-Branch Catalog (9–13 Nov 2026, PR #14–#17):**
   - `f9c1007` & `a67d47c`: mengintegrasikan dataset awal 42 produk (`goapotik_catalog_42.json`, `GoApotikCatalogSeeder`, `GoApotikCatalogIntegrationTest`) dan shortcut `/katalog`.
   - `864184f` & `f9dca74`: menghubungkan endpoint `customer.resep.upload` dengan antrean telaah apoteker (`tenant/dashboard.blade.php`, `tenant/verifikasi-resep.blade.php`), migrasi `2026_11_13_000002_add_catatan_to_resep_table.php`, serta `CustomerPrescriptionUploadAndQueueTest` (15 tests, 69 assertions).
   - `591ead3`, `6c821f6`, `45aa671`, `8418d4c`: membangun alokasi stok multi-cabang deterministik (`UnifiedCatalogAndMultiBranchAvailabilityTest`, 8 tests) sehingga produk yang kosong di cabang aktif otomatis menawarkan tombol alih ke cabang alternatif yang memiliki stok.
   - `e41b2f8`, `1131b25`, `904c648`, `4695b02`, `364efd5`: merapikan profil, shortcut `/apoteker`, `/antrean`, `/verifikasi-resep`, `/stok-fefo`, `/kasir`, `/admin`, serta `welcome-splash.blade.php`.
5. **Dokumentasi Presentasi, Animasi UI Sliding, & Fitur Livewire Tanya Apoteker (19–22 Nov 2026, PR #27–#30):**
   - `c51340d` & `198e118`: menyusun `PANDUAN_PRESENTASI_DAN_ROADMAP_OMNIPHARMA.md`.
   - `a399552`, `3c7f9db`, `f06f891` (PR #27 `1dbd231`, PR #28 `abe5c8d`): animasi sliding indikator kategori/subkategori katalog (`catalog-category-tabs.blade.php`, `catalog-subcategory-sidebar.blade.php`), animasi sliding navbar Super Admin, skrip verifikasi Playwright `scripts/verify-ui-animations.mjs` (678 baris), serta test `ServiceAndCatalogAnimationVerificationTest` & `SuperAdminSlidingNavbarTest`.
   - `00bccb1`, `57754f2`, `9f55305`, `7051c69` (PR #29 `a207224`, PR #30 `fb9410f`): membangun fitur **Tanya Apoteker** Livewire real-time polling (`TanyaApotekerModal.php` 397 baris, `ApotekerChatPortal.php` 278 baris), model `Konsultasi` & `PesanKonsultasi`, migrasi `2026_11_21_000001`, validasi lampiran gambar *magic bytes*, serta `TanyaApotekerTest` (21 tests).

**Log Kronologis Lengkap 66 Commit Muhammad Daffa Ariby (`origin/main`):**

| Hash | Waktu (`+07:00`) | Pesan Commit (Terverifikasi `git log`) | Fase / Cakupan Teknis |
|---|---|---|---|
| `84028e5` | `2026-10-30 08:18` | `startup` | Inisialisasi Laravel 13, CI, PHPStan, Pint, ADR 0001–0005, `references/`. |
| `fb838a4` | `2026-10-30 08:32` | `Update README.md` | Pembaruan dokumentasi awal proyek. |
| `88b36c2` | `2026-10-30 08:33` | `Update README.md` | Penyelarasan catatan readme awal. |
| `1b6b0d8` | `2026-10-30 21:26` | `feat: complete Pertemuan 1 s/d 3 Apotek Multi-Klien implementation` | 6 modul, 16 model/migrasi/factory UUID, `DemoApotekSeeder`, ADR 0006–0008. |
| `866e6bf` | `2026-11-03 18:07` | `bikin tenant context dan middleware resolusi tenant dari parameter url` | `TenantContext`, `TenantManagerInterface`, `ResolveTenantContext`. |
| `ced8b60` | `2026-11-03 18:07` | `tambah tenant scope global dan trait belongs to tenant di model transaksi` | `TenantScope`, `BelongsToTenant`. |
| `3104b0a` | `2026-11-03 18:07` | `pasang policy otorisasi cegah apoteker intip transaksi cabang lain` | `ClientPolicy`, `PesananPolicy`. |
| `30338c3` | `2026-11-03 18:08` | `tambah unit test isolasi tenant dan cegah idor antar cabang` | `TenantIsolationTest` awal. |
| `a43a02a` | `2026-11-03 19:56` | `Merge pull request #4 from heliosparta18/feature/pertemuan-04-multi-tenant` | Integrasi M04 ke `main`. |
| `b751c0d` | `2026-11-03 20:13` | `fix : benerin tipe phpstan dan format kode tenant scope` | Perbaikan tipe PHPStan `TenantScope`. |
| `e77670b` | `2026-11-03 20:14` | `fix: bereskan sisa error linter dan tipe di middleware serta trait` | Pint & PHPStan `ResolveTenantContext` & `BelongsToTenant`. |
| `db0191f` | `2026-11-03 20:39` | `fix: tambah properti status pada model tenant` | Penyelarasan properti model `Client` & `User`. |
| `6d2e8b5` | `2026-11-07 11:37` | `Update README.md` | Pembaruan catatan progres. |
| `cf88c83` | `2026-11-07 22:20` | `fix(tests): perbaiki kredensial testing di phpunit.xml, format rute, dan inisialisasi stok cabang...` | Perbaikan konfigurasi testing & stok cabang. |
| `f88a082` | `2026-11-07 22:27` | `daffa` | `AuditTenantIsolationCommand`, `PRD_DAFFA.md`, exceptions & events tenant. |
| `96d727e` | `2026-11-08 21:48` | `feat(ui): implementasi antarmuka prototipe login OmniPharma multi-klien dengan role switcher demo` | Prototipe antarmuka login role switcher. |
| `3739687` | `2026-11-08 21:56` | `prototype sementara` | Merge PR #9 `ui_prototype`. |
| `1b18bb9` | `2026-11-09 09:11` | `fix: konfigurasi gitattributes binary linguist dan keluarkan pdf referensi dari git tracking` | `.gitattributes` & `.gitignore` `PDM revisi.pdf`. |
| `9787664` | `2026-11-09 09:21` | `Delete evidence directory` | Pembersihan folder `evidence/` dari tracking git. |
| `2e998e0` | `2026-11-09 09:22` | `Delete references/srs_apotek_text.txt` | Penghapusan file teks ekstraksi duplikat. |
| `99ce4d5` | `2026-11-09 09:23` | `Delete references/pdm_revisi_text.txt` | Penghapusan file teks ekstraksi duplikat. |
| `dd88804` | `2026-11-09 09:23` | `Delete references/mockup_text.txt` | Penghapusan file teks ekstraksi duplikat. |
| `20ba36e` | `2026-11-09 11:14` | `fix(security,build): dukung vite dev server di csp lokal dan mutakhirkan aset build produksi` | CSP Vite dev port `5173` di `SecurityHeadersMiddleware`. |
| `32a5471` | `2026-11-09 11:18` | `build: abaikan direktori skills dan konfigurasi ai pada gitignore` | Pembaruan `.gitignore` folder AI. |
| `1845248` | `2026-11-09 12:51` | `fix(ci,ui): resolve CI phpstan type errors, prevent redirect to default laravel page, and make cart reactive` | Perbaikan PHPStan Level 7 CI & redirect auth. |
| `36a15d8` | `2026-11-09 13:19` | `feat(navigation): add bidirectional Dashboard Utama navigation across customer, tenant, and admin portals` | Navigasi dua arah Dashboard Utama + `DashboardTest`. |
| `3f7a5b8` | `2026-11-09 20:07` | `penghapusan file tidak penting` | Pembersihan berkas status lama. |
| `3021cc8` | `2026-11-10 14:07` | `hapus sampah kantin` | Penghapusan legacy model/seeder kantin (`Canteen`, `Menu`, dll.). |
| `48319dd` | `2026-11-11 17:59` | `tugas sementara` | Sinkronisasi backlog dokumentasi sementara. |
| `f9c1007` | `2026-11-13 09:56` | `feat(catalog): integrasi fitur katalog obat & kesehatan goapotik bertema hijau-putih` | `goapotik_catalog_42.json`, `GoApotikCatalogSeeder`, `GoApotikCatalogIntegrationTest`. |
| `a67d47c` | `2026-11-13 10:27` | `feat(navigation): tambahkan shortcut route /katalog dan tombol navigasi katalog di navbar & dashboard` | Shortcut `/katalog` di `routes/web.php`. |
| `ca1f88f` | `2026-11-13 10:43` | `fix(welcome): kembalikan teks tombol CTA guest Mulai Belanja` | Perbaikan CTA guest di `welcome.blade.php`. |
| `a34b35c` | `2026-11-13 11:00` | `katalog fix` | Merge PR #14 `feat/katalog`. |
| `48a0f55` | `2026-11-13 11:24` | `fix(welcome): perbaiki rute tombol mulai belanja & katalog ke login serta perbarui nama apoteker rogojampi` | Pembaruan nama apoteker Rogojampi (`apt. Dinda Ayu Shafira, S.Farm.`). |
| `7f7bd5a` | `2026-11-13 12:01` | `fix button dasboard` | Merge PR #15 `feat/fix-button`. |
| `864184f` | `2026-11-13 12:38` | `feat(prescription): integrasi e2e upload resep customer ke antrean telaah apoteker` | Integrasi `customer.resep.upload` -> `tenant.resep.antrean`, `CustomerPrescriptionUploadAndQueueTest`. |
| `f9dca74` | `2026-11-13 12:52` | `fix(prescription): perbaiki isolasi tenant verifikasi resep, resolusi akun, event broadcast, dan sanitasi input` | Penambahan audit & pemulihan stok saat resep ditolak. |
| `190436d` | `2026-11-13 13:22` | `endpoint_apotekercust` | Merge PR #16 `endpoint_apotekercust`. |
| `591ead3` | `2026-11-13 13:55` | `feat(catalog): implementasikan unified catalog, alokasi stok multi-cabang deterministik, dan indikator ketersediaan cabang` | `UnifiedCatalogAndMultiBranchAvailabilityTest`, tombol alih cabang alternatif. |
| `f272a2e` | `2026-11-13 15:48` | `Merge branch 'origin/main' into feat/katalog: rekonsiliasi navbar dropdown profil...` | Resolusi merge cabang katalog. |
| `6c821f6` | `2026-11-13 16:00` | `fix(catalog): tentukan cabang utama banyuwangi-kota sebagai fallback deterministik getNearestBranch` | Fallback deterministik cabang terdekat. |
| `45aa671` | `2026-11-13 16:09` | `fix(dashboard): pastikan tap target aksesibel min 44px, tautan katalog dinamis berbasis sesi cabang, dan uji alokasi stok eksak` | Tap target `44px` & test alokasi stok. |
| `8418d4c` | `2026-11-13 16:23` | `style(ci): rapikan formatting kode pint dan sesuaikan nullsafe phpstan untuk lolos ci check` | Pint & PHPStan CI fix. |
| `ab60ffd` | `2026-11-13 16:34` | `fix katalog, guntur rusuh bet ah elah` | Merge PR #17 `feat/katalog`. |
| `14afea8`..`364efd5` | `2026-11-13 16:49–18:36` | `fix shortcut`, `fix profile` (x4), `feat(auth): tambahkan animasi welcome splash screen hijau saat login dan registrasi` | Shortcut role, penyempurnaan profil, & `welcome-splash.blade.php` (6 commits). |
| `c51340d`, `198e118` | `2026-11-19 17:37–19:29` | `docs: tambah panduan lengkap presentasi...`, `docs: bersihkan dokumen panduan presentasi...` | Penyusunan `PANDUAN_PRESENTASI_DAN_ROADMAP_OMNIPHARMA.md`. |
| `a399552`, `3c7f9db`, `1dbd231` | `2026-11-20 12:15–12:33` | `feat(catalog-ui): tambahkan animasi sliding kategori, subkategori...`, `fix(lint)`, PR #27 | Animasi sliding UI katalog + `verify-ui-animations.mjs` + `ServiceAndCatalogAnimationVerificationTest`. |
| `f06f891`, `abe5c8d` | `2026-11-20 14:25–14:32` | `feat(admin-ui): tambahkan animasi sliding navbar super admin...`, PR #28 | `SuperAdminSlidingNavbarTest` & animasi navbar admin. |
| `1bb59f5`, `052cb79`, `e975af3` | `2026-11-21 12:29–12:31` | `Delete TUGAS_DAFFA.md`, `Delete perbaikan.md`, `Delete TUGAS_DAFFA.md` | Pembersihan berkas catatan tugas lama. |
| `00bccb1`..`fb9410f` | `2026-11-21 s/d 2026-11-22` | `feat(chat): implementasi fitur tanya apoteker...`, build, generic PHPStan, PR #29, `fix(chat): perbaiki wire:poll...`, PR #30 | Fitur lengkap Tanya Apoteker Livewire (`Konsultasi`, `PesanKonsultasi`, `TanyaApotekerTest` 21 tests) (6 commits). |

### 9.2 Guntur Afandi (`Guntur Afandi` / `Callme Zettx` / `callmezext` — 72 `main` / 74 `--all` Commits)

Guntur Afandi adalah kontributor dengan volume commit tertinggi (`72` pada `main`) yang mengampu **Commerce Pipeline (M08–M11), QRIS Gateway, Autentikasi Firebase/OTP, Scraping Katalog GoApotik, Portal Kasir, & Mobile Responsiveness**:

1. **Layout Awal, Roadmap, & PRD Anggota (30 Okt – 2 Nov 2026):**
   - `d88c465`, `6ef04e2` (PR #3 `96cb27e`): inisiasi `README.md` serta perbaikan layout katalog dan mobile touch target `>= 44px`.
   - `3aa626f` & `fc198c9`: menulis `docs/ROADMAP_DAN_PLANNING_PROYEK.md` serta 4 PRD pembagian modul (`PRD_ARYA.md`, `PRD_CANDI.md`, `PRD_DAFFA.md`, `PRD_GUNTUR.md`).
2. **Keranjang Redis, Checkout Atomik, QRIS EMVCo, & Webhook (5–8 Nov 2026, PR #5 `420ec12` + hardening):**
   - `785f318`, `e3dc7d3`, `121b4b8`, `b2b7118`: membangun `CartService` (Redis Hash `cart:{sessionId}:{clientId}`, TTL 7 hari), `CreateOrderAction` (`lockForUpdate` + snapshot `item_pesanan`), `PaymentGatewayInterface`, `FakeQrisGateway`, `QrisEmvcoBuilder` (CRC16 CCITT), `PaymentWebhookController` (HMAC-SHA256 + idempotensi), tampilan `cart.blade.php`, `checkout.blade.php`, `payment.blade.php`, dan `CartCheckoutPaymentTest`.
   - `44f5676`, `de41df9`, `db9b6cf`, `9933b8d`: menutup celah IDOR tenant payment, memperketat verifikasi signature QRIS, serta membangun `CancelOrderAction` (restorasi stok saat pesanan dibatalkan).
   - `71cc2d7`, `9729892`, `d5e3f19`, `7fa2fa9`: menyusun `docs/PRD_DESIGN_AND_RULES.md` (Aturan Anti-Slop UI & 22 master artboard FigCraft).
3. **Firebase Google Auth, Email OTP, Guest Reverb, Controller Refactor, & Mobile Anti-Slop (9–13 Nov 2026):**
   - `d3bb1b7`: migrasi `2026_11_09_000001_add_token_sesi_to_pesanan_table.php` dan otorisasi tamu di `routes/channels.php`.
   - `65da189` & `db2dca5`: Firebase Auth (`FirebaseAuthSessionController`, `resources/js/firebase-auth.js`, `FirebaseAuthTest`), Email OTP (`EmailOtpAuthController`, `complete-profile.blade.php`, `EmailOtpAuthTest`), serta 1-click demo login di halaman login.
   - `19efcbe`, `abc6300`, `8c4534c`, `dbf6635`, `af062a4`: membangun `welcome.blade.php`, audit responsif mobile pada 10+ view, refactor closure ke `CartController`, `CheckoutController`, `OrderTrackingController`, integrasi `UnggahResepRequest` & `VerifikasiResepRequest` + `EndToEndOrderFlowTest`, dan dokumentasi `README_TUGAS_ANGGOTA_DAN_BACKLOG.md` / `AKUN_LOGIN.md`.
   - `e1c9cea` & `f1f97e2`: bundling Firebase Web SDK via Vite (`npm run build`) dan penyesuaian CSP di `SecurityHeadersMiddleware`.
4. **Scraping Katalog Penuh GoApotik, Checkout Maps Haversine, & Portal Kasir/4-Role (13–16 Nov 2026):**
   - `6df401c`..`816d87a`: membangun scraper Python (`scrape_goapotik.py`, `scrape_goapotik_full.py`, `scrape_goapotik_massive.py`), aset gambar SVG/PNG di `public/images/goapotik/`, `GoApotikCatalogAssets.php`, `GoApotikFullCatalogSeeder.php` (hingga **2.424 produk** di `scraped_goapotik_lengkap.json` pada `227ee67`), fitur GPS checkout + kalkulasi ongkir jarak Haversine (`<= 2 km` Rp 5.000, `+Rp 2.500/km`), dan integrasi Midtrans Sandbox + timer persisten QRIS.
   - `68cc58e`, `69a6d0e`, `4f75d52`, `b233a2f`, `4df4488`, `4890c77`: menegakkan aturan *"no decorative green dots"*, membangun **Portal Kasir POS** (`routes/kasir.php` 401 baris, `kasir/{dashboard,ledger,struk}.blade.php`, `RoleCrudAndKasirPortalTest`), halaman `admin/obat-crud.blade.php` & `admin/cek-apoteker.blade.php`, `mobile-nav-drawer.blade.php`, migrasi `2026_11_15_000001` & `2026_11_16_000001`, serta lazy catalog sync di `CatalogService.php`.

**Log Kronologis Lengkap 72 Commit `origin/main` (+2 Commit PR #2) Guntur Afandi:**

| Periode & Hash Utama | Daftar Commit & Pesan Ringkas (`git log`) | Dampak Teknis & Berkas Utama |
|---|---|---|
| **30 Okt 2026** (`d88c465`, `6d7e4ae`, `8f4cb34`, `6ef04e2`, `96cb27e`) | `Create README.md`; prototipe Firebase/Diprella PR #2 (open); `rapikan layout katalog obat dan perbaiki tampilan mobile` (PR #3). | `README.md`, `customer/home.blade.php`, `obat-detail.blade.php`, layouts mobile 44px. |
| **1–2 Nov 2026** (`3aa626f`, `fc198c9`) | `add project roadmap and PRD documentation for team members` & pembaruan PRD modul. | `docs/ROADMAP_DAN_PLANNING_PROYEK.md`, `PRD_ARYA.md`, `PRD_CANDI.md`, `PRD_DAFFA.md`, `PRD_GUNTUR.md`. |
| **5–7 Nov 2026** (`785f318`, `e3dc7d3`, `121b4b8`, `b2b7118`, `44f5676`, `68a2bb6`, `420ec12`, `de41df9`, `db9b6cf`, `9933b8d`) | Implementasi M08–M11 keranjang Redis, checkout atomik, QRIS EMVCo, webhook HMAC, pembatalan pesanan, & dokumentasi README (10 commits). | `CartService`, `CreateOrderAction`, `CancelOrderAction`, `PaymentGatewayInterface`, `FakeQrisGateway`, `QrisEmvcoBuilder`, `PaymentWebhookController`, `CartCheckoutPaymentTest`. |
| **8 Nov 2026** (`71cc2d7`, `9729892`, `d5e3f19`, `7fa2fa9`) | Audit progres modul Candi, `PRD_DESIGN_AND_RULES.md`, inventaris 22 artboard FigCraft, & panduan demo. | `docs/PRD_DESIGN_AND_RULES.md`, perbaikan rute tracking customer. |
| **9 Nov 2026** (`d3bb1b7`, `65da189`, `3080dc0`, `0281e33`, `5d02438`, `b166add`, `db2dca5`, `d55163c`, `19efcbe`, `c1f74f7`, `d6a49ef`, `36d514c`, `abc6300`, `8c4534c`, `dbf6635`, `b0a8bd4`, `ac4898a`, `af062a4`) | `token_sesi` + guest Reverb auth, Firebase Google Sign-In, Email OTP + `complete-profile`, 1-click demo login, emblem logo farmasi, `welcome.blade.php`, overhaul mobile anti-slop 10+ views, refactor `Cart/Checkout/OrderTrackingController`, FormRequest security, & `EndToEndOrderFlowTest` (18 commits). | `2026_11_09_000001`, `EmailOtpAuthController`, `FirebaseAuthSessionController`, `firebase-auth.js`, `CartController`, `CheckoutController`, `OrderTrackingController`, `UnggahResepRequest`, `VerifikasiResepRequest`, `README_TUGAS_ANGGOTA_DAN_BACKLOG.md`, `EndToEndOrderFlowTest`. |
| **11–13 Nov 2026** (`7b8acac`, `312e4ff`, `0d6d94f`, `887cbdc`, `f9f536c`, `decd222`, `2fc2c4e`, `ee5b90b`, `e1c9cea`, `d0e4365`, `b2369ff`, `f1f97e2`) | Penyempurnaan landing page, `AKUN_LOGIN.md`, penyelarasan desain login/register/dashboard, bundling Firebase via Vite, & pemulihan CSP (12 commits). | `welcome.blade.php`, `AKUN_LOGIN.md`, `login.blade.php`, `register.blade.php`, `SecurityHeadersMiddleware.php`. |
| **13–14 Nov 2026** (`6df401c`, `01175b9`, `e886b79`, `0fae6d2`, `d49530a`, `aeff390`, `783cc9a`, `62d86c3`, `7278774`, `816d87a`) | Transformasi portal e-commerce GoApotik, live search `/api/search-obat`, scraping Python & dataset kategori, checkout peta GPS & ongkir Haversine, Midtrans Sandbox, lulus 185 tes (10 commits). | `scripts/scrape_goapotik*.py`, `GoApotikCatalogAssets`, `GoApotikFullCatalogSeeder`, `2026_11_13_000001`, `gps-location-prompt`, `dashboard.blade.php`. |
| **15 Nov 2026** (`be29f32`, `68cc58e`, `69a6d0e`, `257aec8`, `c75dc18`, `4f75d52`, `b233a2f`, `4df4488`, `cf3c64a`, `dd17f94`) | Timer QRIS persisten, aturan tanpa titik hijau dekoratif, **Portal Kasir POS**, CRUD Cabang/Obat, menu profil adaptif + `admin/cek-apoteker`, optimasi Redis/WAL/pagination, & `mobile-nav-drawer` (10 commits). | `routes/kasir.php`, `kasir/{dashboard,ledger,struk}`, `admin/{obat-crud,cek-apoteker}`, `2026_11_15_000001`, `RoleCrudAndKasirPortalTest`, `User::profileActions()`. |
| **16 Nov 2026** (`ff2569f`, `227ee67`, `4890c77`) | Perbaikan race condition demo login, perluasan scraping GoApotik (2.424 produk), & migrasi sync katalog otomatis (3 commits). | `scraped_goapotik_lengkap.json` (2.424 produk), `2026_11_16_000001`, `CatalogService::ensureCatalogSynchronized()`. |

### 9.3 Candia Purwa (`unknown` / `candiapurwa-sudo` — 43 Commits Lengkap dalam 4 Fase)

Seluruh **43 commit** milik Candia Purwa pada `origin/main` (`2026-11-07` s/d `2026-11-23`) terbagi ke dalam 4 fase terverifikasi sebagai berikut:

1. **Fase 1 — Pilar Kurikulum M12-RT, M13, & M14 (7–8 Nov 2026, 20 commits):**
   - `026aa90`: Setup konfigurasi Laravel Reverb port 8080 (`config/reverb.php`).
   - `8e311f7`: Buat event `OrderStatusUpdated` untuk broadcast private channel.
   - `1852240`: Tambah event `IncomingOrderPlaced` untuk antrean pesanan cabang.
   - `bd6ed8f`: Pasang otorisasi private channel `order` dan `tenant` di `routes/channels.php`.
   - `a77c27f`: Buat view pelacakan pesanan real-time dengan Laravel Echo (`customer/order-track.blade.php`).
   - `ed82626`: Tambah test suite `ReverbOrderStatusBroadcastTest.php`.
   - `62fe550`: Perbaiki penamaan channel private Reverb (`order.`/`tenant.`) dan listener Echo (`.OrderStatusUpdated`).
   - `853d664`: Update model `RingkasanPeriodePenjualan` & proteksi composite key.
   - `8e10dad`: Buat `SalesReportService` untuk agregasi data penjualan & metrik transaksi.
   - `ea2acd3`: Implementasi `CloseSalesPeriodAction` (ADR-0008 snapshot terkunci).
   - `cfbb887`: Tambah `SalesReportExportService` (CSV & mitigasi formula injection).
   - `ad1da04`: Buat tampilan `admin/laporan-penjualan.blade.php`, template `admin/pdf/laporan-penjualan-template.blade.php`, dan rute di `routes/tenant.php`.
   - `55f5a29` & `42be2fa`: Tambah `SalesReportAndPeriodLockTest` & selaraskan agregasi dengan tabel `item_pesanan`.
   - `fb13c90`: Implementasi `SecurityHeadersMiddleware` & registrasi di `bootstrap/app.php`.
   - `30cc5ad`: Konfigurasi `RateLimiter` (`api`, `broadcasting`, `transaksi`) di `AppServiceProvider.php`.
   - `7fec97f`: Tambah `SecurityHardeningTest.php`.
   - `fea8438`, `3aa0730`, `69d1e81`, `c397112`, `5516ab8`: Perbaikan stok checkout & IDOR, penyelarasan test Reverb, merge dengan `feat/daffa-security-e2e` (`f88a082`), formatting Pint 16 berkas, dan perbaikan PHPStan annotations.
2. **Fase 2 — Fitur Browse & Drag-Drop Upload Foto Resep (11 Nov 2026, 3 commits):**
   - `7228628` -> `7470bb1` -> PR #13 `ece1f2d`: Menambahkan komponen drag-drop & browse foto resep dengan validasi ukuran/MIME + pratinjau di `customer/home.blade.php`, endpoint upload disk `public/resep` di `routes/customer.php`, serta 2 test *magic bytes* di `SecurityHardeningTest.php`.
3. **Fase 3 — Riwayat Pesanan Responsif, Filter Cabang, Profil Telp/Alamat, & Lock Stok Habis (17–19 Nov 2026, 12 commits):**
   - `6049fa6` -> PR #19 `3efaadd`: Redesign responsif `order-history.blade.php` dengan thumbnail produk dan tab status Tokopedia-style.
   - `1c42e42` -> PR #20 `d57a9c7`: Penambahan filter cabang aktif & deteksi pesanan cabang lain pada `OrderTrackingController::history()` dan `order-history.blade.php`.
   - `c495448` -> PR #21 `2f02397` & `b547652` -> PR #22 `66c26d0`: Pint formatting & seeding stok pada test riwayat pesanan di `CartCheckoutPaymentTest.php`.
   - `75f4882` -> `7ef7f1a` -> PR #25 `b9c92f1`: Migrasi `2026_11_18_000001_add_phone_and_address_to_users_table.php`, accessor/mutator `User.php`, `ProfileValidationRules.php`, update `FirebaseAuthSessionController.php`, pembaruan UI `⚡profile.blade.php` & `⚡security.blade.php`, tampilan lock hijau `Stok Habis (Terkunci)` di `catalog-product-card.blade.php`, serta test di `ProfileUpdateTest.php` & `GoApotikCatalogIntegrationTest.php`.
   - `3a4af30` -> PR #26 `255ad95`: Penyesuaian assertion menu profil Super Admin pada `RoleCrudAndKasirPortalTest.php`.
4. **Fase 4 — Landing Page Slider Interaktif & Navigasi Cabang (22–23 Nov 2026, 5 commits):**
   - `60e2f7d`: Perbaikan tampilan mobile `welcome.blade.php` menjadi slide interaktif + dukungan `redirect_to` di `routes/web.php:78-80`.
   - `acb3013` -> PR #31 `d85aa92`: Penambahan *smart auto-slide* pada alur & standar pelayanan di `welcome.blade.php`.
   - `bb079a1` -> PR #32 `c39e087`: Penyatuan Cabang, Alur Pasien, dan Standar Pelayanan menjadi slider 3-in-1 terpadu di `welcome.blade.php`.

**Log Kronologis Lengkap Seluruh 43 Commit Candia Purwa (Terurut dari Terbaru ke Awal, Verifikasi `git log --all --author="candia"`):**

| No | Hash | Waktu (`+07:00`) | Author di Git Log | Pesan Commit Resmi |
|---|---|---|---|---|
| 1 | `c39e087` | `2026-11-23 03:15:34` | `candiapurwa-sudo` | `Merge pull request #32 from heliosparta18/feat/candi-upload-foto-resep` |
| 2 | `bb079a1` | `2026-11-23 03:13:57` | `unknown` | `feat: satukan cabang, alur, dan standar pelayanan menjadi slider` |
| 3 | `d85aa92` | `2026-11-23 02:55:55` | `candiapurwa-sudo` | `Merge pull request #31 from heliosparta18/feat/candi-upload-foto-resep` |
| 4 | `acb3013` | `2026-11-23 02:54:30` | `unknown` | `feat: tambahkan smart auto-slide pada alur dan standar pelayanan` |
| 5 | `60e2f7d` | `2026-11-22 23:50:19` | `unknown` | `feat: rapikan tampilan mobile jadi slide interaktif dan` |
| 6 | `255ad95` | `2026-11-19 21:06:04` | `candiapurwa-sudo` | `Merge pull request #26 from heliosparta18/feat/candi-upload-foto-resep` |
| 7 | `3a4af30` | `2026-11-19 21:01:39` | `unknown` | `fix(tests): sesuaikan assertion menu profil super admin` |
| 8 | `b9c92f1` | `2026-11-19 20:38:20` | `candiapurwa-sudo` | `Merge pull request #25 from heliosparta18/feat/candi-upload-foto-resep` |
| 9 | `7ef7f1a` | `2026-11-19 20:31:42` | `unknown` | `Merge branch 'main' into feat/candi-upload-foto-resep` |
| 10 | `75f4882` | `2026-11-18 21:42:25` | `unknown` | `feat(settings,catalog): tambah no telp alamat profil, rapikan` |
| 11 | `66c26d0` | `2026-11-17 21:47:12` | `candiapurwa-sudo` | `Merge pull request #22 from heliosparta18/feat/candi-upload-foto-resep` |
| 12 | `b547652` | `2026-11-17 21:39:57` | `unknown` | `test: seed stock before creating order in order history test` |
| 13 | `2f02397` | `2026-11-17 21:33:22` | `candiapurwa-sudo` | `Merge pull request #21 from heliosparta18/feat/candi-upload-foto-resep` |
| 14 | `c495448` | `2026-11-17 21:26:43` | `unknown` | `style: fix pint formatting in CartCheckoutPaymentTest` |
| 15 | `d57a9c7` | `2026-11-17 21:17:23` | `candiapurwa-sudo` | `Merge pull request #20 from heliosparta18/feat/candi-upload-foto-resep` |
| 16 | `1c42e42` | `2026-11-17 21:11:32` | `unknown` | `fix(history): filter riwayat per cabang aktif` |
| 17 | `3efaadd` | `2026-11-17 20:22:03` | `candiapurwa-sudo` | `Merge pull request #19 from heliosparta18/feat/candi-upload-foto-resep` |
| 18 | `6049fa6` | `2026-11-17 20:20:16` | `unknown` | `feat(history): responsif riwayat pesanan dan thumbnail gambar produk` |
| 19 | `ece1f2d` | `2026-11-11 23:34:07` | `candiapurwa-sudo` | `Merge pull request #13 from heliosparta18/feat/candi-upload-foto-resep` |
| 20 | `7470bb1` | `2026-11-11 22:10:23` | `unknown` | `merge: browsfile` |
| 21 | `7228628` | `2026-11-11 21:34:15` | `unknown` | `feat(prescription): add browse file fitur upload resep` |
| 22 | `5516ab8` | `2026-11-08 10:01:27` | `unknown` | `candia` |
| 23 | `c397112` | `2026-11-08 01:58:02` | `unknown` | `style: format kode otomatis menggunakan laravel pint` |
| 24 | `69d1e81` | `2026-11-08 01:32:01` | `unknown` | `merge: selesaikan konflik pengujian checkout dan gabungkan fitur keamanan ke main` |
| 25 | `3aa0730` | `2026-11-08 01:14:08` | `unknown` | `selesaikan pengujian reverb dan perbaiki skenario stok checkout` |
| 26 | `fea8438` | `2026-11-08 01:10:35` | `unknown` | `test(ordering): sediakan stok obat cabang pada skenario checkout dan idor` |
| 27 | `7fec97f` | `2026-11-08 00:32:49` | `unknown` | `tambah automated test untuk security headers csp dan rate limiter` |
| 28 | `30cc5ad` | `2026-11-08 00:31:33` | `unknown` | `konfigurasi rate limiter proteksi brute force api dan broadcast` |
| 29 | `fb13c90` | `2026-11-08 00:26:59` | `unknown` | `implementasi security headers middleware dan konfigurasi csp` |
| 30 | `42be2fa` | `2026-11-08 00:04:37` | `unknown` | `selaraskan agregasi laporan dengan tabel item_pesanan dan selesaikan pengujian periode terkunci` |
| 31 | `55f5a29` | `2026-11-07 23:28:51` | `unknown` | `tambah test kalkulasi omzet dan verifikasi kekebalan periode terkunci` |
| 32 | `ad1da04` | `2026-11-07 23:25:37` | `unknown` | `buat tampilan laporan analitik template pdf dan registrasi rute` |
| 33 | `cfbb887` | `2026-11-07 23:02:20` | `unknown` | `tambah service ekspor laporan csv dan mitigasi formula injection` |
| 34 | `ea2acd3` | `2026-11-07 23:00:49` | `unknown` | `implementasi penutupan periode kas terkunci sesuai adr 0008` |
| 35 | `8e10dad` | `2026-11-07 22:58:09` | `unknown` | `buat service agregasi data penjualan dan metrik transaksi` |
| 36 | `853d664` | `2026-11-07 22:55:04` | `unknown` | `update model ringkasan periode dan proteksi composite key` |
| 37 | `62fe550` | `2026-11-07 22:42:14` | `unknown` | `perbaiki penamaan channel private reverb dan listener echo` |
| 38 | `ed82626` | `2026-11-07 22:21:53` | `unknown` | `tambah test suite pengujian siaran event reverb` |
| 39 | `a77c27f` | `2026-11-07 22:20:17` | `unknown` | `bikin view pelacakan pesanan real-time dengan laravel echo` |
| 40 | `bd6ed8f` | `2026-11-07 22:17:15` | `unknown` | `pasang otorisasi private channel order dan tenant di routes` |
| 41 | `1852240` | `2026-11-07 22:06:52` | `unknown` | `tambah event incoming order placed untuk antrean pesanan cabang` |
| 42 | `8e311f7` | `2026-11-07 22:01:42` | `unknown` | `buat event order status updated untuk broadcast private channel` |
| 43 | `026aa90` | `2026-11-07 21:55:40` | `unknown` | `setup konfigurasi laravel reverb port 8080` |

### 9.4 Arya Dwi Mahendra (`Miinggg67` — 22 `main` / 23 `--all` Commits)

Arya Dwi Mahendra mengampu **Operasional Cabang, Staf APJ 5NF, Sesi Tamu, FEFO Inventory, Antrean Loket, Redesign Tema Hijau SRS, & Perbaikan Navigasi**:

1. **Layanan Operasional Inti M05–M12 (2–8 Nov 2026, PR #7, PR #6 `68a2bb6`, & `b599624`):**
   - `4fa5ec1`, `7a0f9fe`, `11a3d3b`, `083ded7`, `288a792`, `dd44777`, `847a226`, `b58c00b`: membangun `StaffManagementService` (5NF `peta_pengawas_staf` & `peran_client_pengguna`), `StaffController`, `CustomerSessionService` (opaque token 32-karakter `omnipharma_customer_session`), `CatalogService`, `PublicCatalog` Livewire, `VerifyPrescriptionAction` (validasi format SIP `XXX/SIP/YYYY`), dan `StockFefoService` (`orderBy('expired', 'asc')`, peringatan dini H-30 & kritis H-7).
   - View `admin/staff-management.blade.php`, `tenant/stok-fefo.blade.php`, `tenant/verifikasi-resep.blade.php`, `customer/catalog.blade.php` serta 3 suite test (`FefoStockRotationTest`, `GuestSessionAndCatalogTest`, `PrescriptionVerificationTest`) pada commit `b599624` (PR #8 open) yang diintegrasikan ke `main` lewat `3080dc0`.
2. **Redesign Dashboard Multi-Peran Standar SRS (9 Nov 2026, PR #10 `b0a8bd4`):**
   - `f62576e`: merombak tampilan `dashboard.blade.php`, `admin/dashboard.blade.php`, `tenant/dashboard.blade.php`, dan layout dengan tema hijau medis serta ekstraksi spesifikasi SRS `references/srs_apotek_extracted.txt`.
3. **Antrean Loket Apoteker, Progresi Status Reverb, & Live Monitoring Super Admin (11 Nov 2026, PR #11/#12 `e44272f`):**
   - `deefce5`, `246930d`, `fb45319`, `aa0bf9b`: migrasi `2026_11_11_000002_add_antrean_and_slot_waktu_to_pesanan_table.php`, view `tenant/antrean.blade.php` (611 baris), `admin/monitoring-progres.blade.php` (413 baris, read-only), rute progresi status (yang memanggil `OrderStatusUpdated`) + panggilan antrean + walk-in di `routes/tenant.php:83-212`, serta `QueueAndStatusProgressionTest` (6 tests).
4. **Perbaikan Fitur Ceklis Keranjang & Burger/Drawer UI (17–18 Nov 2026, PR #18 `c726924`, PR #23 `7df7345`, PR #24 `fc7c953`):**
   - `61827da`, `a262f8f`, `3017d75`: memperbaiki logika pemilihan item (*checklist*) di `customer/cart.blade.php` & `CheckoutController.php` beserta penyesuaian Pint/PHPStan.
   - `6f1f3de` & `0da99e0`: menampilkan burger menu desktop, merapikan drawer navigasi, dropdown profil, serta layout awal `order-history.blade.php`.

**Log Kronologis Lengkap Seluruh 23 Commit Arya Dwi Mahendra (`Miinggg67`, 22 `main` + 1 Open PR #8):**

| No | Hash | Waktu (`+07:00`) | Branch / PR | Pesan Commit Resmi (`git log --all`) |
|---|---|---|---|---|
| 1 | `fc7c953` | `2026-11-18 18:36:03` | `main` (PR #24) | `Merge pull request #24 from heliosparta18/feat/bugfix-burger-layout-lagi` |
| 2 | `0da99e0` | `2026-11-18 18:29:34` | PR #24 | `fix(ui): tampilkan burger menu di desktop, rapikan drawer dan dropdown profil` |
| 3 | `7df7345` | `2026-11-18 17:59:02` | `main` (PR #23) | `Merge pull request #23 from heliosparta18/feat/bugfix-burger-layout` |
| 4 | `6f1f3de` | `2026-11-18 17:54:40` | PR #23 | `bug fix layout sama burger` |
| 5 | `c726924` | `2026-11-17 19:13:16` | `main` (PR #18) | `Merge pull request #18 from heliosparta18/feat/ceklis-bugfix` |
| 6 | `3017d75` | `2026-11-17 19:08:33` | PR #18 | `fix(types): remove redundant null coalesce on non-nullable subtotal offset in CheckoutController` |
| 7 | `a262f8f` | `2026-11-17 19:01:43` | PR #18 | `style: fix pint code formatting in CheckoutController` |
| 8 | `61827da` | `2026-11-17 18:53:59` | PR #18 | `bug fix fitur ceklis` |
| 9 | `e44272f` | `2026-11-11 21:53:50` | `main` (PR #11/#12) | `Merge pull request #12 from heliosparta18/feat/antrean` |
| 10 | `aa0bf9b` | `2026-11-11 21:43:08` | PR #12 | `fixxxxxxxxxxxxxx` |
| 11 | `fb45319` | `2026-11-11 21:33:27` | PR #12 | `fix ke 67 kali` |
| 12 | `246930d` | `2026-11-11 21:17:47` | PR #12 | `fix ui` |
| 13 | `deefce5` | `2026-11-11 20:31:31` | PR #11/#12 | `feat(tenant): tombol progresi status pesanan apoteker dan live monitoring read-only super admin` |
| 14 | `f62576e` | `2026-11-09 14:07:09` | PR #10 (`b0a8bd4`) | `feat: redesign dashboard multi-peran sesuai tema hijau dan standar SRS apotek` |
| 15 | `b599624` | `2026-11-08 19:26:21` | `origin/feat/arya-catalog-fefo` (PR #8 Open) | `selesaikan antarmuka dan logika modul operasional apotek arya m05-m12` *(isi diintegrasikan via `3080dc0`)* |
| 16 | `b58c00b` | `2026-11-06 23:39:16` | PR #6 | `style: fix code style issues via pint` |
| 17 | `847a226` | `2026-11-06 23:08:46` | PR #6 | `style: fix code style issues via pint` |
| 18 | `dd44777` | `2026-11-06 21:42:40` | PR #6 | `aryak` |
| 19 | `288a792` | `2026-11-02 11:20:09` | PR #6 | `bikin algoritma rotasi stok fefo dan alert ed` |
| 20 | `083ded7` | `2026-11-02 11:19:44` | PR #6 | `buat antrean telaah resep dan cek sip dokter` |
| 21 | `11a3d3b` | `2026-11-02 11:19:21` | PR #6 | `implementasi katalog obat livewire dan filter` |
| 22 | `7a0f9fe` | `2026-11-02 10:59:29` | PR #7 | `bikin sesi pelanggan opaque token cookie` |
| 23 | `4fa5ec1` | `2026-11-02 10:59:02` | PR #7 | `buat manajemen staf cabang dan apj 5nf` |

### 9.5 `dependabot[bot]` & Analisis Status 3 Pull Request Terbuka (#1, #2, #8)

Hingga commit `c39e087`, masih terdapat 3 PR berstatus `OPEN` di GitHub (`gh pr list`):
1. **PR #1 (`dependabot/github_actions/github-actions-0982b03464`, commit `2740d8d` / remote `852bb61`):** Pembaruan rutin versi pin SHA action pada `.github/workflows/tests.yml` oleh `dependabot[bot]`. Aman untuk di-review/merge jika CI lulus.
2. **PR #2 (`feature/firebase-auth-login` oleh `callmezext`, commits `6d7e4ae`, `8f4cb34`, 30 Okt 2026):** Prototipe awal Diprella double-slider login + Firebase Auth (`resources/css/login-slider.css`, `resources/js/firebase-login.js`, `AGENTS.md`). **Sudah usang (*superseded*)** karena implementasi final Firebase Auth & Email OTP telah ter-merge langsung ke `main` pada `65da189` dan `e1c9cea` (`resources/js/firebase-auth.js`). Sebaiknya ditutup (*close without merge*) agar tidak menimbulkan konflik `AGENTS.md`.
3. **PR #8 (`feat/arya-catalog-fefo` oleh `Miinggg67`, commit `b599624`, 8 Nov 2026):** Mengandung 22 file M05–M12 Arya yang isinya **sudah terintegrasi (*cherry-picked*)** ke `main` pada `3080dc0` dan commit-commit berikutnya (`b0a8bd4`, `e44272f`). Sebaiknya ditutup setelah dikonfirmasi tidak ada diff tersisa yang belum masuk ke `main`.

---

## Bab 10. Inventaris Lengkap Kodebase Terkini (Baseline Commit `c39e087`)

### 10.1 Inventaris 20 Model Eloquent (`app/Models/*`) + Scope & Trait

Seluruh **20 model Eloquent** di `app/Models/` (ditambah `Scopes/TenantScope.php` 44 baris dan `Traits/BelongsToTenant.php` 73 baris) telah diverifikasi langsung:

| No | File Model | Baris | Tabel DB | Primary Key | Trait `BelongsToTenant` | Peran Domain Utama |
|---|---|---|---|---|---|---|
| 1 | `AdminClient.php` | 51 | `admin_client` | `id_admin` (UUID) | Tidak | Pemetaan pengelola / staf admin per cabang apotek. |
| 2 | `Client.php` | 123 | `client` | `id_client` (UUID) | Tidak | Master entitas cabang apotek (`slug`, `radius_km`, `jam_buka`, `aktif`). |
| 3 | `ItemPesanan.php` | 72 | `item_pesanan` | `id_item` (UUID) | Tidak | Snapshot item obat & harga per transaksi (**ADR-0008**). |
| 4 | `KategoriObat.php` | 71 | `kategori_obat` | `id_kategori` (UUID) | Tidak | Kategori regulasi obat (`wajib_resep`, `slug`, `icon_url`, `urutan`). |
| 5 | `Konsultasi.php` | 112 | `konsultasi` | `id_konsultasi` (UUID) | Tidak | Sesi konsultasi pasien–apoteker (fitur Tanya Apoteker). |
| 6 | `LogAudit.php` | 51 | `log_audit` | `id_audit` (UUID) | Tidak | Catatan audit trail *append-only* aksi kritis (`TELAAH_RESEP`, dll.). |
| 7 | `Obat.php` | 197 | `obat` | `id_obat` (UUID) | Tidak | Master sediaan obat, FEFO (`expired`, `batch_number`), helper multi-cabang. |
| 8 | `Pembayaran.php` | 60 | `pembayaran` | `id_pembayaran` (UUID) | Tidak | Rekam pelunasan transaksi (`qris`, `tunai`, `status`, `verifikator`). |
| 9 | `Pengguna.php` | 111 | `pengguna` | `id_pengguna` (UUID) | Tidak | Entitas domain pengguna (`super_admin`, `apoteker`, `admin`, `pelanggan`). |
| 10 | `PeranClientPengguna.php` | 60 | `peran_client_pengguna` | `id` (UUID) | Tidak | Penugasan peran lokal staf/pelanggan per cabang apotek (5NF). |
| 11 | `PermintaanRetur.php` | 52 | `permintaan_retur` | `id_retur` (UUID) | Tidak | Pengajuan retur obat (`jumlah_diajukan`, `jumlah_disetujui`, `status`). |
| 12 | `Pesanan.php` | 227 | `pesanan` | `id_pesanan` (UUID) | **Ya** | Transaksi pesanan, accessor ongkir/jarak/koordinat/Maps, antrean virtual. |
| 13 | `PesanKonsultasi.php` | 79 | `pesan_konsultasi` | `id_pesan` (UUID) | Tidak | Pesan teks & lampiran gambar di dalam thread `Konsultasi`. |
| 14 | `PetaPengawasStaf.php` | 68 | `peta_pengawas_staf` | `id` (UUID) | Tidak | Hierarki pengawasan 5NF antara APJ dan staf cabang (`uq_pengawas_staf_client`). |
| 15 | `Resep.php` | 82 | `resep` | `id_resep` (UUID) | Tidak | Resep dokter digital (`url_gambar`, `sip_dokter`, `sip_valid`, `dosis_valid`). |
| 16 | `RingkasanPeriodePenjualan.php` | 87 | `ringkasan_periode_penjualan` | `id_ringkasan` (UUID) | **Ya** | Snapshot omzet terkunci per cabang & periode (**ADR-0008**, `scopeTerkunci`). |
| 17 | `StokClient.php` | 109 | `stok_client` | `id` (UUID) | **Ya** | Stok fisik obat per cabang (`UNIQUE(id_obat, id_client)`), properti ED dinamis. |
| 18 | `SubkategoriObat.php` | 59 | `subkategori_obat` | `id_subkategori` (UUID) | Tidak | Subkategori katalog GoApotik (`id_kategori`, `slug`, `urutan`). |
| 19 | `SuperAdmin.php` | 42 | `super_admin` | `id_super_admin` (UUID) | Tidak | Profil administrator eksekutif pusat jaringan apotek. |
| 20 | `User.php` | 494 | `users` | `id` (BigInt) | Tidak | Model autentikasi Fortify/2FA/Passkeys, `hasRole()`, `homeRoute()`, `profileActions()`, accessor `telepon`/`no_telp`/`alamat`. |

**Ringkasan Relasi & Scoping Kunci antar Model Eloquent:**
- `Client` memiliki relasi ke `AdminClient`, `PeranClientPengguna`, `StokClient`, `Pesanan`, dan `RingkasanPeriodePenjualan` (seluruh model bertanda `BelongsToTenant` terikat `TenantScope` pada `id_client`).
- `Pengguna` berelasi dengan `SuperAdmin`, `AdminClient`, `PeranClientPengguna`, `PetaPengawasStaf` (sebagai `staf` maupun `pengawas`), `Pesanan`, `Konsultasi`, dan `LogAudit`.
- `Pesanan` berelasi `belongsTo` ke `Pengguna` & `Client`, `hasOne` ke `Resep` & `Pembayaran`, serta `hasMany` ke `ItemPesanan` (alias `itemPesanan()`) & `PermintaanRetur`.
- `Obat` berelasi `belongsTo` ke `KategoriObat` & `SubkategoriObat`, serta `hasMany` ke `StokClient` (`stokClient()` terfilter `TenantScope` dan `allStokClient()` lintas cabang).
- `Konsultasi` berelasi `belongsTo` ke `Client` & `Pengguna`, dan `hasMany` ke `PesanKonsultasi`.

### 10.2 Inventaris 20 File Migrasi Basis Data (`database/migrations/*`)

| No | Nama File Migrasi | Baris | Operasi Skema & Kolom / Constraint Utama |
|---|---|---|---|
| 1 | `0001_01_01_000000_create_users_table.php` | 49 | Membuat tabel `users`, `password_reset_tokens`, dan `sessions`. |
| 2 | `0001_01_01_000001_create_cache_table.php` | 35 | Membuat tabel `cache` dan `cache_locks`. |
| 3 | `0001_01_01_000002_create_jobs_table.php` | 59 | Membuat tabel `jobs`, `job_batches`, dan `failed_jobs`. |
| 4 | `2024_01_01_000000_create_passkeys_table.php` | 34 | Membuat tabel `passkeys` untuk autentikasi tanpa kata sandi WebAuthn. |
| 5 | `2025_08_14_170933_add_two_factor_columns_to_users_table.php` | 34 | Menambahkan `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at` ke `users`. |
| 6 | `2026_08_16_000001_add_role_and_status_to_users_table.php` | 29 | Menambahkan kolom `role` dan `status` ke tabel `users`. |
| 7 | `2026_09_21_000101_create_apotek_clients_and_users_tables.php` | 100 | Membuat `client`, `pengguna`, `super_admin`, `admin_client`, `peran_client_pengguna`, `peta_pengawas_staf` (`uq_pengawas_staf_client`). |
| 8 | `2026_09_21_000102_create_apotek_drug_catalog_tables.php` | 62 | Membuat `kategori_obat`, `obat` (indeks `expired`), `stok_client` (`uq_stok_obat_client`). |
| 9 | `2026_09_21_000103_create_apotek_order_and_prescription_tables.php` | 68 | Membuat `pesanan`, `resep`, `item_pesanan` (snapshot `nama_obat`, `harga_satuan`, `subtotal`). |
| 10 | `2026_09_21_000104_create_apotek_payment_and_return_tables.php` | 47 | Membuat `pembayaran` dan `permintaan_retur`. |
| 11 | `2026_09_21_000105_create_apotek_reporting_and_audit_tables.php` | 52 | Membuat `ringkasan_periode_penjualan` (`uq_ringkasan_client_periode`) dan `log_audit`. |
| 12 | `2026_09_22_000101_add_slug_to_client_table.php` | 26 | Menambahkan kolom `slug` terindeks pada tabel `client`. |
| 13 | `2026_11_09_000001_add_token_sesi_to_pesanan_table.php` | 27 | Menambahkan kolom `token_sesi` (64 char, indexed) pada tabel `pesanan` untuk otorisasi tamu & Reverb. |
| 14 | `2026_11_11_000002_add_antrean_and_slot_waktu_to_pesanan_table.php` | 48 | Menambahkan `slot_waktu`, `nomor_antrean`, dan `catatan_pesanan` pada tabel `pesanan`. |
| 15 | `2026_11_13_000001_add_catalog_attributes_to_obat_and_kategori_table.php` | 85 | Menambah `slug`/`icon_url`/`urutan` di `kategori_obat`, membuat `subkategori_obat`, menambah `id_subkategori`/`slug`/`badge_status`/`gambar_url`/`nomor_ijin_edar`/`indikasi`/`deskripsi`/`dosis`/`komposisi`/`aturan_pakai` di `obat`. |
| 16 | `2026_11_13_000002_add_catatan_to_resep_table.php` | 29 | Menambahkan kolom `catatan` pada tabel `resep`. |
| 17 | `2026_11_15_000001_add_operasional_fields_to_client_table.php` | 30 | Menambahkan `radius_km` (default 5) dan `jam_buka` (default `'07:00 - 22:00 WIB'`) ke `client`. |
| 18 | `2026_11_16_000001_sync_goapotik_full_catalog_products.php` | 31 | Migrasi sinkronisasi katalog lengkap GoApotik saat deployment (lewati di `testing`). |
| 19 | `2026_11_18_000001_add_phone_and_address_to_users_table.php` | 42 | **Oleh Candi (PR #25):** Menambahkan kolom `phone` (50 char) dan `address` (text) ke `users`. |
| 20 | `2026_11_21_000001_create_konsultasi_and_pesan_tables.php` | 47 | Membuat tabel `konsultasi` dan `pesan_konsultasi` untuk fitur Tanya Apoteker. |

**Suplemen Kamus Data Mutakhir (Kolom & Tabel Tambahan Pasca-PDM Awal `docs/data-dictionary.md`):**

| Tabel | Kolom / Tabel Baru | Tipe Data & Constraint | Migrasi Sumber & Tujuan Teknis |
|---|---|---|---|
| `client` | `slug`, `radius_km`, `jam_buka` | `string(150)` nullable indexed; `unsignedSmallInteger` (5); `string(60)` (`'07:00 - 22:00 WIB'`) | `2026_09_22_000101` & `2026_11_15_000001` — Resolusi URL slug cabang & pengaturan operasional gerai. |
| `users` | `phone`, `address` | `string(50)` nullable; `text` nullable | `2026_11_18_000001` (**Candi PR #25**) — Nomor telepon/WA & alamat pengiriman pengguna. |
| `pesanan` | `token_sesi`, `slot_waktu`, `nomor_antrean`, `catatan_pesanan` | `string(64)` nullable indexed; `string(100)`; `string(50)`; `text` | `2026_11_09_000001` & `2026_11_11_000002` — Otorisasi tamu Reverb, antrean virtual `A-XXX`, dan metadata `[ONGKIR]/[JARAK]/[KOORDINAT]/[MAPS]/[ALAMAT]`. |
| `resep` | `catatan` | `text` nullable | `2026_11_13_000002` — Catatan pasien saat mengunggah resep digital. |
| `subkategori_obat` | `id_subkategori`, `id_kategori`, `nama_subkategori`, `slug`, `icon_url`, `urutan`, `dibuat_pada` | PK UUID, FK `kategori_obat` cascade, `slug` unique (120 char) | `2026_11_13_000001` — Hierarki subkategori katalog GoApotik. |
| `obat` | `id_subkategori`, `slug`, `satuan_kemasan`, `badge_status`, `gambar_url`, `nomor_ijin_edar`, `indikasi`, `deskripsi`, `dosis`, `komposisi`, `aturan_pakai` | FK nullable `subkategori_obat`, `slug` unique (200 char), indeks `badge_status` | `2026_11_13_000001` — Atribut lengkap informasi produk farmasi & aset GoApotik. |
| `konsultasi` | `id_konsultasi`, `id_client`, `id_pengguna`, `token_sesi`, `nama_pasien`, `status`, `pesan_terakhir`, `pesan_terakhir_pada`, `dibuat_pada`, `diperbarui_pada` | PK UUID, FK `client` cascade, FK `pengguna` nullOnDelete, index `(id_client, status)` | `2026_11_21_000001` — Sesi konsultasi Tanya Apoteker terisolasi per cabang & sesi pasien. |
| `pesan_konsultasi` | `id_pesan`, `id_konsultasi`, `id_pengguna`, `pengirim_tipe`, `pesan`, `lampiran_url`, `lampiran_tipe`, `dibaca_pada`, `dibuat_pada` | PK UUID, FK `konsultasi` cascade, index `(id_konsultasi, dibuat_pada)` | `2026_11_21_000001` — Isi pesan teks & lampiran gambar konsultasi apoteker. |

### 10.3 Inventaris 6 Modul Domain (`app/Modules/*`), Controller, Service, Action, Event, & Support

Tabel berikut merinci seluruh kelas aktif di dalam `app/Modules/` dan `app/Support/` beserta daftar *public method* yang terverifikasi langsung di kode sumber:

| Modul | ServiceProvider | Kelas Implementasi & Jumlah Baris | Metode Publik Terverifikasi |
|---|---|---|---|
| **`Admin`** | `AdminServiceProvider.php` (27) | `Http/Controllers/StaffController.php` (205) | `__construct`, `index`, `createAccount`, `toggleStatus`, `assignRole`, `removeRole`, `assignSupervisor`, `removeSupervisor` |
| **`Admin`** | — | `Services/StaffManagementService.php` (81) | `tetapkanPengawas`, `beriPeranCabang`, `getStaffByClient`, `getPengawasByClient`, `hapusPeranCabang`, `hapusPengawas` |
| **`Catalog`** | `CatalogServiceProvider.php` (27) | `Livewire/PublicCatalog.php` (224) | `mount`, `updateSearchFromDashboard`, `updateCategoryFromDashboard`, `updatingSearch`, `updatingSediaan`, `updatingKategori`, `updatingSubKategori`, `selectCategory`, `nextCategory`, `prevCategory`, `selectSubCategory`, `filterSubKategori`, `filterSediaan`, `resetFilters`, `switchBranch`, `render` |
| **`Catalog`** | — | `Services/CatalogService.php` (458) | `ensureCatalogSynchronized`, `clearMetadataCache`, `getCatalog`, `getDaftarKategori`, `getDaftarSubkategori`, `getDaftarSediaan` |
| **`Catalog`** | — | `Services/CustomerSessionService.php` (61) | `resolveSession`, `getActiveBranch`, `setActiveBranch`, `getNearestBranch` |
| **`Catalog`** | — | `Services/StockFefoService.php` (95) | `getBatchesByFefo`, `getExpirySummary`, `getNearExpiryBatches` |
| **`Catalog`** | — | `Support/GoApotikCatalogAssets.php` (285) | `categoryIcon`, `subcategoryIcon`, `drugClassBadge`, `uiSvg` |
| **`Ordering`** | `OrderingServiceProvider.php` (27) | `Actions/CreateOrderAction.php` (114) | `execute` (`lockForUpdate` stok, generate `#ORD-...`, `item_pesanan` snapshot ADR-0008) |
| **`Ordering`** | — | `Actions/CancelOrderAction.php` (43) | `execute` (batalkan pesanan belum lunas & pulihkan `jumlah_stok`) |
| **`Ordering`** | — | `Events/OrderStatusUpdated.php` (47) | `__construct`, `broadcastOn`, `broadcastAs`, `broadcastWith` |
| **`Ordering`** | — | `Events/IncomingOrderPlaced.php` (49) | `__construct`, `broadcastOn`, `broadcastWith` |
| **`Ordering`** | — | `Http/Controllers/CartController.php` (116) | `index`, `add`, `update`, `remove` |
| **`Ordering`** | — | `Http/Controllers/CheckoutController.php` (260) | `index`, `process` (cache lock idempotensi, hitung jarak Haversine & ongkir) |
| **`Ordering`** | — | `Http/Controllers/OrderTrackingController.php` (372) | `payment`, `checkStatus`, `history`, `cancel`, `track` |
| **`Ordering`** | — | `Services/CartService.php` (133) | `getCartKey`, `addItem`, `updateItem`, `removeItem`, `getItems`, `getTotal`, `clear`, `getCount` |
| **`Payments`** | `PaymentsServiceProvider.php` (32) | `Contracts/PaymentGatewayInterface.php` (15) | `generateQris`, `verifyWebhookSignature` |
| **`Payments`** | — | `Http/Controllers/PaymentWebhookController.php` (101) | `handle` (Midtrans/HMAC signature verify, idempotency, `OrderStatusUpdated::dispatch`) |
| **`Payments`** | — | `Services/FakeQrisGateway.php` (320) | `generateQris`, `checkTransactionStatus`, `verifyMidtransSignature`, `verifyWebhookSignature` |
| **`Payments`** | — | `Services/QrisEmvcoBuilder.php` (45) | `build`, `crc16Ccitt` |
| **`Prescription`** | `PrescriptionServiceProvider.php` (25) | `Actions/VerifyPrescriptionAction.php` (66) | `execute` (validasi SIP `XXX/SIP/YYYY`, catat `LogAudit`) |
| **`Reporting`** | `ReportingServiceProvider.php` (27) | `Actions/CloseSalesPeriodAction.php` (65) | `execute` (kunci snapshot ADR-0008 `uq_ringkasan_client_periode`) |
| **`Reporting`** | — | `Services/SalesReportExportService.php` (74) | `exportCsv`, `sanitizeForCsv` |
| **`Reporting`** | — | `Services/SalesReportService.php` (59) | `getSummary` (agregasi dari `item_pesanan`) |
| **Shared `App\Support`** | — | `Routing/PortalRoutes.php` (62), `Tenant/TenantContext.php` (105), `TenantManagerInterface.php` (62), `Events/*`, `Exceptions/*` | `PortalRoutes::{customer,tenant,kasir,admin}`, `TenantContext::{set,get,check,has,id,name,slug,clear,runInContext}` |

### 10.4 Inventaris Seluruh Route Files (`routes/*.php`), Middleware, FormRequest, Livewire, & Frontend Assets

**9 Berkas Rute (`routes/*.php`) dan Matriks Endpoint Utama per Portal:**

| File Rute & Prefix | Method & URI | Route Name | Handler / Controller & Catatan Teknis |
|---|---|---|---|
| `routes/web.php` (`/`) | `GET /` | `home` | Render `welcome.blade.php` bagi tamu atau `?welcome=1`; redirect ke `$user->homeRoute()` jika login (`baris 16-31`). |
| `routes/web.php` | `GET /katalog`, `GET /dashboard` | `katalog`, `dashboard` | Redirect ke cabang aktif atau render storefront `dashboard.blade.php` (`baris 33-48`). |
| `routes/web.php` | `GET /{apoteker,antrean,verifikasi-resep,stok-fefo,kasir,admin}` | *(tanpa nama)* | Shortcut redirect menuju portal role sesuai `$user->getClientSlug()` (`baris 56-69`). |
| `routes/web.php` | `POST /cabang/switch` | `branch.switch` | Set session `active_branch` & `active_client`; mendukung `redirect_to` Candi (`baris 71-83`). |
| `routes/web.php` | `GET /api/search-obat` | `api.search.obat` | Live search multi-token (min 2 karakter, maks 8 hasil terurut ketersediaan cabang, `baris 85-140`). |
| `routes/web.php` (`tenant`) | `GET /obat/{id}`, `/keranjang*`, `/checkout*`, `/pembayaran/{id}`, `/pesanan/{id}/*`, `/lacak-pesanan/{id}`, `/riwayat-pesanan` | `obat.show`, `keranjang*`, `checkout.*`, `pembayaran.show`, `pesanan.*` | Short routes pelanggan di bawah middleware `tenant` (`ResolveTenantContext::syncRouteClientParameter`, `baris 142-185`). |
| `routes/web.php` | `POST /auth/send-otp`, `/auth/{verify,register}-otp`, `/auth/firebase-*`, `GET/POST /auth/complete-profile` | `auth.otp.*`, `auth.firebase.*`, `auth.complete-profile*` | Rute OTP ter-throttle (`throttle:5,1` & `10,1`) serta sinkronisasi sesi Firebase & profil awal (`baris 187-203`). |
| `routes/customer.php` (`klien/{client}`) | `GET /`, `/katalog`, `/obat/{id}` | `customer.home`, `customer.catalog`, `customer.obat.detail` | Katalog home Blade, komponen Livewire `PublicCatalog`, & detail obat multi-cabang (`baris 44-114`). |
| `routes/customer.php` | `POST /resep/unggah` | `customer.resep.upload` | Validasi `UnggahResepRequest` (*magic bytes*), simpan `public/resep`, buat `Pesanan` + `Resep` (`baris 116-233`). |
| `routes/customer.php` | `GET/POST /cart*`, `/checkout*`, `/order/{id}/*`, `/riwayat-pesanan` | `customer.cart*`, `customer.checkout*`, `customer.order.*` | Delegasi penuh ke `CartController`, `CheckoutController`, dan `OrderTrackingController` (`baris 236-250`). |
| `routes/tenant.php` (`cabang/{client}`) | `GET /dashboard`, `/antrean`, `POST /pesanan/{id}/progres-status`, `/antrean/{update,tambah-walkin}` | `tenant.dashboard`, `tenant.antrean.*`, `tenant.pesanan.progres-status` | Dashboard apoteker, antrean loket + panggilan +1, walk-in, dan siaran `OrderStatusUpdated` (`baris 31-212`). |
| `routes/tenant.php` | `GET /resep`, `POST /resep/{id}/verifikasi`, `GET /pesanan/{id}` | `tenant.resep.*`, `tenant.pesanan.detail` | Antrean telaah resep, `VerifikasiResepRequest` + `LogAudit` + `OrderStatusUpdated`, dan detail pesanan via `Gate::authorize` (`baris 214-456, 502-515`). |
| `routes/tenant.php` | `GET /stok-fefo`, `POST /stok/{tambah,{id}/penyesuaian}`, `GET /pasien`, `/bantuan` | `tenant.stok.*`, `tenant.pasien.index`, `tenant.bantuan.index` | Rotasi stok FEFO H-30/H-7, penyesuaian/karantina stok, rekam pasien cabang, & SOP Permenkes (`baris 234-342, 458-500`). |
| `routes/tenant.php` | `GET /laporan`, `POST /laporan/tutup-periode` | `tenant.laporan.index`, `tenant.laporan.tutup-periode` | **Portal M13 Candi:** laporan `SalesReportService`, CSV `?export=csv`, & kunci periode ADR-0008 (`baris 517-560`). |
| `routes/admin.php` (`admin`) | `GET /dashboard`, `POST /tutup-periode`, `GET /ekspor-laporan` | `admin.dashboard`, `admin.periode.tutup`, `admin.laporan.ekspor` | Dashboard eksekutif lintas cabang, tutup periode JSON konsolidasi, & ekspor CSV (`baris 29-169`). |
| `routes/admin.php` | `GET/POST/PATCH/DELETE /cabang/{client}/staf*`, `/cabang*`, `/obat*` | `admin.cabang.staf*`, `admin.cabang.*`, `admin.obat.*` | CRUD lengkap staf + pengawas APJ 5NF (`StaffController`), cabang apotek, & master obat multi-cabang (`baris 176-486`). |
| `routes/admin.php` | `GET /kasir-ledger`, `/monitoring-progres`, `/apoteker/{client?}` | `admin.kasir.ledger`, `admin.progres.index`, `admin.apoteker.index` | Audit read-only kasir ledger, monitoring progres live, & audit operasional apoteker (`baris 189-234, 493-616`). |
| `routes/kasir.php` (`kasir`) | `GET /dashboard`, `POST /transaksi-walkin`, `/pesanan/{id}/{lunasi,batalkan}`, `GET /ledger`, `/struk/{id}`, `/pos-obat-search` | `kasir.dashboard`, `kasir.walkin.store`, `kasir.pesanan.*`, `kasir.ledger`, `kasir.struk`, `kasir.pos.search` | Portal POS kasir cabang; walk-in & pelunasan otomatis memancarkan `OrderStatusUpdated` (`baris 57-401`). |
| `routes/api.php` | `POST /api/webhooks/qris` | `webhooks.qris` | Webhook settlement QRIS (HMAC-SHA256 / Midtrans signature, dikecualikan dari CSRF di `bootstrap/app.php:41`). |
| `routes/channels.php` | `App.Models.User.{id}`, `order.{id_pesanan}`, `tenant.{id_client}` | *(broadcast auth)* | Otorisasi saluran privat Reverb WebSocket untuk user, pemilik pesanan/tamu, & operator cabang (`baris 8-53`). |
| `routes/settings.php` & `console.php` | `GET /settings/{profile,security}`, `.well-known/passkey-endpoints` | `profile.edit`, `security.edit`, `well-known.passkeys` | Komponen Volt profil (`phone`/`address`), keamanan (2FA/Passkeys), & metadata WebAuthn (`21` baris + `8` baris). |

**Lapisan HTTP, Livewire Non-Modul, Konfigurasi, & Aset Frontend:**
- **Controllers Auth (`app/Http/Controllers/Auth/`):** `EmailOtpAuthController.php` (212 baris), `FirebaseAuthSessionController.php` (181 baris).
- **Middleware (`app/Http/Middleware/`):** `EnsureUserHasRole.php` (48 baris), `ResolveTenantContext.php` (163 baris), `SecurityHeadersMiddleware.php` (56 baris — global).
- **FormRequests (`app/Http/Requests/`):** `Customer/UnggahResepRequest.php` (70 baris), `Tenant/VerifikasiResepRequest.php` (59 baris).
- **Livewire Components (`app/Livewire/`):** `Actions/Logout.php`, `Customer/TanyaApotekerModal.php` (397 baris), `Tenant/ApotekerChatPortal.php` (278 baris).
- **Policies & Commands:** `ClientPolicy.php` (34), `PesananPolicy.php` (30), `AuditTenantIsolationCommand.php` (85).
- **13 Berkas Konfigurasi (`config/*.php`):** `app.php`, `auth.php`, `broadcasting.php`, `cache.php`, `database.php`, `filesystems.php`, `fortify.php`, `logging.php`, `mail.php`, `queue.php`, `reverb.php`, `services.php`, `session.php`.
- **JavaScript (`resources/js/`):** `app.js` (8), `echo.js` (18), `firebase-auth.js` (354), `passkeys.js` (4).

**Inventaris Lengkap View Blade & Volt per Portal (`resources/views/`):**

| Kategori / Portal | Daftar Berkas & Jumlah Baris Terverifikasi | Peran Antarmuka Utama |
|---|---|---|
| **Root Publik & Dashboard** | `welcome.blade.php` (1.309), `dashboard.blade.php` (1.521) | Landing page slider 3-in-1 (Candi) & storefront utama GoApotik-style. |
| **Portal Pelanggan (`customer/`)** | `home.blade.php` (577), `catalog.blade.php` (205), `obat-detail.blade.php` (357), `cart.blade.php` (418), `checkout.blade.php` (530), `payment.blade.php` (494), `order-track.blade.php` (364), `order-history.blade.php` (315) | Katalog publik, drag-drop upload resep, detail obat multi-cabang, keranjang Redis, checkout Maps/ongkir, QRIS EMVCo, pelacakan Reverb (Candi), riwayat responsif (Candi). |
| **Partials Katalog (`customer/partials/`)** | `catalog-category-tabs.blade.php` (202), `catalog-empty-state.blade.php` (24), `catalog-pagination.blade.php` (153), `catalog-product-card.blade.php` (176), `catalog-skeleton.blade.php` (18), `catalog-subcategory-sidebar.blade.php` (202), `gps-location-prompt.blade.php` (1) | Tab kategori sliding, sidebar subkategori, kartu produk + lock stok habis (Candi), paginasi, skeleton loading. |
| **Portal Apoteker (`tenant/`)** | `dashboard.blade.php` (525), `antrean.blade.php` (611), `stok-fefo.blade.php` (374), `verifikasi-resep.blade.php` (214), `pasien.blade.php` (192), `bantuan.blade.php` (115) | Dashboard apoteker cabang, antrean loket + broadcast Reverb, stok FEFO H-30/H-7, telaah SIP resep, rekam pasien, SOP Permenkes 73/2016. |
| **Portal Kasir (`kasir/`)** | `dashboard.blade.php` (364), `ledger.blade.php` (166), `struk.blade.php` (77) | POS walk-in tunai/QRIS, pelunasan pesanan, buku ledger cabang, cetak struk. |
| **Portal Super Admin (`admin/`)** | `dashboard.blade.php` (365), `cek-apoteker.blade.php` (243), `kasir-ledger.blade.php` (211), `laporan-penjualan.blade.php` (178), `monitoring-progres.blade.php` (413), `obat-crud.blade.php` (488), `pengaturan-cabang.blade.php` (150), `staff-management.blade.php` (420), `pdf/laporan-penjualan-template.blade.php` (73) | Dashboard eksekutif, audit operasional apoteker, ledger konsolidasi, analitik & tutup periode (Candi), live monitoring, CRUD obat/cabang/staf, template PDF (Candi). |
| **Konsultasi Livewire (`livewire/`)** | `customer/tanya-apoteker-modal.blade.php` (407), `tenant/apoteker-chat-portal.blade.php` (425) | Modal chat Tanya Apoteker sisi pasien & portal chat apoteker per cabang. |
| **Autentikasi & Pengaturan (`pages/`)** | `auth/{complete-profile (253), confirm-password (36), forgot-password (31), login (483), register (558), reset-password (54), two-factor-challenge (101), verify-email (29)}`, `settings/{layout (60), ⚡appearance (22), ⚡delete-user-form (20), ⚡delete-user-modal (50), ⚡profile (155), ⚡security (379), ⚡two-factor-setup-modal (310), two-factor/⚡recovery-codes (136)}` | Autentikasi Fortify/Firebase/OTP, profil `phone`/`address` (Candi), 2FA & Passkeys (`⚡security` Candi). |
| **Layouts, Components, Partials, Flux** | `components/layouts/{admin (166), customer (634), internal-portal (364), kasir (33), tenant (57)}`, `layouts/{admin (3), app (15), app/header (57), app/sidebar (93), auth (3), auth/card (33), auth/simple (29), auth/split (50), customer (630), kasir (3), tenant (3)}`, `components/{passkey-registration (118), passkey-verify (76), welcome-splash (58), ...}`, `partials/{head (64), mobile-nav-drawer (269), omni-theme-script (35), profile-role-icon (45), profile-role-menu (46), settings-heading (50), user-avatar (16)}`, `flux/*` (6 berkas) | Kerangka layout 4 portal, komponen keamanan Passkey, drawer navigasi mobile, dan tema light-medical / dark portal internal. |

### 10.5 Inventaris 19 Factory, 4 Seeder, Dataset GoApotik (42 & 2.424 Produk), & Skrip Otomasi

- **19 Factory (`database/factories/`):**

| # | Berkas Factory | Model Eloquent Target | Kegunaan Pengujian / Seeding |
|---|---|---|---|
| 1 | `AdminClientFactory.php` | `AdminClient` | Penugasan manajer/staf admin ke cabang. |
| 2 | `ClientFactory.php` | `Client` | Pembuatan tenant cabang apotek uji (`slug`, `aktif`). |
| 3 | `ItemPesananFactory.php` | `ItemPesanan` | Snapshot item pesanan (`nama_obat`, `harga_satuan`, `subtotal`). |
| 4 | `KategoriObatFactory.php` | `KategoriObat` | Kategori obat bebas, bebas terbatas, & wajib resep. |
| 5 | `KonsultasiFactory.php` | `Konsultasi` | Sesi konsultasi pasien–apoteker (`TanyaApotekerTest`). |
| 6 | `LogAuditFactory.php` | `LogAudit` | Rekam jejak audit aksi sistem. |
| 7 | `ObatFactory.php` | `Obat` | Sediaan obat dengan `expired` & `batch_number` FEFO. |
| 8 | `PembayaranFactory.php` | `Pembayaran` | Data transaksi pembayaran QRIS/tunai. |
| 9 | `PenggunaFactory.php` | `Pengguna` | Akun domain UUID multi-peran. |
| 10 | `PeranClientPenggunaFactory.php` | `PeranClientPengguna` | Penugasan peran lokal staf/pelanggan per cabang. |
| 11 | `PermintaanReturFactory.php` | `PermintaanRetur` | Pengajuan retur obat. |
| 12 | `PesananFactory.php` | `Pesanan` | Transaksi pesanan (`ReverbOrderStatusBroadcastTest`, `SalesReportAndPeriodLockTest`). |
| 13 | `PesanKonsultasiFactory.php` | `PesanKonsultasi` | Pesan chat konsultasi pasien/apoteker. |
| 14 | `PetaPengawasStafFactory.php` | `PetaPengawasStaf` | Relasi pengawasan 5NF APJ dan staf cabang. |
| 15 | `ResepFactory.php` | `Resep` | Resep dokter digital untuk pengujian telaah. |
| 16 | `RingkasanPeriodePenjualanFactory.php` | `RingkasanPeriodePenjualan` | Snapshot periode penjualan terkunci ADR-0008. |
| 17 | `StokClientFactory.php` | `StokClient` | Stok cabang (`UNIQUE(id_obat, id_client)`). |
| 18 | `SuperAdminFactory.php` | `SuperAdmin` | Entitas administrator pusat. |
| 19 | `UserFactory.php` | `User` | Akun autentikasi Laravel Fortify & 2FA/Passkeys. |
- **4 Seeder (`database/seeders/`):**
  1. `DatabaseSeeder.php` — Entrypoint seeder utama.
  2. `DemoApotekSeeder.php` (527 baris) — Mengisi 2 cabang (`banyuwangi-kota`, `rogojampi`), akun demo multi-peran, 3 kategori regulasi, master obat awal, stok FEFO, serta contoh transaksi/resep (`#ORD-20260917-0042` atas nama `Candi Chier (88 th)`).
  3. `GoApotikCatalogSeeder.php` — Memuat 42 produk terkurasi (`database/data/goapotik_catalog_42.json`, 51.768 bytes) + alokasi stok multi-cabang.
  4. `GoApotikFullCatalogSeeder.php` (162 baris) — Memuat dataset katalog lengkap GoApotik dari `database/data/scraped_goapotik_lengkap.json` (3.227.849 bytes, **2.424 produk terverifikasi**) beserta pasangan CSV-nya (`scraped_goapotik_lengkap.csv`, 2.284.973 bytes) dan 8 file JSON per kategori di `database/data/kategori/`.
- **Inventaris Lengkap Dataset `database/data/`:**
  - Katalog Utama & Gabungan: `goapotik_catalog_42.json` (51.768 B, 42 produk), `goapotik_catalog_all.json` (109.866 B), `goapotik_catalog_clean.json` (109.866 B), `scraped_goapotik_lengkap.json` (3.227.849 B, 2.424 produk), `scraped_goapotik_lengkap.csv` (2.284.973 B).
  - Subset Scraping Awal: `scraped_alkes.json` (16.313 B), `scraped_herbal.json` (16.984 B), `scraped_obat.json` (14.692 B), `scraped_suplemen.json` (13.927 B).
  - 8 Dataset per Kategori (`database/data/kategori/`): `scraped_alat_kesehatan.json` (181.416 B), `scraped_herbal.json` (235.938 B), `scraped_ibu_dan_anak.json` (181.545 B), `scraped_mata.json` (79.914 B), `scraped_nutrisi.json` (196.088 B), `scraped_obat.json` (1.830.311 B), `scraped_perawatan_diri.json` (209.532 B), `scraped_suplemen.json` (313.119 B).
- **5 Skrip Utilitas (`scripts/`):** `categories_hierarchy.json` (420 baris), `scrape_goapotik.py` (168 baris), `scrape_goapotik_full.py` (366 baris), `scrape_goapotik_massive.py` (500 baris), dan `verify-ui-animations.mjs` (678 baris).
- **Aset Visual Publik Terverifikasi (`public/` & `public/images/goapotik/`):**
  - Aset root: `logo.png` (387.649 B), `login-ref.jpg` (50.229 B), `favicon.svg` (517.040 B), `favicon.ico` (14.857 B), `favicon-32x32.png` (2.462 B), `apple-touch-icon.png` (32.218 B), serta `public/images/placeholder-product.svg` (1.545 B) yang dipakai sebagai fallback gambar di `catalog-product-card.blade.php` dan `order-history.blade.php`.
  - Aset GoApotik (`public/images/goapotik/`, total **212 berkas**): `badges/` (`5` berkas golongan regulasi BPOM), `categories/` (`9` berkas ikon kategori), `diagnosis/` (`15` berkas), `subcategories/` (`73` berkas), dan `svg/` (`110` berkas ikon UI vektor terkurasi yang direferensikan oleh `GoApotikCatalogAssets.php`).

### 10.6 Inventaris Lengkap 37 File Pengujian (`240` Test Methods)

Pemeriksaan langsung melalui `php vendor/bin/phpunit --list-tests` dan pemindaian seluruh berkas `*Test.php` mengonfirmasi tepat **37 file test** berisi **240 method `test_*`** (`tests/Unit/` 1 file / 7 tests + `tests/Feature/` 36 file / 233 tests):

| No | File Test (`tests/...`) | Baris | Jml Test | Fokus Pengujian Terverifikasi |
|---|---|---|---|---|
| 1 | `Unit/UserRoleTest.php` | 149 | 7 | `hasRole()`, akun suspended/null, `roleLabel()`, isolasi kasir vs super_admin, `profileActions()`. |
| 2 | `Feature/ApotekDatabaseConstraintTest.php` | 166 | 5 | PK UUID, `UNIQUE(id_obat, id_client)`, snapshot `item_pesanan` ADR-0008, `uq_ringkasan_client_periode`, cast decimal. |
| 3 | `Feature/ApotekFoundationTest.php` | 194 | 11 | Registrasi 6 provider, akses portal publik/tenant/admin, penolakan 403 cross-role/inactive, endpoint verifikasi/tutup periode/ekspor. |
| 4 | `Feature/Auth/AuthenticationTest.php` | 81 | 5 | Render login, autentikasi, password salah, redirect 2FA challenge, logout. |
| 5 | `Feature/Auth/EmailVerificationTest.php` | 88 | 4 | Render verifikasi email, verifikasi sukses, hash invalid, redirect jika sudah terverifikasi. |
| 6 | `Feature/Auth/PasswordConfirmationTest.php` | 21 | 1 | Render layar konfirmasi kata sandi. |
| 7 | `Feature/Auth/PasswordResetTest.php` | 81 | 4 | Request reset link, render reset password, reset dengan token sah. |
| 8 | `Feature/Auth/RegistrationTest.php` | 41 | 2 | Render halaman registrasi dan pembuatan akun baru. |
| 9 | `Feature/Auth/TwoFactorChallengeTest.php` | 42 | 2 | Redirect guest dan render tantangan Two-Factor Authentication. |
| 10 | `Feature/CartCheckoutPaymentTest.php` | 955 | 20 | Keranjang Redis, checkout atomik `lockForUpdate`, EMVCo QRIS, webhook HMAC + idempotensi, pembatalan, IDOR payment, ongkir Haversine/Maps, Midtrans sandbox, **riwayat pesanan & filter cabang aktif (Candi)**, QRIS expiry, dan penahanan `menunggu_verifikasi` resep. |
| 11 | `Feature/CustomerPrescriptionUploadAndQueueTest.php` | 531 | 15 | E2E upload resep ke antrean apoteker, penyimpanan `public/resep`, isolasi tenant resep, approve/reject apoteker, validasi ukuran/format, anti-IDOR verifikasi cabang lain, dispatch `OrderStatusUpdated`, `LogAudit` + restorasi stok. |
| 12 | `Feature/DashboardTest.php` | 106 | 9 | Redirect guest, akses `/dashboard`, landing `/` vs redirect role, pengabaian intended URL root, tombol kembali antar portal. |
| 13 | `Feature/EmailOtpAuthTest.php` | 154 | 5 | Generasi & cache OTP, verifikasi OTP sukses/gagal, registrasi OTP, redirect `complete-profile` Google pertama kali. |
| 14 | `Feature/EndToEndOrderFlowTest.php` | 122 | 1 | **Suite E2E Candi:** Resep upload -> Redis Cart -> Checkout -> Webhook QRIS + Reverb `OrderStatusUpdated` -> `CloseSalesPeriodAction` ADR-0008. |
| 15 | `Feature/ExampleTest.php` | 18 | 1 | Smoke test respons `/` 200 OK. |
| 16 | `Feature/FefoStockRotationTest.php` | 125 | 2 | Pengurutan `StockFefoService` berdasarkan `expired` terdekat & indikator H-30 / H-7. |
| 17 | `Feature/FirebaseAuthTest.php` | 142 | 5 | Pembuatan user Firebase session, redirect `super_admin`, logout JSON/HTML, COOP header & blokir user nonaktif. |
| 18 | `Feature/GoApotikCatalogIntegrationTest.php` | 280 | 10 | Katalog 30–50 produk, idempotensi seeder, filter kategori/subkategori/keyword, badge BPOM, isolasi stok cabang, multi-word search, **tampilan terkunci stok habis (Candi)**. |
| 19 | `Feature/GuestSessionAndCatalogTest.php` | 133 | 4 | Opaque token 32-karakter `CustomerSessionService`, filter keyword/sediaan, isolasi stok cabang. |
| 20 | `Feature/ModularFoundationTest.php` | 89 | 8 | Boundary publik/guest/tenant/admin, 403 wrong role/no role/suspended, 6 module providers. |
| 21 | `Feature/ModuleConventionTest.php` | 115 | 5 | Namespace view `{alias}::`, route file dalam `PortalRoutes`, guard portal, Livewire namespace modul. |
| 22 | `Feature/MultiRoleDashboardFlowTest.php` | 319 | 11 | Alur login & homeRoute semua 4 role, `?welcome=1`, logout, pengabaian intended URL lintas portal, login pakai username & switch cabang. |
| 23 | `Feature/PrescriptionVerificationTest.php` | 118 | 4 | `VerifyPrescriptionAction` approve/reject, validasi regex SIP `XXX/SIP/YYYY`, wajib alasan tolak. |
| 24 | `Feature/QueueAndStatusProgressionTest.php` | 238 | 6 | Slot waktu & nomor antrean virtual, progresi status + **dispatch Reverb (Candi)**, panggil antrean +1 & walk-in, monitoring read-only super admin, view `order-track.blade.php`. |
| 25 | `Feature/ReverbOrderStatusBroadcastTest.php` | 49 | 2 | **Suite M12-RT Candi:** Saluran `private-order.{id}` & `private-tenant.{id}`, struktur payload `broadcastWith()`. |
| 26 | `Feature/RoleCrudAndKasirPortalTest.php` | 417 | 5 | Matriks 403 lintas 4 portal, walk-in POS & pelunasan/struk/batal Kasir, CRUD cabang/staf/obat Super Admin, manajemen FEFO/pasien/laporan cabang, menu profil adaptif & audit apoteker. |
| 27 | `Feature/SalesReportAndPeriodLockTest.php` | 117 | 3 | **Suite M13 Candi:** Kunci periode permanen & tolak duplikat ADR-0008, agregasi `SalesReportService`, sanitasi CSV formula injection. |
| 28 | `Feature/SecurityHardeningTest.php` | 81 | 5 | **Suite M14 Candi:** 6 security headers, CSP WebSocket Reverb, definisi 3 rate limiter, validasi *magic bytes* gambar asli vs file PHP tersamar. |
| 29 | `Feature/ServiceAndCatalogAnimationVerificationTest.php` | 208 | 6 | Koneksi Redis, `CartService` Redis, checkpoint tab kategori & terminal meta, pill subkategori sliding, stabilitas grid katalog. |
| 30 | `Feature/Settings/ProfileUpdateTest.php` | 192 | 11 | Render & update profil, verifikasi email, sanitasi input, toast, absensi section delete/portal, **update & sanitasi `phone` & `address` (Candi)**, hapus akun. |
| 31 | `Feature/Settings/SecurityTest.php` | 166 | 8 | Render security settings, 2FA abandonment, update password & validasi konfirmasi/password lama, toast. |
| 32 | `Feature/Settings/SettingsNavigationTest.php` | 40 | 2 | Verifikasi tab appearance dihilangkan dari navigasi settings. |
| 33 | `Feature/SidebarNavigationTest.php` | 65 | 4 | Verifikasi absennya tautan repo/docs di sidebar & tampilan label peran pada widget profil. |
| 34 | `Feature/SuperAdminSlidingNavbarTest.php` | 88 | 3 | Animasi sliding navbar Super Admin, penghindaran eager load `stokObat` tak terpakai, isolasi portal non-admin. |
| 35 | `Feature/TanyaApotekerTest.php` | 502 | 21 | Tombol Tanya Apoteker, modal verifikasi pasien, pemilihan cabang & pesan awal, kirim teks & gambar, tolak file oversized, portal chat apoteker, isolasi lintas cabang (anti-IDOR) & lintas sesi pasien, penanganan file non-previewable & UUID invalid. |
| 36 | `Feature/TenantIsolationTest.php` | 243 | 15 | Resolusi tenant via slug/UUID, 403 cabang nonaktif, 404 tidak ada, filter `TenantScope`, auto-assign & tolak tampering `creating`, anti-IDOR apoteker/staf lintas cabang, bypass `withoutTenancy()`, `clear()`, `runInContext()`, `tenant:audit`. |
| 37 | `Feature/UnifiedCatalogAndMultiBranchAvailabilityTest.php` | 508 | 8 | Alokasi 20–30 produk aktif per cabang, variasi stok multi-cabang, helper `Obat`, tombol alih cabang di `PublicCatalog` & `obat-detail`, shortcut portal, pembelian langsung obat keras di katalog. |

**Katalog Lengkap Seluruh 240 Method `test_*` Terverifikasi (`php vendor/bin/phpunit --list-tests`):**

#### A. Suite Fondasi, Arsitektur, Multi-Tenancy, & Database (`51` tests across `6` files)
- **`Tests\Unit\UserRoleTest` (7):**
  1. `test_has_role_true_only_when_active_and_matching`
  2. `test_suspended_user_holds_no_role`
  3. `test_null_role_holds_no_role`
  4. `test_role_label_returns_customer_for_null_or_unknown_roles`
  5. `test_role_label_maps_roles_correctly`
  6. `test_kasir_and_admin_are_isolated_from_super_admin`
  7. `test_profile_actions_adapt_strictly_per_role`
- **`Tests\Feature\ApotekDatabaseConstraintTest` (5):**
  1. `test_entities_use_valid_uuid_primary_keys`
  2. `test_duplicate_stock_record_for_same_drug_and_client_is_rejected`
  3. `test_order_item_price_snapshot_remains_immutable_when_master_price_changes`
  4. `test_duplicate_sales_period_summary_for_same_client_and_dates_is_rejected`
  5. `test_money_columns_cast_properly_as_decimal`
- **`Tests\Feature\ApotekFoundationTest` (11):**
  1. `test_all_six_apotek_module_providers_are_registered`
  2. `test_customer_portal_is_publicly_accessible`
  3. `test_customer_can_upload_prescription_digitally`
  4. `test_guest_is_redirected_to_login_on_protected_portals`
  5. `test_apoteker_can_access_tenant_portal`
  6. `test_super_admin_can_access_admin_portal`
  7. `test_cross_role_access_is_forbidden_with_403`
  8. `test_inactive_user_is_forbidden`
  9. `test_pharmacist_can_verify_prescription_endpoint`
  10. `test_admin_can_execute_period_closing_endpoint`
  11. `test_admin_can_export_report_endpoint`
- **`Tests\Feature\ModularFoundationTest` (8):**
  1. `test_customer_route_is_public`
  2. `test_guest_is_redirected_to_login_on_tenant_and_admin`
  3. `test_tenant_operator_can_open_tenant_dashboard`
  4. `test_admin_can_open_admin_dashboard`
  5. `test_wrong_role_is_forbidden_not_empty_page`
  6. `test_user_without_role_is_forbidden_on_internal_contexts`
  7. `test_suspended_operator_is_forbidden`
  8. `test_all_six_module_providers_are_registered`
- **`Tests\Feature\ModuleConventionTest` (5):**
  1. `test_every_module_registers_its_own_view_namespace`
  2. `test_module_route_file_is_loaded_inside_portal_group`
  3. `test_module_route_is_guarded_like_core_portal_routes`
  4. `test_module_livewire_component_is_resolved_through_module_namespace`
  5. `test_every_portal_route_carries_the_portal_middleware`
- **`Tests\Feature\TenantIsolationTest` (15):**

| # | Nama Method (`Tests\Feature\TenantIsolationTest`) | Cakupan Verifikasi |
|---|---|---|
| 1 | `test_tenant_context_resolves_active_client_from_route_parameter` | Resolusi `TenantContext` dari parameter slug rute. |
| 2 | `test_tenant_context_resolves_active_client_from_uuid_parameter` | Resolusi `TenantContext` dari UUID `id_client`. |
| 3 | `test_inactive_tenant_returns_403_forbidden` | `TenantInactiveException` mengembalikan HTTP 403. |
| 4 | `test_non_existent_tenant_returns_404_not_found` | `TenantNotFoundException` mengembalikan HTTP 404. |
| 5 | `test_tenant_scope_automatically_filters_orders_by_active_client` | `TenantScope` menyaring `Pesanan` per cabang aktif. |
| 6 | `test_tenant_scope_automatically_assigns_client_id_on_creating` | Hook `creating` `BelongsToTenant` mengisi `id_client`. |
| 7 | `test_tenant_scope_rejects_payload_tampering_with_different_client_id` | Lempar `TenantAccessDeniedException` bila `id_client` dipalsukan. |
| 8 | `test_pharmacist_cannot_view_orders_from_another_branch_via_idor` | `PesananPolicy` / `ResolveTenantContext` blokir IDOR lintas cabang. |
| 9 | `test_pharmacist_cannot_access_unassigned_branch_portal` | Blokir apoteker mengakses `/cabang/{cabang_lain}`. |
| 10 | `test_customer_in_branch_a_cannot_see_branch_b_stock` | `StokClient` terisolasi per cabang aktif. |
| 11 | `test_super_admin_can_bypass_tenant_scope_using_without_tenancy` | Macro `withoutTenancy()` membuka akses lintas cabang untuk Super Admin. |
| 12 | `test_tenant_context_clear_resets_singleton` | `TenantContext::clear()` mereset state singleton. |
| 13 | `test_run_in_context_executes_callback_safely_and_restores_original_context` | `runInContext()` memulihkan konteks asal di blok `finally`. |
| 14 | `test_audit_tenant_isolation_artisan_command_passes` | `php artisan tenant:audit` keluar dengan exit code 0. |
| 15 | `test_authenticated_user_branch_staff_cannot_access_other_branch_portal` | Staf cabang terautentikasi ditolak 403 di portal cabang lain. |

#### B. Suite Autentikasi, OTP, Firebase, Dashboard, & Pengaturan (`63` tests across `15` files)
- **`Tests\Feature\Auth\AuthenticationTest` (5):**
  1. `test_login_screen_can_be_rendered`
  2. `test_users_can_authenticate_using_the_login_screen`
  3. `test_users_can_not_authenticate_with_invalid_password`
  4. `test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge`
  5. `test_users_can_logout`
- **`Tests\Feature\Auth\EmailVerificationTest` (4):**
  1. `test_email_verification_screen_can_be_rendered`
  2. `test_email_can_be_verified`
  3. `test_email_is_not_verified_with_invalid_hash`
  4. `test_already_verified_user_visiting_verification_link_is_redirected_without_firing_event_again`
- **`Tests\Feature\Auth\PasswordConfirmationTest` (1):**
  1. `test_confirm_password_screen_can_be_rendered`
- **`Tests\Feature\Auth\PasswordResetTest` (4):**
  1. `test_reset_password_link_screen_can_be_rendered`
  2. `test_reset_password_link_can_be_requested`
  3. `test_reset_password_screen_can_be_rendered`
  4. `test_password_can_be_reset_with_valid_token`
- **`Tests\Feature\Auth\RegistrationTest` (2):**
  1. `test_registration_screen_can_be_rendered`
  2. `test_new_users_can_register`
- **`Tests\Feature\Auth\TwoFactorChallengeTest` (2):**
  1. `test_two_factor_challenge_redirects_to_login_when_not_authenticated`
  2. `test_two_factor_challenge_can_be_rendered`
- **`Tests\Feature\EmailOtpAuthTest` (5):**
  1. `test_send_otp_generates_and_caches_code`
  2. `test_verify_otp_authenticates_user_successfully`
  3. `test_verify_otp_fails_with_invalid_code`
  4. `test_register_with_otp_verifies_email_and_creates_account`
  5. `test_google_first_time_login_redirects_to_complete_profile`
- **`Tests\Feature\FirebaseAuthTest` (5):**
  1. `test_firebase_session_creates_user_and_authenticates`
  2. `test_firebase_session_redirects_super_admin_to_admin_portal`
  3. `test_firebase_logout_invalidates_session`
  4. `test_firebase_logout_redirects_for_html_requests`
  5. `test_login_page_allows_oauth_popups_and_blocks_inactive_firebase_user`
- **`Tests\Feature\DashboardTest` (9):**
  1. `test_guests_are_redirected_to_the_login_page`
  2. `test_authenticated_users_can_visit_the_dashboard`
  3. `test_root_url_shows_omnipharma_catalog_not_laravel_welcome`
  4. `test_root_url_redirects_authenticated_user_to_dashboard`
  5. `test_root_url_redirects_super_admin_to_admin_dashboard`
  6. `test_root_url_redirects_apoteker_to_tenant_dashboard`
  7. `test_login_ignores_intended_root_to_prevent_landing_on_laravel`
  8. `test_customer_portal_pages_contain_navigation_back_to_dashboard`
  9. `test_tenant_and_admin_portals_contain_navigation_back_to_dashboard`
- **`Tests\Feature\MultiRoleDashboardFlowTest` (11):**
  1. `test_guest_can_access_clean_welcome_page_and_credentials_are_documented`
  2. `test_pelanggan_can_login_and_access_customer_dashboard`
  3. `test_apoteker_can_login_and_access_tenant_dashboard`
  4. `test_super_admin_can_login_and_access_admin_dashboard`
  5. `test_kasir_can_login_and_access_kasir_ledger`
  6. `test_authenticated_user_can_view_welcome_page_with_welcome_param`
  7. `test_logout_flow_clears_session_and_redirects`
  8. `test_guests_are_redirected_to_login_before_accessing_any_role_dashboard`
  9. `test_login_ignores_intended_customer_urls_to_prevent_landing_on_prescription_store`
  10. `test_login_ignores_unauthorized_cross_role_intended_portal_urls`
  11. `test_user_can_login_with_username_and_welcome_branches_switch_cleanly`
- **`Tests\Feature\ExampleTest` (1):**
  1. `test_returns_a_successful_response`
- **`Tests\Feature\Settings\ProfileUpdateTest` (11):**

| # | Nama Method (`ProfileUpdateTest`) | Cakupan Verifikasi |
|---|---|---|
| 1 | `test_profile_page_is_displayed` | Halaman `/settings/profile` tampil 200 OK. |
| 2 | `test_profile_information_can_be_updated` | Pembaruan `name` & `email` berhasil. |
| 3 | `test_email_verification_status_is_unchanged_when_email_address_is_unchanged` | `email_verified_at` dipertahankan jika email sama. |
| 4 | `test_profile_information_is_sanitized_and_trimmed` | Sanitasi tag & spasi pada profil. |
| 5 | `test_profile_update_dispatches_success_toast` | Event toast sukses Flux terpanggil. |
| 6 | `test_delete_account_section_is_absent_from_profile_page` | Bagian hapus akun disembunyikan di halaman profil. |
| 7 | `test_portal_access_section_is_removed_from_profile_page` | Bagian portal access lama tidak muncul. |
| 8 | `test_profile_phone_and_address_can_be_updated` | **(Candi PR #25)** Simpan kolom `phone` & `address`. |
| 9 | `test_profile_phone_and_address_are_trimmed_and_sanitized` | **(Candi PR #25)** Sanitasi `strip_tags(trim(...))` `phone` & `address`. |
| 10 | `test_user_can_delete_their_account` | Komponen `delete-user-form` menghapus user dengan password benar. |
| 11 | `test_correct_password_must_be_provided_to_delete_account` | Penolakan hapus akun jika password salah. |

- **`Tests\Feature\Settings\SecurityTest` (8):**
  1. `test_security_settings_page_can_be_rendered`
  2. `test_security_settings_page_accessible_without_password_confirmation`
  3. `test_security_settings_page_renders_without_two_factor_when_feature_is_disabled`
  4. `test_two_factor_authentication_disabled_when_confirmation_abandoned_between_requests`
  5. `test_password_can_be_updated`
  6. `test_correct_password_must_be_provided_to_update_password`
  7. `test_password_confirmation_mismatch_fails_validation`
  8. `test_password_update_dispatches_success_toast`
- **`Tests\Feature\Settings\SettingsNavigationTest` (2):**
  1. `test_appearance_tab_is_absent_from_settings_navigation`
  2. `test_security_page_also_omits_appearance_tab`
- **`Tests\Feature\SidebarNavigationTest` (4):**
  1. `test_repository_and_documentation_links_are_absent_from_sidebar`
  2. `test_default_user_role_customer_is_displayed_in_profile_widget`
  3. `test_assigned_user_role_is_displayed_in_profile_widget`
  4. `test_apoteker_role_is_displayed_in_profile_widget`

#### C. Suite Katalog, Keranjang, Checkout, QRIS, Resep, & Konsultasi (`104` tests across `10` files)
- **`Tests\Feature\CartCheckoutPaymentTest` (20):**

| # | Nama Method (`CartCheckoutPaymentTest`) | Cakupan Verifikasi |
|---|---|---|
| 1 | `test_cart_service_stores_and_isolates_by_client` | Isolasi keranjang Redis `cart:{session}:{client}`. |
| 2 | `test_atomic_checkout_locks_stock_and_creates_order_snapshot` | Penguncian stok atomik & snapshot `item_pesanan`. |
| 3 | `test_checkout_fails_atomically_if_stock_insufficient` | Rollback transaksi jika stok tidak cukup. |
| 4 | `test_qris_emvco_builder_produces_standard_emvco_string` | Payload string QRIS EMVCo + CRC16 CCITT. |
| 5 | `test_payment_webhook_with_valid_hmac_signature_settles_order` | Webhook HMAC valid melunasi pesanan. |
| 6 | `test_payment_webhook_with_invalid_signature_is_rejected` | Webhook signature salah ditolak HTTP 401. |
| 7 | `test_payment_webhook_is_idempotent` | Webhook duplikat tidak menggandakan record pembayaran. |
| 8 | `test_customer_web_routes_render_cart_checkout_and_payment` | Render halaman cart, checkout, dan payment. |
| 9 | `test_order_cancellation_restores_physical_stock_atomically` | Pembatalan pesanan memulihkan `stok_client`. |
| 10 | `test_payment_page_rejects_cross_tenant_access_preventing_idor` | Blokir IDOR pembayaran silang cabang. |
| 11 | `test_cart_items_include_product_cover_and_metadata` | Item keranjang memuat `url_gambar`, `kategori`, `satuan`. |
| 12 | `test_delivery_checkout_calculates_distance_based_ongkir_and_renders_google_maps_routes` | Ongkir berbasis jarak Haversine + tautan Google Maps. |
| 13 | `test_customer_checkout_accepts_manual_address_and_pasted_google_maps_shortlink` | Parsing shortlink Google Maps & alamat manual. |
| 14 | `test_midtrans_sandbox_qris_charge_status_check_and_webhook_settlement` | Charge, check-status, & settlement Midtrans Sandbox. |
| 15 | `test_order_history_page_renders_customer_orders_and_clean_profile_dropdown` | **(Candi PR #19)** Tampilan riwayat pesanan & dropdown bersih. |
| 16 | `test_order_history_scopes_to_active_branch_and_track_resolves_cross_session_branch` | **(Candi PR #20–#22)** Filter cabang aktif & resolusi track lintas sesi. |
| 17 | `test_qris_expiry_persists_across_refresh_and_auto_poll_does_not_auto_settle_pending_order` | Timer QRIS persisten & auto-poll tidak auto-lunas. |
| 18 | `test_checkout_auto_creates_valid_pengguna_record_for_new_authenticated_user` | Pembuatan otomatis `Pengguna` untuk akun baru saat checkout. |
| 19 | `test_authenticated_customer_cannot_view_or_cancel_another_customers_order` | Blokir akses/pembatalan pesanan milik pelanggan lain (403). |
| 20 | `test_qris_settlement_preserves_menunggu_verifikasi_when_order_has_pending_prescription` | Pelunasan QRIS menahan status `menunggu_verifikasi` jika ada resep tertunda. |

- **`Tests\Feature\CustomerPrescriptionUploadAndQueueTest` (15):**

| # | Nama Method (`CustomerPrescriptionUploadAndQueueTest`) | Cakupan Verifikasi |
|---|---|---|
| 1 | `test_customer_prescription_upload_creates_pesanan_and_resep_records` | Pembuatan record `pesanan` & `resep` saat upload. |
| 2 | `test_customer_prescription_upload_stores_image_in_public_storage` | Simpan foto resep di disk `public/resep`. |
| 3 | `test_uploaded_prescription_appears_in_tenant_pharmacist_dashboard` | Resep tampil di dashboard apoteker cabang. |
| 4 | `test_uploaded_prescription_appears_in_tenant_prescription_verification_queue` | Resep tampil di halaman `/cabang/{client}/resep`. |
| 5 | `test_prescriptions_are_strictly_isolated_by_branch_tenant` | Isolasi resep per cabang (tidak bocor ke cabang lain). |
| 6 | `test_pharmacist_can_approve_prescription` | Persetujuan resep oleh apoteker mengubah status `diproses`. |
| 7 | `test_pharmacist_can_reject_prescription` | Penolakan resep mengubah status `dibatalkan`. |
| 8 | `test_customer_prescription_upload_with_authenticated_user_links_user_account` | Tautkan `id_pengguna` user terautentikasi ke pesanan resep. |
| 9 | `test_customer_prescription_upload_rejects_oversized_file` | Penolakan 422 berkas resep > 5 MB (`UnggahResepRequest`). |
| 10 | `test_customer_prescription_upload_rejects_invalid_file_type` | Penolakan 422 format non-gambar (`UnggahResepRequest`). |
| 11 | `test_customer_prescription_upload_handles_empty_string_inputs_gracefully` | Penanganan input string kosong dengan fallback aman. |
| 12 | `test_pharmacist_cannot_verify_prescription_from_another_branch` | Penolakan 404 verifikasi resep milik cabang lain. |
| 13 | `test_pharmacist_verification_via_browser_form_redirects_back` | Submit form HTML apoteker redirect kembali dengan flash. |
| 14 | `test_pharmacist_verification_dispatches_order_status_updated_event` | **(Terkait M12-RT Candi)** Verifikasi resep memancarkan `OrderStatusUpdated`. |
| 15 | `test_rejecting_prescription_writes_audit_log_and_restores_item_stock` | Penolakan menulis `LogAudit` `TELAAH_RESEP` & memulihkan stok. |

- **`Tests\Feature\TanyaApotekerTest` (21):**

| # | Nama Method (`TanyaApotekerTest`) | Cakupan Verifikasi |
|---|---|---|
| 1 | `test_customer_ui_renders_tanya_apoteker_button_without_soon_badge` | Tombol Tanya Apoteker aktif di UI pelanggan. |
| 2 | `test_dashboard_renders_tanya_apoteker_button_without_soon_badge` | Tombol Tanya Apoteker tampil di `/dashboard`. |
| 3 | `test_customer_verification_modal_validation` | Validasi input modal verifikasi pasien. |
| 4 | `test_customer_branch_selection_and_starter_template_message` | Pemilihan cabang & template pesan pembuka. |
| 5 | `test_customer_can_send_text_message_and_image_attachment` | Kirim teks & lampiran gambar pasien ke apoteker. |
| 6 | `test_customer_image_upload_rejects_oversized_file` | Penolakan lampiran gambar melebihi batas ukuran. |
| 7 | `test_apoteker_portal_renders_chat_button_for_apoteker_role` | Tombol portal chat tampil khusus role `apoteker`. |
| 8 | `test_apoteker_multi_patient_interface_and_branch_isolation` | Daftar multi-pasien terisolasi per cabang. |
| 9 | `test_apoteker_can_view_conversation_and_send_reply` | Apoteker membaca thread & membalas pesan. |
| 10 | `test_apoteker_can_mark_session_as_completed` | Apoteker menutup sesi konsultasi selesai. |
| 11 | `test_unauthenticated_user_cannot_access_apoteker_chat_portal` | Guest ditolak mengakses portal chat apoteker. |
| 12 | `test_customer_user_cannot_access_apoteker_chat_portal` | Pelanggan ditolak 403 mengakses portal apoteker. |
| 13 | `test_apoteker_cannot_select_cross_branch_consultation_idor` | Blokir IDOR pilih sesi konsultasi cabang lain. |
| 14 | `test_apoteker_cannot_reply_to_cross_branch_consultation_idor` | Blokir IDOR balas konsultasi cabang lain. |
| 15 | `test_apoteker_cannot_terminate_cross_branch_consultation_idor` | Blokir IDOR tutup konsultasi cabang lain. |
| 16 | `test_customer_cannot_select_inactive_branch` | Blokir pemilihan cabang berstatus `aktif = false`. |
| 17 | `test_customer_cannot_view_or_send_to_other_customer_session_idor` | Blokir IDOR akses sesi konsultasi pelanggan lain. |
| 18 | `test_non_previewable_file_upload_does_not_crash_customer_modal` | Unggahan non-previewable ditangani aman di modal pasien. |
| 19 | `test_non_previewable_file_upload_does_not_crash_apoteker_portal` | Unggahan non-previewable ditangani aman di portal apoteker. |
| 20 | `test_invalid_uuid_or_deleted_consultation_handled_gracefully` | UUID invalid atau konsultasi terhapus tidak memicu 500. |
| 21 | `test_realtime_upload_hooks_validate_mime_and_size` | Hook validasi MIME & ukuran file lampiran chat. |

- **`Tests\Feature\GoApotikCatalogIntegrationTest` (10):**
  1. `test_catalog_database_has_30_to_50_representative_products`
  2. `test_seeder_is_completely_idempotent_when_executed_multiple_times`
  3. `test_livewire_catalog_filters_by_parent_category`
  4. `test_livewire_catalog_filters_by_subcategory`
  5. `test_livewire_catalog_searches_products_by_keyword_reactively`
  6. `test_catalog_card_renders_bpom_regulatory_badges_and_pricing`
  7. `test_catalog_isolates_tenant_inventory_for_active_branches`
  8. `test_catalog_matches_composite_sediaan_keywords_and_encodes_image_url_spaces`
  9. `test_search_resets_subcategory_supports_multi_word_tokens_and_q_query_param`
  10. `test_out_of_stock_medicine_displays_with_locked_green_appearance_and_notice` *(Candi PR #25)*
- **`Tests\Feature\UnifiedCatalogAndMultiBranchAvailabilityTest` (8):**
  1. `test_seeder_allocates_between_20_and_30_active_products_per_branch`
  2. `test_stock_distribution_has_clear_multi_branch_variation`
  3. `test_obat_model_multi_branch_availability_helpers`
  4. `test_catalog_livewire_renders_multi_branch_availability_and_switch_button`
  5. `test_obat_detail_page_renders_branch_availability_indicators`
  6. `test_dashboard_and_navigation_align_to_unified_catalog`
  7. `test_clean_short_endpoints_and_role_shortcuts`
  8. `test_hard_medicine_can_be_purchased_directly_and_navbar_displays_cart_badge_and_chat_coming_soon`
- **`Tests\Feature\GuestSessionAndCatalogTest` (4):**
  1. `test_guest_session_generates_32_character_opaque_token_cookie`
  2. `test_catalog_filters_medicines_by_search_keyword`
  3. `test_catalog_filters_medicines_by_jenis_sediaan`
  4. `test_catalog_isolates_stock_per_branch`
- **`Tests\Feature\FefoStockRotationTest` (2):**
  1. `test_batches_are_sorted_by_earliest_expiration_date_first_expired_first_out`
  2. `test_early_warning_indicators_for_near_and_critical_expiry`
- **`Tests\Feature\PrescriptionVerificationTest` (4):**
  1. `test_pharmacist_can_approve_prescription_with_valid_sip`
  2. `test_approving_prescription_with_invalid_sip_throws_validation_exception`
  3. `test_pharmacist_can_reject_prescription_with_mandatory_reason`
  4. `test_rejecting_prescription_without_reason_throws_validation_exception`
- **`Tests\Feature\ServiceAndCatalogAnimationVerificationTest` (6):**
  1. `test_redis_service_is_active_and_accessible`
  2. `test_cart_service_operates_cleanly_with_redis`
  3. `test_category_bar_checkpoint_order_and_mata_terminal_boundary`
  4. `test_category_tabs_blade_renders_sliding_indicators_and_terminal_meta`
  5. `test_subcategory_sidebar_blade_renders_sliding_indicator_pill`
  6. `test_catalog_view_eliminates_disruptive_loading_and_stabilizes_grid`
- **`Tests\Feature\SuperAdminSlidingNavbarTest` (3):**
  1. `test_super_admin_portal_renders_persisted_animated_sliding_navbar_with_checkpoints`
  2. `test_dashboard_route_does_not_eager_load_unused_stok_obat_relation`
  3. `test_non_admin_portals_preserve_isolated_static_nav_without_custom_nav_interference`

#### D. Suite Portal Operasional, Real-Time Reverb, Reporting, Security, & E2E (`22` tests across `6` files)
- **`Tests\Feature\QueueAndStatusProgressionTest` (6):**
  1. `test_order_creation_assigns_slot_waktu_and_virtual_queue_number`
  2. `test_pharmacist_can_advance_order_status_and_dispatches_reverb_event` *(terkait M12-RT Candi)*
  3. `test_pharmacist_can_update_current_serving_queue_and_add_walkin`
  4. `test_super_admin_can_access_read_only_monitoring_progres`
  5. `test_customer_can_view_order_tracking_with_virtual_queue` *(terkait M12-RT Candi)*
  6. `test_dashboard_hides_super_admin_and_live_monitoring_from_pharmacists`
- **`Tests\Feature\RoleCrudAndKasirPortalTest` (5):**
  1. `test_cross_role_matrix_enforces_403_across_all_four_portals`
  2. `test_kasir_can_create_walkin_order_settle_unpaid_order_print_struk_and_cancel`
  3. `test_super_admin_can_crud_branch_staff_and_master_obat`
  4. `test_apoteker_can_manage_fefo_stock_isolated_patients_and_branch_report`
  5. `test_dashboard_and_profile_adapt_buttons_for_each_role_and_super_admin_can_audit_apoteker` *(disesuaikan Candi PR #26)*
- **`Tests\Feature\ReverbOrderStatusBroadcastTest` (2 — Candi M12-RT):**
  1. `test_order_status_updated_broadcasts_to_correct_channels`
  2. `test_order_status_updated_broadcast_payload_structure`
- **`Tests\Feature\SalesReportAndPeriodLockTest` (3 — Candi M13):**
  1. `test_close_period_locks_record_permanently_and_prevents_duplicate`
  2. `test_sales_report_service_calculates_summary_correctly`
  3. `test_csv_export_mitigates_formula_injection_characters`
- **`Tests\Feature\SecurityHardeningTest` (5 — Candi M14):**
  1. `test_security_headers_are_present_on_http_responses`
  2. `test_content_security_policy_allows_reverb_websocket_connection`
  3. `test_rate_limiters_are_properly_configured`
  4. `test_prescription_upload_with_real_image_file_passes_magic_bytes_validation` *(Candi PR #13)*
  5. `test_prescription_upload_with_spoofed_file_fails_magic_bytes_validation` *(Candi PR #13)*
- **`Tests\Feature\EndToEndOrderFlowTest` (1 — Candi M14):**
  1. `test_complete_end_to_end_order_and_period_closing_flow`

*(Catatan verifikasi hitungan total: 7 Unit + 233 Feature = **240** test methods terkonfirmasi oleh `php vendor/bin/phpunit --list-tests`; subkelompok A=51, B=63, C=104, D=22 -> 51+63+104+22 = 240.)*

### 10.7 Inventaris Dependensi Paket & Konfigurasi Quality Gate (`composer.json`, `package.json`, `phpstan.neon`, `phpunit.xml`)

**1. Dependensi PHP Produksi & Pengembangan (`composer.json`, 115 baris):**
- **Require Produksi (`baris 11-21`):** `php: ^8.3`, `laravel/chisel: ^0.1.0`, `laravel/fortify: ^1.37.2`, `laravel/framework: ^13.17`, `laravel/reverb: ^1.0`, `laravel/tinker: ^3.0`, `livewire/blaze: ^1.0`, `livewire/flux: ^2.13.1`, `livewire/livewire: ^4.1`.
- **Require Dev (`baris 22-32`):** `fakerphp/faker: ^1.24`, `larastan/larastan: ^3.9`, `laravel/pail: ^1.2.5`, `laravel/pao: ^1.0.6`, `laravel/pint: ^1.27`, `laravel/sail: ^1.53`, `mockery/mockery: ^1.6`, `nunomaduro/collision: ^8.9.3`, `phpunit/phpunit: ^12.5.23`.
- **Composer Scripts (`baris 45-76`):**
  - `composer setup`: install dependensi Composer + salin `.env.example` + `key:generate` + `migrate --force` + `npm install` + `npm run build`.
  - `composer lint` / `lint:check`: `pint --parallel` / `pint --parallel --test`.
  - `composer types:check`: `phpstan analyse`.
  - `composer test` / `composer ci:check`: `@php artisan config:clear --ansi` -> `@lint:check` -> `@types:check` -> `@php artisan test`.

**2. Dependensi Node/Vite (`package.json`, 26 baris):**
- **Dependencies (`baris 9-19`):** `@laravel/passkeys: ^0.2.0`, `@tailwindcss/vite: ^4.1.11`, `concurrently: ^10.0.3`, `firebase: ^13.1.0`, `laravel-echo: ^2.4.0`, `laravel-vite-plugin: ^3.1`, `pusher-js: ^8.6.0`, `tailwindcss: ^4.0.7`, `vite: ^8.0.0`.
- **Scripts (`baris 5-8`):** `npm run build` (`vite build`), `npm run dev` (`vite`).

**3. Konfigurasi Analisis Statis & Pengujian (`phpstan.neon`, `phpunit.xml`, `pint.json`):**
- `phpstan.neon`: Mengaktifkan `larastan` + `carbon` extension, **`level: 7`**, memindai `app/`, `bootstrap/app.php`, `config/`, `database/`, `routes/`, dan mengecualikan `app/Modules/*/resources/*`.
- `phpunit.xml`: Mengatur `APP_ENV=testing`, `BROADCAST_CONNECTION=null`, `CACHE_STORE=array`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `MIDTRANS_SERVER_KEY=""`, `MIDTRANS_CLIENT_KEY=""`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`.
- `pint.json`: `{"preset": "laravel"}`.

---

## Bab 11. Audit Keselarasan Lintas-Dokumentasi & Architecture Decision Records (ADR 0001–0008)

### 11.1 Ringkasan Teknis ADR-0001 hingga ADR-0008 & Status Kepatuhannya

| ADR | Judul Resmi (`docs/adr/*.md`) | Keputusan Inti | Status Kepatuhan di Kodebase (`c39e087`) |
|---|---|---|---|
| **ADR-0001** | Struktur repository & workflow GitHub | Satu repo & satu aplikasi Laravel di root, integrasi tunggal di `main`, PR + CI checks. | **Patuh** (satu app di root, `.github/workflows/tests.yml` aktif di `main` & PR). Catatan: tag `pertemuan-NN` belum dibuat di repo (`git tag -l` kosong). |
| **ADR-0002** | Koneksi MariaDB & Redis | MariaDB port `3306` db `kantin`, Redis port `6379` (tanpa `FLUSHDB` global), `.env` di luar git. | **Patuh** (`.env` di-ignore `.gitignore`, `CartService` memakai key ber-namespace `cart:{session}:{client}`). |
| **ADR-0003** | Zona waktu | Penyimpanan DB dalam `UTC` (`APP_TIMEZONE=UTC`), presentasi UI `Asia/Jakarta` (WIB). | **Patuh** (`.env.example:66` `APP_TIMEZONE=UTC`; view tracking/history/laporan memformat ke `WIB`). |
| **ADR-0004** | Pemilihan payment provider (tertunda) | Abstraksi `PaymentGatewayInterface` dengan `FakeQrisGateway` lokal; verifikasi webhook HMAC raw body. | **Berevolusi** — Selain `FakeQrisGateway` & `QrisEmvcoBuilder`, kode kini juga mengintegrasikan HTTP API **Midtrans Sandbox** opsional saat `MIDTRANS_SERVER_KEY` terisi (`OrderTrackingController.php:106-142`, `PaymentWebhookController.php:26-33`). |
| **ADR-0005** | Framework testing & database uji | PHPUnit `^12`, gate `composer test` (`config:clear` -> `pint` -> `phpstan` -> `phpunit`), MariaDB uji `kantin_test` di CI. | **Patuh** (CI pakai `mariadb:11.4` & `redis:7`; `phpunit.xml` lokal memakai SQLite `:memory:` untuk test cepat). |
| **ADR-0006** | Modular Monolith & Registrasi Modul | 6 bounded context di `app/Modules/*`, `ModuleServiceProvider`, `PortalRoutes`, `EnsureUserHasRole`, `Prescription` mengganti `Kitchen`. | **Patuh 100%** (ditambah portal ke-4 `PortalRoutes::kasir()` di `PortalRoutes.php:43-49` dan diverifikasi `ModuleConventionTest`). |
| **ADR-0007** | Skema Basis Data Apotek Multi-Klien | UUID di semua PK/FK, `UNIQUE(id_obat, id_client)` di `stok_client`, `uq_ringkasan_client_periode`, FEFO `expired` & `batch_number`, append-only `log_audit`. | **Patuh 100%** (teruji di `ApotekDatabaseConstraintTest`). |
| **ADR-0008** | Controlled Denormalization & Price Snapshot | Snapshot `nama_obat`/`harga_satuan` di `item_pesanan`; snapshot `nama_client`/`total_obat_terjual` & immutability di `ringkasan_periode_penjualan`. | **Patuh 100%** (ditegakkan oleh `CreateOrderAction`, `CloseSalesPeriodAction`, dan `SalesReportAndPeriodLockTest`). |

### 11.2 Audit Sinkronisasi Dokumen Root, PRD Anggota, RTM, dan Roadmap

| Berkas Dokumentasi | Isi Utama | Ketidakcocokan Terdeteksi terhadap Kode Aktual (`c39e087`) |
|---|---|---|
| `README.md` (197 baris) | Ringkasan audit proyek, stack, pembagian 4-4-4-3, peta portal, akun demo, instalasi. | Menyebut `18` model, `17` migrasi, `196` tests, `1.576` produk GoApotik, dan tautan `TUGAS_DAFFA.md` (yang sudah dihapus); aktualnya `20` model, `20` migrasi, `37` file / `240` test methods, dan `2.424` produk di `scraped_goapotik_lengkap.json`. |
| `README_TUGAS_ANGGOTA_DAN_BACKLOG.md` (181 baris) | Dokumen historis sisa tugas 2% sebelum refactor controller & FormRequest selesai. | Status `98% / 120 tests` bersifat historis (seluruh tugas FormRequest, refactor controller, `pasien`, `bantuan`, `pengaturan-cabang`, dan `EndToEndOrderFlowTest` sudah rampung). |
| `PANDUAN_PRESENTASI_DAN_ROADMAP_OMNIPHARMA.md` (240 baris) | Panduan demo presentasi 5 menit, skenario 3 peran, roadmap jangka pendek/menengah/panjang, Q&A dosen. | Mencatat angka lama `196/196 tests` dan mencantumkan `/admin/kasir-ledger` untuk Kasir padahal portal khusus Kasir kini di `/kasir/dashboard` (`routes/kasir.php`). |
| `AKUN_LOGIN.md` (73 baris) | Daftar email/password kredensial pengujian manual & alur uji. | Baris 23 menyebut tujuan login Kasir `/admin/kasir-ledger`, sedangkan `User::homeRoute()` (`User.php:163-165`) mengarahkan role `kasir` ke `/kasir/dashboard` (karena `/admin/*` ditolak 403 untuk role `kasir`). |
| `docs/architecture.md` (61 baris) & `docs/data-dictionary.md` (177 baris) | Deskripsi 6 modul, shared kernel, prinsip antislop, kamus data 16 tabel awal. | Belum mendokumentasikan portal ke-4 (`kasir`), tabel `subkategori_obat`, `konsultasi`, `pesan_konsultasi`, kolom `users.phone`/`address`, maupun kolom tambahan `pesanan`/`obat`/`client`. |
| `docs/REQUIREMENTS_TRACEABILITY.md` (48 baris) | Matriks FR-PEL / FR-APT / FR-ADM & NFR terhadap kode & test. | Mereferensikan `StaffHierarchy5NFTest.php` dan `TenantContextIsolationTest.php` yang tidak ada; nama file nyata adalah `TenantIsolationTest.php`, `ApotekFoundationTest.php`, `RoleCrudAndKasirPortalTest.php`. Juga menyebut `FR-PEL-03` masih "SEBAGIAN" padahal `order-history.blade.php` & `OrderTrackingController::history()` sudah selesai penuh. |
| `docs/PRD_CANDI.md` (503 baris) | Spesifikasi M12-RT, M13, M14, contoh kode acuan, dan panduan Q&A Pak Devit. | Menulis `PrivateChannel('private-order.')`, `pesanan.status_pesanan`/`total_harga`, serta mengasumsikan `SalesReportExportService` sudah memiliki ekspor PDF dan limiter sudah terpasang ke rute (lihat rincian Bab 7.1). |
| `docs/PRD_DAFFA.md` (894), `PRD_GUNTUR.md` (600), `PRD_ARYA.md` (430), `ROADMAP_DAN_PLANNING_PROYEK.md` (278), `PRD_DESIGN_AND_RULES.md` (159) | Spesifikasi desain arsitektur masing-masing anggota & aturan UI Antislop/FigCraft. | Menjadi referensi desain awal yang konsisten; perkembangan pasca-M14 (Tanya Apoteker, Portal Kasir, GoApotik 2.424 produk, Slider Landing) berkembang di luar PRD awal. |

### 11.3 Pemetaan Arsip Kurikulum Praktikum (`modul_praktikum/*` & `references/*`) terhadap Implementasi Nyata

Repositori menyimpan **16 berkas dokumen praktikum** di `modul_praktikum/` dan **3 berkas referensi** di `references/`. Perlu dicatat bahwa penamaan modul pada silabus awal berasal dari studi kasus *kantin* sebelum dikonversi penuh menjadi domain farmasi **OmniPharma Network** (sesuai ADR-0006):

| Berkas Referensi / Kurikulum | Ukuran | Modul & Status Implementasi Aktual di OmniPharma |
|---|---|---|
| `modul_praktikum/Pendahuluan.docx` & `Lampiran.docx` | 48.517 B / 57.731 B | Pengantar & lampiran struktur proyek praktikum. |
| `Pertemuan_01_Persiapan_Tool_Development_dan_Konfigurasi_Lingkungan.docx` | 55.281 B | Daffa (`84028e5`) — PHP 8.3+, MariaDB/SQLite, Redis, `.env`, PHPStan, Pint, PHPUnit. |
| `Pertemuan_02_Fondasi_Modular_Monolith_Autentikasi_dan_Layout.docx` | 399.645 B | Daffa (`1b6b0d8`) — `ModuleServiceProvider`, 6 bounded context, `PortalRoutes`, Fortify. |
| `Pertemuan_03_Implementasi_Basis_Data_Migrasi_Model_dan_Seeder.docx` | 426.745 B | Daffa (`1b6b0d8`) — Migrasi `2026_09_21_000101..000105`, 16+ tabel UUID, `DemoApotekSeeder`. |
| `Pertemuan_04_Isolasi_Multi-Tenant_Context_Scope_Policy_dan_Binding.docx` | 53.481 B | Daffa (`a43a02a`, `f88a082`) — `TenantContext`, `TenantScope`, `BelongsToTenant`, `ResolveTenantContext`, `tenant:audit`. |
| `Pertemuan_05_Administrasi_Kantin_Tenant_Role_dan_Skema_Komisi.docx` | 192.206 B | Arya (`4fa5ec1`) — Disesuaikan ke `StaffManagementService`, `StaffController`, & hierarki APJ 5NF (`peta_pengawas_staf`). |
| `Pertemuan_06_Meja_QR_Token_Opaque_dan_Sesi_Pelanggan_Anonim.docx` | 156.276 B | Arya (`7a0f9fe`) — Disesuaikan ke `CustomerSessionService` (`omnipharma_customer_session` 32-char) & multi-cabang. |
| `Pertemuan_07_Katalog_Menu_Tenant_Modifier_Stok_dan_Public_Catalog.docx` | 246.250 B | Arya (`11a3d3b`) + Guntur/Daffa — `PublicCatalog`, `CatalogService`, katalog GoApotik 42 & 2.424 produk, `stok_client`. |
| `Pertemuan_08_Keranjang_Multi-Tenant_di_Redis_dan_Revalidasi_Harga-Stok.docx` | 120.454 B | Guntur (`785f318`) — `CartService` (Redis Hash `cart:{sessionId}:{clientId}` + revalidasi stok cabang). |
| `Pertemuan_09_Checkout_Atomik_Order_Induk_Snapshot_dan_Pre-Order.docx` | 134.038 B | Guntur (`785f318`) — `CreateOrderAction` (`lockForUpdate`, snapshot `item_pesanan` ADR-0008, slot waktu). |
| `Pertemuan_10_Payment_Gateway_Contract_dan_QRIS_Dinamis_Sandbox.docx` | 183.950 B | Guntur (`e3dc7d3`) — `PaymentGatewayInterface`, `FakeQrisGateway` (Midtrans Sandbox + `QrisEmvcoBuilder`). |
| `Pertemuan_11_Webhook_Idempoten_Settlement_Split_Allocation_Ledger_dan_Outbox.docx` | 54.729 B | Guntur (`e3dc7d3`, `69a6d0e`) — `PaymentWebhookController` HMAC/Midtrans + idempotensi + `kasir/ledger` & `admin/kasir-ledger`. |
| `Pertemuan_12_Kitchen_Display_System_Realtime_Status_dan_Notifikasi.docx` | 202.856 B | Arya (telaah resep & FEFO) + **Candia Purwa (M12-RT Reverb `OrderStatusUpdated`, `routes/channels.php`, `order-track.blade.php`)**. |
| `Pertemuan_13_Laporan_Rekonsiliasi_Ekspor_dan_Withdrawal.docx` | 445.539 B | **Candia Purwa (M13)** — `SalesReportService`, `CloseSalesPeriodAction` (ADR-0008), `SalesReportExportService` CSV, `laporan-penjualan*`. |
| `Pertemuan_14_Hardening_Pengujian_End-to-End_Deployment_dan_Demo.docx` | 54.926 B | **Candia Purwa (M14)** — `SecurityHeadersMiddleware`, `RateLimiter`, `UnggahResepRequest`, `VerifikasiResepRequest`, `SecurityHardeningTest`, `EndToEndOrderFlowTest`. |
| `references/Mockup.pdf`, `SRS Apotek.docx`, `srs_apotek_extracted.txt` | 6.7 MB / 3.2 MB / 21 KB | Dokumen desain & kebutuhan acuan utama 4 layar apotek multi-klien OmniPharma. |

---

## Bab 12. Matriks Kesiapan Tim, Risiko Teknis Lintas-Modul, & Rencana Kerja Sinkronisasi

### 12.1 Matriks Kesiapan Lintas-Domain (Baseline `c39e087`)

| Domain / Subsistem | PIC Utama | Bukti Kode & View | Verifikasi Otomatis Terkait | Status Kesiapan |
|---|---|---|---|---|
| **Fondasi Modular Monolith & Multi-Tenancy** | Daffa | `ModuleServiceProvider`, `PortalRoutes`, `TenantContext`, `TenantScope`, `BelongsToTenant`, `ResolveTenantContext`, `tenant:audit` | `ModularFoundationTest` (8), `ModuleConventionTest` (5), `TenantIsolationTest` (15), `ApotekDatabaseConstraintTest` (5), `ApotekFoundationTest` (11) | **Siap Penuh** (Terverifikasi PHPStan L7 & PHPUnit) |
| **Operasional Cabang, Staf APJ 5NF, & FEFO** | Arya | `StaffManagementService`, `StaffController`, `StockFefoService`, `tenant/{antrean,stok-fefo,verifikasi-resep,pasien,bantuan}` | `FefoStockRotationTest` (2), `QueueAndStatusProgressionTest` (6), `PrescriptionVerificationTest` (4), `GuestSessionAndCatalogTest` (4) | **Siap Penuh** |
| **Katalog GoApotik 2.424 Produk & Tanya Apoteker** | Guntur, Daffa, Candi | `CatalogService`, `PublicCatalog`, `GoApotikFullCatalogSeeder`, `catalog-product-card`, `TanyaApotekerModal`, `ApotekerChatPortal` | `GoApotikCatalogIntegrationTest` (10), `UnifiedCatalogAndMultiBranchAvailabilityTest` (8), `TanyaApotekerTest` (21), `ServiceAndCatalogAnimationVerificationTest` (6) | **Siap Penuh** (butuh Redis untuk test cart/redis) |
| **Keranjang, Checkout, QRIS, Kasir POS, & Auth** | Guntur | `CartService`, `CreateOrderAction`, `CancelOrderAction`, `FakeQrisGateway`, `PaymentWebhookController`, `routes/kasir.php`, `EmailOtpAuthController`, `FirebaseAuthSessionController` | `CartCheckoutPaymentTest` (20), `RoleCrudAndKasirPortalTest` (5), `EmailOtpAuthTest` (5), `FirebaseAuthTest` (5) | **Siap Penuh** (butuh Redis aktif untuk `CartService`) |
| **Real-Time Tracking Reverb (M12-RT)** | **Candia Purwa** | `config/reverb.php`, `echo.js`, `OrderStatusUpdated`, `routes/channels.php`, `order-track.blade.php` | `ReverbOrderStatusBroadcastTest` (2), `QueueAndStatusProgressionTest`, `CustomerPrescriptionUploadAndQueueTest` | **Siap Demo** (`OrderStatusUpdated` aktif di 6 titik; `IncomingOrderPlaced` belum di-dispatch) |
| **Analitik Penjualan & Snapshot ADR-0008 (M13)** | **Candia Purwa** | `RingkasanPeriodePenjualan`, `SalesReportService`, `CloseSalesPeriodAction`, `SalesReportExportService`, `laporan-penjualan.blade.php` | `SalesReportAndPeriodLockTest` (3), `ApotekDatabaseConstraintTest` | **Siap Demo untuk CSV & Lock** (PDF hanya template Blade, timestamp lock UI baca boolean) |
| **Security Hardening & Quality Gate (M14)** | **Candia Purwa** | `SecurityHeadersMiddleware`, `AppServiceProvider` limiters, `UnggahResepRequest`, `VerifikasiResepRequest` | `SecurityHardeningTest` (5), `EndToEndOrderFlowTest` (1), PHPStan L7 (0 errors), Pint (pass) | **Siap** (3 named limiters belum di-bind ke route; `EndToEndOrderFlowTest` butuh Redis) |
| **UX Pasca-M14 (Upload Resep, Riwayat, Profil, Slider)** | **Candia Purwa** | `customer/home.blade.php`, `order-history.blade.php`, `User.php`, `ProfileValidationRules`, `⚡profile`, `catalog-product-card`, `welcome.blade.php` | `ProfileUpdateTest` (11), `CartCheckoutPaymentTest`, `GoApotikCatalogIntegrationTest` | **Siap Penuh & Ter-merge di `main`** |

### 12.2 Matriks Ketergantungan Lintas-Modul & Infrastruktur Eksternal

| Bounded Context / Komponen | Model Eloquent yang Diakses | Ketergantungan Modul Lain | Layanan Eksternal / Storage / Cache |
|---|---|---|---|
| **`Admin`** (`StaffController`, `StaffManagementService`) | `Client`, `Pengguna`, `PeranClientPengguna`, `PetaPengawasStaf`, `User` | `TenantContext` (`App\Support`) | MariaDB/SQLite |
| **`Catalog`** (`CatalogService`, `PublicCatalog`, `StockFefoService`, `CustomerSessionService`) | `Client`, `KategoriObat`, `SubkategoriObat`, `Obat`, `StokClient` | `DemoApotekSeeder`, `GoApotikFullCatalogSeeder` | Cache (`clearMetadataCache`), cookie `omnipharma_customer_session`, `storage/framework/cache/goapotik_catalog*`, `public/images/goapotik/` |
| **`Ordering`** (`CartService`, `CartController`, `CheckoutController`, `OrderTrackingController`, `CreateOrderAction`, `CancelOrderAction`, `OrderStatusUpdated`) | `Client`, `Obat`, `StokClient`, `Pengguna`, `Pesanan`, `ItemPesanan`, `Pembayaran`, `Resep` | `Payments` (`PaymentGatewayInterface`, `FakeQrisGateway`), `Catalog` (`CatalogService::clearMetadataCache`) | **Redis** (`CartService` `cart:*`, `Cache::add` idempotensi `checkout_lock:*`, `antrean_berjalan:*`), **Reverb** (`:8080` WebSocket), **Google Maps** (embed/route URL) |
| **`Payments`** (`FakeQrisGateway`, `QrisEmvcoBuilder`, `PaymentWebhookController`) | `Pesanan`, `Pembayaran` | `Ordering` (`OrderStatusUpdated::dispatch`) | **Midtrans Sandbox API** (jika `MIDTRANS_SERVER_KEY` terisi), Cache (`midtrans_order_id:*`), Reverb |
| **`Prescription`** (`VerifyPrescriptionAction`, `routes/tenant.php:344`, `routes/customer.php:116`) | `Pesanan`, `Resep`, `StokClient`, `LogAudit`, `Pengguna` | `Ordering` (`OrderStatusUpdated`), `Catalog` (`CatalogService::clearMetadataCache`) | Disk `public` (`storage/app/public/resep/`), ekstensi PHP `fileinfo` (`finfo_file`) |
| **`Reporting`** (`SalesReportService`, `CloseSalesPeriodAction`, `SalesReportExportService`) | `Client`, `Pesanan`, `ItemPesanan` (via `DB::table`), `RingkasanPeriodePenjualan` | `Ordering` (membaca `pesanan` & `item_pesanan` via `withoutGlobalScopes()`) | Streaming HTTP `php://output` (`StreamedResponse` CSV) |
| **Auth & Konsultasi** (`FirebaseAuthSessionController`, `EmailOtpAuthController`, `TanyaApotekerModal`, `ApotekerChatPortal`) | `User`, `Pengguna`, `Client`, `Konsultasi`, `PesanKonsultasi` | `Catalog` (`CustomerSessionService`) | **Firebase Auth Web SDK** (`firebase-auth.js`), Cache OTP, Mailer (`log`/SMTP), Disk `public` lampiran chat, `wire:poll` |

### 12.3 Risiko Teknis Lintas-Modul yang Wajib Diperhatikan Tim

1. **Ketergantungan Keras `CartService` terhadap Server Redis Lokal saat Pengujian (`EndToEndOrderFlowTest` & `CartCheckoutPaymentTest`):**
   - `phpunit.xml` menyetel `CACHE_STORE=array` dan `SESSION_DRIVER=array`, tetapi `CartService` memanggil facade `Redis::hincrby()` / `Redis::hgetall()` secara langsung tanpa abstraksi driver cache. Akibatnya, menjalankan `php vendor/bin/phpunit` penuh di laptop pengembang yang lupa menyalakan service Redis (`127.0.0.1:6379`) akan memicu kegagalan *connection refused* pada suite yang menyentuh keranjang. Solusi operasional: selalu nyalakan Redis lokal sebelum menjalankan test penuh, atau jalankan via CI Docker (`mariadb:11.4` + `redis:7`).
2. **Ketergantungan `php artisan tenant:audit` dan Demo Runtime terhadap MariaDB Lokal:**
   - Perintah Artisan di luar PHPUnit membaca `.env` (`mysql` di `127.0.0.1:3306`), sehingga MariaDB harus berjalan sebelum `php artisan migrate:fresh --seed` atau `php artisan tenant:audit`.
3. **Divergensi Branch Lokal `main` dan Branch Open PR Lama:**
   - Branch lokal `main` berada di `198e118` (tertinggal 24 commit di belakang `origin/main` `c39e087`), sementara `feat/candi-upload-foto-resep` sudah berada di `c39e087` (`[ahead 2]` terhadap `origin/feat/candi-upload-foto-resep` `bb079a1`). Selain itu, 3 open PR (#1, #2, #8) masih menggantung di GitHub dan perlu dibereskan agar antrean PR bersih.
4. **Celah Halus Keamanan & UX yang Terdeteksi pada Audit Kodebase:**
   - Belum terpasangnya `throttle:api` dan `throttle:transaksi` ke rute publik/checkout/upload resep serta `throttle:broadcasting`.
   - Bypass global `$user->hasRole('apoteker')` di `routes/channels.php:36` pada channel `tenant.{id_client}`.
   - CSP `frame-src` (`SecurityHeadersMiddleware.php:51`) belum mencantumkan `https://maps.google.com` untuk iframe peta `order-track.blade.php:237`.
   - `branch.switch` (`routes/web.php:78-80`) menerima parameter `redirect_to` tanpa validasi *same-origin* / path lokal.
   - `User::homeRoute()` untuk kasir mengarah ke `/kasir/dashboard`, sedangkan dokumen lama (`AKUN_LOGIN.md` & `PANDUAN_PRESENTASI`) masih menyebut `/admin/kasir-ledger`.

### 12.4 Rencana Kerja Sinkronisasi Berikutnya (Untuk Candi & Seluruh Tim)

1. **Sinkronisasi Git & Kebersihan Branch/PR:**
   - Tetapkan `git config --local user.name "candiapurwa-sudo"` di `D:\projek-pdevit\startup` agar commit baru tidak tercatat sebagai `unknown`.
   - Sinkronkan berkas panduan `tugascandi.md` pada branch aktif `feat/candi-upload-foto-resep` ke `https://github.com/heliosparta18/startup.git` (`origin`) dan buat PR atau fast-forward `main` setelah review tim.
   - Tinjau penutupan PR #2 & PR #8 yang sudah terintegrasi ke `main`, serta evaluasi PR #1 (Dependabot).
2. **Sprint Micro-Hardening (Khusus Candi, Estimasi < 1 Jam):**
   - Pasang middleware `throttle:transaksi` pada `resep.upload` & `checkout.process`, serta `throttle:api` pada `api.search.obat` & `webhooks.qris`; perbaiki key limiter di `AppServiceProvider.php:41,51`.
   - Perketat otorisasi `tenant.{id_client}` di `routes/channels.php:35-53` berdasarkan keanggotaan cabang aktual.
   - Tambahkan `https://maps.google.com` ke `frame-src` di `SecurityHeadersMiddleware.php:51` dan validasi path lokal `redirect_to` di `routes/web.php:78-80`.
   - Ubah `routes/tenant.php:531-535` dari `->exists()` ke `->first()` agar timestamp penguncian pada `admin/laporan-penjualan.blade.php:74` akurat, dan lengkapi endpoint cetak/unduh untuk `admin/pdf/laporan-penjualan-template.blade.php`.
   - Aktifkan dispatch `IncomingOrderPlaced` (setelah menyelaraskan properti `status` & `resep()->exists()`) bila notifikasi pesanan masuk real-time pada dasbor apoteker akan didemonstrasikan.
3. **Sinkronisasi Dokumentasi Tim & Verifikasi Akhir Sebelum Sidang:**
   - Perbarui angka baseline di `README.md`, `PANDUAN_PRESENTASI_DAN_ROADMAP_OMNIPHARMA.md`, `AKUN_LOGIN.md`, dan `docs/REQUIREMENTS_TRACEABILITY.md` agar seragam dengan baseline `c39e087` (`20` model, `20` migrasi, `37` file / `240` test methods, `2.424` produk GoApotik, serta portal Kasir `/kasir/dashboard`).
   - Pastikan Redis (`6379`), MariaDB (`3306`), dan Reverb (`8080`) aktif di laptop presentasi utama lalu jalankan `composer ci:check` (`pint`, `phpstan`, `php artisan test`) dan `php artisan tenant:audit` hingga seluruh indikator hijau 100%.

### 12.5 Checklist Verifikasi Teknis Pra-Presentasi Lintas-Tim

Gunakan daftar periksa deterministik berikut sebelum memulai demo atau *merge* cabang ke `main`:

| Langkah Verifikasi | Perintah / Aksi Eksekusi | Kriteria Lulus Terverifikasi | Penanggung Jawab |
|---|---|---|---|
| **1. Static Analysis (Level 7)** | `php -d memory_limit=1G vendor/bin/phpstan analyse --no-progress` | Exit code `0`, `0 errors` pada seluruh `app/`, `config/`, `database/`, `routes/`. | Seluruh Tim |
| **2. Code Style PSR-12** | `php vendor/bin/pint --test` | Exit code `0`, `PASS` sesuai preset `laravel` (`pint.json`). | Seluruh Tim |
| **3. Core Test Suite (SQLite)** | `php vendor/bin/phpunit tests/Feature/ReverbOrderStatusBroadcastTest.php tests/Feature/SalesReportAndPeriodLockTest.php tests/Feature/SecurityHardeningTest.php tests/Feature/CustomerPrescriptionUploadAndQueueTest.php tests/Feature/TenantIsolationTest.php tests/Feature/ModularFoundationTest.php tests/Feature/ModuleConventionTest.php` | `53 tests, 53 passed, 356 assertions` (terverifikasi 26 Nov 2026). | **Candia Purwa** & Daffa |
| **4. Full Test Suite (Butuh Redis)** | Nyalakan daemon Redis `127.0.0.1:6379`, lalu `php vendor/bin/phpunit` | Seluruh **37 file / 240 test methods** lulus termasuk `EndToEndOrderFlowTest` & `CartCheckoutPaymentTest`. | Guntur & **Candia Purwa** |
| **5. Audit Multi-Tenant (Butuh MariaDB)** | Nyalakan MariaDB `127.0.0.1:3306`, lalu `php artisan migrate:fresh --seed && php artisan tenant:audit` | Status `TERISOLASI 100%` (0 `NULL id_client`, 0 `Unregistered Client`). | Daffa & Arya |
| **6. WebSocket Reverb & Frontend Build** | `npm run build` dan `php artisan reverb:start --host=0.0.0.0 --port=8080` | `public/build/manifest.json` mutakhir & handshake `ws://127.0.0.1:8080` sukses di `order-track`. | **Candia Purwa** |
| **7. Git Remote & Hygiene** | `git status --short` & `git remote -v` | Working tree bersih dari `.env`/`storage/`, remote `origin` ke `https://github.com/heliosparta18/startup.git`. | Seluruh Tim |

---
*Dokumen teknis ini disusun berdasarkan inspeksi langsung terhadap kode sumber, migrasi basis data, berkas pengujian, dokumen arsitektur/PRD, dan riwayat git pada `D:\projek-pdevit\startup` (baseline `c39e087`).*
