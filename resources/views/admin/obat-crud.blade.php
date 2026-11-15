<x-layouts.admin title="Master Obat &amp; Stok Multi-Cabang - OmniPharma">
    @php
        $branchInventory = collect($clients)->map(fn ($c) => [
            'id_client' => $c->id_client,
            'nama_client' => $c->nama_client,
            'slug' => $c->slug ?? $c->id_client,
        ])->values();
    @endphp
    <div x-data="{
        createModal: false,
        editModal: false,
        editActionBase: '{{ url('/admin/obat') }}',
        editForm: {
            id_obat: '',
            nama_obat: '',
            merek: '',
            id_kategori: '',
            jenis_sediaan: 'Tablet',
            satuan_kemasan: 'Strip @ 10 Tablet',
            badge_status: 'Obat Bebas',
            harga: 10000,
            expired: '',
            batch_number: '',
            nomor_ijin_edar: '',
            indikasi: '',
            stok: {}
        },
        openEdit(obat, stokMap) {
            this.editForm = {
                id_obat: obat.id_obat,
                nama_obat: obat.nama_obat || '',
                merek: obat.merek || '',
                id_kategori: obat.id_kategori || '',
                jenis_sediaan: obat.jenis_sediaan || 'Tablet',
                satuan_kemasan: obat.satuan_kemasan || '',
                badge_status: obat.badge_status || 'Obat Bebas',
                harga: Number(obat.harga || 0),
                expired: obat.expired ? String(obat.expired).slice(0, 10) : '',
                batch_number: obat.batch_number || '',
                nomor_ijin_edar: obat.nomor_ijin_edar || '',
                indikasi: obat.indikasi || '',
                stok: Object.assign({}, stokMap || {})
            };
            this.editModal = true;
        }
    }" class="space-y-6 sm:space-y-8">

        <!-- Header -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                    <span>Manajemen Katalog Nasional &amp; Distribusi Stok</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-3">
                    Master Obat &amp; Stok Lintas Cabang
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl">
                    Tambah obat baru, perbarui harga dan izin BPOM, serta atur alokasi stok awal ke setiap cabang apotek.
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-2.5">
                <button type="button" @click="createModal = true" class="inline-flex min-h-[44px] items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <span>+ Tambah Master Obat</span>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-[44px] items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                    &larr; Ringkasan Pusat
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 flex items-center gap-2.5">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-bold text-rose-900 space-y-1">
                @if(session('error'))<p>{{ session('error') }}</p>@endif
                @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        <!-- Filter & Search -->
        <form method="GET" action="{{ route('admin.obat.index') }}" class="rounded-2xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label for="search-obat" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Cari Obat / Merek / Batch</label>
                <input id="search-obat" type="text" name="q" value="{{ $search }}" placeholder="Contoh: Paracetamol..." class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 text-xs text-slate-900 focus:border-emerald-600 focus:outline-none">
            </div>
            <div>
                <label for="filter-kategori" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Kategori Farmakologi</label>
                <select id="filter-kategori" name="kategori" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900 focus:border-emerald-600 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id_kategori }}" @selected($kategoriFilter === $kat->id_kategori)>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-golongan" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Golongan Obat</label>
                <select id="filter-golongan" name="golongan" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900 focus:border-emerald-600 focus:outline-none">
                    <option value="">Semua Golongan</option>
                    @foreach(['Obat Bebas', 'Obat Bebas Terbatas', 'Obat Keras', 'Alat Kesehatan'] as $gol)
                        <option value="{{ $gol }}" @selected($golonganFilter === $gol)>{{ $gol }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 min-h-[44px] rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if($search || $kategoriFilter || $golonganFilter)
                    <a href="{{ route('admin.obat.index') }}" class="min-h-[44px] inline-flex items-center px-3.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Tabel Master Obat -->
        <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Daftar Katalog Obat Terdaftar</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $obats->total() }} item obat pada jaringan OmniPharma</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[60rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="p-4">Produk &amp; Merek</th>
                            <th class="p-4">Kategori &amp; Sediaan</th>
                            <th class="p-4">Golongan BPOM</th>
                            <th class="p-4">Harga Jual</th>
                            <th class="p-4">Batch &amp; ED</th>
                            <th class="p-4">Sebaran Stok Cabang</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse($obats as $obat)
                            @php
                                $stokByClient = $obat->allStokClient->mapWithKeys(fn ($s) => [$s->id_client => (int) $s->jumlah_stok])->all();
                                $obatPayload = [
                                    'id_obat' => $obat->id_obat,
                                    'nama_obat' => $obat->nama_obat,
                                    'merek' => $obat->merek,
                                    'id_kategori' => $obat->id_kategori,
                                    'jenis_sediaan' => $obat->jenis_sediaan,
                                    'satuan_kemasan' => $obat->satuan_kemasan,
                                    'badge_status' => $obat->badge_status,
                                    'harga' => $obat->harga,
                                    'expired' => $obat->expired,
                                    'batch_number' => $obat->batch_number,
                                    'nomor_ijin_edar' => $obat->nomor_ijin_edar,
                                    'indikasi' => $obat->indikasi,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition align-top">
                                <td class="p-4">
                                    <div class="font-bold text-sm text-slate-900">{{ $obat->nama_obat }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $obat->merek ?? '-' }} | NIE: {{ $obat->nomor_ijin_edar ?? '-' }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-800">{{ $obat->kategori->nama_kategori ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $obat->jenis_sediaan }} ({{ $obat->satuan_kemasan ?? '-' }})</div>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    @if($obat->badge_status === 'Obat Keras')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                            <span class="w-3.5 h-3.5 rounded-full bg-rose-600 text-white text-[9px] font-black inline-flex items-center justify-center">K</span>
                                            <span>Obat Keras</span>
                                        </span>
                                    @elseif($obat->badge_status === 'Obat Bebas Terbatas')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                            <span class="w-3 h-3 rounded-full bg-sky-600 border border-slate-900 inline-block"></span>
                                            <span>Bebas Terbatas</span>
                                        </span>
                                    @elseif($obat->badge_status === 'Alat Kesehatan')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span>Alat Kesehatan</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="w-3 h-3 rounded-full bg-emerald-600 border border-slate-900 inline-block"></span>
                                            <span>Obat Bebas</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 font-extrabold text-emerald-700 whitespace-nowrap">
                                    Rp {{ number_format((float) $obat->harga, 0, ',', '.') }}
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <div class="font-mono text-slate-700">{{ $obat->batch_number ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">ED: {{ $obat->expired ? \Carbon\Carbon::parse($obat->expired)->format('d M Y') : '-' }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($clients as $branch)
                                            @php $qty = $stokByClient[$branch->id_client] ?? 0; @endphp
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold {{ $qty <= 5 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                                <span>{{ Str::limit(str_replace('Cabang ', '', $branch->nama_client), 12) }}:</span>
                                                <strong>{{ $qty }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button"
                                            @click='openEdit(@json($obatPayload), @json($stokByClient))'
                                            class="min-h-[38px] px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold text-[11px] transition cursor-pointer">
                                            Edit &amp; Stok
                                        </button>
                                        <form method="POST" action="{{ route('admin.obat.destroy', ['id' => $obat->id_obat]) }}" onsubmit="return confirm('Hapus atau kosongkan stok obat {{ addslashes($obat->nama_obat) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="min-h-[38px] px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-bold text-[11px] transition cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500">Tidak ada data obat yang cocok dengan kriteria pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($obats->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $obats->withQueryString()->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Tambah Obat -->
        <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs overflow-y-auto">
            <div @click.away="createModal = false" class="w-full max-w-2xl rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200 my-auto space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-black text-slate-900">Tambah Master Obat Baru</h3>
                    <button type="button" @click="createModal = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-500 hover:text-slate-900 rounded-xl cursor-pointer">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.obat.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Obat *</label>
                            <input type="text" name="nama_obat" required placeholder="Paracetamol 500 mg" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merek / Pabrikan</label>
                            <input type="text" name="merek" placeholder="Sanbe Farma" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                            <select name="id_kategori" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Golongan BPOM *</label>
                            <select name="badge_status" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                                <option value="Obat Bebas">Obat Bebas</option>
                                <option value="Obat Bebas Terbatas">Obat Bebas Terbatas</option>
                                <option value="Obat Keras">Obat Keras</option>
                                <option value="Alat Kesehatan">Alat Kesehatan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Sediaan *</label>
                            <input type="text" name="jenis_sediaan" required value="Tablet" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Satuan Kemasan</label>
                            <input type="text" name="satuan_kemasan" value="Strip @ 10 Tablet" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Harga Jual (Rp) *</label>
                            <input type="number" name="harga" min="100" step="100" required value="12000" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Kedaluwarsa *</label>
                            <input type="date" name="expired" required value="{{ now()->addMonths(18)->toDateString() }}" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Batch</label>
                            <input type="text" name="batch_number" placeholder="BCH-2026-01" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Izin Edar (BPOM)</label>
                            <input type="text" name="nomor_ijin_edar" placeholder="DBL20260001A1" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Indikasi Singkat</label>
                        <textarea name="indikasi" rows="2" placeholder="Indikasi terapeutik utama..." class="w-full rounded-xl border border-slate-300 p-3 text-slate-900 focus:border-emerald-600 focus:outline-none"></textarea>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 space-y-2">
                        <span class="block font-bold text-slate-800">Stok Awal Per Cabang</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($clients as $branch)
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ $branch->nama_client }}</label>
                                    <input type="number" min="0" value="25" name="stok[{{ $branch->id_client }}]" class="w-full min-h-[40px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="createModal = false" class="min-h-[44px] px-4 rounded-xl border border-slate-300 text-slate-700 font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs cursor-pointer">Simpan Master Obat</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Obat & Stok -->
        <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs overflow-y-auto">
            <div @click.away="editModal = false" class="w-full max-w-2xl rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200 my-auto space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-black text-slate-900">Perbarui Obat &amp; Sebaran Stok</h3>
                    <button type="button" @click="editModal = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-500 hover:text-slate-900 rounded-xl cursor-pointer">&times;</button>
                </div>

                <form method="POST" :action="editActionBase + '/' + editForm.id_obat" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Obat *</label>
                            <input type="text" name="nama_obat" x-model="editForm.nama_obat" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Merek</label>
                            <input type="text" name="merek" x-model="editForm.merek" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                            <select name="id_kategori" x-model="editForm.id_kategori" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Golongan BPOM *</label>
                            <select name="badge_status" x-model="editForm.badge_status" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                                <option value="Obat Bebas">Obat Bebas</option>
                                <option value="Obat Bebas Terbatas">Obat Bebas Terbatas</option>
                                <option value="Obat Keras">Obat Keras</option>
                                <option value="Alat Kesehatan">Alat Kesehatan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Sediaan *</label>
                            <input type="text" name="jenis_sediaan" x-model="editForm.jenis_sediaan" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Satuan Kemasan</label>
                            <input type="text" name="satuan_kemasan" x-model="editForm.satuan_kemasan" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Harga (Rp) *</label>
                            <input type="number" name="harga" min="100" step="100" x-model="editForm.harga" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Kedaluwarsa *</label>
                            <input type="date" name="expired" x-model="editForm.expired" required class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Batch</label>
                            <input type="text" name="batch_number" x-model="editForm.batch_number" class="w-full min-h-[42px] rounded-xl border border-slate-300 px-3 text-slate-900">
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 space-y-2">
                        <span class="block font-bold text-slate-800">Stok Aktual Per Cabang</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($clients as $branch)
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ $branch->nama_client }}</label>
                                    <input type="number" min="0"
                                        name="stok[{{ $branch->id_client }}]"
                                        x-model="editForm.stok['{{ $branch->id_client }}']"
                                        class="w-full min-h-[40px] rounded-xl border border-slate-300 bg-white px-3 text-xs text-slate-900">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="editModal = false" class="min-h-[44px] px-4 rounded-xl border border-slate-300 text-slate-700 font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-xs cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
