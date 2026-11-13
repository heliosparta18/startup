{{-- Product Card (GoApotik Authentic Layout, Antislop R-02, R-03 Compliant) --}}
@php
    $badgeStatus = $obat->badge_status ?? '';
    $isKeras = str_contains(strtolower($badgeStatus), 'keras') || ($obat->kategori->wajib_resep ?? false);
    $isBebasTerbatas = str_contains(strtolower($badgeStatus), 'terbatas') || str_contains(strtolower($obat->kategori->nama_kategori ?? ''), 'terbatas');
    $isBebas = str_contains(strtolower($badgeStatus), 'bebas') && ! $isBebasTerbatas;
    $urlGambar = $obat->url_gambar ?: asset('images/placeholder-product.svg');
    $namaObat = $obat->nama_obat;
    $isAvailableHere = $obat->isAvailableInBranch($client);
    $altBranch = ! $isAvailableHere ? $obat->getAlternativeAvailableBranch($client) : null;
@endphp

<div class="bg-white rounded-xl border border-slate-200/90 p-2.5 sm:p-3.5 flex flex-col justify-between hover:border-emerald-500 hover:shadow-md transition-all duration-200 relative group">
    {{-- BPOM Official Regulatory Badge (Top Right) --}}
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
        {{-- Product Packshot Image Container with Safe Fallback --}}
        <a href="{{ route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}" class="block aspect-square w-full rounded-lg bg-slate-50/50 p-2 overflow-hidden mb-2 relative group-hover:bg-slate-50 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <img
                src="{{ $urlGambar }}"
                alt="{{ $namaObat }}"
                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
            />
            {{-- GoApotik Style Micro-Watermark --}}
            <div class="absolute bottom-1 left-2 right-2 flex items-center justify-center opacity-70 group-hover:opacity-100 transition-opacity">
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded bg-white/90 text-slate-500 border border-slate-200/80 shadow-2xs">
                    OmniPharma
                </span>
            </div>
        </a>

        {{-- Subcategory & Manufacturer Metadata --}}
        <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider truncate mb-1">
            {{ $obat->subkategori->nama_subkategori ?? ($obat->kategori->nama_kategori ?? 'Kesehatan') }}
        </div>

        {{-- Product Title (2 Lines Clamped) --}}
        <h4 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-2 min-h-[2.5rem] group-hover:text-emerald-700 transition-colors uppercase tracking-tight leading-snug">
            <a href="{{ route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}" class="hover:underline focus:outline-none focus:text-emerald-700">
                {{ $namaObat }}
            </a>
        </h4>

        {{-- Packaging / Sediaan Unit --}}
        <p class="text-[11px] text-slate-500 font-medium truncate mt-1">
            {{ $obat->satuan_kemasan ?: $obat->jenis_sediaan }}
        </p>

        {{-- Multi-Branch Stock Availability Indicator --}}
        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[10px]">
            @if ($isAvailableHere)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $obat->branch_availability_text }}</span>
                </span>
            @elseif ($altBranch)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-semibold bg-amber-50 text-amber-800 border border-amber-200" title="Kosong di cabang ini">
                    <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $obat->branch_availability_text }}</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    <span>Stok Habis di Semua Cabang</span>
                </span>
            @endif
        </div>
    </div>

    {{-- Price and Action Area --}}
    <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-col gap-2">
        <div class="flex items-center justify-between gap-2">
            <div>
                <span class="block text-[10px] text-slate-400 font-medium">Harga Resmi</span>
                <span class="text-sm sm:text-base font-black text-emerald-800">
                    Rp {{ number_format($obat->harga, 0, ',', '.') }}
                </span>
            </div>

            <a
                href="{{ route('customer.obat.detail', ['client' => $client, 'id' => $obat->id_obat]) }}"
                class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-800 hover:text-white font-semibold text-xs transition-colors duration-150 min-h-[44px] min-w-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 cursor-pointer"
                aria-label="Lihat detail {{ $namaObat }}"
                title="Lihat Detail Produk"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>

        @if (! $isAvailableHere && $altBranch)
            <button
                type="button"
                wire:click="switchBranch('{{ $altBranch->slug }}')"
                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs shadow-xs transition-colors duration-150 min-h-[44px] cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
            >
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Beralih ke {{ preg_replace('/^Cabang\s+/i', '', $altBranch->nama_client) }}</span>
            </button>
        @endif
    </div>
</div>
