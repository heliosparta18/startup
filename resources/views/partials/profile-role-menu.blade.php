@php
    $menuUser = auth()->user();
    $menuBranch = $branchSlug ?? null;
    $actions = $menuUser ? $menuUser->profileActions($menuBranch) : [];
    $isCustomerOnly = $menuUser && ! ($menuUser->hasRole('super_admin') || $menuUser->hasRole('apoteker') || $menuUser->hasRole('kasir'));
    $sectionHeadings = [
        'portal' => 'Portal Utama',
        'crud' => 'CRUD & Pengaturan',
        'audit' => 'Cek & Pengawasan',
        'pelanggan' => $isCustomerOnly ? 'Belanja & Pesanan' : 'Etalase Publik',
    ];
    $groupedActions = collect($actions)->groupBy('section');
@endphp

<div class="space-y-1">
    <a
        href="{{ route('profile.edit') }}"
        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-800 hover:text-slate-900 hover:bg-slate-100 font-semibold transition-colors min-h-[44px]"
        role="menuitem"
    >
        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
        <span>Profile &amp; Akses Akun</span>
    </a>

    <div class="{{ !empty($hideActionsOnMobile) ? 'hidden md:block space-y-1' : 'space-y-1' }}">
        @foreach($groupedActions as $sectionKey => $items)
            <div class="pt-1.5 mt-1 border-t border-slate-100 space-y-0.5">
                @if(!empty($sectionHeadings[$sectionKey]))
                    <p class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                        {{ $sectionHeadings[$sectionKey] }}
                    </p>
                @endif
                @foreach($items as $action)
                    <a
                        href="{{ $action['href'] }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors min-h-[44px] {{ !empty($action['primary']) ? 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold' : 'text-slate-800 hover:text-slate-900 hover:bg-slate-100 font-semibold' }}"
                        role="menuitem"
                    >
                        @include('partials.profile-role-icon', ['icon' => $action['icon'], 'class' => 'w-4 h-4 text-emerald-700 shrink-0'])
                        <span class="truncate">{{ $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
