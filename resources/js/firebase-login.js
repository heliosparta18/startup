import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import {
    getAuth,
    signInWithEmailAndPassword,
    createUserWithEmailAndPassword,
    signInWithPopup,
    GoogleAuthProvider,
    updateProfile,
    signOut,
    onAuthStateChanged,
    sendPasswordResetEmail
} from "firebase/auth";

// Firebase Configuration provided by user
const firebaseConfig = {
    apiKey: "AIzaSyAri_aIsnfiCosT3B0Dqq42fki5cHrmecs",
    authDomain: "ennchant-rogter.firebaseapp.com",
    projectId: "ennchant-rogter",
    storageBucket: "ennchant-rogter.firebasestorage.app",
    messagingSenderId: "272395562120",
    appId: "1:272395562120:web:cca0ce8a6b21734f69833d",
    measurementId: "G-ZC43V8NV93"
};

// Initialize Firebase App & Services
export const app = initializeApp(firebaseConfig);
let analytics = null;
try {
    analytics = getAnalytics(app);
} catch (e) {
    console.warn("Analytics not initialized in non-browser or local environment", e);
}

export const auth = getAuth(app);
export const googleProvider = new GoogleAuthProvider();
googleProvider.setCustomParameters({
    prompt: 'select_account'
});

// Toast Notification Helper
export function showToast(message, type = 'info') {
    let container = document.getElementById('firebase-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'firebase-toast-container';
        container.className = 'fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `transform transition-all duration-300 ease-out translate-y-2 opacity-0 pointer-events-auto flex items-center gap-3 px-5 py-4 rounded-xl shadow-xl text-white font-medium text-sm max-w-md ${
        type === 'success' 
            ? 'bg-emerald-600 border border-emerald-500 shadow-emerald-500/20' 
            : type === 'error'
            ? 'bg-rose-600 border border-rose-500 shadow-rose-500/20'
            : 'bg-teal-700 border border-teal-600 shadow-teal-500/20'
    }`;

    const icon = type === 'success' 
        ? '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
        : type === 'error'
        ? '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
        : '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';

    toast.innerHTML = `
        ${icon}
        <div class="flex-1 text-sm leading-snug">${message}</div>
        <button type="button" class="text-white/80 hover:text-white ml-2 text-base">&times;</button>
    `;

    toast.querySelector('button').onclick = () => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    };

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', 'translate-y-2');
    });

    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }
    }, 4500);
}

// User-friendly error message translator
function formatFirebaseError(error) {
    const code = error.code || '';
    switch (code) {
        case 'auth/invalid-credential':
        case 'auth/wrong-password':
        case 'auth/user-not-found':
            return 'Email atau password yang Anda masukkan salah.';
        case 'auth/email-already-in-use':
            return 'Email ini sudah terdaftar. Silakan login atau gunakan email lain.';
        case 'auth/weak-password':
            return 'Password terlalu lemah. Minimal gunakan 6 karakter.';
        case 'auth/invalid-email':
            return 'Format email tidak valid. Periksa kembali alamat email Anda.';
        case 'auth/popup-closed-by-user':
            return 'Jendela login Google ditutup sebelum proses selesai.';
        case 'auth/network-request-failed':
            return 'Gagal terhubung ke jaringan. Periksa koneksi internet Anda.';
        case 'auth/user-disabled':
            return 'Akun ini telah dinonaktifkan. Hubungi administrator.';
        case 'auth/too-many-requests':
            return 'Terlalu banyak percobaan gagal. Silakan coba lagi beberapa saat lagi.';
        default:
            return error.message || 'Terjadi kesalahan pada proses autentikasi.';
    }
}

// Set button loading state
function setButtonLoading(button, isLoading, originalText) {
    if (!button) return;
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>Memproses...</span>
        `;
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || originalText;
    }
}

// Initialize Auth Events and State Listeners
export function initFirebaseAuthUI() {
    const authCard = document.getElementById('auth-card');
    const signUpBtn = document.getElementById('signUp');
    const signInBtn = document.getElementById('signIn');

    // Panel sliding toggles
    if (signUpBtn && authCard) {
        signUpBtn.addEventListener('click', (e) => {
            e.preventDefault();
            authCard.classList.add('right-panel-active');
            history.replaceState(null, '', '#signup');
        });
    }

    if (signInBtn && authCard) {
        signInBtn.addEventListener('click', (e) => {
            e.preventDefault();
            authCard.classList.remove('right-panel-active');
            history.replaceState(null, '', '#signin');
        });
    }

    // Auto-toggle based on hash or URL
    if (window.location.hash === '#signup' || window.location.pathname.includes('register')) {
        authCard?.classList.add('right-panel-active');
    }

    // Sign Up Form Handler (Create Account)
    const signUpForm = document.getElementById('firebase-signup-form');
    if (signUpForm) {
        signUpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = signUpForm.querySelector('button[type="submit"]');
            const name = document.getElementById('signup-name')?.value.trim();
            const email = document.getElementById('signup-email')?.value.trim();
            const password = document.getElementById('signup-password')?.value;

            if (!name) {
                showToast('Mohon masukkan nama atau username Anda.', 'error');
                return;
            }
            if (!email) {
                showToast('Mohon masukkan alamat email Anda.', 'error');
                return;
            }
            if (!password || password.length < 6) {
                showToast('Password minimal harus 6 karakter.', 'error');
                return;
            }

            setButtonLoading(submitBtn, true);

            try {
                const userCredential = await createUserWithEmailAndPassword(auth, email, password);
                const user = userCredential.user;

                // Update Display Name
                await updateProfile(user, {
                    displayName: name
                });

                showToast(`Akun berhasil dibuat! Selamat datang, ${name}!`, 'success');
                signUpForm.reset();
            } catch (error) {
                console.error("Firebase Sign Up Error:", error);
                showToast(formatFirebaseError(error), 'error');
            } finally {
                setButtonLoading(submitBtn, false);
            }
        });
    }

    // Sign In Form Handler (Email & Password)
    const signInForm = document.getElementById('firebase-signin-form');
    if (signInForm) {
        signInForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = signInForm.querySelector('button[type="submit"]');
            let email = document.getElementById('signin-email')?.value.trim();
            const password = document.getElementById('signin-password')?.value;

            if (!email) {
                showToast('Mohon masukkan email atau username Anda.', 'error');
                return;
            }
            if (!password) {
                showToast('Mohon masukkan password Anda.', 'error');
                return;
            }

            // If user enters username without @, prompt them or handle formatting
            if (!email.includes('@')) {
                showToast('Untuk login Firebase, silakan masukkan alamat email yang terdaftar (contoh: user@domain.com).', 'error');
                return;
            }

            setButtonLoading(submitBtn, true);

            try {
                const userCredential = await signInWithEmailAndPassword(auth, email, password);
                const user = userCredential.user;
                const displayName = user.displayName || user.email.split('@')[0];

                showToast(`Selamat datang kembali, ${displayName}!`, 'success');
                signInForm.reset();
            } catch (error) {
                console.error("Firebase Sign In Error:", error);
                showToast(formatFirebaseError(error), 'error');
            } finally {
                setButtonLoading(submitBtn, false);
            }
        });
    }

    // Google Sign-In Handlers (attach to all google login buttons)
    const googleButtons = document.querySelectorAll('.btn-google-auth');
    googleButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                btn.style.opacity = '0.6';
                const result = await signInWithPopup(auth, googleProvider);
                const user = result.user;
                const displayName = user.displayName || user.email;
                showToast(`Login Google berhasil! Halo, ${displayName}!`, 'success');
            } catch (error) {
                console.error("Firebase Google Auth Error:", error);
                showToast(formatFirebaseError(error), 'error');
            } finally {
                btn.style.opacity = '1';
            }
        });
    });

    // Forgot Password Link Handler
    const forgotPasswordLink = document.getElementById('forgot-password-link');
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', async (e) => {
            e.preventDefault();
            const currentEmail = document.getElementById('signin-email')?.value.trim();
            const email = prompt("Masukkan email Anda untuk reset password:", currentEmail || "");
            
            if (!email) return;

            if (!email.includes('@')) {
                showToast('Format email tidak valid!', 'error');
                return;
            }

            try {
                await sendPasswordResetEmail(auth, email);
                showToast(`Link reset password telah dikirim ke ${email}. Silakan cek kotak masuk atau folder spam Anda.`, 'success');
            } catch (error) {
                console.error("Reset Password Error:", error);
                showToast(formatFirebaseError(error), 'error');
            }
        });
    }

    // Firebase Auth State Listener
    onAuthStateChanged(auth, (user) => {
        updateLoggedInUI(user);
    });

    // Log Out Button Handler
    const logoutBtn = document.getElementById('firebase-logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await signOut(auth);
                showToast('Anda telah berhasil keluar (Sign Out).', 'info');
            } catch (error) {
                console.error("Sign Out Error:", error);
                showToast('Gagal sign out: ' + error.message, 'error');
            }
        });
    }
}

// Update UI based on logged in user
function updateLoggedInUI(user) {
    const userModal = document.getElementById('logged-in-badge');
    const authCardWrapper = document.getElementById('auth-card-wrapper');
    const userPhoto = document.getElementById('user-avatar');
    const userName = document.getElementById('user-display-name');
    const userEmail = document.getElementById('user-email-text');
    const userProvider = document.getElementById('user-provider-tag');

    if (user) {
        if (userModal) {
            userModal.style.display = 'flex';
            userModal.classList.remove('hidden');
        }
        if (userName) userName.textContent = user.displayName || user.email.split('@')[0];
        if (userEmail) userEmail.textContent = user.email;
        
        if (userPhoto) {
            if (user.photoURL) {
                userPhoto.src = user.photoURL;
                userPhoto.classList.remove('hidden');
                document.getElementById('user-avatar-placeholder')?.classList.add('hidden');
            } else {
                userPhoto.classList.add('hidden');
                const placeholder = document.getElementById('user-avatar-placeholder');
                if (placeholder) {
                    placeholder.classList.remove('hidden');
                    const initial = (user.displayName || user.email || 'U').charAt(0).toUpperCase();
                    placeholder.textContent = initial;
                }
            }
        }

        if (userProvider) {
            const isGoogle = user.providerData.some(p => p.providerId === 'google.com');
            userProvider.textContent = isGoogle ? 'Google Account' : 'Email/Password';
        }
    } else {
        if (userModal) {
            userModal.style.display = 'none';
            userModal.classList.add('hidden');
        }
    }
}

// Auto-run when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFirebaseAuthUI);
} else {
    initFirebaseAuthUI();
}
