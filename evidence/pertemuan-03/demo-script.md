# Pertemuan 03 — Demo Script (Apotek Multi-Klien)

## Data Demo Deterministik (`php artisan migrate:fresh --seed`)
- **Cabang**:
  * Cabang Banyuwangi Kota (`a1b2c3d4-0001-4000-8000-000000000001`)
  * Cabang Rogojampi (`a1b2c3d4-0002-4000-8000-000000000002`)
- **Akun Login Demo**:
  * Pelanggan: `candi@gmail.com` / `password`
  * Apoteker Cabang Bwi Kota: `candia@omnipharma.test` / `password`
  * Super Admin Eksekutif: `daffa@omnipharma.test` / `password`
- **Katalog & Stok FEFO**:
  * Amodiaquine 200mg (Batch `#BTC-2024A`, Exp 10 Okt 2026 / H-23 hari, Rp 45.000). Stok: Bwi Kota 12 strip, Rogojampi 0 strip.
  * Paracetamol 500mg (Batch `#PCT-8891`, Exp 20 Des 2027, Rp 8.500). Stok: Bwi Kota 50 strip, Rogojampi 25 strip.
- **Pesanan & Resep Menunggu Verifikasi**:
  * Pesanan `#ORD-20260917-0042` untuk Candi Chier (88 th), dr. Budi Sp.PD (SIP `503/SIP/2024`).
- **Ringkasan Penjualan Periode 01-16 Sep 2026**:
  * Total Omzet Rp 142.850.000 (1.840 pesanan, 3 retur).

## Langkah Demonstrasi
1. Inisialisasi Database:
   ```bash
   php artisan migrate:fresh --seed
   ```
2. Jalankan Suite Pengujian Constraint & Fungsional:
   ```bash
   php artisan test
   ```
3. Verifikasi Rute Portal:
   - Akses Portal Pelanggan: `/klien/a1b2c3d4-0001-4000-8000-000000000001` (OmniPharma Catalog)
   - Akses Portal Apoteker: `/cabang/a1b2c3d4-0001-4000-8000-000000000001/apoteker` (Antrian Verifikasi Resep & FEFO Batch)
   - Akses Dashboard Eksekutif: `/admin` (KPI Omzet & Penutupan Periode)
