# 📋 Peta Pembagian Sisa Tugas Anggota & Rencana Kolaborasi Proyek
### OmniPharma Network — Sistem Informasi Apotek Multi-Klien
> **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi  
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Status Proyek Saat Ini:** **98% Selesai & Ter-merge di `main`** | **120/120 Tests Passed** | **PHPStan Level 7 Passed**  
> **Target:** 100% Sempurna, Bebas Celah Keamanan, & Lulus Nilai A

---

## 🎯 Ringkasan Status Eksekutif

Seluruh 15 milestone modul praktikum inti (M01 s/d M14) serta perbaikan kritis alur QRIS Reverb, integrasi Firebase Auth, Google 1-Click login, verifikasi OTP email, halaman landing sambutan, dan optimasi mobile responsif telah selesai dan terintegrasi di branch `main`.

Sisa pekerjaan saat ini tinggal **2%** berupa pengerasan sanitasi FormRequest, pembuatan 1 test suite E2E terpadu, ekstraksi closure controller, dan sub-halaman sekunder (P1) dari desain FigCraft.

---

## 👥 Rincian Tugas per Individu (Dikerjakan Mandiri)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                              MATRIKS TANGGUNG JAWAB INDIVIDU                           │
├───────────────────┬───────────────────────────────────────────┬────────────┬───────────┤
│ Anggota           │ Fokus Domain                              │ Prioritas  │ Estimasi  │
├───────────────────┼───────────────────────────────────────────┼────────────┼───────────┤
│ **Candia Purwa**  │ FormRequest Sanitasi & Test E2E Terpadu   │ **P0**     │ 30-45 mnt │
│ **Guntur Afandi** │ Refactor Controller & Idempotensi Checkout│ **P0/P1**  │ 30-45 mnt │
│ **Arya Dwi M.**   │ Layar Rekam Medis Pasien & SOP Regulasi   │ **P1**     │ 30-40 mnt │
│ **M. Daffa A.**   │ Sidebar Layout & Pengaturan Cabang Apotek │ **P1**     │ 30-40 mnt │
└───────────────────┴───────────────────────────────────────────┴────────────┴───────────┘
```

---

### 1. CANDIA PURWA (Quality Assurance & Security Hardening Lead)
> **Branch Kerja Mandiri:** `feat/candi-formrequest-and-e2e`

#### 📌 Tugas 1: Buat FormRequest Sanitasi Magic Bytes Unggah Resep
- **Berkas Target:** `app/Http/Requests/Customer/UnggahResepRequest.php`
- **Tujuan:** Mencegah serangan pengunggahan file jahat (*malicious file upload*) berkedok gambar.
- **Spesifikasi:**
  - Validasi file: `required|file|mimes:jpeg,png,jpg|max:5120`.
  - Tambahkan validasi *magic bytes* atau MIME header asli menggunakan `finfo_file()` untuk memastikan file benar-benar berkas gambar asli dan bukan file skrip berbahaya (`.php`, `.sh`, `.exe`).
  - Sambungkan ke endpoint `POST /resep/unggah` di `routes/customer.php`.

#### 📌 Tugas 2: Buat FormRequest Sanitasi XSS Catatan Apoteker
- **Berkas Target:** `app/Http/Requests/Tenant/VerifikasiResepRequest.php`
- **Tujuan:** Mencegah serangan *Stored Cross-Site Scripting* (XSS) pada riwayat resep pasien.
- **Spesifikasi:**
  - Validasi: `action` wajib bernilai `in:disetujui,ditolak`.
  - Sanitasi field `catatan`: Wajib membersihkan tag HTML berbahaya menggunakan `strip_tags()` atau `htmlspecialchars()`.
  - Sambungkan ke endpoint `POST /resep/{id}/verifikasi` di `routes/tenant.php`.

#### 📌 Tugas 3: Buat Automated Test E2E Terpadu
- **Berkas Target:** `tests/Feature/EndToEndOrderFlowTest.php`
- **Tujuan:** Membuktikan ke penguji bahwa sistem bekerja tanpa putus dari hulu ke hilir dalam 1 skenario pengujian otomatis.
- **Alur Test:**
  1. Pasien tamu membuat sesi di cabang Banyuwangi Kota dan mengunggah resep obat keras.
  2. Apoteker login dan menyetujui resep via `VerifyPrescriptionAction` dengan nomor SIP `503/SIP/2024`.
  3. Pasien checkout dan membayar dengan QRIS Sandbox.
  4. Webhook settlement memicu event Reverb `OrderStatusUpdated`.
  5. Admin pusat menutup periode kas via `CloseSalesPeriodAction` $\rightarrow$ snapshot omzet terkunci permanen (ADR-0008).

---

### 2. GUNTUR AFANDI (Core Commerce & Payment Gateway Engineer)
> **Branch Kerja Mandiri:** `feat/guntur-controller-refactor`

#### 📌 Tugas 1: Refactor Routing Closure ke Controller Modular
- **Target File:** `routes/customer.php`
- **Tujuan:** Memindahkan logika bisnis dari inline closure routing ke controller modular di Bounded Context `Ordering`.
- **Spesifikasi Controller yang Dibuat:**
  1. `app/Modules/Ordering/Http/Controllers/CartController.php`:
     - Method `index()`: Menampilkan keranjang belanja Redis.
     - Method `add()`, `update()`, `remove()`: Operasi manipulasi item keranjang.
  2. `app/Modules/Ordering/Http/Controllers/CheckoutController.php`:
     - Method `index()`: Halaman konfirmasi checkout.
     - Method `process()`: Eksekusi transaksi atomik pesimistik `CreateOrderAction`.
  3. `app/Modules/Ordering/Http/Controllers/OrderTrackingController.php`:
     - Method `payment()`: Halaman pembayaran QRIS dinamis EMVCo.
     - Method `cancel()`: Pembatalan pesanan dan restorasi stok.
     - Method `track()`: Pelacakan waktu-nyata Reverb WebSockets.

#### 📌 Tugas 2: Proteksi Idempotensi Double-Submit Checkout
- **Tujuan:** Mencegah pembentukan transaksi ganda jika pasien mengklik tombol checkout berkali-kali saat koneksi lambat.
- **Implementasi:** Pasang cache lock idempotensi 30–60 detik (`Cache::add("checkout_lock:{$sessionId}", true, 60)`) pada awal proses checkout.

---

### 3. ARYA DWI MAHENDRA (Branch Operations & Compliance Engineer)
> **Branch Kerja Mandiri:** `feat/arya-patient-and-sop-views`

#### 📌 Tugas 1: Tampilan Rekam Data Pasien (FigCraft Artboard #5)
- **Berkas Target:** `resources/views/tenant/pasien.blade.php`
- **Rute:** `GET /cabang/{client}/pasien` (name: `tenant.pasien.index`)
- **Fitur Desain:**
  - Tabel daftar pasien apotek terdaftar.
  - Riwayat pembelian obat resep keras vs obat bebas.
  - Status nomor telepon dan alamat pengiriman.

#### 📌 Tugas 2: Tampilan Panduan SOP & Regulasi Kefarmasian (FigCraft Artboard #7)
- **Berkas Target:** `resources/views/tenant/bantuan.blade.php`
- **Rute:** `GET /cabang/{client}/bantuan` (name: `tenant.bantuan.index`)
- **Fitur Desain:**
  - Panduan standar penyerahan obat Permenkes RI No. 73/2016.
  - Aturan penyimpanan obat termolabil (rantai dingin 2–8°C) dan lemari narkotika/psikotropika.
  - SOP penanganan obat kedaluwarsa (#1 FEFO) dan format absah nomor SIP dokter.

---

### 4. MUHAMMAD DAFFA ARIBY (Lead Architect & Multi-Tenancy Core)
> **Branch Kerja Mandiri:** `feat/daffa-sidebar-and-branch-settings`

#### 📌 Tugas 1: Komponen Navigasi Sidebar Responsif (Admin & Tenant)
- **Target File:** `resources/views/layouts/admin.blade.php` dan `layouts/tenant.blade.php`
- **Tujuan:** Menyelaraskan navigasi web dengan 8 menu sidebar pada master artboard FigCraft.
- **Menu Navigasi:**
  - *Portal Admin:* Overview Omzet, Penugasan Staf APJ, Laporan Penjualan CSV, Pengaturan Cabang.
  - *Portal Apoteker:* Dashboard Telaah, Antrean Resep Masuk, Manajemen Batch FEFO, Rekam Pasien, SOP Bantuan.

#### 📌 Tugas 2: Tampilan Pengaturan Cabang Apotek (FigCraft Artboard #8)
- **Berkas Target:** `resources/views/admin/pengaturan-cabang.blade.php`
- **Rute:** `GET /cabang/{client}/pengaturan`
- **Fitur Desain:**
  - Form nama apotek, alamat fisik, titik koordinat radius layanan ($< 5\text{ km}$).
  - Toggle status cabang: Buka / Tutup Operasional.

---

## 🤝 Tugas yang Bisa Dikerjakan Barengan (Kolaboratif)

Pekerjaan berikut sangat disarankan dikerjakan bersama saat kumpul kelompok:

### 1. 🎬 Gladi Resik Skenario Demo 4 Layar (Wajib Semua Anggota — 15 Menit)
Uji coba urutan presentasi di depan Pak Devit agar alur berjalan mulus:
1. **Layar 1 (Pasien — Dikerjakan oleh Guntur):**  
   Buka `http://127.0.0.1:8088/klien/banyuwangi-kota` $\rightarrow$ Tunjukkan badge obat keras WAJIB RESEP $\rightarrow$ Cek stok cabang Bwi Kota vs Rogojampi $\rightarrow$ Unggah resep dokter dr. Budi Sp.PD.
2. **Layar 2 (Apoteker APJ — Dikerjakan oleh Arya):**  
   Buka `http://127.0.0.1:8088/cabang/banyuwangi-kota/dashboard` $\rightarrow$ Tunjukkan antrean resep masuk $\rightarrow$ Verifikasi nomor SIP dokter `503/SIP/2024` $\rightarrow$ Tunjukkan banner peringatan kuning **Batch Kedaluwarsa #1 FEFO (H-23 hari)** $\rightarrow$ Klik **"Setujui Resep"**.
3. **Layar 3 (Pembayaran QRIS Reverb — Dikerjakan oleh Candia & Guntur):**  
   Buka halaman pembayaran QRIS $\rightarrow$ Tunjukkan string standar EMVCo dan timer 15 menit $\rightarrow$ Klik tombol demo **"Simulasi Bayar Sekarang"** $\rightarrow$ Halaman otomatis beralih ke Pelacakan Pesanan Real-Time dengan status maju ke *Diproses* via Reverb WebSockets.
4. **Layar 4 (Super Admin Eksekutif — Dikerjakan oleh Daffa):**  
   Buka `http://127.0.0.1:8088/admin/dashboard` $\rightarrow$ Tunjukkan agregasi omzet lintas cabang $\rightarrow$ Klik tombol **"Tutup Periode & Kunci Snapshot"** (bukti immutability ADR-0008) $\rightarrow$ Klik **"Ekspor Laporan"** (CSV laporan finansial terunduh langsung).

### 2. 🛡️ Quality Gate Check Sebelum Presentasi (Bersama)
Jalankan perintah ini di laptop demo utama untuk memastikan status pengujian hijau 100%:
```bash
# 1. Jalankan seluruh automated tests (120+ tests)
php artisan test

# 2. Periksa kesesuaian tipe data statis
php vendor/bin/phpstan analyse --memory-limit=1G

# 3. Periksa kepatuhan format kode PSR-12
php vendor/bin/pint --test

# 4. Bersihkan dan kompilasi view Blade
php artisan view:clear && php artisan view:cache
```

---

## 🚀 Alur Kerja Git Bebas Konflik (Workflow Guidelines)

1. **Tarik Kode Terbaru Sebelum Mulai:**
   ```bash
   git checkout main
   git pull origin main
   ```
2. **Buat Branch Fitur Sesuai Nama:**
   ```bash
   git checkout -b feat/<nama-anggota>-<nama-fitur>
   ```
3. **Kerjakan File Milik Sendiri (Hindari mengedit file yang sedang diedit teman).**
4. **Commit & Push ke GitHub:**
   ```bash
   git add .
   git commit -m "feat(<scope>): selesaikan tugas <nama-fitur>"
   git push origin feat/<nama-anggota>-<nama-fitur>
   ```
5. **Buat Pull Request (PR) ke `main` dan diskusikan di grup sebelum di-merge.**
