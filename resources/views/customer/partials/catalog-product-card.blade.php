{{-- Product Card (GoApotik Regulatory SVG Badges, Prescription CTA & Clean White Surface) --}}
@php
    $badgeStatus = $obat->badge_status ?? '';
    $isKeras = str_contains(strtolower($badgeStatus), 'keras') || ($obat->kategori->wajib_resep ?? false);
    $drugBadge = \App\Modules\Catalog\Support\GoApotikCatalogAssets::drugClassBadge(
        $badgeStatus,
        (bool) ($obat->kategori->wajib_resep ?? false),
        $obat->kategori->nama_kategori ?? null
    );
    $urlGambar = $obat->url_gambar ?: asset('images/placeholder-product.svg');
    $namaObat = $obat->nama_obat;
    $isAvailableHere = $obat->isAvailableInBranch($client);
    $altBranch = ! $isAvailableHere ? $obat->getAlternativeAvailableBranch($client) : null;
@endphp

<div class="bg-white rounded-2xl border border-slate-200/90 p-2.5 sm:p-3.5 flex flex-col justify-between hover:border-emerald-700/60 hover:shadow-sm transition-all relative min-w-0">
    {{-- Official GoApotik Drug Class SVG Badge (Top Right) --}}
    <div class="absolute top-2.5 right-2.5 sm:top-3 sm:right-3 z-10" title="{{ $drugBadge['title'] }}">
        <img
            src="{{ $drugBadge['svg_url'] }}"
            alt="{{ $drugBadge['title'] }}"
            title="{{ $drugBadge['title'] }}"
            class="w-6 h-6 rounded-full object-contain shadow-2xs bg-white"
            loading="lazy"
            onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');"
        />
        <span class="hidden w-6 h-6 rounded-full {{ $isKeras ? 'bg-rose-700 text-white' : 'bg-emerald-700 text-white' }} border border-slate-900 flex items-center justify-center font-extrabold text-[11px]" title="{{ $drugBadge['title'] }}">
            {{ $drugBadge['label'] }}
        </span>
    </div>

    <div>
        {{-- Product Packshot Image Container --}}
        <a
            href="{{ \Illuminate\Support\Facades\Route::has('obat.show') ? route('obat.show', ['id' => $obat->slug ?: $obat->id_obat]) : route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}"
            class="block aspect-square w-full rounded-xl bg-slate-50/80 border border-slate-100 p-2.5 overflow-hidden mb-2.5 relative focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
        >
            <img
                src="{{ $urlGambar }}"
                alt="{{ $namaObat }}"
                class="w-full h-full object-contain transition-transform duration-200 hover:scale-105"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
            />
        </a>

        {{-- Category / Subcategory Metadata --}}
        <div class="text-[11px] font-bold text-emerald-800 truncate mb-1">
            {{ $obat->subkategori->nama_subkategori ?? ($obat->kategori->nama_kategori ?? 'Kesehatan') }}
        </div>

        {{-- Product Title --}}
        <h4 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-2 min-h-[2.5rem] leading-snug">
            <a
                href="{{ \Illuminate\Support\Facades\Route::has('obat.show') ? route('obat.show', ['id' => $obat->slug ?: $obat->id_obat]) : route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}"
                class="hover:text-emerald-800 hover:underline focus-visible:outline-2 focus-visible:outline-emerald-700 rounded"
            >
                {{ $namaObat }}
            </a>
        </h4>

        {{-- Packaging / Sediaan Unit --}}
        <p class="text-[11px] text-slate-600 font-medium truncate mt-1">
            {{ $obat->satuan_kemasan ?: $obat->jenis_sediaan }}
        </p>

        @if (! $isAvailableHere && ! $altBranch)
            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500" aria-hidden="true"></span>
                    <span>Stok Habis</span>
                </span>
            </div>
        @endif
    </div>

    {{-- Price and Action Area --}}
    <div class="mt-2.5 sm:mt-3 pt-2.5 border-t border-slate-100 flex flex-col gap-1.5 sm:gap-2">
        <div>
            <span class="block text-[11px] text-slate-500 font-medium leading-tight whitespace-nowrap">Harga Resmi</span>
            <p class="text-sm sm:text-base font-extrabold text-slate-900 whitespace-nowrap tracking-tight mt-0.5">
                Rp {{ number_format($obat->harga, 0, ',', '.') }}
            </p>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2">
            <a
                href="{{ \Illuminate\Support\Facades\Route::has('obat.show') ? route('obat.show', ['id' => $obat->slug ?: $obat->id_obat]) : route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}"
                class="flex-1 inline-flex items-center justify-center px-3 py-2 min-h-[40px] sm:min-h-[44px] rounded-xl bg-emerald-50 hover:bg-emerald-700 text-emerald-800 hover:text-white border border-emerald-200 font-bold text-xs transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 cursor-pointer"
                aria-label="Lihat detail {{ $namaObat }}"
            >
                Detail
            </a>

            @if ($isAvailableHere)
                <button
                    type="button"
                    @click.prevent="$dispatch('add-to-cart', { id: @js($obat->id_obat), nama: @js($namaObat), client: @js($client), qty: 1 })"
                    class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[40px] sm:min-h-[44px] min-w-[44px] rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-2xs transition-colors cursor-pointer shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                    title="Masukkan {{ $namaObat }} ke Keranjang"
                    aria-label="Masukkan {{ $namaObat }} ke Keranjang"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="font-extrabold">+</span>
                </button>
            @endif
        </div>

        @if (! $isAvailableHere && $altBranch)
            <button
                type="button"
                wire:click="switchBranch('{{ $altBranch->slug }}')"
                class="w-full inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-[11px] sm:text-xs leading-tight text-center transition-colors min-h-[44px] cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
            >
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Beralih ke {{ preg_replace('/^Cabang\s+/i', '', $altBranch->nama_client) }}</span>
            </button>
        @endif
    </div>
</div>
