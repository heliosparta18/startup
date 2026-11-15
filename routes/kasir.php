<?php

use App\Models\AdminClient;
use App\Models\Client;
use App\Models\Obat;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\PeranClientPengguna;
use App\Models\Pesanan;
use App\Models\StokClient;
use App\Models\User;
use App\Modules\Ordering\Actions\CancelOrderAction;
use App\Modules\Ordering\Actions\CreateOrderAction;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Portal Kasir & Staf Pembayaran Loket Cabang
|--------------------------------------------------------------------------
| Prefix: /kasir | Name: kasir.* | Middleware: web, auth, verified, role:kasir
*/

$resolveKasirBranch = static function (?User $user): Client {
    if ($user) {
        $pengguna = Pengguna::where('telepon_email', $user->email)->first();
        if ($pengguna) {
            $adminClient = AdminClient::where('id_pengguna', $pengguna->id_pengguna)
                ->with('client')
                ->first();
            if ($adminClient && $adminClient->client) {
                return $adminClient->client;
            }

            $peranClient = PeranClientPengguna::where('id_pengguna', $pengguna->id_pengguna)
                ->with('client')
                ->first();
            if ($peranClient && $peranClient->client) {
                return $peranClient->client;
            }
        }

        $slug = $user->getClientSlug();
        $resolved = Client::where('slug', $slug)->first();
        if ($resolved) {
            return $resolved;
        }
    }

    return Client::where('aktif', true)->orderBy('nama_client')->first()
        ?? Client::firstOrFail();
};

Route::get('/dashboard', function (Request $request) use ($resolveKasirBranch) {
    /** @var User|null $authUser */
    $authUser = $request->user();
    $client = $resolveKasirBranch($authUser);

    $unpaidOrders = Pesanan::withoutTenancy()
        ->with(['pengguna', 'items', 'pembayaran'])
        ->where('id_client', $client->id_client)
        ->where('status', 'menunggu_pembayaran')
        ->orderBy('dibuat_pada', 'desc')
        ->get();

    $recentPayments = Pembayaran::query()
        ->whereHas('pesanan', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $client->id_client))
        ->with(['pesanan.pengguna', 'pesanan.items', 'verifikator'])
        ->orderBy('dibuat_pada', 'desc')
        ->take(30)
        ->get();

    $settledQuery = Pembayaran::query()
        ->where('status', 'lunas')
        ->whereHas('pesanan', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $client->id_client));

    $todayStart = now()->startOfDay()->toDateTimeString();
    $todayEnd = now()->endOfDay()->toDateTimeString();

    $paymentStats = (clone $settledQuery)
        ->selectRaw(
            'COALESCE(SUM(jumlah), 0) as total_omzet, COUNT(*) as jumlah_struk, '
            .'COALESCE(SUM(CASE WHEN dibuat_pada >= ? AND dibuat_pada <= ? THEN jumlah ELSE 0 END), 0) as omzet_hari_ini, '
            ."COALESCE(SUM(CASE WHEN metode_pembayaran = 'tunai' THEN jumlah ELSE 0 END), 0) as total_tunai, "
            ."COALESCE(SUM(CASE WHEN metode_pembayaran = 'qris' THEN jumlah ELSE 0 END), 0) as total_qris",
            [$todayStart, $todayEnd]
        )
        ->first();

    $omzetHariIni = (float) ($paymentStats->omzet_hari_ini ?? 0);
    $totalOmzetLunas = (float) ($paymentStats->total_omzet ?? 0);
    $jumlahStrukLunas = (int) ($paymentStats->jumlah_struk ?? 0);
    $totalTunai = (float) ($paymentStats->total_tunai ?? 0);
    $totalQris = (float) ($paymentStats->total_qris ?? 0);

    $stokObat = StokClient::withoutTenancy()
        ->with('obat:id_obat,nama_obat,harga,jenis_sediaan,badge_status')
        ->join('obat', 'stok_client.id_obat', '=', 'obat.id_obat')
        ->select('stok_client.id', 'stok_client.id_obat', 'stok_client.id_client', 'stok_client.jumlah_stok', 'stok_client.diperbarui_pada')
        ->where('stok_client.id_client', $client->id_client)
        ->where('stok_client.jumlah_stok', '>', 0)
        ->orderBy('obat.nama_obat', 'asc')
        ->limit(80)
        ->get();

    return view('kasir.dashboard', [
        'client' => $client,
        'clientSlug' => $client->slug ?? 'banyuwangi-kota',
        'unpaidOrders' => $unpaidOrders,
        'recentPayments' => $recentPayments,
        'omzetHariIni' => $omzetHariIni,
        'totalOmzetLunas' => $totalOmzetLunas,
        'jumlahStrukLunas' => $jumlahStrukLunas,
        'totalTunai' => $totalTunai,
        'totalQris' => $totalQris,
        'stokObat' => $stokObat,
    ]);
})->name('dashboard');

Route::post('/transaksi-walkin', function (Request $request, CreateOrderAction $createOrderAction) use ($resolveKasirBranch) {
    /** @var User|null $authUser */
    $authUser = $request->user();
    $client = $resolveKasirBranch($authUser);

    $validated = $request->validate([
        'id_obat' => ['required', 'uuid', 'exists:obat,id_obat'],
        'jumlah' => ['required', 'integer', 'min:1', 'max:50'],
        'nama_pasien' => ['nullable', 'string', 'max:100'],
        'metode_pembayaran' => ['required', 'string', 'in:tunai,qris'],
        'langsung_selesai' => ['nullable', 'boolean'],
    ]);

    $obat = Obat::where('id_obat', $validated['id_obat'])->firstOrFail();
    $qty = (int) $validated['jumlah'];
    $namaPasien = trim((string) ($validated['nama_pasien'] ?? ''));
    $namaPasien = $namaPasien !== '' ? strip_tags($namaPasien) : 'Pelanggan Walk-in';

    $kasirPengguna = $authUser?->email
        ? Pengguna::where('telepon_email', $authUser->email)->first()
        : null;

    $guestCustomer = Pengguna::where('peran', 'pelanggan')->first();

    try {
        $result = DB::transaction(function () use (
            $createOrderAction,
            $client,
            $obat,
            $qty,
            $namaPasien,
            $validated,
            $kasirPengguna,
            $guestCustomer,
            $request
        ) {
            $pesanan = $createOrderAction->execute(
                $client->id_client,
                $guestCustomer?->id_pengguna,
                [[
                    'id_obat' => $obat->id_obat,
                    'nama_obat' => $obat->nama_obat,
                    'harga' => (float) $obat->harga,
                    'jumlah' => $qty,
                ]],
                'click_collect',
                null,
                'Langsung di Kasir',
                null,
                'Walk-in Kasir: '.$namaPasien
            );

            $subtotal = (float) $obat->harga * $qty;
            $nextStatus = $request->boolean('langsung_selesai', true) ? 'selesai' : 'diproses';
            $pesanan->update(['status' => $nextStatus]);

            $pembayaran = Pembayaran::create([
                'id_pembayaran' => (string) Str::uuid(),
                'id_pesanan' => $pesanan->id_pesanan,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'jumlah' => $subtotal,
                'status' => 'lunas',
                'diverifikasi_oleh' => $kasirPengguna?->id_pengguna,
                'dibuat_pada' => now(),
            ]);

            try {
                OrderStatusUpdated::dispatch($pesanan);
            } catch (Throwable) {
                // Jangan gagalkan transaksi kasir jika WebSocket Reverb offline
            }

            return [$pesanan, $pembayaran];
        });

        /** @var Pesanan $pesanan */
        /** @var Pembayaran $pembayaran */
        [$pesanan, $pembayaran] = $result;

        return redirect()
            ->route('kasir.dashboard')
            ->with('success', "Transaksi {$pesanan->nomor_pesanan} lunas via ".strtoupper($validated['metode_pembayaran']).'.')
            ->with('struk_id', $pembayaran->id_pembayaran);
    } catch (RuntimeException $e) {
        return back()->withInput()->with('error', $e->getMessage());
    }
})->name('walkin.store');

Route::post('/pesanan/{id}/lunasi', function (string $id, Request $request) use ($resolveKasirBranch) {
    /** @var User|null $authUser */
    $authUser = $request->user();
    $client = $resolveKasirBranch($authUser);

    $validated = $request->validate([
        'metode_pembayaran' => ['required', 'string', 'in:tunai,qris'],
    ]);

    $pesanan = Pesanan::withoutTenancy()
        ->with(['items', 'resep'])
        ->where('id_client', $client->id_client)
        ->where('id_pesanan', $id)
        ->firstOrFail();

    $kasirPengguna = $authUser?->email
        ? Pengguna::where('telepon_email', $authUser->email)->first()
        : null;

    $totalTagihan = (float) $pesanan->total_harga;
    if ($totalTagihan <= 0) {
        $totalTagihan = (float) Pembayaran::where('id_pesanan', $pesanan->id_pesanan)->value('jumlah');
    }

    $pembayaran = DB::transaction(function () use ($pesanan, $validated, $totalTagihan, $kasirPengguna) {
        $existing = Pembayaran::where('id_pesanan', $pesanan->id_pesanan)->first();
        if ($existing) {
            $existing->update([
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'jumlah' => $totalTagihan > 0 ? $totalTagihan : $existing->jumlah,
                'status' => 'lunas',
                'diverifikasi_oleh' => $kasirPengguna?->id_pengguna,
            ]);
            $pembayaran = $existing->fresh();
        } else {
            $pembayaran = Pembayaran::create([
                'id_pembayaran' => (string) Str::uuid(),
                'id_pesanan' => $pesanan->id_pesanan,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'jumlah' => $totalTagihan,
                'status' => 'lunas',
                'diverifikasi_oleh' => $kasirPengguna?->id_pengguna,
                'dibuat_pada' => now(),
            ]);
        }

        if ($pesanan->status === 'menunggu_pembayaran') {
            $pesanan->update([
                'status' => ($pesanan->resep && $pesanan->resep->status_verifikasi === 'menunggu')
                    ? 'menunggu_verifikasi'
                    : 'diproses',
            ]);
        }

        try {
            OrderStatusUpdated::dispatch($pesanan->fresh() ?? $pesanan);
        } catch (Throwable) {
            // Skip jika Reverb tidak aktif
        }

        return $pembayaran;
    });

    return redirect()
        ->route('kasir.dashboard')
        ->with('success', "Pelunasan pesanan {$pesanan->nomor_pesanan} terverifikasi (".strtoupper($validated['metode_pembayaran']).').')
        ->with('struk_id', $pembayaran?->id_pembayaran);
})->name('pesanan.lunasi');

Route::post('/pesanan/{id}/batalkan', function (string $id, Request $request, CancelOrderAction $cancelOrderAction) use ($resolveKasirBranch) {
    /** @var User|null $authUser */
    $authUser = $request->user();
    $client = $resolveKasirBranch($authUser);

    $pesanan = Pesanan::withoutTenancy()
        ->where('id_client', $client->id_client)
        ->where('id_pesanan', $id)
        ->firstOrFail();

    try {
        $cancelOrderAction->execute($pesanan, 'Dibatalkan oleh kasir di loket cabang.');

        return redirect()
            ->route('kasir.dashboard')
            ->with('success', "Pesanan {$pesanan->nomor_pesanan} dibatalkan dan stok fisik dikembalikan ke rak.");
    } catch (RuntimeException $e) {
        return back()->with('error', $e->getMessage());
    }
})->name('pesanan.batalkan');

Route::get('/ledger', function (Request $request) use ($resolveKasirBranch) {
    /** @var User|null $authUser */
    $authUser = $request->user();
    $client = $resolveKasirBranch($authUser);

    $metodeFilter = (string) $request->query('metode', 'semua');
    $statusFilter = (string) $request->query('status', 'semua');
    $tanggalFilter = (string) $request->query('tanggal', '');
    $search = trim((string) $request->query('q', ''));

    $baseQuery = Pembayaran::query()
        ->whereHas('pesanan', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $client->id_client));

    $filteredQuery = (clone $baseQuery)
        ->with(['pesanan.pengguna', 'pesanan.items', 'verifikator'])
        ->when($metodeFilter !== 'semua', fn ($q) => $q->where('metode_pembayaran', $metodeFilter))
        ->when($statusFilter !== 'semua', fn ($q) => $q->where('status', $statusFilter))
        ->when($tanggalFilter !== '', fn ($q) => $q->whereDate('dibuat_pada', $tanggalFilter))
        ->when($search !== '', function ($q) use ($search): void {
            $q->whereHas('pesanan', fn ($oq) => $oq->withoutGlobalScopes()->where('nomor_pesanan', 'like', "%{$search}%"));
        });

    $pembayarans = $filteredQuery->orderBy('dibuat_pada', 'desc')->take(100)->get();

    $settledBranchQuery = (clone $baseQuery)->where('status', 'lunas');
    $totalHariIni = (float) (clone $settledBranchQuery)->whereDate('dibuat_pada', now()->toDateString())->sum('jumlah');
    $totalSemuaLunas = (float) (clone $settledBranchQuery)->sum('jumlah');
    $totalTransaksi = (clone $baseQuery)->count();
    $totalLunasCount = (clone $settledBranchQuery)->count();
    $totalQris = (float) (clone $settledBranchQuery)->where('metode_pembayaran', 'qris')->sum('jumlah');
    $totalTunai = (float) (clone $settledBranchQuery)->where('metode_pembayaran', 'tunai')->sum('jumlah');

    return view('kasir.ledger', [
        'client' => $client,
        'clientSlug' => $client->slug ?? 'banyuwangi-kota',
        'pembayarans' => $pembayarans,
        'totalHariIni' => $totalHariIni,
        'totalSemuaLunas' => $totalSemuaLunas,
        'totalTransaksi' => $totalTransaksi,
        'totalLunasCount' => $totalLunasCount,
        'totalQris' => $totalQris,
        'totalTunai' => $totalTunai,
        'metodeFilter' => $metodeFilter,
        'statusFilter' => $statusFilter,
        'tanggalFilter' => $tanggalFilter,
        'search' => $search,
    ]);
})->name('ledger');

Route::get('/struk/{id}', function (string $id, Request $request) use ($resolveKasirBranch) {
    /** @var User|null $authUser */
    $authUser = $request->user();
    $client = $resolveKasirBranch($authUser);

    $pembayaran = Pembayaran::query()
        ->with(['pesanan.pengguna', 'pesanan.items', 'verifikator'])
        ->where('id_pembayaran', $id)
        ->whereHas('pesanan', fn ($q) => $q->withoutGlobalScopes()->where('id_client', $client->id_client))
        ->firstOrFail();

    return view('kasir.struk', [
        'client' => $client,
        'clientSlug' => $client->slug ?? 'banyuwangi-kota',
        'pembayaran' => $pembayaran,
        'pesanan' => $pembayaran->pesanan,
    ]);
})->name('struk');

Route::get('/pos-obat-search', function (Request $request) use ($resolveKasirBranch) {
    /** @var User|null $authUser */
    $authUser = $request->user();
    $client = $resolveKasirBranch($authUser);
    $keyword = trim((string) $request->query('q', ''));

    $query = StokClient::withoutTenancy()
        ->with('obat:id_obat,nama_obat,harga,jenis_sediaan,badge_status')
        ->join('obat', 'stok_client.id_obat', '=', 'obat.id_obat')
        ->select('stok_client.id', 'stok_client.id_obat', 'stok_client.id_client', 'stok_client.jumlah_stok')
        ->where('stok_client.id_client', $client->id_client)
        ->where('stok_client.jumlah_stok', '>', 0);

    if ($keyword !== '') {
        $escaped = addcslashes($keyword, '%_\\');
        $query->where('obat.nama_obat', 'like', '%'.$escaped.'%');
    }

    $items = $query->orderBy('obat.nama_obat', 'asc')
        ->limit(50)
        ->get()
        ->map(fn (StokClient $s): array => [
            'id' => $s->id_obat,
            'nama' => $s->obat->nama_obat ?? 'Obat',
            'harga' => (float) ($s->obat->harga ?? 0),
            'stok' => (int) $s->jumlah_stok,
            'sediaan' => $s->obat->jenis_sediaan ?? 'Sediaan',
            'golongan' => $s->obat->badge_status ?? 'Obat Bebas',
        ])
        ->values();

    return response()->json(['items' => $items]);
})->name('pos.search');
