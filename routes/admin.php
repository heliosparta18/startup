<?php

use App\Models\Client;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\PeranClientPengguna;
use App\Models\Pesanan;
use App\Models\PetaPengawasStaf;
use App\Models\Resep;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use App\Modules\Admin\Http\Controllers\StaffController;
use App\Modules\Catalog\Services\StockFefoService;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use App\Modules\Reporting\Services\SalesReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Konteks SUPER ADMIN PUSAT (internal). Prefix: admin, name: admin.*
 * Melayani Executive KPI Dashboard, Tutup Periode & Kunci Snapshot, Ekspor Laporan, CRUD Cabang, Staf, & Obat.
 */
Route::get('/dashboard', function (Request $request) {
    $clients = Client::query()->with('stok.obat')->orderBy('nama_client')->get();
    $summaries = RingkasanPeriodePenjualan::query()->orderBy('periode_mulai', 'desc')->get();

    $nonCancelledOrders = Pesanan::withoutTenancy()
        ->with('items')
        ->where('status', '!=', 'dibatalkan')
        ->get();

    $totalOmzet = (float) Pembayaran::where('status', 'lunas')->sum('jumlah');
    if ($totalOmzet <= 0) {
        $totalOmzet = (float) $nonCancelledOrders->sum(fn (Pesanan $order) => (float) $order->items->sum('subtotal'));
    }

    $totalPesanan = $nonCancelledOrders->count();
    $totalObatTerjual = (int) $nonCancelledOrders->sum(fn (Pesanan $order) => (int) $order->items->sum('kuantitas'));

    $branchStats = $clients->map(function (Client $c) use ($nonCancelledOrders, $summaries): array {
        $branchOrders = $nonCancelledOrders->where('id_client', $c->id_client);
        $branchOmzet = (float) $branchOrders->sum(fn (Pesanan $order) => (float) $order->items->sum('subtotal'));
        $branchStaffCount = PeranClientPengguna::where('id_client', $c->id_client)->count();
        $branchLowStock = StokClient::withoutTenancy()
            ->where('id_client', $c->id_client)
            ->where('jumlah_stok', '<=', 10)
            ->count();
        $latestSummary = $summaries->firstWhere('id_client', $c->id_client);

        return [
            'client' => $c,
            'omzet' => $branchOmzet,
            'pesanan_count' => $branchOrders->count(),
            'staf_count' => $branchStaffCount,
            'stok_kritis_count' => $branchLowStock,
            'terkunci' => (bool) ($latestSummary->status_terkunci ?? false),
        ];
    });

    $stokKritis = StokClient::withoutTenancy()
        ->with(['obat', 'client'])
        ->where(function ($q): void {
            $q->where('jumlah_stok', '<=', 10)
                ->orWhereHas('obat', fn ($oq) => $oq->whereDate('expired', '<=', now()->addDays(30)->toDateString()));
        })
        ->orderBy('jumlah_stok', 'asc')
        ->take(12)
        ->get();

    $activePeriodeMulai = now()->startOfMonth()->toDateString();
    $activePeriodeSelesai = now()->toDateString();
    $isPeriodeLocked = RingkasanPeriodePenjualan::where('status_terkunci', true)
        ->where('periode_mulai', $activePeriodeMulai)
        ->where('periode_selesai', $activePeriodeSelesai)
        ->exists();

    return view('admin.dashboard', [
        'clients' => $clients,
        'summaries' => $summaries,
        'branchStats' => $branchStats,
        'totalOmzet' => $totalOmzet,
        'totalPesanan' => $totalPesanan,
        'totalObatTerjual' => $totalObatTerjual,
        'stokKritis' => $stokKritis,
        'activePeriodeMulai' => $activePeriodeMulai,
        'activePeriodeSelesai' => $activePeriodeSelesai,
        'isPeriodeLocked' => $isPeriodeLocked,
    ]);
})->name('dashboard');

Route::post('/tutup-periode', function (Request $request, CloseSalesPeriodAction $action) {
    $periodeMulai = (string) $request->input('periode_mulai', now()->startOfMonth()->toDateString());
    $periodeSelesai = (string) $request->input('periode_selesai', now()->toDateString());

    $targetClientId = $request->input('id_client');
    $targetClients = $targetClientId
        ? Client::where('id_client', $targetClientId)->get()
        : Client::where('aktif', true)->get();

    if ($targetClients->isEmpty()) {
        $fallback = Client::first();
        if ($fallback) {
            $targetClients = collect([$fallback]);
        }
    }

    $omzet = 0.0;
    $pesanan = 0;

    foreach ($targetClients as $branch) {
        try {
            $ringkasan = $action->execute((string) $branch->id_client, $periodeMulai, $periodeSelesai);
            $omzet += (float) $ringkasan->total_pendapatan;
            $pesanan += (int) $ringkasan->total_pesanan;
        } catch (Throwable) {
            $existing = RingkasanPeriodePenjualan::where('id_client', $branch->id_client)
                ->where('periode_mulai', $periodeMulai)
                ->where('periode_selesai', $periodeSelesai)
                ->first();
            if ($existing) {
                $omzet += (float) $existing->total_pendapatan;
                $pesanan += (int) $existing->total_pesanan;
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => "Periode penjualan {$periodeMulai} s/d {$periodeSelesai} berhasil ditutup dan snapshot transaksi terkunci.",
        'status_terkunci' => true,
        'periode' => "{$periodeMulai} s/d {$periodeSelesai}",
        'omzet_terkunci' => $omzet,
        'total_pesanan' => $pesanan,
    ], 200);
})->name('periode.tutup');

Route::get('/ekspor-laporan', function (Request $request, SalesReportExportService $exportService) {
    $client = Client::where('id_client', $request->input('id_client'))->first()
        ?? Client::where('slug', 'banyuwangi-kota')->first()
        ?? Client::first();
    $clientId = $client ? (string) $client->id_client : 'a1b2c3d4-0001-4000-8000-000000000001';
    $mulai = (string) $request->input('periode_mulai', now()->startOfMonth()->toDateString());
    $selesai = (string) $request->input('periode_selesai', now()->toDateString());
    $format = (string) $request->query('format', 'xlsx');

    if ($request->has('download') || $format === 'csv') {
        return $exportService->exportCsv($clientId, $mulai, $selesai);
    }

    return response()->json([
        'success' => true,
        'message' => 'Laporan penjualan dan rekap konsolidasi lintas cabang siap diunduh.',
        'format' => $format,
        'download_url' => route('admin.laporan.ekspor', [
            'download' => 1,
            'id_client' => $clientId,
            'periode_mulai' => $mulai,
            'periode_selesai' => $selesai,
            'format' => 'csv',
        ]),
        'filename' => "Rekap_Penjualan_OmniPharma_{$mulai}_{$selesai}.csv",
    ]);
})->name('laporan.ekspor');

/*
|--------------------------------------------------------------------------
| CRUD Staf & Pengawasan APJ Cabang
|--------------------------------------------------------------------------
*/
Route::get('/cabang/{client}/staf', [StaffController::class, 'index'])->name('cabang.staf');
Route::post('/cabang/{client}/staf/akun', [StaffController::class, 'createAccount'])->name('cabang.staf.akun');
Route::patch('/cabang/{client}/staf/{pengguna}/status', [StaffController::class, 'toggleStatus'])->name('cabang.staf.status');
Route::post('/cabang/{client}/staf/role', [StaffController::class, 'assignRole'])->name('cabang.staf.role');
Route::delete('/cabang/{client}/staf/role/{roleId}', [StaffController::class, 'removeRole'])->name('cabang.staf.role.destroy');
Route::post('/cabang/{client}/staf/pengawas', [StaffController::class, 'assignSupervisor'])->name('cabang.staf.pengawas');
Route::delete('/cabang/{client}/staf/pengawas/{supervisionId}', [StaffController::class, 'removeSupervisor'])->name('cabang.staf.pengawas.destroy');

/*
|--------------------------------------------------------------------------
| Ledger Pembayaran & Kasir Konsolidasi (Super Admin Read/Audit)
|--------------------------------------------------------------------------
*/
Route::get('/kasir-ledger', function (Request $request) {
    $clients = Client::orderBy('nama_client')->get();
    $selectedClient = (string) $request->query('client', 'all');
    $statusFilter = (string) $request->query('status', 'semua');
    $metodeFilter = (string) $request->query('metode', 'semua');

    $clientFilterId = null;
    if ($selectedClient !== '' && $selectedClient !== 'all') {
        $clientFilterId = Client::where('id_client', $selectedClient)->orWhere('slug', $selectedClient)->value('id_client');
    }

    $baseQuery = Pembayaran::query()
        ->when($clientFilterId, function ($q) use ($clientFilterId): void {
            $q->whereHas('pesanan', fn ($pq) => $pq->withoutGlobalScopes()->where('id_client', $clientFilterId));
        });

    $pembayarans = (clone $baseQuery)
        ->with(['pesanan.client', 'pesanan.pengguna', 'verifikator'])
        ->when($statusFilter !== 'semua', fn ($q) => $q->where('status', $statusFilter))
        ->when($metodeFilter !== 'semua', fn ($q) => $q->where('metode_pembayaran', $metodeFilter))
        ->orderBy('dibuat_pada', 'desc')
        ->take(80)
        ->get();

    $settledQuery = (clone $baseQuery)->where('status', 'lunas');
    $totalHariIni = (float) (clone $settledQuery)->whereDate('dibuat_pada', now()->toDateString())->sum('jumlah');
    $totalSemuaLunas = (float) (clone $settledQuery)->sum('jumlah');
    $totalTransaksi = (clone $baseQuery)->count();
    $totalLunasCount = (clone $settledQuery)->count();
    $totalMenunggu = (float) (clone $baseQuery)->where('status', 'menunggu_verifikasi')->sum('jumlah');
    $totalQris = (float) (clone $settledQuery)->where('metode_pembayaran', 'qris')->sum('jumlah');

    return view('admin.kasir-ledger', [
        'clients' => $clients,
        'selectedClient' => $selectedClient,
        'statusFilter' => $statusFilter,
        'metodeFilter' => $metodeFilter,
        'pembayarans' => $pembayarans,
        'totalHariIni' => $totalHariIni,
        'totalSemuaLunas' => $totalSemuaLunas,
        'totalTransaksi' => $totalTransaksi,
        'totalLunasCount' => $totalLunasCount,
        'totalMenunggu' => $totalMenunggu,
        'totalQris' => $totalQris,
    ]);
})->name('kasir.ledger');

/*
|--------------------------------------------------------------------------
| CRUD Cabang Apotek
|--------------------------------------------------------------------------
*/
Route::get('/cabang/{client}/pengaturan', function (string $client) {
    $clients = Client::orderBy('nama_client')->get();

    return view('admin.pengaturan-cabang', [
        'client' => $client,
        'clients' => $clients,
    ]);
})->name('cabang.pengaturan');

Route::post('/cabang', function (Request $request) {
    $validated = $request->validate([
        'nama_client' => ['required', 'string', 'max:120'],
        'slug' => ['nullable', 'string', 'max:120', 'unique:client,slug'],
        'alamat' => ['required', 'string', 'max:255'],
        'radius_km' => ['nullable', 'integer', 'min:1', 'max:50'],
        'jam_buka' => ['nullable', 'string', 'max:60'],
        'aktif' => ['nullable', 'boolean'],
    ]);

    $slugBase = $validated['slug'] ?: Str::slug(str_replace('Cabang ', '', $validated['nama_client']));
    $slug = $slugBase;
    $suffix = 1;
    while (Client::where('slug', $slug)->exists()) {
        $slug = "{$slugBase}-{$suffix}";
        $suffix++;
    }

    $branch = Client::create([
        'id_client' => (string) Str::uuid(),
        'nama_client' => trim(strip_tags($validated['nama_client'])),
        'slug' => $slug,
        'alamat' => trim(strip_tags($validated['alamat'])),
        'radius_km' => (int) ($validated['radius_km'] ?? 5),
        'jam_buka' => trim(strip_tags((string) ($validated['jam_buka'] ?? '07:00 - 22:00 WIB'))),
        'aktif' => $request->boolean('aktif', true),
    ]);

    return back()->with('success', "Cabang baru {$branch->nama_client} berhasil ditambahkan.");
})->name('cabang.store');

Route::post('/cabang/{client}/pengaturan', function (string $client, Request $request) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->firstOrFail();

    $validated = $request->validate([
        'nama_client' => ['required', 'string', 'max:120'],
        'alamat' => ['required', 'string', 'max:255'],
        'radius_km' => ['nullable', 'integer', 'min:1', 'max:50'],
        'jam_buka' => ['nullable', 'string', 'max:60'],
        'aktif' => ['nullable', 'boolean'],
    ]);

    $clientModel->update([
        'nama_client' => trim(strip_tags($validated['nama_client'])),
        'alamat' => trim(strip_tags($validated['alamat'])),
        'radius_km' => (int) ($validated['radius_km'] ?? $clientModel->radius_km ?? 5),
        'jam_buka' => trim(strip_tags((string) ($validated['jam_buka'] ?? $clientModel->jam_buka ?? '07:00 - 22:00 WIB'))),
        'aktif' => $request->boolean('aktif', true),
    ]);

    return back()->with('success', "Konfigurasi cabang {$clientModel->nama_client} berhasil diperbarui.");
})->name('cabang.pengaturan.save');

Route::delete('/cabang/{client}', function (string $client) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->firstOrFail();

    if (Pesanan::withoutTenancy()->where('id_client', $clientModel->id_client)->exists()) {
        return back()->with('error', "Cabang {$clientModel->nama_client} memiliki riwayat transaksi. Nonaktifkan status operasional alih-alih menghapus permanen.");
    }

    $nama = $clientModel->nama_client;
    $clientModel->delete();

    return redirect()
        ->route('admin.cabang.pengaturan', ['client' => Client::first()->slug ?? 'banyuwangi-kota'])
        ->with('success', "Cabang {$nama} berhasil dihapus.");
})->name('cabang.destroy');

/*
|--------------------------------------------------------------------------
| CRUD Master Obat & Stok Multi-Cabang
|--------------------------------------------------------------------------
*/
Route::get('/obat', function (Request $request) {
    $search = trim((string) $request->query('q', ''));
    $kategoriFilter = (string) $request->query('kategori', 'semua');
    $golonganFilter = (string) $request->query('golongan', 'semua');

    $obats = Obat::query()
        ->with(['kategori', 'allStokClient.client'])
        ->when($search !== '', function ($q) use ($search): void {
            $q->where(function ($sub) use ($search): void {
                $sub->where('nama_obat', 'like', "%{$search}%")
                    ->orWhere('merek', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            });
        })
        ->when($kategoriFilter !== 'semua', fn ($q) => $q->where('id_kategori', $kategoriFilter))
        ->when($golonganFilter !== 'semua', fn ($q) => $q->where('badge_status', $golonganFilter))
        ->orderBy('nama_obat', 'asc')
        ->paginate(20)
        ->withQueryString();

    $kategoris = KategoriObat::orderBy('urutan')->orderBy('nama_kategori')->get();
    $clients = Client::orderBy('nama_client')->get();

    return view('admin.obat-crud', [
        'obats' => $obats,
        'kategoris' => $kategoris,
        'clients' => $clients,
        'search' => $search,
        'kategoriFilter' => $kategoriFilter,
        'golonganFilter' => $golonganFilter,
    ]);
})->name('obat.index');

Route::post('/obat', function (Request $request) {
    $validated = $request->validate([
        'nama_obat' => ['required', 'string', 'max:150'],
        'merek' => ['nullable', 'string', 'max:100'],
        'id_kategori' => ['required', 'uuid', 'exists:kategori_obat,id_kategori'],
        'jenis_sediaan' => ['required', 'string', 'max:50'],
        'satuan_kemasan' => ['nullable', 'string', 'max:100'],
        'badge_status' => ['required', 'string', 'in:Obat Bebas,Obat Bebas Terbatas,Obat Keras,Alat Kesehatan'],
        'harga' => ['required', 'numeric', 'min:100'],
        'expired' => ['required', 'date'],
        'batch_number' => ['nullable', 'string', 'max:50'],
        'nomor_ijin_edar' => ['nullable', 'string', 'max:100'],
        'indikasi' => ['nullable', 'string', 'max:500'],
        'stok' => ['nullable', 'array'],
        'stok.*' => ['nullable', 'integer', 'min:0'],
    ]);

    $slugBase = Str::slug($validated['nama_obat']);
    $slug = $slugBase;
    $idx = 1;
    while (Obat::where('slug', $slug)->exists()) {
        $slug = "{$slugBase}-{$idx}";
        $idx++;
    }

    $obat = DB::transaction(function () use ($validated, $slug): Obat {
        $stokInputs = is_array($validated['stok'] ?? null) ? $validated['stok'] : [];
        $totalStok = 0;
        foreach ($stokInputs as $qty) {
            $totalStok += max(0, (int) $qty);
        }

        $obat = Obat::create([
            'id_obat' => (string) Str::uuid(),
            'nama_obat' => trim(strip_tags($validated['nama_obat'])),
            'slug' => $slug,
            'merek' => trim(strip_tags((string) ($validated['merek'] ?? 'OmniPharma'))),
            'id_kategori' => $validated['id_kategori'],
            'jenis_sediaan' => trim(strip_tags($validated['jenis_sediaan'])),
            'satuan_kemasan' => trim(strip_tags((string) ($validated['satuan_kemasan'] ?? 'Strip'))),
            'badge_status' => $validated['badge_status'],
            'harga' => (float) $validated['harga'],
            'expired' => $validated['expired'],
            'batch_number' => trim(strip_tags((string) ($validated['batch_number'] ?? 'BATCH-'.now()->format('Ymd')))),
            'nomor_ijin_edar' => trim(strip_tags((string) ($validated['nomor_ijin_edar'] ?? 'BPOM RI'))),
            'indikasi' => trim(strip_tags((string) ($validated['indikasi'] ?? 'Sediaan farmasi resmi apotek.'))),
            'jumlah_obat' => $totalStok,
        ]);

        foreach ($stokInputs as $clientId => $qty) {
            if (Client::where('id_client', $clientId)->exists()) {
                StokClient::withoutTenancy()->updateOrCreate(
                    ['id_obat' => $obat->id_obat, 'id_client' => $clientId],
                    ['id' => (string) Str::uuid(), 'jumlah_stok' => max(0, (int) $qty), 'diperbarui_pada' => now()]
                );
            }
        }

        return $obat;
    });

    return back()->with('success', "Obat {$obat->nama_obat} dan stok cabang berhasil ditambahkan.");
})->name('obat.store');

Route::put('/obat/{id}', function (string $id, Request $request) {
    $obat = Obat::findOrFail($id);

    $validated = $request->validate([
        'nama_obat' => ['required', 'string', 'max:150'],
        'merek' => ['nullable', 'string', 'max:100'],
        'id_kategori' => ['required', 'uuid', 'exists:kategori_obat,id_kategori'],
        'jenis_sediaan' => ['required', 'string', 'max:50'],
        'satuan_kemasan' => ['nullable', 'string', 'max:100'],
        'badge_status' => ['required', 'string', 'in:Obat Bebas,Obat Bebas Terbatas,Obat Keras,Alat Kesehatan'],
        'harga' => ['required', 'numeric', 'min:100'],
        'expired' => ['required', 'date'],
        'batch_number' => ['nullable', 'string', 'max:50'],
        'stok' => ['nullable', 'array'],
        'stok.*' => ['nullable', 'integer', 'min:0'],
    ]);

    DB::transaction(function () use ($obat, $validated): void {
        $stokInputs = $validated['stok'] ?? [];
        foreach ($stokInputs as $clientId => $qty) {
            if (Client::where('id_client', $clientId)->exists()) {
                StokClient::withoutTenancy()->updateOrCreate(
                    ['id_obat' => $obat->id_obat, 'id_client' => $clientId],
                    ['id' => (string) Str::uuid(), 'jumlah_stok' => max(0, (int) $qty), 'diperbarui_pada' => now()]
                );
            }
        }

        $totalStok = (int) StokClient::withoutTenancy()->where('id_obat', $obat->id_obat)->sum('jumlah_stok');

        $obat->update([
            'nama_obat' => trim(strip_tags($validated['nama_obat'])),
            'merek' => trim(strip_tags((string) ($validated['merek'] ?? $obat->merek))),
            'id_kategori' => $validated['id_kategori'],
            'jenis_sediaan' => trim(strip_tags($validated['jenis_sediaan'])),
            'satuan_kemasan' => trim(strip_tags((string) ($validated['satuan_kemasan'] ?? $obat->satuan_kemasan))),
            'badge_status' => $validated['badge_status'],
            'harga' => (float) $validated['harga'],
            'expired' => $validated['expired'],
            'batch_number' => trim(strip_tags((string) ($validated['batch_number'] ?? $obat->batch_number))),
            'jumlah_obat' => $totalStok,
        ]);
    });

    return back()->with('success', "Data master & stok {$obat->nama_obat} berhasil diperbarui.");
})->name('obat.update');

Route::delete('/obat/{id}', function (string $id) {
    $obat = Obat::findOrFail($id);

    if (DB::table('item_pesanan')->where('id_obat', $obat->id_obat)->exists()) {
        StokClient::withoutTenancy()->where('id_obat', $obat->id_obat)->update(['jumlah_stok' => 0]);

        return back()->with('success', "Stok {$obat->nama_obat} dikosongkan di semua cabang karena masih tercatat pada riwayat pesanan.");
    }

    $nama = $obat->nama_obat;
    $obat->delete();

    return back()->with('success', "Master obat {$nama} berhasil dihapus.");
})->name('obat.destroy');

/*
|--------------------------------------------------------------------------
| Monitoring Progres Pesanan Lintas Cabang
|--------------------------------------------------------------------------
*/
Route::get('/monitoring-progres', function (Request $request) {
    $clients = Client::orderBy('nama_client')->get();
    $selectedClient = $request->query('client');

    $query = Pesanan::withoutGlobalScopes()
        ->with(['items.obat', 'client', 'pengguna'])
        ->orderBy('dibuat_pada', 'desc');

    if ($selectedClient && $selectedClient !== 'all') {
        $clientModel = Client::where('id_client', $selectedClient)->orWhere('slug', $selectedClient)->first();
        if ($clientModel) {
            $query->where('id_client', $clientModel->id_client);
        }
    }

    $pesanans = $query->take(60)->get();

    $antreanMap = [];
    foreach ($clients as $c) {
        $antreanMap[$c->id_client] = Cache::get("antrean_berjalan:{$c->id_client}", 'A-001');
    }

    $totalDiproses = $pesanans->where('status', 'diproses')->count();
    $totalSiap = $pesanans->where('status', 'siap_diambil')->count();
    $totalObatDiproses = $pesanans->where('status', 'diproses')->sum(fn ($p) => $p->items->sum('kuantitas'));

    return view('admin.monitoring-progres', [
        'clients' => $clients,
        'selectedClient' => $selectedClient,
        'pesanans' => $pesanans,
        'antreanMap' => $antreanMap,
        'totalDiproses' => $totalDiproses,
        'totalSiap' => $totalSiap,
        'totalObatDiproses' => $totalObatDiproses,
    ]);
})->name('progres.index');

/*
|--------------------------------------------------------------------------
| Cek & Audit Operasional Apoteker Cabang (Super Admin Supervision)
|--------------------------------------------------------------------------
*/
Route::get('/apoteker/{client?}', function (StockFefoService $fefoService, ?string $client = null) {
    $clients = Client::orderBy('nama_client')->get();
    $requestedSlug = $client ?: 'banyuwangi-kota';

    $activeBranch = $clients->first(fn (Client $c) => $c->slug === $requestedSlug || $c->id_client === $requestedSlug)
        ?? $clients->first();

    if (! $activeBranch) {
        abort(404, 'Cabang apotek belum tersedia.');
    }

    $branchSlug = $activeBranch->slug ?? $activeBranch->id_client;

    $staffs = PeranClientPengguna::with('pengguna')
        ->where('id_client', $activeBranch->id_client)
        ->orderBy('peran')
        ->get();

    $supervisions = PetaPengawasStaf::with(['pengawas', 'staf'])
        ->where('id_client', $activeBranch->id_client)
        ->get();

    $resepBaseQuery = Resep::query()->whereHas('pesanan', function ($q) use ($activeBranch): void {
        $q->withoutGlobalScopes()->where('id_client', $activeBranch->id_client);
    });

    $reseps = (clone $resepBaseQuery)
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat', 'verifikator'])
        ->latest('dibuat_pada')
        ->take(30)
        ->get();

    $resepCounts = (clone $resepBaseQuery)
        ->selectRaw('status_verifikasi, COUNT(*) as total')
        ->groupBy('status_verifikasi')
        ->pluck('total', 'status_verifikasi');

    $resepMenungguCount = (int) ($resepCounts['menunggu'] ?? 0);
    $resepDisetujuiCount = (int) ($resepCounts['disetujui'] ?? 0);
    $resepDitolakCount = (int) ($resepCounts['ditolak'] ?? 0);

    $fefoBatches = $fefoService->getBatchesByFefo($branchSlug, null, false, 15)
        ->map(function ($batch): array {
            $expired = $batch->obat->expired ?? $batch->tanggal_kadaluarsa;
            $urgency = ($batch->is_critical_ed ?? false)
                ? 'critical'
                : (($batch->is_near_ed ?? false) ? 'near' : 'normal');

            return [
                'nama_obat' => $batch->obat->nama_obat ?? 'Sediaan Obat',
                'batch_number' => $batch->obat->batch_number ?? ('BTC-'.substr((string) $batch->id, 0, 6)),
                'stok' => (int) $batch->jumlah_stok,
                'expired' => $expired ? Carbon::parse($expired)->format('d M Y') : '-',
                'urgency' => $urgency,
            ];
        });
    $fefoSummary = $fefoService->getExpirySummary($activeBranch->id_client);
    $antreanBerjalan = Cache::get("antrean_berjalan:{$activeBranch->id_client}", 'A-001');

    $branchOrders = Pesanan::withoutGlobalScopes()
        ->with(['pengguna', 'items'])
        ->where('id_client', $activeBranch->id_client)
        ->latest('dibuat_pada')
        ->take(20)
        ->get();

    return view('admin.cek-apoteker', [
        'clients' => $clients,
        'activeBranch' => $activeBranch,
        'branchSlug' => $branchSlug,
        'staffs' => $staffs,
        'supervisions' => $supervisions,
        'reseps' => $reseps,
        'resepMenungguCount' => $resepMenungguCount,
        'resepDisetujuiCount' => $resepDisetujuiCount,
        'resepDitolakCount' => $resepDitolakCount,
        'fefoBatches' => $fefoBatches,
        'fefoSummary' => $fefoSummary,
        'antreanBerjalan' => $antreanBerjalan,
        'branchOrders' => $branchOrders,
    ]);
})->name('apoteker.index');
