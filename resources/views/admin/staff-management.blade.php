<x-layouts.admin title="Manajemen Staf & Pengawasan APJ Cabang">
    <div class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-slate-500 flex-wrap">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-800 font-medium transition py-1">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-slate-900 font-bold py-1">Manajemen Staf &amp; Supervisi APJ</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="w-full sm:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- Hero Banner: Nuansa Hijau Deep Spruce Sesuai Halaman Login -->
        <div class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#0A231E] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <!-- Lingkaran Dekorasi Geometris Halus Sesuai Login -->
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full border border-emerald-500/10 pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full border border-emerald-500/15 pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-900/60 text-emerald-200 border border-emerald-500/30">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#D4F94E]"></span>
                            Standar Permenkes RI No. 73/2016
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-white/10 text-[#D4F94E] border border-white/15">
                            5NF Hierarki Pengawasan
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-3 leading-tight">
                        Supervisi Staf &amp; Apoteker Penanggung Jawab
                    </h1>
                    <p class="text-xs sm:text-sm text-emerald-200/90 mt-1 max-w-3xl leading-relaxed">
                        Pengelolaan tenaga kefarmasian resmi (APJ, Tenaga Teknis Kefarmasian, dan Kasir) untuk <strong>{{ $clientModel->nama_client }}</strong>.
                    </p>

                    <div class="flex flex-wrap items-center gap-2 mt-4 text-xs">
                        <span class="px-3 py-1 rounded-xl bg-white/10 border border-white/15 text-white font-bold">
                            🏢 {{ $clientModel->nama_client }}
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-white/10 border border-white/15 text-emerald-200 font-mono">
                            Kode Cabang: {{ $clientModel->slug ?? $clientModel->id_client }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-[#D4F94E] font-bold">
                            <span class="w-2 h-2 rounded-full bg-[#D4F94E] animate-pulse"></span>
                            Operasional Aktif
                        </span>
                    </div>
                </div>

                <div class="shrink-0 flex flex-col gap-2">
                    <div class="rounded-2xl bg-white/10 border border-white/15 p-4 text-center backdrop-blur-xs">
                        <span class="block text-2xl font-black text-[#D4F94E]">{{ count($staff) }}</span>
                        <span class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider">Staf Terdaftar</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifikasi Pesan Flash -->
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-xs font-bold text-emerald-900 shadow-xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-bold text-rose-900 shadow-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 2 Formulir Penugasan & Supervisi (Gaya Tombol Hijau Menu Login) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Form 1: Beri Peran Staf Cabang -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-[#0A231E] to-[#0E352E] text-[#D4F94E] flex items-center justify-center font-black text-sm border border-emerald-500/20 shadow-xs shrink-0">
                            1
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">Penugasan Peran Staf Cabang</h2>
                            <p class="text-xs text-slate-500">Tabel entitas normalisasi: <code class="font-mono text-emerald-800">peran_client_pengguna</code></p>
                        </div>
                    </div>

                    <form action="{{ route('admin.cabang.staf.role', ['client' => $client]) }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="id_pengguna" class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Pengguna / Akun Terdaftar</label>
                            <select name="id_pengguna" id="id_pengguna" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">
                                <option value="">-- Pilih Akun Terdaftar --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id_pengguna }}">
                                        {{ $user->nama_lengkap }} ({{ $user->telepon_email ?? $user->email ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="peran" class="block text-xs font-bold text-slate-700 mb-1.5">Peran Resmi di Cabang Ini</label>
                            <select name="peran" id="peran" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">
                                <option value="apoteker">Apoteker (Apoteker Penanggung Jawab / APJ)</option>
                                <option value="asisten_apoteker">Asisten Apoteker (Tenaga Teknis Kefarmasian)</option>
                                <option value="kasir">Kasir Pelaksana</option>
                                <option value="staf_klien">Staf Administrasi Cabang</option>
                            </select>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full min-h-[46px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                <span>Simpan Penugasan Peran</span>
                                <span class="text-sm">&rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form 2: Hubungan Supervisi APJ 5NF -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-black text-sm shadow-xs shrink-0">
                            2
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">Hierarki Pengawasan Farmasi (5NF)</h2>
                            <p class="text-xs text-slate-500">Tabel relasi ternari: <code class="font-mono text-emerald-800">peta_pengawas_staf</code></p>
                        </div>
                    </div>

                    <form action="{{ route('admin.cabang.staf.pengawas', ['client' => $client]) }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="id_apj" class="block text-xs font-bold text-slate-700 mb-1.5">Apoteker Penanggung Jawab (APJ Pengawas)</label>
                            <select name="id_apj" id="id_apj" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">
                                <option value="">-- Pilih Apoteker Ber-SIPA --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id_pengguna }}">
                                        apt. {{ $user->nama_lengkap }} ({{ $user->id_pengguna }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="id_staf" class="block text-xs font-bold text-slate-700 mb-1.5">Staf Teknis yang Disupervisi</label>
                            <select name="id_staf" id="id_staf" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 transition">
                                <option value="">-- Pilih Staf Pelaksana / Asisten --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id_pengguna }}">
                                        {{ $user->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-xl bg-emerald-50/70 border border-emerald-200/80 p-3 text-[11px] text-emerald-900 leading-relaxed font-medium">
                            Relasi 5NF menjamin integritas hukum delegasi resep dokter dan pengawasan penyerahan obat keras (Standar Permenkes No. 73/2016).
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full min-h-[46px] inline-flex items-center justify-center gap-2 rounded-xl bg-[#0A231E] hover:bg-[#0E352E] text-[#D4F94E] border border-emerald-500/30 font-bold text-xs shadow-xs transition cursor-pointer">
                                <span>Tetapkan Hierarki Pengawasan (5NF)</span>
                                <span class="text-sm">&rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel 1: Staf Terdaftar di Cabang -->
        <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Daftar Staf Aktif Cabang</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Seluruh tenaga kefarmasian yang memiliki penugasan legal pada cabang ini</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    Total: {{ count($staff) }} Staf
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[36rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200 whitespace-nowrap">
                        <tr>
                            <th class="py-3.5 px-4">Nama Lengkap</th>
                            <th class="py-3.5 px-4">Kontak / Email</th>
                            <th class="py-3.5 px-4">Peran Cabang</th>
                            <th class="py-3.5 px-4">Ditugaskan Sejak</th>
                            <th class="py-3.5 px-4 text-right">Status Legal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse ($staff as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $item->pengguna->nama_lengkap ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-slate-500 font-mono whitespace-nowrap">{{ $item->pengguna->telepon_email ?? '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ $item->peran === 'apoteker' ? 'bg-[#0A231E] text-[#D4F94E] border border-emerald-500/20' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->peran)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $item->ditugaskan_dari ? \Carbon\Carbon::parse($item->ditugaskan_dari)->format('d M Y') : '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Aktif Terdaftar
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada staf yang ditugaskan ke cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel 2: Hubungan Supervisi APJ (5NF) -->
        <div class="rounded-3xl border border-slate-200/90 bg-white overflow-hidden shadow-xs">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Matriks Supervisi APJ &amp; Tenaga Teknis (5NF)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Struktur garis komando farmasi antara Apoteker Penanggung Jawab dan staf pelaksana</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Total: {{ count($supervisions) }} Relasi 5NF
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[36rem]">
                    <thead class="bg-slate-100 text-slate-700 font-extrabold uppercase border-b border-slate-200 whitespace-nowrap">
                        <tr>
                            <th class="py-3.5 px-4">Staf Pelaksana</th>
                            <th class="py-3.5 px-4">APJ Pengawas (Supervising Pharmacist)</th>
                            <th class="py-3.5 px-4">Berlaku Sejak</th>
                            <th class="py-3.5 px-4 text-right">Derajat Normalisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse ($supervisions as $sup)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $sup->staf->nama_lengkap ?? '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-extrabold text-emerald-800">apt. {{ $sup->pengawas->nama_lengkap ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $sup->berlaku_dari ? \Carbon\Carbon::parse($sup->berlaku_dari)->format('d M Y') : '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-emerald-50 text-emerald-900 border border-emerald-200">
                                        5NF (peta_pengawas_staf)
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">Belum ada relasi supervisi APJ yang dipetakan pada cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
