<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ItemPesanan;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Constraint basis data Apotek Multi-Klien diuji pada MariaDB / MySQL nyata:
 * UUID primary key, composite unique stock (id_obat, id_client),
 * immutability snapshot harga pada item_pesanan (ADR-05),
 * dan idempotency ringkasan periode penjualan.
 */
class ApotekDatabaseConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_entities_use_valid_uuid_primary_keys(): void
    {
        $client = Client::factory()->create();
        $this->assertTrue(Str::isUuid((string) $client->id_client), 'Client ID bukan UUID valid');

        $pengguna = Pengguna::factory()->create();
        $this->assertTrue(Str::isUuid((string) $pengguna->id_pengguna), 'Pengguna ID bukan UUID valid');

        $kategori = KategoriObat::factory()->create();
        $this->assertTrue(Str::isUuid((string) $kategori->id_kategori), 'KategoriObat ID bukan UUID valid');

        $obat = Obat::factory()->create(['id_kategori' => $kategori->id_kategori]);
        $this->assertTrue(Str::isUuid((string) $obat->id_obat), 'Obat ID bukan UUID valid');

        $stok = StokClient::factory()->create([
            'id_obat' => $obat->id_obat,
            'id_client' => $client->id_client,
        ]);
        $this->assertTrue(Str::isUuid((string) $stok->id), 'StokClient ID bukan UUID valid');

        $pesanan = Pesanan::factory()->create([
            'id_pengguna' => $pengguna->id_pengguna,
            'id_client' => $client->id_client,
        ]);
        $this->assertTrue(Str::isUuid((string) $pesanan->id_pesanan), 'Pesanan ID bukan UUID valid');
    }

    public function test_duplicate_stock_record_for_same_drug_and_client_is_rejected(): void
    {
        $client = Client::factory()->create();
        $obat = Obat::factory()->create();

        // Baris pertama stok obat pada cabang tertentu
        StokClient::create([
            'id' => (string) Str::uuid(),
            'id_obat' => $obat->id_obat,
            'id_client' => $client->id_client,
            'jumlah_stok' => 50,
            'diperbarui_pada' => now(),
        ]);

        // Baris kedua dengan kombinasi (id_obat, id_client) yang sama wajib ditolak database
        $this->expectException(QueryException::class);
        StokClient::create([
            'id' => (string) Str::uuid(),
            'id_obat' => $obat->id_obat,
            'id_client' => $client->id_client,
            'jumlah_stok' => 20,
            'diperbarui_pada' => now(),
        ]);
    }

    public function test_order_item_price_snapshot_remains_immutable_when_master_price_changes(): void
    {
        $client = Client::factory()->create();
        $pengguna = Pengguna::factory()->create();
        $obat = Obat::factory()->create([
            'nama_obat' => 'Amodiaquine 200mg',
            'harga' => 45000,
        ]);

        $pesanan = Pesanan::factory()->create([
            'id_pengguna' => $pengguna->id_pengguna,
            'id_client' => $client->id_client,
        ]);

        // Simpan item pesanan dengan snapshot harga saat transaksi (Rp 45.000)
        $item = ItemPesanan::create([
            'id_item' => (string) Str::uuid(),
            'id_pesanan' => $pesanan->id_pesanan,
            'id_obat' => $obat->id_obat,
            'kuantitas' => 2,
            'nama_obat' => $obat->nama_obat,
            'harga_satuan' => $obat->harga,
            'subtotal' => $obat->harga * 2,
            'dibuat_pada' => now(),
        ]);

        $this->assertEquals(45000, (float) $item->harga_satuan);
        $this->assertEquals(90000, (float) $item->subtotal);

        // Simulasi perubahan harga master obat oleh pengelola di kemudian hari (naik ke Rp 60.000)
        $obat->update(['harga' => 60000]);
        $this->assertEquals(60000, (float) $obat->fresh()->harga);

        // Invarian ADR-05: snapshot harga pada item pesanan historis TIDAK BERUBAH
        $freshItem = $item->fresh();
        $this->assertNotNull($freshItem);
        $this->assertEquals(45000, (float) $freshItem->harga_satuan);
        $this->assertEquals('Amodiaquine 200mg', $freshItem->nama_obat);
        $this->assertEquals(90000, (float) $freshItem->subtotal);
    }

    public function test_duplicate_sales_period_summary_for_same_client_and_dates_is_rejected(): void
    {
        $client = Client::factory()->create();
        $mulai = '2026-09-01';
        $selesai = '2026-09-16';

        // Penutupan periode pertama untuk Cabang Banyuwangi Kota
        RingkasanPeriodePenjualan::create([
            'id_ringkasan' => (string) Str::uuid(),
            'id_client' => $client->id_client,
            'nama_client' => $client->nama_client,
            'total_obat_terjual' => '820 item',
            'periode_mulai' => $mulai,
            'periode_selesai' => $selesai,
            'total_pesanan' => 820,
            'total_pendapatan' => 68200000,
            'total_retur' => 1,
            'status_terkunci' => true,
            'dibuat_pada' => now(),
        ]);

        // Usaha menutup ulang periode yang sama pada klien yang sama wajib ditolak (idempotency constraint)
        $this->expectException(QueryException::class);
        RingkasanPeriodePenjualan::create([
            'id_ringkasan' => (string) Str::uuid(),
            'id_client' => $client->id_client,
            'nama_client' => $client->nama_client,
            'total_obat_terjual' => '820 item',
            'periode_mulai' => $mulai,
            'periode_selesai' => $selesai,
            'total_pesanan' => 820,
            'total_pendapatan' => 68200000,
            'total_retur' => 1,
            'status_terkunci' => true,
            'dibuat_pada' => now(),
        ]);
    }

    public function test_money_columns_cast_properly_as_decimal(): void
    {
        $obat = Obat::factory()->create(['harga' => 45000.50]);
        $freshObat = $obat->fresh();
        $this->assertNotNull($freshObat);
        $this->assertEquals(45000.50, (float) $freshObat->harga);
    }
}
