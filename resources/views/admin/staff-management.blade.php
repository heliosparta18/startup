<x-layouts.admin title="Manajemen Staf & Pengawasan APJ Cabang">
    <div class="space-y-6">
        <!-- Breadcrumb & Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-zinc-500 flex-wrap">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition py-1">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-zinc-900 dark:text-zinc-100 font-semibold py-1">Manajemen Staf Cabang</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 text-xs font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-800 active:scale-[0.98] transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Header Cabang -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Administrasi Operasional Cabang</div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight">{{ $clientModel->nama_client }}</h2>
                    <p class="text-xs text-zinc-500 mt-1 break-words">Alamat: {{ $clientModel->alamat ?? 'Wilayah Operasional Banyuwangi' }} • Kode Cabang: <code class="font-mono text-zinc-700 dark:text-zinc-300">{{ $clientModel->slug ?? $clientModel->id_client }}</code></p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        Cabang Aktif
                    </span>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-semibold text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="break-words">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-xs font-semibold text-rose-900 dark:border-rose-800 dark:bg-rose-950 dark:text-rose-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="break-words">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Grid 2 Form Aksi: Penugasan Peran & Pemetaan APJ (5NF) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Form 1: Beri Peran Staf Cabang -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
                <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="h-8 w-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-zinc-700 dark:text-zinc-200 text-sm shrink-0">
                        1
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Penugasan Peran Staf Cabang</h3>
                        <p class="text-[11px] text-zinc-500">Tabel entitas: <code>peran_client_pengguna</code></p>
                    </div>
                </div>

                <form action="{{ route('admin.cabang.staf.role', ['client' => $client]) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="id_pengguna" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Pilih Pengguna</label>
                        <select name="id_pengguna" id="id_pengguna" required class="w-full min-h-[44px] rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Akun Terdaftar --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id_pengguna }}">
                                    {{ $user->nama_lengkap }} ({{ $user->telepon_email ?? $user->email ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="peran" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Peran di Cabang Ini</label>
                        <select name="peran" id="peran" required class="w-full min-h-[44px] rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="apoteker">Apoteker (Apoteker Penanggung Jawab / APJ)</option>
                            <option value="asisten_apoteker">Asisten Apoteker (Tenaga Teknis Kefarmasian)</option>
                            <option value="kasir">Kasir Pelaksana</option>
                            <option value="staf_klien">Staf Administrasi Cabang</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full min-h-[44px] inline-flex items-center justify-center rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-xs font-bold hover:bg-zinc-800 dark:hover:bg-zinc-200 active:scale-[0.98] transition">
                        Simpan Penugasan Peran
                    </button>
                </form>
            </div>

            <!-- Form 2: Hubungan Supervisi APJ 5NF -->
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
                <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="h-8 w-8 rounded-lg bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center font-bold text-emerald-800 dark:text-emerald-300 text-sm shrink-0">
                        2
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Hierarki Pengawasan Farmasi (5NF)</h3>
                        <p class="text-[11px] text-zinc-500">Tabel relasi ternari: <code>peta_pengawas_staf</code></p>
                    </div>
                </div>

                <form action="{{ route('admin.cabang.staf.pengawas', ['client' => $client]) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="id_apj" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Apoteker Penanggung Jawab (APJ Pengawas)</label>
                        <select name="id_apj" id="id_apj" required class="w-full min-h-[44px] rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Apoteker Ber-SIPA --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id_pengguna }}">
                                    apt. {{ $user->nama_lengkap }} ({{ $user->id_pengguna }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="id_staf" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Staf Teknis yang Disupervisi</label>
                        <select name="id_staf" id="id_staf" required class="w-full min-h-[44px] rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Staf Pelaksana / Asisten --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id_pengguna }}">
                                    {{ $user->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/60 p-3 text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Relasi 5NF memastikan independensi penugasan APJ per cabang tanpa menimbulkan anomali join saat staf dimutasi sementara.
                    </div>

                    <button type="submit" class="w-full min-h-[44px] inline-flex items-center justify-center rounded-lg bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 active:scale-[0.98] transition">
                        Tetapkan Hierarki Pengawasan (5NF)
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel 1: Staf Terdaftar di Cabang -->
        <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <div class="p-4 sm:p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Daftar Staf Aktif Cabang</h3>
                    <p class="text-xs text-zinc-500">Staf yang memiliki hak akses legal pada cabang ini</p>
                </div>
                <span class="text-xs font-semibold text-zinc-500 self-start sm:self-auto">{{ count($staff) }} staf terdaftar</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[34rem]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 whitespace-nowrap">
                        <tr>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Nama Lengkap</th>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Kontak</th>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Peran Cabang</th>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Ditugaskan Sejak</th>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-800 dark:text-zinc-200">
                        @forelse ($staff as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="py-3 px-3.5 sm:px-4 font-semibold whitespace-nowrap">{{ $item->pengguna->nama_lengkap ?? '-' }}</td>
                                <td class="py-3 px-3.5 sm:px-4 text-zinc-500 whitespace-nowrap">{{ $item->pengguna->telepon_email ?? '-' }}</td>
                                <td class="py-3 px-3.5 sm:px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $item->peran === 'apoteker' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->peran)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 sm:px-4 text-zinc-500 whitespace-nowrap">{{ $item->ditugaskan_dari ? \Carbon\Carbon::parse($item->ditugaskan_dari)->format('d M Y') : '-' }}</td>
                                <td class="py-3 px-3.5 sm:px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-zinc-400">Belum ada staf yang ditugaskan ke cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel 2: Hubungan Supervisi APJ (5NF) -->
        <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-800 dark:bg-zinc-900 shadow-xs">
            <div class="p-4 sm:p-6 border-b border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Matriks Supervisi APJ &amp; Tenaga Teknis (5NF)</h3>
                    <p class="text-xs text-zinc-500">Pengawasan sah antara Apoteker Penanggung Jawab dan staf pelaksana</p>
                </div>
                <span class="text-xs font-semibold text-zinc-500 self-start sm:self-auto">{{ count($supervisions) }} relasi terdaftar</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[34rem]">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 border-b border-zinc-200 dark:border-zinc-800 whitespace-nowrap">
                        <tr>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Staf Pelaksana</th>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">APJ Pengawas (Supervising Pharmacist)</th>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Berlaku Sejak</th>
                            <th class="py-3 px-3.5 sm:px-4 font-semibold">Derajat Normalisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-800 dark:text-zinc-200">
                        @forelse ($supervisions as $sup)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="py-3 px-3.5 sm:px-4 font-semibold whitespace-nowrap">{{ $sup->staf->nama_lengkap ?? '-' }}</td>
                                <td class="py-3 px-3.5 sm:px-4 whitespace-nowrap">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">apt. {{ $sup->pengawas->nama_lengkap ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3.5 sm:px-4 text-zinc-500 whitespace-nowrap">{{ $sup->berlaku_dari ? \Carbon\Carbon::parse($sup->berlaku_dari)->format('d M Y') : '-' }}</td>
                                <td class="py-3 px-3.5 sm:px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                        5NF (peta_pengawas_staf)
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-zinc-400">Belum ada relasi supervisi APJ yang dipetakan pada cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>

