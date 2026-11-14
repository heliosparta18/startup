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
6. **Dilarang Menggunakan Titik Hijau Dekoratif (No AI Status Dots):** Hindari titik-titik kecil hijau/neon bulat (`rounded-full`) maupun animasi berkedip/ping (`animate-pulse` / `animate-ping`) pada badge, status, heading, kartu, tombol role, atau pill cabang yang bersifat dekoratif ala antarmuka AI generik. Pengecualian hanya berlaku untuk ikon klasifikasi resmi golongan obat BPOM/Permenkes RI (lingkaran hijau Obat Bebas, biru Obat Bebas Terbatas, dan merah huruf K Obat Keras).

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

## 5. Daftar 22 Master Artboard Desain pada FigCraft (Struktur Lengkap 8 Modul Sidebar + QRIS + Auth)

Seluruh modul sidebar dari referensi desain telah diwujudkan secara lengkap dalam pasangan **Desktop Web (1440 × 900)** dan **Mobile Web (390 × 844)** menggunakan palet Pharmly Deep Spruce (`#0A231E`), aksen Chartreuse Lime (`#D4F94E`), dan ikon vektor SVG murni (nol emotikon):

### 5.1 Modul Inti Sidebar (8 Menu Lengkap)
1. **Overview (Super Admin Executive)**:
   - Desktop: `ID 4676` (1440 × 900, `x=100, y=2000`)
   - Mobile: `ID 4759` (390 × 844, `x=1580, y=2000`)
2. **Katalog Obat (Products & Inventory)**:
   - Desktop: `ID 5024` (1440 × 900, `x=100, y=100`)
   - Mobile: `ID 5213` (390 × 844, `x=1580, y=100`)
3. **Antrean Resep (Orders & FEFO Review)**:
   - Desktop: `ID 4549` (1440 × 900, `x=100, y=1050`)
   - Mobile: `ID 4641` (390 × 844, `x=1580, y=1050`)
4. **Penjualan (Sales & Cashier Transactions)**:
   - Desktop: `ID 5288` (1440 × 900, `x=100, y=2950`)
   - Mobile: `ID 5420` (390 × 844, `x=1580, y=2950`)
5. **Pelanggan (Customers & Patient Records)**:
   - Desktop: `ID 5477` (1440 × 900, `x=2020, y=2950`)
   - Mobile: `ID 5602` (390 × 844, `x=3500, y=2950`)
6. **Pembayaran (Payments & Reconciliation Ledger)**:
   - Desktop: `ID 5653` (1440 × 900, `x=100, y=3900`)
   - Mobile: `ID 5759` (390 × 844, `x=1580, y=3900`)
7. **Bantuan (Help & Support / Regulatory SOP)**:
   - Desktop: `ID 5811` (1440 × 900, `x=2020, y=3900`)
   - Mobile: `ID 5896` (390 × 844, `x=3500, y=3900`)
8. **Pengaturan (Settings & Branch Config)**:
   - Desktop: `ID 5938` (1440 × 900, `x=100, y=4850`)
   - Mobile: `ID 6009` (390 × 844, `x=1580, y=4850`)

### 5.2 Modul Transaksi & Autentikasi Tambahan
9. **Pembayaran QRIS Dinamis Sandbox (EMVCo 15m)**:
   - Desktop: `ID 4792` (1440 × 900, `x=2020, y=100`)
   - Mobile: `ID 4815` (390 × 844, `x=3500, y=100`)
10. **Autentikasi Masuk Portal (Login)**:
    - Desktop: `ID 4839` (1440 × 900, `x=2020, y=1050`)
    - Mobile: `ID 4897` (390 × 844, `x=3500, y=1050`)
11. **Pendaftaran Akun Staf & Pasien (Register)**:
    - Desktop: `ID 4935` (1440 × 900, `x=2020, y=2000`)
    - Mobile: `ID 4991` (390 × 844, `x=3500, y=2000`)
