@php
    $settingsUser = auth()->user();
    $settingsUserName = $settingsUser->name ?? 'Pengguna OmniPharma';
    $settingsUserEmail = $settingsUser->email ?? '-';
    $settingsRoleLabel = $settingsUser && method_exists($settingsUser, 'roleLabel') ? $settingsUser->roleLabel() : 'Customer';
    $settingsClientName = $settingsUser && method_exists($settingsUser, 'getClientName') ? $settingsUser->getClientName() : 'Cabang Banyuwangi Kota';
    $settingsPhotoUrl = session('firebase_photo') ?? null;
@endphp

<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 mb-6">
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-3">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-700 transition">Katalog Apotek</a>
        <span>/</span>
        <span class="text-slate-900 font-bold">Pengaturan Akun &amp; Profile</span>
    </div>

    <div class="rounded-3xl bg-white text-slate-900 p-5 sm:p-7 shadow-xs border border-slate-200 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                @if($settingsPhotoUrl)
                    <img src="{{ $settingsPhotoUrl }}" alt="{{ $settingsUserName }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shrink-0 shadow-xs">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl tracking-tight shrink-0 shadow-xs">
                        {{ $settingsUser && method_exists($settingsUser, 'initials') ? $settingsUser->initials() : strtoupper(substr($settingsUserName, 0, 2)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <span class="text-xs font-medium text-slate-500 block">{{ $settingsClientName }}</span>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 mt-0.5 truncate">{{ $settingsUserName }}</h1>
                    <p class="text-xs sm:text-sm text-slate-600 truncate">{{ $settingsUserEmail }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 self-start sm:self-center">
                <a
                    href="{{ \Illuminate\Support\Facades\Route::has('pesanan.riwayat') ? route('pesanan.riwayat') : route('dashboard') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-xs font-bold transition"
                >
                    <span>Riwayat Pesanan</span>
                </a>
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition shadow-xs"
                >
                    <span>Belanja Obat</span>
                </a>
            </div>
        </div>
    </div>
</div>
