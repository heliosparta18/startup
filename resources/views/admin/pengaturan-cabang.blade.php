<x-layouts.admin title="Pengaturan Operasional Multi-Cabang - OmniPharma">
    <div x-data="{ showAddBranch: false }" class="space-y-6 sm:space-y-8">
        <!-- Header -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    <span>Manajemen Cabang Apotek | {{ count($clients) }} Cabang Terhubung</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3">
                    Pengaturan Operasional &amp; Lokasi Cabang
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl">
                    Tambahkan cabang baru, ubah alamat gerai, atur jam operasional, radius pengiriman, serta aktifkan atau nonaktifkan operasional apotek.
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-2.5">
                <button type="button" @click="showAddBranch = !showAddBranch" class="inline-flex min-h-[44px] items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <span x-text="showAddBranch ? 'Tutup Form Tambah' : '+ Tambah Cabang Baru'"></span>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-[44px] items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 shadow-xs flex items-center gap-2.5">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-bold text-rose-900 space-y-1">
                @if (session('error'))<p>{{ session('error') }}</p>@endif
                @foreach ($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <!-- Form Tambah Cabang Baru -->
        <div x-show="showAddBranch" x-cloak class="rounded-3xl border border-emerald-200 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-extrabold text-slate-900">Buka Cabang Apotek Baru</h2>
                <p class="text-xs text-slate-500 mt-0.5">Cabang baru langsung tersedia di portal admin, tenant apoteker, dan pemilihan cabang pelanggan.</p>
            </div>
            <form method="POST" action="{{ route('admin.cabang.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Cabang *</label>
                    <input type="text" name="nama_client" required placeholder="Cabang Genteng" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3.5 text-slate-900 focus:border-emerald-600 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Slug URL Cabang (Opsional)</label>
                    <input type="text" name="slug" placeholder="genteng" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3.5 font-mono text-slate-900 focus:border-emerald-600 focus:outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Alamat Lengkap *</label>
                    <textarea name="alamat" rows="2" required placeholder="Jl. Gajah Mada No. 12, Genteng, Banyuwangi" class="w-full rounded-xl border border-slate-300 p-3 text-slate-900 focus:border-emerald-600 focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Radius Layanan (KM)</label>
                    <input type="number" name="radius_km" min="1" max="50" value="5" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3.5 text-slate-900">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jam Operasional</label>
                    <input type="text" name="jam_buka" value="07:00 - 22:00 WIB" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3.5 text-slate-900">
                </div>
                <div class="md:col-span-2 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 pt-2">
                    <button type="button" @click="showAddBranch = false" class="min-h-[44px] px-4 rounded-xl border border-slate-300 text-slate-700 font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="min-h-[44px] px-6 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold shadow-xs cursor-pointer">Simpan Cabang Baru</button>
                </div>
            </form>
        </div>

        <!-- Daftar Kartu Konfigurasi Cabang -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach ($clients as $c)
                @php $branchSlug = $c->slug ?? $c->id_client; @endphp
                <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs flex flex-col justify-between space-y-5">
                    <div>
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <span class="text-[11px] font-mono text-slate-500">Kode: {{ $branchSlug }}</span>
                                <h3 class="text-lg font-extrabold text-slate-900 mt-0.5">{{ $c->nama_client }}</h3>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold shrink-0 {{ $c->aktif ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $c->aktif ? 'Beroperasi Aktif' : 'Tutup Sementara' }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('admin.cabang.pengaturan.save', ['client' => $branchSlug]) }}" class="mt-5 space-y-4 text-xs">
                            @csrf
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nama Cabang Resmi *</label>
                                <input type="text" name="nama_client" value="{{ $c->nama_client }}" required class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-slate-900 focus:border-emerald-600 focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Alamat Lengkap Gerai *</label>
                                <textarea name="alamat" rows="2" required class="w-full rounded-xl border border-slate-300 bg-white p-3 text-slate-900 focus:border-emerald-600 focus:outline-none">{{ $c->alamat }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Radius (KM)</label>
                                    <input type="number" name="radius_km" min="1" max="50" value="{{ $c->radius_km ?? 5 }}" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Jam Operasional</label>
                                    <input type="text" name="jam_buka" value="{{ $c->jam_buka ?? '07:00 - 22:00 WIB' }}" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Status Gerai</label>
                                    <select name="aktif" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                                        <option value="1" @selected((bool) $c->aktif)>Beroperasi</option>
                                        <option value="0" @selected(! (bool) $c->aktif)>Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row sm:flex-wrap sm:items-center justify-between gap-2.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.cabang.staf', ['client' => $branchSlug]) }}" class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold text-xs transition">
                                        Kelola Staf
                                    </a>
                                    <a href="{{ route('tenant.laporan.index', ['client' => $branchSlug]) }}" class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold text-xs transition">
                                        Laporan Penjualan
                                    </a>
                                </div>
                                <button type="submit" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-700 hover:bg-emerald-800 px-5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                                    Simpan Konfigurasi
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
                        <span>Terakhir diperbarui: {{ $c->updated_at ? $c->updated_at->format('d M Y H:i') : '-' }}</span>
                        <form method="POST" action="{{ route('admin.cabang.destroy', ['client' => $branchSlug]) }}" onsubmit="return confirm('Hapus cabang {{ addslashes($c->nama_client) }}? Cabang dengan riwayat transaksi akan tetap dilindungi.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="min-h-[44px] inline-flex items-center font-bold text-rose-700 hover:text-rose-900 underline cursor-pointer">
                                Hapus Cabang
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
