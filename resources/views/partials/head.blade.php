<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    window.omniGetTheme = function() {
        try {
            const saved = localStorage.getItem('omni_theme') || localStorage.getItem('theme') || localStorage.getItem('flux.appearance');
            return saved === 'dark' ? 'dark' : 'light';
        } catch (e) {
            return 'light';
        }
    };
    window.omniApplyTheme = function(mode) {
        const theme = mode === 'dark' ? 'dark' : 'light';
        document.documentElement.classList.remove('light', 'dark');
        document.documentElement.classList.add(theme);
        document.documentElement.style.colorScheme = theme;
        try {
            localStorage.setItem('omni_theme', theme);
            localStorage.setItem('theme', theme);
            localStorage.setItem('flux.appearance', theme);
        } catch (e) {}
        return theme;
    };
    window.omniSetTheme = function(mode) {
        const applied = window.omniApplyTheme(mode);
        window.dispatchEvent(new CustomEvent('omni-theme-changed', { detail: { theme: applied } }));
        return applied;
    };
    window.omniApplyTheme(window.omniGetTheme());
</script>
