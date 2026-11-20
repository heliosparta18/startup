@props([
    'title' => 'Portal Operasional - OmniPharma',
    'portalSubtitle' => 'Operasional Apotek',
    'navItems' => [],
    'portalHomeRoute' => route('dashboard'),
    'portalHomeLabel' => 'Ringkasan Portal',
    'showSplash' => true,
])
<!DOCTYPE html>
<html lang="id" class="light scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @include('partials.omni-theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-[#F8FAFC] text-slate-900 antialiased flex flex-col font-sans overflow-x-hidden transition-colors duration-200" x-data="{ mobileNav: false }">
    @if($showSplash)
        <x-welcome-splash />
    @endif

    @php
        $loggedUser = auth()->user();
        $userName = $loggedUser->name ?? 'Pengguna OmniPharma';
        $userEmail = $loggedUser->email ?? '';
        $photoUrl = session('firebase_photo');
        $initials = collect(explode(' ', trim($userName)))
            ->filter()
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->take(2)
            ->implode('') ?: 'OP';
    @endphp

    <header
        class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-2xs"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                    <!-- Hamburger Menu Button (Mobile & Desktop) -->
                    <button
                        type="button"
                        @click="mobileNav = !mobileNav"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-emerald-800 transition shrink-0 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                        :aria-expanded="mobileNav"
                        aria-label="Menu Navigasi Portal"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <a
                        href="{{ $portalHomeRoute }}"
                        aria-label="OmniPharma Dashboard Utama"
                        class="flex items-center gap-2 sm:gap-2.5 group shrink-0 min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 rounded-xl py-1 min-w-0"
                    >
                        <span class="sr-only">Dashboard Utama</span>
                        <img src="/logo.png" alt="OmniPharma Logo" class="h-8 w-8 sm:h-9 sm:w-9 object-contain shrink-0 group-hover:scale-105 transition-transform duration-200">
                        <div class="flex flex-col leading-tight min-w-0">
                            <span class="text-sm sm:text-base font-black tracking-tight text-slate-900 truncate">OmniPharma</span>
                            <span class="text-[11px] font-semibold text-emerald-700 truncate">{{ $portalSubtitle }}</span>
                        </div>
                    </a>
                </div>

                @isset($headerExtra)
                    <div class="hidden md:flex items-center gap-2">
                        {{ $headerExtra }}
                    </div>
                @endisset

                <div class="flex items-center gap-2 shrink-0">
                    @auth
                        <div class="relative" x-data="{ open: false }" @keydown.escape.stop="open = false" @click.outside="open = false">
                            <button
                                type="button"
                                @click="open = !open"
                                id="internal-user-menu-button"
                                :aria-expanded="open"
                                aria-haspopup="true"
                                aria-label="Menu Pengguna"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-slate-100 shadow-2xs text-xs transition-colors cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                            >
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="Foto Profil {{ $userName }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-300">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-emerald-700 text-white flex items-center justify-center font-extrabold text-[11px] shadow-2xs shrink-0">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <span class="hidden sm:inline font-bold text-slate-900 max-w-[140px] truncate">{{ \Illuminate\Support\Str::limit($userName, 18) }}</span>
                                <svg class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                                class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] max-h-[78vh] overflow-y-auto rounded-2xl bg-white shadow-xl border border-slate-200 p-2 z-50 origin-top-right text-xs font-medium"
                                role="menu"
                                aria-orientation="vertical"
                                aria-labelledby="internal-user-menu-button"
                            >
                                <div class="px-3 py-2.5 bg-slate-50 rounded-xl mb-1.5 border border-slate-200">
                                    <p class="font-bold text-slate-900 truncate">{{ $userName }}</p>
                                    @if($userEmail !== '')
                                        <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $userEmail }}</p>
                                    @endif
                                </div>

                                <div class="py-1">
                                    <a
                                        href="{{ route('profile.edit') }}"
                                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-800 hover:text-slate-900 hover:bg-slate-100 font-semibold transition-colors min-h-[44px]"
                                        role="menuitem"
                                    >
                                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        <span>Profile &amp; Akses Akun</span>
                                    </a>
                                </div>

                                <div
                                    class="border-t border-slate-100 px-3 py-2.5"
                                    x-data="{ themeMode: window.omniGetTheme ? window.omniGetTheme() : 'light' }"
                                    @omni-theme-changed.window="themeMode = $event.detail.theme"
                                >
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[11px] font-bold text-slate-600">Tema Tampilan</span>
                                        <span class="text-[10px] font-extrabold uppercase text-emerald-700" x-text="themeMode === 'dark' ? 'Dark' : 'Putih'"></span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button
                                            type="button"
                                            @click="themeMode = window.omniSetTheme('light')"
                                            :class="themeMode === 'light' ? 'bg-emerald-700 text-white border-emerald-700 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                            class="inline-flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-lg border text-[11px] font-bold transition cursor-pointer min-h-[44px]"
                                        >
                                            <span>Putih</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="themeMode = window.omniSetTheme('dark')"
                                            :class="themeMode === 'dark' ? 'bg-emerald-700 text-white border-emerald-700 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                            class="inline-flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-lg border text-[11px] font-bold transition cursor-pointer min-h-[44px]"
                                        >
                                            <span>Dark</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="border-t border-slate-100 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 min-h-[44px] text-rose-700 hover:bg-rose-50 rounded-xl transition-colors font-bold cursor-pointer" role="menuitem">
                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                            <span>Keluar dari Akun</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Desktop Navigation Rail -->
            @isset($customNav)
                {{ $customNav }}
            @else
                <nav aria-label="Navigasi Portal" class="hidden lg:flex border-t border-slate-100 py-1.5 items-center gap-1 overflow-x-auto text-xs">
                    @foreach($navItems as $item)
                        <a
                            href="{{ $item['href'] }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl min-h-[44px] whitespace-nowrap transition {{ !empty($item['active']) ? 'bg-emerald-700 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-emerald-800 hover:bg-emerald-50 font-semibold' }}"
                        >
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            @endisset
        </div>
    </header>

    <!-- Slide-Over Navigation Drawer (Mobile & Desktop) -->
    <div
        x-show="mobileNav"
        x-cloak
        @keydown.escape.window="mobileNav = false"
        class="fixed inset-0 z-50 flex"
        role="dialog"
        aria-modal="true"
        aria-label="Menu Navigasi Portal Internal"
    >
        <!-- Translucent Backdrop Scrim -->
        <div
            x-show="mobileNav"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/50 backdrop-blur-[2px]"
            @click="mobileNav = false"
            aria-hidden="true"
        ></div>

        <!-- Left Slide-Over Panel -->
        <div
            x-show="mobileNav"
            x-transition:enter="transition ease-out duration-250 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            @click.stop
            class="relative z-10 w-[86vw] max-w-[320px] h-full bg-white shadow-2xl flex flex-col border-r border-slate-200 overflow-hidden"
        >
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-4 h-16 border-b border-slate-200 shrink-0 bg-white">
                <a href="{{ $portalHomeRoute }}" @click="mobileNav = false" class="flex items-center gap-2.5 min-w-0">
                    <img src="/logo.png" alt="OmniPharma Logo" class="w-7 h-7 object-contain shrink-0">
                    <div class="flex flex-col leading-tight min-w-0">
                        <span class="text-sm font-extrabold text-slate-900 tracking-tight whitespace-nowrap">OmniPharma</span>
                        <span class="text-[11px] font-semibold text-emerald-700 truncate">{{ $portalSubtitle }}</span>
                    </div>
                </a>
                <button
                    type="button"
                    @click="mobileNav = false"
                    class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] -mr-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition cursor-pointer"
                    aria-label="Tutup menu navigasi"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable Drawer Body -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4 text-xs">
                @if(!empty($navItems))
                    <div class="space-y-1.5">
                        <p class="px-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Menu Portal Cabang</p>
                        <nav class="rounded-2xl border border-slate-200/80 bg-white divide-y divide-slate-100 overflow-hidden" aria-label="Navigasi Portal Mobile">
                            @foreach($navItems as $item)
                                <a
                                    href="{{ $item['href'] }}"
                                    @click="mobileNav = false"
                                    class="flex items-center justify-between gap-3 px-3.5 min-h-[44px] transition {{ !empty($item['active']) ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-800 hover:bg-slate-50 font-semibold' }}"
                                >
                                    <span class="truncate">{{ $item['label'] }}</span>
                                    @if(!empty($item['active']))
                                        <span class="w-2 h-2 rounded-full bg-emerald-700 shrink-0" aria-hidden="true"></span>
                                    @else
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endif

                {{-- Akses Cepat Navigasi Tambahan --}}
                <div class="space-y-1.5 pt-1">
                    <p class="px-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">Akses Cepat</p>
                    <nav class="rounded-2xl border border-slate-200/80 bg-white divide-y divide-slate-100 overflow-hidden">
                        <a
                            href="{{ route('dashboard') }}"
                            @click="mobileNav = false"
                            class="flex items-center justify-between gap-3 px-3.5 min-h-[44px] text-slate-800 hover:bg-slate-50 font-semibold transition"
                        >
                            <span class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span class="truncate">Katalog Apotek Publik</span>
                            </span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Drawer Footer: Quick Profile Info -->
            <div class="border-t border-slate-200 bg-slate-50/80 px-4 py-3 shrink-0 flex items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 min-w-0">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Foto {{ $userName }}" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-300">
                    @else
                        <div class="w-7 h-7 rounded-full bg-emerald-700 text-white flex items-center justify-center font-extrabold text-[10px] shadow-2xs shrink-0">
                            {{ $initials }}
                        </div>
                    @endif
                    <span class="font-bold text-slate-900 truncate">{{ $userName }}</span>
                </div>
                <a href="{{ route('profile.edit') }}" @click="mobileNav = false" class="text-emerald-700 font-bold hover:underline shrink-0 min-h-[44px] inline-flex items-center">
                    Profil &rarr;
                </a>
            </div>
        </div>
    </div>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <span class="font-bold text-slate-700">OmniPharma Network</span>
            <span>Sistem Informasi Farmasi Multi-Klien sesuai standar pelayanan Permenkes RI.</span>
        </div>
    </footer>

    @livewireScripts
    @fluxScripts
    @stack('scripts')
</body>
</html>
