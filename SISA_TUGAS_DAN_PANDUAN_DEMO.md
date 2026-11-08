# 📋 Panduan Penyelesaian Sisa Tugas (Backlog 5%) & Skenario Demo 100%
### Proyek Praktikum Pengembangan Sistem Informasi — OmniPharma Network
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Status Integrasi:** 95% Ter-merge di `main` | **Target:** 100% Siap Demo & Lulus Sempurna

Dokumen ini adalah panduan pembagian tugas sisa (*actionable checklist*) untuk masing-masing anggota kelompok agar sistem genap 100% sesuai Modul Praktikum, Mockup 4 Layar, dan dokumen SRS.

---

## 🎯 Ringkasan Status Proyek Saat Ini
- **Fondasi Modular Monolith & Multi-Tenancy (Daffa):** 100% SELESAI
- **Keranjang Redis, Checkout Atomik & QRIS Sandbox (Guntur):** 100% SELESAI
- **Katalog Livewire, Sesi Tamu & Telaah FEFO (Arya):** 92% SELESAI
- **Real-Time Reverb, Analitik Finansial & Hardening (Candi):** 92% SELESAI
- **Sisa Pekerjaan:** Tinggal **5%** berupa wiring aksi admin, 2 file FormRequest sanitasi, tampilan UI penugasan APJ, dan 4 berkas automated test suite.

---

## 🎨 Audit Desain UI FigCraft (22 Artboard): Yang Selesai vs Belum Selesai

Pada dokumen `docs/PRD_DESIGN_AND_RULES.md`, sistem telah memiliki spesifikasi **22 Master Artboard** di FigCraft (Desktop 1440×900 & Mobile 390×844) dengan palet resmi *Pharmly Deep Spruce* (`#0A231E`) dan aksen *Chartreuse Lime* (`#D4F94E`). Berikut adalah audit kesesuaian antara desain visual dan kode Blade di Laravel:

### A. Desain yang SUDAH Selesai Diimplementasikan ke Web (Blade Views)
1. **Overview (Super Admin Executive)**
   - Artboard FigCraft: Desktop `ID 4676` | Mobile `ID 4759`
   - Berkas Kode: `resources/views/admin/dashboard.blade.php` (KPI Card, Agregasi Cabang, Tabel Omzet).
2. **Katalog Obat (Products & Inventory)**
   - Artboard FigCraft: Desktop `ID 5024` | Mobile `ID 5213`
   - Berkas Kode: `resources/views/customer/home.blade.php`, `catalog.blade.php`, `obat-detail.blade.php` (Filter kategori, badge obat, cek stok multi-cabang).
3. **Antrean Resep (Orders & FEFO Review)**
   - Artboard FigCraft: Desktop `ID 4549` | Mobile `ID 4641`
   - Berkas Kode: `resources/views/tenant/dashboard.blade.php` (Validasi SIP dokter, modal approve/reject, kartu peringatan batch FEFO H-23 hari).
4. **Penjualan & Kasir (Sales & Cashier Checkout)**
   - Artboard FigCraft: Desktop `ID 5288` | Mobile `ID 5420`
   - Berkas Kode: `resources/views/customer/cart.blade.php` & `checkout.blade.php` (Keranjang Redis & Checkout Click & Collect).
5. **Pembayaran QRIS Dinamis Sandbox (EMVCo)**
   - Artboard FigCraft: Desktop `ID 4792` | Mobile `ID 4815`
   - Berkas Kode: `resources/views/customer/payment.blade.php` (QRIS string EMVCo, countdown timer 15m, modal simulasi webhook).
6. **Pelacakan Pesanan Waktu-Nyata (Reverb WebSockets)**
   - Artboard FigCraft: Terintegrasi pada alur pesanan
   - Berkas Kode: `resources/views/customer/order-track.blade.php` (Live order stepper Reverb).
7. **Autentikasi (Login & Registrasi)**
   - Artboard FigCraft: Desktop `ID 4839, 4935` | Mobile `ID 4897, 4991`
   - Berkas Kode: `resources/views/pages/auth/login.blade.php` & `register.blade.php`.

---

### B. Desain yang BELUM Selesai / Belum Ada Tampilan Kodenya (Design Backlog)

Terdapat **5 desain antarmuka** yang ada di perancangan FigCraft / SRS namun belum memiliki berkas view web di Laravel:

#### 1. [Prioritas P0 - Wajib Demo] Antarmuka Penugasan Staf APJ (M05)
- **Status:** Backend `StaffController` dan `StaffManagementService` sudah selesai, namun **belum ada view Blade**.
- **Kebutuhan Berkas:** `resources/views/admin/staff-management.blade.php`
- **Fitur Desain:** Tabel daftar staf apotek, dropdown hierarki pengawasan 5NF antara Apoteker Penanggung Jawab (APJ) dan Asisten Apoteker/Kasir, dan tombol simpan penugasan.
- **Penanggung Jawab:** **Arya Dwi Mahendra**.

#### 2. [Prioritas P1 - Fitur Tambahan] Modul Rekam Data Pasien (Customers / Patients)
- **Status:** Belum ada view Blade di portal tenant/admin.
- **Artboard FigCraft:** Desktop `ID 5477` | Mobile `ID 5602`
- **Fitur Desain:** Tabel daftar pasien, nomor kontak/email, riwayat penebusan resep obat keras, dan verifikasi identitas pasien.
- **Catatan:** Sifatnya sekunder (*nice-to-have* jika waktu cukup sebelum demo).

#### 3. [Prioritas P1 - Fitur Tambahan] Modul Buku Kasir & Rekonsiliasi (Payments Ledger)
- **Status:** Saat ini data transaksi menempel di laporan analitik umum (`laporan-penjualan.blade.php`), belum ada sub-halaman ledger kasir terpisah.
- **Artboard FigCraft:** Desktop `ID 5653` | Mobile `ID 5759`
- **Fitur Desain:** Rekonsiliasi mutasi kas harian, rincian pembayaran QRIS vs Tunai/Manual, dan selisih kas per kasir.

#### 4. [Prioritas P1 - Fitur Tambahan] Modul Bantuan & SOP Regulasi Farmasi (Help & Support)
- **Status:** Belum ada berkas view di web.
- **Artboard FigCraft:** Desktop `ID 5811` | Mobile `ID 5896`
- **Fitur Desain:** Halaman panduan regulasi penyerahan obat Permenkes RI No. 73/2016, batas legalitas SIP dokter, dan SOP penanganan obat kedaluwarsa.

#### 5. [Prioritas P1 - Fitur Tambahan] Modul Pengaturan Cabang (Settings & Branch Config)
- **Status:** Belum ada view khusus konfigurasi cabang apotek.
- **Artboard FigCraft:** Desktop `ID 5938` | Mobile `ID 6009`
- **Fitur Desain:** Form pengaturan nama apotek klien, alamat, titik koordinat radius pengiriman, dan status buka/tutup cabang.

---

## 👥 Pembagian Tugas Teknis & Checklist per Anggota

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                             PEMBAGIAN TUGAS                                 │
├─────────────────┬──────────────────────────────┬────────────────────────────┤
│ Nama Anggota    │ Fokus Modul Praktikum        │ Estimasi Waktu             │
├─────────────────┼──────────────────────────────┼────────────────────────────┤
│ Candia Purwa    │ Admin Reporting & Sanitasi   │ ~30 - 45 Menit             │
│ Arya Dwi M.     │ UI Staf APJ & Test FEFO      │ ~30 - 45 Menit             │
│ Guntur Afandi   │ Polish Redirect QRIS Tracking│ ~15 - 20 Menit             │
│ M. Daffa Ariby  │ Seeder Demo & Orkestrator    │ ~20 - 30 Menit             │
└─────────────────┴──────────────────────────────┴────────────────────────────┘
```

---

### 1. CANDIA PURWA (Real-Time, Reporting & Hardening)
> **Branch Git:** `fix/candi-admin-wiring-and-hardening`

#### ✅ Tugas 1: Sambungkan Rute Admin ke Service Asli (`routes/admin.php`)
Saat ini rute tutup periode dan ekspor laporan di `routes/admin.php` masih mengembalikan *JSON statis (mock)*. Sambungkan ke service yang sudah kamu buat sebelumnya di modul Reporting:
- **`POST /admin/tutup-periode`**: Panggil `App\Modules\Reporting\Actions\CloseSalesPeriodAction::execute()`.
- **`GET /admin/ekspor-laporan`**: Panggil `App\Modules\Reporting\Services\SalesReportExportService::exportCsv()`.

#### ✅ Tugas 2: Buat 2 Berkas FormRequest Sanitasi Khusus
Pindahkan validasi inline request ke berkas FormRequest terdedikasi untuk perlindungan XSS dan keamanan file:
1. **`app/Http/Requests/Customer/UnggahResepRequest.php`**
   - Validasi berkas foto resep: `required|file|mimes:jpeg,png,jpg|max:5120`.
   - Cek magic bytes untuk mencegah unggahan script berbahaya berkedok ekstensi gambar.
2. **`app/Http/Requests/Tenant/VerifikasiResepRequest.php`**
   - Validasi keputusan: `required|in:disetujui,ditolak`.
   - Sanitasi input `catatan`: bersihkan tag HTML berbahaya menggunakan `strip_tags()` untuk mencegah serangan Cross-Site Scripting (XSS).

#### ✅ Tugas 3: Buat Automated Test E2E (`tests/Feature/EndToEndOrderFlowTest.php`)
- Uji alur lengkap hulu ke hilir: Pasien pesan obat keras $\rightarrow$ apoteker menyetujui resep $\rightarrow$ bayar QRIS $\rightarrow$ status maju via Reverb $\rightarrow$ penutupan buku kas admin berhasil mengunci snapshot.

---

### 2. ARYA DWI MAHENDRA (Operasional Cabang, Resep & FEFO)
> **Branch Git:** `fix/arya-staff-ui-and-fefo-tests`

#### ✅ Tugas 1: Buat View Admin Penugasan Staf APJ (`resources/views/admin/staff-management.blade.php`)
- Buat tampilan tabel sederhana di portal super admin untuk menampilkan daftar staf dan Apoteker Penanggung Jawab (APJ) per cabang apotek.
- Form penugasan sederhana: Dropdown pilih Staf, dropdown pilih Apoteker Supervisor (APJ), tombol "Simpan Penugasan".
- Sambungkan rute di `routes/admin.php`:
  ```php
  use App\Modules\Admin\Http\Controllers\StaffController;
  Route::get('/staf', [StaffController::class, 'index'])->name('staff.index');
  Route::post('/staf/assign', [StaffController::class, 'assignSupervisor'])->name('staff.assign');
  ```

#### ✅ Tugas 2: Buat 3 File Test Suite Domain Operasional Apotek
1. **`tests/Feature/GuestSessionAndCatalogTest.php`**
   - Uji pembuatan cookie token buram 32-karakter (`omnipharma_customer_session`) dan pencarian obat di katalog Livewire.
2. **`tests/Feature/PrescriptionVerificationTest.php`**
   - Uji verifikasi nomor SIP dokter (contoh format `503/SIP/2024`) serta aksi setujui/tolak resep oleh apoteker.
3. **`tests/Feature/FefoStockRotationTest.php`**
   - Uji query pengurutan stok berbasis FEFO (`orderBy('expired', 'asc')`) dan trigger peringatan dini batch H-30 hari.

---

### 3. GUNTUR AFANDI (Commerce Pipeline & Payment Gateway)
> **Branch Git:** `fix/guntur-payment-track-polish`

#### ✅ Tugas 1: Polish Alur Pembayaran ke Pelacakan Real-time
- Buka `resources/views/customer/payment.blade.php`.
- Pastikan setelah tombol **"Simulasi Pelunasan Webhook (Demo)"** selesai dijalankan, antarmuka otomatis memunculkan tombol mencolok atau auto-redirect ke halaman pelacakan pesanan:
  `route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan])`
- Hal ini agar saat presentasi di depan Pak Devit, setelah klik bayar, dosen langsung melihat animasi stepper pelacakan Reverb WebSockets yang maju secara otomatis.

#### ✅ Tugas 2: Verifikasi Timer Kedaluwarsa QRIS 15 Menit
- Pastikan countdown timer di halaman QRIS berjalan lancar dan mengarah ke pembatalan otomatis jika waktu 15 menit habis tanpa menimbulkan error loop.

---

### 4. MUHAMMAD DAFFA ARIBY (Lead Architect & Orkestrasi Demo)
> **Branch Git:** `chore/daffa-demo-seeder-orchestration`

#### ✅ Tugas 1: Pastikan Dummy Data Demo Langsung Muncul di Seeder (`DemoApotekSeeder.php`)
- Pastikan saat `php artisan migrate:fresh --seed` dijalankan, basis data langsung terisi:
  1. Minimal 1 pesanan obat keras berstatus `menunggu_verifikasi` dengan foto resep dr. Budi Sp.PD (SIP: `503/SIP/2024`).
  2. Batch obat Amodiaquine dengan tanggal kedaluwarsa H-23 hari (Batch `#BTC-2024A`), sehingga banner peringatan kuning FEFO langsung menyala di dashboard apoteker tanpa perlu di-setup ulang.

#### ✅ Tugas 2: Review & Merge Pull Request
- Menjalankan format kode sebelum penggabungan akhir:
  ```bash
  vendor/bin/pint
  ```
- Menjalankan seluruh test suite otomatis:
  ```bash
  php artisan test
  ```

---

## 🚀 Alur Kerja Git (Biar Gak Nabrak Merge Conflict)

1. **Tarik Kode Terbaru:**
   ```bash
   git checkout main
   git pull origin main
   ```
2. **Buat Branch Sesuai Nama Masing-masing:**
   ```bash
   git checkout -b fix/<nama-tugas>
   ```
3. **Kerjakan Berkas Sesuai Bagian Masing-Masing (Jangan Sentuh Berkas Anggota Lain).**
4. **Commit & Push ke GitHub:**
   ```bash
   git add .
   git commit -m "feat/fix: selesaikan tugas sisa modul <nama_modul>"
   git push origin fix/<nama-tugas>
   ```
5. **Buat Pull Request (PR) ke `main` dan minta Daffa untuk merge.**

---

## 🎬 Skenario Demonstrasi di Depan Pak Devit (5 Menit Selesai)

Gunakan skenario urutan ini saat demo:

1. **Layar 1 — Pelanggan (Buka `http://127.0.0.1:8000/klien/banyuwangi-kota`):**
   - Tunjukkan badge obat keras "WAJIB RESEP DOKTER" pada obat Amodiaquine.
   - Klik obat, tunjukkan ketersediaan stok cabang Banyuwangi Kota (12 strip) vs Rogojampi (Kosong).
   - Klik "+ Unggah Resep", masukkan resep dokter, dan submit.
2. **Layar 2 — Portal Apoteker (Buka `http://127.0.0.1:8000/cabang/banyuwangi-kota/dashboard`):**
   - Tunjukkan antrean verifikasi resep masuk dari pelanggan tadi.
   - Tunjukkan verifikasi nomor SIP dokter terverifikasi.
   - Tunjukkan peringatan **Batch Kedaluwarsa #1 FEFO (H-23 hari)**.
   - Klik **"Setujui Resep"**.
3. **Layar 3 — Pembayaran QRIS Dinamis (Layar Pelanggan):**
   - Buka halaman pembayaran, tunjukkan string QRIS standar EMVCo dan countdown 15 menit.
   - Klik tombol demo **"Simulasi Pelunasan Webhook"**.
   - Halaman otomatis beralih ke Pelacakan Pesanan Real-time (Reverb WebSockets) dengan status maju ke *Diproses*.
4. **Layar 4 — Super Admin Executive (Buka `http://127.0.0.1:8000/admin/dashboard`):**
   - Tunjukkan agregasi omzet periode berjalan lintas cabang.
   - Klik tombol **"Tutup Periode & Kunci Snapshot"** (Tunjukkan bukti data terkunci permanen sesuai ADR-0008).
   - Klik **"Ekspor Laporan"** (File CSV laporan finansial terunduh langsung).
