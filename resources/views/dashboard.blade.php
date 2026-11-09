<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Pengguna - OmniPharma Network</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-800 antialiased flex flex-col font-sans">
    <!-- Navbar Atas -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand -->
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <img src="/logo.png" alt="OmniPharma Logo" class="h-10 w-10 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.35)] hover:scale-105 transition-transform duration-200">
                    <div>
                        <span class="block text-xs font-bold tracking-widest text-emerald-700 uppercase">OmniPharma</span>
                        <span class="block text-sm font-semibold text-slate-900 leading-tight">Dashboard Pelanggan</span>
                    </div>
                </a>
            </div>

            <!-- User Menu & Logout -->
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex items-center gap-2.5 px-3 py-1.5 rounded-full border border-slate-200 bg-slate-50/70 text-xs">
                    @php
                        $photoUrl = session('firebase_photo') ?? null;
                        $provider = session('firebase_provider', 'password');
                        $user = auth()->user();
                        $userName = $user->name ?? 'Pengguna';
                        $userEmail = $user->email ?? '-';
                        $userRole = $user->role ?? ($user->peran ?? 'pelanggan');
                    @endphp

                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Avatar" class="w-6 h-6 rounded-full object-cover">
                    @else
                        <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-[10px]">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </div>
                    @endif
                    <span class="font-semibold text-slate-800 hidden sm:inline">{{ $userName }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-200 text-slate-700">
                        {{ $userRole }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $provider === 'google' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $provider === 'google' ? 'Google' : 'Email/Pass' }}
                    </span>
                </div>

                <!-- Tombol Logout -->
                <button
                    type="button"
                    onclick="if(window.OmniFirebaseAuth) { window.OmniFirebaseAuth.logoutBoth(); } else { document.getElementById('native-logout-form').submit(); }"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>

                <form id="native-logout-form" method="POST" action="{{ route('auth.firebase.logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </header>

    <!-- Konten Utama Dashboard -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Hero Banner Sambutan -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
                <div class="flex items-center gap-4">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Avatar" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-emerald-500 shadow-md">
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-md">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                Halo, {{ $userName }}!
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                Peran: {{ $userRole }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $provider === 'google' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                @if($provider === 'google')
                                    <svg class="w-3 h-3" viewBox="0 0 24 24"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                                    Google Identity
                                @else
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Email/Pass
                                @endif
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            {{ $userEmail }} • Terhubung melalui <span class="font-semibold text-slate-700">{{ $provider === 'google' ? 'Google Identity Services' : 'Firebase Password Auth' }}</span>
                        </p>
                        @if(session('firebase_uid'))
                            <div class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">
                                <span>UID:</span>
                                <span class="select-all font-semibold text-slate-600">{{ session('firebase_uid') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <a
                        href="{{ route('customer.home', ['client' => 'banyuwangi-kota']) }}"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-xs transition min-h-[44px]"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Mulai Belanja Obat</span>
                    </a>
                    <a
                        href="{{ route('customer.cart', ['client' => 'banyuwangi-kota']) }}"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm transition min-h-[44px]"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Lihat Keranjang</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bagian Portal Akses Terpadu (Sesuai Proyek OmniPharma) -->
        <div>
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-900">Portal Layanan & Ekosistem Farmasi</h2>
                <p class="text-xs sm:text-sm text-slate-500">Pilih modul navigasi untuk menguji sistem sesuai alur penugasan praktikum.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Katalog Pasien Cabang Banyuwangi Kota -->
                <a
                    href="{{ route('customer.home', ['client' => 'banyuwangi-kota']) }}"
                    class="group p-5 rounded-2xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm group-hover:text-emerald-700">Katalog Obat Cabang Bwi</h3>
                        <p class="text-xs text-slate-500 mt-1">Cek obat bebas & keras, unggah foto resep dokter, dan pesan obat.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-600">
                        <span>Buka Cabang Kota</span>
                        <span>&rarr;</span>
                    </div>
                </a>

                <!-- 2. Katalog Pasien Cabang Rogojampi -->
                <a
                    href="{{ route('customer.home', ['client' => 'rogojampi']) }}"
                    class="group p-5 rounded-2xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3 group-hover:scale-110 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm group-hover:text-blue-700">Katalog Cabang Rogojampi</h3>
                        <p class="text-xs text-slate-500 mt-1">Isolasi stok multi-tenant cabang 02 dan cek ketersediaan obat.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-blue-600">
                        <span>Buka Rogojampi</span>
                        <span>&rarr;</span>
                    </div>
                </a>

                <!-- 3. Portal Apoteker & Staf Cabang -->
                <a
                    href="{{ route('tenant.dashboard', ['client' => 'banyuwangi-kota']) }}"
                    class="group p-5 rounded-2xl bg-white border border-slate-200 hover:border-amber-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:scale-110 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm group-hover:text-amber-700">Antrean Resep & FEFO (APJ)</h3>
                        <p class="text-xs text-slate-500 mt-1">Verifikasi SIP dokter, rotasi batch terdekat ED, dan telaah farmasi.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-amber-600">
                        <span>Portal Apoteker</span>
                        <span>&rarr;</span>
                    </div>
                </a>

                <!-- 4. Portal Super Admin Eksekutif -->
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="group p-5 rounded-2xl bg-white border border-slate-200 hover:border-purple-500 hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm group-hover:text-purple-700">Super Admin Eksekutif</h3>
                        <p class="text-xs text-slate-500 mt-1">Omzet agregat lintas cabang, tutup periode akuntansi, dan ekspor CSV.</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-purple-600">
                        <span>Portal Admin</span>
                        <span>&rarr;</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Rincian Informasi Sesi & Kredensial Medis -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4">Informasi Sesi Autentikasi Pengguna</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block mb-1">Status Autentikasi</span>
                    <span class="font-bold text-emerald-700 text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Terverifikasi Sesi
                    </span>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block mb-1">Penyedia Identitas (IdP)</span>
                    <span class="font-bold text-slate-800 text-sm">
                        {{ $provider === 'google' ? 'Google OAuth 2.0 (Firebase)' : 'Email & Password (Firebase)' }}
                    </span>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                    <span class="text-slate-400 block mb-1">Peran Pengguna</span>
                    <span class="font-bold text-slate-800 text-sm uppercase">
                        {{ $userRole }}
                    </span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-400">
        &copy; 2026 OmniPharma Network. Dikembangkan untuk Praktikum Pengembangan Sistem Informasi (Pak Devit Suwardiyanto).
    </footer>

    @fluxScripts
</body>
</html>
