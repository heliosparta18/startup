# Kamus Data (Data Dictionary) : Apotek Multi-Klien (PDM Revisi)

Kamus data ini diturunkan dari `references/PDM revisi.pdf`, `references/pdm_revisi_text.txt`, dan `references/srs_apotek_text.txt`.
Seluruh entitas menggunakan **UUID (Char 36)** untuk primary key dan foreign key. Nilai moneter menggunakan tipe data exact `DECIMAL(12, 2)`.

---

## 1. Kelompok A: Klien & Pengguna

### 1.1 `client`
Mewakili entitas cabang apotek / klien bisnis dalam jaringan multi-klien.
- `id_client` (UUID, Primary Key): ID unik cabang apotek.
- `nama_client` (VARCHAR 255): Nama cabang, mis. `Cabang Banyuwangi Kota`.
- `alamat` (TEXT): Alamat fisik operasional apotek cabang.
- `aktif` (BOOLEAN): Status operasional cabang apotek.
- `dibuat_pada` / `diperbarui_pada` (TIMESTAMP): Jejak waktu pencatatan.

### 1.2 `pengguna`
Tabel akun terpadu untuk pelanggan, apoteker penanggung jawab, staf cabang, dan administrator pusat.
- `id_pengguna` (UUID, Primary Key): ID unik pengguna.
- `nama_lengkap` (VARCHAR 255): Nama pengguna.
- `peran` (VARCHAR 50): Peran pengguna (`super_admin`, `apoteker`, `pelanggan`, `staf_klien`).
- `telepon_email` (VARCHAR 255, Unique): Kontak email atau telepon.
- `password` (VARCHAR 255): Hash kata sandi terenkripsi (Bcrypt).
- `terdaftar_pada` (DATE): Tanggal registrasi.
- `aktif` (BOOLEAN): Status keaktifan akun (enforcement 403 jika false).
- `dibuat_pada` / `diperbarui_pada` (TIMESTAMP): Jejak waktu.

### 1.3 `super_admin`
Administrator pusat jaringan Apotek Connect.
- `id_super_admin` (UUID, Primary Key): ID unik super admin.
- `id_pengguna` (UUID, FK -> `pengguna.id_pengguna`): Referensi akun pengguna.
- `nama` (VARCHAR 255): Nama admin pusat.
- `email` (VARCHAR 255): Email resmi.

### 1.4 `admin_client`
Pengelola / manajer operasional cabang apotek tertentu.
- `id_admin` (UUID, Primary Key): ID unik pengelola cabang.
- `id_pengguna` (UUID, FK -> `pengguna.id_pengguna`): Akun pengguna pengelola.
- `id_client` (UUID, FK -> `client.id_client`): Cabang apotek yang dikelola.
- `nama` (VARCHAR 255) / `email` (VARCHAR 255): Identitas pengelola.

### 1.5 `peran_client_pengguna`
Pemetaan relasi multi-klien dan penugasan staf/pelanggan per cabang (5NF).
- `id` (UUID, Primary Key): ID unik penugasan.
- `id_pengguna` (UUID, FK -> `pengguna.id_pengguna`): Pengguna terkait.
- `id_client` (UUID, FK -> `client.id_client`): Cabang apotek.
- `peran` (VARCHAR 50): Peran lokal di cabang.
- `ditugaskan_dari` / `ditugaskan_sampai` (DATE): Rentang waktu penugasan berlaku.

### 1.6 `peta_pengawas_staf`
Hierarki pengawasan farmasi antara Apoteker Penanggung Jawab (APJ/Supervisor) dan asisten apoteker/staf cabang.
- `id` (UUID, Primary Key): ID relasi hierarki.
- `id_staf` (UUID, FK -> `pengguna.id_pengguna`): Staf farmasi yang diawasi.
- `id_pengawas` (UUID, FK -> `pengguna.id_pengguna`): Apoteker pembina.
- `id_client` (UUID, FK -> `client.id_client`): Konteks cabang apotek.
- `berlaku_dari` / `berlaku_sampai` (DATE): Masa berlaku hubungan pengawasan.

---

## 2. Kelompok B: Katalog Obat & Multi-Klien Inventory

### 2.1 `kategori_obat`
Golongan obat sesuai regulasi kefarmasian Indonesia.
- `id_kategori` (UUID, Primary Key): ID golongan obat.
- `nama_kategori` (VARCHAR 100): Mis. `Wajib Resep (Keras)`, `Obat Bebas`, `Bebas Terbatas`.
- `wajib_resep` (BOOLEAN): Penanda apakah pembelian memerlukan verifikasi resep dokter.
- `deskripsi` (TEXT, Nullable): Keterangan regulasi golongan obat.
- `id_client` (UUID, FK -> `client.id_client`, Nullable): Null jika berlaku global.

### 2.2 `obat`
Master sediaan obat dan pelacakan batch kedatangan (FEFO).
- `id_obat` (UUID, Primary Key): ID unik sediaan obat.
- `nama_obat` (VARCHAR 255): Nama obat dagang/generik (mis. `Amodiaquine 200mg`).
- `merek` (VARCHAR 100): Produsen farmasi.
- `jenis_sediaan` (VARCHAR 100): Bentuk sediaan (Strip 10 tablet, Botol, Sirup).
- `jumlah_obat` (INTEGER): Satuan kuantitas master.
- `id_kategori` (UUID, FK -> `kategori_obat.id_kategori`): Golongan regulasi.
- `harga` (DECIMAL 12,2): Harga katalog berlaku saat ini.
- `expired` (DATE): Tanggal kedaluwarsa batch fisik.
- `batch_number` (VARCHAR 100): Nomor batch kedatangan (mis. `#BTC-2024A`).

### 2.3 `stok_client`
Jumlah ketersediaan stok fisik per cabang apotek.
- `id` (UUID, Primary Key): ID baris inventori.
- `id_obat` (UUID, FK -> `obat.id_obat`): Obat terkait.
- `id_client` (UUID, FK -> `client.id_client`): Cabang apotek pemilik stok.
- `jumlah_stok` (INTEGER): Kuantitas fisik tersedia.
- **Unique Invariant**: `UNIQUE(id_obat, id_client)` menjamin satu baris stok unik per kombinasi obat dan cabang apotek.

---

## 3. Kelompok C: Pemesanan & Resep Digital

### 3.1 `pesanan`
Transaksi pemesanan obat oleh pelanggan.
- `id_pesanan` (UUID, Primary Key): ID unik transaksi pesanan.
- `id_pengguna` (UUID, FK -> `pengguna.id_pengguna`): Pelanggan pemesan.
- `id_client` (UUID, FK -> `client.id_client`): Cabang apotek penyedia obat.
- `nomor_pesanan` (VARCHAR 50, Unique): Nomor pesanan unik (mis. `#ORD-20260917-0042`).
- `status` (VARCHAR 50): Status order (`menunggu_verifikasi`, `diproses`, `selesai`, `dibatalkan`).
- `metode_layanan` (VARCHAR 50): Mis. `click_collect` (ambil di apotek) atau kurir.

### 3.2 `resep`
Data resep dokter digital yang diunggah pelanggan.
- `id_resep` (UUID, Primary Key): ID unik berkas resep.
- `id_pesanan` (UUID, FK -> `pesanan.id_pesanan`): Pesanan terkait.
- `url_gambar` (TEXT): Lokasi file foto resep dokter.
- `status_verifikasi` (VARCHAR 50): `menunggu`, `disetujui`, `ditolak`.
- `nama_dokter` (VARCHAR 255): Nama dokter penulis resep (mis. `dr. Budi, Sp.PD`).
- `sip_dokter` (VARCHAR 100): Nomor Surat Izin Praktik dokter (mis. `503/SIP/2024`).
- `nama_pasien` (VARCHAR 255): Identitas dan usia pasien (mis. `Candi Chier (88 th)`).
- `sip_valid` (BOOLEAN): Status validitas keabsahan SIP dokter.
- `dosis_valid` (BOOLEAN): Status konfirmasi ketepatan dosis obat geriatri/pediatri.
- `catatan_apoteker` (TEXT): Catatan legalitas dari apoteker penanggung jawab.
- `diverifikasi_oleh` (UUID, FK -> `pengguna.id_pengguna`, Nullable): Apoteker pemeriksa.

### 3.3 `item_pesanan`
Rincian obat yang dipesan dengan penerapan Controlled Denormalization (ADR-05).
- `id_item` (UUID, Primary Key): ID unik item pesanan.
- `id_pesanan` (UUID, FK -> `pesanan.id_pesanan`): Pesanan induk.
- `id_obat` (UUID, FK -> `obat.id_obat`): Obat terkait.
- `kuantitas` (INTEGER): Jumlah unit yang dibeli.
- `nama_obat` (VARCHAR 255, Snapshot): Nama obat pada saat transaksi dibuat.
- `harga_satuan` (DECIMAL 12,2, Snapshot): Harga satuan pada saat pesanan dibuat.
- `subtotal` (DECIMAL 12,2): Hasil kali kuantitas dan harga satuan snapshot.
- **Invarian Snapshot (ADR-05)**: Nilai `nama_obat` dan `harga_satuan` bersifat immutable (tidak boleh terpengaruh bila master data harga obat berubah).

---

## 4. Kelompok D: Pembayaran & Retur

### 4.1 `pembayaran`
Pencatatan pelunasan transaksi pesanan.
- `id_pembayaran` (UUID, Primary Key): ID unik pembayaran.
- `id_pesanan` (UUID, FK -> `pesanan.id_pesanan`): Pesanan terkait.
- `metode_pembayaran` (VARCHAR 50): `transfer`, `e-wallet`, `cod`.
- `jumlah` (DECIMAL 12,2): Nominal pembayaran.
- `url_lampiran` (TEXT, Nullable): Bukti transfer pelanggan.
- `status` (VARCHAR 50): `menunggu`, `berhasil`, `gagal`.
- `diverifikasi_oleh` (UUID, FK -> `pengguna.id_pengguna`, Nullable): Staf kasir/verifikator.

### 4.2 `permintaan_retur`
Pengajuan pengembalian obat akibat kemasan rusak atau kekeliruan pengantaran.
- `id_retur` (UUID, Primary Key): ID pengajuan retur.
- `id_pesanan` (UUID, FK -> `pesanan.id_pesanan`): Pesanan terkait.
- `alasan` (TEXT): Alasan retur barang.
- `jumlah_diajukan` (DECIMAL 12,2): Nilai nominal retur diajukan.
- `jumlah_disetujui` (DECIMAL 12,2): Nilai nominal retur yang disetujui.
- `status` (VARCHAR 50): `menunggu`, `disetujui`, `ditolak`.

---

## 5. Kelompok E: Pelaporan & Audit Trail

### 5.1 `ringkasan_periode_penjualan`
Agregasi penjualan per cabang apotek untuk keperluan penutupan buku dan audit keuangan.
- `id_ringkasan` (UUID, Primary Key): ID ringkasan periode.
- `id_client` (UUID, FK -> `client.id_client`): Cabang apotek terkait.
- `nama_client` (VARCHAR 255, Snapshot): Nama cabang saat periode ditutup.
- `total_obat_terjual` (VARCHAR 100, Snapshot): Total fisik obat terjual.
- `periode_mulai` (DATE) / `periode_selesai` (DATE): Rentang periode akuntansi.
- `total_pesanan` (INTEGER): Jumlah pesanan terlayani (mis. 820 order).
- `total_pendapatan` (DECIMAL 12,2): Total omzet terkunci (mis. Rp 68.200.000).
- `total_retur` (INTEGER): Total pengajuan retur yang disetujui.
- `status_terkunci` (BOOLEAN): Status apakah buku akuntansi periode telah dikunci.
- **Unique Invariant**: `UNIQUE(id_client, periode_mulai, periode_selesai)` menjamin idempotensi penutupan periode (tidak dapat ditutup ganda).

### 5.2 `log_audit`
Pencatatan jejak audit (audit trail) aktivitas penting (append-only).
- `id_audit` (UUID, Primary Key): ID unik log audit.
- `id_aktor` (UUID, FK -> `pengguna.id_pengguna`): Aktor pengguna yang melakukan tindakan.
- `aksi` (VARCHAR 100): Jenis aktivitas, mis. `VERIFIKASI_RESEP`, `TUTUP_PERIODE`.
- `nama_entitas` (VARCHAR 100): Nama tabel entitas yang terdampak.
- `id_entitas` (UUID): ID rekaman entitas terkait.
- `payload` (JSON / TEXT, Nullable): Metadata perubahan sebelum dan sesudah.
- `dibuat_pada` (TIMESTAMP): Waktu pasti aksi dicatat.
