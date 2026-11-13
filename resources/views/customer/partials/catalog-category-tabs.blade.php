{{-- Top Category Navigation Tabs (Green-and-White Theme) --}}
@php
    $categories = [
        [
            'name' => 'Obat',
            'slug' => 'obat',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
        ],
        [
            'name' => 'Suplemen',
            'slug' => 'suplemen',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
        ],
        [
            'name' => 'Nutrisi',
            'slug' => 'nutrisi',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
        ],
        [
            'name' => 'Herbal',
            'slug' => 'herbal',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        ],
        [
            'name' => 'Ibu & Anak',
            'slug' => 'ibu-anak',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        [
            'name' => 'Alat Kesehatan',
            'slug' => 'alat-kesehatan',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        ],
        [
            'name' => 'Perawatan Diri',
            'slug' => 'perawatan-diri',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>',
        ],
    ];
@endphp

<div class="w-full bg-white border-b border-slate-200">
    <div class="flex items-center gap-2 sm:gap-4 overflow-x-auto no-scrollbar scroll-smooth snap-x py-3 px-1 sm:px-2">
        @foreach ($categories as $cat)
            @php
                $isActive = (strtolower($kategoriAktif ?: 'obat') === strtolower($cat['name']));
            @endphp
            <button
                type="button"
                wire:click="selectCategory('{{ $cat['name'] }}')"
                class="flex flex-col items-center gap-1.5 min-w-[76px] sm:min-w-[88px] py-2 px-2.5 rounded-xl transition-all duration-200 cursor-pointer min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 snap-start group"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                aria-label="Kategori {{ $cat['name'] }}"
            >
                <div class="w-12 h-12 rounded-full flex items-center justify-center transition-transform group-hover:scale-105 {{ $isActive ? 'bg-emerald-100 text-emerald-800 border-2 border-emerald-600 shadow-sm' : 'bg-slate-100 text-slate-600 border border-slate-200 group-hover:bg-emerald-50 group-hover:text-emerald-700' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $cat['icon'] !!}
                    </svg>
                </div>
                <span class="text-xs font-semibold whitespace-nowrap {{ $isActive ? 'text-emerald-800 font-bold' : 'text-slate-700 group-hover:text-emerald-700' }}">
                    {{ $cat['name'] }}
                </span>
                <span class="h-1 w-8 rounded-full transition-all duration-200 {{ $isActive ? 'bg-emerald-600' : 'bg-transparent' }}"></span>
            </button>
        @endforeach
    </div>
</div>
