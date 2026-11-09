<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniPharma Network - Sistem Informasi Apotek Multi-Klien</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-[#F8FAFC] text-slate-800 flex flex-col font-sans selection:bg-emerald-500 selection:text-white overflow-x-hidden">

    <!-- Header & Navigasi Utama -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-[4rem] sm:min-h-[4.5rem] py-2 sm:py-0 flex items-center justify-between gap-2 sm:gap-4">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3 group focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-xl p-1 min-h-[44px] shrink-0" aria-label="OmniPharma Network Beranda">
                <img src="/logo.png" alt="OmniPharma Logo" class="h-9 w-9 sm:h-11 sm:w-11 object-contain drop-shadow-[0_2px_6px_rgba(5,150,105,0.3)] group-hover:scale-105 transition-transform duration-200">
                <div class="min-w-0">
                    <span class="block text-[10px] sm:text-xs font-bold tracking-widest text-emerald-700 uppercase leading-none">OmniPharma</span>
                    <span class="block text-xs sm:text-base font-extrabold text-slate-900 leading-tight">Network</span>
                </div>
            </a>

            <!-- Navigasi Pintar Cabang & Kredensial -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <div class="hidden lg:flex items-center gap-2 text-xs font-semibold text-slate-500 bg-slate-100/80 px-3.5 py-1.5 rounded-full border border-slate-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Multi-Klien: Banyuwangi &amp; Rogojampi</span>
                </div>

                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition min-h-[44px]"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span>Dashboard<span class="hidden sm:inline"> Saya</span></span>
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-700 hover:text-emerald-700 hover:bg-slate-100 transition min-h-[44px]"
                    >
                        Masuk
                    </a>
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-xs transition min-h-[44px]"
                    >
                        <span>Daftar<span class="hidden sm:inline"> Pasien</span></span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 space-y-12 sm:space-y-16 lg:space-y-24 pb-16 sm:pb-20">

        <!-- HERO SECTION: Sambutan Hangat & Ajakan Bertindak -->
        <section class="relative overflow-hidden pt-8 pb-12 sm:pt-12 sm:pb-16 lg:pt-20 lg:pb-24 border-b border-slate-200/80 bg-gradient-to-b from-white via-slate-50/50 to-[#F8FAFC]">
            <!-- Aksen Garis Geometris Halus (Non-Slop) -->
            <div class="absolute -top-40 right-0 w-96 h-96 rounded-full bg-emerald-50/60 blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    <!-- Teks Hero Kiri -->
                    <div class="lg:col-span-7 space-y-5 sm:space-y-6 text-center lg:text-left">
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                <span class="h-2 w-2 rounded-full bg-emerald-600 shrink-0"></span>
                                <span>Praktikum PSI Pak Devit Suwardiyanto</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-slate-600 bg-slate-100/90 border border-slate-200">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                                <span>Cabang: BWI Kota &amp; Rogojampi</span>
                            </div>
                        </div>

                        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                            Sistem Pelayanan Apotek <span class="text-emerald-600">Multi-Klien</span> yang Cepat &amp; Akurat
                        </h1>

                        <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                            Selamat datang di portal terpadu OmniPharma Network. Cari obat bebas maupun keras, unggah resep dokter terverifikasi SIP, nikmati manajemen stok FEFO multi-cabang, serta lakukan pelunasan instan dengan QRIS Dinamis.
                        </p>

                        <!-- Tombol CTA Utama -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center lg:justify-start gap-3 pt-2">
                            <a
                                href="{{ route('customer.home', ['client' => 'banyuwangi-kota']) }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-5 sm:px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm transition min-h-[48px]"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                <span>Cari &amp; Pesan Obat Sekarang</span>
                            </a>

                            <a
                                href="{{ route('login') }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm shadow-2xs transition min-h-[48px]"
                            >
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                <span>Masuk ke Akun Anda</span>
                            </a>
                        </div>

                        <!-- Info Sertifikasi Resmi -->
                        <div class="pt-3 flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-4 text-xs font-semibold text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Standar Permenkes No. 73/2016
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                QRIS Standar EMVCo
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                WebSocket Live Tracking
                            </span>
                        </div>
                    </div>

                    <!-- Showcase Logo & Kartu Visual Kanan -->
                    <div class="lg:col-span-5 flex justify-center w-full">
                        <div class="relative w-full max-w-md bg-gradient-to-br from-[#0A231E] to-[#0E352E] rounded-3xl p-6 sm:p-8 text-white shadow-xl overflow-hidden border border-emerald-950/20">
                            <!-- Logo Floating Transparan Berbayang -->
                            <div class="text-center space-y-3 sm:space-y-4">
                                <div class="inline-block p-2 sm:p-4">
                                    <img src="/logo.png" alt="OmniPharma Emblem" class="h-20 w-20 sm:h-28 sm:w-28 mx-auto object-contain drop-shadow-[0_8px_20px_rgba(0,0,0,0.5)] hover:scale-105 transition-transform duration-300">
                                </div>
                                <h3 class="text-base sm:text-lg font-extrabold tracking-tight text-white">OmniPharma Network</h3>
                                <p class="text-xs text-emerald-200/80 leading-relaxed">
                                    Pusat integrasi resep dokter, stok apotek cabang, dan verifikasi legalitas SIP apoteker secara real-time.
                                </p>
                            </div>

                            <!-- Pilihan Cepat Cabang Apotek -->
                            <div class="mt-5 sm:mt-6 pt-4 sm:pt-5 border-t border-white/10 space-y-2.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300 block text-center sm:text-left">Pilih Cabang Apotek Langsung:</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-bold">
                                    <a href="{{ route('customer.home', ['client' => 'banyuwangi-kota']) }}" class="min-h-[44px] px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-center transition flex items-center justify-center">
                                        Cabang BWI Kota
                                    </a>
                                    <a href="{{ route('customer.home', ['client' => 'rogojampi']) }}" class="min-h-[44px] px-3 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-center transition flex items-center justify-center">
                                        Cabang Rogojampi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- PANDUAN: Cara Cari & Pesan Obat Sesuai Proyek OmniPharma -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Alur Pelayanan Pasien</span>
                <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                    Cara Cari &amp; Tebus Obat di OmniPharma
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-2">
                    Proses pemesanan obat dirancang sistematis sesuai standar pelayanan kefarmasian di apotek.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Langkah 1: Pilih Cabang -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 font-black text-sm flex items-center justify-center mb-4">
                            01
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Pilih Cabang Apotek</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pilih cabang apotek terdekat (Banyuwangi Kota atau Rogojampi) untuk mengecek ketersediaan stok fisik obat secara akurat.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-emerald-700">
                        Isolasi Stok Multi-Tenant
                    </div>
                </div>

                <!-- Langkah 2: Cari Obat / Unggah Resep -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-800 font-black text-sm flex items-center justify-center mb-4">
                            02
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Cari Obat atau Unggah Resep</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Obat bebas dapat langsung dibeli. Untuk obat keras (bertanda lingkaran K merah), unggah foto resep dokter untuk diverifikasi apoteker.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-blue-700">
                        Validasi SIP Dokter
                    </div>
                </div>

                <!-- Langkah 3: Bayar dengan QRIS -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 font-black text-sm flex items-center justify-center mb-4">
                            03
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Checkout &amp; Bayar QRIS</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Stok obat dikunci secara pesimistik saat checkout. Lakukan pembayaran instan menggunakan QRIS dinamis standar EMVCo.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-amber-700">
                        Pessimistic Stock Lock
                    </div>
                </div>

                <!-- Langkah 4: Live Tracking Reverb -->
                <div class="p-5 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 font-black text-sm flex items-center justify-center mb-4">
                            04
                        </div>
                        <h3 class="font-bold text-slate-900 text-base mb-2">Lacak Pesanan Real-Time</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pantau tahapan persiapan obat di apotek melalui live stepper WebSocket Reverb hingga pesanan siap diambil di kasir.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-semibold text-purple-700">
                        Laravel Reverb WebSockets
                    </div>
                </div>
            </div>
        </section>
        <!-- KARTU AKSES 4 PORTAL DEMO PRAKTIKUM (PAK DEVIT) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-slate-900 text-white p-5 sm:p-8 lg:p-12 shadow-xl relative overflow-hidden">
                <div class="max-w-2xl mb-6 sm:mb-8">
                    <span class="text-xs font-bold uppercase tracking-widest text-[#D4F94E]">4 Layar Demo Praktikum</span>
                    <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight text-white mt-1">
                        Portal Terpadu Seluruh Peran Pengguna
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-2">
                        Masuk ke antarmuka yang Anda butuhkan untuk mendemonstrasikan sistem informasi apotek jaringan multi-klien:
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-slate-900">
                    <!-- 1. Katalog Pasien -->
                    <a href="{{ route('customer.home', ['client' => 'banyuwangi-kota']) }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <div>
                            <span class="inline-block text-[10px] font-bold text-emerald-700 uppercase bg-emerald-50 px-2 py-0.5 rounded-md">Layar 1 • Pasien</span>
                            <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-emerald-700">Katalog Obat Publik</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Cari obat bebas &amp; keras, unggah foto resep, dan cek stok cabang.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-emerald-600 flex items-center justify-between min-h-[44px]">
                            <span>Buka Katalog</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>

                    <!-- 2. Portal Apoteker (APJ) -->
                    <a href="{{ route('tenant.dashboard', ['client' => 'banyuwangi-kota']) }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <div>
                            <span class="inline-block text-[10px] font-bold text-amber-700 uppercase bg-amber-50 px-2 py-0.5 rounded-md">Layar 2 • Apoteker</span>
                            <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-amber-700">Antrean Resep &amp; FEFO</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Telaah resep dokter, validasi SIP, dan pantau batch mendekati ED.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-amber-600 flex items-center justify-between min-h-[44px]">
                            <span>Buka Portal Cabang</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>

                    <!-- 3. Super Admin Eksekutif -->
                    <a href="{{ route('admin.dashboard') }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <div>
                            <span class="inline-block text-[10px] font-bold text-purple-700 uppercase bg-purple-50 px-2 py-0.5 rounded-md">Layar 3-4 • Admin</span>
                            <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-purple-700">Executive Dashboard</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Agregasi omzet cabang, tutup periode akuntansi, dan ekspor CSV.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-purple-600 flex items-center justify-between min-h-[44px]">
                            <span>Buka Super Admin</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>

                    <!-- 4. Portal Otentikasi -->
                    <a href="{{ route('login') }}" class="min-h-[44px] p-5 rounded-2xl bg-white hover:bg-slate-50 transition flex flex-col justify-between group focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div>
                            <span class="inline-block text-[10px] font-bold text-blue-700 uppercase bg-blue-50 px-2 py-0.5 rounded-md">Otentikasi • Akun</span>
                            <h3 class="font-bold text-sm text-slate-900 mt-2 group-hover:text-blue-700">Masuk &amp; Registrasi</h3>
                            <p class="text-[11px] text-slate-500 mt-1">Login 1-klik Google, verifikasi OTP email, atau akun demo praktikum.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-xs font-bold text-blue-600 flex items-center justify-between min-h-[44px]">
                            <span>Masuk Portal</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-8 px-4 text-center text-xs text-slate-500 space-y-2">
        <div class="flex items-center justify-center gap-2">
            <img src="/logo.png" alt="OmniPharma" class="h-6 w-6 object-contain">
            <span class="font-bold text-slate-800">OmniPharma Network</span>
        </div>
        <p>Proyek Praktikum Pengembangan Sistem Informasi • Dosen Pengampu: Pak Devit Suwardiyanto</p>
        <p class="text-slate-400 text-[11px]">&copy; 2026 OmniPharma Network. Hak Cipta Dilindungi.</p>
    </footer>

    @fluxScripts
</body>
</html>
