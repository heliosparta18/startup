# 📋 PANDUAN LENGKAP PRESENTASI & ROADMAP PENGEMBANGAN
## OMNIPHARMA NETWORK — Platform Jaringan Apotek Multi-Cabang O2O (Online-to-Offline)

> **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi (TRPL)  
> **Dosen Pengampu:** Pak Devit Suwardiyanto  
> **Status Proyek:** **100% Siap Demo Presentasi (Quality Gate: 196/196 Automated Tests Passed | PHPStan Level 7 Passed | Pint Clean)**  
> **Tim Pengembang:**
> - **Muhammad Daffa Ariby (Lead Architect & Core Multi-Tenancy)**
> - **Guntur Afandi (Commerce Pipeline, QRIS Gateway & Auth Lead)**
> - **Arya Dwi Mahendra (Branch Operations & FEFO Batch Lead)**
> - **Candia Purwa (Real-Time WebSockets, Analytics & Security Lead)**

---

## 1. Ringkasan Eksekutif & Status Progress Aktual

### A. Seberapa Jauh Proyek Ini Selesai?
Secara fungsionalitas inti untuk **kebutuhan presentasi praktikum**, proyek OmniPharma Network sudah mencapai **100% Kesiapan Demo (Production-Ready Prototype)**:
- **15 Milestone Modul Praktikum (M01 – M14 + Security & Real-Time)** telah selesai dan terintegrasi di branch `main`.
- **Quality Gate:** 
  - `php artisan test`: **196 passed, 0 failures, 1246 assertions** (100% hijau).
  - `vendor/bin/pint --test`: **Passed** (0 style violations, standar PSR-12).
  - `phpstan analyse`: **Level 7 Passed** (Zero static typing errors).
- **Semua Alur Bisnis Bekerja**: Mulai dari pencarian obat, upload resep, skrining apoteker, FEFO batch allocation, pembayaran QRIS, antrean live WebSocket, hingga penutupan buku akuntansi kas cabang oleh Super Admin.

```
┌────────────────────────────────────────────────────────────────────────┐
│                   STATUS KESIAPAN MODUL PRESENTASI                     │
├─────────────────────────┬──────────────┬───────────────────────────────┤
│ Modul                   │ Status       │ Keterangan                    │
├─────────────────────────┼──────────────┼───────────────────────────────┤
│ 1. Multi-Tenancy Cabang │ ✅ Selesai   │ Banyuwangi Kota & Rogojampi   │
│ 2. Katalog & Stok Split │ ✅ Selesai   │ Deteksi stok lintas cabang    │
│ 3. Resep Digital & SIP  │ ✅ Selesai   │ Validasi format SIP dokter    │
│ 4. Batch Stock FEFO     │ ✅ Selesai   │ First-Expired, First-Out      │
│ 5. Keranjang & Checkout │ ✅ Selesai   │ Redis + Atomic Lock DB        │
│ 6. Pembayaran QRIS      │ ✅ Selesai   │ Dynamic EMVCo + Simulator     │
│ 7. Antrean & Reverb WS  │ ✅ Selesai   │ Real-time live status monitor │
│ 8. POS Kasir & Ledger   │ ✅ Selesai   │ Rekonsiliasi kas cabang       │
│ 9. Admin Konsolidasi    │ ✅ Selesai   │ Kunci Periode Akuntansi (ADR) │
│ 10. Profil & Splash UI  │ ✅ Selesai   │ Tema hijau-putih elegan       │
└─────────────────────────┴──────────────┴───────────────────────────────┘
```

---

## 2. Kredensial Akun Demo Live Presentasi

Semua akun ini telah terverifikasi aktif dengan kata sandi: `password`

| Peran (Role) | Email Login | Password | URL Akses Langsung | Fokus Demo |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin Pusat** | `daffa@omnipharma.test` | `password` | `/admin/dashboard` | Konsolidasi omzet, audit log, kunci periode kas |
| **Apoteker Banyuwangi** | `bambang@omnipharma.test` | `password` | `/cabang/banyuwangi-kota/dashboard` | Skrining resep SIP, rotasi batch FEFO, panggil antrean |
| **Apoteker Rogojampi** | `dinda@omnipharma.test` | `password` | `/cabang/rogojampi/dashboard` | Manajemen stok cabang sekunder, verifikasi resep |
| **Kasir Cabang** | `kasir.bwi@omnipharma.test` | `password` | `/admin/kasir-ledger` | POS transaksi langsung & rekonsiliasi kas harian |
| **Pelanggan (Pasien)** | `gunturafandy5@gmail.com` | `password` | `/klien/banyuwangi-kota` | Belanja obat, upload resep, bayar QRIS, pantau antrean |

---

## 3. Arsitektur Teknis Sistem (Nilai Tambah saat Ditanya Dosen)

1. **Modular Monolith Architecture:**
   - Terbagi dalam 6 Bounded Context di `app/Modules/`: `Admin`, `Catalog`, `Ordering`, `Payments`, `Prescription`, dan `Reporting`.
   - Kode terisolasi rapi, tidak bercampur aduk, memudahkan pengembangan skala besar tanpa overhead microservices.
2. **Multi-Tenancy Tanpa Kebocoran Data (Zero Data Leakage):**
   - Menggunakan `TenantScope` (Eloquent Global Scope) yang otomatis memfilter query basis data berdasarkan ID cabang (`client_id`).
   - Mencegah celah keamanan IDOR (*Insecure Direct Object Reference*); apoteker Rogojampi mustahil melihat resep cabang Banyuwangi.
3. **Concurrency Control & Idempotensi:**
   - **Keranjang:** Disimpan di cache Redis berkecepatan tinggi dengan masa kedaluwarsa otomatis.
   - **Checkout:** Menggunakan transaksi pesimistik basis data (`SELECT ... FOR UPDATE`) dan atomic lock agar tidak terjadi *overselling* saat ada 2 pasien berebut obat yang stoknya sisa 1.
4. **Kepatuhan Regulasi Farmasi Resmi (Permenkes RI No. 73/2016):**
   - **FEFO (First Expired, First Out):** Pengeluaran obat otomatis memprioritaskan batch yang tanggal kedaluwarsanya paling dekat.
   - **Audit Trail & Keabsahan SIP:** Resep dokter wajib mencantumkan nomor Surat Izin Praktik (SIP) dokter yang divalidasi regex sebelum disetujui apoteker.
5. **Real-Time Communication:**
   - Menggunakan Laravel Reverb (WebSocket murni berbasis PHP tanpa ketergantungan pihak ketiga berbayar) untuk memancarkan event perubahan status resep dan panggilan antrean langsung ke layar HP pelanggan.

---

## 4. Alur Kerja Sistem (End-to-End User Journey)

### Flowchart Alur Utama Sistem
```
[PASIEN / PELANGGAN]
       │
       ▼
1. Buka Web & Pilih Cabang (Banyuwangi Kota / Rogojampi)
       │
       ▼
2. Cari Obat di Katalog / Unggah Resep Dokter
       ├─ Jika Obat Habis di Cabang Ini ──► Sistem Beri Rekomendasi Cabang Lain
       │
       ▼
3. Masuk Keranjang & Konfirmasi Checkout (Pilih: Ambil Sendiri / Antar Kurir)
       │
       ▼
4. Bayar via Dynamic QRIS EMVCo (Countdown 15 Menit)
       │
       ▼
5. Pasien Dapat Tiket Antrean Virtual (#ANT-XXX) & Live Estimasi Waktu

─────────────────────────────────────────────────────────────────────────────

[APOTEKER CABANG / TENANT]
       │
       ▼
6. Buka Portal Apoteker ──► Terima Notifikasi Resep / Pesanan Baru
       │
       ▼
7. Telaah Resep & Validasi SIP Dokter (Disetujui / Ditolak)
       │
       ▼
8. Alokasi Stok Obat Metode FEFO (Batch Kedaluwarsa Terdekat)
       │
       ▼
9. Update Status: "Diproses" ──► "Siap Diambil" (Pemicu Event WebSocket Reverb)

─────────────────────────────────────────────────────────────────────────────

[SUPER ADMIN & KASIR PUSAT]
       │
       ▼
10. Kasir Rekonsiliasi Pembayaran QRIS & Kas Masuk di Kasir Ledger
       │
       ▼
11. Super Admin Pantau Dashboard Konsolidasi Omzet Real-Time
       │
       ▼
12. Tutup Buku Periode Penjualan (ADR-0008: Snapshot Kas Terkunci Permanen)
       │
       ▼
13. Ekspor Laporan Penjualan CSV (Aman dari CSV Formula Injection)
```

---

## 5. Rencana Skenario Live Demo untuk Presentasi (5 Menit)

Agar presentasi lancar, terstruktur, dan membuat dosen terkesan, ikuti langkah-langkah demo berikut:

### Skenario 1: Sisi Pasien (Frontend Pelanggan) — Durasi: 2 Menit
1. Buka browser: kunjungi `http://127.0.0.1:8000/klien/banyuwangi-kota`.
2. Tunjukkan **Katalog Obat Multi-Kategori** (Obat Bebas, Keras K, Terbatas).
3. Tunjukkan fitur **Deteksi Stok Lintas Cabang**:
   - Cari obat yang stoknya kosong di Banyuwangi, perlihatkan label hijau ramah: *"Tersedia di Apotek Rogojampi"*.
4. Masukkan obat ke keranjang & lakukan **Checkout**:
   - Pilih metode *Click & Collect* (Ambil di Apotek) dengan slot waktu pengambilan.
5. Klik **Bayar Sekarang** &rarr; Tunjukkan **Dynamic QRIS EMVCo** lengkap dengan timer hitung mundur 15 menit.
6. Lakukan simulasi pembayaran berhasil &rarr; Tunjukkan **Tiket Antrean Virtual** dengan nomor giliran dan progress bar interaktif.

### Skenario 2: Sisi Apoteker Cabang (Portal Tenant) — Durasi: 1.5 Menit
1. Buka tab baru / incognito: login sebagai `bambang@omnipharma.test` (password: `password`).
2. Masuk ke `/cabang/banyuwangi-kota/dashboard`.
3. Tunjukkan **Antrean Pelayanan Farmasi**:
   - Pesanan dari Pasien tadi langsung muncul di daftar antrean apoteker.
4. Tunjukkan menu **Manajemen Batch FEFO**:
   - Perlihatkan bagaimana sistem mengurutkan obat berdasarkan tanggal kedaluwarsa terdekat (ada warning kuning untuk batch kedaluwarsa < 30 hari).
5. Klik **"Siap Diambil"** &rarr; Jelaskan ke dosen bahwa aksi ini memancarkan sinyal WebSocket ke layar pasien secara instan tanpa perlu refresh halaman.

### Skenario 3: Sisi Super Admin (Executive Reporting) — Durasi: 1.5 Menit
1. Buka browser: login sebagai `daffa@omnipharma.test` (password: `password`).
2. Masuk ke `/admin/dashboard`.
3. Perlihatkan **KPI Eksekutif**:
   - Grafik omzet gabungan cabang Banyuwangi dan Rogojampi.
   - Peta staf APJ (Apoteker Penanggung Jawab) per cabang.
4. Tunjukkan fitur **Penutupan Periode Akuntansi Kas (ADR-0008)**:
   - Jelaskan bahwa data penjualan yang sudah ditutup akan di-freeze (diabadikan) menjadi snapshot permanen agar tidak bisa dimanipulasi atau diubah di kemudian hari.
5. Tunjukkan tombol **Ekspor CSV Laporan** yang sudah dilengkapi proteksi *Formula Injection*.

---

## 6. Roadmap: Bagaimana Sistem Ini Dilanjutkan ke Depan?

Bila dosen bertanya: *"Rencana pengembangan ke depannya bagaimana?"*, berikut jawaban strategis yang terbagi dalam 3 fase:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   ROADMAP PENGEMBANGAN OMNIPHARMA                      │
├────────────────────┬───────────────────────────────────────────────────┤
│ Jangka Pendek      │ • Refactor Route Closure ke Controller Terpisah   │
│ (1 - 2 Bulan)      │ • Notifikasi WhatsApp Gateway Transaksi (Fonnte)  │
│                    │ • Optimasi Indeks Kueri Multi-Cabang              │
├────────────────────┼───────────────────────────────────────────────────┤
│ Jangka Menengah    │ • Integrasi Payment Gateway Real (Midtrans/Xendit)│
│ (3 - 6 Bulan)      │ • Integrasi Platform SATUSEHAT Kemenkes RI        │
│                    │ • Fitur Peringatan Interaksi Obat Otomatis (DDIs) │
├────────────────────┼───────────────────────────────────────────────────┤
│ Jangka Panjang     │ • Aplikasi Mobile Pasien & Driver (Flutter)       │
│ (6 - 12 Bulan)     │ • Multi-Warehouse & Inter-Branch Stock Transfer   │
│                    │ • AI Assistant Apoteker untuk Rekomendasi Dosis   │
└────────────────────┴───────────────────────────────────────────────────┘
```

### Rincian Tiap Fase:

#### 1. Jangka Pendek (Penyempurnaan Platform & Stabilitas)
- **Refactoring Arsitektur Controller:**
  - Memindahkan seluruh route closure di `routes/admin.php` dan `routes/tenant.php` ke dalam Dedicated Controller Classes untuk mempermudah pemeliharaan dan pengujian unit terisolasi.
- **Notifikasi Transaksi via WhatsApp Gateway:**
  - Mengintegrasikan API WhatsApp agar nomor antrean dan link struk QRIS langsung terkirim ke nomor WA pasien saat transaksi berhasil.
- **Optimasi Indeks Kueri Multi-Cabang:**
  - Menambahkan indeks komposit basis data pada pencarian sediaan obat dan pemfilteran stok multi-cabang.

#### 2. Jangka Menengah (Integrasi Ekosistem Kesehatan Nasional)
- **Integrasi SATUSEHAT Kemenkes RI:**
  - Menghubungkan resep digital dan riwayat pengobatan pasien dengan standar format HL7 FHIR Kemenkes, sehingga data obat pasien terhubung dengan rekam medis elektronik nasional.
- **Deteksi Interaksi Obat Otomatis (Drug-Drug Interaction Alert):**
  - Sistem otomatis memeriksa jika pasien membeli 2 obat yang memiliki kontraindikasi/interaksi berbahaya dan memberikan peringatan ke apoteker saat telaah resep.
- **Produksi Payment Gateway:**
  - Migrasi simulator QRIS EMVCo ke gateway berizin Bank Indonesia (Midtrans / Xendit / QRIS BCA).

#### 3. Jangka Panjang (Ekspansi Bisnis & Logistik Cerdas)
- **Aplikasi Mobile Pelanggan & Kurir Apotek (Flutter / React Native):**
  - Pelanggan dapat memantau posisi kurir apotek secara live GPS map saat obat sedang diantar ke rumah.
- **Sistem Mutasi Stok Antar-Cabang Otomatis (Inter-Branch Transfer):**
  - Jika Apotek Banyuwangi kehabisan stok sementara Rogojampi surplus, sistem otomatis menerbitkan *Surat Jalan Mutasi Stok* antar cabang tanpa input manual.
- **AI Prediksi Kebutuhan Obat (Demand Forecasting):**
  - Memprediksi lonjakan kebutuhan obat flu/batuk saat pergantian musim berdasarkan riwayat penjualan bulan-bulan sebelumnya.

---

## 7. Tanya Jawab Kritis Dosen (Dan Cara Menjawabnya)

1. **T: Kenapa memilih arsitektur Modular Monolith dibanding Microservices?**
   - **J:** *"Untuk skala jaringan apotek multi-cabang saat ini, Modular Monolith memberikan isolasi domain yang rapi melalui Bounded Context tanpa beban kompleksitas jaringan, latensi HTTP, atau biaya server microservices. Jika suatu saat cabang berkembang ke 100+ apotek, setiap modul di `app/Modules` sangat mudah diekstrak menjadi microservice independen."*

2. **T: Bagaimana sistem menjamin data cabang satu tidak bocor ke cabang lain?**
   - **J:** *"Kami mengimplementasikan `TenantScope` di lapisan model Eloquent dan `TenantContext` di middleware. Setiap query ke database otomatis di-filter dengan `WHERE client_id = ?`. Jadi sekalipun ada user mencoba memanipulasi parameter URL, sistem akan mengembalikan 404 atau data kosong (Mitigasi Celah IDOR)."*

3. **T: Mengapa menggunakan metode FEFO dan bukan FIFO biasa?**
   - **J:** *"Sesuai Permenkes No. 73 Tahun 2016 tentang Standar Pelayanan Kefarmasian, produk farmasi memiliki masa kedaluwarsa (ED). Barang yang masuk pertama belum tentu kedaluwarsa pertama. FEFO menjamin batch obat yang tanggal kedaluwarsanya paling dekat harus dikeluarkan lebih dahulu, meminimalkan kerugian apotek akibat obat basi."*

4. **T: Bagaimana jika ada 2 pasien memesan obat yang sama secara bersamaan dan stok sisa 1?**
   - **J:** *"Di proses checkout kami menggunakan `Pessimistic Locking` basis data (`DB::transaction` dengan `lockForUpdate`) dan Redis atomic lock. Request kedua akan menunggu antrean transaksi atau menerima notifikasi stok habis, sehingga mustahil terjadi overselling."*

---

> **Dokumen disusun untuk:** Muhammad Daffa Ariby & Tim OmniPharma Network  
> **Terakhir Diperbarui:** 19 November 2026  
> **Siap Dicetak / Dipakai untuk Bahan Slide Presentasi**
