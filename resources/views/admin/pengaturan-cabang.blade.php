<x-layouts.admin :title="'Pengaturan Cabang - OmniPharma'">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-800 font-medium transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Pengaturan Cabang Apotek</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Hero Banner: Nuansa Deep Spruce Hijau Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Konfigurasi Multi-Tenant Cabang
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Pengaturan &amp; Operasional Cabang
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Pengaturan parameter cabang apotek, radius layanan pengiriman kurir, dan jadwal jam operasional harian seluruh gerai.
                    </p>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-[#D4F94E] font-bold text-xs backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-[#D4F94E] animate-pulse"></span>
                        {{ count($clients) }} Cabang Terhubung
                    </span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-xs font-bold text-emerald-900 flex items-center gap-2.5 shadow-xs">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Grid 2 Cabang: Banyuwangi Kota & Rogojampi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            @foreach($clients as $c)
                <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs space-y-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Gerai Resmi Apotek</span>
                                <h3 class="text-lg font-extrabold text-slate-900">{{ $c->nama_client }}</h3>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $c->aktif ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $c->aktif ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                                {{ $c->aktif ? 'Beroperasi' : 'Tutup Sementara' }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('admin.cabang.pengaturan.save', ['client' => $c->slug ?? $c->id_client]) }}" class="mt-5 space-y-4 text-xs">
                            @csrf
                            <div>
                                <label class="block font-bold text-slate-700 mb-1.5">Nama Resmi Cabang</label>
                                <input type="text" name="nama_client" value="{{ $c->nama_client }}" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1.5">Alamat Fisik Cabang (SIPA Terdaftar)</label>
                                <textarea name="alamat" rows="2" required class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">{{ $c->alamat }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1.5">Radius Pengiriman (km)</label>
                                    <input type="number" name="radius_km" value="5" min="1" max="25" class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1.5">Jam Operasional Layanan</label>
                                    <input type="text" name="jam_buka" value="07:00 - 22:00 WIB" class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full min-h-[46px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                    <span>Simpan Perubahan Cabang</span>
                                    <span class="text-sm">&rarr;</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
