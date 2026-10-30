# Pertemuan 02 — Stage Checklist (Apotek Multi-Klien)

Output: Struktur `app/Modules/*` dengan `Prescription` (menggantikan `Kitchen`), Provider `PrescriptionServiceProvider` terdaftar di `bootstrap/providers.php`, route portal customer/tenant/admin terkonfigurasi, 3 layout Antislop dan view Mockup Layar 1 s/d 4, smoke test auth dan role authorization.

| Tahap | Aktivitas | Status | Bukti |
|---|---|---|---|
| 1 | Petakan bounded context Apotek | ✅ | `docs/architecture.md` (6 modul kefarmasian: Admin, Catalog, Ordering, Prescription, Payments, Reporting), ADR-0006 |
| 2 | Struktur & provider modul | ✅ | `app/Modules/Prescription/` vertical slice dibuat, modul Kitchen dihapus; `PrescriptionServiceProvider` terdaftar di `bootstrap/providers.php`; `php artisan about` bersih |
| 3 | Route per konteks portal | ✅ | `PortalRoutes` + `routes/{customer,tenant,admin}.php`; `route:list` aktif untuk `customer.home`, `tenant.dashboard`, `admin.dashboard` |
| 4 | Tiga layout & views Antislop | ✅ | Layout `x-layouts.{customer,tenant,admin}` & views Layar 1-4; target sentuh min-h-11 (44px); tanpa em dash; data domain riil |
| 5 | Auth & navigasi RBAC | ✅ | Middleware `role` (`EnsureUserHasRole`) untuk `super_admin`, `apoteker`, `pelanggan`; proteksi 403 untuk akses silang dan pengguna nonaktif |
| 6 | Testing fondasi modul | ✅ | `tests/Feature/ModularFoundationTest.php` dan `tests/Feature/ModuleConventionTest.php` 100% lulus hijau |
| 7 | Dokumentasi arsitektur | ✅ | `docs/architecture.md`, `evidence/pertemuan-02/antislop-audit.txt`, `evidence/pertemuan-02/portal-routes.txt` |
