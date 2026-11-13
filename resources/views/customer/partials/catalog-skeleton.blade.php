{{-- Loading Skeleton (Antislop R-27 Compliant) --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5 sm:gap-4.5" aria-hidden="true">
    @for ($i = 0; $i < 10; $i++)
        <div class="bg-white rounded-xl border border-slate-200 p-3 sm:p-3.5 flex flex-col justify-between animate-pulse">
            <div>
                <div class="aspect-square w-full rounded-lg bg-slate-100 mb-2.5"></div>
                <div class="h-3 w-16 bg-slate-200 rounded mb-2"></div>
                <div class="h-4 w-full bg-slate-200 rounded mb-1.5"></div>
                <div class="h-4 w-3/4 bg-slate-200 rounded mb-2"></div>
                <div class="h-3 w-1/2 bg-slate-100 rounded"></div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                <div class="h-5 w-20 bg-slate-200 rounded"></div>
                <div class="w-8 h-8 rounded-lg bg-slate-100"></div>
            </div>
        </div>
    @endfor
</div>
