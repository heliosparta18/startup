<x-layouts.tenant :title="'Manajemen Antrean & Progres Status - OmniPharma'">
    <div x-data="{
        modalWalkin: false,
        modalManualAntrean: false,
        nomorManual: '{{ $antreanBerjalan }}',
        notifSukses: '{{ session('success') ?? '' }}',
        notifError: '{{ session('error') ?? '' }}',
        loadingId: null,
        updateStatus(orderId, status) {
            this.loadingId = orderId;
            fetch('{{ url('/cabang/'.$client.'/pesanan') }}/' + orderId + '/progres-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(res => res.json())
            .then(data => {
                this.loadingId = null;
                if (data.success) {
                    this.notifSukses = data.message;
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    this.notifError = data.message || 'Gagal mengubah status.';
                }
            })
            .catch(err => {
                this.loadingId = null;
                window.location.reload();
            });
        }
    }" class="space-y-6">

        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="hover:text-emerald-800 font-medium transition">Dashboard Cabang</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Antrean &amp; Progresi Status</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="modalWalkin = true"
                    class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <span>➕ Tambah Antrean Walk-In</span>
                </button>
                <a href="{{ route('tenant.dashboard', ['client' => $client]) }}" class="inline-flex min-h-[44px] items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    &larr; Dashboard Cabang
                </a>
            </div>
        </div>

        <!-- Hero Banner: Nuansa Hijau Deep Spruce Sesuai Menu Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                        Standar Permenkes RI No. 73/2016 • Antrean &amp; Progresi Layanan
                    </span>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Antrean Virtual &amp; Tombol Progresi Status
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-2xl leading-relaxed">
                        Kelola antrean berjalan, slot jam pengambilan pasien (<em>Click &amp; Collect</em>), dan perbarui siklus penyiapan obat ke pelanggan secara real-time.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-3">
                    <button
                        type="button"
                        @click="modalManualAntrean = true"
                        class="inline-flex min-h-[44px] items-center gap-2 px-4 py-2.5 rounded-xl bg-[#D4F94E] hover:bg-[#c2e83b] text-[#0A231E] font-black text-xs shadow-xs transition cursor-pointer">
                        <span>Antrean Aktif: <strong class="text-sm" x-text="nomorManual"></strong></span>
                        <span>✏️</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div x-show="notifSukses" x-cloak class="rounded-2xl border border-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 p-4 text-xs font-bold text-emerald-900 dark:text-emerald-200 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifSukses"></span>
            </div>
            <button @click="notifSukses = ''" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
        </div>

        <div x-show="notifError" x-cloak class="rounded-2xl border border-rose-300 bg-rose-50 dark:bg-rose-950/50 p-4 text-xs font-bold text-rose-900 dark:text-rose-200 shadow-xs flex items-center justify-between">
            <span x-text="notifError"></span>
            <button @click="notifError = ''" class="text-rose-700 hover:text-rose-900 font-bold">&times;</button>
        </div>

        <!-- Panel Kontrol Antrean Berjalan (Apoteker Control Panel) -->
        <div class="rounded-3xl border border-slate-200/90 bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Left: Info Antrean Berjalan Saat Ini -->
                <div class="flex items-center gap-4 sm:gap-6">
                    <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-3xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] flex flex-col items-center justify-center border-2 border-emerald-500/30 shadow-md shrink-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Sedang Dipanggil</span>
                        <span class="text-2xl sm:text-3xl font-black tracking-tight">{{ $antreanBerjalan }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                Loket Penyerahan Aktif
                            </span>
                            <span class="text-xs text-slate-400">Cabang {{ ucwords(str_replace('-', ' ', $client)) }}</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-zinc-100 mt-1">
                            Antrean Farmasi Berjalan
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Pasien dengan nomor antrean ini sedang dilayani di loket pengambilan obat.
                        </p>
                    </div>
                </div>

                <!-- Right: Action Buttons to advance queue -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <form action="{{ route('tenant.antrean.update', ['client' => $client]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="panggil_berikutnya">
                        <button
                            type="submit"
                            class="min-h-[44px] inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                            <span>📢 Panggil Berikutnya (+1)</span>
                        </button>
                    </form>

                    <button
                        type="button"
                        @click="modalManualAntrean = true"
                        class="min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-slate-50 text-slate-700 dark:text-zinc-200 font-bold text-xs shadow-2xs transition cursor-pointer">
                        <span>⚙️ Atur Nomor Antrean</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabel Antrean Pesanan & Progresi Status (Role Apoteker Control) -->
        <div class="rounded-3xl border border-slate-200/90 bg-white dark:bg-zinc-900 p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-zinc-800 pb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-zinc-100">
                        Daftar Antrean &amp; Kontrol Progresi Status Pesanan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Apoteker dapat memajukan siklus penyiapan obat dari status diproses, siap diambil, hingga selesai.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Total: {{ $pesanans->count() }} Pesanan</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-zinc-800">
                <table class="w-full text-left text-xs min-w-[50rem]">
                    <thead class="bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 font-extrabold uppercase border-b border-slate-200 dark:border-zinc-700">
                        <tr>
                            <th class="p-3.5">Antrean &amp; Pesanan</th>
                            <th class="p-3.5">Pasien</th>
                            <th class="p-3.5">Slot Jam Ambil</th>
                            <th class="p-3.5">Obat yang Disiapkan</th>
                            <th class="p-3.5">Status Progres</th>
                            <th class="p-3.5 text-right">Tombol Progresi Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-zinc-800 font-medium">
                        @forelse($pesanans as $order)
                            @php
                                $status = $order->status ?? 'menunggu_pembayaran';
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/50 transition">
                                <td class="p-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl font-black text-xs {{ $order->nomor_antrean === $antreanBerjalan ? 'bg-[#D4F94E] text-[#0A231E]' : 'bg-slate-200 dark:bg-zinc-700 text-slate-800 dark:text-zinc-200' }}">
                                            {{ $order->nomor_antrean ?? 'A-'.substr($order->nomor_pesanan, -3) }}
                                        </span>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-zinc-100">{{ $order->nomor_pesanan }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $order->created_at ? $order->created_at->format('H:i') : '-' }} WIB</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900 dark:text-zinc-100">{{ $order->pengguna->nama_lengkap ?? 'Pasien Umum' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $order->pengguna->telepon_email ?? '-' }}</div>
                                </td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-zinc-300">
                                        <span>⏱️</span>
                                        <span>{{ $order->slot_waktu ?? '15 Menit Lagi' }}</span>
                                    </span>
                                </td>
                                <td class="p-3.5 max-w-[240px]">
                                    <div class="space-y-1">
                                        @foreach($order->items->take(2) as $item)
                                            <div class="text-xs text-slate-800 dark:text-zinc-200 truncate font-semibold">
                                                • {{ $item->nama_obat }} ({{ $item->kuantitas }}x)
                                            </div>
                                        @endforeach
                                        @if($order->items->count() > 2)
                                            <div class="text-[10px] text-emerald-700 font-bold">
                                                +{{ $order->items->count() - 2 }} obat lainnya
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    @if($status === 'diproses')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Diproses Apotek
                                        </span>
                                    @elseif($status === 'siap_diambil')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-950 border border-emerald-300">
                                            <span>📦</span>
                                            Siap Diambil
                                        </span>
                                    @elseif($status === 'selesai')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-[#0A231E] text-[#D4F94E] border border-emerald-500/20">
                                            <span>✓</span>
                                            Selesai
                                        </span>
                                    @elseif($status === 'menunggu_pembayaran')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-400">
                                            Menunggu Bayar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                            {{ str_replace('_', ' ', $status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right">
                                    <div class="inline-flex items-center justify-end gap-2 flex-wrap">
                                        @if($status === 'menunggu_pembayaran')
                                            <button
                                                type="button"
                                                @click="updateStatus('{{ $order->id_pesanan }}', 'diproses')"
                                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                                class="min-h-[44px] px-3.5 py-2 rounded-xl border border-emerald-600/50 bg-emerald-50 text-emerald-900 hover:bg-emerald-100 hover:border-emerald-600 font-bold text-xs shadow-2xs transition cursor-pointer disabled:opacity-50">
                                                Mulai Proses
                                            </button>
                                        @elseif($status === 'diproses')
                                            <button
                                                type="button"
                                                @click="updateStatus('{{ $order->id_pesanan }}', 'siap_diambil')"
                                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                                class="min-h-[44px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer disabled:opacity-50">
                                                Tandai Siap Ambil
                                            </button>
                                        @elseif($status === 'siap_diambil')
                                            <button
                                                type="button"
                                                @click="updateStatus('{{ $order->id_pesanan }}', 'selesai')"
                                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                                class="min-h-[44px] px-4 py-2 rounded-xl bg-[#0A231E] hover:bg-[#0E352E] text-[#D4F94E] font-bold text-xs border border-emerald-500/30 shadow-xs transition cursor-pointer disabled:opacity-50">
                                                Selesaikan Penyerahan
                                            </button>
                                        @elseif($status === 'selesai')
                                            <span class="text-xs text-emerald-800 font-extrabold pr-2">✓ Transaksi Tuntas</span>
                                        @endif

                                        @if($status !== 'selesai' && $status !== 'dibatalkan')
                                            <button
                                                type="button"
                                                @click="if(confirm('Batalkan pesanan ini?')) updateStatus('{{ $order->id_pesanan }}', 'dibatalkan')"
                                                :disabled="loadingId === '{{ $order->id_pesanan }}'"
                                                class="min-h-[44px] px-3 py-2 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 text-xs font-bold transition cursor-pointer disabled:opacity-50"
                                                title="Batalkan Pesanan">
                                                ✕
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Belum ada transaksi atau antrean masuk di cabang ini hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL 1: Tambah Antrean Walk-In (Pasien Datang Langsung) -->
        <div x-show="modalWalkin" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="modalWalkin = false" class="w-full max-w-md rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-zinc-100">Tambah Antrean Walk-In</h3>
                        <p class="text-xs text-slate-500">Penerbitan tiket antrean pasien datang langsung di apotek</p>
                    </div>
                    <button @click="modalWalkin = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('tenant.antrean.walkin', ['client' => $client]) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Nama Pasien / Tamu</label>
                        <input type="text" name="nama_pasien" required placeholder="Contoh: Ny. Sri Wahyuni" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Catatan Obat / Keluhan</label>
                        <textarea name="catatan" rows="2" placeholder="Contoh: Pembelian obat batuk sirup langsung di kasir" class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" @click="modalWalkin = false" class="min-h-[44px] px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs">Cetak &amp; Terbitkan Tiket</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: Atur Nomor Antrean Berjalan Manual -->
        <div x-show="modalManualAntrean" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.outside="modalManualAntrean = false" class="w-full max-w-sm rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-zinc-100">Atur Nomor Antrean Berjalan</h3>
                    <button @click="modalManualAntrean = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('tenant.antrean.update', ['client' => $client]) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="set_nomor">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-zinc-300 mb-1">Nomor Antrean Aktif</label>
                        <input type="text" name="nomor_antrean" value="{{ $antreanBerjalan }}" placeholder="Contoh: A-005" required class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 text-base font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none text-center">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-zinc-800">
                        <button type="button" @click="modalManualAntrean = false" class="min-h-[44px] px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Batal</button>
                        <button type="submit" class="min-h-[44px] px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs">Simpan Nomor</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.tenant>
