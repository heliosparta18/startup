{{-- Product Card (Green-and-White Theme, Antislop R-02, R-03 Compliant) --}}
@php
    $badgeStatus = $obat->badge_status ?? '';
    $isKeras = str_contains(strtolower($badgeStatus), 'keras') || ($obat->kategori->wajib_resep ?? false);
    $isBebasTerbatas = str_contains(strtolower($badgeStatus), 'terbatas') || str_contains(strtolower($obat->kategori->nama_kategori ?? ''), 'terbatas');
    $isBebas = str_contains(strtolower($badgeStatus), 'bebas') && ! $isBebasTerbatas;
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between hover:border-emerald-400 hover:shadow-md transition-all duration-200 relative group">
    {{-- BPOM Regulatory Badge (Top Right) --}}
    <div class="absolute top-2.5 right-2.5 z-10">
        @if ($isKeras)
            <div class="w-5 h-5 rounded-full bg-rose-600 border border-black flex items-center justify-center text-white font-extrabold text-[10px] shadow-2xs" title="Obat Keras (Wajib Resep)">
                K
            </div>
        @elseif ($isBebasTerbatas)
            <div class="w-5 h-5 rounded-full bg-sky-600 border border-black flex items-center justify-center text-white font-bold text-[9px] shadow-2xs" title="Obat Bebas Terbatas">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500 border border-white"></span>
            </div>
        @elseif ($isBebas)
            <div class="w-5 h-5 rounded-full bg-emerald-600 border border-black flex items-center justify-center text-white font-bold text-[9px] shadow-2xs" title="Obat Bebas">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 border border-white"></span>
            </div>
        @elseif ($badgeStatus)
            <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ $badgeStatus }}
            </span>
        @endif
    </div>

    <div>
        {{-- Product Packshot Image --}}
        <a href="{{ route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}" class="block aspect-square w-full rounded-lg bg-slate-50/60 p-2 overflow-hidden mb-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <img
                src="{{ $obat->url_gambar }}"
                alt="{{ $obat->nama_obat }}"
                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
            />
        </a>

        {{-- Subcategory & Manufacturer Metadata --}}
        <div class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wider truncate mb-1">
            {{ $obat->subkategori->nama_subkategori ?? ($obat->kategori->nama_kategori ?? 'Kesehatan') }}
        </div>

        {{-- Product Title --}}
        <h4 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-2 min-h-[2.5rem] group-hover:text-emerald-700 transition-colors uppercase tracking-tight">
            <a href="{{ route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}" class="hover:underline focus:outline-none focus:text-emerald-700">
                {{ $obat->nama_obat }}
            </a>
        </h4>

        {{-- Packaging / Sediaan Unit --}}
        <p class="text-[11px] text-slate-500 font-medium truncate mt-1">
            {{ $obat->satuan_kemasan ?: $obat->jenis_sediaan }}
        </p>
    </div>

    {{-- Price and Action Area --}}
    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2">
        <div>
            <span class="block text-[10px] text-slate-400 font-medium">Harga Resmi</span>
            <span class="text-sm sm:text-base font-black text-emerald-800">
                Rp {{ number_format($obat->harga, 0, ',', '.') }}
            </span>
        </div>

        <a
            href="{{ route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}"
            class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-800 hover:text-white font-semibold text-xs transition-colors duration-150 min-h-[44px] min-w-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 cursor-pointer"
            aria-label="Lihat detail {{ $obat->nama_obat }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
