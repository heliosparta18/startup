# Catatan dan Logbook Tugas — Muhammad Daffa Ariby

> **Proyek:** OmniPharma Network — Sistem Informasi Apotek Multi-Klien  
> **Repositori:** [github.com/heliosparta18/startup](https://github.com/heliosparta18/startup)  
> **Identitas Pengembang:** Muhammad Daffa Ariby (`heliosparta18` / `heliosparta18@gmail.com`)  
> **Peran & Fokus:** Lead Architect & Core Multi-Tenancy Kernel  
> **Total Kontribusi:** 28 Commit lintas 5 branch (`main`, `feature/pertemuan-04-multi-tenant`, `feat/daffa-security-e2e`, `ui_prototype`, `daffa-pertemuan1-3`)

---

## 1. Ringkasan Eksekutif

Sebagai Lead Architect dan pengembang Core Multi-Tenancy pada proyek OmniPharma Network, tanggung jawab utama mencakup pembangunan fondasi modular monolith, perancangan skema database domain apotek (16 tabel PDM), perancangan arsitektur isolasi multi-tenant otomatis (scope, context, middleware, domain exceptions, audit command), penguatan keamanan Content Security Policy (CSP), perbaikan integrasi CI/CD, hingga navigasi dua arah antar portal.

---

## 2. Rincian Implementasi Berdasarkan Modul

### A. Fondasi Arsitektur Modular Monolith & Setup Toolchain (Pertemuan 1 – 3)
* **Setup Toolchain:** Menginisialisasi Laravel 13, MariaDB 11, Redis 7, Tailwind CSS v4, Livewire 4, PHPStan Level 7, dan Laravel Pint.
* **Modular Monolith Engine:**
  * Merancang arsitektur 6 *bounded context*: `Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, dan `Reporting` di direktori `app/Modules/`.
  * Membangun `app/Modules/ModuleServiceProvider.php` untuk memuat routing, view, dan service provider masing-masing modul secara terisolasi.
* **Skema Basis Data Domain (16 Tabel PDM):**
  * Membuat migrasi dan model Eloquent inti: `Client`, `Obat`, `KategoriObat`, `StokClient`, `Pesanan`, `ItemPesanan`, `Pembayaran`, `Resep`, `PermintaanRetur`, `LogAudit`, `PetaPengawasStaf`, `RingkasanPeriodePenjualan`, dll.
* **Layout Multi-Portal:**
  * Mengembangkan template Blade dasar untuk 3 portal: Admin (`layouts/admin.blade.php`), Tenant/Cabang (`layouts/tenant.blade.php`), dan Pelanggan (`layouts/customer.blade.php`).
  * Konfigurasi rute independen di `routes/admin.php`, `routes/tenant.php`, dan `routes/customer.php`.
* **Testing Fondasi:** Menulis test suite konvensi arsitektur dan relasi integritas PDM di `ApotekDatabaseConstraintTest` dan `ModuleConventionTest`.

### B. Core Multi-Tenancy Kernel & Mitigasi IDOR (Pertemuan 4)
* **Manajemen State Tenant:**
  * Membuat kontrak `TenantManagerInterface` dan implementasi `app/Support/Tenant/TenantContext.php` untuk mencatat identitas cabang aktif per request.
* **Middleware Resolusi Cabang:**
  * Membuat `app/Http/Middleware/ResolveTenantContext.php` yang membaca slug parameter URL `/klien/{client}/...` dan memvalidasi keaktifan apotek cabang.
* **Domain Events & Exceptions:**
  * Exception: `TenantNotFoundException`, `TenantInactiveException`, `TenantAccessDeniedException`, `TenantContextMissingException`.
  * Event: `TenantResolved`, `TenantSwitched`.
* **Global Scope & Trait Otomatis:**
  * Membuat global scope `app/Models/Scopes/TenantScope.php` yang menyuntikkan klausa `WHERE id_client = ?` pada model transaksi.
  * Membuat trait `app/Models/Traits/BelongsToTenant.php` untuk binding otomatis event `creating` dan relasi ke `Client`.
* **Otorisasi IDOR (Insecure Direct Object Reference):**
  * Menerapkan `app/Policies/PesananPolicy.php` guna memastikan apoteker atau staf cabang A tidak dapat mengintip maupun mengelola transaksi cabang B.
* **Artisan Audit Command:**
  * Mengembangkan perintah CLI `app/Console/Commands/AuditTenantIsolationCommand.php` (`php artisan tenant:audit`) untuk mendeteksi potensi kebocoran data antar-cabang.
* **Testing Multi-Tenant:**
  * Menulis `tests/Feature/TenantIsolationTest.php` untuk memvalidasi isolasi query, exception handling, dan pencegahan IDOR.

### C. Keamanan CSP, Dev Server & Asset Pipeline
* **CSP & Vite Dev Server:**
  * Memodifikasi `SecurityHeadersMiddleware.php` agar Content Security Policy (CSP) lokal dapat menerima websocket HMR dan script injector Vite Dev Server tanpa mengorbankan header keamanan di mode produksi.
* **Kompilasi Aset Produksi:**
  * Melakukan build dan rilis aset `manifest.json`, CSS, dan JS bundle terkompilasi.
* **Git Hygiene:**
  * Memperbarui `.gitignore` untuk mengabaikan direktori skill dan AI tools internal.
  * Mengonfigurasi `.gitattributes` untuk deteksi bahasa Linguist pada file referensi.

### D. Prototipe Antarmuka & Role Switcher Demo
* **Login Multi-Peran Terpadu:**
  * Mengembangkan prototipe antarmuka login OmniPharma di `resources/views/pages/auth/login.blade.php`.
  * Menyematkan fitur *Demo Role Switcher* (Super Admin, Apoteker Cabang, dan Pasien) untuk mempermudah alur demonstrasi praktikum.

### E. Integrasi CI/CD, Reactivity Keranjang & Perbaikan Auth
* **Penanganan PHPStan Level 7:**
  * Menyelesaikan issue type-safety pada `EmailOtpAuthController`, `FirebaseAuthSessionController`, dan model `RingkasanPeriodePenjualan`.
* **Penanganan Route Default Laravel:**
  * Mengonfigurasi `FortifyServiceProvider` dan `routes/web.php` agar pengguna tidak diarahkan ke tampilan bawaan Laravel saat mengakses sistem.
* **Reaktivitas Keranjang Belanja:**
  * Memperbaiki interaktivitas penambahan dan pengurangan stok obat pada `customer/cart.blade.php` dan `customer/obat-detail.blade.php`.
* **Fix Testing Environment:**
  * Menyinkronkan kredensial testing di `phpunit.xml` dan menginisialisasi stok dummy pada `CartCheckoutPaymentTest.php`.

### F. Navigasi Dua Arah Lintas Portal (Bidirectional Navigation)
* **Penghubung Portal Utama:**
  * Menambahkan tombol dan link "Dashboard Utama" dua arah di layout Admin, Tenant, dan Pelanggan.
  * Memasang link navigasi pada seluruh alur transaksi: halaman detail obat, keranjang belanja, checkout, pembayaran, tracking pesanan, hingga verifikasi resep apoteker.
* **Verifikasi Navigasi:**
  * Membuat automated test di `tests/Feature/DashboardTest.php` untuk memvalidasi ketersediaan navigasi antar-peran.

### G. Audit Dokumen, Traceability & Pembersihan Berkas Legacy
* **Pembersihan Berkas Non-SRS:**
  * Menghapus file sisa materi lama non-farmasi: `IMPLEMENTATION_MATRIX.md`, `IMPLEMENTATION_STATUS.md`, `MODULE_REVISION_LOG.md`, direktori `evidence/`, dan catatan referensi usang.
* **Requirements Traceability Matrix (RTM):**
  * Memperbarui total `docs/REQUIREMENTS_TRACEABILITY.md` agar 100% selaras dengan kebutuhan SRS Apotek (FR-PEL, FR-APT, FR-ADM).
* **Dokumentasi Audit Perbaikan:**
  * Menyusun dokumen komprehensif `perbaikan.md` yang merinci hasil audit kepatuhan SRS, panduan bugfix front-end, matriks sisa backlog anggota, dan SOP quality gate.

---

## 3. Matriks Riwayat 27 Commit

| No | Hash | Tanggal | Branch Terkait | Pesan Commit | Ringkasan Berkas Diubah |
| :-: | :---: | :---: | :---: | :--- | :--- |
| 1 | `84028e5` | 30-10-2026 | `main` | `startup` | Inisialisasi awal repositori proyek (142 file). |
| 2 | `fb838a4` | 30-10-2026 | `main` | `Update README.md` | Pembaruan dokumentasi awal README. |
| 3 | `88b36c2` | 30-10-2026 | `main` | `Update README.md` | Penyesuaian deskripsi proyek apotek multi-klien. |
| 4 | `1b6b0d8` | 30-10-2026 | `main`, `daffa-pertemuan1-3` | `feat: complete Pertemuan 1 s/d 3 Apotek Multi-Klien implementation` | Setup modular monolith (6 module), 16 model PDM, layout portal, dan test suite (201 file). |
| 5 | `866e6bf` | 03-11-2026 | `feature/pertemuan-04-multi-tenant` | `bikin tenant context dan middleware resolusi tenant dari parameter url` | Implementasi `TenantContext` dan middleware `ResolveTenantContext`. |
| 6 | `ced8b60` | 03-11-2026 | `feature/pertemuan-04-multi-tenant` | `tambah tenant scope global dan trait belongs to tenant di model transaksi` | Menambahkan `TenantScope` dan trait `BelongsToTenant` pada model pesanan. |
| 7 | `3104b0a` | 03-11-2026 | `feature/pertemuan-04-multi-tenant` | `pasang policy otorisasi cegah apoteker intip transaksi cabang lain` | Menambahkan `PesananPolicy` untuk memblokir IDOR lintas cabang. |
| 8 | `30338c3` | 03-11-2026 | `feature/pertemuan-04-multi-tenant` | `tambah unit test isolasi tenant dan cegah idor antar cabang` | Menulis pengujian unit dan fitur pada `TenantIsolationTest.php`. |
| 9 | `a43a02a` | 03-11-2026 | `main` | `Merge pull request #4 from heliosparta18/feature/pertemuan-04-multi-tenant` | Penggabungan implementasi Core Multi-Tenant ke branch utama. |
| 10 | `b751c0d` | 03-11-2026 | `main` | `fix : benerin tipe phpstan dan format kode tenant scope` | Koreksi anotasi tipe PHPStan pada `TenantScope.php`. |
| 11 | `e77670b` | 03-11-2026 | `main` | `fix: bereskan sisa error linter dan tipe di middleware serta trait` | Standarisasi PSR-12 dan perbaikan tipe di middleware tenant. |
| 12 | `db0191f` | 03-11-2026 | `main` | `fix: tambah properti status pada model tenant` | Menambahkan atribut status pada entitas tenant. |
| 13 | `6d2e8b5` | 07-11-2026 | `main` | `Update README.md` | Sinkronisasi status progres integrasi multi-tenant. |
| 14 | `cf88c83` | 07-11-2026 | `feat/daffa-security-e2e` | `fix(tests): perbaiki kredensial testing di phpunit.xml, format rute, dan inisialisasi stok cabang untuk pencegahan error transaksi` | Konfigurasi DB test di `phpunit.xml` dan seed stok di `CartCheckoutPaymentTest`. |
| 15 | `f88a082` | 07-11-2026 | `feat/daffa-security-e2e` | `daffa` | Menambahkan `AuditTenantIsolationCommand`, domain events/exceptions, PRD, dan roadmap proyek. |
| 16 | `96d727e` | 08-11-2026 | `ui_prototype` | `feat(ui): implementasi antarmuka prototipe login OmniPharma multi-klien dengan role switcher demo` | Pembuatan UI login interaktif dan role switcher demo. |
| 17 | `3739687` | 08-11-2026 | `ui_prototype` | `prototype sementara` | Penyesuaian responsivitas pada prototipe antarmuka login. |
| 18 | `1b18bb9` | 09-11-2026 | `main` | `fix: konfigurasi gitattributes binary linguist dan keluarkan pdf referensi dari git tracking` | Pembersihan git tracking untuk file PDF besar dan konfigurasi Linguist. |
| 19 | `9787664` | 09-11-2026 | `main` | `Delete evidence directory` | Penghapusan 41 file evidence pengujian sementara. |
| 20 | `2e998e0` | 09-11-2026 | `main` | `Delete references/srs_apotek_text.txt` | Pembersihan dump teks referensi yang tidak terpakai. |
| 21 | `99ce4d5` | 09-11-2026 | `main` | `Delete references/pdm_revisi_text.txt` | Pembersihan berkas teks PDM lama. |
| 22 | `dd88804` | 09-11-2026 | `main` | `Delete references/mockup_text.txt` | Pembersihan berkas teks mockup lama. |
| 23 | `20ba36e` | 09-11-2026 | `main` | `fix(security,build): dukung vite dev server di csp lokal dan mutakhirkan aset build produksi` | Pembaruan CSP header di middleware dan rebuild bundle frontend. |
| 24 | `32a5471` | 09-11-2026 | `main` | `build: abaikan direktori skills dan konfigurasi ai pada gitignore` | Penyesuaian aturan ignore pada `.gitignore`. |
| 25 | `1845248` | 09-11-2026 | `main` | `fix(ci,ui): resolve CI phpstan type errors, prevent redirect to default laravel page, and make cart reactive` | Perbaikan PHPStan level 7, Fortify redirect, dan reaktivitas keranjang belanja. |
| 26 | `36a15d8` | 09-11-2026 | `main` | `feat(navigation): add bidirectional Dashboard Utama navigation across customer, tenant, and admin portals` | Implementasi navigasi dua arah antar-portal di 21 view dan test dashboard. |
| 27 | `3f7a5b8` | 09-11-2026 | `main` | `penghapusan file tidak penting` | Hapus file non-SRS kantin, perbarui RTM resmi, dan rilis panduan `perbaikan.md`. |
| 28 | `ce07bcc` | 10-11-2026 | `main` | `hapus sampah kantin` | Pembersihan 14 model, 9 factory/seeder warisan kantin, serta penghapusan pengujian redundan. |

---

## 4. Berkas Utama yang Diimplementasikan

Berikut daftar berkas kunci yang dibuat atau dikembangkan oleh Muhammad Daffa Ariby:

* **Arsitektur Modular:**
  * `app/Modules/ModuleServiceProvider.php`
* **Multi-Tenancy Kernel:**
  * `app/Support/Tenant/TenantContext.php`
  * `app/Support/Tenant/Contracts/TenantManagerInterface.php`
  * `app/Support/Tenant/Events/TenantResolved.php`
  * `app/Support/Tenant/Events/TenantSwitched.php`
  * `app/Support/Tenant/Exceptions/TenantNotFoundException.php`
  * `app/Support/Tenant/Exceptions/TenantInactiveException.php`
  * `app/Support/Tenant/Exceptions/TenantAccessDeniedException.php`
  * `app/Support/Tenant/Exceptions/TenantContextMissingException.php`
  * `app/Http/Middleware/ResolveTenantContext.php`
  * `app/Models/Scopes/TenantScope.php`
  * `app/Models/Traits/BelongsToTenant.php`
  * `app/Policies/PesananPolicy.php`
  * `app/Console/Commands/AuditTenantIsolationCommand.php`
* **Pengujian Otomatis:**
  * `tests/Feature/TenantIsolationTest.php`
  * `tests/Feature/DashboardTest.php`
  * `tests/Feature/ApotekFoundationTest.php`
  * `tests/Feature/ApotekDatabaseConstraintTest.php`
  * `tests/Feature/ModuleConventionTest.php`
* **Dokumentasi & Audit:**
  * `perbaikan.md`
  * `docs/REQUIREMENTS_TRACEABILITY.md`
  * `docs/PRD_DAFFA.md`
