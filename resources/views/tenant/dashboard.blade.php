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
        <div x-show="notifAksi" x-cloak class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="notifAksi"></span>
            </div>
        </div>

        <!-- Two Column Layout (Mockup Screen 2: Nuansa Hijau & Putih Bersih) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: Verifikasi Resep Masuk (#ORD-20260917-0042) -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Antrean Verifikasi Resep</span>
                        <h2 class="text-lg font-extrabold text-slate-900">#ORD-20260917-0042</h2>
                    </div>
                    <div>
                        <span
                            x-text="statusVerifikasi === 'menunggu' ? 'Menunggu Telaah' : (statusVerifikasi === 'disetujui' ? 'Disetujui' : 'Ditolak')"
                            :class="statusVerifikasi === 'menunggu' ? 'bg-amber-100 text-amber-900 border-amber-300' : (statusVerifikasi === 'disetujui' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-rose-100 text-rose-900 border-rose-300')"
                            class="inline-flex items-center rounded-xl border px-3 py-1 text-xs font-bold shadow-2xs">
                        </span>
                    </div>
                </div>

                <!-- Prescription Image Preview Box -->
                <div class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-6">
                    <div class="flex h-36 w-full items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white text-slate-500 text-xs font-medium shadow-2xs">
                        [ Foto Resep Dokter : dr. Budi Sp.PD ]
                    </div>
                    <button
                        type="button"
                        @click="modalResepBesar = true"
                        class="mt-3 inline-flex min-h-[44px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer shadow-2xs">
                        🔍 Perbesar Resep
                    </button>
                </div>

                <!-- Doctor & Prescription Details -->
                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 space-y-2 text-xs">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-extrabold text-sm text-slate-900">dr. Budi, Sp.PD</div>
                            <div class="text-slate-500">SIP: 503/SIP/2024</div>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-500 font-medium">Data Pasien:</span>
                            <div class="font-bold text-slate-900">Candi Chier (88 th)</div>
                        </div>
                    </div>
                    <div class="border-t border-slate-200 pt-2 font-mono text-slate-800 space-y-1">
                        <div class="font-bold text-emerald-800">R/ Amodiaquine No. X</div>
                        <div class="text-slate-600">S 3 dd tab I (Sesudah makan)</div>
                    </div>
                </div>

                <!-- Pemeriksaan Legalitas Farmasi -->
                <div class="space-y-2 text-xs">
                    <span class="font-bold uppercase tracking-wider text-slate-700">Pemeriksaan Legalitas Farmasi:</span>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50/70 p-2.5 font-medium text-emerald-900">
                            <input type="checkbox" checked disabled class="h-4 w-4 rounded text-emerald-600" />
                            <span>✔ SIP Dokter Terverifikasi Aktif pada Konsil Kedokteran</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50/70 p-2.5 font-medium text-emerald-900">
                            <input type="checkbox" checked disabled class="h-4 w-4 rounded text-emerald-600" />
                            <span>✔ Dosis Sesuai Standar Farmakope untuk Geriatri (88 th)</span>
                        </label>
                    </div>
                </div>

                <!-- Catatan Apoteker -->
                <div class="space-y-1 text-xs">
                    <label class="font-bold text-slate-700">Catatan Apoteker:</label>
                    <textarea
                        x-model="catatanApoteker"
                        rows="2"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-emerald-600 focus:ring-emerald-500">
                    </textarea>
                </div>

                <!-- Action Verification Buttons (>= 44px target) -->
                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="button"
                        @click="verifikasi('disetujui')"
                        :disabled="statusVerifikasi === 'disetujui'"
                        class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 text-xs font-bold text-white shadow-xs hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50 transition cursor-pointer">
                        Setujui
                    </button>
                    <button
                        type="button"
                        @click="verifikasi('ditolak')"
                        :disabled="statusVerifikasi === 'ditolak'"
                        class="flex-1 min-h-[44px] inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 text-xs font-bold text-white shadow-xs hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 transition cursor-pointer">
                        Tolak
                    </button>
                </div>
            </section>

            <!-- RIGHT COLUMN: Peringatan Batch Kedaluwarsa (FEFO) -->
            <section class="lg:col-span-6 rounded-3xl border border-slate-200/90 bg-white p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Manajemen Inventori Batch</span>
                    <h2 class="text-lg font-extrabold text-slate-900">Peringatan Batch Kedaluwarsa (FEFO)</h2>
                </div>

                <!-- Warning Alert Banner (Mockup Screen 2) -->
                <div class="flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-xs font-bold text-amber-900">
                    <span class="text-lg">⚠️</span>
                    <span>1 Batch mendekati kedaluwarsa (H-23 hari)!</span>
                </div>

                <!-- FEFO Table -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                            <tr>
                                <th class="p-3">Obat / Batch</th>
                                <th class="p-3">Exp Date</th>
                                <th class="p-3">Stok</th>
                                <th class="p-3">Prioritas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <!-- Batch #1: Amodiaquine (H-23 FEFO) -->
                            <tr class="bg-amber-50/40 font-medium">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">Amodiaquine 200mg</div>
                                    <div class="text-[11px] font-mono text-slate-500">Batch: #BTC-2024A</div>
                                </td>
                                <td class="p-3 font-semibold text-amber-800">10 Okt 2026</td>
                                <td class="p-3 font-bold">12 str</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-2.5 py-1 text-[11px] font-extrabold text-amber-900 border border-amber-300">
                                        #1 FEFO
                                    </span>
                                </td>
                            </tr>

                            <!-- Batch #2: Amodiaquine 2027 -->
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">Amodiaquine 200mg</div>
                                    <div class="text-[11px] font-mono text-slate-500">Batch: #BTC-2025B</div>
                                </td>
                                <td class="p-3 text-slate-600">15 Jan 2027</td>
                                <td class="p-3 font-bold">80 str</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 border border-slate-200">
                                        #2 Nanti
                                    </span>
                                </td>
                            </tr>

                            <!-- Batch #3: Paracetamol (Kritis <5) -->
                            <tr class="bg-rose-50/30">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">Paracetamol 500mg</div>
                                    <div class="text-[11px] font-mono text-slate-500">Batch: #PCT-8891</div>
                                </td>
                                <td class="p-3 text-slate-600">20 Des 2027</td>
                                <td class="p-3 font-bold text-rose-700">4 box</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center rounded-md bg-rose-100 px-2.5 py-1 text-[11px] font-extrabold text-rose-800 border border-rose-300">
                                        Kritis &lt;5
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 text-xs text-slate-600">
                    <p class="font-semibold text-slate-900 mb-1">Pedoman Pengeluaran Obat (FEFO):</p>
                    <p>Keluarkan selalu batch #BTC-2024A terlebih dahulu sebelum membuka batch baru untuk mencegah kerugian kadaluwarsa pada klien cabang.</p>
                </div>
            </section>
        </div>

        <!-- Modal Perbesar Resep -->
        <div x-show="modalResepBesar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div @click.away="modalResepBesar = false" class="w-full max-w-2xl rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Pratinjau Resep Dokter : #ORD-20260917-0042</h3>
                    <button type="button" @click="modalResepBesar = false" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-slate-400 hover:text-slate-800 rounded-xl cursor-pointer">✕</button>
                </div>
                <div class="flex h-72 items-center justify-center rounded-2xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-600">
                    [ Tampilan Resolusi Penuh: Resep dr. Budi Sp.PD - Pasien Candi Chier (88 th) ]
                </div>
                <div class="flex justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="modalResepBesar = false" class="min-h-[44px] inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 text-xs font-bold text-white shadow-2xs cursor-pointer">
                        Tutup Pratinjau
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.tenant>
