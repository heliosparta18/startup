<x-layouts.admin :title="'Pengaturan Cabang - OmniPharma'">
    <div class="space-y-6">
        <!-- Breadcrumb & Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-zinc-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-zinc-900 transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-zinc-900 font-semibold">Pengaturan Cabang Apotek</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Header -->
        <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                        Konfigurasi Multi-Tenant
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-2">
                        Pengaturan &amp; Operasional Cabang
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                        Pengaturan parameter cabang apotek, radius layanan pengiriman kurir, dan jadwal jam operasional harian.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-mono text-xs font-bold border border-slate-200">
                        2 Cabang Terdaftar
                    </span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Grid 2 Cabang: Banyuwangi Kota & Rogojampi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($clients as $c)
                <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Cabang Terdaftar</span>
                            <h3 class="text-lg font-bold text-slate-900">{{ $c->nama_client }}</h3>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $c->aktif ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $c->aktif ? 'Beroperasi' : 'Tutup Sementara' }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.cabang.pengaturan.save', ['client' => $c->slug ?? $c->id_client]) }}" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Apotek Cabang</label>
                            <input type="text" name="nama_client" value="{{ $c->nama_client }}" required class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Alamat Operasional Cabang</label>
                            <textarea name="alamat" rows="2" required class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20">{{ $c->alamat }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Radius Pengiriman (km)</label>
                                <input type="number" name="radius_km" value="5" min="1" max="25" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Jam Operasional</label>
                                <input type="text" name="jam_buka" value="07:00 - 22:00 WIB" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                Simpan Perubahan Cabang
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
