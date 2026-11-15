<script>
    (function() {
        if (!window.omniGetTheme) {
            window.omniGetTheme = function() {
                try {
                    const saved = localStorage.getItem('omni_theme') || localStorage.getItem('theme') || localStorage.getItem('flux.appearance');
                    if (saved === 'dark') return 'dark';
                } catch (e) {}
                return 'light';
            };
        }
        if (!window.omniApplyTheme) {
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
        }
        if (!window.omniSetTheme) {
            window.omniSetTheme = function(mode) {
                const applied = window.omniApplyTheme(mode);
                window.dispatchEvent(new CustomEvent('omni-theme-changed', { detail: { theme: applied } }));
                return applied;
            };
        }
        window.omniApplyTheme(window.omniGetTheme());
    })();
</script>
