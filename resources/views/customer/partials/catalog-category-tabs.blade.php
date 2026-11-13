{{-- Top Category Navigation Tabs (GoApotik Authentic Layout) --}}
@php
    $categories = [
        [
            'name' => 'Obat',
            'slug' => 'obat',
            'badge_bg' => 'bg-sky-50 text-sky-600 border-sky-200 group-hover:bg-sky-100',
            'active_border' => 'border-sky-600 text-sky-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
        ],
        [
            'name' => 'Suplemen',
            'slug' => 'suplemen',
            'badge_bg' => 'bg-amber-50 text-amber-600 border-amber-200 group-hover:bg-amber-100',
            'active_border' => 'border-amber-600 text-amber-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
        ],
        [
            'name' => 'Nutrisi',
            'slug' => 'nutrisi',
            'badge_bg' => 'bg-orange-50 text-orange-600 border-orange-200 group-hover:bg-orange-100',
            'active_border' => 'border-orange-600 text-orange-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
        ],
        [
            'name' => 'Herbal',
            'slug' => 'herbal',
            'badge_bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200 group-hover:bg-emerald-100',
            'active_border' => 'border-emerald-600 text-emerald-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        ],
        [
            'name' => 'Ibu & Anak',
            'slug' => 'ibu-anak',
            'badge_bg' => 'bg-rose-50 text-rose-600 border-rose-200 group-hover:bg-rose-100',
            'active_border' => 'border-rose-600 text-rose-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        [
            'name' => 'Alat Kesehatan',
            'slug' => 'alat-kesehatan',
            'badge_bg' => 'bg-red-50 text-red-600 border-red-200 group-hover:bg-red-100',
            'active_border' => 'border-red-600 text-red-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        ],
        [
            'name' => 'Perawatan Diri',
            'slug' => 'perawatan-diri',
            'badge_bg' => 'bg-purple-50 text-purple-600 border-purple-200 group-hover:bg-purple-100',
            'active_border' => 'border-purple-600 text-purple-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>',
        ],
        [
            'name' => 'Mata',
            'slug' => 'mata',
            'badge_bg' => 'bg-teal-50 text-teal-600 border-teal-200 group-hover:bg-teal-100',
            'active_border' => 'border-teal-600 text-teal-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        ],
    ];
@endphp

<div class="w-full bg-white border-b border-slate-200 shadow-2xs">
    <div class="max-w-7xl mx-auto px-2 sm:px-6">
        <div class="flex items-center gap-1 sm:gap-3 overflow-x-auto no-scrollbar py-2 sm:py-3 justify-start lg:justify-center">
            @foreach ($categories as $cat)
                @php
                    $isActive = (strtolower($kategoriAktif ?: 'obat') === strtolower($cat['name']));
                @endphp
                <button
                    type="button"
                    wire:click="selectCategory('{{ $cat['name'] }}')"
                    class="relative flex flex-col items-center gap-1.5 min-w-[72px] sm:min-w-[84px] py-1.5 px-2 rounded-xl transition-all duration-150 cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 group shrink-0"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                    aria-label="Kategori {{ $cat['name'] }}"
                >
                    {{-- Circular Icon with Soft Colored Glow Matching GoApotik --}}
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full flex items-center justify-center border transition-all duration-200 {{ $cat['badge_bg'] }} {{ $isActive ? 'scale-105 shadow-sm ring-2 ring-emerald-500/40' : 'opacity-85 group-hover:opacity-100 group-hover:scale-105' }}">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $cat['icon'] !!}
                        </svg>
                    </div>

                    {{-- Category Label --}}
                    <span class="text-[11px] sm:text-xs font-semibold whitespace-nowrap tracking-tight transition-colors {{ $isActive ? 'text-emerald-800 font-extrabold' : 'text-slate-600 group-hover:text-slate-900' }}">
                        {{ $cat['name'] }}
                    </span>

                    {{-- Active Bottom Underline Indicator (GoApotik style) --}}
                    @if ($isActive)
                        <span class="absolute -bottom-2 sm:-bottom-3 h-0.5 sm:h-1 w-12 sm:w-16 rounded-full bg-emerald-600"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
