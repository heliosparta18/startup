<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\StokClient;
use App\Models\User;
use App\Modules\Catalog\Services\StockFefoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FefoStockRotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_batches_are_sorted_by_earliest_expiration_date_first_expired_first_out(): void
    {
        $client = Client::factory()->create(['nama_client' => 'Cabang Banyuwangi Kota']);
        $kategori = KategoriObat::factory()->create();

        // Batch 1: Expired dalam 20 hari
        $obatBatch20d = Obat::factory()->create([
            'nama_obat' => 'Amoxicillin Batch 20D',
            'id_kategori' => $kategori->id_kategori,
            'expired' => now()->addDays(20)->toDateString(),
        ]);
        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatBatch20d->id_obat,
            'jumlah_stok' => 15,
        ]);

        // Batch 2: Expired dalam 100 hari
        $obatBatch100d = Obat::factory()->create([
            'nama_obat' => 'Amoxicillin Batch 100D',
            'id_kategori' => $kategori->id_kategori,
            'expired' => now()->addDays(100)->toDateString(),
        ]);
        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatBatch100d->id_obat,
            'jumlah_stok' => 50,
        ]);

        // Batch 3: Expired paling dekat dalam 5 hari
        $obatBatch5d = Obat::factory()->create([
            'nama_obat' => 'Amoxicillin Batch 5D',
            'id_kategori' => $kategori->id_kategori,
            'expired' => now()->addDays(5)->toDateString(),
        ]);
        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatBatch5d->id_obat,
            'jumlah_stok' => 10,
        ]);

        $service = app(StockFefoService::class);
        $batches = $service->getBatchesByFefo($client->id_client);

        // FEFO menjamin batch dengan expired terdekat berada pada indeks pertama (#1)
        $this->assertCount(3, $batches);
        $this->assertSame($obatBatch5d->id_obat, $batches[0]->id_obat);
        $this->assertSame($obatBatch20d->id_obat, $batches[1]->id_obat);
        $this->assertSame($obatBatch100d->id_obat, $batches[2]->id_obat);
    }

    public function test_early_warning_indicators_for_near_and_critical_expiry(): void
    {
        $client = Client::factory()->create();
        $kategori = KategoriObat::factory()->create();

        // Kritis (&le; 7 hari)
        $obatKritis = Obat::factory()->create([
            'nama_obat' => 'Obat Kritis',
            'id_kategori' => $kategori->id_kategori,
            'expired' => now()->addDays(5)->toDateString(),
        ]);
        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatKritis->id_obat,
            'jumlah_stok' => 10,
        ]);

        // Waspada (&le; 30 hari)
        $obatWaspada = Obat::factory()->create([
            'nama_obat' => 'Obat Waspada',
            'id_kategori' => $kategori->id_kategori,
            'expired' => now()->addDays(20)->toDateString(),
        ]);
        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatWaspada->id_obat,
            'jumlah_stok' => 10,
        ]);

        // Aman (> 30 hari)
        $obatAman = Obat::factory()->create([
            'nama_obat' => 'Obat Aman',
            'id_kategori' => $kategori->id_kategori,
            'expired' => now()->addDays(60)->toDateString(),
        ]);
        StokClient::factory()->create([
            'id_client' => $client->id_client,
            'id_obat' => $obatAman->id_obat,
            'jumlah_stok' => 10,
        ]);

        $service = app(StockFefoService::class);
        $batches = $service->getBatchesByFefo($client->id_client);

        $batchKritis = $batches->firstWhere('id_obat', $obatKritis->id_obat);
        $batchWaspada = $batches->firstWhere('id_obat', $obatWaspada->id_obat);
        $batchAman = $batches->firstWhere('id_obat', $obatAman->id_obat);

        $this->assertTrue((bool) $batchKritis->is_critical_ed);
        $this->assertTrue((bool) $batchKritis->is_near_ed);

        $this->assertFalse((bool) $batchWaspada->is_critical_ed);
        $this->assertTrue((bool) $batchWaspada->is_near_ed);

        $this->assertFalse((bool) $batchAman->is_critical_ed);
        $this->assertFalse((bool) $batchAman->is_near_ed);
    }

    public function test_stok_fefo_route_and_service_paginate_and_filter_batches(): void
    {
        $client = Client::factory()->create([
            'nama_client' => 'Cabang Banyuwangi Kota',
            'slug' => 'banyuwangi-kota',
        ]);
        $kategori = KategoriObat::factory()->create();

        for ($i = 1; $i <= 30; $i++) {
            $obat = Obat::factory()->create([
                'nama_obat' => $i === 1 ? 'Paracetamol Kritis' : "Obat Batch {$i}",
                'batch_number' => sprintf('BTC-%03d', $i),
                'id_kategori' => $kategori->id_kategori,
                'expired' => now()->addDays($i === 1 ? 3 : 40 + $i)->toDateString(),
            ]);

            StokClient::factory()->create([
                'id_client' => $client->id_client,
                'id_obat' => $obat->id_obat,
                'jumlah_stok' => $i === 2 ? 0 : ($i === 3 ? 5 : 25),
            ]);
        }

        $service = app(StockFefoService::class);
        $pageOne = $service->paginateBatchesByFefo($client->id_client, '', '', 25);
        $this->assertSame(30, $pageOne->total());
        $this->assertCount(25, $pageOne->items());
        $this->assertTrue((bool) $pageOne->items()[0]->is_critical_ed);

        $criticalOnly = $service->paginateBatchesByFefo($client->id_client, 'critical', '', 25);
        $this->assertSame(1, $criticalOnly->total());

        $emptyOnly = $service->paginateBatchesByFefo($client->id_client, 'empty', '', 25);
        $this->assertSame(1, $emptyOnly->total());

        $searchMatch = $service->paginateBatchesByFefo($client->id_client, '', 'BTC-003', 25);
        $this->assertSame(1, $searchMatch->total());

        $apoteker = User::factory()->create([
            'role' => 'apoteker',
            'status' => 'active',
        ]);

        $this->actingAs($apoteker)
            ->get(route('tenant.stok.fefo', ['client' => $client->slug]))
            ->assertOk()
            ->assertSee('Total 30 Batch Terdata')
            ->assertSee('PERINGATAN KRITIS: 1 Batch')
            ->assertSee('page=2');
    }
}
