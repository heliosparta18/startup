{{-- Empty State (Antislop R-27 Compliant, No Em Dashes R-02 Compliant) --}}
<div class="col-span-full py-12 px-4 text-center bg-white rounded-2xl border border-slate-200">
    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
        </svg>
    </div>
    <h3 class="text-base font-bold text-slate-900 mb-1">
        Produk Tidak Ditemukan
    </h3>
    <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto mb-5 leading-relaxed">
        Kami tidak menemukan produk yang cocok dengan kata kunci atau filter yang Anda pilih. Silakan periksa ejaan atau bersihkan filter pencarian.
    </p>
    <button
        type="button"
        wire:click="resetFilters"
        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm transition-colors cursor-pointer min-h-[44px] shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
    >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Reset Semua Filter
    </button>
</div>
