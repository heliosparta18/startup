@php
    $welcomeUser = session('welcome_user') ?? (request()->has('welcome') && auth()->check() ? auth()->user()->name : null);
@endphp

@if ($welcomeUser)
    <div
        id="omni-welcome-splash"
        x-data="{ visible: true }"
        x-show="visible"
        x-init="
            setTimeout(() => {
                visible = false;
                setTimeout(() => $el.remove(), 150);
            }, 400);
        "
        class="animate-welcome-overlay fixed inset-0 z-[999999] pointer-events-none flex flex-col items-start sm:items-center justify-center bg-white dark:bg-[#09090b] select-none px-5 sm:px-6 text-left sm:text-center transition-opacity duration-150"
        aria-live="polite"
        role="status"
    >
        <div class="animate-welcome-text pointer-events-auto flex flex-col items-start sm:items-center justify-center w-full max-w-md sm:max-w-4xl sm:mx-auto">
            <!-- Brand Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 dark:bg-[#07291f] text-emerald-800 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-500/40 mb-5 text-xs sm:text-sm font-bold tracking-wider uppercase shadow-2xs">
                <img src="/logo.png" alt="Logo" class="w-4 h-4 object-contain">
                <span>OmniPharma Network</span>
            </div>

            <!-- Big Green Welcome Text as requested by user -->
            <h1 class="text-2xl sm:text-5xl md:text-7xl font-black tracking-tight text-emerald-700 dark:text-emerald-400 leading-tight break-words max-w-full">
                Welcome, <span class="text-emerald-800 dark:text-emerald-300">{{ $welcomeUser }}</span>
            </h1>

            <p class="mt-4 text-xs sm:text-sm font-semibold uppercase tracking-widest text-slate-600 dark:text-zinc-300">
                Menyiapkan Dashboard Apotek Anda...
            </p>

            <button
                type="button"
                @click="visible = false"
                class="mt-6 inline-flex w-full sm:w-auto items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-500 bg-slate-50 dark:bg-[#141416] hover:bg-slate-100 dark:hover:bg-[#262626] text-xs sm:text-sm font-semibold text-slate-700 dark:text-zinc-100 transition min-h-[44px] cursor-pointer"
            >
                Lewati Sambutan &rarr;
            </button>
        </div>

        <script>
            (function() {
                var splash = document.getElementById('omni-welcome-splash');
                if (splash) {
                    setTimeout(function() {
                        if (splash && splash.parentNode) {
                            splash.parentNode.removeChild(splash);
                        }
                    }, 550);
                }
            })();
        </script>
    </div>
@endif
