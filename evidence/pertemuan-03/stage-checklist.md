# Pertemuan 03 — Stage Checklist

Output: Migrasi seluruh tabel baseline (16 entitas) dari PDM Apotek Multi-Klien, model dan relasi Eloquent, factory dan seeder deterministik, schema constraints, dan data dictionary.

| Tahap | Aktivitas | Status | Bukti |
|---|---|---|---|
| 1 | Audit PDM & Urutan Dependensi | ✅ | 16 entitas dipetakan 5 kelompok migrasi; dependensi parent→child; `docs/data-dictionary.md` |
| 2 | Migrasi Klien & Identitas Apotek | ✅ | `client`, `pengguna`, `super_admin`, `admin_client`, `peran_client_pengguna`, `peta_pengawas_staf`; UUID PK |
| 3 | Migrasi Katalog Obat & Stok Cabang | ✅ | `kategori_obat`, `obat`, `stok_client`; Unique composite `(id_obat, id_client)` pada stok cabang |
| 4 | Migrasi Transaksi & Resep Digital | ✅ | `pesanan`, `item_pesanan`, `resep`, `pembayaran`, `permintaan_retur`; snapshot `harga_satuan` & `subtotal` |
| 5 | Migrasi Agregasi Eksekutif & Audit | ✅ | `ringkasan_periode_penjualan`, `log_audit`; composite unique periode omzet cabang |
| 6 | Model, Factory, Seeder | ✅ | 16 Model PHPStan generic Level 7 + 16 Factories; `DemoApotekSeeder` deterministik & idempoten |
| 7 | Verifikasi Constraint & Query | ✅ | `ApotekDatabaseConstraintTest` (70 test hijau, 241 assertions); price snapshot terisolasi |
| 8 | Dokumentasi & Architecture Decision | ✅ | `docs/architecture.md`, `docs/data-dictionary.md`, ADR-0006 s/d ADR-0008 |
