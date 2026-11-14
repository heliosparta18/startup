{{-- Empty State (Antislop R-27 Compliant, No Em Dashes R-02 Compliant) --}}
<div class="col-span-full py-12 px-4 text-center bg-white rounded-2xl border border-slate-200" role="status">
    <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
        class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs sm:text-sm transition-colors cursor-pointer min-h-[44px] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
    >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Reset Semua Filter
    </button>
</div>
