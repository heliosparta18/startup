<x-layouts.tenant title="Pemantauan & Manajemen Stok FEFO - OmniPharma">
    <div x-data="{
        addModal: false,
        editModal: false,
        penyesuaianBase: '{{ url('/cabang/' . $client . '/stok') }}',
        editBatch: {
            id: '',
            nama_obat: '',
            jumlah_stok: 0,
            batch_number: '',
            expired: ''
        },
        openEdit(batch) {
            this.editBatch = {
                id: batch.id,
                nama_obat: batch.nama_obat,
                jumlah_stok: Number(batch.jumlah_stok || 0),
                batch_number: batch.batch_number || '',
                expired: batch.expired ? String(batch.expired).slice(0, 10) : ''
            };
            this.editModal = true;
        }
    }" class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Dashboard Cabang</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Stok Batch FEFO</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="addModal = true" class="min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                    + Tambah / Atur Stok Cabang
                </button>
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    &larr; Dashboard Cabang
                </a>
                <a href="{{ route('tenant.antrean.index', ['client' => $client]) }}" class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 shadow-2xs transition">
                    Kelola Antrean &rarr;
                </a>
            </div>
        </div>

        <!-- Clean Light Header Banner -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Standar Permenkes RI No. 73/2016 | Rotasi Mutu FEFO
                </span>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-2 leading-tight">
                    Kartu Stok FEFO &amp; Manajemen Batch Cabang
                </h1>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs">
                    Rotasi Otomatis FEFO Aktif
                </span>
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

        @php
            $criticalCount = $fefoSummary['critical_count'] ?? $fefoBatches->filter(fn($b) => $b->is_critical_ed)->count();
            $nearCount = isset($fefoSummary['near_count'])
                ? max(0, (int) $fefoSummary['near_count'] - (int) $criticalCount)
                : $fefoBatches->filter(fn($b) => $b->is_near_ed && ! $b->is_critical_ed)->count();
        @endphp

        @if ($criticalCount > 0)
            <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 sm:p-5 text-rose-900 shadow-xs">
                <h2 class="text-sm font-black tracking-tight">PERINGATAN KRITIS: {{ number_format($criticalCount) }} Batch Mendekati/Melewati Kedaluwarsa (&le; 7 Hari)</h2>
                <p class="text-xs mt-1 text-rose-800">Gunakan tombol Karantina (0) pada batch yang sudah tidak layak edar.</p>
            </div>
        @endif

        @if ($nearCount > 0)
            <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:p-5 text-amber-900 shadow-xs">
                <h2 class="text-sm font-black tracking-tight">PERINGATAN DINI: {{ number_format($nearCount) }} Batch Waspada Kedaluwarsa (&le; 30 Hari)</h2>
                <p class="text-xs mt-1 text-amber-800">Prioritaskan pengeluaran batch di urutan teratas terlebih dahulu.</p>
            </div>
        @endif

        <!-- Search & Filter Bar -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                @php
                    $tabs = [
                        '' => 'Semua Batch',
                        'critical' => 'H-7 Kritis',
                        'near_ed' => 'H-30 Near ED',
                        'low' => 'Stok Menipis (<=10)',
                        'empty' => 'Stok Habis (0)',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('tenant.stok.fefo', array_filter(['client' => $client, 'filter' => $key, 'q' => $search])) }}"
                       class="inline-flex min-h-[44px] items-center px-3.5 py-2 rounded-xl transition {{ ($filter ?? '') === $key ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('tenant.stok.fefo', ['client' => $client]) }}" class="flex w-full sm:w-auto items-center gap-2 text-xs">
                @if(!empty($filter))
                    <input type="hidden" name="filter" value="{{ $filter }}">
                @endif
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari obat atau nomor batch..." class="min-h-[44px] flex-1 sm:flex-none rounded-xl border border-slate-300 bg-white px-3 text-slate-900 focus:border-emerald-600 focus:outline-none">
                <button type="submit" class="min-h-[44px] px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold cursor-pointer shrink-0">
                    Cari
                </button>
            </form>
        </div>

        <!-- Tabel Kartu Stok FEFO -->
        <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Daftar Batch Obat (Urutan FEFO Terdepan)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Disortir otomatis berdasarkan tanggal kedaluwarsa tercepat</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Total {{ number_format($fefoBatches->total()) }} Batch Terdata
                </span>
            </div>

            {{-- Mobile Card List --}}
            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($fefoBatches as $index => $batch)
                    @php
                        $expiredDate = $batch->obat?->expired ?? $batch->tanggal_kadaluarsa;
                        $sisaHari = $batch->sisa_hari_ed ?? 0;
                        $editPayload = [
                            'id' => $batch->id,
                            'nama_obat' => $batch->obat?->nama_obat ?? 'Sediaan Obat',
                            'jumlah_stok' => (int) $batch->jumlah_stok,
                            'batch_number' => $batch->obat?->batch_number ?? '',
                            'expired' => $expiredDate,
                        ];
                    @endphp
                    <div class="p-4 space-y-3 text-xs {{ $batch->is_critical_ed ? 'bg-rose-50/40' : ($batch->is_near_ed ? 'bg-amber-50/30' : '') }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    @php $fefoRank = ($fefoBatches->firstItem() ?? 1) + $index; @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $fefoRank === 1 ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        #{{ $fefoRank }} FEFO
                                    </span>
                                    <span class="font-mono font-bold text-slate-700 text-[11px]">{{ $batch->obat?->batch_number ?? 'BTC-'.substr((string) $batch->id, 0, 6) }}</span>
                                </div>
                                <div class="font-bold text-slate-900">{{ $batch->obat?->nama_obat ?? 'Sediaan Obat' }}</div>
                                <div class="text-[11px] text-slate-500 capitalize">{{ $batch->obat?->jenis_sediaan ?? 'tablet' }} | {{ $batch->obat?->merek ?? 'Generic' }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-black text-sm text-slate-900">{{ (int) $batch->jumlah_stok }} unit</div>
                                <div class="text-[11px] font-bold {{ $sisaHari < 0 || $batch->is_critical_ed ? 'text-rose-700' : ($batch->is_near_ed ? 'text-amber-700' : 'text-emerald-800') }}">
                                    {{ $expiredDate ? \Carbon\Carbon::parse($expiredDate)->format('d M Y') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                            <form method="POST" action="{{ route('tenant.stok.penyesuaian', ['client' => $client, 'id' => $batch->id]) }}">
                                @csrf
                                <input type="hidden" name="delta" value="-1">
                                <button type="submit" title="Kurangi 1 unit" class="min-h-[44px] min-w-[44px] px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 font-bold text-slate-700 cursor-pointer">-1</button>
                            </form>
                            <form method="POST" action="{{ route('tenant.stok.penyesuaian', ['client' => $client, 'id' => $batch->id]) }}">
                                @csrf
                                <input type="hidden" name="delta" value="1">
                                <button type="submit" title="Tambah 1 unit" class="min-h-[44px] min-w-[44px] px-3 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 font-bold text-emerald-800 cursor-pointer">+1</button>
                            </form>
                            <button type="button" @click='openEdit(@json($editPayload))' class="flex-1 min-h-[44px] px-3.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-slate-700 cursor-pointer">
                                Edit
                            </button>
                            @if((int) $batch->jumlah_stok > 0)
                                <form method="POST" action="{{ route('tenant.stok.penyesuaian', ['client' => $client, 'id' => $batch->id]) }}" onsubmit="return confirm('Karantina batch ini dengan mengosongkan stok menjadi 0?')">
                                    @csrf
                                    <input type="hidden" name="karantina" value="1">
                                    <button type="submit" class="min-h-[44px] px-3.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 font-bold text-rose-800 cursor-pointer">
                                        Karantina
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 px-4 text-center text-xs text-slate-500">Belum ada data batch stok obat yang cocok untuk filter ini.</div>
                @endforelse
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[58rem] text-left text-xs">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 text-center">Prioritas FEFO</th>
                            <th class="py-3.5 px-4">Nomor Batch</th>
                            <th class="py-3.5 px-4">Nama Obat &amp; Sediaan</th>
                            <th class="py-3.5 px-4">Tanggal Kedaluwarsa</th>
                            <th class="py-3.5 px-4 text-center">Sisa Waktu</th>
                            <th class="py-3.5 px-4 text-center">Status Mutu</th>
                            <th class="py-3.5 px-4 text-right">Stok Fisik</th>
                            <th class="py-3.5 px-4 text-right">Penyesuaian Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse ($fefoBatches as $index => $batch)
                            @php
                                $expiredDate = $batch->obat?->expired ?? $batch->tanggal_kadaluarsa;
                                $sisaHari = $batch->sisa_hari_ed ?? 0;
                                $editPayload = [
                                    'id' => $batch->id,
                                    'nama_obat' => $batch->obat?->nama_obat ?? 'Sediaan Obat',
                                    'jumlah_stok' => (int) $batch->jumlah_stok,
                                    'batch_number' => $batch->obat?->batch_number ?? '',
                                    'expired' => $expiredDate,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition {{ $batch->is_critical_ed ? 'bg-rose-50/40' : ($batch->is_near_ed ? 'bg-amber-50/30' : '') }}">
                                <td class="py-3.5 px-4 text-center">
                                    @php $fefoRank = ($fefoBatches->firstItem() ?? 1) + $index; @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $fefoRank === 1 ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        #{{ $fefoRank }} FEFO
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    {{ $batch->obat?->batch_number ?? 'BTC-'.substr((string) $batch->id, 0, 6) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $batch->obat?->nama_obat ?? 'Sediaan Obat' }}</div>
                                    <div class="text-[11px] text-slate-500 capitalize">{{ $batch->obat?->jenis_sediaan ?? 'tablet' }} | {{ $batch->obat?->merek ?? 'Generic' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $expiredDate ? \Carbon\Carbon::parse($expiredDate)->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    @if ($sisaHari < 0)
                                        <span class="text-rose-700">Expired (Lewat {{ abs($sisaHari) }} hari)</span>
                                    @elseif ($sisaHari <= 7)
                                        <span class="text-rose-700">H-{{ $sisaHari }} hari</span>
                                    @elseif ($sisaHari <= 30)
                                        <span class="text-amber-700">H-{{ $sisaHari }} hari</span>
                                    @else
                                        <span class="text-emerald-800 font-extrabold">&gt; 30 hari</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($sisaHari < 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white">KEDALUWARSA</span>
                                    @elseif ($batch->is_critical_ed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Kritis (H-7)</span>
                                    @elseif ($batch->is_near_ed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Waspada (H-30)</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-900 border border-emerald-200">Aman</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-slate-900">
                                    {{ (int) $batch->jumlah_stok }} unit
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <form method="POST" action="{{ route('tenant.stok.penyesuaian', ['client' => $client, 'id' => $batch->id]) }}">
                                            @csrf
                                            <input type="hidden" name="delta" value="-1">
                                            <button type="submit" title="Kurangi 1 unit" class="min-h-[44px] min-w-[44px] px-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 font-bold text-slate-700 cursor-pointer">-1</button>
                                        </form>
                                        <form method="POST" action="{{ route('tenant.stok.penyesuaian', ['client' => $client, 'id' => $batch->id]) }}">
                                            @csrf
                                            <input type="hidden" name="delta" value="1">
                                            <button type="submit" title="Tambah 1 unit" class="min-h-[44px] min-w-[44px] px-2.5 rounded-lg border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 font-bold text-emerald-800 cursor-pointer">+1</button>
                                        </form>
                                        <button type="button" @click='openEdit(@json($editPayload))' class="min-h-[44px] px-3 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 font-bold text-slate-700 cursor-pointer">
                                            Edit
                                        </button>
                                        @if((int) $batch->jumlah_stok > 0)
                                            <form method="POST" action="{{ route('tenant.stok.penyesuaian', ['client' => $client, 'id' => $batch->id]) }}" onsubmit="return confirm('Karantina batch ini dengan mengosongkan stok menjadi 0?')">
                                                @csrf
                                                <input type="hidden" name="karantina" value="1">
                                                <button type="submit" class="min-h-[44px] px-3 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 font-bold text-rose-800 cursor-pointer">
                                                    Karantina
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data batch stok obat yang cocok untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($fefoBatches->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $fefoBatches->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Tambah / Atur Stok Obat Cabang -->
        <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4 backdrop-blur-xs">
            <div @click.away="addModal = false" class="omni-bottom-sheet-panel w-full max-w-md max-h-[90dvh] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white p-5 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-1" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">Tambah / Atur Stok Obat Cabang</h3>
                    <button type="button" @click="addModal = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-500 hover:text-slate-900 rounded-xl cursor-pointer">&times;</button>
                </div>
                <form method="POST" action="{{ route('tenant.stok.store', ['client' => $client]) }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Obat Master *</label>
                        <select name="id_obat" required class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                            @foreach($allObats ?? [] as $ob)
                                <option value="{{ $ob->id_obat }}">{{ $ob->nama_obat }} ({{ $ob->merek ?? 'Generic' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jumlah Stok Cabang *</label>
                        <input type="number" name="jumlah_stok" min="0" max="10000" required value="20" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3 text-slate-900">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Batch (Opsional)</label>
                        <input type="text" name="batch_number" placeholder="BTC-2026-NEW" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3 text-slate-900">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kedaluwarsa Baru (Opsional)</label>
                        <input type="date" name="expired" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3 text-slate-900">
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="addModal = false" class="min-h-[44px] px-4 rounded-xl border border-slate-300 text-slate-700 font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold cursor-pointer">Simpan Stok Cabang</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Batch Stok -->
        <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4 backdrop-blur-xs">
            <div @click.away="editModal = false" class="omni-bottom-sheet-panel w-full max-w-md max-h-[90dvh] overflow-y-auto rounded-t-[20px] sm:rounded-3xl bg-white p-5 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                <div class="sm:hidden mx-auto w-10 h-1 rounded-full bg-slate-300 -mt-1 mb-1" aria-hidden="true"></div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">Penyesuaian Stok: <span x-text="editBatch.nama_obat"></span></h3>
                    <button type="button" @click="editModal = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-500 hover:text-slate-900 rounded-xl cursor-pointer">&times;</button>
                </div>
                <form method="POST" :action="penyesuaianBase + '/' + editBatch.id + '/penyesuaian'" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Stok Fisik Aktual (Unit) *</label>
                        <input type="number" name="jumlah_stok" min="0" max="10000" required x-model="editBatch.jumlah_stok" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3 text-slate-900">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Batch</label>
                        <input type="text" name="batch_number" x-model="editBatch.batch_number" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3 text-slate-900">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kedaluwarsa</label>
                        <input type="date" name="expired" x-model="editBatch.expired" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3 text-slate-900">
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="editModal = false" class="min-h-[44px] px-4 rounded-xl border border-slate-300 text-slate-700 font-bold cursor-pointer">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold cursor-pointer">Perbarui Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.tenant>
