<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diprella - Authentication</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page-body">

    <!-- Decorative Corner Background Shapes -->
    <div class="bg-accent-yellow" aria-hidden="true"></div>
    <div class="bg-accent-coral" aria-hidden="true"></div>

    <!-- Active User State Badge (Appears when logged in with Firebase) -->
    <div id="logged-in-badge" class="logged-in-modal-card" style="display: none;">
        <div class="relative">
            <img id="user-avatar" src="" alt="User Avatar" class="w-12 h-12 rounded-full border-2 border-emerald-500 object-cover hidden">
            <div id="user-avatar-placeholder" class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-lg shadow-md">
                U
            </div>
            <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
        </div>
        <div class="flex flex-col">
            <div class="flex items-center gap-2">
                <span id="user-display-name" class="font-bold text-gray-800 text-sm leading-tight">Pengguna</span>
                <span id="user-provider-tag" class="text-[10px] font-semibold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Firebase</span>
            </div>
            <span id="user-email-text" class="text-xs text-gray-500 leading-tight">user@example.com</span>
            <div class="mt-2 flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold underline">
                    Dashboard &rarr;
                </a>
                <span class="text-gray-300">|</span>
                <button id="firebase-logout-btn" type="button" class="text-xs text-rose-500 hover:text-rose-700 font-semibold cursor-pointer">
                    Keluar (Sign Out)
                </button>
            </div>
        </div>
    </div>

    <!-- Main Double Slider Auth Card -->
    <div class="auth-card-container {{ request()->routeIs('register') || request()->has('signup') ? 'right-panel-active' : '' }}" id="auth-card">

        <!-- ================= SIGN UP CONTAINER (Create Account) ================= -->
        <div class="auth-form-container auth-sign-up-container">
            <form id="firebase-signup-form" class="auth-form" action="#">
                <h1 class="auth-title">Create Account</h1>

                <!-- Social Sign Up Icons -->
                <div class="auth-social-container">
                    <button type="button" class="auth-social-btn" title="Sign up with Facebook">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.688 5H18V0h-3.808C10.595 0 9 1.583 9 4.615V8z"/>
                        </svg>
                    </button>
                    <button type="button" class="auth-social-btn btn-google-auth" title="Sign up with Google">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.04h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.04c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.27v3.13C3.27 21.36 7.35 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.28 14.28c-.25-.72-.38-1.49-.38-2.28s.13-1.56.38-2.28V6.59H1.27C.46 8.21 0 10.05 0 12s.46 3.79 1.27 5.41l4.01-3.13z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.27 2.64 1.27 6.59l4.01 3.13c.95-2.83 3.6-4.97 6.72-4.97z"/>
                        </svg>
                    </button>
                    <button type="button" class="auth-social-btn" title="Sign up with LinkedIn">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z"/>
                        </svg>
                    </button>
                </div>

                <span class="auth-subtitle">or use your email for registration:</span>

                <!-- Name Input -->
                <div class="auth-input-group">
                    <span class="auth-input-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <input id="signup-name" type="text" class="auth-input" placeholder="Name" required autocomplete="name" />
                </div>

                <!-- Email Input -->
                <div class="auth-input-group">
                    <span class="auth-input-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input id="signup-email" type="email" class="auth-input" placeholder="Email" required autocomplete="email" />
                </div>

                <!-- Password Input -->
                <div class="auth-input-group">
                    <span class="auth-input-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input id="signup-password" type="password" class="auth-input" placeholder="Password" required autocomplete="new-password" />
                </div>

                <button type="submit" class="auth-btn-primary mt-4">
                    SIGN UP
                </button>

                <!-- Mobile switch link -->
                <p class="mobile-switch-link" id="mobile-to-signin">
                    Already have an account? <span class="underline">Sign in</span>
                </p>
            </form>
        </div>

        <!-- ================= SIGN IN CONTAINER ================= -->
        <div class="auth-form-container auth-sign-in-container">
            <form id="firebase-signin-form" class="auth-form" action="#">
                <h1 class="auth-title">Sign In</h1>

                <!-- Social Sign In Icons -->
                <div class="auth-social-container">
                    <button type="button" class="auth-social-btn" title="Sign in with Facebook">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.688 5H18V0h-3.808C10.595 0 9 1.583 9 4.615V8z"/>
                        </svg>
                    </button>
                    <button type="button" class="auth-social-btn btn-google-auth" title="Sign in with Google">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.04h3.88c2.27-2.09 3.665-5.17 3.665-9.14z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.04c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.27v3.13C3.27 21.36 7.35 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.28 14.28c-.25-.72-.38-1.49-.38-2.28s.13-1.56.38-2.28V6.59H1.27C.46 8.21 0 10.05 0 12s.46 3.79 1.27 5.41l4.01-3.13z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.35 0 3.27 2.64 1.27 6.59l4.01 3.13c.95-2.83 3.6-4.97 6.72-4.97z"/>
                        </svg>
                    </button>
                    <button type="button" class="auth-social-btn" title="Sign in with LinkedIn">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                            <path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z"/>
                        </svg>
                    </button>
                </div>

                <span class="auth-subtitle">or use your email account:</span>

                <!-- Email or Username Input -->
                <div class="auth-input-group">
                    <span class="auth-input-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input id="signin-email" type="email" class="auth-input" placeholder="Email" required autocomplete="email" />
                </div>

                <!-- Password Input -->
                <div class="auth-input-group">
                    <span class="auth-input-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input id="signin-password" type="password" class="auth-input" placeholder="Password" required autocomplete="current-password" />
                </div>

                <!-- Forgot Password Link -->
                <a id="forgot-password-link" href="#" class="auth-forgot-link">Forgot your password?</a>

                <button type="submit" class="auth-btn-primary">
                    SIGN IN
                </button>

                <!-- Mobile switch link -->
                <p class="mobile-switch-link" id="mobile-to-signup">
                    Don't have an account? <span class="underline">Create Account</span>
                </p>
            </form>
        </div>

        <!-- ================= SLIDING OVERLAY CONTAINER ================= -->
        <div class="auth-overlay-container">
            <div class="auth-overlay">

                <!-- Geometric floating decor shapes -->
                <div class="overlay-shape shape-1"></div>
                <div class="overlay-shape shape-2"></div>
                <div class="overlay-shape shape-3"></div>

                <!-- Diprella Brand Logo -->
                <div class="auth-brand-logo">
                    <span class="auth-brand-icon">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="4" />
                            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                            <path d="M8.5 10v5.5a2 2 0 002 2h5" />
                            <circle cx="15.5" cy="17.5" r="1.5" fill="currentColor"/>
                        </svg>
                    </span>
                    <span>Diprella</span>
                </div>

                <!-- Overlay Panel LEFT (Seen when Sign Up is active, like in the screenshot!) -->
                <div class="auth-overlay-panel auth-overlay-left">
                    <h1 class="auth-overlay-title">Welcome Back!</h1>
                    <p class="auth-overlay-desc">
                        To keep connected with us please login with your personal info
                    </p>
                    <button class="auth-btn-ghost" id="signIn">
                        SIGN IN
                    </button>
                </div>

                <!-- Overlay Panel RIGHT (Seen when Sign In is active) -->
                <div class="auth-overlay-panel auth-overlay-right">
                    <h1 class="auth-overlay-title">Hello, Friend!</h1>
                    <p class="auth-overlay-desc">
                        Enter your personal details and start journey with us
                    </p>
                    <button class="auth-btn-ghost" id="signUp">
                        SIGN UP
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- Mobile Switch Script Fallback -->
    <script>
        document.getElementById('mobile-to-signin')?.addEventListener('click', function() {
            document.getElementById('auth-card')?.classList.remove('right-panel-active');
        });
        document.getElementById('mobile-to-signup')?.addEventListener('click', function() {
            document.getElementById('auth-card')?.classList.add('right-panel-active');
        });
    </script>
</body>
</html>
