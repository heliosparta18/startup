<x-layouts.admin title="Manajemen Staf &amp; Pengawasan APJ Cabang">
    <div x-data="{ showCreateStaff: false }" class="space-y-6 sm:space-y-8">
        <!-- Breadcrumb & Cabang Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-emerald-800 font-medium transition">Dashboard Pusat</a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Manajemen Staf &amp; Supervisi APJ</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="showCreateStaff = !showCreateStaff" class="min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                    <span x-text="showCreateStaff ? 'Tutup Form Akun' : '+ Buat Akun Staf Baru'"></span>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Clean Light Header Banner -->
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Standar Permenkes RI No. 73/2016
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        5NF Hierarki Pengawasan
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 mt-2 leading-tight">
                    Supervisi Staf &amp; Apoteker Penanggung Jawab
                </h1>

                <!-- Switcher Cabang -->
                @if(isset($clients) && $clients->count() > 0)
                    <div class="flex flex-wrap items-center gap-2 mt-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mr-1">Cabang:</span>
                        @foreach($clients as $c)
                            @php $cSlug = $c->slug ?? $c->id_client; @endphp
                            <a href="{{ route('admin.cabang.staf', ['client' => $cSlug]) }}"
                               class="min-h-[44px] inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold transition {{ ($client === $cSlug || $clientModel->id_client === $c->id_client) ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                {{ $c->nama_client }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="shrink-0">
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 px-6 text-center">
                    <span class="block text-2xl font-black text-emerald-800">{{ count($staff) }}</span>
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Staf Terdaftar</span>
                </div>
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
            <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-xs font-bold text-rose-900 shadow-xs space-y-1">
                @if (session('error'))<p>{{ session('error') }}</p>@endif
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <!-- Modal / Panel Buat Akun Staf Baru -->
        <div x-show="showCreateStaff" x-cloak class="rounded-3xl border border-emerald-200 bg-white p-6 sm:p-8 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base font-extrabold text-slate-900">Buat Akun Login Staf &amp; Tugaskan ke {{ $clientModel->nama_client }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Membuat akun login baru (Apoteker atau Kasir) sekaligus menautkannya ke cabang aktif ini.</p>
            </div>
            <form action="{{ route('admin.cabang.staf.akun', ['client' => $client]) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                @csrf
                <div>
                    <label for="new_nama_lengkap" class="block font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input id="new_nama_lengkap" type="text" name="nama_lengkap" required placeholder="apt. Rina Kartika, S.Farm" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3.5 text-slate-900 focus:border-emerald-600 focus:outline-none">
                </div>
                <div>
                    <label for="new_email" class="block font-bold text-slate-700 mb-1">Email Login *</label>
                    <input id="new_email" type="email" name="email" required placeholder="staf@omnipharma.test" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3.5 text-slate-900 focus:border-emerald-600 focus:outline-none">
                </div>
                <div>
                    <label for="new_password" class="block font-bold text-slate-700 mb-1">Password Awal *</label>
                    <input id="new_password" type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full min-h-[44px] rounded-xl border border-slate-300 px-3.5 text-slate-900 focus:border-emerald-600 focus:outline-none">
                </div>
                <div>
                    <label for="new_role" class="block font-bold text-slate-700 mb-1">Portal Login *</label>
                    <select id="new_role" name="role" required class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                        <option value="apoteker">Portal Apoteker (Tenaga Kefarmasian)</option>
                        <option value="kasir">Portal Kasir &amp; Loket POS</option>
                    </select>
                </div>
                <div>
                    <label for="new_peran_lokal" class="block font-bold text-slate-700 mb-1">Jabatan Lokal Cabang</label>
                    <select id="new_peran_lokal" name="peran_lokal" class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-slate-900">
                        <option value="apoteker">Apoteker (APJ)</option>
                        <option value="asisten_apoteker">Asisten Apoteker (TTK)</option>
                        <option value="kasir">Kasir Pelaksana</option>
                        <option value="staf_klien">Staf Administrasi Cabang</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full min-h-[44px] rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold shadow-xs cursor-pointer">
                        Buat Akun &amp; Tugaskan
                    </button>
                </div>
            </form>
        </div>

        <!-- 2 Formulir Penugasan & Supervisi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Form 1: Beri Peran Staf Cabang -->
            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <div class="h-10 w-10 rounded-2xl bg-emerald-700 text-white flex items-center justify-center font-black text-sm shadow-xs shrink-0">
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
                            <select name="id_pengguna" id="id_pengguna" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-emerald-600 transition">
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
                            <select name="peran" id="peran" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-emerald-600 transition">
                                <option value="apoteker">Apoteker (Apoteker Penanggung Jawab / APJ)</option>
                                <option value="asisten_apoteker">Asisten Apoteker (Tenaga Teknis Kefarmasian)</option>
                                <option value="kasir">Kasir Pelaksana</option>
                                <option value="staf_klien">Staf Administrasi Cabang</option>
                            </select>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full min-h-[46px] inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                <span>Simpan Penugasan Peran</span>
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
                            <select name="id_apj" id="id_apj" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-emerald-600 transition">
                                <option value="">-- Pilih Apoteker Ber-SIPA --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id_pengguna }}">
                                        apt. {{ $user->nama_lengkap }} ({{ $user->telepon_email ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="id_staf" class="block text-xs font-bold text-slate-700 mb-1.5">Staf Teknis yang Disupervisi</label>
                            <select name="id_staf" id="id_staf" required class="w-full min-h-[46px] rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-emerald-600 transition">
                                <option value="">-- Pilih Staf Pelaksana / Asisten --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id_pengguna }}">
                                        {{ $user->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-[11px] text-emerald-900 leading-relaxed font-medium">
                            Relasi 5NF menjamin kepatuhan delegasi telaah resep dan penyerahan obat keras sesuai Standar Permenkes No. 73/2016.
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full min-h-[46px] inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                <span>Tetapkan Hierarki Pengawasan (5NF)</span>
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
                    <p class="text-xs text-slate-500 mt-0.5">Kelola status aktif/nonaktif akun maupun pencabutan peran pada cabang ini</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-auto">
                    Total: {{ count($staff) }} Staf
                </span>
            </div>

            <!-- Mobile Card View: Daftar Staf -->
            <div class="md:hidden divide-y divide-slate-100 text-xs">
                @forelse ($staff as $item)
                    @php $isAktifMobile = (bool) ($item->pengguna->aktif ?? true); @endphp
                    <article class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-slate-900">{{ $item->pengguna->nama_lengkap ?? '-' }}</h4>
                                <p class="text-[11px] font-mono text-slate-500 break-all mt-0.5">{{ $item->pengguna->telepon_email ?? '-' }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isAktifMobile ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                {{ $isAktifMobile ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px]">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-extrabold {{ $item->peran === 'apoteker' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">
                                {{ ucfirst(str_replace('_', ' ', $item->peran)) }}
                            </span>
                            <span class="text-slate-500">Sejak {{ $item->ditugaskan_dari ? \Carbon\Carbon::parse($item->ditugaskan_dari)->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="grid grid-cols-{{ $item->pengguna ? '2' : '1' }} gap-2 pt-1">
                            @if($item->pengguna)
                                <form method="POST" action="{{ route('admin.cabang.staf.status', ['client' => $client, 'pengguna' => $item->id_pengguna]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs transition cursor-pointer">
                                        {{ $isAktifMobile ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.cabang.staf.role.destroy', ['client' => $client, 'roleId' => $item->id]) }}" onsubmit="return confirm('Cabut peran staf ini dari cabang?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-bold text-xs transition cursor-pointer">
                                    Hapus Peran
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center text-slate-500">Belum ada staf yang ditugaskan ke cabang ini.</div>
                @endforelse
            </div>

            <!-- Desktop Table View: Daftar Staf -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[48rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200 whitespace-nowrap">
                        <tr>
                            <th class="py-3.5 px-4">Nama Lengkap</th>
                            <th class="py-3.5 px-4">Kontak / Email</th>
                            <th class="py-3.5 px-4">Peran Cabang</th>
                            <th class="py-3.5 px-4">Ditugaskan Sejak</th>
                            <th class="py-3.5 px-4">Status Akun</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse ($staff as $item)
                            @php $isAktif = (bool) ($item->pengguna->aktif ?? true); @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $item->pengguna->nama_lengkap ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-slate-500 font-mono whitespace-nowrap">{{ $item->pengguna->telepon_email ?? '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ $item->peran === 'apoteker' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->peran)) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $item->ditugaskan_dari ? \Carbon\Carbon::parse($item->ditugaskan_dari)->format('d M Y') : '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $isAktif ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                        {{ $isAktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if($item->pengguna)
                                            <form method="POST" action="{{ route('admin.cabang.staf.status', ['client' => $client, 'pengguna' => $item->id_pengguna]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="min-h-[44px] px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition cursor-pointer">
                                                    {{ $isAktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.cabang.staf.role.destroy', ['client' => $client, 'roleId' => $item->id]) }}" onsubmit="return confirm('Cabut peran staf ini dari cabang?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="min-h-[44px] px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-bold text-xs transition cursor-pointer">
                                                Hapus Peran
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">Belum ada staf yang ditugaskan ke cabang ini.</td>
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
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 self-start sm:self-auto">
                    Total: {{ count($supervisions) }} Relasi 5NF
                </span>
            </div>

            <!-- Mobile Card View: Supervisi 5NF -->
            <div class="md:hidden divide-y divide-slate-100 text-xs">
                @forelse ($supervisions as $sup)
                    <article class="p-4 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-slate-900">{{ $sup->staf->nama_lengkap ?? '-' }}</h4>
                                <p class="font-extrabold text-emerald-700 mt-0.5">APJ: apt. {{ $sup->pengawas->nama_lengkap ?? '-' }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">Berlaku sejak {{ $sup->berlaku_dari ? \Carbon\Carbon::parse($sup->berlaku_dari)->format('d M Y') : '-' }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-emerald-50 text-emerald-900 border border-emerald-200">
                                5NF
                            </span>
                        </div>
                        <form method="POST" action="{{ route('admin.cabang.staf.pengawas.destroy', ['client' => $client, 'supervisionId' => $sup->id]) }}" onsubmit="return confirm('Hapus relasi supervisi APJ ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-bold text-xs transition cursor-pointer">
                                Hapus Relasi
                            </button>
                        </form>
                    </article>
                @empty
                    <div class="p-8 text-center text-slate-500">Belum ada relasi supervisi APJ yang dipetakan pada cabang ini.</div>
                @endforelse
            </div>

            <!-- Desktop Table View: Supervisi 5NF -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[40rem]">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200 whitespace-nowrap">
                        <tr>
                            <th class="py-3.5 px-4">Staf Pelaksana</th>
                            <th class="py-3.5 px-4">APJ Pengawas</th>
                            <th class="py-3.5 px-4">Berlaku Sejak</th>
                            <th class="py-3.5 px-4">Normalisasi</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @forelse ($supervisions as $sup)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $sup->staf->nama_lengkap ?? '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-extrabold text-emerald-700">apt. {{ $sup->pengawas->nama_lengkap ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $sup->berlaku_dari ? \Carbon\Carbon::parse($sup->berlaku_dari)->format('d M Y') : '-' }}</td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-emerald-50 text-emerald-900 border border-emerald-200">
                                        5NF
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                    <form method="POST" action="{{ route('admin.cabang.staf.pengawas.destroy', ['client' => $client, 'supervisionId' => $sup->id]) }}" onsubmit="return confirm('Hapus relasi supervisi APJ ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="min-h-[44px] px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-bold text-xs transition cursor-pointer">
                                            Hapus Relasi
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">Belum ada relasi supervisi APJ yang dipetakan pada cabang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
