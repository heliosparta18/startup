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
            <h1 class="text-5xl sm:text-7xl md:text-8xl font-black tracking-tight text-emerald-600 leading-none">
                Welcome, <span class="text-emerald-700">{{ $welcomeUser }}</span>
            </h1>

            <p class="mt-4 text-xs sm:text-sm font-semibold uppercase tracking-widest text-slate-400">
                Menyiapkan Dashboard Apotek Anda...
            </p>
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
