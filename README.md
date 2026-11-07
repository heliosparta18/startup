# OmniPharma Network — Sistem Informasi Apotek Multi-Klien

> **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi  
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Basis Teknologi:** Laravel 13.25, Livewire 4.1, Tailwind CSS v4, Laravel Reverb (WebSockets), MariaDB 11.4, Redis 7.2  

---

## Ringkasan Progres Proyek

| Metrik | Status | Keterangan |
| :--- | :---: | :--- |
| **Progres Kode Keseluruhan** | **~80%** | 12 dari 15 milestone fitur selesai dikembangkan (termasuk PR aktif). |
| **Progres Ter-merge di `main`** | **~33%** | Fondasi Arsitektur (M01–M03), UI Mobile Refactor, dan Isolasi Multi-Tenant (M04). |
| **Dalam Tahap Pull Request (PR)** | **~47%** | Modul Commerce & QRIS (PR #5) serta Modul Staf, Sesi & Resep FEFO (PR #6 & #7). |
| **Sisa Pekerjaan (Belum Dikerjakan)**| **~20%** | WebSockets Reverb (M12-RT), Dashboard Analitik (M13), dan Security E2E (M14). |

```
Progres Pengembangan Sistem:
[====================----] 80% Selesai
- Selesai di branch main (33%)  : M01, M02, M03, M04, Refaktor UI Mobile (Daffa & Guntur)
- Selesai di branch PR (47%)    : M05, M06, M07, M08, M09, M10, M11, M12 (Arya & Guntur)
- Belum dikerjakan (20%)        : M12-RT, M13, M14 (Candi)
```

---

## Matriks Pembagian Tugas dan Status Anggota (Struktur 4 - 4 - 4 - 3)

| Anggota Tim | Peran & Alokasi Modul | Status Pengerjaan | Branch / PR |
| :--- | :--- | :---: | :--- |
| **Muhammad Daffa Ariby** | **Architecture & Multi-Tenancy Kernel**<br>• M01: Setup Toolchain & Environment<br>• M02: Modular Monolith 6 Providers<br>• M03: Database 16 Tabel Domain & Seeder<br>• M04: Isolasi Multi-Tenant Context & Scope | **SELESAI**<br>(4 dari 4 Tugas) | Merged ke `main`<br>(Commit `1b6b0d8` & PR #4) |
| **Guntur Afandi** | **Commerce Pipeline & Payment Gateway QRIS**<br>• M-UI: Refaktor UI Katalog & Touch Target 44px<br>• M08: Keranjang Belanja Redis Multi-Tenant<br>• M09: Checkout Atomik & Snapshot Harga<br>• M10: Payment Gateway Dynamic QRIS Sandbox<br>• M11: Webhook Idempoten & Auto-Settlement | **SELESAI**<br>(Kode siap di PR #5) | PR #3 (Merged)<br>PR #5 (`feat/guntur-checkout-qris`) |
| **Arya Dwi Mahendra** | **Branch Admin, Guest Session & FEFO Inventory**<br>• M05: Administrasi Staf Cabang & APJ (5NF)<br>• M06: Sesi Tamu Opaque Token Cookie<br>• M07: Katalog Obat Multi-Cabang Livewire<br>• M12: Telaah Resep Dokter & Rotasi Stok FEFO | **SELESAI**<br>(Kode siap di PR #6 & #7) | PR #6 (`feat/arya-catalog-fefo`)<br>PR #7 (`feat/arya-admin-session`) |
| **Candia Purwa** | **Real-Time, Financial Reporting & Hardening**<br>• M12-RT: Real-time Order Tracking (Reverb)<br>• M13: Dashboard Analitik & Ekspor Laporan<br>• M14: Security Hardening & 50+ E2E Test Suite | **BELUM**<br>(0 dari 3 Tugas) | Belum ada commit di branch `feat/candi-*` |

---

## Rincian Progres Modul yang Telah dan Sedang Dikerjakan

### 1. Fondasi Sistem dan Multi-Tenancy (Daffa — Selesai & Merged)
- [x] **M01 — Toolchain:** Setup Laravel 13, MariaDB 11, Redis 7, Reverb, PHPStan, dan Laravel Pint.
- [x] **M02 — Modular Monolith:** 6 Bounded Context (`Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, `Reporting`), registrasi route portal, dan blade layout.
- [x] **M03 — Skema Database:** 16 tabel domain utama PDM (UUID primary keys, relasi integritas FK), seeder `DemoApotekSeeder`, dan automated test suite.
- [x] **M04 — Isolasi Multi-Tenant:** `TenantContext`, global scope `TenantScope` (`WHERE id_client = ?`), middleware `ResolveTenantContext`, dan policy pencegahan IDOR (Merged via PR #4).

### 2. Transaksi dan Pembayaran QRIS (Guntur — Selesai di PR #5)
- [x] **UI Refactor:** Optimasi mobile touch-target 44px, grid katalog responsif, dan badge klasifikasi obat (Merged via PR #3).
- [x] **M08 — Keranjang Redis:** Hash in-memory `cart:{sessionId}:{clientId}` (TTL 7 hari) dan revalidasi ketersediaan stok fisik cabang.
- [x] **M09 — Checkout Atomik:** Transaksi aman konkurensi (`DB::transaction` + `lockForUpdate`), denormalisasi harga satuan (`item_pesanan` sesuai ADR-0008), dan generate order code `#ORD-YYYYMMDD-XXXX`.
- [x] **M10 — Dynamic QRIS Sandbox:** Abstraksi `PaymentGatewayInterface`, emulator `FakeQrisGateway`, generator string QRIS standar EMVCo (NMID, MCC 5912 Apotek, CRC16 CCITT), dan antarmuka countdown timer 15 menit.
- [x] **M11 — Webhook Idempoten:** Endpoint `POST /api/webhooks/qris` dengan verifikasi signature HMAC-SHA256, auto-settlement, dan ledger split allocation.

### 3. Operasional Cabang, Katalog dan Resep (Arya — Selesai di PR #6 & PR #7)
- [x] **M05 — Administrasi Cabang & APJ:** Manajemen staf cabang berbasis peran (RBAC) dan pemetaan hierarki Apoteker Penanggung Jawab (APJ) berderajat 5NF pada `peta_pengawas_staf`.
- [x] **M06 — Sesi Tamu Anonim:** Pengelolaan token buram (opaque token 32 karakter via cookie) tanpa mewajibkan login awal serta pendeteksi cabang terdekat.
- [x] **M07 — Katalog Livewire:** Pencarian obat reaktif per cabang, filter sediaan farmasi (tablet, sirup, salep), badge `wajib_resep`, dan ketersediaan stok real-time.
- [x] **M12 — Telaah Resep & FEFO:** Portal apoteker untuk validasi keabsahan nomor SIP dokter (`503/SIP/2024`), telaah resep (approve/reject), serta algoritma alokasi stok obat berdasarkan masa kedaluwarsa terdekat (First Expired, First Out).

---

## Modul yang Belum Dikerjakan (Sisa 20%)

Pekerjaan berikut merupakan alokasi tanggung jawab modul **Candia Purwa (M12-RT, M13, M14)**:

- [ ] **M12-RT — Real-Time Order Tracking via WebSockets (Laravel Reverb):**
  - Konfigurasi broadcasting Reverb pada port 8080.
  - Event `OrderStatusUpdated` ke private channel `private-order.{id_pesanan}`.
  - Halaman pelacakan pesanan real-time (`order-track.blade.php`) menggunakan Laravel Echo (stepper status otomatis bergerak tanpa refresh).
- [ ] **M13 — Dashboard Analitik Penjualan, Rekonsiliasi & Ekspor Laporan:**
  - Dashboard analitik omzet dan transaksi multi-cabang dengan filter rentang tanggal dinamis.
  - Penutupan periode akuntansi kas terkunci (`ringkasan_periode_penjualan` dengan flag `status_terkunci = true` dan constraint unik sesuai ADR-0008).
  - Ekspor laporan ke PDF (layout print-ready) dan Excel/CSV dengan sanitasi formula injection.
- [ ] **M14 — Security Hardening & 50+ Automated Test Suite:**
  - Sanitasi input form (anti XSS/SQLi) dan Redis Rate Limiter pada endpoint resep dan autentikasi.
  - Penyusunan suite 50+ automated feature test (`php artisan test`) untuk persiapan demonstrasi dan sidang akhir bersama Pak Devit Suwardiyanto.

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
