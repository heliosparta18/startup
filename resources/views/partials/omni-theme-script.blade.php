<script>
    (function() {
        const isForceLight = @json((bool) ($forceLight ?? false));

        window.omniGetTheme = function() {
            if (isForceLight) {
                return 'light';
            }
            try {
                const saved = localStorage.getItem('omni_theme') || localStorage.getItem('theme') || localStorage.getItem('flux.appearance');
                return saved === 'dark' ? 'dark' : 'light';
            } catch (e) {
                return 'light';
            }
        };

        window.omniApplyTheme = function(mode) {
            const theme = isForceLight ? 'light' : (mode === 'dark' ? 'dark' : 'light');
            const root = document.documentElement;
            root.classList.remove('light', 'dark');
            root.classList.add(theme);
            root.style.colorScheme = theme;

            let themeMeta = document.querySelector('meta[name="theme-color"]');
            if (!themeMeta) {
                themeMeta = document.createElement('meta');
                themeMeta.name = 'theme-color';
                document.head.appendChild(themeMeta);
            }
            themeMeta.content = theme === 'dark' ? '#09090b' : '#f8fafc';

            if (isForceLight) {
                root.setAttribute('data-force-light', 'true');
                return 'light';
            }

            root.removeAttribute('data-force-light');
            try {
                localStorage.setItem('omni_theme', theme);
                localStorage.setItem('theme', theme);
                localStorage.setItem('flux.appearance', theme);
            } catch (e) {}
            return theme;
        };

        window.omniSetTheme = function(mode) {
            if (isForceLight) {
                return window.omniApplyTheme('light');
            }
            const applied = window.omniApplyTheme(mode);
            window.dispatchEvent(new CustomEvent('omni-theme-changed', { detail: { theme: applied } }));
            return applied;
        };

        window.omniApplyTheme(window.omniGetTheme());
    })();
</script>
