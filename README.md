# OmniPharma Network — Sistem Informasi Apotek Multi-Klien

> **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi  
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Basis Teknologi:** Laravel 13.25, Livewire 4.1, Tailwind CSS v4, Laravel Reverb (WebSockets), MariaDB 11.4, Redis 7.2  

---

Ringkasan Progres Proyek
| Metrik | Status | Keterangan |
| :--- | :---: | :--- |
| **Progres Ter-merge di `main`** | **~98%** | 120/120 tests passed, PHPStan Level 7 lulus, Pint PSR-12 passed, Firebase Auth, Google 1-Click, Email OTP, dan optimasi mobile tuntas. |
| **Status Integrasi Pull Request** | **Lengkap** | Seluruh cabang kerja modul inti (Daffa, Guntur, Arya, Candi) telah berhasil digabungkan ke `main`. |
| **Sisa Pekerjaan (Penyempurnaan / Polish)**| **~2%** | FormRequest sanitasi file resep, 1 test E2E terpadu, refactor routing closure, dan gladi resik demo. |

```
Progres Integrasi Sistem:
[=========================-] 98% Selesai
- Ter-merge di branch main (98%): M01 s/d M14, Firebase Auth + Google Login, Email OTP, Mobile Reflow 10 Layar
- Sisa backlog penyempurnaan (2%): FormRequest sanitasi, Test E2E terpadu, Refactor controller modular, & Gladi demo
```
---

## Matriks Pembagian Tugas dan Status Anggota (Struktur 4 - 4 - 4 - 3)

| Anggota Tim | Peran & Alokasi Modul | Status Pengerjaan | Status di `main` |
| :--- | :--- | :---: | :--- |
| **Muhammad Daffa Ariby** | **Architecture & Multi-Tenancy Kernel**<br>• M01: Setup Toolchain & Environment<br>• M02: Modular Monolith 6 Providers<br>• M03: Database 16 Tabel Domain & Seeder<br>• M04: Isolasi Multi-Tenant Context & Scope | **SELESAI**<br>(4 dari 4 Tugas) | Ter-merge di `main`<br>(Commit `1b6b0d8` & PR #4) |
| **Guntur Afandi** | **Commerce Pipeline & Payment Gateway QRIS**<br>• M-UI: Refaktor UI Katalog & Touch Target 44px<br>• M08: Keranjang Belanja Redis Multi-Tenant<br>• M09: Checkout Atomik & Snapshot Harga<br>• M10: Payment Gateway Dynamic QRIS Sandbox<br>• M11: Webhook Idempoten & Auto-Settlement | **SELESAI**<br>(4 dari 4 Tugas) | Ter-merge di `main`<br>(PR #3 & PR #5) |
| **Arya Dwi Mahendra** | **Branch Admin, Guest Session & FEFO Inventory**<br>• M05: Administrasi Staf Cabang & APJ (5NF)<br>• M06: Sesi Tamu Opaque Token Cookie<br>• M07: Katalog Obat Multi-Cabang Livewire<br>• M12: Telaah Resep Dokter & Rotasi Stok FEFO | **SELESAI**<br>(4 dari 4 Tugas) | Ter-merge di `main`<br>(PR #6 & PR #7) |
| **Candia Purwa** | **Real-Time, Financial Reporting & Hardening**<br>• M12-RT: Real-time Order Tracking (Reverb)<br>• M13: Dashboard Analitik & Ekspor Laporan<br>• M14: Security Hardening & Rate Limiting | **SELESAI**<br>(3 dari 3 Tugas) | Ter-merge di `main`<br>(Commit `69d1e81` & `c397112`) |

---

## Rincian Modul yang Telah Selesai dan Terintegrasi

### 1. Fondasi Sistem dan Multi-Tenancy (Daffa)
- [x] **M01 — Toolchain:** Setup Laravel 13, MariaDB 11, Redis 7, Reverb, PHPStan, dan Laravel Pint.
- [x] **M02 — Modular Monolith:** 6 Bounded Context (`Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, `Reporting`), registrasi route portal, dan blade layout.
- [x] **M03 — Skema Database:** 16 tabel domain utama PDM (UUID primary keys, relasi integritas FK), seeder `DemoApotekSeeder`, dan automated test suite.
- [x] **M04 — Isolasi Multi-Tenant:** `TenantContext`, global scope `TenantScope` (`WHERE id_client = ?`), middleware `ResolveTenantContext`, domain exceptions, artisan audit command `tenant:audit`, dan policy pencegahan IDOR (PR #4).

### 2. Transaksi dan Pembayaran QRIS (Guntur)
- [x] **UI Refactor:** Optimasi mobile touch-target 44px, grid katalog responsif, dan badge klasifikasi obat (PR #3).
- [x] **M08 — Keranjang Redis:** Hash in-memory `cart:{sessionId}:{clientId}` (TTL 7 hari) dan revalidasi ketersediaan stok fisik cabang (PR #5).
- [x] **M09 — Checkout Atomik:** Transaksi aman konkurensi (`DB::transaction` + `lockForUpdate`), denormalisasi harga satuan (`item_pesanan` sesuai ADR-0008), dan generate order code `#ORD-YYYYMMDD-XXXX` (PR #5).
- [x] **M10 — Dynamic QRIS Sandbox:** Abstraksi `PaymentGatewayInterface`, emulator `FakeQrisGateway`, generator string QRIS standar EMVCo (NMID, MCC 5912 Apotek, CRC16 CCITT), dan antarmuka countdown timer 15 menit (PR #5).
- [x] **M11 — Webhook Idempoten:** Endpoint `POST /api/webhooks/qris` dengan verifikasi signature HMAC-SHA256, auto-settlement, dan ledger split allocation (PR #5).

### 3. Operasional Cabang, Katalog dan Resep (Arya)
- [x] **M05 — Administrasi Cabang & APJ:** Manajemen staf cabang berbasis peran (RBAC) pada `StaffManagementService` dan pemetaan hierarki Apoteker Penanggung Jawab (APJ) berderajat 5NF pada `peta_pengawas_staf` (PR #6 & PR #7).
- [x] **M06 — Sesi Tamu Anonim:** Pengelolaan token buram (opaque token 32 karakter via cookie) tanpa mewajibkan login awal serta pendeteksi cabang terdekat via `CustomerSessionService` (PR #6 & PR #7).
- [x] **M07 — Katalog Livewire:** Pencarian obat reaktif per cabang, filter sediaan farmasi (tablet, sirup, salep), badge `wajib_resep`, dan ketersediaan stok real-time (PR #6).
- [x] **M12 — Telaah Resep & FEFO:** Portal apoteker terpadu pada `tenant/dashboard.blade.php` untuk validasi keabsahan nomor SIP dokter (`503/SIP/2024`), telaah resep (approve/reject via `VerifyPrescriptionAction`), serta inspeksi batch kedaluwarsa terdekat (#1 FEFO via `StockFefoService`) dengan peringatan dini H-30 hari (PR #6).

### 4. Real-Time WebSockets, Pelaporan Finansial & Pengerasan Keamanan (Candi)
- [x] **M12-RT — Real-Time Order Tracking via WebSockets (Laravel Reverb):**
  - Konfigurasi broadcasting Laravel Reverb port 8080 (`config/reverb.php`).
  - Event broadcast `OrderStatusUpdated` dan `IncomingOrderPlaced` ke private channel (`private-order.{id}` dan `private-tenant.{id_client}`).
  - Otorisasi private channel di `routes/channels.php` untuk mencegah penyadapan data pasien.
  - Antarmuka pelacakan status pesanan real-time pelanggan terintegrasi Laravel Echo (`resources/views/customer/order-track.blade.php`).
  - Automated test suite siaran event Reverb (`tests/Feature/ReverbOrderStatusBroadcastTest.php`).
- [x] **M13 — Dashboard Analitik Penjualan, Rekonsiliasi & Ekspor Laporan:**
  - Penegakan composite key & snapshot immutability pada `RingkasanPeriodePenjualan` sesuai ADR-0008.
  - Agregasi omzet dan metrik transaksi multi-cabang via `SalesReportService`.
  - Aksi penutupan buku kas transaksi apotek cabang berstatus terkunci via `CloseSalesPeriodAction`.
  - Service streaming ekspor CSV aman dengan sanitasi mitigasi *Formula Injection* via `SalesReportExportService`.
  - Antarmuka dashboard analitik eksekutif (`laporan-penjualan.blade.php`) dan template cetak PDF formal (`laporan-penjualan-template.blade.php`).
  - Automated test suite kalkulasi laporan & penutupan periode (`tests/Feature/SalesReportAndPeriodLockTest.php`).
- [x] **M14 — Security Hardening, Rate Limiting & Quality Gate:**
  - Middleware keamanan `SecurityHeadersMiddleware` (X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy) dan Content Security Policy (CSP).
  - Proteksi brute force API & broadcast rate limiter di `AppServiceProvider.php`.
  - Automated test suite uji keamanan (`tests/Feature/SecurityHardeningTest.php`).
  - Perbaikan dan integrasi stok obat cabang pada skenario pengujian checkout (`tests/Feature/CartCheckoutPaymentTest.php`).
  - Standarisasi kode menggunakan Laravel Pint (`c397112`).

---

## Sisa Pekerjaan & Backlog Penyempurnaan (~2%)

> 📌 **PANDUAN LENGKAP TUGAS ANGGOTA & BACKLOG:** Buka dokumen resmi **[`README_TUGAS_ANGGOTA_DAN_BACKLOG.md`](./README_TUGAS_ANGGOTA_DAN_BACKLOG.md)** untuk melihat rincian tugas mandiri setiap anggota (Candia, Guntur, Arya, Daffa), tugas kolaboratif barengan, checklist 22 artboard FigCraft, dan skenario presentasi live demo.

### Ringkasan Pembagian Sisa Tugas per Individu:
1. **Candia Purwa (Security & QA):**
   - Buat `app/Http/Requests/Customer/UnggahResepRequest.php` (validasi MIME magic bytes file foto resep).
   - Buat `app/Http/Requests/Tenant/VerifikasiResepRequest.php` (sanitasi XSS catatan apoteker).
   - Buat `tests/Feature/EndToEndOrderFlowTest.php` (uji terpadu 1 siklus transaksi utuh hulu ke hilir).
2. **Guntur Afandi (Commerce & Payments):**
   - Refactor routing closure di `routes/customer.php` ke controller modular (`CartController.php` & `CheckoutController.php`).
   - Tambahkan cache lock idempotensi 60 detik pencegah double-submit saat checkout.
3. **Arya Dwi Mahendra (Branch Operations):**
   - Buat halaman rekam data pasien `resources/views/tenant/pasien.blade.php` (FigCraft Artboard #5).
   - Buat halaman panduan SOP regulasi farmasi `resources/views/tenant/bantuan.blade.php` (FigCraft Artboard #7).
4. **Muhammad Daffa Ariby (Lead Architect):**
   - Tambahkan komponen navigasi sidebar terpadu di layout Admin dan Tenant (`layouts/admin.blade.php` & `tenant.blade.php`).
   - Buat halaman konfigurasi apotek cabang `resources/views/admin/pengaturan-cabang.blade.php` (FigCraft Artboard #8).

### 🤝 Tugas yang Dikerjakan Barengan (Kolaboratif):
- **Gladi Resik Demo 4 Layar (15 Menit):** Pasien (Guntur) $\rightarrow$ Apoteker (Arya) $\rightarrow$ QRIS Reverb (Candia & Guntur) $\rightarrow$ Super Admin Tutup Kas (Daffa).
- **Pemeriksaan Quality Gate Terpadu:** Menjalankan `php artisan test`, `phpstan`, dan `pint` sebelum presentasi akhir.
---

## Panduan Instalasi Lokal

1. **Clone repositori:**
   ```bash
   git clone https://github.com/heliosparta18/startup.git
   cd startup
   ```

2. **Setup environment dan dependencies:**
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   ```

3. **Konfigurasi Database dan Redis di file `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=startup
   DB_USERNAME=root
   DB_PASSWORD=

   REDIS_CLIENT=phpredis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

4. **Migrasi Database dan Seeder:**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Menjalankan Layanan Aplikasi:**
   ```bash
   # Terminal 1: Web Server
   php artisan serve

   # Terminal 2: Asset Compiler
   npm run dev

   # Terminal 3: WebSocket Server (Laravel Reverb)
   php artisan reverb:start
   ```

---

## Eksekusi Pengujian Otomatis

```bash
php artisan test
```
