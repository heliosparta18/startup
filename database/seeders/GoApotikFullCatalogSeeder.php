<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\StokClient;
use App\Models\SubkategoriObat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GoApotikFullCatalogSeeder extends Seeder
{
    /**
     * Seed all scraped products from GoApotik into database idempotently.
     */
    public function run(): void
    {
        $jsonPath = database_path('data/scraped_goapotik_lengkap.json');
        if (! File::exists($jsonPath)) {
            $this->command?->error("File data tidak ditemukan di: {$jsonPath}");

            return;
        }

        $items = json_decode(File::get($jsonPath), true);
        if (! is_array($items)) {
            $this->command?->error('Gagal membaca JSON katalog.');

            return;
        }

        $clients = Client::all();
        if ($clients->isEmpty()) {
            $this->call(DemoApotekSeeder::class);
            $clients = Client::all();
        }

        $bwiClient = $clients->firstWhere('slug', 'banyuwangi-kota') ?? $clients->first();
        $rogoClient = $clients->firstWhere('slug', 'rogojampi') ?? $clients->skip(1)->first();

        $this->command?->info('Memulai seeding '.count($items).' produk GoApotik lengkap...');

        $kategoriCache = KategoriObat::all()->keyBy('nama_kategori');
        $subkategoriBySlug = SubkategoriObat::all()->keyBy('slug');
        $existingObats = Obat::all()->keyBy('slug');

        DB::transaction(function () use ($items, $clients, $bwiClient, $rogoClient, &$kategoriCache, &$subkategoriBySlug, &$existingObats) {
            foreach ($items as $index => $data) {
                $catName = trim((string) ($data['kategori'] ?? 'Obat'));
                $catSlug = Str::slug($catName);

                // 1. Kategori Utama
                $kategori = $kategoriCache->get($catName);
                if (! $kategori) {
                    $kategori = KategoriObat::firstOrCreate(
                        ['nama_kategori' => $catName],
                        [
                            'id_kategori' => (string) Str::uuid(),
                            'slug' => $catSlug,
                            'wajib_resep' => $catName === 'Obat' && ($data['wajib_resep'] ?? false),
                            'deskripsi' => "Kategori produk kesehatan {$catName}",
                        ]
                    );
                    $kategoriCache->put($catName, $kategori);
                }
                if (! $kategori->slug) {
                    $kategori->update(['slug' => $catSlug]);
                }

                // 2. Subkategori (scoped slug jika bentrok antar kategori)
                $subkategoriId = null;
                $subName = trim((string) ($data['subkategori'] ?? ''));
                if ($subName !== '') {
                    $subName = mb_substr($subName, 0, 100);
                    $baseSubSlug = Str::slug($subName);
                    $subkat = $subkategoriBySlug->get($baseSubSlug);

                    if ($subkat && $subkat->id_kategori !== $kategori->id_kategori) {
                        $scopedSlug = Str::limit($catSlug.'-'.$baseSubSlug, 120, '');
                        $subkat = $subkategoriBySlug->get($scopedSlug);
                        $slugToUse = $scopedSlug;
                    } else {
                        $slugToUse = Str::limit($baseSubSlug, 120, '');
                    }

                    if (! $subkat) {
                        $subkat = SubkategoriObat::create([
                            'id_subkategori' => (string) Str::uuid(),
                            'id_kategori' => $kategori->id_kategori,
                            'nama_subkategori' => $subName,
                            'slug' => $slugToUse,
                            'urutan' => 0,
                            'dibuat_pada' => now(),
                        ]);
                        $subkategoriBySlug->put($slugToUse, $subkat);
                    }
                    $subkategoriId = $subkat->id_subkategori;
                }

                // 3. Obat / Produk
                $slug = mb_substr(trim((string) ($data['slug'] ?? Str::slug($data['nama_produk']))), 0, 190);
                $obat = $existingObats->get($slug);

                $attributes = [
                    'nama_obat' => mb_substr((string) $data['nama_produk'], 0, 150),
                    'merek' => mb_substr((string) ($data['manufaktur'] ?? 'Farmasi Indonesia'), 0, 100),
                    'jenis_sediaan' => mb_substr((string) ($data['jenis_sediaan'] ?? 'Tablet'), 0, 50),
                    'satuan_kemasan' => mb_substr((string) ($data['satuan_kemasan'] ?? '1 Pcs'), 0, 100),
                    'jumlah_obat' => 100,
                    'id_kategori' => $kategori->id_kategori,
                    'id_subkategori' => $subkategoriId,
                    'harga' => (float) ($data['harga'] ?? 15000),
                    'badge_status' => mb_substr((string) ($data['badge_status'] ?? 'Obat Bebas'), 0, 50),
                    'gambar_url' => mb_substr((string) ($data['gambar_url'] ?? ''), 0, 500),
                    'nomor_ijin_edar' => isset($data['nomor_ijin_edar_bpom']) ? mb_substr((string) $data['nomor_ijin_edar_bpom'], 0, 100) : null,
                    'indikasi' => $data['indikasi'] ?? null,
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'dosis' => $data['dosis'] ?? null,
                    'komposisi' => $data['komposisi'] ?? null,
                    'aturan_pakai' => $data['cara_pakai'] ?? null,
                    'expired' => now()->addMonths(24)->toDateString(),
                    'batch_number' => '#GOA-'.strtoupper(substr(md5($slug), 0, 6)),
                ];

                if (! $obat) {
                    $obat = Obat::create(array_merge([
                        'id_obat' => (string) Str::uuid(),
                        'slug' => $slug,
                    ], $attributes));
                    $existingObats->put($slug, $obat);
                } else {
                    $obat->update($attributes);
                }

                // 4. Stok Cabang (tambahkan stok jika belum ada agar produk tampil & mempertahankan variasi multi-cabang demo)
                foreach ($clients as $client) {
                    $stokQty = ($client->id_client === $bwiClient?->id_client) ? 35 : (($client->id_client === $rogoClient?->id_client) ? 25 : 20);
                    StokClient::withoutGlobalScopes()->firstOrCreate(
                        [
                            'id_obat' => $obat->id_obat,
                            'id_client' => $client->id_client,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'jumlah_stok' => $stokQty,
                            'diperbarui_pada' => now(),
                        ]
                    );
                }
            }
        });

        $this->command?->info('Berhasil mengimpor '.count($items).' produk ke database!');
    }
}
