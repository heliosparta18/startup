# Product Requirement Document (PRD): Design System, Interaksi, & Aturan UI (Anti-Slop)

## OmniPharma Network: Sistem Informasi Apotek Multi-Klien
- **Mata Kuliah:** Proyek Praktikum Pengembangan Sistem Informasi
- **Dosen Pengampu:** Pak Devit Suwardiyanto
- **Target Platform:** Web Desktop (1440px) dan Mobile Web / Flutter MobileDev (390px)
- **Framework UI:** Tailwind CSS v4, Livewire Flux UI, Alpine.js
- **Tools Desain Canvas:** FigCraft Native (127.0.0.1:7979)

---

## 1. Prinsip Desain dan Aturan Anti-Slop (Design Philosophy & Anti-Slop Rules)

Dokumen ini menjadi acuan mutlak dalam perancangan antarmuka OmniPharma Network. Seluruh perancangan menolak pola generik kecerdasan buatan (AI slop) demi menghasilkan karya perangkat lunak yang fungsional, bernapas (*spacious*), dan memenuhi standar farmasi resmi Republik Indonesia (Permenkes RI No. 73/2016).

### 1.1 Larangan Pola AI Slop (Strict Bans)
1. **Dilarang Menggunakan Gradien Ungu/Biru Generik:** Latar belakang tidak boleh menggunakan gradien ungu ke biru neon, orbs bercahaya, atau mesh gradient tanpa makna.
2. **Dilarang Spamming Capsule Badges:** Menghindari penumpukan label kapsul bercahaya kecil (seperti "AI Powered", "Fast", "Secure") pada sudut kartu. Hanya label klasifikasi obat resmi yang diizinkan.
3. **Dilarang Menggunakan Karakter Em Dash:** Seluruh teks antarmuka dan dokumentasi tidak menggunakan karakter em dash. Gunakan tanda koma, titik dua, atau kurung sebagai pemisah klausa.
4. **Dilarang Memadati Ruang (Anti-Claustrophobia):** Setiap kontainer wajib memiliki margin dan padding yang lega (minimum padding 24px hingga 32px pada desktop, 16px pada mobile).
5. **Dilarang Elemen Interaktif Palsu (Dead Controls):** Setiap tombol, dropdown, tautan navigasi, dan input wajib memiliki aksi nyata, tujuan rute, atau modal terhubung.

### 1.2 Palet Warna Medis Terkurasi (Curated Clinical Palette)
Palet dibatasi secara ketat pada 2 warna netral struktural dan 1 warna aksen medis utama:

| Token Warna | Nilai Hex | Peruntukan Fungsional |
| :--- | :---: | :--- |
| **Surface Pure** | `#FFFFFF` | Latar kartu utama, dialog pop-up, dan kontainer produk |
| **Surface Canvas** | `#F8FAFC` | Latar dasar halaman web dan pemisah area sekunder |
| **Border Neutral** | `#E2E8F0` | Garis batas struktural kartu dan input (tanpa bayangan tebal) |
| **Ink Heading** | `#0F172A` | Teks judul utama, harga produk, dan tombol aksi tegas |
| **Ink Body/Muted**| `#64748B` | Keterangan sediaan, nomor batch, tanggal, dan placeholder |
| **Clinical Emerald**| `#059669` | Aksen brand primer, tombol checkout, dan status stok tersedia |
| **Clinical Emerald Tint**| `#ECFDF5` | Latar badge obat bebas dan indikator verifikasi sukses |
| **Rx Crimson** | `#E11D48` | Peringatan obat keras wajib resep dokter dan tombol tolak |
| **Rx Crimson Tint** | `#FFF1F2` | Latar label obat keras golongan G (lingkaran merah K) |
| **Amber Warning** | `#D97706` | Peringatan batch kedaluwarsa terdekat (FEFO H-30 hari) |
| **Amber Warning Tint**| `#FFFBEB` | Latar banner peringatan inventori batch mendekati ED |
| **Limited Blue** | `#2563EB` | Penanda obat bebas terbatas (lingkaran biru Pemenkes) |

---

## 2. Skala Tipografi dan Grid Spasi (Typography Scale & 8pt Spacing Grid)

Menggunakan keluarga huruf sans-serif modern yang jernih (*clean humanist sans*).

- **Heading Display (28px Bold, leading 36px):** Judul utama portal dan ringkasan eksekutif.
- **Section Heading (20px Bold, leading 28px):** Judul panel dua kolom dan judul grup katalog.
- **Card Title (16px - 18px Bold, leading 24px):** Nama obat, nomor order pesanan, dan nama cabang.
- **Body Regular (14px Regular, leading 20px):** Teks instruksi, data pasien, dan deskripsi sediaan.
- **Caption / Meta (12px Medium, leading 16px):** Nomor SIP dokter, sisa hari kedaluwarsa, dan label status.
- **Micro Badge (11px Bold, uppercase):** Penanda FEFO rank dan badge regulasi obat.

Spasi berpatokan pada kelipatan 8px: `8px`, `16px`, `24px`, `32px`, `48px`, dan `64px`.

---

## 3. Matriks Interaksi, Redirect, dan Pop-up State Machine

Sistem memiliki alur interaksi terpadu dari pelanggan hingga apoteker cabang dan super admin:

```
[Katalog Publik]
      │
      ├─► Klik Obat Keras ────► [Pop-up Modal: Detail Obat & Cek Stok Cabang]
      │                                │
      │                                └─► Klik "+ Unggah Resep"
      │                                          │
      │                                          ▼
      │                                [Pop-up Modal: Form Unggah Foto Resep]
      │                                          │
      ├─► Klik Obat Bebas ────► [Slide-over Drawer: Keranjang Belanja]
      │                                          │
      ▼                                          ▼
[Halaman Checkout Atomik: Kunci Snapshot Harga ADR-0008]
      │
      ▼
[Halaman Pembayaran QRIS Dinamis (EMVCo) + Timer 15 Menit]
      │
      ├─► Tombol Demo: [Pop-up Modal: Simulasi Webhook Pelunasan]
      │                          │
      │                          ▼ (Auto-Settlement)
      └──────────────────► [Redirect: Halaman Sukses Pembayaran]
                                 │
                                 ▼
                     [Halaman Pelacakan Live Stepper: Laravel Reverb WebSockets]
                                 │
                                 ▼ (Real-time Broadcast)
                     [Portal Apoteker: Antrean Telaah Resep Masuk]
                                 │
                     ┌───────────┴───────────┐
                     ▼                       ▼
      [Pop-up Modal: Pratinjau Resep]  [Pop-up Modal: Alasan Penolakan]
                     │
                     ▼ (Setujui Resep)
      [Alokasi Otomatis Batch Kedaluwarsa Terdekat: #1 FEFO]
                     │
                     ▼ (Transaksi Selesai)
      [Super Admin: Executive Dashboard & Agregasi Cabang]
                     │
                     ▼
      [Pop-up Modal: Tutup Periode & Kunci Permanen ADR-0008]
                     │
                     ▼
      [Dialog Pop-up: Ekspor Laporan Finansial PDF / XLSX]
```

---

## 4. Spesifikasi Tampilan Mobile Web / MobileDev (Viewport 390px × 844px)

1. **Touch Target Standard:** Seluruh tombol tindakan, tautan tap, dan opsi radio memiliki tinggi area tekan minimal 44px (W3C Mobile Accessibility).
2. **Fixed Bottom Action Bar:** Pada tampilan katalog obat dan checkout, tombol aksi utama (*Call to Action*) melayang di bagian bawah dengan latar belakang putih bergaris batas tipis.
3. **Bottom Sheet Dialog:** Modal pada mobile menggunakan pola *drag-down bottom sheet* dengan sudut melengkung 20px pada bagian atas.
4. **Order Stepper Mobile:** Indikator tahapan Reverb disusun vertikal atau horizontal kompak agar tidak terjadi tumpang tindih teks pada layar kecil.

---

## 5. Daftar 25 Artboard Desain pada FigCraft

1. **D01:** Desktop Portal Pelanggan: Beranda & Katalog Obat Bersih
2. **D02:** Desktop Pop-up Modal: Detail Obat & Cek Stok Multi-Cabang
3. **D03:** Desktop Pop-up Modal: Form Unggah Foto Resep Dokter & SIP
4. **D04:** Desktop Slide-over Drawer: Keranjang Belanja Redis Multi-Tenant
5. **D05:** Desktop Halaman Checkout Atomik: Pre-Order & Snapshot Harga
6. **D06:** Desktop Halaman Pembayaran: QRIS Dinamis Standar EMVCo 15 Menit
7. **D07:** Desktop Pop-up Modal: Simulator Webhook Pelunasan QRIS Sandbox
8. **D08:** Desktop Halaman Sukses Pembayaran & Tombol Lacak Pesanan
9. **D09:** Desktop Halaman Pelacakan Live: Stepper Reverb WebSockets
10. **D10:** Desktop Portal Apoteker: Antrean Telaah Resep Masuk (#ORD-0042)
11. **D11:** Desktop Pop-up Modal: Pembesar Gambar Resep & Validasi Dokter
12. **D12:** Desktop Pop-up Modal: Konfirmasi Tolak Resep (Input Alasan Wajib)
13. **D13:** Desktop Portal Apoteker: Manajemen Batch FEFO & Warning Near-ED
14. **D14:** Desktop Pop-up Modal: Penyesuaian Kartu Stok Fisik Batch
15. **D15:** Desktop Super Admin: Executive KPI Dashboard & Agregasi Cabang
16. **D16:** Desktop Pop-up Modal: Konfirmasi Tutup Periode Kas & Kunci ADR-0008
17. **D17:** Desktop Pop-up Modal: Dialog Ekspor Laporan Penjualan (PDF/XLSX)
18. **M01:** Mobile Web Pelanggan: Katalog Obat & Pemilih Cabang Terdekat
19. **M02:** Mobile Web Pelanggan: Bottom Sheet Detail Obat & Resep
20. **M03:** Mobile Web Pelanggan: Bottom Sheet Unggah Foto Resep dari Kamera
21. **M04:** Mobile Web Pelanggan: Bottom Sheet Keranjang Belanja
22. **M05:** Mobile Web Pelanggan: Tampilan Bayar QRIS Dinamis & Timer
23. **M06:** Mobile Web Pelanggan: Live Stepper Pelacakan Pesanan Reverb
24. **M07:** Mobile Web Apoteker: Kartu Verifikasi Resep Masuk & Aksi Cepat
25. **M08:** Mobile Web Apoteker: Monitoring Batch Kedaluwarsa FEFO H-23 Hari
26. **DS01:** Design System Foundations: Palet Warna Medis Anti-Slop, Tipografi, & Komponen
