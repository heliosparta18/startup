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
                setTimeout(() => $el.remove(), 400);
            }, 2600);
        "
        class="animate-welcome-overlay fixed inset-0 z-[999999] flex flex-col items-center justify-center bg-white select-none px-6 text-center"
        style="background-color: #ffffff;"
        aria-live="polite"
        role="status"
    >
        <div class="animate-welcome-text flex flex-col items-center justify-center max-w-4xl mx-auto">
            <!-- Brand Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/80 mb-5 text-xs sm:text-sm font-bold tracking-wider uppercase shadow-2xs">
                <img src="/logo.png" alt="Logo" class="w-4 h-4 object-contain">
                <span>OmniPharma Network</span>
            </div>

            <!-- Big Green Welcome Text as requested by user -->
            <h1 class="text-3xl sm:text-6xl md:text-7xl font-black tracking-tight text-emerald-600 leading-tight break-words max-w-full">
                Welcome, <span class="text-emerald-700">{{ $welcomeUser }}</span>
            </h1>

            <p class="mt-4 text-xs sm:text-sm font-semibold uppercase tracking-widest text-slate-500">
                Menyiapkan Dashboard Apotek Anda...
            </p>

            <button
                type="button"
                @click="visible = false"
                class="mt-6 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-600 transition min-h-[40px] cursor-pointer"
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
                    }, 2800);
                }
            })();
        </script>
    </div>
@endif
