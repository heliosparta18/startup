# Product Requirement Document (PRD) — Candia Purwa
**Modul:** Real-Time Order Tracking (Laravel Reverb) & Pertemuan 13 (Sales Analytics Dashboard, Rekonsiliasi Transaksi, dan Ekspor Laporan)  
**Penanggung Jawab:** Candia Purwa (`candia@omnipharma.test`)  
**Peran Sistem:** Apoteker Cabang & Pelaksana Reporting Finansial Multi-Klien  
**Dosen Pengampu:** Pak Devit Suwardiyanto  
**Repository:** `D:\KELOMPOK PAK DEVIT`  
**Target Dokumen:** `docs/PRD_CANDI.md`  
**Status Dokumen:** Final Draft (Rebalanced Roadmap Sesuai R1 & R5)  
**Versi:** 2.0.0 (Sinkronisasi Laravel 13, Livewire 4, Reverb 1.0, Redis 7.2)

---

## 1. Module Description & Architectural Objectives

### 1.1 Deskripsi Modul
Modul yang diampu oleh **Candia Purwa** mencakup dua pilar fungsional utama pada arsitektur OmniPharma Network:
1. **Real-time Order Status Tracking via WebSockets (Laravel Reverb & Echo)**:
   Infrastruktur komunikasi dua arah (*bidirectional full-duplex*) berlatensi rendah untuk memperbarui status pesanan pelanggan secara instan tanpa *browser refresh* atau *polling* HTTP berulang, serta memberikan notifikasi waktu-nyata (*real-time incoming order alert*) pada dasbor apoteker cabang saat resep baru atau pesanan masuk.
2. **Pertemuan 13 — Executive Sales Analytics, Rekonsiliasi Transaksi & Ekspor Laporan**:
   Pusat intelijen bisnis dan akuntansi multi-cabang untuk Super Admin dan pimpinan jaringan apotek. Modul ini menyediakan agregasi penjualan lintas cabang, rekonsiliasi pembayaran versus pesanan, penutupan buku periode transaksi dengan penguncian *snapshot* data permanen (*append-only period locking* pada tabel `ringkasan_periode_penjualan`), serta *pipeline* ekspor laporan berformat PDF dan Excel/CSV dengan proteksi *CSV formula injection*.

### 1.2 Tujuan Arsitektur (Architectural Objectives)
* **High-Concurrency & Low-Latency Event Broadcasting**:
  Menggantikan beban *HTTP long-polling* tradisional yang membebani web server dengan Laravel Reverb (server WebSocket berbasis event-loop non-blocking pada port TCP 8080). Event order disiarkan asinkron via antrean Redis dan dipancarkan ke *private channel* terenkripsi.
* **Strict Multi-Tenant & Channel Authorization**:
  Menjamin bahwa data status pesanan pelanggan dan alert cabang tidak dapat disadap (*eavesdropping*) antar-klien. Saluran WebSocket dikunci melalui `routes/channels.php` dengan validasi kepemilikan pesanan (`id_pengguna`) dan keanggotaan apotek cabang (`id_client`).
* **Financial Data Integrity & Snapshot Immutability (ADR-0008)**:
  Mencegah anomali laporan keuangan akibat fluktuasi harga obat di masa depan. Rekonsiliasi periode penjualan melakukan kalkulasi agregat omzet dan membukukan baris rekonsiliasi yang terkunci (`status_terkunci = true`) dengan *composite unique constraint* `UNIQUE(id_client, periode_mulai, periode_selesai)` pada basis data MariaDB.
* **Safe Streaming Binary Exports**:
  Memproses ribuan baris transaksi penjualan ke format CSV dan PDF menggunakan *streamed response* untuk menjaga penggunaan memori PHP tetap di bawah 64 MB, dilengkapi sanitasi karakter berbahaya (`=`, `+`, `-`, `@`) untuk menangkis *CSV Injection / Formula Injection* di aplikasi spreadsheet klien.

### 1.3 Alokasi Tugas & Rationale Rebalancing
Sesuai arahan orkestrator dan penyesuaian roadmap proyek:
* Tanggung jawab integrasi QRIS Sandbox dan penanganan Webhook (Pertemuan 10 & 11) dialihkan sepenuhnya ke **Guntur Afandi**, sehingga alur pemesanan (*Katalog ➔ Keranjang Redis ➔ Checkout Atomik ➔ Pembayaran QRIS ➔ Webhook Settlement*) menjadi satu kesatuan vertikal di bawah Guntur.
* **Candia Purwa** difokuskan 100% untuk menguasai teknologi WebSockets modern (Laravel Reverb) dan analitik finansial eksekutif (Pertemuan 13). Rebalancing ini memastikan kontribusi proyek terdistribusi adil (~25% per mahasiswa) dan memberikan nilai keunggulan teknis tinggi saat ujian demonstrasi di hadapan Pak Devit Suwardiyanto.

---

## 2. List of Files to Create and Edit (Exact Paths)

Berikut adalah daftar lengkap berkas yang dibuat dan dimodifikasi di dalam struktur modul `D:\KELOMPOK PAK DEVIT`:

```
D:\KELOMPOK PAK DEVIT\
├── app/
│   ├── Modules/
│   │   ├── Ordering/
│   │   │   └── Events/
│   │   │       ├── OrderStatusUpdated.php                      [CREATE] Event broadcast status pesanan
│   │   │       └── IncomingOrderPlaced.php                     [CREATE] Event broadcast notifikasi cabang
│   │   └── Reporting/
│   │       ├── Actions/
│   │       │   └── CloseSalesPeriodAction.php                  [CREATE] Aksi penutupan & penguncian periode
│   │       ├── Http/
│   │       │   └── Controllers/
│   │       │       └── SalesReportController.php               [CREATE] Controller dasbor & ekspor laporan
│   │       ├── Services/
│   │       │   ├── SalesReportService.php                      [CREATE] Service kalkulasi metrik & agregasi
│   │       │   ├── SalesReconciliationService.php              [CREATE] Service rekonsiliasi transaksi
│   │       │   └── SalesReportExportService.php                [CREATE] Service streaming export PDF & Excel
│   │       └── routes/
│   │           └── web.php                                     [EDIT/CREATE] Rute internal modul reporting
│   └── Models/
│       ├── Pesanan.php                                         [EDIT] Helper event broadcast model
│       └── RingkasanPeriodePenjualan.php                       [EDIT] Scope & helper status_terkunci
├── routes/
│   ├── channels.php                                            [EDIT] Registrasi otorisasi private channel
│   └── admin.php                                               [EDIT] Sambungkan rute dashboard ke controller
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── dashboard.blade.php                             [EDIT] Integrasi data dinamis & modal kunci
│       │   └── pdf/
│       │       └── laporan-penjualan-template.blade.php        [CREATE] Template print & PDF laporan penjualan
│       ├── customer/
│       │   └── order-status.blade.php                          [CREATE] UI Stepper live tracking Echo
│       └── tenant/
│           └── dashboard.blade.php                             [EDIT] Listener notifikasi pesanan masuk
└── tests/
    └── Feature/
        ├── ReverbOrderStatusBroadcastTest.php                  [CREATE] Unit/Feature test WebSocket Reverb
        ├── SalesReconciliationAndPeriodLockTest.php            [CREATE] Test rekonsiliasi & DB unique constraint
        └── SalesReportExportTest.php                           [CREATE] Test ekspor streaming & sanitasi CSV
```

---

## 3. Git Branch Workflow

Pengembangan modul dikerjakan secara bertahap menggunakan Git flow terstruktur dengan branch feature yang terisolasi sebelum digabungkan (*merge*) ke branch utama (`main`).

```
                              [feat/candi-reverb-laporan]
                                    │
                                    ├─── 1. Setup Reverb events & channels
                                    ├─── 2. Echo UI stepper pada order tracking
                                    └─── 3. Audio/Toast notif di tenant dashboard
                                    │
[main] ─────────────────────────────┼─────────────────────────────────────────── [Merge PR #4]
                                    │
                                    ├─── 4. Service Agregasi & KPI Dashboard
                                    ├─── 5. Idempotent Period Lock & Constraint
                                    └─── 6. Streaming CSV & PDF Export
                                    │
                         [feature/pertemuan-13-analytics-reverb]
```

### 3.1 Aturan Penamaan Branch
Tersedia dua opsi konvensi penamaan branch yang sah dan konsisten dengan histori git tim:
1. **Developer Feature Branch (Default Praktikum Tim):**
   * `feat/candi-reverb-laporan` (Branch terpadu pengerjaan fitur Reverb & Pelaporan)
   * `feat/candi-realtime-tracking` (Branch spesifik tracking WebSocket)
   * `feat/candi-sales-reconciliation` (Branch spesifik rekonsiliasi & ekspor)
2. **Academic Meeting Branch (Penilaian Kurikulum Pak Devit):**
   * `feature/pertemuan-realtime-tracking`
   * `feature/pertemuan-13-analytics-reverb`

### 3.2 Langkah Eksekusi Git Branch
```powershell
# 1. Pastikan repo lokal sinkron dengan upstream main
git checkout main
git pull origin main

# 2. Buat branch baru untuk pengerjaan modul Candi
git checkout -b feat/candi-reverb-laporan

# 3. Lakukan commit berkala dengan pesan alami (lihat Bagian 4)
git add .
git commit -m "pasang event broadcast reverb untuk update status pesanan"

# 4. Dorong branch ke remote GitHub
git push -u origin feat/candi-reverb-laporan

# 5. Buat Pull Request (PR) di GitHub ke branch main
# Review kelayakan test: php artisan test
```

---

## 4. Natural, Human-written Commit Message Conventions

Pak Devit Suwardiyanto sangat memperhatikan keaslian proses *coding* mahasiswa melalui riwayat commit. Seluruh commit wajib ditulis dengan gaya manusia autentik (gaya bahasa Indonesia aktif atau Conventional Commits ringkas), dan **DILARANG KERAS** menggunakan format *AI boilerplate* atau generator otomatis.

### 4.1 Contoh Pesan Commit Alami (Indonesian Casual & Action-Oriented)
* `setup event broadcast reverb untuk pembaruan status pesanan`
* `tambah otorisasi private channel order dan tenant di channels.php`
* `bikin halaman lacak pesanan customer dengan listener laravel echo`
* `pasang notifikasi suara dan badge pesanan baru di dashboard apotek cabang`
* `buat sales report service untuk agregasi omzet dan obat terlaris`
* `implementasi aksi tutup periode penjualan dan kunci ringkasan transaksi`
* `tangani duplicate key exception saat periode penjualan sudah dikunci`
* `bikin service ekspor laporan penjualan ke format csv dan cetak pdf`
* `tambah sanitasi karakter formula excel untuk cegah serangan csv injection`
* `tambah unit test broadcast reverb dan rekonsiliasi periode penjualan`

### 4.2 Contoh Alternatif (Clean Conventional Commits)
* `feat(websockets): setup reverb broadcast event for order progression`
* `feat(channels): add private channel authorization for tenant and orders`
* `feat(customer): integrate laravel echo stepper on order status view`
* `feat(reporting): implement sales aggregation service and top medicine kpi`
* `feat(reconciliation): implement period locking action with unique constraint`
* `feat(export): stream csv and pdf sales reports with formula sanitization`
* `test(reporting): add feature tests for reverb events and period locking`

### 4.3 Anti-Patterns: Contoh Commit yang DILARANG (AI Boilerplate)
* ❌ `feat: comprehensive implementation of enterprise real-time broadcast and analytics architecture`
* ❌ `refactor: adhere to best practices and guidelines for modern reactive dashboards`
* ❌ `chore: update files according to user instructions and prompt requirements`
* ❌ `Co-authored-by: AI Assistant <ai@anthropic.com>`
* ❌ Commit dengan deskripsi esai panjang bertabur kata-kata promosi seperti *"seamless"*, *"state-of-the-art"*, atau *"cutting-edge"*.

---

## 5. Step-by-Step Technical Implementation Guide

### Fase 1: Konfigurasi Laravel Reverb & Event Broadcasting

#### 1.1 Verifikasi Konfigurasi Environment (`.env`)
Pastikan variabel lingkungan Reverb dan antrean Redis terkonfigurasi dengan benar:
```ini
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis

REVERB_APP_ID=omnipharma_app
REVERB_APP_KEY=omnipharma_key_2026
REVERB_APP_SECRET=omnipharma_secret_2026
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

#### 1.2 Event `OrderStatusUpdated` (`app/Modules/Ordering/Events/OrderStatusUpdated.php`)
Event ini mengimplementasikan antarmuka `ShouldBroadcastNow` agar pembaruan status langsung dipancarkan seketika (*zero-latency*) tanpa harus menunggu antrean worker dieksekusi:

```php
<?php

namespace App\Modules\Ordering\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Pesanan $pesanan,
        public string $statusSebelumnya,
        public string $statusBaru,
        public ?string $catatan = null
    ) {}

    /**
     * Tentukan channel siaran event.
     * 1. Private channel khusus untuk pemesan: private-order.{id_pesanan}
     * 2. Private channel untuk apotek cabang: private-tenant.{id_client}
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.'.$this->pesanan->id_pesanan),
            new PrivateChannel('tenant.'.$this->pesanan->id_client),
        ];
    }

    /**
     * Nama event yang didengarkan oleh client-side Echo.
     */
    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    /**
     * Payload data yang dikirimkan melalui WebSocket.
     */
    public function broadcastWith(): array
    {
        return [
            'id_pesanan' => $this->pesanan->id_pesanan,
            'nomor_pesanan' => $this->pesanan->nomor_pesanan,
            'id_client' => $this->pesanan->id_client,
            'status_sebelumnya' => $this->statusSebelumnya,
            'status_baru' => $this->statusBaru,
            'catatan' => $this->catatan,
            'waktu_update' => now()->toISOString(),
        ];
    }
}
```

#### 1.3 Event `IncomingOrderPlaced` (`app/Modules/Ordering/Events/IncomingOrderPlaced.php`)
Event yang dipicu saat pelanggan menyelesaikan pesanan baru (termasuk pesanan resep obat keras), sehingga dasbor apoteker cabang langsung berdering dan memunculkan kartu antrean tanpa perlu me-*refresh* browser:

```php
<?php

namespace App\Modules\Ordering\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingOrderPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Pesanan $pesanan
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.'.$this->pesanan->id_client),
        ];
    }

    public function broadcastAs(): string
    {
        return 'IncomingOrderPlaced';
    }

    public function broadcastWith(): array
    {
        $this->pesanan->loadMissing(['pengguna', 'items.obat', 'resep']);

        return [
            'id_pesanan' => $this->pesanan->id_pesanan,
            'nomor_pesanan' => $this->pesanan->nomor_pesanan,
            'nama_pelanggan' => $this->pesanan->pengguna?->nama_lengkap ?? 'Pelanggan Tamu',
            'metode_layanan' => $this->pesanan->metode_layanan,
            'memerlukan_resep' => $this->pesanan->resep !== null,
            'total_item' => $this->pesanan->items->count(),
            'total_biaya' => (float) $this->pesanan->items->sum('subtotal'),
            'dibuat_pada' => $this->pesanan->dibuat_pada?->format('H:i:s') ?? now()->format('H:i:s'),
        ];
    }
}
```

#### 1.4 Aturan Otorisasi Saluran (`routes/channels.php`)
Setiap saluran privat wajib diotorisasi agar tidak terjadi kebocoran informasi antar pelanggan atau antar cabang:

```php
<?php

use App\Models\Pesanan;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Broadcast;

/**
 * 1. Otorisasi Channel Pemesan Pribadi: private-order.{id_pesanan} & private-orders.{id_pesanan}
 * Mendukung pelanggan terotentikasi, super admin, staf cabang, serta pelanggan tamu (guest session / order token).
 */
Broadcast::channel('order.{idPesanan}', function ($user = null, string $idPesanan) {
    $pesanan = Pesanan::withoutGlobalScopes()->find($idPesanan);
    if (! $pesanan) {
        return false;
    }

    // A. Pengguna Terotentikasi (Auth User)
    if ($user) {
        // Pemilik pesanan sah
        if ((string) $user->id_pengguna === (string) $pesanan->id_pengguna) {
            return true;
        }

        // Super Admin diizinkan menginspeksi seluruh pesanan
        if ($user->peran === 'super_admin') {
            return true;
        }

        // Apoteker atau staf cabang yang menangani pesanan tersebut
        if (in_array($user->peran, ['apoteker', 'staf_klien']) && (string) $user->id_client === (string) $pesanan->id_client) {
            return true;
        }
    }

    // B. Pelanggan Tamu Anonim (Guest Session Authorization)
    // 1. Verifikasi melalui cookie sesi tamu 'omnipharma_customer_session'
    $sessionToken = request()->cookie('omnipharma_customer_session') ?? request()->header('X-Guest-Session-Token');
    if ($sessionToken) {
        $guestEmail = "guest_{$sessionToken}@guest.omnipharma.local";
        $guestUser = Pengguna::where('email', $guestEmail)->first();
        if ($guestUser && (string) $guestUser->id_pengguna === (string) $pesanan->id_pengguna) {
            return true;
        }
    }

    // 2. Verifikasi melalui token akses pesanan rahasia (URL tracking token / secret parameter)
    $orderToken = request()->input('token') ?? request()->header('X-Order-Token');
    if ($orderToken && hash_equals(hash('sha256', $pesanan->id_pesanan . config('app.key')), (string) $orderToken)) {
        return true;
    }

    return false;
});

// Alias channel plural untuk kompatibilitas rute klien
Broadcast::channel('orders.{idPesanan}', function ($user = null, string $idPesanan) {
    return Broadcast::channel('order.' . $idPesanan, fn() => true);
});

/**
 * 2. Otorisasi Channel Apotek Cabang: private-tenant.{id_client}
 * Hanya staf/apoteker cabang aktif dan super admin yang dapat mendengarkan sinyal cabang.
 */
Broadcast::channel('tenant.{idClient}', function ($user, string $idClient) {
    if (! $user) {
        return false;
    }

    if ($user->peran === 'super_admin') {
        return true;
    }

    if (in_array($user->peran, ['apoteker', 'staf_klien'])) {
        return (string) $user->id_client === (string) $idClient;
    }

    return false;
});
```

---

### Fase 2: Integrasi Frontend (Laravel Echo, Alpine.js & Live Stepper)

#### 2.1 Halaman Lacak Status Pesanan (`resources/views/customer/order-status.blade.php`)
Menampilkan stepper responsif yang secara dinamis berpindah status saat sinyal WebSocket diterima via Laravel Echo:

```blade
<x-layouts.customer :title="'Lacak Pesanan ' . $pesanan->nomor_pesanan">
    <div x-data="{
        idPesanan: '{{ $pesanan->id_pesanan }}',
        currentStatus: '{{ $pesanan->status }}',
        nomorPesanan: '{{ $pesanan->nomor_pesanan }}',
        catatanApoteker: '{{ $pesanan->resep?->catatan_apoteker ?? '' }}',
        lastUpdated: '{{ now()->format('H:i:s') }}',
        koneksiWebSocket: false,
        
        statusSequence: [
            { key: 'menunggu_pembayaran', label: 'Menunggu Pembayaran', desc: 'Selesaikan transaksi melalui QRIS' },
            { key: 'menunggu_verifikasi', label: 'Verifikasi Resep', desc: 'Apoteker sedang memeriksa SIP dokter' },
            { key: 'diproses', label: 'Obat Disiapkan', desc: 'Staf apotek mengambil obat sesuai FEFO' },
            { key: 'siap_diambil', label: 'Siap Diambil / Dikirim', desc: 'Pesanan siap di konter atau dalam kurir' },
            { key: 'selesai', label: 'Pesanan Selesai', desc: 'Obat telah diterima dengan baik' }
        ],

        isStepActive(stepKey) {
            const keys = this.statusSequence.map(s => s.key);
            const targetIdx = keys.indexOf(stepKey);
            const currentIdx = keys.indexOf(this.currentStatus);
            return currentIdx >= targetIdx;
        },

        isCurrentStep(stepKey) {
            return this.currentStatus === stepKey;
        },

        init() {
            if (typeof window.Echo !== 'undefined') {
                // 1. Langganan ke private channel pesanan
                window.Echo.private(`order.${this.idPesanan}`)
                    .listen('.OrderStatusUpdated', (e) => {
                        this.currentStatus = e.status_baru;
                        if (e.catatan) {
                            this.catatanApoteker = e.catatan;
                        }
                        this.lastUpdated = new Date().toLocaleTimeString();
                    });

                // 2. Binding Listener Status Koneksi WebSocket (E-11)
                if (window.Echo.connector && window.Echo.connector.pusher) {
                    const conn = window.Echo.connector.pusher.connection;
                    this.koneksiWebSocket = (conn.state === 'connected');

                    conn.bind('state_change', (states) => {
                        this.koneksiWebSocket = (states.current === 'connected');
                    });
                } else {
                    this.koneksiWebSocket = true;
                }
            }

            // 3. Graceful Polling Fallback saat koneksi WebSocket terputus (E-11)
            setInterval(() => {
                if (!this.koneksiWebSocket) {
                    fetch(`/api/pesanan/${this.idPesanan}/status`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.status && data.status !== this.currentStatus) {
                            this.currentStatus = data.status;
                            if (data.catatan) {
                                this.catatanApoteker = data.catatan;
                            }
                            this.lastUpdated = new Date().toLocaleTimeString();
                        }
                    })
                    .catch(err => console.debug('Polling order status fallback error:', err));
                }
            }, 15000); // Polling setiap 15 detik jika WebSocket terputus
        }
    }" class="max-w-3xl mx-auto py-8 px-4 sm:px-6 space-y-6">

        <!-- Card Header Status Pesanan -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-800">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Nomor Registrasi Pesanan</span>
                    <h1 class="text-xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight" x-text="nomorPesanan"></h1>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                        :class="koneksiWebSocket ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                        <span class="h-2 w-2 rounded-full" :class="koneksiWebSocket ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                        <span x-text="koneksiWebSocket ? 'Live WebSocket Aktif' : 'Mode Fallback Polling'"></span>
                    </span>
                </div>
            </div>

            <!-- Stepper Progress Bar -->
            <div class="py-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t-2 border-zinc-200 dark:border-zinc-800"></div>
                    </div>
                    <div class="relative flex justify-between">
                        <template x-for="(step, index) in statusSequence" :key="step.key">
                            <div class="flex flex-col items-center">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all duration-300"
                                    :class="isCurrentStep(step.key) 
                                        ? 'border-emerald-600 bg-emerald-600 text-white ring-4 ring-emerald-100 dark:ring-emerald-950'
                                        : (isStepActive(step.key) 
                                            ? 'border-emerald-600 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'border-zinc-300 bg-white text-zinc-400 dark:border-zinc-700 dark:bg-zinc-800')">
                                    <template x-if="isStepActive(step.key) && !isCurrentStep(step.key)">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    <template x-if="!isStepActive(step.key) || isCurrentStep(step.key)">
                                        <span class="text-xs font-bold" x-text="index + 1"></span>
                                    </template>
                                </div>
                                <span class="mt-2 text-center text-[11px] font-bold max-w-[80px]"
                                    :class="isCurrentStep(step.key) ? 'text-emerald-700 dark:text-emerald-300' : 'text-zinc-600 dark:text-zinc-400'"
                                    x-text="step.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Catatan Apoteker jika ada resep / penyesuaian -->
            <div x-show="catatanApoteker" class="rounded-xl bg-blue-50 border border-blue-200 p-4 text-xs text-blue-900 dark:bg-blue-950 dark:border-blue-800 dark:text-blue-200">
                <span class="font-bold">Catatan Apoteker: </span>
                <span x-text="catatanApoteker"></span>
            </div>

            <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-[11px] text-zinc-400 text-right">
                Pembaruan terakhir: <span x-text="lastUpdated"></span>
            </div>
        </div>
    </div>
</x-layouts.customer>
```

#### 2.2 Notifikasi Pesanan Masuk pada Dasbor Apoteker Cabang (`resources/views/tenant/dashboard.blade.php`)
Apoteker menerima notifikasi *pop-up toast* dan dering audio saat pesanan baru masuk ke apotek cabang mereka:

```javascript
// Listener Echo yang dipasang di view tenant.dashboard
document.addEventListener('DOMContentLoaded', () => {
    const tenantId = '{{ $client->id_client }}';
    
    if (typeof window.Echo !== 'undefined') {
        window.Echo.private(`tenant.${tenantId}`)
            .listen('.IncomingOrderPlaced', (event) => {
                // 1. Mainkan audio alert halus
                const audio = new Audio('/sounds/notification-chime.mp3');
                audio.play().catch(() => {});

                // 2. Tampilkan banner toast antislop
                window.dispatchEvent(new CustomEvent('tampilkan-toast', {
                    detail: {
                        pesan: `Pesanan Baru ${event.nomor_pesanan} (${event.nama_pelanggan}) - Rp ${event.total_biaya.toLocaleString('id-ID')}`,
                        tipe: 'sukses'
                    }
                }));

                // 3. Sisipkan pesanan baru ke antrean tabel secara reaktif
                const antreanTable = document.getElementById('tabel-antrean-pesanan');
                if (antreanTable) {
                    // Tambahkan baris baru dengan efek highlight
                }
            });
    }
});
```

---

### Fase 3: Sales Analytics Dashboard & Service Agregasi

#### 3.1 Service Analitik Penjualan (`app/Modules/Reporting/Services/SalesReportService.php`)
Service ini mengumpulkan metrik bisnis secara efisien melalui kueri SQL teroptimasi:

```php
<?php

namespace App\Modules\Reporting\Services;

use App\Models\Client;
use App\Models\ItemPesanan;
use App\Models\Pesanan;
use App\Models\PermintaanRetur;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * Hitung ringkasan KPI eksekutif untuk rentang tanggal tertentu.
     */
    public function getExecutiveKpis(?string $idClient, Carbon $mulai, Carbon $selesai): array
    {
        $orderQuery = Pesanan::query()
            ->whereBetween('dibuat_pada', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->whereIn('status', ['diproses', 'siap_diambil', 'selesai']);

        if ($idClient) {
            $orderQuery->where('id_client', $idClient);
        }

        $orderIds = (clone $orderQuery)->pluck('id_pesanan');

        // Omzet didasarkan pada snapshot harga di tabel item_pesanan (ADR-0008)
        $totalOmzet = (float) ItemPesanan::query()
            ->whereIn('id_pesanan', $orderIds)
            ->sum('subtotal');

        $totalPesanan = $orderIds->count();

        // Hitung pesanan via resep dokter
        $pesananResep = (clone $orderQuery)
            ->has('resep')
            ->count();

        // Hitung total retur obat yang disetujui
        $returQuery = PermintaanRetur::query()
            ->whereIn('id_pesanan', $orderIds)
            ->where('status', 'disetujui');

        $totalRetur = $returQuery->count();

        return [
            'total_omzet' => $totalOmzet,
            'total_pesanan' => $totalPesanan,
            'pesanan_reguler' => $totalPesanan - $pesananResep,
            'pesanan_resep' => $pesananResep,
            'total_retur' => $totalRetur,
            'periode_mulai' => $mulai->toDateString(),
            'periode_selesai' => $selesai->toDateString(),
        ];
    }

    /**
     * Daftar 5 obat dengan volume penjualan tertinggi (Top 5 Best-Selling Medicines).
     */
    public function getTopSellingDrugs(?string $idClient, Carbon $mulai, Carbon $selesai, int $limit = 5): Collection
    {
        $query = DB::table('item_pesanan')
            ->join('pesanan', 'item_pesanan.id_pesanan', '=', 'pesanan.id_pesanan')
            ->whereBetween('pesanan.dibuat_pada', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->whereIn('pesanan.status', ['diproses', 'siap_diambil', 'selesai']);

        if ($idClient) {
            $query->where('pesanan.id_client', $idClient);
        }

        return $query->select(
            'item_pesanan.nama_obat',
            DB::raw('SUM(item_pesanan.kuantitas) as total_kuantitas'),
            DB::raw('SUM(item_pesanan.subtotal) as total_nominal')
        )
            ->groupBy('item_pesanan.nama_obat')
            ->orderByDesc('total_kuantitas')
            ->limit($limit)
            ->get();
    }

    /**
     * Rincian kinerja penjualan per cabang apotek.
     */
    public function getBranchPerformanceBreakdown(Carbon $mulai, Carbon $selesai): Collection
    {
        return Client::query()
            ->withCount(['pesanan' => function ($q) use ($mulai, $selesai) {
                $q->whereBetween('dibuat_pada', [$mulai->startOfDay(), $selesai->endOfDay()])
                  ->whereIn('status', ['diproses', 'siap_diambil', 'selesai']);
            }])
            ->get()
            ->map(function ($client) use ($mulai, $selesai) {
                $omzet = (float) DB::table('item_pesanan')
                    ->join('pesanan', 'item_pesanan.id_pesanan', '=', 'pesanan.id_pesanan')
                    ->where('pesanan.id_client', $client->id_client)
                    ->whereBetween('pesanan.dibuat_pada', [$mulai->startOfDay(), $selesai->endOfDay()])
                    ->whereIn('pesanan.status', ['diproses', 'siap_diambil', 'selesai'])
                    ->sum('item_pesanan.subtotal');

                return [
                    'id_client' => $client->id_client,
                    'nama_client' => $client->nama_client,
                    'total_pesanan' => $client->pesanan_count,
                    'omzet' => $omzet,
                ];
            });
    }
}
```

---

### Fase 4: Rekonsiliasi Transaksi & Aksi Kunci Periode (Period Locking)

#### 4.1 Aksi Kunci Periode (`app/Modules/Reporting/Actions/CloseSalesPeriodAction.php`)
Sesuai prinsip **ADR-0008**, penutupan periode akuntansi mengunci nilai transaksi agar bersifat *immutable* dan tahan terhadap perubahan harga obat di masa depan. Database constraint `UNIQUE(id_client, periode_mulai, periode_selesai)` mencegah terjadinya penutupan buku ganda:

```php
<?php

namespace App\Modules\Reporting\Actions;

use App\Models\Client;
use App\Models\RingkasanPeriodePenjualan;
use App\Modules\Reporting\Services\SalesReportService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CloseSalesPeriodAction
{
    public function __construct(
        protected SalesReportService $reportService
    ) {}

    /**
     * Tutup dan kunci periode penjualan secara atomik.
     *
     * @throws RuntimeException Jika periode sudah terkunci sebelumnya
     */
    public function execute(string $idClient, Carbon $mulai, Carbon $selesai): RingkasanPeriodePenjualan
    {
        $client = Client::query()->findOrFail($idClient);

        return DB::transaction(function () use ($client, $mulai, $selesai) {
            // 1. Cek apakah periode telah terkunci sebelumnya
            $existing = RingkasanPeriodePenjualan::query()
                ->where('id_client', $client->id_client)
                ->where('periode_mulai', $mulai->toDateString())
                ->where('periode_selesai', $selesai->toDateString())
                ->first();

            if ($existing && $existing->status_terkunci) {
                throw new RuntimeException("Periode penjualan ({$mulai->format('d M Y')} - {$selesai->format('d M Y')}) untuk cabang {$client->nama_client} telah ditutup dan berstatus terkunci.");
            }

            // 2. Kalkulasi agregat final transaksi
            $kpis = $this->reportService->getExecutiveKpis($client->id_client, $mulai, $selesai);

            try {
                // 3. Simpan snapshot permanen ke tabel ringkasan_periode_penjualan
                return RingkasanPeriodePenjualan::create([
                    'id_ringkasan' => (string) Str::uuid(),
                    'id_client' => $client->id_client,
                    'nama_client' => $client->nama_client,
                    'total_obat_terjual' => "{$kpis['total_pesanan']} order",
                    'periode_mulai' => $mulai->toDateString(),
                    'periode_selesai' => $selesai->toDateString(),
                    'total_pesanan' => $kpis['total_pesanan'],
                    'total_pendapatan' => $kpis['total_omzet'],
                    'total_retur' => $kpis['total_retur'],
                    'status_terkunci' => true,
                    'dibuat_pada' => now(),
                ]);
            } catch (QueryException $e) {
                // Tangkap jika terjadi pelanggaran unique constraint pada level MariaDB
                if (str_contains($e->getMessage(), 'Duplicate entry') || $e->getCode() === '23000') {
                    throw new RuntimeException("Terdeteksi penutupan ganda untuk periode {$mulai->toDateString()} s/d {$selesai->toDateString()}. Transaksi dibatalkan untuk menjaga integritas data.");
                }
                throw $e;
            }
        });
    }
}
```

---

### Fase 5: Pipeline Ekspor Laporan Streaming (PDF & Excel/CSV)

#### 5.1 Service Ekspor dengan Proteksi CSV Injection (`app/Modules/Reporting/Services/SalesReportExportService.php`)
Ketika mengekspor ribuan baris transaksi penjualan, memori RAM server harus dijaga dengan teknik *chunked streaming*. Selain itu, sel teks harus dibersihkan dari karakter berbahaya formula spreadsheet:

```php
<?php

namespace App\Modules\Reporting\Services;

use App\Models\Pesanan;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportExportService
{
    /**
     * Sanitasi sel teks untuk menangkal kerentanan CSV / Formula Injection.
     * Jika diawali dengan karakter =, +, -, @, sisipkan tanda petik tunggal (') di awal string.
     */
    public function sanitizeForCsv(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $dangerousPrefixes = ['=', '+', '-', '@', "\t", "\r"];
        $firstChar = substr($value, 0, 1);

        if (in_array($firstChar, $dangerousPrefixes, true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Stream CSV penjualan dengan konsumsi memori konstan (O(1) memory).
     */
    public function streamCsv(?string $idClient, Carbon $mulai, Carbon $selesai): StreamedResponse
    {
        $filename = sprintf('Rekap_Penjualan_%s_%s.csv', $mulai->format('Ymd'), $selesai->format('Ymd'));

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($idClient, $mulai, $selesai) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar Microsoft Excel membaca encoding karakter dengan benar
            fputs($handle, "\xEF\xBB\xBF");

            // Baris Header CSV
            fputcsv($handle, [
                'Nomor Pesanan',
                'Tanggal Transaksi',
                'Cabang Apotek',
                'Pelanggan',
                'Metode Layanan',
                'Obat / Item',
                'Kuantitas',
                'Harga Satuan (Snapshot)',
                'Subtotal',
                'Status Pesanan',
            ]);

            $query = Pesanan::query()
                ->with(['client', 'pengguna', 'items'])
                ->whereBetween('dibuat_pada', [$mulai->startOfDay(), $selesai->endOfDay()])
                ->orderBy('dibuat_pada', 'asc');

            if ($idClient) {
                $query->where('id_client', $idClient);
            }

            // Stream dalam chunk 200 baris untuk efisiensi RAM
            $query->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    foreach ($order->items as $item) {
                        fputcsv($handle, [
                            $this->sanitizeForCsv($order->nomor_pesanan),
                            $order->dibuat_pada?->format('Y-m-d H:i:s') ?? '',
                            $this->sanitizeForCsv($order->client?->nama_client ?? '-'),
                            $this->sanitizeForCsv($order->pengguna?->nama_lengkap ?? 'Guest'),
                            $order->metode_layanan,
                            $this->sanitizeForCsv($item->nama_obat),
                            $item->kuantitas,
                            (float) $item->harga_satuan,
                            (float) $item->subtotal,
                            $order->status,
                        ]);
                    }
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
```

#### 5.2 Template Cetak PDF Laporan Penjualan (`resources/views/admin/pdf/laporan-penjualan-template.blade.php`)
Template cetak resmi dengan tata letak laporan akuntansi siap cetak atau konversi PDF:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Konsolidasi - OmniPharma Network</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; margin: 20px; }
        .kop-surat { border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 20px; }
        .kop-surat h1 { font-size: 18px; font-weight: 900; margin: 0; text-transform: uppercase; }
        .kop-surat p { font-size: 10px; color: #4b5563; margin: 2px 0 0; }
        .meta-box { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .meta-box td { padding: 4px 0; }
        .badge-locked { display: inline-block; padding: 2px 8px; font-size: 9px; font-weight: bold; background-color: #111827; color: #ffffff; border-radius: 4px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th { background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 8px 6px; text-align: left; font-size: 10px; font-weight: 800; }
        table.data-table td { border: 1px solid #e5e7eb; padding: 6px; font-size: 10px; }
        .text-right { text-align: right; }
        .total-row { font-weight: 800; background-color: #f9fafb; }
        .signature-section { margin-top: 40px; width: 100%; }
        .signature-box { float: right; width: 200px; text-align: center; }
        .signature-line { margin-top: 60px; border-top: 1px solid #111827; font-weight: bold; }
    </style>
</head>
<body>
    <div class="kop-surat">
        <h1>OmniPharma Network — Executive Sales Report</h1>
        <p>Sistem Informasi Apotek Multi-Klien • Konsolidasi Finansial Lintas Cabang</p>
    </div>

    <table class="meta-box">
        <tr>
            <td style="width: 20%;"><strong>Periode Laporan:</strong></td>
            <td style="width: 40%;">{{ $periodeMulai }} s/d {{ $periodeSelesai }}</td>
            <td style="width: 20%;"><strong>Status Periode:</strong></td>
            <td style="width: 20%;"><span class="badge-locked">TERKUNCI (SNAPSHOT)</span></td>
        </tr>
        <tr>
            <td><strong>Dicetak Oleh:</strong></td>
            <td>{{ auth()->user()->nama_lengkap ?? 'Super Admin' }}</td>
            <td><strong>Waktu Cetak:</strong></td>
            <td>{{ now()->format('d M Y, H:i:s') }} WIB</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Cabang Apotek</th>
                <th style="width: 20%;" class="text-right">Total Transaksi</th>
                <th style="width: 20%;" class="text-right">Total Retur</th>
                <th style="width: 20%;" class="text-right">Omzet Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summaries as $index => $summary)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $summary->nama_client }}</strong></td>
                    <td class="text-right">{{ number_format($summary->total_pesanan) }} order</td>
                    <td class="text-right">{{ number_format($summary->total_retur) }} kasus</td>
                    <td class="text-right">Rp {{ number_format($summary->total_pendapatan, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2"><strong>KONSOLIDASI KESELURUHAN</strong></td>
                <td class="text-right"><strong>{{ number_format($summaries->sum('total_pesanan')) }} order</strong></td>
                <td class="text-right"><strong>{{ number_format($summaries->sum('total_retur')) }} kasus</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($summaries->sum('total_pendapatan'), 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>Banyuwangi, {{ now()->format('d M Y') }}<br>Penanggung Jawab Finansial,</p>
            <div class="signature-line">apt. Candia Purwa, S.Farm.</div>
            <p style="font-size: 9px; color: #6b7280; margin: 0;">SIPA: 19980412/SIPA_35.10/2024</p>
        </div>
    </div>
</body>
</html>
```

#### 5.3 Controller Laporan Penjualan (`app/Modules/Reporting/Http/Controllers/SalesReportController.php`)
```php
<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\RingkasanPeriodePenjualan;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use App\Modules\Reporting\Services\SalesReportExportService;
use App\Modules\Reporting\Services\SalesReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function __construct(
        protected SalesReportService $reportService,
        protected CloseSalesPeriodAction $closePeriodAction,
        protected SalesReportExportService $exportService
    ) {}

    public function index(Request $request)
    {
        $mulai = Carbon::parse($request->input('periode_mulai', '2026-09-01'));
        $selesai = Carbon::parse($request->input('periode_selesai', '2026-09-16'));
        $idClient = $request->input('id_client');

        $clients = Client::all();
        $kpis = $this->reportService->getExecutiveKpis($idClient, $mulai, $selesai);
        $topDrugs = $this->reportService->getTopSellingDrugs($idClient, $mulai, $selesai);
        $branchBreakdown = $this->reportService->getBranchPerformanceBreakdown($mulai, $selesai);
        $summaries = RingkasanPeriodePenjualan::query()->orderBy('periode_mulai', 'desc')->get();

        return view('admin.dashboard', [
            'clients' => $clients,
            'kpis' => $kpis,
            'topDrugs' => $topDrugs,
            'branchBreakdown' => $branchBreakdown,
            'summaries' => $summaries,
            'periodeMulai' => $mulai->toDateString(),
            'periodeSelesai' => $selesai->toDateString(),
        ]);
    }

    public function tutupPeriode(Request $request): JsonResponse
    {
        $request->validate([
            'id_client' => 'required|uuid',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
        ]);

        $mulai = Carbon::parse($request->input('periode_mulai'));
        $selesai = Carbon::parse($request->input('periode_selesai'));

        $summary = $this->closePeriodAction->execute(
            $request->input('id_client'),
            $mulai,
            $selesai
        );

        return response()->json([
            'success' => true,
            'message' => "Periode penjualan {$mulai->format('d M Y')} s/d {$selesai->format('d M Y')} berhasil ditutup dan seluruh snapshot transaksi terkunci.",
            'status_terkunci' => true,
            'data' => $summary,
        ]);
    }

    public function ekspor(Request $request)
    {
        $format = $request->input('format', 'csv');
        $mulai = Carbon::parse($request->input('periode_mulai', '2026-09-01'));
        $selesai = Carbon::parse($request->input('periode_selesai', '2026-09-16'));
        $idClient = $request->input('id_client');

        if ($format === 'pdf') {
            $summaries = RingkasanPeriodePenjualan::query()
                ->whereBetween('periode_mulai', [$mulai->toDateString(), $selesai->toDateString()])
                ->get();

            return view('admin.pdf.laporan-penjualan-template', [
                'summaries' => $summaries,
                'periodeMulai' => $mulai->format('d M Y'),
                'periodeSelesai' => $selesai->format('d M Y'),
            ]);
        }

        return $this->exportService->streamCsv($idClient, $mulai, $selesai);
    }
}
```

---

### Fase 6: Pengujian Otomatis (Feature Tests)

#### 6.1 Test Reverb Broadcast (`tests/Feature/ReverbOrderStatusBroadcastTest.php`)
```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Modules\Ordering\Events\IncomingOrderPlaced;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReverbOrderStatusBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_update_dispatches_broadcast_now_event(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $client = Client::factory()->create();
        $pengguna = Pengguna::factory()->create();

        $pesanan = Pesanan::factory()->create([
            'id_client' => $client->id_client,
            'id_pengguna' => $pengguna->id_pengguna,
            'status' => 'menunggu_verifikasi',
        ]);

        // Simulasikan pembaruan status
        $statusSebelumnya = $pesanan->status;
        $pesanan->update(['status' => 'diproses']);

        OrderStatusUpdated::dispatch($pesanan, $statusSebelumnya, 'diproses', 'Resep disetujui');

        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($pesanan) {
            return $event->pesanan->id_pesanan === $pesanan->id_pesanan &&
                   $event->statusBaru === 'diproses' &&
                   $event->broadcastOn()[0]->name === 'private-order.'.$pesanan->id_pesanan;
        });
    }

    public function test_incoming_order_dispatches_tenant_channel_broadcast(): void
    {
        Event::fake([IncomingOrderPlaced::class]);

        $client = Client::factory()->create();
        $pesanan = Pesanan::factory()->create(['id_client' => $client->id_client]);

        IncomingOrderPlaced::dispatch($pesanan);

        Event::assertDispatched(IncomingOrderPlaced::class, function ($event) use ($client) {
            return $event->broadcastOn()[0]->name === 'private-tenant.'.$client->id_client;
        });
    }
}
```

#### 6.2 Test Rekonsiliasi & Database Unique Constraint (`tests/Feature/SalesReconciliationAndPeriodLockTest.php`)
```php
<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\ItemPesanan;
use App\Models\RingkasanPeriodePenjualan;
use App\Modules\Reporting\Actions\CloseSalesPeriodAction;
use App\Modules\Reporting\Services\SalesReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SalesReconciliationAndPeriodLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_period_can_be_locked_and_creates_immutable_snapshot(): void
    {
        $client = Client::factory()->create(['nama_client' => 'Cabang Banyuwangi Kota']);
        $pengguna = Pengguna::factory()->create();

        $pesanan = Pesanan::factory()->create([
            'id_client' => $client->id_client,
            'id_pengguna' => $pengguna->id_pengguna,
            'status' => 'selesai',
            'dibuat_pada' => Carbon::parse('2026-09-10 10:00:00'),
        ]);

        ItemPesanan::create([
            'id_item' => (string) \Illuminate\Support\Str::uuid(),
            'id_pesanan' => $pesanan->id_pesanan,
            'id_obat' => (string) \Illuminate\Support\Str::uuid(),
            'nama_obat' => 'Paracetamol 500mg',
            'harga_satuan' => 15000,
            'kuantitas' => 2,
            'subtotal' => 30000,
            'dibuat_pada' => now(),
        ]);

        $action = new CloseSalesPeriodAction(new SalesReportService());
        $summary = $action->execute(
            $client->id_client,
            Carbon::parse('2026-09-01'),
            Carbon::parse('2026-09-16')
        );

        $this->assertInstanceOf(RingkasanPeriodePenjualan::class, $summary);
        $this->assertTrue($summary->status_terkunci);
        $this->assertEquals(30000, (float) $summary->total_pendapatan);
        $this->assertEquals('Cabang Banyuwangi Kota', $summary->nama_client);
    }

    public function test_duplicate_period_lock_is_rejected_by_unique_constraint(): void
    {
        $client = Client::factory()->create();
        $action = new CloseSalesPeriodAction(new SalesReportService());

        $mulai = Carbon::parse('2026-09-01');
        $selesai = Carbon::parse('2026-09-16');

        // Kunci pertama kali berhasil
        $action->execute($client->id_client, $mulai, $selesai);

        // Kunci kedua kali wajib melempar Exception dan ditolak
        $this->expectException(RuntimeException::class);
        $action->execute($client->id_client, $mulai, $selesai);
    }
}
```

#### 6.3 Test Ekspor & Proteksi CSV Injection (`tests/Feature/SalesReportExportTest.php`)
```php
<?php

namespace Tests\Feature;

use App\Modules\Reporting\Services\SalesReportExportService;
use Tests\TestCase;

class SalesReportExportTest extends TestCase
{
    public function test_csv_sanitization_neutralizes_formula_injection_characters(): void
    {
        $service = new SalesReportExportService();

        // Uji berbagai vektor formula berbahaya
        $this->assertEquals("'=SUM(A1:A10)", $service->sanitizeForCsv('=SUM(A1:A10)'));
        $this->assertEquals("'+123456", $service->sanitizeForCsv('+123456'));
        $this->assertEquals("'-100", $service->sanitizeForCsv('-100'));
        $this->assertEquals("'@cmd", $service->sanitizeForCsv('@cmd'));

        // Karakter normal tidak boleh dimodifikasi
        $this->assertEquals("Paracetamol 500mg", $service->sanitizeForCsv('Paracetamol 500mg'));
        $this->assertEquals("#ORD-20260917-0042", $service->sanitizeForCsv('#ORD-20260917-0042'));
    }
}
```

---

## 6. Presentation Guide for Lecturer (Pak Devit Suwardiyanto)

Bagian ini disusun khusus sebagai panduan pertahanan lisan (*oral defense script*) bagi **Candia Purwa** saat mempresentasikan modul real-time WebSockets dan reporting akuntansi di depan **Pak Devit Suwardiyanto**.

### 6.1 Penguasaan Konsep Arsitektur Inti

#### 1. Perbedaan Mendasar Laravel Reverb vs HTTP Polling Tradisional
> **Pertanyaan Pak Devit:**  
> *"Mengapa Anda repot-repot memasang Laravel Reverb? Bukankah cukup pakai polling AJAX atau `wire:poll` setiap 3 detik di Livewire?"*

**Jawaban Candia yang Memuaskan:**  
* "Pak Devit, polling HTTP berulang (*short-polling* setiap 3 detik) menciptakan beban komputasi yang tidak efisien (*overhead*) pada arsitektur multi-klien:
  1. **Beban Handshake & Header HTTP**: Setiap request polling mengirimkan header HTTP lengkap, cookie sesi, dan melewati pipeline *middleware* Laravel dari awal. Pada 1.000 pelanggan aktif, server menerima 20.000 request kosong per menit yang sebagian besar menghasilkan respons 304 Not Modified.
  2. **Latency Gap**: Polling memiliki rata-rata jeda waktu hingga 3 detik sebelum pelanggan mengetahui status resepnya telah diverifikasi oleh apoteker.
  3. **Arsitektur Reverb**: Laravel Reverb bekerja berbasis standar WebSocket (RFC 6455) di mana koneksi TCP persisten dibuka sekali pada port 8080. Ketika event `OrderStatusUpdated` dipicu dengan `ShouldBroadcastNow`, Reverb langsung mendorong frame data ke socket pelanggan dalam hitungan milidetik (*sub-50ms latency*), menghemat bandwidth jaringan hingga 90% dan membebaskan worker PHP-FPM untuk transaksi pembayaran."

#### 2. Mekanisme Keamanan Otorisasi Private Channel
> **Pertanyaan Pak Devit:**  
> *"Bagaimana Anda menjamin bahwa pelanggan Cabang Rogojampi tidak bisa membuka DevTools browser lalu menyadap pesan WebSocket transaksi obat dari Cabang Banyuwangi Kota?"*

**Jawaban Candia yang Memuaskan:**  
* "Kami menerapkan **Zero-Trust Private Channel Authorization** di berkas `routes/channels.php`:
  1. Channel disetel dengan prefiks `private-order.{id_pesanan}` dan `private-tenant.{id_client}`. Klien WebSocket tidak dapat melakukan *subscribe* sebelum melakukan autentikasi ke rute `POST /broadcasting/auth`.
  2. Pada rute otorisasi tersebut, Laravel mengekstrak token sesi atau kredensial pengguna yang sedang login (`$user`).
  3. Callback otorisasi kami mencocokkan secara ketat: apakah `$user->id_pengguna` sama persis dengan `id_pengguna` pada model `Pesanan`. Apabila ada pengguna jahat yang mencoba mendengarkan ID pesanan milik orang lain, handler langsung mengembalikan nilai `false` yang menyebabkan Reverb menolak koneksi dengan kode status HTTP 403 Forbidden."

#### 3. Integritas Rekonsiliasi & Immutabilitas Snapshot Periode (ADR-0008)
> **Pertanyaan Pak Devit:**  
> *"Jika sebulan setelah penutupan buku ada admin yang mengubah master harga obat Paracetamol di database dari Rp 15.000 menjadi Rp 50.000, apakah laporan omzet periode lalu ikut berubah?"*

**Jawaban Candia yang Memuaskan:**  
* "Sama sekali tidak akan berubah, Pak Devit. Sistem kami menerapkan arsitektur *Controlled Denormalization & Price Snapshot* sesuai **ADR-0008**:
  1. Saat transaksi terjadi, nilai `harga_satuan` dibekukan ke baris `item_pesanan`.
  2. Saat Super Admin menutup periode melalui `CloseSalesPeriodAction`, total omzet dan kuantitas penjualan dihitung dan dibukukan ke tabel `ringkasan_periode_penjualan` dengan flag `status_terkunci = true`.
  3. Baris ringkasan tersebut dilindungi oleh *composite unique constraint* `UNIQUE(id_client, periode_mulai, periode_selesai)` pada skema MariaDB, sehingga bersifat *append-only* dan tidak dapat ditimpa ulang.
  4. Seluruh fitur ekspor PDF dan Excel membaca langsung dari tabel snapshot ringkasan yang terkunci, bukan menghitung ulang secara dinamis dari katalog master obat. Dengan demikian, laporan historis 100% konsisten untuk keperluan audit keuangan."

#### 4. Mitigasi CSV Injection / Formula Injection
> **Pertanyaan Pak Devit:**  
> *"Ketika Anda mengekspor data ke file Excel atau CSV, celah keamanan apa yang paling berbahaya dan bagaimana cara Anda mengatasinya?"*

**Jawaban Candia yang Memuaskan:**  
* "Celah yang paling rawan adalah **CSV Formula Injection**. Jika ada pembeli yang mengisi nama profil atau catatan obat dengan formula berbahaya seperti `=cmd|'/c calc'!A0` atau `=HYPERLINK(...)`, lalu file CSV tersebut dibuka oleh manajer apotek menggunakan Microsoft Excel, Excel akan mengeksekusi perintah tersebut secara otomatis (*Remote Code Execution / Data Exfiltration*).  
Kami mengatasinya di `SalesReportExportService::sanitizeForCsv()` dengan mendeteksi karakter awalan berbahaya (`=`, `+`, `-`, `@`, tab, newline) dan secara otomatis menyisipkan karakter *single quote* (`'`) di depannya, sehingga Excel membacanya murni sebagai teks string statis dan bukan rumus."

---

### 6.2 Skenario Demo Interaktif (Step-by-Step Live Demo)

Saat sesi demonstrasi di depan Pak Devit, jalankan langkah-langkah terstruktur berikut:

```
[Langkah 1: Split Screen Setup]
Buka 2 window browser berdampingan:
Window Kiri  : Tampilan Pelanggan (/klien/banyuwangi-kota/pesanan/ORD-20260917-0042)
Window Kanan : Dasbor Apoteker Cabang (/cabang/banyuwangi-kota/dashboard)

[Langkah 2: Trigger Status Pembaruan]
Di dasbor apoteker (Window Kanan), klik tombol "Verifikasi Resep: Disetujui".

[Langkah 3: Amati Pembaruan Real-Time Reverb]
Perhatikan Window Kiri milik Pelanggan:
- Tanpa reload browser, indikator stepper "Verifikasi Resep" langsung berubah hijau centang.
- Status stepper maju otomatis ke "Obat Disiapkan".
- Teks catatan apoteker langsung muncul di layar pelanggan.

[Langkah 4: Dasbor Finansial & Tutup Periode]
Buka Window Super Admin (/admin/dashboard):
- Tunjukkan KPI Omzet Konsolidasi (Rp 142.850.000) dan rincian transaksi per cabang.
- Klik tombol "TUTUP PERIODE & KUNCI SNAPSHOT".
- Tunjukkan dialog konfirmasi dan klik "Kunci Snapshot Sekarang".
- Tunjukkan bahwa badge berubah menjadi "● Snapshot Terkunci" dan tombol otomatis di-disable.
- Coba kirim request tutup periode kedua via Postman/Curl untuk membuktikan penolakan database constraint (idempotency).

[Langkah 5: Ekspor Laporan Finansial]
- Klik tombol "EKSPOR LAPORAN (XLSX/PDF)".
- Buka file CSV yang terunduh dan tunjukkan struktur data yang rapi dan terproteksi formula injection.
- Tampilkan print preview PDF dengan kop resmi OmniPharma dan tanda tangan apoteker penanggung jawab.
```

---

### 6.3 Antisipasi Pertanyaan Kritis Pak Devit & Kunci Jawaban

| # | Pertanyaan Kritis Pak Devit | Aspek Pengujian | Jawaban Taktis Candia |
|---|---|---|---|
| 1 | *"Bagaimana jika server Reverb tiba-tiba down atau koneksi internet pelanggan putus nyambung saat menunggu pesanan?"* | Resilience & High Availability | "Frontend Echo kami di `order-status.blade.php` dilengkapi listener `state_change` pada konektor Pusher/Reverb. Jika koneksi terputus (`koneksiWebSocket === false`), Alpine.js secara otomatis mengaktifkan *fallback polling* HTTP asinkron berkala setiap 15 detik ke endpoint `/api/pesanan/{id}/status`, disertai badge status visual di UI pelanggan sehingga pelacakan tetap berjalan mulus tanpa gangguan." |
| 2 | *"Mengapa pada event `OrderStatusUpdated` Anda menggunakan `ShouldBroadcastNow` dan bukan `ShouldBroadcast` biasa?"* | Event Loop & Queue Architecture | "Penggunaan `ShouldBroadcast` biasa akan mendorong event ke antrean Redis (`queue:work`). Jika ada penumpukan antrean pemrosesan gambar resep atau email, update status pesanan pelanggan bisa mengalami delay beberapa detik. Karena payload status pesanan sangat ringan (hanya string UUID dan status), kami menggunakan `ShouldBroadcastNow` agar event langsung dipancarkan ke socket Reverb secara sinkron tanpa menunggu worker antrean." |
| 3 | *"Tunjukkan di mana letak constraint basis data yang mencegah penutupan ganda untuk periode yang sama!"* | Database Normalization & Constraint | "Letaknya ada pada migrasi tabel `ringkasan_periode_penjualan`: `$table->unique(['id_client', 'periode_mulai', 'periode_selesai'])`. Apabila Super Admin mencoba menutup periode yang sama untuk cabang yang sama dua kali, MariaDB langsung melempar `QueryException` (error code 23000 duplicate entry) yang kami tangani di `CloseSalesPeriodAction` dengan pesan informatif bahwa periode tersebut telah dikunci sebelumnya." |
| 4 | *"Bagaimana Anda mengekspor laporan jika data transaksi mencapai 500.000 baris tanpa membuat server PHP mengalami Out of Memory (OOM)?"* | Memory Management & Scalability | "Kami tidak memuat seluruh 500.000 baris ke dalam memori Eloquent via `Pesanan::all()`. Kami menggunakan `Symfony\Component\HttpFoundation\StreamedResponse` yang dipadukan dengan method `chunk(200)` dari Eloquent. Data ditarik bertahap per 200 baris, langsung ditulis ke buffer output `php://output`, lalu memorinya segera dibersihkan oleh garbage collector, sehingga penggunaan RAM PHP konstan di kisaran 15–20 MB." |

---

## 7. Quality Gate & Definition of Done (DoD)

Pengerjaan modul oleh Candia Purwa dinyatakan tuntas apabila memenuhi kriteria berikut:
1. **Fungsionalitas WebSockets**: Event `OrderStatusUpdated` dan `IncomingOrderPlaced` berhasil disiarkan melalui server Reverb dan diterima oleh client-side Echo tanpa error konsol.
2. **Keamanan Channel**: Akses ke private channel `order.{id_pesanan}` dan `tenant.{id_client}` diverifikasi via `routes/channels.php` dan mengembalikan 403 bagi pengguna tanpa hak akses.
3. **Integritas Penutupan Periode**: Aksi `CloseSalesPeriodAction` membukukan baris `ringkasan_periode_penjualan` yang terkunci, dan penguncian berulang berhasil digagalkan oleh *unique constraint*.
4. **Keamanan & Efisiensi Ekspor**: File CSV terunduh secara streaming dengan waktu tanggap < 2 detik dan seluruh sel formula berbahaya telah disanitasi.
5. **Cakupan Pengujian**: Seluruh unit dan feature test di `tests/Feature/ReverbOrderStatusBroadcastTest.php`, `tests/Feature/SalesReconciliationAndPeriodLockTest.php`, dan `tests/Feature/SalesReportExportTest.php` lulus 100% (*green*).
6. **Kesiapan Ujian Pak Devit**: Mahasiswa menguasai seluruh alur demonstrasi dan mampu menjawab pertanyaan penguji secara percaya diri dan berbasis bukti kode.
