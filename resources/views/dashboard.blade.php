<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Diprella</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col antialiased">

    <!-- Loading Overlay while checking Firebase Auth State -->
    <div id="auth-loading" class="fixed inset-0 bg-white/90 backdrop-blur-sm z-50 flex flex-col items-center justify-center gap-3">
        <svg class="animate-spin h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span class="text-sm font-medium text-gray-600">Memeriksa sesi login...</span>
    </div>

    <!-- Navigation Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <!-- Brand -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-bold text-xl text-emerald-600 hover:text-emerald-700 transition">
                <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center p-1.5 shadow-sm">
                    <svg class="w-full h-full fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="4" />
                        <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                        <path d="M8.5 10v5.5a2 2 0 002 2h5" />
                        <circle cx="15.5" cy="17.5" r="1.5" fill="currentColor"/>
                    </svg>
                </span>
                <span>Diprella</span>
            </a>

            <!-- User Menu & Actions -->
            <div class="flex items-center gap-4">
                <!-- Profile Trigger Button -->
                <button id="profile-toggle-btn" type="button" class="flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition cursor-pointer text-sm font-medium text-gray-700">
                    <img id="nav-user-avatar" src="" alt="Avatar" class="w-7 h-7 rounded-full object-cover hidden">
                    <span id="nav-user-initial" class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">
                        U
                    </span>
                    <span id="nav-user-name" class="font-medium">Profil</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Logout Button -->
                <button id="dashboard-logout-btn" type="button" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-full transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Clean Welcome Body ("Polosan") -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="max-w-xl w-full bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
            
            <!-- Welcome Icon / Avatar -->
            <div class="mx-auto w-20 h-20 mb-5 relative flex items-center justify-center">
                <img id="hero-user-avatar" src="" alt="User Avatar" class="w-20 h-20 rounded-full object-cover border-4 border-emerald-100 shadow-md hidden">
                <div id="hero-user-initial" class="w-20 h-20 rounded-full bg-emerald-600 text-white text-3xl font-bold flex items-center justify-center shadow-md">
                    U
                </div>
                <span class="absolute bottom-1 right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></span>
            </div>

            <!-- Welcoming Headline -->
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                Selamat Datang, <span id="hero-user-name" class="text-emerald-600">Pengguna</span>!
            </h1>
            <p class="text-gray-500 text-sm mt-2 max-w-md mx-auto leading-relaxed">
                Anda telah berhasil masuk ke dalam sistem menggunakan autentikasi Firebase.
            </p>

            <!-- User Information Overview Card -->
            <div class="mt-8 bg-gray-50 rounded-xl p-5 border border-gray-100 text-left space-y-3.5">
                <div class="flex items-center justify-between text-sm py-1 border-b border-gray-200/60">
                    <span class="text-gray-500 font-medium">Alamat Email</span>
                    <span id="hero-user-email" class="text-gray-900 font-semibold">user@example.com</span>
                </div>
                <div class="flex items-center justify-between text-sm py-1 border-b border-gray-200/60">
                    <span class="text-gray-500 font-medium">Metode Login</span>
                    <span id="hero-user-provider" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Firebase Auth
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm py-1">
                    <span class="text-gray-500 font-medium">Status Akun</span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Terverifikasi & Aktif
                    </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-7 flex items-center justify-center gap-3">
                <button id="hero-profile-btn" type="button" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full text-sm font-semibold shadow-sm transition cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Buka Profil</span>
                </button>
                <button id="hero-logout-btn" type="button" class="px-6 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-full text-sm font-semibold transition cursor-pointer">
                    Keluar (Logout)
                </button>
            </div>
        </div>
    </main>

    <!-- Slide-over / Modal Profile Card -->
    <div id="profile-modal" class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all duration-300">
            <!-- Modal Header -->
            <div class="bg-emerald-600 px-6 py-5 text-white flex items-center justify-between">
                <h3 class="font-bold text-lg">Profil Pengguna</h3>
                <button id="profile-modal-close" type="button" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                    <img id="modal-user-avatar" src="" alt="Avatar" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-500 hidden">
                    <div id="modal-user-initial" class="w-16 h-16 rounded-full bg-emerald-600 text-white text-2xl font-bold flex items-center justify-center">
                        U
                    </div>
                    <div>
                        <h4 id="modal-user-name" class="font-bold text-gray-900 text-lg leading-tight">Pengguna</h4>
                        <p id="modal-user-email" class="text-sm text-gray-500">user@example.com</p>
                        <span id="modal-user-provider" class="inline-block mt-1 text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full">
                            Google Account
                        </span>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs text-gray-600">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-400">Firebase User ID</span>
                        <span id="modal-user-uid" class="font-mono text-gray-800 text-[11px] select-all">--</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-400">Email Verified</span>
                        <span id="modal-user-verified" class="font-semibold text-emerald-600">Ya</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-100">
                <button id="modal-logout-btn" type="button" class="text-rose-600 hover:text-rose-700 font-semibold text-sm cursor-pointer">
                    Keluar dari Akun
                </button>
                <button id="modal-close-btn" type="button" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm font-medium transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</body>
</html>
