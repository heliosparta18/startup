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

class GoApotikCatalogSeeder extends Seeder
{
    /**
     * Seed 42 curated products from GoApotik into database idempotently.
     */
    public function run(): void
    {
        $filename = config('app.goapotik_catalog_file', 'goapotik_catalog_42.json');
        $jsonPath = database_path("data/{$filename}");
        if (! File::exists($jsonPath)) {
            $jsonPath = database_path('data/goapotik_catalog_42.json');
        }
        if (! File::exists($jsonPath)) {
            return;
        }

        $items = json_decode(File::get($jsonPath), true);
        if (! is_array($items)) {
            return;
        }

        $clients = Client::all();
        $bwiClient = $clients->firstWhere('slug', 'banyuwangi-kota') ?? $clients->first();
        $rogoClient = $clients->firstWhere('slug', 'rogojampi') ?? $clients->skip(1)->first();

        DB::transaction(function () use ($items, $clients, $bwiClient, $rogoClient) {
            foreach ($items as $index => $data) {
                // 1. Kategori Utama
                $kategori = KategoriObat::firstOrCreate(
                    ['nama_kategori' => $data['kategori']],
                    [
                        'id_kategori' => (string) Str::uuid(),
                        'slug' => Str::slug($data['kategori']),
                        'wajib_resep' => $data['kategori'] === 'Obat' && ($data['wajib_resep'] ?? false),
                        'deskripsi' => "Kategori produk kesehatan {$data['kategori']}",
                    ]
                );

                if (! $kategori->slug) {
                    $kategori->update(['slug' => Str::slug($data['kategori'])]);
                }

                // 2. Subkategori
                $subkategoriId = null;
                if (! empty($data['subkategori'])) {
                    $subkategoriSlug = Str::slug($data['subkategori']);
                    $subkat = SubkategoriObat::where('slug', $subkategoriSlug)->first();
                    if (! $subkat) {
                        $subkat = SubkategoriObat::create([
                            'id_subkategori' => (string) Str::uuid(),
                            'id_kategori' => $kategori->id_kategori,
                            'nama_subkategori' => $data['subkategori'],
                            'slug' => $subkategoriSlug,
                            'urutan' => 0,
                            'dibuat_pada' => now(),
                        ]);
                    }
                    $subkategoriId = $subkat->id_subkategori;
                }

                // 3. Obat / Produk
                $obat = Obat::where('slug', $data['slug'])->first();
                if (! $obat) {
                    $obat = Obat::create([
                        'id_obat' => (string) Str::uuid(),
                        'nama_obat' => $data['nama_produk'],
                        'slug' => $data['slug'],
                        'merek' => $data['manufaktur'] ?? 'Farmasi Indonesia',
                        'jenis_sediaan' => substr($data['jenis_sediaan'] ?? 'Tablet', 0, 50),
                        'satuan_kemasan' => substr($data['satuan_kemasan'] ?? '1 Pcs', 0, 100),
                        'jumlah_obat' => 100,
                        'id_kategori' => $kategori->id_kategori,
                        'id_subkategori' => $subkategoriId,
                        'harga' => $data['harga'],
                        'badge_status' => $data['badge_status'],
                        'gambar_url' => $data['gambar_url'],
                        'nomor_ijin_edar' => $data['nomor_ijin_edar_bpom'] ?? null,
                        'indikasi' => $data['indikasi'] ?? null,
                        'deskripsi' => $data['deskripsi'] ?? null,
                        'dosis' => $data['dosis'] ?? null,
                        'komposisi' => $data['komposisi'] ?? null,
                        'aturan_pakai' => $data['cara_pakai'] ?? null,
                        'expired' => now()->addMonths(24)->toDateString(),
                        'batch_number' => '#GOA-'.strtoupper(substr(md5($data['slug']), 0, 6)),
                    ]);
                } else {
                    $obat->update([
                        'nama_obat' => $data['nama_produk'],
                        'merek' => $data['manufaktur'] ?? 'Farmasi Indonesia',
                        'jenis_sediaan' => substr($data['jenis_sediaan'] ?? 'Tablet', 0, 50),
                        'satuan_kemasan' => substr($data['satuan_kemasan'] ?? '1 Pcs', 0, 100),
                        'jumlah_obat' => 100,
                        'id_kategori' => $kategori->id_kategori,
                        'id_subkategori' => $subkategoriId,
                        'harga' => $data['harga'],
                        'badge_status' => $data['badge_status'],
                        'gambar_url' => $data['gambar_url'],
                        'nomor_ijin_edar' => $data['nomor_ijin_edar_bpom'] ?? null,
                        'indikasi' => $data['indikasi'] ?? null,
                        'deskripsi' => $data['deskripsi'] ?? null,
                        'dosis' => $data['dosis'] ?? null,
                        'komposisi' => $data['komposisi'] ?? null,
                        'aturan_pakai' => $data['cara_pakai'] ?? null,
                    ]);
                }

                // 4. Multi-client stok untuk cabang aktif dengan alokasi variasi deterministik
                // Group 1 (index 0..13, 14 produk): Tersedia di kedua cabang (BWI > 0, Rogojampi > 0)
                // Group 2 (index 14..24, 11 produk): Hanya tersedia di Banyuwangi Kota (BWI > 0, Rogojampi = 0)
                // Group 3 (index 25..35, 11 produk): Hanya tersedia di Rogojampi (BWI = 0, Rogojampi > 0)
                // Group 4 (index 36..41, 6 produk): Kosong di kedua cabang (BWI = 0, Rogojampi = 0)
                if ($index <= 13) {
                    $stokBwi = 35;
                    $stokRogo = 25;
                } elseif ($index <= 24) {
                    $stokBwi = 30;
                    $stokRogo = 0;
                } elseif ($index <= 35) {
                    $stokBwi = 0;
                    $stokRogo = 30;
                } else {
                    $stokBwi = 0;
                    $stokRogo = 0;
                }

                if ($bwiClient) {
                    StokClient::updateOrCreate(
                        [
                            'id_obat' => $obat->id_obat,
                            'id_client' => $bwiClient->id_client,
                        ],
                        [
                            'jumlah_stok' => $stokBwi,
                            'diperbarui_pada' => now(),
                        ]
                    );
                }

                if ($rogoClient) {
                    StokClient::updateOrCreate(
                        [
                            'id_obat' => $obat->id_obat,
                            'id_client' => $rogoClient->id_client,
                        ],
                        [
                            'jumlah_stok' => $stokRogo,
                            'diperbarui_pada' => now(),
                        ]
                    );
                }

                foreach ($clients as $client) {
                    if ($client->id_client !== $bwiClient?->id_client && $client->id_client !== $rogoClient?->id_client) {
                        StokClient::updateOrCreate(
                            [
                                'id_obat' => $obat->id_obat,
                                'id_client' => $client->id_client,
                            ],
                            [
                                'jumlah_stok' => 0,
                                'diperbarui_pada' => now(),
                            ]
                        );
                    }
                }
            }
        });
    }
}
