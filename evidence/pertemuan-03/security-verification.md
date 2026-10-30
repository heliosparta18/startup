# Pertemuan 03 — Security & Data Verification (Apotek Multi-Klien)

Fokus Keamanan dan Integritas Data Lapisan Database & Middleware:

| Kontrol Keamanan | Status | Mekanisme & Bukti |
|---|---|---|
| Role-Based Access Control (RBAC) | ✅ Lolos | Middleware `EnsureUserHasRole` membatasi rute tenant (`apoteker`), admin pusat (`super_admin`), dan customer (`pelanggan`). Diuji di `ApotekFoundationTest`. |
| Cross-Role Authorization Isolation | ✅ Lolos | Pelanggan mengakses portal apoteker atau admin menghasilkan HTTP 403 Forbidden. Apoteker mengakses dashboard eksekutif menghasilkan HTTP 403. |
| Suspended User Quarantine | ✅ Lolos | Pengguna non-aktif (`aktif = false`) ditolak aksesnya dengan HTTP 403 Forbidden. |
| Prescription Legality Control | ✅ Lolos | Verifikasi resep digital menuntut validasi SIP dokter dan kesesuaian dosis sebelum status beralih ke `disetujui`. |
| Controlled Denormalization Integrity | ✅ Lolos | Nilai transaksi historis (`item_pesanan.harga_satuan`) diisolasi secara permanen dari perubahan harga katalog dinamis (`obat.harga`). |
| Multi-Branch Inventory Collision Guard | ✅ Lolos | Unique constraint `stok_client(id_obat, id_client)` menjamin tidak terjadi inkonsistensi record stok ganda untuk cabang yang sama. |
| Audit Trail Persistence | ✅ Lolos | Operasi penting (seeding, perubahan stok, status verifikasi) dicatat dalam tabel `log_audit`. |
