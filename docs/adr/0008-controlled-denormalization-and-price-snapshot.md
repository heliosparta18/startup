# ADR-0008 : Controlled Denormalization & Price Snapshot Immutability (ADR-05)

- Status: Diterima
- Tanggal: 2026-09-21
- Konteks: Pada sistem ritel farmasi dan e-commerce obat, harga master obat dan identitas cabang dapat mengalami revisi berkala oleh manajemen. Jika skema ternormalisasi murni (3NF murni hanya merujuk foreign key ke tabel master obat), pembaruan harga master akan secara otomatis mengubah nilai historis transaksi masa lalu yang telah dicetak pada kuitansi dan dilaporkan pada pembukuan pajak.

## Keputusan
1. **Snapshot pada `item_pesanan`**:
   - Kolom `nama_obat` (VARCHAR 255) dan `harga_satuan` (DECIMAL 12,2) disimpan secara eksplisit sebagai snapshot denormalisasi terkendali pada saat pesanan obat dibuat.
   - Determinan kolom snapshot adalah *peristiwa transaksi pemesanan itu sendiri*, bukan status master obat saat ini.
2. **Snapshot pada `ringkasan_periode_penjualan`**:
   - Kolom `nama_client` dan `total_obat_terjual` disimpan sebagai snapshot saat penutupan buku periode akuntansi dilakukan oleh super admin (Mockup Layar 3-4).
3. **Immutability & Append-Only (ADR-05)**:
   - Data historis transaksi pada `item_pesanan` dan `ringkasan_periode_penjualan` bersifat append-only dan tidak boleh diubah (immutable).
   - Koreksi transaksi dilakukan melalui entri penyesuaian (retur obat via `permintaan_retur` atau adjustment baris baru), bukan memodifikasi baris transaksi yang telah selesai.

## Konsekuensi
- Menghilangkan anomali pembaruan (update anomaly) pada pembukuan dan kuitansi pasien.
- Integritas audit trail dan laporan keuangan terlindungi secara permanen dari fluktuasi perubahan harga obat master.
- Terverifikasi secara otomatis melalui pengujian fitur `tests/Feature/ApotekDatabaseConstraintTest.php::test_order_item_price_snapshot_remains_immutable_when_master_price_changes`.
