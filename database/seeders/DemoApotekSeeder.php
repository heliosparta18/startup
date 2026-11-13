<?php

namespace Database\Seeders;

use App\Models\AdminClient;
use App\Models\Client;
use App\Models\ItemPesanan;
use App\Models\KategoriObat;
use App\Models\LogAudit;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\PeranClientPengguna;
use App\Models\Pesanan;
use App\Models\PetaPengawasStaf;
use App\Models\Resep;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoApotekSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cabang / Klien
        $bwiKota = Client::firstOrCreate(
            ['nama_client' => 'Cabang Banyuwangi Kota'],
            [
                'id_client' => 'a1b2c3d4-0001-4000-8000-000000000001',
                'slug' => 'banyuwangi-kota',
                'alamat' => 'Jl. Ahmad Yani No. 45, Banyuwangi Kota',
                'aktif' => true,
            ]
        );

        $rogojampi = Client::firstOrCreate(
            ['nama_client' => 'Cabang Rogojampi'],
            [
                'id_client' => 'a1b2c3d4-0002-4000-8000-000000000002',
                'slug' => 'rogojampi',
                'alamat' => 'Jl. Raya Rogojampi No. 12, Rogojampi',
                'aktif' => true,
            ]
        );

        // 2. Pengguna (Super Admin, Apoteker, Staf, Kasir, Pelanggan)
        // 2a. Demo User 1: Super Admin Pusat (daffa@omnipharma.test / password)
        $superAdminUser = Pengguna::firstOrCreate(
            ['telepon_email' => 'daffa@omnipharma.test'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'Muhammad Daffa Ariby',
                'peran' => 'super_admin',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-08-01',
                'aktif' => true,
            ]
        );

        SuperAdmin::firstOrCreate(
            ['id_pengguna' => $superAdminUser->id_pengguna],
            [
                'id_super_admin' => (string) Str::uuid(),
                'nama' => $superAdminUser->nama_lengkap,
                'email' => $superAdminUser->telepon_email,
            ]
        );
        User::updateOrCreate(
            ['email' => $superAdminUser->telepon_email],
            [
                'name' => $superAdminUser->nama_lengkap,
                'password' => bcrypt('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2b. Demo User 2: Apoteker BWI (bambang@omnipharma.test / password)
        $apotekerBwi = Pengguna::firstOrCreate(
            ['telepon_email' => 'bambang@omnipharma.test'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'apt. Bambang, S.Farm',
                'peran' => 'apoteker',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-08-10',
                'aktif' => true,
            ]
        );

        AdminClient::firstOrCreate(
            ['id_pengguna' => $apotekerBwi->id_pengguna, 'id_client' => $bwiKota->id_client],
            [
                'id_admin' => (string) Str::uuid(),
                'nama' => $apotekerBwi->nama_lengkap,
                'email' => $apotekerBwi->telepon_email,
            ]
        );

        PeranClientPengguna::firstOrCreate(
            ['id_pengguna' => $apotekerBwi->id_pengguna, 'id_client' => $bwiKota->id_client],
            [
                'id' => (string) Str::uuid(),
                'peran' => 'staf_klien',
                'ditugaskan_dari' => '2026-08-10',
            ]
        );
        User::updateOrCreate(
            ['email' => $apotekerBwi->telepon_email],
            [
                'name' => $apotekerBwi->nama_lengkap,
                'password' => bcrypt('password'),
                'role' => 'apoteker',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2c. Demo User 3: Apoteker Rogojampi (dewi@omnipharma.test / password)
        $apotekerRogo = Pengguna::firstOrCreate(
            ['telepon_email' => 'dewi@omnipharma.test'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'apt. Dinda Ayu Shafira, S.Farm.',
                'peran' => 'apoteker',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-08-12',
                'aktif' => true,
            ]
        );
        $apotekerRogo->update([
            'nama_lengkap' => 'apt. Dinda Ayu Shafira, S.Farm.',
        ]);

        $adminRogo = AdminClient::firstOrCreate(
            ['id_pengguna' => $apotekerRogo->id_pengguna, 'id_client' => $rogojampi->id_client],
            [
                'id_admin' => (string) Str::uuid(),
                'nama' => $apotekerRogo->nama_lengkap,
                'email' => $apotekerRogo->telepon_email,
            ]
        );
        $adminRogo->update([
            'nama' => $apotekerRogo->nama_lengkap,
        ]);

        PeranClientPengguna::firstOrCreate(
            ['id_pengguna' => $apotekerRogo->id_pengguna, 'id_client' => $rogojampi->id_client],
            [
                'id' => (string) Str::uuid(),
                'peran' => 'staf_klien',
                'ditugaskan_dari' => '2026-08-12',
            ]
        );
        User::updateOrCreate(
            ['email' => $apotekerRogo->telepon_email],
            [
                'name' => $apotekerRogo->nama_lengkap,
                'password' => bcrypt('password'),
                'role' => 'apoteker',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'dinda@omnipharma.test'],
            [
                'name' => $apotekerRogo->nama_lengkap,
                'password' => bcrypt('password'),
                'role' => 'apoteker',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // 2d. Demo User 4: Kasir BWI (kasir.bwi@omnipharma.test / password)
        $kasirBwi = Pengguna::firstOrCreate(
            ['telepon_email' => 'kasir.bwi@omnipharma.test'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'Kasir Staf Banyuwangi',
                'peran' => 'admin',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-08-15',
                'aktif' => true,
            ]
        );

        AdminClient::firstOrCreate(
            ['id_pengguna' => $kasirBwi->id_pengguna, 'id_client' => $bwiKota->id_client],
            [
                'id_admin' => (string) Str::uuid(),
                'nama' => $kasirBwi->nama_lengkap,
                'email' => $kasirBwi->telepon_email,
            ]
        );

        PeranClientPengguna::firstOrCreate(
            ['id_pengguna' => $kasirBwi->id_pengguna, 'id_client' => $bwiKota->id_client],
            [
                'id' => (string) Str::uuid(),
                'peran' => 'staf_klien',
                'ditugaskan_dari' => '2026-08-15',
            ]
        );
        User::updateOrCreate(
            ['email' => $kasirBwi->telepon_email],
            [
                'name' => $kasirBwi->nama_lengkap,
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Pengguna pendukung lainnya (backward compatibility untuk test suite)
        $apotekerUser = Pengguna::firstOrCreate(
            ['telepon_email' => 'candia@omnipharma.test'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'apt. Candia Purwa, S.Farm',
                'peran' => 'apoteker',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-08-10',
                'aktif' => true,
            ]
        );

        AdminClient::firstOrCreate(
            ['id_pengguna' => $apotekerUser->id_pengguna, 'id_client' => $bwiKota->id_client],
            [
                'id_admin' => (string) Str::uuid(),
                'nama' => $apotekerUser->nama_lengkap,
                'email' => $apotekerUser->telepon_email,
            ]
        );

        PeranClientPengguna::firstOrCreate(
            ['id_pengguna' => $apotekerUser->id_pengguna, 'id_client' => $bwiKota->id_client],
            [
                'id' => (string) Str::uuid(),
                'peran' => 'staf_klien',
                'ditugaskan_dari' => '2026-08-10',
            ]
        );

        $stafRogojampi = Pengguna::firstOrCreate(
            ['telepon_email' => 'arya@omnipharma.test'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'Arya Dwi Mahendra',
                'peran' => 'admin',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-08-15',
                'aktif' => true,
            ]
        );

        AdminClient::firstOrCreate(
            ['id_pengguna' => $stafRogojampi->id_pengguna, 'id_client' => $rogojampi->id_client],
            [
                'id_admin' => (string) Str::uuid(),
                'nama' => $stafRogojampi->nama_lengkap,
                'email' => $stafRogojampi->telepon_email,
            ]
        );

        // Hierarki pengawasan: apt. Candia Purwa mengawasi Arya Dwi Mahendra di Cabang Bwi Kota
        PetaPengawasStaf::firstOrCreate(
            ['id_staf' => $stafRogojampi->id_pengguna, 'id_pengawas' => $apotekerUser->id_pengguna, 'id_client' => $bwiKota->id_client],
            [
                'id' => (string) Str::uuid(),
                'berlaku_dari' => '2026-08-15',
            ]
        );
        $pelangganGuntur = Pengguna::firstOrCreate(
            ['telepon_email' => 'gunturafandy5@gmail.com'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'Guntur Afandy',
                'peran' => 'pelanggan',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-09-01',
                'aktif' => true,
            ]
        );

        $pelangganUser = Pengguna::firstOrCreate(
            ['telepon_email' => 'candi@gmail.com'],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama_lengkap' => 'Candi Chier',
                'peran' => 'pelanggan',
                'password' => bcrypt('password'),
                'terdaftar_pada' => '2026-09-01',
                'aktif' => true,
            ]
        );

        // Sinkronisasi seluruh akun pengguna ke tabel users untuk login Laravel Fortify & Auth Guard
        foreach (Pengguna::all() as $p) {
            User::updateOrCreate(
                ['email' => $p->telepon_email],
                [
                    'name' => $p->nama_lengkap,
                    'password' => $p->password ?: bcrypt('password'),
                    'role' => $p->peran,
                    'status' => $p->aktif ? 'active' : 'inactive',
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3. Kategori Obat (Mockup Layar 1)
        $katKeras = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Wajib Resep (Keras)'],
            [
                'id_kategori' => (string) Str::uuid(),
                'wajib_resep' => true,
                'deskripsi' => 'Obat keras wajib disertai resep dokter yang sah dan terverifikasi SIP-nya.',
            ]
        );

        $katBebas = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Obat Bebas'],
            [
                'id_kategori' => (string) Str::uuid(),
                'wajib_resep' => false,
                'deskripsi' => 'Obat bebas yang dapat dibeli langsung tanpa resep dokter.',
            ]
        );

        $katBebasTerbatas = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Bebas Terbatas'],
            [
                'id_kategori' => (string) Str::uuid(),
                'wajib_resep' => false,
                'deskripsi' => 'Obat bebas terbatas dengan tanda lingkaran biru dan peringatan pemakaian.',
            ]
        );

        // 4. Master Data Obat & Batch FEFO
        $amodiaquine = Obat::firstOrCreate(
            ['nama_obat' => 'Amodiaquine 200mg'],
            [
                'id_obat' => (string) Str::uuid(),
                'merek' => 'Amodiaquine',
                'jenis_sediaan' => 'Dosis terapi infeksi • Strip 10 tablet',
                'jumlah_obat' => 92,
                'id_kategori' => $katKeras->id_kategori,
                'harga' => 45000.00,
                'expired' => '2026-10-10', // Mendekati kedaluwarsa H-23 hari (Mockup Layar 2)
                'batch_number' => '#BTC-2024A',
            ]
        );

        $paracetamol = Obat::firstOrCreate(
            ['nama_obat' => 'Paracetamol 500mg'],
            [
                'id_obat' => (string) Str::uuid(),
                'merek' => 'Sanmol',
                'jenis_sediaan' => 'Pereda demam & nyeri • Strip 10 kaplet',
                'jumlah_obat' => 75,
                'id_kategori' => $katBebas->id_kategori,
                'harga' => 8500.00,
                'expired' => '2027-12-20',
                'batch_number' => '#PCT-8891',
            ]
        );

        $amoxicillin = Obat::firstOrCreate(
            ['nama_obat' => 'Amoxicillin 500mg'],
            [
                'id_obat' => (string) Str::uuid(),
                'merek' => 'Amoxan',
                'jenis_sediaan' => 'Antibiotik • Strip 10 kapsul',
                'jumlah_obat' => 35,
                'id_kategori' => $katKeras->id_kategori,
                'harga' => 22000.00,
                'expired' => '2026-11-15',
                'batch_number' => '#AMX-7712',
            ]
        );

        $antasida = Obat::firstOrCreate(
            ['nama_obat' => 'Antasida Doen'],
            [
                'id_obat' => (string) Str::uuid(),
                'merek' => 'Promag',
                'jenis_sediaan' => 'Obat lambung & maag • Blister 12 tablet',
                'jumlah_obat' => 70,
                'id_kategori' => $katBebas->id_kategori,
                'harga' => 9000.00,
                'expired' => '2027-08-10',
                'batch_number' => '#ANT-3310',
            ]
        );

        // 5. Stok per Klien / Cabang (Mockup Layar 1)
        StokClient::updateOrCreate(
            ['id_obat' => $amodiaquine->id_obat, 'id_client' => $bwiKota->id_client],
            ['id' => (string) Str::uuid(), 'jumlah_stok' => 12] // 12 strip (Tersedia)
        );
        StokClient::updateOrCreate(
            ['id_obat' => $amodiaquine->id_obat, 'id_client' => $rogojampi->id_client],
            ['id' => (string) Str::uuid(), 'jumlah_stok' => 0] // 0 strip (Kosong)
        );

        StokClient::updateOrCreate(
            ['id_obat' => $paracetamol->id_obat, 'id_client' => $bwiKota->id_client],
            ['id' => (string) Str::uuid(), 'jumlah_stok' => 50] // >50 strip
        );
        StokClient::updateOrCreate(
            ['id_obat' => $paracetamol->id_obat, 'id_client' => $rogojampi->id_client],
            ['id' => (string) Str::uuid(), 'jumlah_stok' => 25] // Tersedia
        );

        // 6. Transaksi Pesanan & Verifikasi Resep (Mockup Layar 2)
        $pesanan = Pesanan::firstOrCreate(
            ['nomor_pesanan' => 'ORD-20260917-0042'],
            [
                'id_pesanan' => (string) Str::uuid(),
                'id_pengguna' => $pelangganUser->id_pengguna,
                'id_client' => $bwiKota->id_client,
                'status' => 'menunggu_verifikasi',
                'metode_layanan' => 'click_collect',
                'dibuat_pada' => '2026-09-17 08:30:00',
            ]
        );

        $pesananRogojampi = Pesanan::firstOrCreate(
            ['nomor_pesanan' => 'ORD-20260917-0099'],
            [
                'id_pesanan' => (string) Str::uuid(),
                'id_pengguna' => $pelangganUser->id_pengguna,
                'id_client' => $rogojampi->id_client,
                'status' => 'diproses',
                'metode_layanan' => 'ambil_di_tempat',
            ]
        );

        ItemPesanan::firstOrCreate(
            ['id_pesanan' => $pesanan->id_pesanan, 'id_obat' => $amodiaquine->id_obat],
            [
                'id_item' => (string) Str::uuid(),
                'kuantitas' => 1,
                'nama_obat' => $amodiaquine->nama_obat,
                'harga_satuan' => $amodiaquine->harga,
                'subtotal' => $amodiaquine->harga,
            ]
        );

        Resep::firstOrCreate(
            ['id_pesanan' => $pesanan->id_pesanan],
            [
                'id_resep' => (string) Str::uuid(),
                'url_gambar' => '/images/resep-amodiaquine.jpg',
                'status_verifikasi' => 'menunggu',
                'nama_dokter' => 'dr. Budi, Sp.PD',
                'sip_dokter' => '503/SIP/2024',
                'nama_pasien' => 'Candi Chier (88 th)',
                'sip_valid' => true,
                'dosis_valid' => true,
                'catatan_apoteker' => 'Resep asli dan masih berlaku.',
                'dibuat_pada' => '2026-09-17 08:30:00',
            ]
        );

        Pembayaran::firstOrCreate(
            ['id_pesanan' => $pesanan->id_pesanan],
            [
                'id_pembayaran' => (string) Str::uuid(),
                'metode_pembayaran' => 'transfer',
                'jumlah' => 45000.00,
                'status' => 'menunggu',
            ]
        );

        // 7. Ringkasan Periode Penjualan Eksekutif (Mockup Layar 3-4)
        $rkBwi = RingkasanPeriodePenjualan::firstOrNew(['id_client' => $bwiKota->id_client]);
        $rkBwi->id_ringkasan = $rkBwi->id_ringkasan ?: (string) Str::uuid();
        $rkBwi->nama_client = 'Cabang Banyuwangi Kota';
        $rkBwi->periode_mulai = '2026-09-01';
        $rkBwi->periode_selesai = '2026-09-16';
        $rkBwi->total_obat_terjual = '820';
        $rkBwi->total_pesanan = 820;
        $rkBwi->total_pendapatan = 68200000.00;
        $rkBwi->total_retur = 1;
        $rkBwi->status_terkunci = false;
        $rkBwi->save();

        $rkRogo = RingkasanPeriodePenjualan::firstOrNew(['id_client' => $rogojampi->id_client]);
        $rkRogo->id_ringkasan = $rkRogo->id_ringkasan ?: (string) Str::uuid();
        $rkRogo->nama_client = 'Cabang Rogojampi';
        $rkRogo->periode_mulai = '2026-09-01';
        $rkRogo->periode_selesai = '2026-09-16';
        $rkRogo->total_obat_terjual = '610';
        $rkRogo->total_pesanan = 610;
        $rkRogo->total_pendapatan = 45400000.00;
        $rkRogo->total_retur = 2;
        $rkRogo->status_terkunci = false;
        $rkRogo->save();

        // 8. Log Audit
        LogAudit::create([
            'id_audit' => (string) Str::uuid(),
            'id_aktor' => $superAdminUser->id_pengguna,
            'aksi' => 'SEED_BASELINE_APOTEK',
            'nama_entitas' => 'client',
            'id_entitas' => $bwiKota->id_client,
            'payload' => ['keterangan' => 'Inisialisasi baseline Apotek OmniPharma'],
        ]);
    }
}
