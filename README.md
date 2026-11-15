# OmniPharma Network — Sistem Informasi Apotek Multi-Klien

> **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi  
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Repositori:** [github.com/heliosparta18/startup](https://github.com/heliosparta18/startup)  
> **Basis Teknologi:** PHP `^8.3` (teruji PHP `8.4.24`), Laravel `13.25.0`, Livewire `^4.1` + Flux `^2.13.1`, Laravel Fortify (2FA & Passkeys), Laravel Reverb (WebSockets), Tailwind CSS `^4.0.7`, Vite `^8.0.0`, MySQL/MariaDB, Redis

---

## Ringkasan Audit & Status Implementasi

Berdasarkan audit menyeluruh terhadap kode sumber, migrasi basis data, routing portal, layanan bisnis, antarmuka pengguna, dan hasil quality gate, sistem **OmniPharma Network** telah mencapai status **100% terintegrasi pada branch `main`**. Seluruh item yang sebelumnya masuk daftar backlog (FormRequest sanitasi resep, refactor controller `CartController` & `CheckoutController`, antrean & pelacakan pesanan, CRUD multi-cabang/obat/staf, portal khusus Kasir, hingga `EndToEndOrderFlowTest`) telah selesai diimplementasikan dan terverifikasi otomatis.

| Aspek Audit | Hasil Verifikasi Nyata di Kodebase |
| :--- | :--- |
| **Arsitektur Backend** | **Modular Monolith** — `6` *bounded context* (`Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, `Reporting`) lewat `App\Modules\ModuleServiceProvider` |
| **Model & Skema Basis Data** | `18` Model Eloquent (`app/Models/`) dan `17` File Migrasi (`database/migrations/`) menggunakan kunci utama UUID (`CHAR(36)`) |
| **Dataset & Seeder** | `4` Seeder (`DatabaseSeeder`, `DemoApotekSeeder`, `GoApotikCatalogSeeder` berisi `42` produk terkurasi, serta `GoApotikFullCatalogSeeder` dengan `1.576` produk lengkap) |
| **Isolasi Multi-Portal (RBAC)** | `4` Portal Terproteksi (`PortalRoutes` + `EnsureUserHasRole` + `TenantContext` + `TenantScope` + `PesananPolicy`): **Pelanggan**, **Apoteker/Tenant**, **Kasir/POS**, dan **Super Admin** |
| **Quality Gate — PHPUnit** | **196 tests, 1.246 assertions — 100% PASSED** (`34` file test di `tests/Feature` & `tests/Unit`, menggunakan SQLite `:memory:` per `phpunit.xml`) |
| **Quality Gate — PHPStan** | **Level 7 — 0 errors** (`phpstan.neon`, Larastan `^3.9`) |
| **Quality Gate — Laravel Pint** | **PSR-12 — PASSED** (`php vendor/bin/pint --test`) |

> Catatan arsip: Dokumen [README_TUGAS_ANGGOTA_DAN_BACKLOG.md](./README_TUGAS_ANGGOTA_DAN_BACKLOG.md) dan [TUGAS_DAFFA.md](./TUGAS_DAFFA.md) disimpan sebagai catatan historis pembagian kerja tim pada fase awal pengembangan.

---

## Tim Pengembang & Pembagian Domain (Struktur 4 - 4 - 4 - 3)

| Anggota Tim | Peran Utama | Cakupan Domain & Kontribusi Utama |
| :--- | :--- | :--- |
| **Muhammad Daffa Ariby** (`heliosparta18`) | **Lead Architect & Core Multi-Tenancy Kernel** | Setup toolchain Laravel 13, 6 modul `ModuleServiceProvider`, skema UUID multi-tabel, `TenantContext` & `TenantScope`, middleware `ResolveTenantContext`, `PortalRoutes`, `php artisan tenant:audit`, layout internal portal, dan CRUD pengaturan cabang (`admin/pengaturan-cabang`). |
| **Guntur Afandi** (`Callme Zettx`) | **Commerce Pipeline, Payment Gateway & Auth Lead** | Katalog responsif GoApotik-style, `CartService` (Redis/session), `CartController`, `CheckoutController` (cache lock idempotensi `30` detik, kalkulator ongkir & link/koordinat Google Maps), `CreateOrderAction` (`lockForUpdate`), `FakeQrisGateway` (**Midtrans Sandbox** + fallback `QrisEmvcoBuilder` CRC16), webhook HMAC-SHA256, serta autentikasi Firebase Google & Email OTP. |
| **Arya Dwi Mahendra** (`Miinggg67`) | **Branch Operations, Guest Session & FEFO Inventory** | `StaffManagementService` (RBAC & hierarki pengawasan APJ berderajat 5NF di `peta_pengawas_staf`), `CustomerSessionService` (token sesi pelanggan anonim), komponen Livewire `PublicCatalog`, `StockFefoService` (prioritas *First Expired, First Out* + peringatan H-30/H-7), serta halaman operasional `tenant/pasien` dan `tenant/bantuan`. |
| **Candia Purwa** (`candia.purwa`) | **Real-Time WebSockets, Analytics & Security Hardening** | Laravel Reverb WebSockets & channel privat (`routes/channels.php`), event `OrderStatusUpdated` & `IncomingOrderPlaced`, `SalesReportService`, `CloseSalesPeriodAction`, `SalesReportExportService` (CSV anti *Formula Injection* + cetak PDF), `SecurityHeadersMiddleware`, rate limiter di `AppServiceProvider`, validasi `UnggahResepRequest` (magic bytes) & `VerifikasiResepRequest`, serta `EndToEndOrderFlowTest`. |

---

## Arsitektur Sistem & Struktur Kode Nyata

```text
app/
├── Console/Commands/
│   └── AuditTenantIsolationCommand.php      # Artisan: php artisan tenant:audit
├── Http/
│   ├── Controllers/
│   │   └── Auth/                            # EmailOtpAuthController, FirebaseAuthSessionController
│   ├── Middleware/
│   │   ├── EnsureUserHasRole.php            # Validasi role & status aktif di sisi server (403 jika tidak cocok)
│   │   ├── ResolveTenantContext.php         # Resolusi tenant/cabang per request (/klien/{client}, /cabang/{client})
│   │   └── SecurityHeadersMiddleware.php    # X-Frame-Options, CSP (Reverb + Firebase), Permissions-Policy, dll.
│   └── Requests/
│       ├── Customer/UnggahResepRequest.php  # Validasi file resep maks 5 MB + cek MIME Magic Bytes (finfo)
│       └── Tenant/VerifikasiResepRequest.php# Sanitasi tag HTML (XSS) catatan & alasan penolakan apoteker
├── Models/                                  # 18 Model Eloquent berbasis UUID
│   ├── Client, Pengguna, User, SuperAdmin, AdminClient, PeranClientPengguna, PetaPengawasStaf
│   ├── KategoriObat, SubkategoriObat, Obat, StokClient
│   ├── Pesanan, ItemPesanan, Pembayaran, Resep, PermintaanRetur
│   └── RingkasanPeriodePenjualan, LogAudit
├── Modules/                                 # 6 Bounded Context (Modular Monolith)
│   ├── Admin/                               # AdminServiceProvider, StaffController, StaffManagementService
│   ├── Catalog/                             # CatalogServiceProvider, PublicCatalog (Livewire), CatalogService,
│   │                                        # StockFefoService, CustomerSessionService, GoApotikCatalogAssets
│   ├── Ordering/                            # OrderingServiceProvider, CartController, CheckoutController,
│   │                                        # OrderTrackingController, CartService, CreateOrderAction, CancelOrderAction
│   ├── Payments/                            # PaymentsServiceProvider, PaymentWebhookController,
│   │                                        # PaymentGatewayInterface, FakeQrisGateway, QrisEmvcoBuilder
│   ├── Prescription/                        # PrescriptionServiceProvider, VerifyPrescriptionAction
│   └── Reporting/                           # ReportingServiceProvider, SalesReportService,
│                                            # SalesReportExportService, CloseSalesPeriodAction
├── Policies/PesananPolicy.php               # Proteksi IDOR lintas cabang
└── Support/Routing/PortalRoutes.php         # Grup route tunggal per portal (customer, tenant, kasir, admin)
```

---

## Peta Portal, Hak Akses (RBAC) & Fitur Terverifikasi

Setiap portal diikat langsung di `bootstrap/app.php` via `App\Support\Routing\PortalRoutes` dan diverifikasi melalui `EnsureUserHasRole`. Selain pembatasan endpoint, dropdown **Menu Pengguna** dan halaman `/settings/profile` secara dinamis menyesuaikan daftar pintasan portal & CRUD melalui `User::profileActions()`.

| Role (`users.role`) | Portal Utama (`User::homeRoute()`) | Middleware & Prefix | Cakupan Fitur Utama |
| :--- | :--- | :--- | :--- |
| **`super_admin`** | `/admin/dashboard` | `auth`, `verified`, `role:super_admin` (`routes/admin.php`) | • Executive dashboard omzet & pesanan konsolidasi lintas cabang<br>• Tutup periode penjualan terkunci (`POST /admin/tutup-periode`) & ekspor CSV/PDF konsolidasi<br>• **CRUD Cabang & Pengaturan** (`/admin/cabang/{client}/pengaturan`, `jam_buka`, `radius_km`, tambah/hapus cabang)<br>• **CRUD Master Obat & Alokasi Stok Cabang** (`/admin/obat`)<br>• **CRUD Akun Staf, Role & Pengawasan APJ 5NF** (`/admin/cabang/{client}/staf`)<br>• **Audit Read-Only**: Cek Operasional Apoteker (`/admin/apoteker/{client?}`), Monitoring Progres Obat Live (`/admin/monitoring-progres`), Ledger Kasir Konsolidasi (`/admin/kasir-ledger`) |
| **`apoteker` / `tenant`** | `/cabang/{client}/dashboard` | `auth`, `verified`, `role:apoteker`, `tenant` (`routes/tenant.php`) | • Dashboard Apoteker Cabang (terisolasi tenant via `TenantScope`)<br>• **Telaah Resep Dokter** (`/cabang/{client}/resep`, validasi nomor SIP `503/SIP/2024`, pencatatan `LogAudit`)<br>• **Manajemen Stok & Batch FEFO** (`/cabang/{client}/stok-fefo`, tambah stok, penyesuaian stok, peringatan dini H-30 & kritis H-7)<br>• **Antrean Loket & Walk-In** (`/cabang/{client}/antrean`, progresi status `menunggu_pembayaran` -> `diproses` -> `siap_diambil` -> `selesai` dengan broadcast Reverb, panggil nomor antrean +1)<br>• Rekam Data Pasien Cabang, Laporan & Tutup Periode Cabang, Panduan SOP Farmasi |
| **`kasir` / `admin` / `staf_klien`** | `/kasir/dashboard` | `auth`, `verified`, `role:kasir` (`routes/kasir.php`) | • Dashboard Kasir & POS Loket Cabang<br>• Pencarian obat cepat POS (`/kasir/pos-obat-search`)<br>• Input transaksi **Walk-In Tunai / QRIS** (`/kasir/transaksi-walkin`)<br>• Pelunasan pesanan menunggu bayar (`/kasir/pesanan/{id}/lunasi`) & pembatalan transaksi (`/kasir/pesanan/{id}/batalkan`)<br>• **Buku Ledger Kasir** (`/kasir/ledger`) & **Cetak Struk Pembayaran** (`/kasir/struk/{id}`) |
| **`pelanggan` / `customer`** | `/dashboard` | Publik/Session + `tenant` (`routes/web.php`, `routes/customer.php`) | • Etalase Katalog GoApotik (`PublicCatalog` Livewire): `7` kategori induk, filter subkategori, filter jenis sediaan, badge golongan BPOM, live search autocomplete (`/api/search-obat`), & tukar cabang aktif (`/cabang/switch`)<br>• Keranjang terisolasi per cabang (`cart.*` / `keranjang.*`)<br>• Checkout **Click & Collect** (slot jam ambil) atau **Delivery Kurir Apotek** (kalkulasi jarak Haversine, parsing link/koordinat Google Maps, & ongkir otomatis: <= `2 km` Rp `5.000`, selanjutnya `+Rp 2.500/km`)<br>• Pembayaran **QRIS Dinamis** (Midtrans Sandbox Core API + fallback EMVCo lokal, timer countdown persisten `30` menit, webhook `/api/webhooks/qris`, cek status, & batal pesanan)<br>• Pelacakan Pesanan & Antrean Virtual Real-Time via Laravel Echo (`order.{id}`) serta Unggah Resep Dokter Digital |

---

## Autentikasi, Keamanan & Optimasi Performa

1. **Autentikasi Multi-Jalur:**
   - **Laravel Fortify:** Login standar email & kata sandi, registrasi, verifikasi email, reset kata sandi, *Two-Factor Authentication* (2FA + recovery codes), dan login tanpa kata sandi dengan **Passkeys** (`@laravel/passkeys`).
   - **Email OTP:** Pengiriman, verifikasi, dan registrasi berbasis kode OTP (`/auth/send-otp`, `/auth/verify-otp`, `/auth/register-otp`) dengan pembatasan laju permintaan (`throttle:5,1` & `throttle:10,1`).
   - **Firebase Google 1-Click:** Autentikasi sisi klien menggunakan Firebase JS SDK (`resources/js/firebase-auth.js`), sinkronisasi sesi ke Laravel (`/auth/firebase-session`), serta halaman kelengkapan profil awal (`/auth/complete-profile`).

2. **Pengerasan Keamanan (*Security Hardening*):**
   - `SecurityHeadersMiddleware`: Menyuntikkan `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, `Referrer-Policy`, `Permissions-Policy`, dan `Content-Security-Policy` yang terkonfigurasi untuk WebSocket Reverb (`ws://`/`wss://`) serta Firebase Auth.
   - `AppServiceProvider`: Pembatas laju (`RateLimiter`) khusus `api` (`60/menit`), `broadcasting` (`30/menit`), dan `transaksi` (`15/menit`).
   - Integritas Data: Pessimistic database lock (`lockForUpdate`) pada stok saat checkout (`CreateOrderAction`), cache lock idempotensi `30` detik saat submit checkout (`CheckoutController`), validasi magic bytes gambar resep (`UnggahResepRequest`), dan sanitasi Formula Injection saat ekspor CSV (`SalesReportExportService`).

3. **Konsistensi Tema Visual & Optimasi Kecepatan:**
   - Halaman sambutan (`resources/views/welcome.blade.php`) dan seluruh layout autentikasi (`layouts/auth/{card,simple,split}.blade.php`) menjaga konsistensi tema terang medis (`data-force-light="true"`, latar `#F8FAFC`/`#FFFFFF`, palet Deep Forest Spruce & Emerald), sedangkan portal operasional internal mendukung mode terang/gelap lewat `partials/omni-theme-script.blade.php`.
   - Optimasi performa backend mencakup memoisasi slug cabang pada `User::getClientSlug()`, agregasi ringkasan kedaluwarsa di level SQL pada `StockFefoService::getExpirySummary()`, serta proteksi dispatch non-blocking bila server Reverb tidak aktif.

---

## Akun Demo Terverifikasi (`DemoApotekSeeder`)

Seluruh akun demo di bawah ini dibuat otomatis oleh `database/seeders/DemoApotekSeeder.php` dan disinkronkan ke tabel `users` (kata sandi seluruh akun: **`password`**):

| Role | Email Login | Cabang Terhubung | Kata Sandi |
| :--- | :--- | :--- | :---: |
| **Super Admin Pusat** | `daffa@omnipharma.test` | Kantor Pusat & Lintas Cabang | `password` |
| **Apoteker Banyuwangi** | `bambang@omnipharma.test` | Cabang Banyuwangi Kota (`banyuwangi-kota`) | `password` |
| **Apoteker Rogojampi** | `dewi@omnipharma.test` atau `dinda@omnipharma.test` | Cabang Rogojampi (`rogojampi`) | `password` |
| **Kasir Banyuwangi** | `kasir.bwi@omnipharma.test` | Cabang Banyuwangi Kota (`banyuwangi-kota`) | `password` |
| **Pelanggan / Pasien** | `gunturafandy5@gmail.com` / `candi@gmail.com` | Pelanggan Publik (Default `banyuwangi-kota`) | `password` |

---

## Panduan Instalasi & Menjalankan Secara Lokal

### 1. Setup Dependensi & Environment
```bash
git clone https://github.com/heliosparta18/startup.git
cd startup

composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Konfigurasi `.env`
Sesuaikan koneksi database MySQL/MariaDB dan Redis di `.env`:
```env
APP_NAME="OmniPharma Network"
APP_URL=http://localhost:8000

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
*(Opsional: tambahkan `MIDTRANS_MERCHANT_ID`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_SERVER_KEY`, dan `MIDTRANS_IS_PRODUCTION=false` untuk memakai Midtrans Sandbox QRIS secara live; jika dikosongkan, sistem otomatis memakai fallback emulator QRIS EMVCo lokal.)*

### 3. Migrasi, Seeder & Build Aset
```bash
# Seeding standar (2 cabang, akun demo, dan 42 produk terkurasi GoApotik)
php artisan migrate:fresh --seed

# Opsional: muat seluruh 1.576 produk katalog lengkap GoApotik
php artisan db:seed --class=GoApotikFullCatalogSeeder

# Kompilasi aset produksi Tailwind CSS v4 + Vite
npm run build
```

### 4. Menjalankan Layanan Aplikasi
- **Cara standar (Laravel Artisan & Vite):**
  ```bash
  # Terminal 1 — Web server Laravel
  php artisan serve

  # Terminal 2 — Build mode pengembangan (opsional jika sudah npm run build)
  npm run dev

  # Terminal 3 — WebSocket server real-time (opsional untuk live status tracking)
  php artisan reverb:start
  ```
- **Catatan Performa di Windows:** Pada Windows, `php artisan serve` berjalan *single-threaded* dan bisa terasa lambat saat browser membuka banyak request paralel atau menyelesaikan DNS `localhost` (`[::1]` vs `127.0.0.1`). Untuk performa lokal yang responsif (~100 ms per halaman), disarankan menjalankan **Nginx** yang mendengarkan `127.0.0.1` dan `[::1]` di belakang beberapa worker `php-cgi` dengan ekstensi `opcache` aktif.

---

## Verifikasi & Quality Gate

```bash
# 1. Jalankan seluruh unit & feature test (SQLite in-memory sesuai phpunit.xml)
php vendor/bin/phpunit

# 2. Analisis statis PHPStan / Larastan Level 7
php -d memory_limit=1G vendor/bin/phpstan analyse --no-progress

# 3. Pemeriksaan gaya kode PSR-12 dengan Laravel Pint
# (Di Windows dengan OPcache CLI aktif, jalankan single-process tanpa --parallel)
php vendor/bin/pint --test

# 4. Audit isolasi multi-tenant
php artisan tenant:audit
```
