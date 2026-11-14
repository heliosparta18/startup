<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row items-start gap-6">
        <!-- Menu Navigasi Pengaturan -->
        <aside class="w-full md:w-64 shrink-0">
            <nav aria-label="{{ __('Settings') }}" class="bg-white rounded-3xl border border-slate-200/90 p-3 shadow-xs space-y-1">
                <a
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-3.5 py-2.5 min-h-[44px] rounded-2xl text-xs sm:text-sm font-bold transition {{ request()->routeIs('profile.edit') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>{{ __('Profile') }}</span>
                </a>

                <a
                    href="{{ route('security.edit') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-3.5 py-2.5 min-h-[44px] rounded-2xl text-xs sm:text-sm font-bold transition {{ request()->routeIs('security.edit') ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-700 hover:bg-slate-50' }}"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>{{ __('Security') }}</span>
                </a>

                @if(\Illuminate\Support\Facades\Route::has('pesanan.riwayat'))
                    <a
                        href="{{ route('pesanan.riwayat') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 min-h-[44px] rounded-2xl text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 transition"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Riwayat Pesanan</span>
                    </a>
                @endif
            </nav>
        </aside>

        <!-- Konten Pengaturan -->
        <div class="flex-1 w-full min-w-0">
            <div class="mb-4">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $heading ?? '' }}</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ $subheading ?? '' }}</p>
            </div>

            <div class="w-full space-y-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
