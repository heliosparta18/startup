# ADR-0006 : Modular Monolith & Registrasi Modul (Apotek Multi-Klien)

- Status: Diterima
- Tanggal: 2026-08-16 (Diperbarui: 2026-09-21)
- Konteks: Sistem penjualan obat online apotek multi-klien (OmniPharma Network) memerlukan pemisahan domain bounded context yang tegas tanpa kompleksitas operasional microservices (satu deployment, satu database, transaksi lokal ACID).

## Keputusan
1. Enam bounded context diimplementasikan pada `app/Modules/{Admin,Catalog,Ordering,Payments,Prescription,Reporting}`. Modul `Prescription` menggantikan modul legacy `Kitchen` untuk menangani telaah resep dokter digital, verifikasi legalitas SIP, dan kesesuaian dosis geriatri/pediatri.
2. Setiap modul merupakan **vertical slice** mandiri:
   - `{Actions, Data, Services}` untuk logika domain
   - `Http/Controllers` untuk controller fitur modul
   - `routes/{customer,tenant,admin}.php` untuk rute modul
   - `resources/views` dan `resources/views/livewire` untuk antarmuka ber-namespace `{alias}::`
   - `{Modul}ServiceProvider` turunan `App\Modules\ModuleServiceProvider`.
3. Satu ServiceProvider per modul terdaftar di `bootstrap/providers.php`.
4. Rute portal dikelompokkan berdasarkan konteks peran:
   - Pelanggan (`customer.`): `klien/{client}` & `/`
   - Apoteker Cabang (`tenant.`): `cabang/{client}`
   - Super Admin Pusat (`admin.`): `admin`
   Grup didefinisikan secara sentral pada `App\Support\Routing\PortalRoutes`.
5. Otorisasi server-side ditegakkan via middleware `role` (`EnsureUserHasRole`) yang memverifikasi peran `super_admin`, `apoteker`, `pelanggan` dan status keaktifan akun pengguna.

## Konsekuensi
- Modul Prescription menjadi modul inti untuk kepatuhan regulasi kefarmasian Indonesia (UU Kesehatan & Permenkes terkait resep elektronik).
- Model Eloquent, middleware keamanan, dan antarmuka layout Antislop tetap berada pada shared kernel di lokasi standar Laravel.
