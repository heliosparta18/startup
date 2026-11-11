<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('light');
    try {
        localStorage.setItem('flux.appearance', 'light');
        localStorage.setItem('theme', 'light');
    } catch (e) {}
    if (window.MutationObserver) {
        new MutationObserver(function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }
</script>
