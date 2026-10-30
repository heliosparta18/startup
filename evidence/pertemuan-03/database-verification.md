# Pertemuan 03 — Database Verification (Apotek Multi-Klien)

## Skema Baseline PDM
16 entitas domain farmasi termigrasi penuh di MySQL/MariaDB:
1. `client` (Multi-Cabang Apotek)
2. `pengguna` (Multi-Role User: super_admin, apoteker, admin, pelanggan)
3. `super_admin` (Admin Eksekutif OmniPharma)
4. `admin_client` (Admin Cabang Apotek)
5. `peran_client_pengguna` (Penugasan peran staf per cabang)
6. `peta_pengawas_staf` (Hierarki pengawasan apoteker terhadap staf)
7. `kategori_obat` (Penggolongan obat: Keras, Bebas, Bebas Terbatas)
8. `obat` (Master data obat & batch kadaluwarsa FEFO)
9. `stok_client` (Inventaris stok independen per cabang)
10. `pesanan` (Order farmasi & metode layanan click & collect / delivery)
11. `item_pesanan` (Item snapshot harga satuan & subtotal)
12. `resep` (Resep digital, SIP dokter, dan verifikasi apoteker)
13. `pembayaran` (Metode & status pelunasan transaksi)
14. `permintaan_retur` (Pengajuan retur obat rusak/salah)
15. `ringkasan_periode_penjualan` (Agregasi omzet & order per cabang untuk KPI pusat)
16. `log_audit` (Rekam jejak audit keamanan)

## Invariant Terbukti (Automated Tests)
| Invariant | Status | Bukti Pengujian |
|---|---|---|
| UUID Primary Keys pada seluruh tabel | ✅ Terbukti | `ApotekDatabaseConstraintTest::test_all_models_use_uuid_primary_keys` |
| Composite Unique Stock `(id_obat, id_client)` | ✅ Terbukti | `ApotekDatabaseConstraintTest::test_stock_composite_unique_constraint_enforced` |
| Imutabilitas Snapshot Harga (ADR-0008) | ✅ Terbukti | `ApotekDatabaseConstraintTest::test_price_snapshot_immutability_on_order_items` |
| Idempotensi Penutupan Periode Penjualan | ✅ Terbukti | `ApotekDatabaseConstraintTest::test_period_summary_composite_unique_constraint_enforced` |
| Presisi Tipe Data Desimal (Monetary & Stock) | ✅ Terbukti | `ApotekDatabaseConstraintTest::test_decimal_casting_on_monetary_columns` |
| Migrasi Up & Down Simetris | ✅ Terbukti | `migrate:fresh` dan `migrate:rollback` exit code 0 |
