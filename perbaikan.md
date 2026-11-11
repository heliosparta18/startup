# 🛠️ DOKUMEN PERBAIKAN SISTEM & PENYELARASAN SRS
### OmniPharma Network — Sistem Informasi Apotek Multi-Klien
> **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi  
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Status:** **Tervalidasi & Bersih dari File Legacy** | **100% Bebas Penambahan Kode Aplikasi Baru**  
> **Dokumen Acuan:** `references/SRS Apotek.docx` / `references/srs_apotek_extracted.txt` (Draf Fase 0 Revisi 2) & `references/PDM revisi.pdf`

---

## 📌 DAFTAR ISI
1. [Ringkasan Pembersihan Berkas (Non-SRS & Non-Logic)](#1-ringkasan-pembersihan-berkas-non-srs--non-logic)
2. [Hasil Audit Kesenjangan SRS vs Backlog Sisa Tugas](#2-hasil-audit-kesenjangan-srs-vs-backlog-sisa-tugas)
3. [Daftar Temuan Bug Front-End & Rekomendasi Solusi](#3-daftar-temuan-bug-front-end--rekomendasi-solusi)
4. [Peta Pembagian Sisa Tugas Anggota Terkini (100% SRS Compliant)](#4-peta-pembagian-sisa-tugas-anggota-terkini-100-srs-compliant)
5. [Quality Gate & SOP Kolaborasi Git](#5-quality-gate--sop-kolaborasi-git)

---

## 1. RINGKASAN PEMBERSIHAN BERKAS (NON-SRS & NON-LOGIC)
Sesuai instruksi untuk menghapus berkas apapun yang **tidak sesuai dengan SRS Apotek** dan **tidak sesuai dengan logic yang telah dibuat**, berkas-berkas warisan berikut telah dihapus dari repositori:

| Nama Berkas | Lokasi | Alasan Penghapusan & Ketidaksesuaian |
| :--- | :--- | :--- |
| `IMPLEMENTATION_MATRIX.md` | `docs/` | **Bukan Domain Apotek.** Berkas sisa materi lama (*"Kantin Multi-Tenant"*) yang mereferensikan modul tutorial kantin yang tidak ada di repositori. |
| `IMPLEMENTATION_STATUS.md` | `docs/` | **Bukan Domain Apotek.** Berkas pelacakan status proyek kantin (Agustus 2026) dengan referensi database `kantin_test` dan repositori lama. |
| `MODULE_REVISION_LOG.md` | `docs/` | **Bukan Domain Apotek.** Log catatan revisi modul praktikum kantin yang tidak relevan dengan arsitektur farmasi OmniPharma. |
| `routes_dump.json` | Root | **Berkas Sampah/Temporary.** Dump rute JSON sementara sisa proses audit developer yang tidak dibutuhkan aplikasi. |

> 💡 **Penyelarasan RTM:** Berkas `docs/REQUIREMENTS_TRACEABILITY.md` yang sebelumnya berisi teks template kantin **telah diperbarui total** menjadi matriks ketertelusuran kebutuhan resmi Apotek OmniPharma (memetakan seluruh FR-PEL-01..06, FR-APT-01..04, dan FR-ADM-01..04 ke kode implementasi dan unit tests riil).

---

## 2. HASIL AUDIT KESENJANGAN SRS VS BACKLOG SISA TUGAS

Berdasarkan perbandingan antara dokumen SRS Draf Fase 0 Revisi 2 ([`references/srs_apotek_extracted.txt`](./references/srs_apotek_extracted.txt)) dan kondisi codebase aktual pada commit `af062a4`:

1. **Status Tugas yang Sudah Selesai di `main` (Perlu Di-checklist di README):**
   - FormRequest Sanitasi Magic Bytes (`UnggahResepRequest.php`) $\rightarrow$ **Selesai**.
   - FormRequest Sanitasi XSS Catatan Apoteker (`VerifikasiResepRequest.php`) $\rightarrow$ **Selesai**.
   - Refactor Controller Modular Ordering (`CartController`, `CheckoutController`, `OrderTrackingController`) $\rightarrow$ **Selesai**.
   - Tampilan Rekam Data Pasien (`tenant/pasien.blade.php`) $\rightarrow$ **Selesai**.
   - Tampilan Panduan SOP Farmasi Permenkes (`tenant/bantuan.blade.php`) $\rightarrow$ **Selesai**.
   - Tampilan Pengaturan Cabang (`admin/pengaturan-cabang.blade.php`) $\rightarrow$ **Selesai**.
   - Automated Test Suite E2E Terpadu (`EndToEndOrderFlowTest.php`) $\rightarrow$ **Selesai**.

2. **Kebutuhan SRS yang Belum Tercantum di Backlog Sisa Tugas:**
   - **FR-PEL-06 & UC-11 (Click & Collect Scheduling):** Form checkout memerlukan input slot jadwal pengambilan (jam/slot waktu) saat metode "Ambil di Apotek" dipilih.
   - **FR-PEL-06 & Fitur Unggulan 2.3 (Nomor Antrean Virtual):** Halaman pelacakan pesanan memerlukan kartu nomor antrean virtual (misal: `#ANT-015`) dan estimasi waktu penyiapan obat.
   - **FR-PEL-03 & UC-03 (Riwayat Pesanan Pelanggan):** Pelanggan memerlukan halaman rekap transaksi pribadi (`/klien/{client}/pesanan`) untuk memantau seluruh riwayat pesanan.
   - **FR-PEL-01 & Fitur Unggulan 2.3 (Rekomendasi Cabang Lain):** Menampilkan ketersediaan stok obat di cabang apotek lain jika stok cabang aktif habis.
   - **FR-ADM-04 & UC-13 (Dinamisasi Pantauan Stok Kritis Multi-Cabang):** Menghubungkan tabel stok kritis di dashboard Super Admin dengan kueri database `StokClient` dinamis.

---

## 3. DAFTAR TEMUAN BUG FRONT-END & REKOMENDASI SOLUSI

Berikut adalah temuan audit front-end untuk dikerjakan masing-masing anggota di branch fitur mereka:

### 🔴 1. Crash HTTP 500 pada Halaman Laporan Penjualan Cabang
- **Lokasi File:** `resources/views/admin/laporan-penjualan.blade.php` (baris 1-3)
- **Penyebab:** Menggunakan sintaks `@extends('layouts.app')` yang memicu `ErrorException: Undefined variable $slot`.
- **Rekomendasi Solusi:** Ganti pembungkus layout dengan `<x-layouts.tenant :title="'Analitik Penjualan & Rekonsiliasi - OmniPharma'">` dan tutup dengan `</x-layouts.tenant>`.

### 🔴 2. Form Verifikasi Resep Mengembalikan Layar Putih Berisi Raw JSON
- **Lokasi File:** `routes/tenant.php` (baris 97-101) & `resources/views/tenant/verifikasi-resep.blade.php`
- **Penyebab:** Form disubmit secara native HTML form, namun backend selalu mengembalikan `response()->json(...)`.
- **Rekomendasi Solusi:** Tambahkan pengecekan:
  ```php
  if ($request->wantsJson()) {
      return response()->json([...]);
  }
  return back()->with('success', "Resep {$id} berhasil diverifikasi ({$action}).");
  ```

### 🔴 3. Tombol Ekspor Laporan di Super Admin Tidak Men-download File
- **Lokasi File:** `resources/views/admin/dashboard.blade.php` (baris 31-44)
- **Penyebab:** Callback fetch menerima payload JSON `data.download_url`, tetapi tidak pernah memanggil `window.location.href = data.download_url`.
- **Rekomendasi Solusi:** Tambahkan baris redirect pada handler JS agar berkas XLSX/CSV terunduh otomatis.

### ⚠️ 4. Nomor Telepon Hardcoded pada Form Checkout
- **Lokasi File:** `resources/views/customer/checkout.blade.php` (baris 93)
- **Penyebab:** Nilai `081234567890` di-hardcode di atribut `value="..."` alih-alih di atribut `placeholder="..."`.
- **Rekomendasi Solusi:** Ubah menjadi `value="{{ auth()->user()->phone ?? old('telepon', '') }}" placeholder="Contoh: 081234567890"`.

### ⚠️ 5. Browser Native `alert()` pada Kegagalan Webhook Pembayaran
- **Lokasi File:** `resources/views/customer/payment.blade.php` (baris 54)
- **Penyebab:** Masih menggunakan `alert('Gagal menyimulasikan webhook: ' + err.message);`.
- **Rekomendasi Solusi:** Ganti dengan banner notifikasi Alpine.js yang responsif.

---

## 4. PETA PEMBAGIAN SISA TUGAS ANGGOTA TERKINI (100% SRS COMPLIANT)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        MATRIKS TANGGUNG JAWAB PENYEMPURNAAN SRS                        │
├───────────────────┬───────────────────────────────────────────┬────────────┬───────────┤
│ Anggota           │ Fokus Domain Kepatuhan SRS                │ Prioritas  │ Estimasi  │
├───────────────────┼───────────────────────────────────────────┼────────────┼───────────┤
│ **Candia Purwa**  │ Agregasi Retur, Top Drugs & Kredensial DB │ **P0**     │ 20-30 mnt │
│ **Guntur Afandi** │ Jadwal Click & Collect & Riwayat Pesanan  │ **P0/P1**  │ 30-40 mnt │
│ **Arya Dwi M.**   │ Status Fulfillment & Rekomendasi Cabang   │ **P1**     │ 25-35 mnt │
│ **M. Daffa A.**   │ Dinamisasi Dashboard Admin & Sidebar Link │ **P1**     │ 20-30 mnt │
└───────────────────┴───────────────────────────────────────────┴────────────┴───────────┘
```

### 1. CANDIA PURWA (Financial Reporting & Quality Assurance Lead)
- [ ] **Tugas 1: Dinamisasi Agregasi Retur & 5 Obat Terlaris (FR-ADM-02)**
  - *Berkas:* `app/Modules/Reporting/Services/SalesReportService.php` dan `CloseSalesPeriodAction.php`
  - *Aksi:* Kueri tabel `permintaan_retur` dengan status `disetujui` untuk mengisi kolom `total_retur` (menggantikan nilai hardcode `0`). Tambahkan kalkulasi 5 obat terlaris di `item_pesanan`.
- [ ] **Tugas 2: Penyelarasan Kredensial Database Pengujian**
  - *Berkas:* `phpunit.xml`
  - *Aksi:* Pastikan konfigurasi database test selaras dengan environment lokal agar 120+ tests lulus 100%.

### 2. GUNTUR AFANDI (Commerce Pipeline & Customer Experience Lead)
- [ ] **Tugas 1: Pilihan Slot Jam Pengambilan Click & Collect (FR-PEL-06 / UC-11)**
  - *Berkas:* `resources/views/customer/checkout.blade.php` dan `CreateOrderAction.php`
  - *Aksi:* Tambahkan pilihan radio slot jam pickup (misal: "Hari ini, 13:00 - 15:00 WIB") saat metode "Ambil di Apotek" dipilih, dan simpan ke metadata pesanan.
- [ ] **Tugas 2: Antarmuka Nomor Antrean Virtual & Estimasi Giliran (FR-PEL-06 / Fitur 2.3)**
  - *Berkas:* `resources/views/customer/order-track.blade.php`
  - *Aksi:* Tampilkan kartu nomor antrean virtual (`#ANT-015`) dan estimasi waktu tunggu di atas stepper status Reverb.
- [ ] **Tugas 3: Halaman Riwayat Pesanan Pelanggan (FR-PEL-03 / UC-03)**
  - *Berkas:* `routes/customer.php` dan `resources/views/customer/orders-history.blade.php`
  - *Aksi:* Sediakan rute `GET /klien/{client}/pesanan` untuk menampilkan daftar pesanan pelanggan berdasarkan sesi/login.

### 3. ARYA DWI MAHENDRA (Branch Operations & Compliance Lead)
- [ ] **Tugas 1: Aksi Staf Apotek Memajukan Status Pesanan Reguler (FR-APT-01 / UC-05)**
  - *Berkas:* `routes/tenant.php` dan `resources/views/tenant/dashboard.blade.php`
  - *Aksi:* Sediakan tombol aksi bagi apoteker/staf untuk memajukan status pesanan dari `diproses` $\rightarrow$ `siap_diambil` $\rightarrow$ `selesai` (memicu event Reverb `OrderStatusUpdated`).
- [ ] **Tugas 2: Rekomendasi Stok Cabang Lain Saat Stok Obat Habis (FR-PEL-01 / Fitur 2.3)**
  - *Berkas:* `resources/views/customer/obat-detail.blade.php` dan `CatalogService.php`
  - *Aksi:* Jika stok obat bernilai 0 di cabang aktif, tampilkan banner ketersediaan obat di cabang apotek lain (contoh: *"Tersedia 30 strip di Cabang Rogojampi"*).
- [ ] **Tugas 3: Dinamisasi Data Tabel FEFO di Dashboard Staf**
  - *Berkas:* `resources/views/tenant/dashboard.blade.php`
  - *Aksi:* Ganti baris tabel statis dengan perulangan `@foreach($fefoBatches as $batch)` seperti yang sudah diterapkan di `tenant/stok-fefo.blade.php`.

### 4. MUHAMMAD DAFFA ARIBY (Lead Architect & Core Tenancy)
- [ ] **Tugas 1: Dinamisasi Agregasi Stok Kritis Multi-Cabang Super Admin (FR-ADM-04 / UC-13)**
  - *Berkas:* `resources/views/admin/dashboard.blade.php` dan `routes/admin.php`
  - *Aksi:* Ganti tabel HTML statis dengan kueri dinamis `StokClient` yang mengagregasikan obat berstok $\le 5$ dan batch kedaluwarsa $\le 30$ hari untuk seluruh cabang.
- [ ] **Tugas 2: Penyelarasan Menu Navigasi Sidebar Terpadu**
  - *Berkas:* `resources/views/components/layouts/admin.blade.php` dan `components/layouts/tenant.blade.php`
  - *Aksi:* Pastikan seluruh link menu sidebar terhubung ke route yang tepat.

---

## 5. QUALITY GATE & SOP KOLABORASI GIT

Sebelum mempresentasikan hasil ke Pak Devit Suwardiyanto, jalankan quality gate check:

```bash
# 1. Bersihkan dan kompilasi template Blade
php artisan view:clear && php artisan view:cache

# 2. Build asset produksi Vite
npm run build

# 3. Jalankan automated test suite
php artisan test

# 4. Verifikasi format PSR-12
php vendor/bin/pint --test
```
