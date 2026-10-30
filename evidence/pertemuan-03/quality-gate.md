# Pertemuan 03 — Quality Gate

Dijalankan pada branch `feat/pertemuan-03-database`, root repository. Database test: MariaDB/MySQL `kantin_test` (port 3306). Skema PDM Farmasi Multi-Klien 16 entitas baseline.

| Command | Exit | Ringkasan |
|---|---:|---|
| `php artisan migrate:fresh --seed` | 0 | 5 migrasi modul Apotek (16 entitas PDM) + bawaan framework + DemoApotekSeeder DONE |
| `php artisan migrate:rollback` | 0 | down() simetris; seluruh tabel Apotek rollback bersih tanpa foreign key constraint error |
| `php artisan db:seed --class=DemoApotekSeeder` (run ke-2) | 0 | Idempoten via `firstOrCreate`/`updateOrCreate`; integritas relasi terjaga |
| `php artisan test` | 0 | **70 passed** (241 assertions), 0 failures |
| `./vendor/bin/pint --test` | 0 | Pint 1.27.1 passed (0 styling issues) |
| `./vendor/bin/phpstan analyse --memory-limit=512M` | 0 | PHPStan Level 7 passed (0 errors) |
| `npm run build` | 0 | Vite production bundle built successfully (2.76s) |
| `git diff --check` | 0 | Bersih dari trailing whitespace dan merge conflict markers |

## Database Constraint Evidence (ApotekDatabaseConstraintTest)
- UUID Primary Keys: Semua entitas (16 model) memiliki PK berbasis UUID RFC 4122.
- Composite Stock Unique `(id_obat, id_client)`: Duplikasi insert melemparkan `QueryException` (Error 1062).
- Price Snapshot Immutability (ADR-05 / ADR-0008): Perubahan harga katalog `Obat.harga` tidak merubah snapshot riwayat `item_pesanan.harga_satuan`.
- Idempotent Period Closing: Composite unique `(id_client, periode_mulai, periode_selesai)` menolak duplikasi ringkasan omzet cabang.
