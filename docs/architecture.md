# Arsitektur : Apotek Multi-Klien (Modular Monolith)

Satu aplikasi Laravel 13, satu database MariaDB/MySQL 8.4, satu deployment. Batas domain dibuat eksplisit di kode (`app/Modules/*`) sebagai vertical slice bounded context untuk jaringan apotek multi-klien (OmniPharma Network).

## Modul & Tanggung Jawab (6 Bounded Contexts)

| Modul | Tanggung Jawab Domain | Status Registrasi Provider |
|---|---|---|
| `Admin` | Administrasi multi-klien apotek, penugasan pengawas/staf, log audit aktivitas sistem. | `AdminServiceProvider::class` |
| `Catalog` | Master obat, kategori golongan obat (Bebas, Bebas Terbatas, Wajib Resep), stok inventori per cabang. | `CatalogServiceProvider::class` |
| `Ordering` | Keranjang belanja, pesanan obat, pemesanan langsung, reservasi click & collect. | `OrderingServiceProvider::class` |
| `Prescription` | Resep digital, antrean telaah resep (#ORD-20260917-0042), verifikasi SIP dokter & dosis oleh apoteker. | `PrescriptionServiceProvider::class` |
| `Payments` | Pembayaran digital (transfer, e-wallet, cod), verifikasi bukti pembayaran, pengajuan dan retur barang. | `PaymentsServiceProvider::class` |
| `Reporting` | Rekapitulasi penjualan lintas cabang, dashboard eksekutif, penutupan periode penjualan akuntansi. | `ReportingServiceProvider::class` |

*Catatan: Modul `Prescription` menggantikan modul legacy `Kitchen` untuk domain kefarmasian.*

### Struktur per Modul (Vertical Slice)

Setiap modul mengelola berkas fiturnya secara terisolasi:

```
app/Modules/{Modul}/
├── {Modul}ServiceProvider.php   ← extends App\Modules\ModuleServiceProvider (alias: huruf kecil)
├── Http/Controllers/            ← controller fitur modul
├── Actions/  Data/  Services/   ← logika aplikasi & workflow domain
├── routes/{customer,tenant,admin}.php   ← route fitur portal
└── resources/views/             ← view('{alias}::...')
    └── livewire/                ← <livewire:{alias}::nama-komponen />
```

`ModuleServiceProvider::boot()` memuat otomatis `routes/*.php`, namespace view `{alias}::`, dan namespace Livewire `{alias}::`. Seluruh 6 provider modul terdaftar pada `bootstrap/providers.php`.

**Shared Kernel:** Model Eloquent pada `app/Models/` (16 entitas PDM revisi), middleware keamanan `app/Http/Middleware/EnsureUserHasRole.php`, layout & komponen UI `resources/views/layouts/`, serta routing helper `app/Support/Routing/PortalRoutes.php`.

## Routing per Konteks Portal

| Portal | Ref Mockup | URL Prefix | Name Prefix | Middleware Pipeline |
|---|---|---|---|---|
| **Pelanggan (Customer)** | Mockup Layar 1 | `klien/{client}` & `/` | `customer.` | `['web']` |
| **Apoteker Cabang (Tenant)** | Mockup Layar 2 | `cabang/{client}` | `tenant.` | `['web', 'auth', 'verified', 'role:apoteker']` |
| **Super Admin Pusat (Admin)** | Mockup Layar 3 & 4 | `admin` | `admin.` | `['web', 'auth', 'verified', 'role:super_admin']` |

Grup tiap portal didefinisikan secara tunggal pada `App\Support\Routing\PortalRoutes`. Route inti didaftarkan pada `routes/{customer,tenant,admin}.php`, dan rute fitur modul didaftarkan pada `app/Modules/{Modul}/routes/`.

## Keamanan & Kontrak Otorisasi

1. **Autentikasi Server-Side**:
   - Rute internal tenant dan admin dijaga oleh middleware `auth` dan `verified`.
   - Tamu yang belum login dialihkan otomatis ke rute login (`/login`).
2. **Role-Based Access Control (RBAC)**:
   - Ditegakkan oleh `EnsureUserHasRole` (`role:super_admin`, `role:apoteker`, `role:pelanggan`).
   - Pengguna dengan peran tidak sesuai ditolak dengan kode status **403 Forbidden**.
   - Pengguna nonaktif (`aktif = false` atau `status != 'active'`) langsung ditolak dengan **403 Forbidden**.

## Prinsip Antislop UI & Aksesibilitas

1. **Touch Target**: Seluruh tombol, filter pills, dan input form memiliki tinggi target minimal **44px** (`min-h-11`).
2. **Tipografi & Kontras**: Memenuhi standar WCAG AA (rasio kontras normal >= 4.5:1, teks besar >= 3:1).
3. **Copywriting Alami**: Tidak menggunakan karakter em dash (`—`) pada teks UI; menggunakan tanda hubung, titik dua, atau kurung.
4. **Data Domain Nyata**: Menghilangkan teks placeholder generik, menampilkan data katalog sediaan obat, peringatan batch FEFO (H-23), dan KPI omzet riil sesuai Mockup Layar 1 s/d 4.
