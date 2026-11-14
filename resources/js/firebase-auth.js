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

// Konfigurasi Firebase resmi yang diberikan oleh pengguna
const firebaseConfig = {
    apiKey: "AIzaSyAri_aIsnfiCosT3B0Dqq42fki5cHrmecs",
    authDomain: "ennchant-rogter.firebaseapp.com",
    projectId: "ennchant-rogter",
    storageBucket: "ennchant-rogter.firebasestorage.app",
    messagingSenderId: "272395562120",
    appId: "1:272395562120:web:cca0ce8a6b21734f69833d",
    measurementId: "G-ZC43V8NV93"
};

// Inisialisasi Firebase App
export const app = initializeApp(firebaseConfig);

let analytics = null;
try {
    if (typeof window !== 'undefined') {
        analytics = getAnalytics(app);
    }
} catch (e) {
    // Analytics fallback jika diblokir ekstensi adblock / CSP
}

export const auth = getAuth(app);
export const googleProvider = new GoogleAuthProvider();
googleProvider.setCustomParameters({
    prompt: 'select_account'
});

// Toast notification helper
export function showToast(message, type = 'info') {
    let container = document.getElementById('firebase-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'firebase-toast-container';
        container.className = 'fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full px-4';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `transform transition-all duration-300 ease-out translate-y-2 opacity-0 pointer-events-auto flex items-center gap-3 px-4 py-3.5 rounded-xl shadow-lg text-white font-medium text-xs sm:text-sm ${
        type === 'success'
            ? 'bg-emerald-600 border border-emerald-500 shadow-emerald-500/20'
            : type === 'error'
            ? 'bg-rose-600 border border-rose-500 shadow-rose-500/20'
            : 'bg-slate-800 border border-slate-700 shadow-slate-900/20'
    }`;

    const icon = type === 'success'
        ? '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
        : type === 'error'
        ? '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
        : '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';

    toast.innerHTML = `
        ${icon}
        <div class="flex-1 leading-snug">${message}</div>
        <button type="button" class="text-white/80 hover:text-white ml-2 text-base leading-none">&times;</button>
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

// Error translator bahasa Indonesia
export function formatFirebaseError(error) {
    const code = error.code || '';
    switch (code) {
        case 'auth/invalid-credential':
        case 'auth/wrong-password':
        case 'auth/user-not-found':
            return 'Email atau kata sandi yang Anda masukkan salah.';
        case 'auth/email-already-in-use':
            return 'Email ini sudah terdaftar. Silakan masuk atau gunakan email lain.';
        case 'auth/weak-password':
            return 'Kata sandi terlalu singkat. Minimal gunakan 6 karakter.';
        case 'auth/invalid-email':
            return 'Format email tidak valid. Periksa kembali alamat email Anda.';
        case 'auth/popup-closed-by-user':
            return 'Jendela masuk Google ditutup sebelum proses selesai.';
        case 'auth/popup-blocked':
            return 'Pop-up browser diblokir. Izinkan pop-up untuk login Google.';
        case 'auth/unauthorized-domain':
            return 'Domain/host ini belum diotorisasi di Firebase Auth. Buka aplikasi via http://localhost:8088 atau daftarkan domain di Firebase Console.';
        case 'auth/cancelled-popup-request':
            return 'Proses masuk Google dibatalkan karena ada jendela baru yang dibuka.';
        case 'auth/internal-error':
            return 'Terjadi kendala internal koneksi Google. Silakan periksa jaringan dan coba lagi.';
        case 'auth/network-request-failed':
            return 'Gagal terhubung ke server Firebase. Periksa koneksi internet Anda.';
        case 'auth/user-disabled':
            return 'Akun ini telah dinonaktifkan oleh administrator.';
        case 'auth/too-many-requests':
            return 'Terlalu banyak percobaan gagal. Silakan coba lagi beberapa saat lagi.';
        default:
            return error.message || 'Terjadi kesalahan pada proses autentikasi.';
    }
}

// Ambil CSRF Token Laravel
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : '';
}

// Sinkronisasi sesi Firebase ke backend Laravel
export async function syncSessionWithBackend(firebaseUser, provider = 'password') {
    const csrfToken = getCsrfToken();
    const idToken = await firebaseUser.getIdToken();

    const response = await fetch('/auth/firebase-session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            uid: firebaseUser.uid,
            email: firebaseUser.email,
            name: firebaseUser.displayName || firebaseUser.email.split('@')[0],
            photo_url: firebaseUser.photoURL || null,
            provider: provider,
            id_token: idToken
        })
    });

    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Gagal menyinkronkan sesi ke server backend.');
    }

    return await response.json();
}

// Handler: Login dengan Email/Username & Password
export async function loginWithEmailOrUsername(identifier, password) {
    let email = identifier.trim();

    // Jika pengguna memasukkan username tanpa domain email, kita format dengan default domain
    if (!email.includes('@')) {
        // Coba email standar sesuai nama akun
        email = `${email.toLowerCase()}@omnipharma.test`;
    }

    const userCredential = await signInWithEmailAndPassword(auth, email, password);
    const user = userCredential.user;

    // Sinkronkan ke Laravel Session
    const sessionResult = await syncSessionWithBackend(user, 'password');
    return { user, session: sessionResult };
}

// Handler: Login Cepat dengan Akun Google
export async function loginWithGoogle() {
    const result = await signInWithPopup(auth, googleProvider);
    const user = result.user;

    // Sinkronkan ke Laravel Session
    const sessionResult = await syncSessionWithBackend(user, 'google');
    return { user, session: sessionResult };
}

// Handler: Registrasi Akun Baru dengan Firebase
export async function registerWithEmail(name, email, password, options = {}) {
    const userCredential = await createUserWithEmailAndPassword(auth, email.trim(), password);
    const user = userCredential.user;

    // Perbarui display name di Firebase
    if (name) {
        await updateProfile(user, { displayName: name });
    }

    if (options && options.syncSession === false) {
        return { user, session: null };
    }

    // Sinkronkan akun baru ke backend
    const sessionResult = await syncSessionWithBackend(user, 'password');
    return { user, session: sessionResult };
}

// Handler: Logout dari Firebase & Laravel
export async function logoutBoth() {
    try {
        await signOut(auth);
    } catch (e) {
        console.warn('Firebase signout error:', e);
    }

    const csrfToken = getCsrfToken();
    try {
        await fetch('/auth/firebase-logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
    } catch (e) {
        console.warn('Backend logout error:', e);
    }

    window.location.href = '/login';
}

// Expose ke global window untuk Alpine.js & Vanilla JS
if (typeof window !== 'undefined') {
    window.OmniFirebaseAuth = {
        auth,
        app,
        showToast,
        formatFirebaseError,
        loginWithEmailOrUsername,
        loginWithGoogle,
        registerWithEmail,
        logoutBoth,
        syncSessionWithBackend,
        onAuthStateChanged: (cb) => onAuthStateChanged(auth, cb)
    };
}
    if (typeof document !== 'undefined') {
        document.dispatchEvent(new CustomEvent('omni:firebase-ready', { detail: window.OmniFirebaseAuth }));
    }
