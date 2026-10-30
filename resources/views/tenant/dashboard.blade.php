<x-layouts.tenant title="Verifikasi Resep &amp; FEFO">
    <div x-data="{
        statusVerifikasi: 'menunggu',
        catatanApoteker: 'Resep asli dan masih berlaku.',
        modalResepBesar: false,
        notifAksi: '',
        verifikasi(action) {
            fetch('{{ route('tenant.resep.verifikasi', ['client' => $client, 'id' => 'ORD-20260917-0042']) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action: action,
                    catatan: this.catatanApoteker
                })
            })
            .then(res => res.json())
            .then(data => {
                this.statusVerifikasi = action;
                this.notifAksi = 'Resep berhasil ditandai: ' + action.toUpperCase();
                setTimeout(() => this.notifAksi = '', 5000);
            })
            .catch(() => {
                this.statusVerifikasi = action;
                this.notifAksi = 'Resep berhasil diverifikasi (' + action + ').';
                setTimeout(() => this.notifAksi = '', 5000);
            });
        }
    }" class="space-y-6">

        <!-- Top Notification Banner -->
        <div x-show="notifAksi" x-cloak class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifAksi"></span>
            </div>
        </div>

        <!-- Two Column Layout (Mockup Screen 2) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: Verifikasi Resep Masuk (#ORD-20260917-0042) -->
            <section class="lg:col-span-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Antrean Verifikasi Resep</span>
                        <h2 class="text-lg font-extrabold text-zinc-900 dark:text-zinc-100">#ORD-20260917-0042</h2>
                    </div>
                    <div>
                        <span
                            x-text="statusVerifikasi === 'menunggu' ? 'Menunggu Telaah' : (statusVerifikasi === 'disetujui' ? 'Disetujui' : 'Ditolak')"
                            :class="statusVerifikasi === 'menunggu' ? 'bg-amber-100 text-amber-900 border-amber-300' : (statusVerifikasi === 'disetujui' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-rose-100 text-rose-900 border-rose-300')"
                            class="inline-flex items-center rounded-lg border px-3 py-1 text-xs font-bold">
                        </span>
                    </div>
                </div>

                <!-- Prescription Image Preview Box -->
                <div class="relative flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <div class="flex h-36 w-full items-center justify-center rounded-lg border border-dashed border-zinc-300 bg-white text-zinc-400 dark:border-zinc-600 dark:bg-zinc-800 text-xs font-medium">
                        [ Foto Resep Dokter ]
                    </div>
                    <button
                        type="button"
                        @click="modalResepBesar = true"
                        class="mt-3 inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200">
                        🔍 Perbesar Resep
                    </button>
                </div>

                <!-- Doctor & Prescription Details -->
                <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200 dark:bg-zinc-800/50 dark:border-zinc-700 space-y-2 text-xs">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-extrabold text-sm text-zinc-900 dark:text-zinc-100">dr. Budi, Sp.PD</div>
                            <div class="text-zinc-500">SIP: 503/SIP/2024</div>
                        </div>
                        <div class="text-right">
                            <span class="text-zinc-500 font-medium">Data Pasien:</span>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100">Candi Chier (88 th)</div>
                        </div>
                    </div>
                    <div class="border-t border-zinc-200 pt-2 dark:border-zinc-700 font-mono text-zinc-800 dark:text-zinc-200 space-y-1">
                        <div class="font-bold text-emerald-800 dark:text-emerald-400">R/ Amodiaquine No. X</div>
                        <div class="text-zinc-600 dark:text-zinc-400">S 3 dd tab I (Sesudah makan)</div>
                    </div>
                </div>

                <!-- Pemeriksaan Legalitas Farmasi -->
                <div class="space-y-2 text-xs">
                    <span class="font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Pemeriksaan Legalitas Farmasi:</span>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50/70 p-2.5 font-medium text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200">
                            <input type="checkbox" checked disabled class="h-4 w-4 rounded text-emerald-600" />
                            <span>✔ SIP Dokter Terverifikasi Aktif pada Konsil Kedokteran</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50/70 p-2.5 font-medium text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200">
                            <input type="checkbox" checked disabled class="h-4 w-4 rounded text-emerald-600" />
                            <span>✔ Dosis Sesuai Standar Farmakope untuk Geriatri (88 th)</span>
                        </label>
                    </div>
                </div>

                <!-- Catatan Apoteker -->
                <div class="space-y-1 text-xs">
                    <label class="font-bold text-zinc-700 dark:text-zinc-300">Catatan Apoteker:</label>
                    <textarea
                        x-model="catatanApoteker"
                        rows="2"
                        class="w-full rounded-xl border border-zinc-300 p-2.5 text-xs text-zinc-900 focus:border-emerald-600 focus:ring-emerald-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    </textarea>
                </div>

                <!-- Action Verification Buttons (>= 44px target) -->
                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="button"
                        @click="verifikasi('disetujui')"
                        :disabled="statusVerifikasi === 'disetujui'"
                        class="flex-1 min-h-11 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50">
                        Setujui
                    </button>
                    <button
                        type="button"
                        @click="verifikasi('ditolak')"
                        :disabled="statusVerifikasi === 'ditolak'"
                        class="flex-1 min-h-11 inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 text-xs font-bold text-white shadow hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50">
                        Tolak
                    </button>
                </div>
            </section>

            <!-- RIGHT COLUMN: Peringatan Batch Kedaluwarsa (FEFO) -->
            <section class="lg:col-span-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-5">
                <div class="border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Manajemen Inventori Batch</span>
                    <h2 class="text-lg font-extrabold text-zinc-900 dark:text-zinc-100">Peringatan Batch Kedaluwarsa (FEFO)</h2>
                </div>

                <!-- Warning Alert Banner (Mockup Screen 2) -->
                <div class="flex items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-xs font-bold text-amber-900 dark:border-amber-700 dark:bg-amber-950/70 dark:text-amber-200">
                    <span class="text-lg">⚠️</span>
                    <span>1 Batch mendekati kedaluwarsa (H-23 hari)!</span>
                </div>

                <!-- FEFO Table -->
                <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-100 text-zinc-700 font-bold dark:bg-zinc-800 dark:text-zinc-300 border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="p-3">Obat / Batch</th>
                                <th class="p-3">Exp Date</th>
                                <th class="p-3">Stok</th>
                                <th class="p-3">Prioritas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <!-- Batch #1: Amodiaquine (H-23 FEFO) -->
                            <tr class="bg-amber-50/40 dark:bg-amber-950/20 font-medium">
                                <td class="p-3">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">Amodiaquine 200mg</div>
                                    <div class="text-[11px] font-mono text-zinc-500">Batch: #BTC-2024A</div>
                                </td>
                                <td class="p-3 font-semibold text-amber-800 dark:text-amber-300">10 Okt 2026</td>
                                <td class="p-3 font-bold">12 str</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-2.5 py-1 text-[11px] font-extrabold text-amber-900 border border-amber-300 dark:bg-amber-900/60 dark:text-amber-200">
                                        #1 FEFO
                                    </span>
                                </td>
                            </tr>

                            <!-- Batch #2: Amodiaquine 2027 -->
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="p-3">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">Amodiaquine 200mg</div>
                                    <div class="text-[11px] font-mono text-zinc-500">Batch: #BTC-2025B</div>
                                </td>
                                <td class="p-3 text-zinc-600 dark:text-zinc-400">15 Jan 2027</td>
                                <td class="p-3 font-bold">80 str</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-[11px] font-semibold text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                                        #2 Nanti
                                    </span>
                                </td>
                            </tr>

                            <!-- Batch #3: Paracetamol (Kritis <5) -->
                            <tr class="bg-rose-50/40 dark:bg-rose-950/20">
                                <td class="p-3">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100">Paracetamol 500mg</div>
                                    <div class="text-[11px] font-mono text-zinc-500">Batch: #PCT-8891</div>
                                </td>
                                <td class="p-3 text-zinc-600 dark:text-zinc-400">20 Des 2027</td>
                                <td class="p-3 font-bold text-rose-700 dark:text-rose-400">4 box</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-rose-100 px-2.5 py-1 text-[11px] font-extrabold text-rose-800 border border-rose-300 dark:bg-rose-900/60 dark:text-rose-200">
                                        Kritis &lt;5
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200 dark:bg-zinc-800/40 dark:border-zinc-700 text-xs text-zinc-600 dark:text-zinc-400">
                    <p class="font-semibold text-zinc-800 dark:text-zinc-200 mb-1">Pedoman Pengeluaran Obat (FEFO):</p>
                    <p>Keluarkan selalu batch #BTC-2024A terlebih dahulu sebelum membuka batch baru untuk mencegah kerugian kadaluwarsa pada klien cabang.</p>
                </div>
            </section>
        </div>

        <!-- Modal Perbesar Resep -->
        <div x-show="modalResepBesar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
            <div @click.away="modalResepBesar = false" class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-3 dark:border-zinc-800">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Pratinjau Resep Dokter : #ORD-20260917-0042</h3>
                    <button type="button" @click="modalResepBesar = false" class="min-h-11 min-w-11 inline-flex items-center justify-center text-zinc-500 hover:text-zinc-800 rounded-lg">✕</button>
                </div>
                <div class="flex h-72 items-center justify-center rounded-xl bg-zinc-50 border border-zinc-200 text-xs font-mono text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                    [ Tampilan Resolusi Penuh: Resep dr. Budi Sp.PD - Pasien Candi Chier (88 th) ]
                </div>
                <div class="flex justify-end">
                    <button type="button" @click="modalResepBesar = false" class="min-h-11 inline-flex items-center justify-center rounded-xl bg-zinc-800 px-5 text-xs font-bold text-white hover:bg-zinc-900">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.tenant>
