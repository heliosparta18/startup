@php
    $sidebarUser = auth()->user();
    $sidebarActions = $sidebarUser ? $sidebarUser->profileActions(session('active_branch') ?? session('active_client')) : [];
@endphp
<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row items-start gap-6">
        <!-- Menu Navigasi Pengaturan -->
        <aside class="w-full md:w-72 shrink-0">
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

                @if(count($sidebarActions) > 0)
                    <div class="pt-2 mt-2 border-t border-slate-100 space-y-1">
                        <p class="px-3.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Akses Portal Akun
                        </p>
                        @foreach($sidebarActions as $action)
                            <a
                                href="{{ $action['href'] }}"
                                class="flex items-center gap-2.5 px-3.5 py-2 min-h-[40px] rounded-xl text-xs font-bold transition {{ !empty($action['primary']) ? 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/70' : 'text-slate-700 hover:bg-slate-50' }}"
                            >
                                @include('partials.profile-role-icon', ['icon' => $action['icon'], 'class' => 'w-4 h-4 text-emerald-700 shrink-0'])
                                <span class="truncate">{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
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
