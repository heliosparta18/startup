<?php

use App\Http\Requests\Tenant\VerifikasiResepRequest;
use App\Models\Client;
use App\Models\ItemPesanan;
use App\Models\LogAudit;
use App\Models\Obat;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Catalog\Services\StockFefoService;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use App\Modules\Reporting\Services\SalesReportExportService;
use App\Modules\Reporting\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Konteks OPERATOR TENANT / APOTEKER CABANG (internal). Prefix: cabang/{client}, name: tenant.*
 * Melayani verifikasi resep (#ORD-20260917-0042) dan inspeksi stok batch FEFO (Mockup Layar 2).
 */
Route::get('/dashboard', function (string $client, StockFefoService $fefoService) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $resepPending = Resep::query()
        ->whereHas('pesanan', function ($q) use ($clientId) {
            $q->withoutGlobalScopes()->where('id_client', $clientId);
        })
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat'])
        ->where('status_verifikasi', 'menunggu')
        ->latest('dibuat_pada')
        ->first();

    $resepPendingCount = Resep::query()
        ->whereHas('pesanan', function ($q) use ($clientId) {
            $q->withoutGlobalScopes()->where('id_client', $clientId);
        })
        ->where('status_verifikasi', 'menunggu')
        ->count();

    $fefoBatches = $fefoService->getBatchesByFefo($client, null, false, 6);
    $fefoSummary = $fefoService->getExpirySummary($clientId);

    $antreanBerjalan = Cache::get("antrean_berjalan:{$clientId}", 'A-001');

    $pesanansAktif = Pesanan::withoutGlobalScopes()
        ->where('id_client', $clientId)
        ->with(['items', 'pengguna', 'resep'])
        ->whereIn('status', ['diproses', 'menunggu_pembayaran', 'siap_diambil', 'menunggu_verifikasi'])
        ->orderBy('dibuat_pada', 'desc')
        ->take(10)
        ->get();

    $orderSelesaiHariIni = Pesanan::withoutGlobalScopes()
        ->where('id_client', $clientId)
        ->where('status', 'selesai')
        ->whereDate('dibuat_pada', now()->toDateString())
        ->count();

    return view('tenant.dashboard', [
        'client' => $client,
        'clientModel' => $clientModel,
        'resepPending' => $resepPending,
        'resepPendingCount' => $resepPendingCount,
        'fefoBatches' => $fefoBatches,
        'fefoSummary' => $fefoSummary,
        'antreanBerjalan' => $antreanBerjalan,
        'pesanansAktif' => $pesanansAktif,
        'orderSelesaiHariIni' => $orderSelesaiHariIni,
    ]);
})->name('dashboard');

Route::get('/antrean', function (string $client) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $antreanBerjalan = Cache::get("antrean_berjalan:{$clientId}", 'A-001');

    $pesanans = Pesanan::withoutGlobalScopes()
        ->where('id_client', $clientId)
        ->with(['items', 'pengguna', 'resep'])
        ->orderBy('dibuat_pada', 'desc')
        ->take(50)
        ->get();

    return view('tenant.antrean', [
        'client' => $client,
        'clientModel' => $clientModel,
        'antreanBerjalan' => $antreanBerjalan,
        'pesanans' => $pesanans,
    ]);
})->name('antrean.index');

Route::post('/pesanan/{id}/progres-status', function (string $client, string $id, Request $request) {
    $pesanan = Pesanan::withoutGlobalScopes()->where('id_pesanan', $id)->firstOrFail();

    $newStatus = (string) $request->input('status');
    $validStatuses = ['menunggu_pembayaran', 'diproses', 'siap_diambil', 'dikirim', 'selesai', 'dibatalkan'];

    if (! in_array($newStatus, $validStatuses, true)) {
        return back()->with('error', 'Status pesanan tidak sah.');
    }

    $pesanan->update(['status' => $newStatus]);

    // Pancarkan siaran pembaruan status ke Reverb WebSockets
    event(new OrderStatusUpdated($pesanan));

    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => "Status pesanan {$pesanan->nomor_pesanan} berhasil diperbarui menjadi ".str_replace('_', ' ', $newStatus).'.',
            'status' => $newStatus,
            'nomor_pesanan' => $pesanan->nomor_pesanan,
        ]);
    }

    return back()->with('success', "Status pesanan {$pesanan->nomor_pesanan} berhasil diperbarui menjadi ".str_replace('_', ' ', $newStatus).'.');
})->name('pesanan.progres-status');

Route::post('/antrean/update', function (string $client, Request $request) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $action = $request->input('action');
    $current = (string) Cache::get("antrean_berjalan:{$clientId}", 'A-001');

    if ($action === 'panggil_berikutnya') {
        $num = (int) preg_replace('/\D/', '', $current);
        $next = 'A-'.str_pad((string) ($num + 1), 3, '0', STR_PAD_LEFT);
        Cache::put("antrean_berjalan:{$clientId}", $next, now()->addDays(7));
        $msg = "Antrean berikutnya berhasil dipanggil: {$next}.";
    } elseif ($action === 'set_nomor') {
        $raw = trim((string) $request->input('nomor_antrean', 'A-001'));
        $digits = preg_replace('/\D/', '', $raw) ?: '1';
        $newNumber = 'A-'.str_pad($digits, 3, '0', STR_PAD_LEFT);
        Cache::put("antrean_berjalan:{$clientId}", $newNumber, now()->addDays(7));
        $msg = "Antrean berjalan berhasil diset ke {$newNumber}.";
    } else {
        $msg = 'Pengaturan antrean diperbarui.';
    }

    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => $msg,
            'antrean_berjalan' => Cache::get("antrean_berjalan:{$clientId}"),
        ]);
    }

    return back()->with('success', $msg);
})->name('antrean.update');

Route::post('/antrean/tambah-walkin', function (string $client, Request $request, CreateOrderAction $action) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $namaPasien = (string) $request->input('nama_pasien', 'Pasien Walk-In Apotek');
    $catatan = (string) $request->input('catatan', 'Tiket antrean walk-in langsung di apotek');

    // Buat record pengguna tamu jika belum ada
    $guestUser = Pengguna::create([
        'id_pengguna' => (string) Str::uuid(),
        'nama_lengkap' => $namaPasien,
        'peran' => 'pelanggan',
        'telepon_email' => 'walkin-'.Str::random(6).'@omnipharma.test',
        'password' => bcrypt(Str::random(16)),
        'aktif' => true,
    ]);

    $obat = Obat::first();
    $items = [];
    if ($obat) {
        $items[] = [
            'id_obat' => $obat->id_obat,
            'nama_obat' => $obat->nama_obat,
            'harga' => (float) ($obat->harga ?? 25000),
            'jumlah' => 1,
        ];
    }

    $todayCount = Pesanan::withoutTenancy()
        ->where('id_client', $clientId)
        ->whereDate('dibuat_pada', now()->toDateString())
        ->count();
    $nomorAntrean = 'A-'.str_pad((string) ($todayCount + 1), 3, '0', STR_PAD_LEFT);

    $pesanan = $action->execute(
        $clientId,
        $guestUser->id_pengguna,
        $items,
        'click_collect',
        null,
        'Walk-In Langsung di Apotek',
        $nomorAntrean,
        $catatan
    );

    $pesanan->update(['status' => 'diproses']);

    return back()->with('success', "Tiket antrean baru {$nomorAntrean} untuk {$namaPasien} berhasil ditambahkan dan masuk status diproses.");
})->name('antrean.walkin');

Route::get('/resep', function (string $client) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $resepPending = Resep::query()
        ->whereHas('pesanan', function ($q) use ($clientId) {
            $q->withoutGlobalScopes()->where('id_client', $clientId);
        })
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat'])
        ->where('status_verifikasi', 'menunggu')
        ->orderBy('dibuat_pada', 'desc')
        ->get();

    return view('tenant.verifikasi-resep', [
        'client' => $client,
        'clientModel' => $clientModel,
        'resepPending' => $resepPending,
    ]);
})->name('resep.antrean');

Route::get('/stok-fefo', function (string $client, Request $request, StockFefoService $fefoService) {
    $filter = (string) $request->query('filter', 'all');
    $search = trim((string) $request->query('q', ''));
    $includeEmpty = in_array($filter, ['empty', 'all'], true);

    $fefoBatches = $fefoService->getBatchesByFefo($client, null, $includeEmpty);

    if ($search !== '') {
        $needle = mb_strtolower($search);
        $fefoBatches = $fefoBatches->filter(function (StokClient $batch) use ($needle): bool {
            $nama = mb_strtolower((string) ($batch->obat->nama_obat ?? ''));
            $batchNo = mb_strtolower((string) ($batch->obat->batch_number ?? ''));

            return str_contains($nama, $needle) || str_contains($batchNo, $needle);
        })->values();
    }

    if ($filter === 'critical') {
        $fefoBatches = $fefoBatches->filter(fn (StokClient $b) => $b->is_critical_ed)->values();
    } elseif ($filter === 'near_ed') {
        $fefoBatches = $fefoBatches->filter(fn (StokClient $b) => $b->is_near_ed)->values();
    } elseif ($filter === 'low') {
        $fefoBatches = $fefoBatches->filter(fn (StokClient $b) => $b->jumlah_stok > 0 && $b->jumlah_stok <= 10)->values();
    } elseif ($filter === 'empty') {
        $fefoBatches = $fefoBatches->filter(fn (StokClient $b) => $b->jumlah_stok <= 0)->values();
    }

    $allObats = Obat::orderBy('nama_obat', 'asc')->get();

    return view('tenant.stok-fefo', [
        'client' => $client,
        'fefoBatches' => $fefoBatches,
        'allObats' => $allObats,
        'filter' => $filter,
        'search' => $search,
    ]);
})->name('stok.fefo');

Route::post('/stok/tambah', function (string $client, Request $request) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->firstOrFail();

    $validated = $request->validate([
        'id_obat' => ['required', 'uuid', 'exists:obat,id_obat'],
        'jumlah_stok' => ['required', 'integer', 'min:0', 'max:10000'],
        'batch_number' => ['nullable', 'string', 'max:50'],
        'expired' => ['nullable', 'date'],
    ]);

    $obat = Obat::where('id_obat', $validated['id_obat'])->firstOrFail();

    StokClient::updateOrCreate(
        [
            'id_client' => $clientModel->id_client,
            'id_obat' => $obat->id_obat,
        ],
        [
            'id' => (string) Str::uuid(),
            'jumlah_stok' => (int) $validated['jumlah_stok'],
            'diperbarui_pada' => now(),
        ]
    );

    if (! empty($validated['batch_number']) || ! empty($validated['expired'])) {
        $obat->update(array_filter([
            'batch_number' => $validated['batch_number'] ?? null,
            'expired' => $validated['expired'] ?? null,
        ]));
    }

    app(CatalogService::class)->clearMetadataCache();

    return back()->with('success', "Stok {$obat->nama_obat} pada cabang {$clientModel->nama_client} diperbarui menjadi {$validated['jumlah_stok']} unit.");
})->name('stok.store');

Route::get('/bantuan', function (string $client) {
    return view('tenant.bantuan', [
        'client' => $client,
    ]);
})->name('bantuan.index');

Route::get('/pasien', function (string $client) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->firstOrFail();

    $branchOrders = Pesanan::withoutGlobalScopes()
        ->where('id_client', $clientModel->id_client)
        ->with(['items', 'resep'])
        ->orderBy('dibuat_pada', 'desc')
        ->get();

    $patientOrdersMap = $branchOrders->groupBy('id_pengguna');
    $patientIds = $patientOrdersMap->keys()->filter()->values();

    $pasiens = $patientIds->isNotEmpty()
        ? Pengguna::whereIn('id_pengguna', $patientIds)->orderBy('nama_lengkap')->get()
        : collect();

    $pasiens->each(function (Pengguna $pasien) use ($patientOrdersMap): void {
        $orders = $patientOrdersMap->get($pasien->id_pengguna, collect());
        $pasien->setAttribute('pesanan_count', $orders->count());
        $pasien->setAttribute('pesanan_terbaru', $orders->take(5));
        $pasien->setAttribute('resep_count', $orders->filter(fn (Pesanan $o) => $o->resep !== null)->count());
    });

    return view('tenant.pasien', [
        'client' => $client,
        'clientModel' => $clientModel,
        'pasiens' => $pasiens,
    ]);
})->name('pasien.index');

Route::post('/resep/{id}/verifikasi', function (string $client, string $id, VerifikasiResepRequest $request) {
    $action = $request->input('action', 'disetujui');
    $catatan = $request->input('catatan') ?? $request->input('alasan') ?? 'Resep asli dan masih berlaku.';
    $nomorSip = $request->input('nomor_sip') ?? '503/SIP/2024';

    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $resep = Resep::query()->find($id)
        ?? Resep::where('id_pesanan', $id)->first()
        ?? Resep::whereHas('pesanan', function ($q) use ($id) {
            $q->withoutGlobalScopes()->where('nomor_pesanan', $id)->orWhere('nomor_pesanan', '#'.$id);
        })->first();

    if ($resep) {
        $belongsToBranch = Pesanan::withoutGlobalScopes()
            ->where('id_pesanan', $resep->id_pesanan)
            ->where('id_client', $clientId)
            ->exists();

        if (! $belongsToBranch) {
            abort(404, "Resep {$id} tidak ditemukan pada cabang ini.");
        }

        $updatedPesanan = DB::transaction(function () use ($resep, $action, $catatan, $nomorSip, $clientId) {
            $verifikatorId = null;
            if (auth()->check()) {
                $user = auth()->user();
                if (isset($user->id_pengguna) && ! empty($user->id_pengguna)) {
                    $verifikatorId = $user->id_pengguna;
                } elseif (isset($user->email)) {
                    $verifikatorId = Pengguna::where('telepon_email', $user->email)->value('id_pengguna');
                }
            }

            $resep->update([
                'status_verifikasi' => $action,
                'catatan_apoteker' => $catatan,
                'sip_dokter' => $nomorSip ?: $resep->sip_dokter,
                'sip_valid' => $action === 'disetujui',
                'dosis_valid' => $action === 'disetujui',
                'diverifikasi_oleh' => $verifikatorId,
            ]);

            LogAudit::create([
                'id_audit' => (string) Str::uuid(),
                'id_aktor' => $verifikatorId,
                'aksi' => 'TELAAH_RESEP',
                'nama_entitas' => 'resep',
                'id_entitas' => $resep->id_resep,
                'payload' => [
                    'keputusan' => $action,
                    'sip_dokter' => $resep->sip_dokter,
                    'catatan_apoteker' => $catatan,
                    'id_pesanan' => $resep->id_pesanan,
                ],
                'dibuat_pada' => now(),
            ]);

            $pesanan = Pesanan::withoutGlobalScopes()
                ->where('id_pesanan', $resep->id_pesanan)
                ->lockForUpdate()
                ->first();

            if (! $pesanan) {
                return null;
            }

            $previousStatus = $pesanan->status;
            $nextStatus = $action === 'disetujui' ? 'diproses' : 'dibatalkan';

            if ($nextStatus === 'dibatalkan' && $previousStatus !== 'dibatalkan') {
                $items = ItemPesanan::where('id_pesanan', $pesanan->id_pesanan)->get();
                $restoredAny = false;

                foreach ($items as $item) {
                    $stok = StokClient::withoutGlobalScopes()
                        ->where('id_client', $clientId)
                        ->where('id_obat', $item->id_obat)
                        ->lockForUpdate()
                        ->first();

                    if ($stok) {
                        $stok->increment('jumlah_stok', (int) $item->kuantitas);
                        $restoredAny = true;
                    }
                }

                if ($restoredAny) {
                    app(CatalogService::class)->clearMetadataCache();
                }
            }

            $pesanan->update(['status' => $nextStatus]);

            return $pesanan;
        });

        if ($updatedPesanan) {
            event(new OrderStatusUpdated($updatedPesanan));
        }
    }

    if (str_contains((string) $request->header('Accept'), 'text/html') && $request->has('_token')) {
        return back()->with('success', "Resep {$id} berhasil diverifikasi ({$action}).");
    }

    return response()->json([
        'success' => true,
        'message' => "Resep {$id} berhasil diverifikasi ({$action}).",
        'status_verifikasi' => $action,
    ]);
})->name('resep.verifikasi');

Route::post('/stok/{id}/penyesuaian', function (string $client, string $id, Request $request) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $stokRecord = StokClient::query()->with('obat')->find($id);

    $nextQty = (int) $request->input('jumlah_stok', 12);
    if ($request->filled('delta')) {
        $current = $stokRecord ? (int) $stokRecord->jumlah_stok : 0;
        $nextQty = max(0, $current + (int) $request->input('delta'));
    } elseif ($request->boolean('karantina')) {
        $nextQty = 0;
    } else {
        $nextQty = max(0, $nextQty);
    }

    if ($stokRecord) {
        $stokRecord->update([
            'jumlah_stok' => $nextQty,
            'diperbarui_pada' => now(),
        ]);

        if ($stokRecord->obat && ($request->filled('batch_number') || $request->filled('expired'))) {
            $stokRecord->obat->update(array_filter([
                'batch_number' => $request->filled('batch_number') ? trim(strip_tags((string) $request->input('batch_number'))) : null,
                'expired' => $request->filled('expired') ? (string) $request->input('expired') : null,
            ]));
        }

        app(CatalogService::class)->clearMetadataCache();
    }

    $branchName = $clientModel->nama_client ?? $client;
    $itemName = $stokRecord->obat->nama_obat ?? 'Obat';

    if ($request->expectsJson() || (! $request->has('_token') && ! str_contains((string) $request->header('Accept'), 'text/html'))) {
        return response()->json([
            'success' => true,
            'message' => "Stok {$itemName} pada cabang {$branchName} berhasil disesuaikan.",
            'jumlah_stok' => $nextQty,
        ]);
    }

    return back()->with('success', "Stok {$itemName} di {$branchName} disesuaikan menjadi {$nextQty} unit.");
})->name('stok.penyesuaian');

Route::get('/pesanan/{id}', function (string $client, string $id) {
    $pesanan = Pesanan::withoutGlobalScopes()->find($id);

    if (! $pesanan) {
        abort(404, 'Pesanan tidak ditemukan.');
    }

    Gate::authorize('view', $pesanan);

    return response()->json([
        'success' => true,
        'pesanan' => $pesanan,
    ]);
})->name('pesanan.detail');

Route::get('/laporan', function (string $client, Request $request, SalesReportService $reportService, SalesReportExportService $exportService) {
    // Menemukan UUID client berdasarkan slug cabang atau id_client
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $mulai = $request->query('periode_mulai', now()->startOfMonth()->toDateString());
    $selesai = $request->query('periode_selesai', now()->toDateString());

    // Stream download CSV jika ada query export=csv
    if ($request->query('export') === 'csv') {
        return $exportService->exportCsv((string) $clientId, $mulai, $selesai);
    }

    $summary = $reportService->getSummary((string) $clientId, $mulai, $selesai);
    $terkunci = RingkasanPeriodePenjualan::where('id_client', $clientId)
        ->where('periode_mulai', $mulai)
        ->where('periode_selesai', $selesai)
        ->where('status_terkunci', true)
        ->exists();

    return view('admin.laporan-penjualan', [
        'client' => $client,
        'summary' => $summary,
        'periodeMulai' => $mulai,
        'periodeSelesai' => $selesai,
        'ringkasanTerkunci' => $terkunci,
    ]);
})->name('laporan.index');

Route::post('/laporan/tutup-periode', function (string $client, Request $request, CloseSalesPeriodAction $action) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $mulai = $request->input('periode_mulai');
    $selesai = $request->input('periode_selesai');

    try {
        $action->execute((string) $clientId, $mulai, $selesai);

        return back()->with('success', "Periode kas {$mulai} s/d {$selesai} berhasil ditutup dan berstatus terkunci permanen.");
    } catch (Exception $e) {
        return back()->with('error', $e->getMessage());
    }
})->name('laporan.tutup-periode');
