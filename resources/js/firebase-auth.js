import { initializeApp } from "firebase/app";
import {
    getAuth,
    signInWithEmailAndPassword,
    createUserWithEmailAndPassword,
    signInWithPopup,
    signInWithRedirect,
    getRedirectResult,
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
            ? 'bg-emerald-700 border border-emerald-600 shadow-emerald-950/30'
            : type === 'error'
            ? 'bg-rose-700 border border-rose-600 shadow-rose-950/30'
            : 'bg-zinc-900 border border-zinc-700 shadow-black/30'
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

// Deteksi host loopback IP (127.0.0.1 / ::1) yang belum terdaftar di Firebase Authorized Domains
export function isLoopbackIp(hostname = typeof window !== 'undefined' ? window.location.hostname : '') {
    return hostname === '127.0.0.1' || hostname === '[::1]' || hostname === '::1';
}

function buildLocalhostOrigin() {
    if (typeof window === 'undefined') return '';
    const port = window.location.port ? `:${window.location.port}` : '';
    return `${window.location.protocol}//localhost${port}`;
}

async function isOriginReachable(origin) {
    try {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 1200);
        await fetch(`${origin}/favicon.ico`, { mode: 'no-cors', cache: 'no-store', signal: controller.signal });
        clearTimeout(timer);
        return true;
    } catch {
        return false;
    }
}

export async function switchToAuthorizedLocalhostForGoogle() {
    if (typeof window === 'undefined' || !isLoopbackIp()) {
        return false;
    }
    const targetOrigin = buildLocalhostOrigin();
    const reachable = await isOriginReachable(targetOrigin);
    if (!reachable) {
        return false;
    }
    const url = new URL(window.location.href);
    url.hostname = 'localhost';
    url.searchParams.set('google_signin', '1');
    window.location.replace(url.toString());
    return true;
}

// Error translator bahasa Indonesia
export function formatFirebaseError(error) {
    const code = error?.code || '';
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
            return 'Pop-up diblokir browser; mencoba pengalihan langsung ke halaman masuk Google...';
        case 'auth/unauthorized-domain': {
            const port = typeof window !== 'undefined' && window.location.port ? `:${window.location.port}` : '';
            return `Domain "${typeof window !== 'undefined' ? window.location.host : 'ini'}" belum diotorisasi di Firebase Console. Gunakan http://localhost${port}/login atau daftarkan domain di Authentication -> Settings -> Authorized domains.`;
        }
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
            return error?.message || 'Terjadi kesalahan pada proses autentikasi.';
    }
}

// Ambil CSRF Token Laravel
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    const input = document.querySelector('input[name="_token"]');
    return input ? input.value : '';
}

async function refreshCsrfToken() {
    try {
        const res = await fetch(window.location.pathname, { credentials: 'same-origin' });
        if (!res.ok) return getCsrfToken();
        const html = await res.text();
        const match = html.match(/name="csrf-token"\s+content="([^"]+)"/);
        if (match && match[1]) {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', match[1]);
            return match[1];
        }
    } catch {
        // abaikan jika refresh CSRF gagal
    }
    return getCsrfToken();
}

// Sinkronisasi sesi Firebase ke backend Laravel
export async function syncSessionWithBackend(firebaseUser, provider = 'password') {
    const payload = JSON.stringify({
        uid: firebaseUser.uid,
        email: firebaseUser.email,
        name: firebaseUser.displayName || firebaseUser.email.split('@')[0],
        photo_url: firebaseUser.photoURL || null,
        provider: provider,
        id_token: await firebaseUser.getIdToken()
    });

    const postSync = (token) => fetch('/auth/firebase-session', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: payload
    });

    let response = await postSync(getCsrfToken());
    if (response.status === 419) {
        const freshToken = await refreshCsrfToken();
        response = await postSync(freshToken);
    }

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

// Selesaikan hasil redirect OAuth Google jika kembali dari signInWithRedirect
export async function consumeGoogleRedirectResult() {
    try {
        const result = await getRedirectResult(auth);
        if (!result || !result.user) {
            return null;
        }
        const sessionResult = await syncSessionWithBackend(result.user, 'google');
        return { user: result.user, session: sessionResult };
    } catch (error) {
        if (error?.code === 'auth/no-auth-event') {
            return null;
        }
        throw error;
    }
}

// Handler: Login Cepat dengan Akun Google (popup utama + fallback redirect bila popup diblokir + loopback localhost auto-switch)
export async function loginWithGoogle() {
    if (isLoopbackIp()) {
        const switched = await switchToAuthorizedLocalhostForGoogle();
        if (switched) {
            return { redirected: true };
        }
    }

    try {
        const result = await signInWithPopup(auth, googleProvider);
        const user = result.user;

        // Sinkronkan ke Laravel Session
        const sessionResult = await syncSessionWithBackend(user, 'google');
        return { user, session: sessionResult };
    } catch (error) {
        const code = error?.code || '';
        if (code === 'auth/unauthorized-domain' && isLoopbackIp()) {
            const switched = await switchToAuthorizedLocalhostForGoogle();
            if (switched) {
                return { redirected: true };
            }
        }
        if (code === 'auth/popup-blocked' || code === 'auth/operation-not-supported-in-this-environment') {
            await signInWithRedirect(auth, googleProvider);
            return { redirected: true };
        }
        throw error;
    }
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
        isLoopbackIp,
        switchToAuthorizedLocalhostForGoogle,
        consumeGoogleRedirectResult,
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
