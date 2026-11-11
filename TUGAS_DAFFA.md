# Logbook dan Rekapitulasi Tugas Tim Pengembang — OmniPharma Network

> **Proyek:** OmniPharma Network — Sistem Informasi Apotek Multi-Klien  
> **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi  
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Repositori GitHub:** [github.com/heliosparta18/startup](https://github.com/heliosparta18/startup)  
> **Basis Teknologi:** Laravel 13.25, Livewire 4.4, Tailwind CSS v4, Laravel Reverb (WebSockets), MariaDB 11.4, Redis 7.2  
> **Status Integrasi:** **100% Terintegrasi di branch `main`** | 120+ Automated Tests Passed | PHPStan Level 7 Passed | Pint PSR-12 Passed  
> **Total Kontribusi:** 100 Commit lintas seluruh anggota tim

---

## 1. Ringkasan Eksekutif & Struktur Tim (4 - 4 - 4 - 3)

Sistem OmniPharma Network dikembangkan secara kolaboratif oleh 4 pengembang dengan pembagian modul dan tanggung jawab fungsional yang saling terisolasi (*Separation of Concerns*) berbasis arsitektur **Modular Monolith**:

| Nama Pengembang | Akun GitHub | Peran & Alokasi Domain | Modul Praktikum | Jumlah Commit |
| :--- | :--- | :--- | :---: | :---: |
| **Muhammad Daffa Ariby** | `heliosparta18` | **Lead Architect & Core Multi-Tenancy Kernel** | M01, M02, M03, M04 | **28 Commit** |
| **Guntur Afandi** | `Callme Zettx` / `callmezext` | **Commerce Pipeline, QRIS Gateway & Auth Lead** | M-UI, M08, M09, M10, M11 | **39 Commit** |
| **Arya Dwi Mahendra** | `Miinggg67` | **Branch Operations, Guest Session & FEFO Lead** | M05, M06, M07, M12 | **10 Commit** |
| **Candia Purwa** | `candia.purwa` | **Real-Time WebSockets, Analytics & Security Lead**| M12-RT, M13, M14 | **22 Commit** |
| *Dependabot* | `dependabot[bot]` | *Automated Dependency Security Updates* | CI/CD | 1 Commit |

---

## 2. Rincian Pekerjaan Komprehensif per Anggota Tim

---

### A. Muhammad Daffa Ariby (`heliosparta18`)
**Peran:** Lead Architect & Core Multi-Tenancy Kernel  
**Alokasi Tugas:** Modul 01 s/d Modul 04 + Penguatan Navigasi & Pembersihan Warisan

#### 1. Fondasi Arsitektur Modular Monolith & Setup Toolchain (M01 – M03)
* **Inisialisasi Toolchain:** Konfigurasi awal Laravel 13, MariaDB 11, Redis 7, Tailwind CSS v4, Livewire 4, PHPStan Level 7, dan Laravel Pint (`84028e5`, `1b6b0d8`).
* **Modular Monolith Engine:**
  * Merancang arsitektur 6 *bounded context*: `Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, dan `Reporting` di bawah direktori `app/Modules/`.
  * Membangun `app/Modules/ModuleServiceProvider.php` untuk memuat routing, view, dan service provider masing-masing modul secara terisolasi.
* **Skema Basis Data Domain (16 Tabel PDM):**
  * Merancang migrasi dan model Eloquent berbasis UUID: `Client`, `Obat`, `KategoriObat`, `StokClient`, `Pesanan`, `ItemPesanan`, `Pembayaran`, `Resep`, `PermintaanRetur`, `LogAudit`, `PetaPengawasStaf`, `RingkasanPeriodePenjualan`, dll.
* **Layout Multi-Portal:**
  * Membangun template Blade dasar untuk 3 portal: Admin (`layouts/admin.blade.php`), Tenant/Cabang (`layouts/tenant.blade.php`), dan Pelanggan (`layouts/customer.blade.php`).
* **Pengujian Fondasi:**
  * Menulis test suite konvensi arsitektur dan relasi integritas PDM di `ApotekDatabaseConstraintTest` dan `ModuleConventionTest`.

#### 2. Core Multi-Tenancy Kernel & Mitigasi IDOR (M04 / PR #4)
* **Manajemen State Tenant:**
  * Membuat kontrak `TenantManagerInterface` dan implementasi `app/Support/Tenant/TenantContext.php` untuk melacak cabang aktif per siklus request.
* **Middleware Resolusi Cabang:**
  * Membuat `app/Http/Middleware/ResolveTenantContext.php` yang membaca slug parameter URL `/klien/{client}/...` dan memvalidasi keaktifan cabang.
* **Domain Events & Exceptions:**
  * Exceptions: `TenantNotFoundException`, `TenantInactiveException`, `TenantAccessDeniedException`, `TenantContextMissingException`.
  * Events: `TenantResolved`, `TenantSwitched`.
* **Global Scope & Trait Otomatis:**
  * Membuat global scope `app/Models/Scopes/TenantScope.php` yang menyuntikkan klausa `WHERE id_client = ?` secara otomatis pada kueri model transaksi.
  * Membuat trait `app/Models/Traits/BelongsToTenant.php` untuk auto-binding `id_client` saat model dibuat.
* **Otorisasi IDOR (Insecure Direct Object Reference):**
  * Menerapkan `app/Policies/PesananPolicy.php` guna memastikan apoteker atau staf cabang A tidak dapat mengintip maupun mengelola transaksi cabang B.
* **Artisan Audit Command:**
  * Mengembangkan perintah CLI `app/Console/Commands/AuditTenantIsolationCommand.php` (`php artisan tenant:audit`) untuk mendeteksi potensi kebocoran data antar-cabang.
* **Testing Multi-Tenant:**
  * Menulis `tests/Feature/TenantIsolationTest.php` untuk memvalidasi isolasi query, exception handling, dan pencegahan IDOR.

#### 3. Keamanan CSP, Navigasi Multi-Portal & Pembersihan Sistem
* **CSP & Vite Dev Server:** Memodifikasi `SecurityHeadersMiddleware.php` agar Content Security Policy (CSP) mengizinkan Vite HMR di lokal tanpa mengurangi keamanan produksi (`20ba36e`).
* **Prototipe Demo Role Switcher:** Membangun antarmuka login demo OmniPharma multi-klien dengan switcher peran instan (`96d727e`, `3739687`).
* **Navigasi Dua Arah Portal:** Mengimplementasikan tombol dan alur navigasi "Dashboard Utama" dua arah di 21 tampilan layout dan view transaksi (`36a15d8`).
* **Fix CI/CD & Reactivity:** Menyelesaikan error tipe PHPStan level 7 pada auth controller, mencegah redirect bawaan Laravel di Fortify, dan memperbaiki reaktivitas keranjang belanja (`1845248`).
* **Pembersihan Warisan & RTM:**
  * Menghapus file markdown sisa materi kantin lama di commit `3f7a5b8` dan menerbitkan panduan `perbaikan.md`.
  * Menghapus 14 model zombie kantin, 9 factory/seeder kantin, dan file redundan di commit `3021cc8` (`hapus sampah kantin`).

---

### B. Guntur Afandi (`Callme Zettx` / `callmezext`)
**Peran:** Commerce Pipeline, Payment Gateway QRIS & Authentication Lead  
**Alokasi Tugas:** Modul UI, Modul 08, Modul 09, Modul 10, Modul 11 + Autentikasi Firebase & Landing Page

#### 1. Autentikasi Modern: Firebase, Google Sign-In & Email OTP
* **Firebase Auth & Slider UI:** Mengimplementasikan form login/register sliding interaktif (Diprella double-slider) terintegrasi Firebase Authentication (`6d7e4ae`, `8f4cb34`, `65da189`).
* **Verifikasi Email OTP:** Membangun alur verifikasi kode OTP berbasis email saat pendaftaran akun dan login (`5d02438`, `b166add`).
* **Google 1-Click Sign-In:** Integrasi login instan akun Google beserta alur onboarding pengisian data profil pasien pertama kali (`5d02438`).
* **Demo 1-Click Login:** Menyediakan tombol masuk demo instan (Super Admin, Apoteker, Pasien) beserta seeder akun pengujian (`db2dca5`).
* **Proteksi Brute Force:** Memasang rate limiting ketat pada request OTP dan autentikasi di `AppServiceProvider` (`d55163c`).

#### 2. Keranjang Belanja Redis & Checkout Atomik (M08 & M09 / PR #5)
* **Keranjang Belanja Multi-Tenant di Redis:**
  * Membangun hash in-memory Redis `cart:{sessionId}:{clientId}` dengan TTL 7 hari (`785f318`).
  * Menyertakan mekanisme revalidasi ketersediaan stok fisik obat cabang sebelum transaksi (`785f318`).
* **Checkout Bebas Race-Condition (Atomik):**
  * Menggunakan database transaction (`DB::transaction`) dipadukan dengan pesimistik locking (`lockForUpdate`) untuk mencegah pembelian ganda / *overselling* obat (`785f318`).
  * Denormalisasi harga satuan pada `item_pesanan` (ADR-0008) agar perubahan master obat tidak mempengaruhi riwayat nota transaksi.
  * Pembuatan format kode pesanan otomatis `#ORD-YYYYMMDD-XXXX`.
* **Sesi Tamu & Token:** Menambahkan migrasi kolom `token_sesi` pada tabel `pesanan` untuk mendukung pelacakan pesanan tanpa akun login (`d3bb1b7`).

#### 3. Payment Gateway Dynamic QRIS Sandbox & Webhook Auto-Settlement (M10 & M11 / PR #5)
* **Dynamic QRIS Sandbox:**
  * Merancang interface `PaymentGatewayInterface` dan kelas implementasi `FakeQrisGateway` (`e3dc7d3`).
  * Membangun generator string QRIS standar EMVCo (NMID, Merchant Category Code 5912 Apotek, CRC16 CCITT) lengkap dengan QR Code visual dan timer countdown 15 menit (`e3dc7d3`).
* **Webhook Idempoten & Auto-Settlement:**
  * Membangun endpoint `POST /api/webhooks/qris` yang memvalidasi integritas pesan melalui signature HMAC-SHA256 (`e3dc7d3`).
  * Mengubah status pesanan secara otomatis menjadi `dibayar` dan mencatat mutasi ledger keuangan cabang (`d3bb1b7`).
  * Mekanisme otomatis pengembalian / restorasi stok fisik apotek bila pesanan kedaluwarsa atau dibatalkan (`9933b8d`).

#### 4. Overhaul Mobile Responsiveness, Controller Modular & Landing Page
* **Mobile Responsiveness (Anti-Slop):** Melakukan audit dan overhaul mobile di seluruh 10 tampilan portal apotek; memastikan touch-target minimal 44px dan bebas horizontal overflow (`6ef04e2`, `abc6300`, `8c4534c`).
* **Welcoming Landing Page:** Membangun landing page profesional di root URL (`/`) lengkap dengan panduan pencarian obat, portal gateway, dan logo resmi farmasi (`19efcbe`, `3080dc0`, `36d514c`, `c1f74f7`).
* **Refactoring Modular Ordering:** Memecah closure rute pelanggan menjadi controller terstruktur: `CartController`, `CheckoutController`, dan `OrderTrackingController` (`af062a4`).
* **Dokumentasi & Backlog:** Menyusun dokumen roadmap tim, inventaris 22 artboard FigCraft di `PRD_DESIGN_AND_RULES.md`, serta panduan backlog di `README_TUGAS_ANGGOTA_DAN_BACKLOG.md` (`3aa626f`, `d5e3f19`, `dbf6635`).

---

### C. Arya Dwi Mahendra (`Miinggg67`)
**Peran:** Branch Operations, Guest Session & FEFO Inventory Lead  
**Alokasi Tugas:** Modul 05, Modul 06, Modul 07, Modul 12 (Operasional Cabang)

#### 1. Administrasi Staf Cabang & Relasi APJ Derajat 5NF (M05 / PR #6 & PR #7)
* **Manajemen Staf Berbasis Peran (RBAC):**
  * Membangun `StaffManagementService` untuk administrasi penugasan apoteker, kasir, dan staf gudang per cabang apotek (`4fa5ec1`).
* **Normalisasi Derajat Kelima (5NF):**
  * Mengimplementasikan tabel `peta_pengawas_staf` yang memetakan hubungan non-trivial antara Apoteker Penanggung Jawab (APJ), staf cabang, dan lisensi farmasi secara matematis bebas anomali (`4fa5ec1`).

#### 2. Sesi Tamu Opaque Token Cookie (M06 / PR #6)
* **Opaque Token Guest Session:**
  * Membangun mekanisme sesi belanja pasien tanpa mewajibkan login di awal menggunakan token buram (*opaque token* 32 karakter acak via cookie terenkripsi) (`7a0f9fe`).
* **Branch Auto-Detection:**
  * Mengembangkan `CustomerSessionService` untuk mendeteksi cabang apotek terdekat atau cabang yang dipilih pelanggan dalam sesi belanja (`7a0f9fe`).

#### 3. Katalog Obat Interaktif Livewire & Filter Golongan (M07 / PR #6)
* **Komponen Livewire Public Catalog:**
  * Membangun komponen reaktif `PublicCatalog` untuk browsing katalog obat multi-cabang secara instan tanpa reload halaman (`11a3d3b`).
* **Pencarian & Filter Golongan Obat:**
  * Menyediakan fitur live search nama obat dan indikasi.
  * Menyediakan filter kategori golongan regulasi farmasi: Obat Bebas (lingkaran hijau), Bebas Terbatas (lingkaran biru), Obat Keras (lingkaran merah huruf K), dan Obat Wajib Apotek / Narkotika (`11a3d3b`).

#### 4. Telaah Resep Dokter Digital & Rotasi Stok FEFO (M12 / PR #6)
* **Antrean Telaah Resep:**
  * Membangun antarmuka telaah resep dokter digital bagi apoteker cabang dengan validasi legalitas Nomor SIP (*Surat Izin Praktik*) dokter (`083ded7`).
* **Algoritma Rotasi Stok FEFO (First Expired, First Out):**
  * Merancang algoritma pengambilan batch obat otomatis berdasarkan tanggal kedaluwarsa paling awal (`288a792`).
  * Sistem *early warning* otomatis (peringatan H-30 hari) untuk obat yang mendekati tanggal *expired* guna mencegah kerugian apotek (`288a792`).
* **Redesign Dashboard Staf & Apoteker:**
  * Menyelaraskan seluruh antarmuka dashboard operasional tenant apotek dengan palet warna hijau farmasi resmi sesuai SRS (`b599624`, `f62576e`).
  * Standarisasi sintaks kode menggunakan Laravel Pint (`847a226`, `b58c00b`).

---

### D. Candia Purwa (`unknown <candia.purwa@gmail.com>`)
**Peran:** Real-Time WebSockets, Financial Analytics & Security Hardening Lead  
**Alokasi Tugas:** Modul 12-RT, Modul 13, Modul 14

#### 1. Pelacakan Pesanan Real-Time via WebSockets / Laravel Reverb (M12-RT)
* **Infrastruktur Laravel Reverb:**
  * Menyiapkan konfigurasi server WebSocket mandiri Laravel Reverb pada port 8080 di `config/reverb.php` (`026aa90`).
* **Broadcasting Private Channels:**
  * Membuat event broadcast `OrderStatusUpdated` (memberitahu pasien saat status obat berubah dari diproses $\rightarrow$ disiapkan $\rightarrow$ siap diambil) (`8e311f7`).
  * Membuat event broadcast `IncomingOrderPlaced` (memberitahu layar apoteker saat ada pesanan baru masuk secara instan) (`1852240`).
* **Otorisasi Saluran Privat:**
  * Menerapkan aturan otorisasi ketat di `routes/channels.php` pada saluran `private-order.{id}` dan `private-tenant.{id_client}` untuk mencegah penyadapan data pasien (`bd6ed8f`).
* **Antarmuka Pelacakan Echo:**
  * Membangun view pelacakan status pesanan real-time pelanggan terintegrasi Laravel Echo di `resources/views/customer/order-track.blade.php` (`a77c27f`, `62fe550`).
* **Test Suite Reverb:**
  * Menulis pengujian broadcast otomatis di `tests/Feature/ReverbOrderStatusBroadcastTest.php` (`ed82626`, `3aa0730`).

#### 2. Dashboard Analitik Penjualan, Rekonsiliasi & Ekspor Laporan (M13)
* **Service Agregasi Finansial:**
  * Mengembangkan `SalesReportService` untuk mengagregasikan omzet penjualan harian/bulanan, total transaksi, dan metrik keuangan multi-cabang (`8e10dad`, `42be2fa`).
* **Immutability Snapshot Penjualan (ADR-0008):**
  * Merancang model `RingkasanPeriodePenjualan` dengan composite key unik (`id_client`, tanggal awal, tanggal akhir) agar rekap omzet kebal dari duplikasi atau manipulasi (`853d664`).
* **Tutup Buku Kas Terkunci:**
  * Membangun aksi penutupan kas apotek melalui `CloseSalesPeriodAction` yang mengunci buku transaksi cabang (`ea2acd3`).
* **Ekspor CSV Kebal Formula Injection:**
  * Membangun `SalesReportExportService` untuk ekspor data CSV secara streaming dengan sanitasi otomatis karakter berbahaya (`=`, `+`, `-`, `@`) guna mencegah celah keamanan *CSV Formula Injection* (`cfbb887`).
* **Tampilan Laporan & PDF Formal:**
  * Membangun antarmuka analitik eksekutif `laporan-penjualan.blade.php` dan template cetak dokumen PDF formal (`ad1da04`).
* **Testing Analitik:**
  * Menulis automated test kalkulasi laporan dan proteksi periode terkunci di `tests/Feature/SalesReportAndPeriodLockTest.php` (`55f5a29`).

#### 3. Security Hardening, Rate Limiting & Pengujian Mutu (M14)
* **Security Headers Middleware:**
  * Mengimplementasikan `SecurityHeadersMiddleware.php` yang menyuntikkan header keamanan HTTP tingkat tinggi: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security` (HSTS), `Referrer-Policy`, dan Content Security Policy (CSP) (`fb13c90`).
* **Proteksi Brute Force & Broadcast Rate Limiting:**
  * Mengonfigurasi rate limiter API dan WebSocket broadcast di `AppServiceProvider` (`30cc5ad`).
* **Automated Security Test Suite:**
  * Menulis pengujian otomatis untuk header keamanan, CSP, dan rate limiter di `tests/Feature/SecurityHardeningTest.php` (`7fec97f`).
* **Integrasi Data Uji:**
  * Menyiapkan batch data obat dan stok fisik pada skenario uji checkout di `tests/Feature/CartCheckoutPaymentTest.php` (`fea8438`).
  * Standarisasi format kode menggunakan Laravel Pint (`c397112`).

---

## 3. Matriks Lengkap Riwayat Commit Seluruh Tim (Chronological Log)

Berikut adalah rekapitulasi seluruh **100 commit** yang tercatat di repositori GitHub, diurutkan secara kronologis:

| No | Hash | Tanggal | Penulis | Pesan Commit | Modul Terkait |
| :-: | :---: | :---: | :--- | :--- | :--- |
| 1 | `84028e5` | 30-10-2026 | Muhammad Daffa Ariby | `startup` | Inisialisasi awal repositori |
| 2 | `d88c465` | 30-10-2026 | Guntur Afandi | `Create README.md` | Dokumentasi awal |
| 3 | `fb838a4` | 30-10-2026 | Muhammad Daffa Ariby | `Update README.md` | Dokumentasi |
| 4 | `88b36c2` | 30-10-2026 | Muhammad Daffa Ariby | `Update README.md` | Dokumentasi |
| 5 | `6d7e4ae` | 30-10-2026 | Guntur Afandi | `feat(auth): implement Diprella double-slider login & register with Firebase Auth` | Auth (Firebase) |
| 6 | `1b6b0d8` | 30-10-2026 | Muhammad Daffa Ariby | `feat: complete Pertemuan 1 s/d 3 Apotek Multi-Klien implementation` | M01, M02, M03 (Core) |
| 7 | `8f4cb34` | 30-10-2026 | Guntur Afandi | `feat(auth): enhance dashboard profile modal and web route for firebase auth session` | Auth |
| 8 | `6ef04e2` | 30-10-2026 | Guntur Afandi | `rapikan layout katalog obat dan perbaiki tampilan mobile` | M-UI (Mobile) |
| 9 | `96cb27e` | 30-10-2026 | Guntur Afandi | `Merge pull request #3 from heliosparta18/feat/guntur-katalog-mobile` | Merge PR #3 |
| 10 | `3aa626f` | 01-11-2026 | Guntur Afandi | `docs: add project roadmap and PRD documentation for team members (Arya, Candi, Daffa, Guntur)` | Roadmap & PRD |
| 11 | `fc198c9` | 02-11-2026 | Guntur Afandi | `docs: update roadmap dan prd pembagian modul daffa guntur arya candi` | Roadmap & PRD |
| 12 | `4fa5ec1` | 02-11-2026 | Arya Dwi Mahendra | `buat manajemen staf cabang dan apj 5nf` | M05 (Staf & APJ 5NF) |
| 13 | `7a0f9fe` | 02-11-2026 | Arya Dwi Mahendra | `bikin sesi pelanggan opaque token cookie` | M06 (Sesi Tamu) |
| 14 | `11a3d3b` | 02-11-2026 | Arya Dwi Mahendra | `implementasi katalog obat livewire dan filter` | M07 (Katalog Livewire) |
| 15 | `083ded7` | 02-11-2026 | Arya Dwi Mahendra | `buat antrean telaah resep dan cek sip dokter` | M12 (Telaah Resep) |
| 16 | `288a792` | 02-11-2026 | Arya Dwi Mahendra | `bikin algoritma rotasi stok fefo dan alert ed` | M12 (Rotasi Stok FEFO) |
| 17 | `866e6bf` | 03-11-2026 | Muhammad Daffa Ariby | `bikin tenant context dan middleware resolusi tenant dari parameter url` | M04 (Tenant Context) |
| 18 | `ced8b60` | 03-11-2026 | Muhammad Daffa Ariby | `tambah tenant scope global dan trait belongs to tenant di model transaksi` | M04 (Tenant Scope) |
| 19 | `3104b0a` | 03-11-2026 | Muhammad Daffa Ariby | `pasang policy otorisasi cegah apoteker intip transaksi cabang lain` | M04 (Policy Anti-IDOR) |
| 20 | `30338c3` | 03-11-2026 | Muhammad Daffa Ariby | `tambah unit test isolasi tenant dan cegah idor antar cabang` | M04 (Unit Test Tenant) |
| 21 | `a43a02a` | 03-11-2026 | Muhammad Daffa Ariby | `Merge pull request #4 from heliosparta18/feature/pertemuan-04-multi-tenant` | Merge PR #4 (M04) |
| 22 | `b751c0d` | 03-11-2026 | Muhammad Daffa Ariby | `fix : benerin tipe phpstan dan format kode tenant scope` | M04 (Fix PHPStan) |
| 23 | `e77670b` | 03-11-2026 | Muhammad Daffa Ariby | `fix: bereskan sisa error linter dan tipe di middleware serta trait` | M04 (Fix Linter) |
| 24 | `db0191f` | 03-11-2026 | Muhammad Daffa Ariby | `fix: tambah properti status pada model tenant` | M04 (Fix Model) |
| 25 | `785f318` | 05-11-2026 | Guntur Afandi | `feat(ordering): implementasi keranjang belanja redis multi-tenant dan checkout atomik (M08-M09)` | M08, M09 (Cart & Checkout) |
| 26 | `e3dc7d3` | 05-11-2026 | Guntur Afandi | `feat(payments): implementasi payment gateway qris emvco sandbox dan webhook settlement (M10-M11)` | M10, M11 (QRIS & Webhook) |
| 27 | `121b4b8` | 05-11-2026 | Guntur Afandi | `docs: perbarui status modul guntur (M08-M11) menjadi SELESAI` | Dokumentasi Modul |
| 28 | `0b9713a` | 06-11-2026 | Dependabot | `build(deps): bump the github-actions group across 1 directory with 2 updates` | CI/CD |
| 29 | `dd44777` | 06-11-2026 | Arya Dwi Mahendra | `aryak` | Branch Sync |
| 30 | `847a226` | 06-11-2026 | Arya Dwi Mahendra | `style: fix code style issues via pint` | Code Styling (Pint) |
| 31 | `b58c00b` | 06-11-2026 | Arya Dwi Mahendra | `style: fix code style issues via pint` | Code Styling (Pint) |
| 32 | `b2b7118` | 07-11-2026 | Guntur Afandi | `docs: perbarui README dengan ringkasan progres proyek dan status pengerjaan` | README |
| 33 | `44f5676` | 07-11-2026 | Guntur Afandi | `docs: perbarui README dengan ringkasan progres proyek dan status pengerjaan` | README |
| 34 | `68a2bb6` | 07-11-2026 | Guntur Afandi | `Merge pull request #6 from heliosparta18/feat/arya-catalog-fefo` | Merge PR #6 (Arya) |
| 35 | `420ec12` | 07-11-2026 | Guntur Afandi | `Merge pull request #5 from heliosparta18/feat/guntur-checkout-qris` | Merge PR #5 (Guntur) |
| 36 | `de41df9` | 07-11-2026 | Guntur Afandi | `docs: formalisasi README tanpa emoji dan standarisasi rincian progres` | README |
| 37 | `db9b6cf` | 07-11-2026 | Guntur Afandi | `docs: sesuaikan matriks progres pasca-merge PR #5, #6, dan #7 tanpa emoji` | README |
| 38 | `6d2e8b5` | 07-11-2026 | Muhammad Daffa Ariby | `Update README.md` | README |
| 39 | `9933b8d` | 07-11-2026 | Guntur Afandi | `fix(ordering,payments): perbaiki idor tenant payment, cocokkan signature simulasi qris, dan tambah restorasi stok pembatalan pesanan` | Fix Ordering & QRIS |
| 40 | `026aa90` | 07-11-2026 | Candia Purwa | `setup konfigurasi laravel reverb port 8080` | M12-RT (Reverb Config) |
| 41 | `8e311f7` | 07-11-2026 | Candia Purwa | `buat event order status updated untuk broadcast private channel` | M12-RT (Status Event) |
| 42 | `1852240` | 07-11-2026 | Candia Purwa | `tambah event incoming order placed untuk antrean pesanan cabang` | M12-RT (Incoming Event) |
| 43 | `bd6ed8f` | 07-11-2026 | Candia Purwa | `pasang otorisasi private channel order dan tenant di routes` | M12-RT (Channel Auth) |
| 44 | `a77c27f` | 07-11-2026 | Candia Purwa | `bikin view pelacakan pesanan real-time dengan laravel echo` | M12-RT (Echo View) |
| 45 | `ed82626` | 07-11-2026 | Candia Purwa | `tambah test suite pengujian siaran event reverb` | M12-RT (Test Suite) |
| 46 | `62fe550` | 07-11-2026 | Candia Purwa | `perbaiki penamaan channel private reverb dan listener echo` | M12-RT (Fix Channel) |
| 47 | `853d664` | 07-11-2026 | Candia Purwa | `update model ringkasan periode dan proteksi composite key` | M13 (Model ADR-0008) |
| 48 | `8e10dad` | 07-11-2026 | Candia Purwa | `buat service agregasi data penjualan dan metrik transaksi` | M13 (Sales Report Service) |
| 49 | `ea2acd3` | 07-11-2026 | Candia Purwa | `implementasi penutupan periode kas terkunci sesuai adr 0008` | M13 (Close Period Action) |
| 50 | `cfbb887` | 07-11-2026 | Candia Purwa | `tambah service ekspor laporan csv dan mitigasi formula injection` | M13 (Export CSV Safe) |
| 51 | `ad1da04` | 07-11-2026 | Candia Purwa | `buat tampilan laporan analitik template pdf dan registrasi rute` | M13 (Analytics View & PDF) |
| 52 | `55f5a29` | 07-11-2026 | Candia Purwa | `tambah test kalkulasi omzet dan verifikasi kekebalan periode terkunci` | M13 (Test Analytics) |
| 53 | `cf88c83` | 07-11-2026 | Muhammad Daffa Ariby | `fix(tests): perbaiki kredensial testing di phpunit.xml, format rute, dan inisialisasi stok cabang untuk pencegahan error transaksi` | Testing Environment Fix |
| 54 | `f88a082` | 07-11-2026 | Muhammad Daffa Ariby | `daffa` | Tenant Audit & PRD |
| 55 | `42be2fa` | 08-11-2026 | Candia Purwa | `selaraskan agregasi laporan dengan tabel item_pesanan dan selesaikan pengujian periode terkunci` | M13 (Fix Aggregation) |
| 56 | `fb13c90` | 08-11-2026 | Candia Purwa | `implementasi security headers middleware dan konfigurasi csp` | M14 (CSP & Headers) |
| 57 | `30cc5ad` | 08-11-2026 | Candia Purwa | `konfigurasi rate limiter proteksi brute force api dan broadcast` | M14 (Rate Limiter) |
| 58 | `7fec97f` | 08-11-2026 | Candia Purwa | `tambah automated test untuk security headers csp dan rate limiter` | M14 (Security Tests) |
| 59 | `fea8438` | 08-11-2026 | Candia Purwa | `test(ordering): sediakan stok obat cabang pada skenario checkout dan idor` | Testing Fix |
| 60 | `3aa0730` | 08-11-2026 | Candia Purwa | `selesaikan pengujian reverb dan perbaiki skenario stok checkout` | Testing Fix |
| 61 | `69d1e81` | 08-11-2026 | Candia Purwa | `merge: selesaikan konflik pengujian checkout dan gabungkan fitur keamanan ke main` | Merge & QA |
| 62 | `c397112` | 08-11-2026 | Candia Purwa | `style: format kode otomatis menggunakan laravel pint` | Code Styling (Pint) |
| 63 | `5516ab8` | 08-11-2026 | Candia Purwa | `candia` | Branch Sync |
| 64 | `71cc2d7` | 08-11-2026 | Guntur Afandi | `docs: perbarui README dengan audit progres modul Candi (M12-RT, M13, M14) dan sisa backlog penyempurnaan` | README |
| 65 | `9729892` | 08-11-2026 | Guntur Afandi | `docs: tambah PRD_DESIGN_AND_RULES dan perbaiki rute tracking pesanan customer` | PRD Design & Rules |
| 66 | `d5e3f19` | 08-11-2026 | Guntur Afandi | `docs: mutakhirkan PRD_DESIGN_AND_RULES dengan inventaris lengkap 22 artboard FigCraft` | FigCraft Inventory |
| 67 | `7fa2fa9` | 08-11-2026 | Guntur Afandi | `docs: tambah panduan penyelesaian sisa tugas demo dan inventarisasi desain FigCraft` | Panduan Demo |
| 68 | `b599624` | 08-11-2026 | Arya Dwi Mahendra | `selesaikan antarmuka dan logika modul operasional apotek arya m05-m12` | M05-M12 (Fulfillment) |
| 69 | `96d727e` | 08-11-2026 | Muhammad Daffa Ariby | `feat(ui): implementasi antarmuka prototipe login OmniPharma multi-klien dengan role switcher demo` | UI Demo Role Switcher |
| 70 | `3739687` | 08-11-2026 | Muhammad Daffa Ariby | `prototype sementara` | UI Prototype |
| 71 | `1b18bb9` | 09-11-2026 | Muhammad Daffa Ariby | `fix: konfigurasi gitattributes binary linguist dan keluarkan pdf referensi dari git tracking` | Git Configuration |
| 72 | `9787664` | 09-11-2026 | Muhammad Daffa Ariby | `Delete evidence directory` | Pembersihan Evidence |
| 73 | `2e998e0` | 09-11-2026 | Muhammad Daffa Ariby | `Delete references/srs_apotek_text.txt` | Pembersihan Referensi |
| 74 | `99ce4d5` | 09-11-2026 | Muhammad Daffa Ariby | `Delete references/pdm_revisi_text.txt` | Pembersihan Referensi |
| 75 | `dd88804` | 09-11-2026 | Muhammad Daffa Ariby | `Delete references/mockup_text.txt` | Pembersihan Referensi |
| 76 | `d3bb1b7` | 09-11-2026 | Guntur Afandi | `feat(ordering): implement token_sesi migration, guest session reverb auth, and qris settlement event dispatch` | M09, M11 (Token Sesi) |
| 77 | `65da189` | 09-11-2026 | Guntur Afandi | `feat(auth): implement Firebase authentication, Google sign-in, and responsive user dashboard` | Auth (Google & Dashboard) |
| 78 | `3080dc0` | 09-11-2026 | Guntur Afandi | `feat(ui): update official pharmacy emblem logo across login, register, dashboard, layouts, and favicons` | UI Branding |
| 79 | `0281e33` | 09-11-2026 | Guntur Afandi | `fix(assets): bundle compiled public/build assets and enforce https scheme on trusted proxies to prevent mixed-content blocking` | Production Assets |
| 80 | `5d02438` | 09-11-2026 | Guntur Afandi | `feat(auth): add email OTP authentication flow and first-time Google onboarding profile completion` | Auth (OTP & Google Onboarding) |
| 81 | `b166add` | 09-11-2026 | Guntur Afandi | `feat(auth): streamline login form to email/username and password, and implement register email OTP verification` | Auth (Email OTP Verification) |
| 82 | `db2dca5` | 09-11-2026 | Guntur Afandi | `feat(auth): enable 1-click demo login, seed demo users, and polish non-slop split card UI` | Auth (1-Click Demo) |
| 83 | `20ba36e` | 09-11-2026 | Muhammad Daffa Ariby | `fix(security,build): dukung vite dev server di csp lokal dan mutakhirkan aset build produksi` | Security CSP & Vite |
| 84 | `32a5471` | 09-11-2026 | Muhammad Daffa Ariby | `build: abaikan direktori skills dan konfigurasi ai pada gitignore` | Git Hygiene |
| 85 | `d55163c` | 09-11-2026 | Guntur Afandi | `refactor: resolve PHPStan level 7 types, wire admin closing action, and add OTP rate limiting` | Quality Gate (PHPStan Level 7) |
| 86 | `1845248` | 09-11-2026 | Muhammad Daffa Ariby | `fix(ci,ui): resolve CI phpstan type errors, prevent redirect to default laravel page, and make cart reactive` | CI/CD & Cart Reactivity |
| 87 | `19efcbe` | 09-11-2026 | Guntur Afandi | `feat(ui): implement welcoming landing page with drug search guide and portal gateway` | UI Landing Page |
| 88 | `c1f74f7` | 09-11-2026 | Guntur Afandi | `build(assets): update compiled assets for welcoming landing page` | Assets Build |
| 89 | `d6a49ef` | 09-11-2026 | Guntur Afandi | `fix(catalog): fix unescaped quote HTML attribute leak in customer home view` | Bugfix View |
| 90 | `36d514c` | 09-11-2026 | Guntur Afandi | `fix(routes): render welcoming landing page on root URL for unauthenticated visitors` | Route Landing Page |
| 91 | `36a15d8` | 09-11-2026 | Muhammad Daffa Ariby | `feat(navigation): add bidirectional Dashboard Utama navigation across customer, tenant, and admin portals` | Navigasi Lintas Portal |
| 92 | `abc6300` | 09-11-2026 | Guntur Afandi | `feat(ui): enforce anti-slop mobile responsiveness, eliminate horizontal overflow, and optimize touch targets across all views` | UI Mobile Optimization |
| 93 | `8c4534c` | 09-11-2026 | Guntur Afandi | `feat(ui): comprehensive mobile responsiveness overhaul across all 10 portal views and layouts` | UI Mobile Overhaul |
| 94 | `f62576e` | 09-11-2026 | Arya Dwi Mahendra | `feat: redesign dashboard multi-peran sesuai tema hijau dan standar SRS apotek` | UI Theme Redesign (Hijau SRS) |
| 95 | `dbf6635` | 09-11-2026 | Guntur Afandi | `docs: add dedicated individual task backlog guide and update README with audit findings` | Backlog Guide |
| 96 | `b0a8bd4` | 09-11-2026 | Guntur Afandi | `merge: merge branch 'origin/feat/roles-dashboard' into main resolving layout conflicts cleanly` | Merge & Layout Conflict |
| 97 | `ac4898a` | 09-11-2026 | Guntur Afandi | `build(assets): update production bundle after resolving merge conflicts` | Production Build Bundle |
| 98 | `af062a4` | 09-11-2026 | Guntur Afandi | `feat(system): complete all remaining frontend views, modular ordering controllers, security FormRequests, and E2E test suite` | Complete System & E2E Test |
| 99 | `3f7a5b8` | 09-11-2026 | Muhammad Daffa Ariby | `penghapusan file tidak penting` | Pembersihan Non-SRS & perbaikan.md |
| 100 | `3021cc8` | 10-11-2026 | Muhammad Daffa Ariby | `hapus sampah kantin` | Pembersihan 14 Model & 9 Seeder Kantin |

---

## 4. Matriks Pemenuhan Kebutuhan SRS (*Requirements Traceability*)

Berikut adalah pemetaan implementasi kode tim terhadap seluruh kebutuhan fungsional resmi dalam SRS Apotek:

| Kode Kebutuhan | Deskripsi Fitur SRS | Pengembang Penanggung Jawab | File & Komponen Implementasi | Status Uji Otomatis |
| :--- | :--- | :--- | :--- | :---: |
| **FR-PEL-01** | Browsing Katalog Obat & Pencarian Multi-Cabang | **Arya Dwi M.** & **Guntur A.** | `PublicCatalog.php`, `home.blade.php`, `CatalogService.php` | PASSED |
| **FR-PEL-02** | Unggah Resep Dokter Digital | **Arya Dwi M.** & **Daffa A.** | `PrescriptionController.php`, `UnggahResepRequest.php` | PASSED |
| **FR-PEL-03** | Keranjang Belanja Redis & Checkout Atomik | **Guntur Afandi** | `CartController.php`, `CheckoutController.php`, Redis Cart Hash | PASSED |
| **FR-PEL-04** | Pembayaran Dynamic QRIS Sandbox & Timer 15 Menit | **Guntur Afandi** | `FakeQrisGateway.php`, `payment.blade.php`, EMVCo String | PASSED |
| **FR-PEL-05** | Pelacakan Status Pesanan Real-Time (WebSockets) | **Candia Purwa** | `order-track.blade.php`, Laravel Reverb, Laravel Echo | PASSED |
| **FR-PEL-06** | Sesi Belanja Tamu Anonim (*Opaque Token*) | **Arya Dwi M.** & **Guntur A.** | `CustomerSessionService.php`, cookie `guest_token`, `token_sesi` | PASSED |
| **FR-APT-01** | Otorisasi & Isolasi Akses Transaksi Cabang (Anti-IDOR)| **M. Daffa Ariby** | `TenantContext.php`, `TenantScope.php`, `PesananPolicy.php` | PASSED |
| **FR-APT-02** | Telaah Resep Dokter & Verifikasi SIP | **Arya Dwi Mahendra** | `verifikasi-resep.blade.php`, `VerifikasiResepRequest.php` | PASSED |
| **FR-APT-03** | Rotasi Pengambilan Stok Obat Berbasis FEFO | **Arya Dwi Mahendra** | `stok-fefo.blade.php`, kueri FEFO `orderBy('tgl_kedaluwarsa')` | PASSED |
| **FR-APT-04** | Peringatan Dini Obat Mendekati Kedaluwarsa (H-30) | **Arya Dwi Mahendra** | `tenant/dashboard.blade.php`, indikator peringatan kedaluwarsa | PASSED |
| **FR-ADM-01** | Dashboard Analitik Penjualan Multi-Cabang | **Candia Purwa** | `SalesReportService.php`, `laporan-penjualan.blade.php` | PASSED |
| **FR-ADM-02** | Penutupan Buku Kas Transaksi Cabang Terkunci | **Candia Purwa** | `CloseSalesPeriodAction.php`, `RingkasanPeriodePenjualan.php` | PASSED |
| **FR-ADM-03** | Ekspor Laporan CSV Aman (*Anti-Formula Injection*) | **Candia Purwa** | `SalesReportExportService.php` | PASSED |
| **FR-ADM-04** | Manajemen Hierarki Staf Cabang & APJ 5NF | **Arya Dwi M.** & **Daffa A.** | `StaffManagementService.php`, tabel `peta_pengawas_staf` | PASSED |
| **FR-SEC-01** | Pengerasan Keamanan HTTP Header & CSP | **Candia Purwa** & **Daffa A.** | `SecurityHeadersMiddleware.php` | PASSED |
| **FR-SEC-02** | Audit Otomatis Isolasi Multi-Tenant CLI | **M. Daffa Ariby** | `php artisan tenant:audit` (`AuditTenantIsolationCommand.php`) | PASSED |
| **FR-SEC-03** | Proteksi Brute Force & Rate Limiting | **Candia Purwa** & **Guntur A.** | Rate Limiter di `AppServiceProvider.php` | PASSED |

---

## 5. Ringkasan Kualitas Kode & Quality Gate

Sebelum integrasi final ke branch `main`, seluruh kode tim telah lolos serangkaian pengujian terotomasi:
1. **Automated Testing Suite (PHPUnit):** 120+ skenario pengujian unit, feature, dan integrasi end-to-end berjalan sukses 100%.
2. **Static Analysis (PHPStan):** Lulus analisa statis tingkat ketat **Level 7** (bebas error tipe data, union types, dan strict parameter checking).
3. **Standarisasi Gaya Kode (Laravel Pint):** Format kode 100% selaras dengan standar industri **PSR-12**.
4. **Keamanan Data:** Terverifikasi bebas dari celah keamanan IDOR, CSV Formula Injection, XSS pada unggah resep, serta kebocoran data antar-cabang apotek.
