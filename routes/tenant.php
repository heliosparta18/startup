<?php

use App\Http\Requests\Tenant\VerifikasiResepRequest;
use App\Models\Client;
use App\Models\Obat;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Models\RingkasanPeriodePenjualan;
use App\Models\StokClient;
use App\Modules\Catalog\Services\StockFefoService;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use App\Modules\Reporting\Services\SalesReportExportService;
use App\Modules\Reporting\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Konteks OPERATOR TENANT / APOTEKER CABANG (internal). Prefix: cabang/{client}, name: tenant.*
 * Melayani verifikasi resep (#ORD-20260917-0042) dan inspeksi stok batch FEFO (Mockup Layar 2).
 */
Route::get('/dashboard', function (string $client) {
    $clientModel = Client::where('id_client', $client)->orWhere('slug', $client)->first();
    $clientId = $clientModel ? $clientModel->id_client : $client;

    $resepPending = Resep::query()
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat'])
        ->where('status_verifikasi', 'menunggu')
        ->first();

    $fefoBatches = Obat::query()
        ->with('stokClient')
        ->orderBy('expired', 'asc')
        ->get();

    $antreanBerjalan = Cache::get("antrean_berjalan:{$clientId}", 'A-001');

    $pesanansAktif = Pesanan::withoutGlobalScopes()
        ->where('id_client', $clientId)
        ->with(['items', 'pengguna', 'resep'])
        ->whereIn('status', ['diproses', 'menunggu_pembayaran', 'siap_diambil'])
        ->orderBy('dibuat_pada', 'desc')
        ->take(10)
        ->get();

    return view('tenant.dashboard', [
        'client' => $client,
        'clientModel' => $clientModel,
        'resepPending' => $resepPending,
        'fefoBatches' => $fefoBatches,
        'antreanBerjalan' => $antreanBerjalan,
        'pesanansAktif' => $pesanansAktif,
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
    $resepPending = Resep::query()
        ->with(['pesanan.pengguna', 'pesanan.itemPesanan.obat'])
        ->where('status_verifikasi', 'menunggu')
        ->orderBy('dibuat_pada', 'desc')
        ->get();

    return view('tenant.verifikasi-resep', [
        'client' => $client,
        'resepPending' => $resepPending,
    ]);
})->name('resep.antrean');

Route::get('/stok-fefo', function (string $client, StockFefoService $fefoService) {
    $fefoBatches = $fefoService->getBatchesByFefo($client);

    return view('tenant.stok-fefo', [
        'client' => $client,
        'fefoBatches' => $fefoBatches,
    ]);
})->name('stok.fefo');

Route::get('/bantuan', function (string $client) {
    return view('tenant.bantuan', [
        'client' => $client,
    ]);
})->name('bantuan.index');

Route::get('/pasien', function (string $client) {
    $pasiens = Pengguna::where('peran', 'pelanggan')->get();

    return view('tenant.pasien', [
        'client' => $client,
        'pasiens' => $pasiens,
    ]);
})->name('pasien.index');

Route::post('/resep/{id}/verifikasi', function (string $client, string $id, VerifikasiResepRequest $request) {
    $action = $request->input('action', 'disetujui');
    $catatan = $request->input('catatan', 'Resep asli dan masih berlaku.');

    $resep = Resep::query()->find($id);
    if ($resep) {
        $resep->update([
            'status_verifikasi' => $action,
            'catatan_apoteker' => $catatan,
            'sip_valid' => true,
            'dosis_valid' => true,
        ]);
        if ($resep->pesanan) {
            $resep->pesanan->update([
                'status' => $action === 'disetujui' ? 'diproses' : 'dibatalkan',
            ]);
        }
    }

    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => "Resep {$id} berhasil diverifikasi ({$action}).",
            'status_verifikasi' => $action,
        ]);
    }

    return back()->with('success', "Resep {$id} berhasil diverifikasi ({$action}).");
})->name('resep.verifikasi');

Route::post('/stok/{id}/penyesuaian', function (string $client, string $id, Request $request) {
    $stok = (int) $request->input('jumlah_stok', 12);
    $stokRecord = StokClient::query()->find($id);
    if ($stokRecord) {
        $stokRecord->update(['jumlah_stok' => $stok]);
    }

    return response()->json([
        'success' => true,
        'message' => "Stok obat pada cabang {$client} berhasil disesuaikan.",
        'jumlah_stok' => $stok,
    ]);
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
