# Panduan Akun Login & Pengujian Manual Multi-Peran
## OmniPharma Network

Dokumen ini berisi daftar kredensial akun untuk pengujian manual sistem informasi apotek jaringan **OmniPharma**. Semua akun demo telah disinkronkan pada basis data lokal dan siap digunakan untuk pengujian login manual melalui halaman autentikasi.

---

### URL Akses Pengujian

- **Halaman Login**: [http://127.0.0.1:8088/login](http://127.0.0.1:8088/login)
- **Halaman Sambutan**: [http://127.0.0.1:8088/](http://127.0.0.1:8088/)
- **Kembali ke Sambutan saat Login**: [http://127.0.0.1:8088/?welcome=1](http://127.0.0.1:8088/?welcome=1)

---

### Tabel Kredensial Akun Pengujian

| Peran Pengguna | Nama Lengkap | Email / Username | Kata Sandi | Cabang / Cakupan | Tujuan Dashboard Setelah Login |
| :--- | :--- | :--- | :---: | :--- | :--- |
| **👑 Super Admin Eksekutif** | Muhammad Daffa Ariby | `daffa@omnipharma.test` | `password` | Kantor Pusat (Semua Cabang) | `/admin/dashboard` |
| **🩺 Apoteker Penanggung Jawab (APJ)** | apt. Bambang, S.Farm | `bambang@omnipharma.test` | `password` | Cabang Banyuwangi Kota | `/cabang/banyuwangi-kota/dashboard` |
| **💊 Apoteker Penanggung Jawab (APJ)** | apt. Dewi Sartika, S.Farm | `dewi@omnipharma.test` | `password` | Cabang Rogojampi | `/cabang/rogojampi/dashboard` |
| **🧾 Kasir / Staf Administrasi** | Kasir Staf Banyuwangi | `kasir.bwi@omnipharma.test` | `password` | Cabang Banyuwangi Kota | `/admin/kasir-ledger` |
| **🛒 Pasien / Pelanggan Utama** | Guntur Afandy | `gunturafandy5@gmail.com` | `password` | Pelanggan Umum | `/dashboard` |
| **🛒 Pasien / Pelanggan Alternatif** | Candi Chier | `candi@gmail.com` | `password` | Pelanggan Umum | `/dashboard` |

---

### Rincian Fitur & Hak Akses Masing-Masing Peran

#### 1. Super Admin Eksekutif (`daffa@omnipharma.test`)
- **Tujuan Dashboard**: `/admin/dashboard`
- **Fitur Utama**:
  - Rekapitulasi omzet penjualan konsolidasi seluruh cabang aktif.
  - Penutupan periode akuntansi dan penguncian snapshot penjualan harian (*immutability*).
  - Ekspor laporan audit penjualan dalam format spreadsheet CSV/XLSX.
  - Akses buku kasir ledger dan pengaturan operasional cabang.

#### 2. Apoteker Penanggung Jawab / APJ (`bambang@omnipharma.test` & `dewi@omnipharma.test`)
- **Tujuan Dashboard**:
  - Banyuwangi Kota: `/cabang/banyuwangi-kota/dashboard`
  - Rogojampi: `/cabang/rogojampi/dashboard`
- **Fitur Utama**:
  - Antrean telaah resep dokter masuk dan validasi nomor izin SIPA dokter.
  - Verifikasi resep obat keras (tanda K merah).
  - Manajemen dan rotasi stok batch obat mendekati masa kedaluwarsa (metode FEFO).
  - Akses panduan SOP pelayanan kefarmasian Permenkes RI.

#### 3. Kasir / Staf Administrasi Cabang (`kasir.bwi@omnipharma.test`)
- **Tujuan Akses**: `/admin/kasir-ledger`
- **Fitur Utama**:
  - Rekonsiliasi transaksi pembayaran tunai dan pembayaran non-tunai (QRIS Dinamis).
  - Pengecekan saldo fisik kas harian kasir apotek.
  - Verifikasi status pelunasan transaksi pesanan obat.

#### 4. Pasien / Pelanggan Biasa (`gunturafandy5@gmail.com` & `candi@gmail.com`)
- **Tujuan Dashboard**: `/dashboard`
- **Fitur Utama**:
  - Akses katalog sediaan obat bebas dan obat resep per cabang.
  - Pengunggahan foto resep dokter digital untuk diverifikasi apoteker.
  - Pengelolaan keranjang belanja multi-tenant berbasis sesi.
  - Checkout pesanan dengan penguncian stok obat dan pembayaran QRIS instan.
  - Pelacakan status pemrosesan pesanan secara real-time.

---

### Alur Pengujian Manual oleh Tim:

1. Buka peramban dan arahkan ke alamat [http://127.0.0.1:8088/login](http://127.0.0.1:8088/login).
2. Masukkan salah satu **Email** dan **Kata Sandi** di atas (`password`).
3. Klik tombol **Masuk**.
4. Sistem akan memvalidasi sesi dan secara otomatis mengarahkan ke dashboard yang sesuai dengan peran akun yang dimasukkan.
5. Untuk berganti peran pengujian, klik tombol **Keluar (Logout)** di sudut kanan atas dashboard atau navbar, lalu ulangi langkah di atas dengan akun peran lain.
