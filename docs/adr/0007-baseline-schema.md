# ADR-0007 : Skema Basis Data Apotek Multi-Klien (PDM Revisi)

- Status: Diterima
- Tanggal: 2026-09-21
- Konteks: ERD Fase 0 (16 entitas) diturunkan secara mockup-first dari alur antarmuka Layar 1 s/d 4 ke dalam skema fisik MariaDB 11.4 / MySQL 8.4 melalui 5 berkas migrasi database (`2026_09_21_000101` s/d `000105`).

## Keputusan
1. **UUID Everywhere**: Seluruh primary key dan foreign key relasi menggunakan UUID standar (`CHAR(36)` / `uuid`), menghindari masalah enumerasi sekuensial dan mempermudah sinkronisasi multi-klien terdistribusi.
2. **Multi-Tenant Composite Isolation**:
   - `stok_client` menerapkan `UNIQUE(id_obat, id_client)` untuk menjamin partisi stok fisik per cabang apotek.
   - `ringkasan_periode_penjualan` menerapkan `UNIQUE(id_client, periode_mulai, periode_selesai)` untuk penutupan periode akuntansi yang idempoten.
3. **FEFO & Batch Tracking**:
   - Entitas `obat` mencatat kolom `expired` (DATE) dan `batch_number` (VARCHAR) guna mendukung prioritas pengeluaran First-Expired First-Out (FEFO) dan peringatan batch mendekati kedaluwarsa (H-23).
4. **Append-Only & Audit Trail**:
   - `log_audit` dan `ringkasan_periode_penjualan` bersifat append-only untuk menjaga integritas pembukuan dan rekonsiliasi keuangan.

## Konsekuensi
- Database integrity ditegakkan pada level engine MariaDB/MySQL nyata, bukan sekadar logika aplikasi.
- Seluruh 16 model Eloquent dilengkapi dengan generic annotations Level 7 dan deterministic seeders (`DemoApotekSeeder`).
