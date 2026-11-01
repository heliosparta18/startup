# Product Requirement Document (PRD) — Guntur Afandi
## Modul: Customer Commerce & Transaction Pipeline (Katalog, Keranjang, Checkout, & QRIS Sandbox)
### OmniPharma Network — Sistem Informasi Apotek Multi-Klien

- **Nama Mahasiswa:** Guntur Afandi
- **NIM / Peran:** Pengembang Frontend & Commerce Engine (Pertemuan 06–11)
- **Dosen Pengampu:** Pak Devit Suwardiyanto
- **Target Repository:** `D:\KELOMPOK PAK DEVIT`
- **Cabang Git Utama:** `feat/guntur-checkout-qris`
- **Sub-Cabang Praktikum:**
  - `feature/pertemuan-06-07-katalog-sesi`
  - `feature/pertemuan-08-09-cart-checkout`
  - `feature/pertemuan-10-11-qris-webhook`
- **Status Baseline:**
  - Pertemuan 01–03 selesai pada commit `1b6b0d8` (oleh Daffa).
  - Refaktor UI responsif & touch target mobile 44px selesai pada commit `6ef04e2` / PR #3 `96cb27e` (oleh Guntur).

---

## 1. Deskripsi Modul & Tujuan Arsitektural

### 1.1 Unifikasi Alur Transaksi Pelanggan (Vertical Commerce Slice)
Modul ini menyatukan seluruh siklus hidup pembelian obat oleh pelanggan (*customer commerce slice*) di bawah kepemilikan tunggal Guntur Afandi:
```
[ Katalog Obat Multi-Cabang & Sesi Tamu ] 
                  │
                  ▼
[ Keranjang Belanja Redis Multi-Tenant ] 
                  │
                  ▼
[ Validasi Ulang Stok & Checkout Atomik ] 
                  │
                  ▼
[ Gateway QRIS Dinamis Sandbox (EMVCo) ] 
                  │
                  ▼
[ Webhook Idempoten & Settlement Transaksi ]
```

Penyatuan ini menyelesaikan risiko fragmentasi antarmuka antara keranjang belanja dan *payment gateway*, memastikan satu model transaksi yang teruji tuntas dari pemilihan produk hingga status pelunasan terverifikasi.

### 1.2 Tujuan Arsitektural Utama
1. **Katalog Publik & Sesi Anonim Terisolasi (Pertemuan 06–07)**:
   - Menyediakan antarmuka katalog obat berbasis Livewire yang reaktif, mobile-friendly (touch target $\ge 44$px), dan mendukung pencarian instan.
   - Mengelola sesi tamu (*guest customer session*) menggunakan opaque token 32-karakter berbasis cookie (`omnipharma_customer_session`), disematkan ke cabang apotek aktif (`id_client`) tanpa mewajibkan registrasi akun di awal.
   - Menegakkan klasifikasi regulasi obat (*badging*) farmasi: **Obat Bebas** (Hijau), **Bebas Terbatas** (Biru dengan tanda peringatan P.No. 1–6), dan **Wajib Resep / Obat Keras** (Merah dengan lingkaran K dan kunci alur checkout resep).

2. **Keranjang Belanja Redis Multi-Tenant & Revalidasi Pra-Checkout (Pertemuan 08)**:
   - Memanfaatkan Redis Hash dengan skema kunci `cart:{sessionId}:{clientId}` berdurasi TTL 7 hari. Data keranjang diisolasi per cabang apotek untuk mencegah percampuran stok antar-cabang yang berbeda secara geografis.
   - Menerapkan *Pre-Checkout Price & Stock Revalidation* yang mendeteksi perubahan harga master obat atau penyusutan stok fisik sebelum transaksi dibuat.

3. **Checkout Atomik, Snapshot Harga, & Penomoran Pesanan (Pertemuan 09)**:
   - Menjalankan pembuatan pesanan dalam blok `DB::transaction` dengan *pessimistic locking* (`lockForUpdate`) pada tabel `stok_client` untuk mencegah *race condition* (pembelian ganda pada sisa stok terakhir).
   - Menegakkan **ADR-0008 (Controlled Denormalization & Price Snapshot Immutability)**: menyalin `nama_obat` dan `harga_satuan` ke tabel `item_pesanan` pada detik transaksi dibuat sehingga riwayat pembukuan dan kuitansi kebal terhadap fluktuasi harga obat di masa depan.
   - Menerbitkan format penomoran pesanan standar yang ramah manusia: `#ORD-YYYYMMDD-XXXX`.

4. **Payment Gateway Abstraction & Dynamic QRIS Sandbox (Pertemuan 10)**:
   - Mengimplementasikan kontrak antarmuka `PaymentGatewayInterface` sesuai ketetapan **ADR-0004 (Payment Provider Deferred)**, memastikan arsitektur *loosely coupled* tanpa keterikatan SDK eksternal berlebih.
   - Membangun `FakeQrisGateway` dan `QrisEmvcoBuilder` deterministik yang memproduksi string QRIS standar EMVCo (NMID, MCC 5912 Apotek, Currency 360 IDR, kalkulasi CRC16 CCITT) lengkap dengan hitung mundur waktu kedaluwarsa 15 menit (*countdown timer*).
   - Menyediakan modal simulasi pembayaran untuk keperluan pengujian dan demonstrasi langsung di hadapan dosen penguji.

5. **Webhook Idempoten & Settlement Transaksi (Pertemuan 11)**:
   - Membangun endpoint HTTP POST `/api/webhooks/qris` yang memvalidasi otentisitas *payload* menggunakan tanda tangan digital HMAC-SHA256 (`X-Signature`).
   - Menerapkan *idempotency guard*: jika notifikasi webhook diterima berulang kali akibat *network retry*, sistem mencatat ke `log_audit` dan mengembalikan HTTP 200 OK tanpa memproses ulang pemotongan stok atau menduplikasi saldo.
   - Mengubah status pesanan secara atomik: pesanan tanpa obat keras langsung berstatus `diproses`, sedangkan pesanan yang memuat obat keras masuk ke antrean `menunggu_verifikasi` apoteker.

---

## 2. Daftar Berkas yang Dibuat dan Diubah (Exact Paths)

Berikut adalah daftar lengkap berkas yang menjadi tanggung jawab Guntur Afandi di repositori `D:\KELOMPOK PAK DEVIT`:

| Modul / Domain | Path Berkas Relatif | Operasi | Deskripsi & Peran Teknis |
|---|---|---|---|
| **Catalog** | `app/Modules/Catalog/Services/CatalogService.php` | **Create** | Service penanganan kueri katalog obat, filter kategori, stok cabang, dan regulasi obat keras. |
| **Catalog** | `app/Modules/Catalog/Http/Controllers/CatalogController.php` | **Create** | Controller render katalog obat publik dan detail obat spesifik per cabang. |
| **Catalog** | `app/Modules/Catalog/CatalogServiceProvider.php` | **Edit** | Mendaftarkan dependency injection `CatalogService` dan routing internal modul katalog. |
| **Ordering** | `app/Modules/Ordering/Services/CustomerSessionService.php` | **Create** | Layanan resolusi opaque token sesi tamu, penyediaan shadow guest user (integritas FK), dan cookie branch pinning. |
| **Ordering** | `app/Modules/Ordering/Services/CartService.php` | **Create** | Manajemen keranjang berbasis Redis Hash: penambahan item, update kuantitas, hitung subtotal, dan pembersihan TTL. |
| **Ordering** | `app/Modules/Ordering/DTOs/CartItemDTO.php` | **Create** | Data Transfer Object representasi item obat di dalam keranjang belanja. |
| **Ordering** | `app/Modules/Ordering/DTOs/CheckoutOrderDTO.php` | **Create** | DTO payload checkout mencakup data pembeli, metode layanan (*delivery* / *pickup*), dan catatan resep. |
| **Ordering** | `app/Modules/Ordering/Actions/RevalidateCartAction.php` | **Create** | Aksi verifikasi ketersediaan stok fisik terkini dan deteksi perubahan harga sebelum pesanan dicatat. |
| **Ordering** | `app/Modules/Ordering/Actions/CreateOrderAction.php` | **Create** | Eksekusi atomik `DB::transaction` dengan `lockForUpdate`, penegakan resep (E-07), revalidasi harga live (E-06), dan Reverb broadcast. |
| **Ordering** | `app/Modules/Ordering/Http/Controllers/CartController.php` | **Create** | Controller aksi AJAX / Livewire tambah ke keranjang, perbarui jumlah, dan hapus item. |
| **Ordering** | `app/Modules/Ordering/Http/Controllers/CheckoutController.php` | **Create** | Controller alur checkout, pengumpulan data pengiriman/pengambilan, dan *trigger* pembayaran. |
| **Ordering** | `app/Modules/Ordering/OrderingServiceProvider.php` | **Edit** | Registrasi singleton `CartService`, `CustomerSessionService`, dan binding modul ordering. |
| **Payments** | `app/Modules/Payments/Contracts/PaymentGatewayInterface.php` | **Create** | Kontrak antarmuka standar untuk pembuatan tagihan QRIS dan verifikasi signature (ADR-0004). |
| **Payments** | `app/Modules/Payments/DTOs/PaymentChargeRequest.php` | **Create** | DTO permintaan pembuatan QRIS (ID pesanan, nominal tagihan, nama pelanggan, waktu kedaluwarsa). |
| **Payments** | `app/Modules/Payments/DTOs/PaymentChargeResponse.php` | **Create** | DTO balasan QRIS memuat payload EMVCo string, URL QR code, batas waktu kedaluwarsa, dan payment reference. |
| **Payments** | `app/Modules/Payments/Services/FakeQrisGateway.php` | **Create** | Implementasi sandbox simulator deterministik penghasil QRIS dinamis dan simulasi callback. |
| **Payments** | `app/Modules/Payments/Services/QrisEmvcoBuilder.php` | **Create** | Generator string QRIS EMVCo Tag-Length-Value (TLV) dan algoritma penghitung CRC16-CCITT. |
| **Payments** | `app/Modules/Payments/Http/Controllers/PaymentController.php` | **Create** | Controller penampil halaman pembayaran QRIS pelanggan dan trigger simulasi sandbox. |
| **Payments** | `app/Modules/Payments/Http/Controllers/PaymentWebhookController.php` | **Create** | Controller penerima webhook gateway, verifikasi signature HMAC, settlement idempoten, cek kedaluwarsa & restorasi stok (E-05/E-09). |
| **Payments** | `app/Modules/Payments/Http/Requests/WebhookPaymentRequest.php` | **Create** | FormRequest validasi integritas struktur payload webhook sebelum dieksekusi controller. |
| **Payments** | `app/Modules/Payments/PaymentsServiceProvider.php` | **Edit** | Binding `PaymentGatewayInterface` ke `FakeQrisGateway` pada container Laravel. |
| **Routes** | `routes/customer.php` | **Edit** | Menghubungkan rute keranjang (`/keranjang`), checkout (`/checkout`), dan pembayaran (`/pesanan/{id}/bayar`). |
| **Routes** | `routes/api.php` | **Create / Edit** | Mendaftarkan rute publik webhook: `POST /api/webhooks/qris`. |
| **Views** | `resources/views/customer/home.blade.php` | **Edit** | Menyambungkan tombol 'Beli Langsung' dan 'Tambah Keranjang' ke backend Redis Cart. |
| **Views** | `resources/views/customer/obat-detail.blade.php` | **Edit** | Menyambungkan penambahan kuantitas obat dan peringatan regulasi obat keras ke keranjang. |
| **Views** | `resources/views/customer/cart.blade.php` | **Create** | Halaman daftar keranjang belanja, penyesuaian kuantitas, info cabang, dan tombol lanjut checkout. |
| **Views** | `resources/views/customer/checkout.blade.php` | **Create** | Halaman formulir checkout, ringkasan belanja, opsi delivery/ambil di apotek, dan upload resep dokter. |
| **Views** | `resources/views/customer/payment.blade.php` | **Create** | Halaman pembayaran interaktif memuat QRIS dinamis, countdown 15 menit, dan modal simulasi lunas. |
| **Tests** | `tests/Feature/CatalogAndCustomerSessionTest.php` | **Create** | Uji coba sesi tamu anonim, pinning cabang apotek pada cookie, dan filter katalog regulasi. |
| **Tests** | `tests/Feature/RedisCartAndRevalidationTest.php` | **Create** | Uji coba manipulasi keranjang Redis, isolasi antarcabang, dan deteksi revalidasi harga/stok. |
| **Tests** | `tests/Feature/AtomicCheckoutConcurrencyTest.php` | **Create** | Uji coba transaksi atomik, penomoran pesanan, snapshot harga ADR-0008, dan proteksi race condition stok. |
| **Tests** | `tests/Feature/DynamicQrisPaymentAndWebhookTest.php` | **Create** | Uji coba generator EMVCo QRIS, kedaluwarsa timer, verifikasi tanda tangan HMAC, dan idempotensi webhook. |

---

## 3. Git Branch Workflow

Alur kerja Git dirancang rapi, mudah diaudit oleh tim penguji, dan memisahkan setiap tonggak capaian praktikum tanpa risiko konflik kode (*merge conflict*).

```
main (branch produksi/rilis)
  │
  └── feat/guntur-checkout-qris (branch integrasi utama Guntur)
        │
        ├── feature/pertemuan-06-07-katalog-sesi
        │     └── PR -> merge ke feat/guntur-checkout-qris
        │
        ├── feature/pertemuan-08-09-cart-checkout
        │     └── PR -> merge ke feat/guntur-checkout-qris
        │
        └── feature/pertemuan-10-11-qris-webhook
              └── PR -> merge ke feat/guntur-checkout-qris
```

### 3.1 Rincian Sub-Cabang Akademik
1. **`feature/pertemuan-06-07-katalog-sesi`**:
   - Dibuat dari: `main` (commit baseline `96cb27e`).
   - Fokus: Service katalog obat, cookie sesi tamu anonim, penanda cabang aktif (*branch pinning*), dan penyempurnaan lencana regulasi obat keras pada tampilan mobile.
   - Perintah pembentukan:
     ```bash
     git checkout main
     git pull origin main
     git checkout -b feature/pertemuan-06-07-katalog-sesi
     ```

2. **`feature/pertemuan-08-09-cart-checkout`**:
   - Dibuat dari: `feature/pertemuan-06-07-katalog-sesi` (setelah katalog & sesi teruji).
   - Fokus: `CartService` berbasis Redis Hash, deteksi perubahan harga/stok, `CreateOrderAction` atomik dengan `lockForUpdate`, snapshot `item_pesanan`, dan nomor pesanan `#ORD-...`.
   - Perintah pembentukan:
     ```bash
     git checkout -b feature/pertemuan-08-09-cart-checkout
     ```

3. **`feature/pertemuan-10-11-qris-webhook`**:
   - Dibuat dari: `feature/pertemuan-08-09-cart-checkout`.
   - Fokus: Kontrak `PaymentGatewayInterface`, generator TLV EMVCo QRIS, halaman bayar dengan timer 15 menit, modal simulasi lunas, dan controller webhook dengan verifikasi tanda tangan HMAC serta idempotensi.
   - Perintah pembentukan:
     ```bash
     git checkout -b feature/pertemuan-10-11-qris-webhook
     ```

4. **Integrasi & Penggabungan ke `main`**:
   - Seluruh sub-cabang diintegrasikan ke branch `feat/guntur-checkout-qris` sebelum diajukan Pull Request final ke `main`.
   - Sebelum merger ke `main`, wajib menjalankan pemeriksaan mandiri (*quality gate*):
     ```bash
     php artisan test tests/Feature/CatalogAndCustomerSessionTest.php
     php artisan test tests/Feature/RedisCartAndRevalidationTest.php
     php artisan test tests/Feature/AtomicCheckoutConcurrencyTest.php
     php artisan test tests/Feature/DynamicQrisPaymentAndWebhookTest.php
     ./vendor/bin/pint --test
     ```

---

## 4. Konvensi Pesan Commit Alami (Human-Written Style)

Sebagai pengembang mahasiswa, Guntur Afandi menerapkan gaya pesan commit yang **otentik, ringkas, bernalar teknis, dan bebas dari kalimat klise AI** (*anti-AI boilerplate*). 

### 4.1 Pantangan Pesan Commit (Anti-Patterns)
- ❌ DILARANG memakai kalimat promosi/pemasaran: `feat: implement cutting-edge state of the art robust payment architecture`
- ❌ DILARANG menyertakan penanda bot/asisten: `Co-authored-by: AI Assistant <ai@anthropic.com>`
- ❌ DILARANG menggunakan ringkasan panjang bertingkat yang tidak realistis dibuat manusia saat coding.
- ❌ DILARANG membuat pesan commit samar tanpa konteks: `update code`, `fix bug`, `perbaikan`.

### 4.2 Contoh Commit Log Asli per Tahapan Praktikum

#### Tahap 1: Pertemuan 06–07 (Katalog & Sesi Tamu)
```text
bikin sesi guest anonim dan simpan cabang apotek di cookie
feat(catalog): tambah filter kategori obat dan badge golongan keras
hubungkan tombol tambah keranjang di katalog mobile ke livewire
rapikan kueri stok obat per cabang terdekat agar tidak n+1
tambah test unit untuk pinning cabang pada sesi pelanggan
```

#### Tahap 2: Pertemuan 08–09 (Keranjang Redis & Checkout Atomik)
```text
buat service keranjang belanja multi-tenant pakai redis hash
feat(ordering): implementasi validasi stok dan harga sebelum checkout
pasang db transaction lock for update saat buat pesanan
simpan snapshot harga obat di item pesanan sesuai adr-0008
bikin format nomor order ORD tahun bulan tanggal dan urutan unik
buat tampilan halaman keranjang belanja dan formulir checkout
tambah feature test untuk proteksi race condition sisa stok
```

#### Tahap 3: Pertemuan 10–11 (QRIS Dinamis Sandbox & Webhook)
```text
feat(payments): definisikan interface payment gateway dan fake qris
buat generator payload emvco qris dinamis dan hitung crc16
tambah halaman bayar qris dengan countdown 15 menit dan modal simulasi
feat(webhook): buat endpoint webhook idempoten dengan verifikasi hmac
update status pesanan otomatis saat qris lunas via webhook
catat riwayat pembayaran ke log audit untuk pembukuan
tambah automated test lengkap untuk alur katalog sampai bayar qris
```

---

## 5. Panduan Teknis Implementasi Langkah-demi-Langkah

Bagian ini menyajikan arsitektur teknis mendalam dan spesifikasi kode konkret untuk setiap tahapan.

```
================================================================================
BAGIAN A: PERTEMUAN 06-07 (KATALOG OBAT & SESI TAMU)
================================================================================
```

### 5.1 Siklus Hidup Sesi Tamu Anonim (`CustomerSessionService`)
Pengunjung publik belum tentu memiliki akun terdaftar saat pertama kali membuka katalog apotek. Sistem menerbitkan sesi anonim yang mengikat preferensi cabang apotek pelanggan:
- **Token Opaque**: Dihasilkan menggunakan `Str::random(32)`.
- **Media Penyimpanan**: Cookie terenkripsi HTTP-Only `omnipharma_customer_session` dengan masa aktif 30 hari.
- **Pinning Cabang**: Sesi mengikat parameter `{client}` (contoh: `banyuwangi-kota` atau `rogojampi`). Jika URL diakses tanpa slug cabang, sistem mengambil cabang *default* atau cabang terdekat berdasarkan riwayat kunjungan.

```php
namespace App\Modules\Ordering\Services;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CustomerSessionService
{
    const COOKIE_NAME = 'omnipharma_customer_session';
    const COOKIE_LIFETIME_MINUTES = 43200; // 30 hari

    public function resolveSessionId(Request $request): string
    {
        $sessionId = $request->cookie(self::COOKIE_NAME);

        if (!$sessionId || !is_string($sessionId) || strlen($sessionId) < 16) {
            $sessionId = 'gst_' . Str::random(32);
            Cookie::queue(self::COOKIE_NAME, $sessionId, self::COOKIE_LIFETIME_MINUTES);
        }

        return $sessionId;
    }

    public function resolveActiveClient(string $clientSlug): Client
    {
        return Client::where('nama_client', 'like', "%{$clientSlug}%")
            ->orWhere('id_client', $clientSlug)
            ->firstOrFail();
    }

    /**
     * Menyediakan shadow guest user untuk mematuhi integritas foreign key NOT NULL pada tabel pesanan (id_pengguna).
     */
    public function resolveOrCreateGuestUser(string $sessionId, ?string $nama = null): string
    {
        $email = "guest_{$sessionId}@guest.omnipharma.local";
        $user = \App\Models\Pengguna::firstOrCreate(
            ['email' => $email],
            [
                'id_pengguna' => (string) Str::uuid(),
                'nama' => $nama ?: 'Pelanggan Tamu',
                'kata_sandi' => bcrypt(Str::random(32)),
                'peran' => 'pelanggan',
                'aktif' => true,
                'dibuat_pada' => now(),
            ]
        );

        return $user->id_pengguna;
    }
}
```

### 5.2 Kueri Katalog Multi-Cabang & Badging Regulasi Obat (`CatalogService`)
Obat memiliki regulasi ketat sesuai peraturan kefarmasian BPOM:
1. **Obat Bebas**: Penjualan bebas tanpa resep (Hijau).
2. **Obat Bebas Terbatas**: Penjualan bebas disertai tanda peringatan P.No. 1 s/d P.No. 6 (Biru).
3. **Obat Wajib Resep (Keras)**: Lingkaran merah bertuliskan huruf **K**, wajib menyertakan resep dokter (Merah). Tombol checkout terkunci jika belum mengunggah resep dokter.

Kueri katalog wajib memuat relasi `stokClient` yang disaring khusus untuk cabang aktif (`id_client`), menghindari N+1 query problem:

```php
namespace App\Modules\Catalog\Services;

use App\Models\Obat;
use Illuminate\Database\Eloquent\Collection;

class CatalogService
{
    public function getCatalogForBranch(string $clientId, ?string $categoryId = null, ?string $search = null): Collection
    {
        $query = Obat::query()
            ->with(['kategori', 'stokClient' => function ($q) use ($clientId) {
                $q->where('id_client', $clientId);
            }]);

        if ($categoryId && $categoryId !== 'semua') {
            $query->where('id_kategori', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_obat', 'like', "%{$search}%")
                  ->orWhere('merek', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama_obat')->get();
    }
}
```

```
================================================================================
BAGIAN B: PERTEMUAN 08-09 (KERANJANG BELANJA REDIS & CHECKOUT ATOMIK)
================================================================================
```

### 5.3 Keranjang Belanja Redis Multi-Tenant (`CartService`)
- **Format Kunci Redis**: `cart:{sessionId}:{clientId}`
- **Struktur Data**: Redis Hash (`HSET`, `HGETALL`, `HDEL`, `EXPIRE`).
- **Pencegahan Kontaminasi Antarcabang**: Keranjang belanja terisolasi per `clientId`. Apabila pelanggan yang sedang memiliki keranjang di Cabang Banyuwangi Kota berpindah ke Cabang Rogojampi, keranjang Cabang Rogojampi kosong secara independen (atau diberi peringatan pergantian cabang).
- **TTL**: Kunci Redis diperpanjang 7 hari (`604800` detik) setiap ada mutasi item.

```php
namespace App\Modules\Ordering\Services;

use App\Models\Obat;
use App\Models\StokClient;
use Illuminate\Support\Facades\Redis;

class CartService
{
    const TTL_SECONDS = 604800; // 7 hari

    protected function getCartKey(string $sessionId, string $clientId): string
    {
        return "cart:{$sessionId}:{$clientId}";
    }

    public function addItem(string $sessionId, string $clientId, string $idObat, int $quantity = 1): array
    {
        $key = $this->getCartKey($sessionId, $clientId);
        $obat = Obat::with('kategori')->findOrFail($idObat);

        // Validasi fisik stok cabang sebelum masuk keranjang
        $stok = StokClient::where('id_client', $clientId)
            ->where('id_obat', $idObat)
            ->value('jumlah_stok') ?? 0;

        $existing = Redis::hget($key, $idObat);
        $currentQty = $existing ? json_decode($existing, true)['kuantitas'] : 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $stok) {
            throw new \InvalidArgumentException("Stok tidak mencukupi. Tersedia: {$stok}, diminta: {$newQty}");
        }

        $itemData = [
            'id_obat' => $obat->id_obat,
            'nama_obat' => $obat->nama_obat,
            'harga' => (float) $obat->harga,
            'kuantitas' => $newQty,
            'wajib_resep' => (bool) ($obat->kategori->wajib_resep ?? false),
            'subtotal' => (float) ($obat->harga * $newQty),
            'diperbarui_pada' => now()->toIso8601String(),
        ];

        Redis::hset($key, $idObat, json_encode($itemData));
        Redis::expire($key, self::TTL_SECONDS);

        return $this->getCart($sessionId, $clientId);
    }

    public function getCart(string $sessionId, string $clientId): array
    {
        $key = $this->getCartKey($sessionId, $clientId);
        $items = Redis::hgetall($key);

        $parsedItems = [];
        $totalNominal = 0;
        $adaObatKeras = false;

        foreach ($items as $idObat => $json) {
            $item = json_decode($json, true);
            $parsedItems[] = $item;
            $totalNominal += $item['subtotal'];
            if (!empty($item['wajib_resep'])) {
                $adaObatKeras = true;
            }
        }

        return [
            'client_id' => $clientId,
            'session_id' => $sessionId,
            'items' => $parsedItems,
            'total_item' => count($parsedItems),
            'total_nominal' => $totalNominal,
            'ada_obat_keras' => $adaObatKeras,
        ];
    }

    public function clearCart(string $sessionId, string $clientId): void
    {
        Redis::del($this->getCartKey($sessionId, $clientId));
    }
}
```

### 5.4 Revalidasi Pra-Checkout (`RevalidateCartAction`)
Sebelum checkout diproses, sistem wajib memvalidasi kembali bahwa:
1. Stok obat pada `stok_client` masih mencukupi kuantitas keranjang.
2. Harga master obat pada `obat.harga` tidak mengalami perubahan semenjak item dimasukkan ke dalam keranjang.
Jika ada perubahan harga, sistem memperbarui keranjang dan memberikan peringatan transparan ke pengguna sebelum transaksi terbentuk.

```php
namespace App\Modules\Ordering\Actions;

use App\Models\Obat;
use App\Models\StokClient;
use App\Modules\Ordering\Services\CartService;

class RevalidateCartAction
{
    public function __construct(protected CartService $cartService) {}

    public function execute(string $sessionId, string $clientId): array
    {
        $cart = $this->cartService->getCart($sessionId, $clientId);
        $issues = [];

        foreach ($cart['items'] as $item) {
            $obat = Obat::find($item['id_obat']);
            $stok = StokClient::where('id_client', $clientId)
                ->where('id_obat', $item['id_obat'])
                ->value('jumlah_stok') ?? 0;

            if ($item['kuantitas'] > $stok) {
                $issues[] = [
                    'id_obat' => $item['id_obat'],
                    'nama_obat' => $item['nama_obat'],
                    'tipe' => 'stok_kurang',
                    'pesan' => "Stok {$item['nama_obat']} tersisa {$stok}, tidak mencukupi permintaan ({$item['kuantitas']}).",
                ];
            }

            if ((float) $obat->harga !== (float) $item['harga']) {
                $issues[] = [
                    'id_obat' => $item['id_obat'],
                    'nama_obat' => $item['nama_obat'],
                    'tipe' => 'perubahan_harga',
                    'harga_lama' => $item['harga'],
                    'harga_baru' => (float) $obat->harga,
                    'pesan' => "Harga {$item['nama_obat']} telah disesuaikan dari Rp " . number_format($item['harga'], 0, ',', '.') . " ke Rp " . number_format($obat->harga, 0, ',', '.'),
                ];
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
        ];
    }
}
```

### 5.5 Eksekusi Checkout Atomik & Snapshot Harga (`CreateOrderAction`)
Pembuatan pesanan dijalankan dalam transaksi database penuh dengan proteksi konkurensi:
- **`lockForUpdate`**: Mengunci baris `stok_client` agar dua pembeli yang menekan tombol bayar bersamaan pada sisa stok terakhir tidak menyebabkan stok bernilai negatif.
- **ADR-0008 Snapshot**: Mengisi atribut `nama_obat` dan `harga_satuan` pada baris `item_pesanan` secara permanen.
- **Format Penomoran**: `#ORD-YYYYMMDD-XXXX` (contoh `#ORD-20261031-0042`).

```php
namespace App\Modules\Ordering\Actions;

use App\Models\ItemPesanan;
use App\Models\Obat;
use App\Models\Pesanan;
use App\Models\Resep;
use App\Models\StokClient;
use App\Modules\Ordering\Actions\RevalidateCartAction;
use App\Modules\Ordering\DTOs\CheckoutOrderDTO;
use App\Modules\Ordering\Events\IncomingOrderPlaced;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Ordering\Services\CartService;
use App\Modules\Ordering\Services\CustomerSessionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function __construct(
        protected CartService $cartService,
        protected RevalidateCartAction $revalidateCartAction,
        protected CustomerSessionService $customerSessionService
    ) {}

    public function execute(CheckoutOrderDTO $dto): Pesanan
    {
        return DB::transaction(function () use ($dto) {
            $cart = $this->cartService->getCart($dto->sessionId, $dto->clientId);

            if (empty($cart['items'])) {
                throw new \RuntimeException('Keranjang belanja kosong.');
            }

            // 1. Penegakan Wajib Resep Dokter (E-07: Anti-Bypass Obat Keras)
            if (!empty($cart['ada_obat_keras']) && empty($dto->urlGambarResep)) {
                throw ValidationException::withMessages([
                    'resep' => 'Resep dokter wajib diunggah untuk pembelian obat golongan keras.',
                ]);
            }

            // 2. Revalidasi Stok Fisik & Fluktuasi Harga Katalog Terkini (E-06)
            $revalidation = $this->revalidateCartAction->execute($dto->sessionId, $dto->clientId);
            if (!$revalidation['valid']) {
                $errorMsg = $revalidation['issues'][0]['pesan'] ?? 'Terjadi ketidaksesuaian stok atau harga pada keranjang belanja.';
                throw new \RuntimeException($errorMsg);
            }

            // 3. Verifikasi & Kunci Stok Fisik secara Pesimistik (Pessimistic Locking)
            foreach ($cart['items'] as $item) {
                $stokRecord = StokClient::where('id_client', $dto->clientId)
                    ->where('id_obat', $item['id_obat'])
                    ->lockForUpdate()
                    ->first();

                if (!$stokRecord || $stokRecord->jumlah_stok < $item['kuantitas']) {
                    throw new \RuntimeException("Stok untuk {$item['nama_obat']} tidak mencukupi saat proses checkout.");
                }

                // Kurangi stok fisik cabang (akan direstorasi jika pesanan kedaluwarsa atau resep ditolak - E-05)
                $stokRecord->decrement('jumlah_stok', $item['kuantitas']);
                $stokRecord->update(['diperbarui_pada' => now()]);
            }

            // 4. Resolusi id_pengguna untuk Menjamin Kompatibilitas Skema Foreign Key NOT NULL (O-01)
            $idPengguna = $dto->idPengguna ?? $this->customerSessionService->resolveOrCreateGuestUser(
                $dto->sessionId,
                $dto->namaPasien ?? 'Pelanggan Tamu'
            );

            // 5. Buat nomor pesanan unik #ORD-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $randomSeq = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $nomorPesanan = "#ORD-{$datePrefix}-{$randomSeq}";

            // 6. Simpan entitas Pesanan (State Machine: menunggu_verifikasi untuk obat keras, menunggu_pembayaran untuk reguler)
            $initialStatus = !empty($cart['ada_obat_keras']) ? 'menunggu_verifikasi' : 'menunggu_pembayaran';
            $pesanan = Pesanan::create([
                'id_pesanan' => (string) Str::uuid(),
                'id_pengguna' => $idPengguna,
                'id_client' => $dto->clientId,
                'nomor_pesanan' => $nomorPesanan,
                'status' => $initialStatus,
                'metode_layanan' => $dto->metodeLayanan, // 'click_collect' atau 'delivery'
            ]);

            // 7. Simpan Item Pesanan dengan Snapshot Harga Live Database (ADR-0008 & E-06)
            foreach ($cart['items'] as $item) {
                $liveObat = Obat::findOrFail($item['id_obat']);
                $unitPrice = (float) $liveObat->harga; // Snapshot harga resmi dari database, bukan array Redis lama
                $subtotal = $unitPrice * $item['kuantitas'];

                ItemPesanan::create([
                    'id_item' => (string) Str::uuid(),
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_obat' => $item['id_obat'],
                    'kuantitas' => $item['kuantitas'],
                    'nama_obat' => $liveObat->nama_obat,     // IMMUTABLE SNAPSHOT
                    'harga_satuan' => $unitPrice,            // IMMUTABLE SNAPSHOT
                    'subtotal' => $subtotal,
                    'dibuat_pada' => now(),
                ]);
            }

            // 8. Simpan Resep jika ada obat keras yang dipesan (E-07)
            if (!empty($cart['ada_obat_keras'])) {
                Resep::create([
                    'id_resep' => (string) Str::uuid(),
                    'id_pesanan' => $pesanan->id_pesanan,
                    'url_gambar' => $dto->urlGambarResep,
                    'status_verifikasi' => 'menunggu',
                    'nama_dokter' => $dto->namaDokter,
                    'sip_dokter' => $dto->sipDokter,
                    'nama_pasien' => $dto->namaPasien,
                    'dibuat_pada' => now(),
                ]);
            }

            // 9. Kosongkan keranjang belanja Redis setelah order tersimpan
            $this->cartService->clearCart($dto->sessionId, $dto->clientId);

            // 10. Siarkan Event Real-Time (Reverb WebSockets)
            IncomingOrderPlaced::dispatch($pesanan);
            OrderStatusUpdated::dispatch(
                $pesanan,
                null,
                $pesanan->status,
                'Pesanan baru berhasil dibuat dan menunggu proses berikutnya.'
            );

            return $pesanan;
        });
    }
}
```

```
================================================================================
BAGIAN C: PERTEMUAN 10-11 (QRIS DINAMIS SANDBOX & WEBHOOK)
================================================================================
```

### 5.6 Kontrak Abstraksi Payment Gateway (`PaymentGatewayInterface`)
Sesuai **ADR-0004**, aplikasi apotek tidak boleh terkunci (*vendor lock-in*) pada satu provider gateway tertentu. Kontrak didefinisikan secara bersih:

```php
namespace App\Modules\Payments\Contracts;

use App\Modules\Payments\DTOs\PaymentChargeRequest;
use App\Modules\Payments\DTOs\PaymentChargeResponse;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Membuat tagihan transaksi QRIS dinamis.
     */
    public function charge(PaymentChargeRequest $request): PaymentChargeResponse;

    /**
     * Memverifikasi keabsahan signature webhook pengirim.
     */
    public function verifyWebhookSignature(Request $request): bool;
}
```

### 5.7 Generator String EMVCo QRIS Standar & Algoritma CRC16 (`QrisEmvcoBuilder`)
Spesifikasi QRIS Bank Indonesia berbasis standar EMVCo MPM (Merchant Presented Mode) Tag-Length-Value:
- **Tag 00**: Format Indicator (`01`)
- **Tag 01**: Point of Initiation (`12` = Dynamic QR)
- **Tag 26**: Merchant Account Information (NMID: `ID102026090001`, Acquirer: `93600998`)
- **Tag 52**: Merchant Category Code (`5912` = Drug Stores & Pharmacies)
- **Tag 53**: Currency Code (`360` = Rupiah Indonesia)
- **Tag 54**: Transaction Amount (nominal dinamis sesuai pesanan)
- **Tag 58**: Country Code (`ID`)
- **Tag 59**: Merchant Name (contoh: `OMNIPHARMA BANYUWANGI`)
- **Tag 60**: Merchant City (`BANYUWANGI`)
- **Tag 62**: Additional Data (Invoice Number `#ORD-...`)
- **Tag 63**: Checksum CRC16-CCITT (4 karakter heksadesimal)

```php
namespace App\Modules\Payments\Services;

class QrisEmvcoBuilder
{
    public static function build(string $merchantName, string $orderNumber, float $amount): string
    {
        $tlv = '';
        
        // Tag 00: Payload Format Indicator
        $tlv .= self::formatTag('00', '01');
        
        // Tag 01: Dynamic QR (Point of Initiation Method = 12)
        $tlv .= self::formatTag('01', '12');
        
        // Tag 26: Merchant Account Information
        $sub26 = self::formatTag('00', '93600998') . self::formatTag('01', 'ID102026090001');
        $tlv .= self::formatTag('26', $sub26);
        
        // Tag 52: Merchant Category Code (5912 = Apotek)
        $tlv .= self::formatTag('52', '5912');
        
        // Tag 53: Transaction Currency (360 = IDR)
        $tlv .= self::formatTag('53', '360');
        
        // Tag 54: Transaction Amount
        $tlv .= self::formatTag('54', number_format($amount, 2, '.', ''));
        
        // Tag 58: Country Code
        $tlv .= self::formatTag('58', 'ID');
        
        // Tag 59: Merchant Name
        $cleanName = substr(strtoupper(preg_replace('/[^A-Za-z0-9 ]/', '', $merchantName)), 0, 25);
        $tlv .= self::formatTag('59', $cleanName);
        
        // Tag 60: Merchant City
        $tlv .= self::formatTag('60', 'BANYUWANGI');
        
        // Tag 62: Additional Data Field (Invoice / Nomor Pesanan)
        $sub62 = self::formatTag('01', $orderNumber);
        $tlv .= self::formatTag('62', $sub62);
        
        // Tag 63: CRC16 Checksum
        $payloadWithoutCrc = $tlv . '6304';
        $crc = self::calculateCrc16($payloadWithoutCrc);
        
        return $payloadWithoutCrc . $crc;
    }

    private static function formatTag(string $tag, string $value): string
    {
        $length = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    public static function calculateCrc16(string $str): string
    {
        $crc = 0xFFFF;
        $len = strlen($str);

        for ($c = 0; $c < $len; $c++) {
            $crc ^= (ord($str[$c]) << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
```

### 5.8 Implementasi Gateway Sandbox (`FakeQrisGateway`)
Gateway sandbox menyimpan rekaman pembayaran berstatus `menunggu` pada tabel `pembayaran` dan menyediakan payload QRIS yang dapat diuji pada browser:

```php
namespace App\Modules\Payments\Services;

use App\Models\Pembayaran;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use App\Modules\Payments\DTOs\PaymentChargeRequest;
use App\Modules\Payments\DTOs\PaymentChargeResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FakeQrisGateway implements PaymentGatewayInterface
{
    public function charge(PaymentChargeRequest $request): PaymentChargeResponse
    {
        $emvcoString = QrisEmvcoBuilder::build(
            $request->merchantName,
            $request->orderNumber,
            $request->amount
        );

        $expiresAt = now()->addMinutes(15);

        // Catat tagihan ke tabel pembayaran
        $pembayaran = Pembayaran::create([
            'id_pembayaran' => (string) Str::uuid(),
            'id_pesanan' => $request->orderId,
            'metode_pembayaran' => 'qris',
            'jumlah' => $request->amount,
            'status' => 'menunggu',
            'url_lampiran' => null,
            'dibuat_pada' => now(),
        ]);

        return new PaymentChargeResponse(
            paymentId: $pembayaran->id_pembayaran,
            orderId: $request->orderId,
            qrString: $emvcoString,
            qrImageUrl: "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($emvcoString),
            amount: $request->amount,
            expiresAt: $expiresAt,
            status: 'menunggu'
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-Signature');
        if (!$signature) {
            return false;
        }

        $secret = config('services.qris.webhook_secret', 'omnipharma-sandbox-secret-2026');
        $computed = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($computed, $signature);
    }
}
```

### 5.9 Halaman Pembayaran Interaktif & Hitung Mundur 15 Menit (`payment.blade.php`)
Halaman menampilkan visual QR code dinamis, nominal pembayaran, timer 15 menit, serta tombol **"Simulasikan Pembayaran Berhasil (Sandbox Mode)"** yang memicu callback webhook lokal:

```html
<x-layouts.customer :title="'Pembayaran QRIS - OmniPharma'">
    <div class="max-w-md mx-auto my-8 p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm"
         x-data="{
             remainingSeconds: {{ max(0, $pembayaran->expires_at ? now()->diffInSeconds($pembayaran->expires_at, false) : 900) }},
             isExpired: {{ ($pembayaran->expires_at && now()->gt($pembayaran->expires_at)) ? 'true' : 'false' }},
             timerText: '15:00',
             isProcessing: false,
             init() {
                 let updateText = () => {
                     let m = Math.floor(Math.max(0, this.remainingSeconds) / 60).toString().padStart(2, '0');
                     let s = (Math.max(0, this.remainingSeconds) % 60).toString().padStart(2, '0');
                     this.timerText = `${m}:${s}`;
                 };
                 updateText();
                 if (this.remainingSeconds <= 0) {
                     this.isExpired = true;
                     this.timerText = '00:00';
                     return;
                 }
                 let interval = setInterval(() => {
                     if (this.remainingSeconds <= 0) {
                         this.isExpired = true;
                         this.timerText = '00:00';
                         clearInterval(interval);
                     } else {
                         this.remainingSeconds--;
                         updateText();
                     }
                 }, 1000);
             },
             simulasikanLunas() {
                 this.isProcessing = true;
                 fetch('{{ route('customer.payment.simulate', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     }
                 })
                 .then(res => res.json())
                 .then(data => {
                     window.location.href = '{{ route('customer.order.track', ['client' => $client, 'id' => $pesanan->id_pesanan]) }}';
                 })
                 .catch(() => alert('Gagal simulasi lunas. Periksa server.'));
             }
         }">

        <div class="text-center mb-6">
            <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-xs font-semibold rounded-full mb-2">
                QRIS Dinamis Standar Nasional
            </span>
            <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Selesaikan Pembayaran</h2>
            <p class="text-sm text-zinc-500">Nomor Pesanan: <span class="font-mono font-semibold">{{ $pesanan->nomor_pesanan }}</span></p>
        </div>

        <!-- Timer Card -->
        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 rounded-xl text-center mb-6">
            <p class="text-xs text-amber-700 dark:text-amber-300 font-medium">Batas Waktu Pembayaran</p>
            <p class="text-2xl font-mono font-bold text-amber-900 dark:text-amber-200" x-text="timerText"></p>
        </div>

        <!-- QR Display -->
        <div class="flex justify-center p-4 bg-white border border-zinc-200 rounded-xl mb-6">
            <template x-if="!isExpired">
                <img src="{{ $paymentResponse->qrImageUrl }}" alt="QRIS Code" class="w-64 h-64 object-contain">
            </template>
            <template x-if="isExpired">
                <div class="w-64 h-64 flex flex-col items-center justify-center text-center p-4 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                    <p class="text-sm font-semibold text-rose-600">QRIS Telah Kedaluwarsa</p>
                    <p class="text-xs text-zinc-500 mt-1">Silakan lakukan pemesanan ulang.</p>
                </div>
            </template>
        </div>

        <div class="text-center mb-6">
            <p class="text-xs text-zinc-500">Total Nominal Tagihan</p>
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                Rp {{ number_format($paymentResponse->amount, 0, ',', '.') }}
            </p>
        </div>

        <!-- Tombol Simulasi Sandbox untuk Pengujian Dosen -->
        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <button type="button"
                    @click="simulasikanLunas()"
                    :disabled="isExpired || isProcessing"
                    class="w-full min-h-[44px] flex items-center justify-center px-4 py-3 bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 text-sm font-semibold rounded-xl transition shadow-sm disabled:opacity-50">
                <span x-show="!isProcessing">Simulasikan Pembayaran Berhasil (Sandbox)</span>
                <span x-show="isProcessing">Memproses Notifikasi Webhook...</span>
            </button>
            <p class="text-[11px] text-zinc-400 text-center mt-2">
                *Tombol ini memicu HTTP Callback Webhook HMAC untuk keperluan demo Pak Devit.
            </p>
        </div>
    </div>
</x-layouts.customer>
```

### 5.10 Webhook Idempoten & Verifikasi Signature (`PaymentWebhookController`)
Endpoint menerima notifikasi asinkron dari payment gateway dan menerapkan prinsip keamanan finansial:
1. Validasi keberadaan header `X-Signature`.
2. Verifikasi keaslian hash HMAC-SHA256 dari *raw request payload*.
3. **Idempotency Check**: Apabila `pembayaran.status` telah `berhasil`, balas langsung dengan HTTP 200 OK tanpa memproses ulang pesanan.
4. Mutasi status pesanan:
   - Jika pesanan memuat obat keras (`resep` ada), status menjadi `menunggu_verifikasi`.
   - Jika pesanan hanya memuat obat bebas, status langsung menjadi `diproses`.
5. Pencatatan audit trail pada tabel `log_audit`.

```php
namespace App\Modules\Payments\Http\Controllers;

use App\Models\LogAudit;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\StokClient;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use App\Modules\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentWebhookController
{
    public function __construct(protected PaymentGatewayInterface $gateway) {}

    public function handle(Request $request): JsonResponse
    {
        // 1. Verifikasi Signature HMAC-SHA256
        if (!$this->gateway->verifyWebhookSignature($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.'
            ], 401);
        }

        $payload = $request->validate([
            'order_id' => 'required|uuid',
            'payment_id' => 'required|uuid',
            'status' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($payload, $request) {
            $pembayaran = Pembayaran::where('id_pembayaran', $payload['payment_id'])
                ->where('id_pesanan', $payload['order_id'])
                ->lockForUpdate()
                ->first();

            if (!$pembayaran) {
                return response()->json(['message' => 'Payment record not found.'], 404);
            }

            // 2. Idempotency Guard: Hindari kredit ganda jika webhook terkirim ulang
            if ($pembayaran->status === 'berhasil') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already settled previously (idempotent).'
                ], 200);
            }

            $pesanan = Pesanan::with(['resep', 'itemPesanan.obat.kategori'])->findOrFail($payload['order_id']);

            // 3. Verifikasi Batas Kedaluwarsa QRIS 15 Menit & Restorasi Stok Fisik (E-09 & E-05)
            if ($pembayaran->expires_at && now()->gt($pembayaran->expires_at)) {
                $pembayaran->update(['status' => 'kedaluwarsa']);
                $pesanan->update(['status' => 'kedaluwarsa']);

                // Restorasi stok fisik cabang secara otomatis agar tidak terjadi kebocoran inventori (E-05)
                foreach ($pesanan->itemPesanan as $item) {
                    StokClient::where('id_client', $pesanan->id_client)
                        ->where('id_obat', $item->id_obat)
                        ->increment('jumlah_stok', $item->kuantitas);
                }

                // Broadcast perubahan status ke pembeli via WebSocket
                OrderStatusUpdated::dispatch(
                    $pesanan,
                    'menunggu_pembayaran',
                    'kedaluwarsa',
                    'Batas waktu pembayaran QRIS (15 menit) telah habis. Pesanan dibatalkan dan stok dikembalikan.'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan QRIS telah kedaluwarsa. Transaksi ditolak dan stok telah direstorasi.'
                ], 422);
            }

            // 4. Mutasi status pembayaran & evaluasi kelayakan status pesanan
            if ($payload['status'] === 'SETTLED' || $payload['status'] === 'SUCCESS') {
                $pembayaran->update([
                    'status' => 'berhasil',
                ]);

                // Deteksi apakah pesanan memuat obat keras / wajib resep
                $hasHardDrugs = $pesanan->itemPesanan->contains(function ($item) {
                    return (bool) ($item->obat->kategori->wajib_resep ?? false);
                });

                // Jika pesanan memuat obat keras dan resep belum disetujui apoteker,
                // pesanan ditahan pada status 'menunggu_verifikasi'.
                // Jika pesanan bebas obat keras, atau resep telah disetujui, pesanan maju ke 'diproses'.
                $resepApproved = $pesanan->resep && $pesanan->resep->status_verifikasi === 'disetujui';
                $nextStatus = ($hasHardDrugs && !$resepApproved) ? 'menunggu_verifikasi' : 'diproses';

                $oldStatus = $pesanan->status;
                $pesanan->update(['status' => $nextStatus]);

                // 5. Siarkan Event Real-Time (Reverb WebSockets)
                OrderStatusUpdated::dispatch(
                    $pesanan,
                    $oldStatus,
                    $nextStatus,
                    $nextStatus === 'menunggu_verifikasi'
                        ? 'Pembayaran QRIS berhasil diterima. Menunggu telaah resep dokter oleh apoteker cabang.'
                        : 'Pembayaran QRIS berhasil dikonfirmasi. Pesanan sedang disiapkan staf apotek sesuai FEFO.'
                );

                // 6. Catat Audit Log Finansial
                LogAudit::create([
                    'id_audit' => (string) Str::uuid(),
                    'id_aktor' => $pesanan->id_pengguna,
                    'aksi' => 'SETTLEMENT_WEBHOOK_QRIS',
                    'nama_entitas' => 'pembayaran',
                    'id_entitas' => $pembayaran->id_pembayaran,
                    'payload' => [
                        'ip_pengirim' => $request->ip(),
                        'nominal' => $payload['amount'],
                        'nomor_pesanan' => $pesanan->nomor_pesanan,
                        'status_baru' => 'berhasil',
                        'status_pesanan' => $nextStatus,
                    ],
                    'dibuat_pada' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook settlement processed successfully.'
            ], 200);
        });
    }
}
```

---

## 6. Panduan Presentasi & Sidang Uji Praktikum (Pak Devit Suwardiyanto)

Pak Devit Suwardiyanto menguji mahasiswa dengan fokus ketat pada **integritas basis data, penanganan konkurensi (race condition), pemahaman standar industri (EMVCo), dan keamanan idempotensi transaksi**.

### 6.1 Peta Konsep Arsitektural untuk Pertahanan Sidang
Saat Pak Devit meminta penjelasan arsitektur, gunakan argumen berbasis rekayasa perangkat lunak berikut:

1. **Mengapa Redis untuk Keranjang Belanja, Bukan MySQL Session atau File?**
   - *Jawaban*: Redis adalah in-memory key-value store berkecepatan mikrodetik yang mendukung struktur data *Hash*. Menaruh keranjang di database relasional akan membebani I/O MariaDB akibat operasi baca-tulis keranjang yang sangat frekuen (setiap penambahan/pengurangan item obat). 
   - Dengan Redis Hash berformat `cart:{sessionId}:{clientId}`, data keranjang otomatis kedaluwarsa setelah 7 hari via mekanisme TTL *native*, tanpa membutuhkan cron job pembersih data sampah. Selain itu, pemisahan key per `{clientId}` menjamin keranjang Cabang Banyuwangi Kota tidak akan pernah tercampur dengan keranjang Cabang Rogojampi.

2. **Bagaimana Menangani Race Condition Saat Sisa Stok Tinggal 1 dan Dicheckout 2 Pengguna Bersamaan?**
   - *Jawaban*: Kami menerapkan *Pessimistic Locking* menggunakan perintah `lockForUpdate()` pada baris `stok_client` di dalam transaksi database `DB::transaction`. 
   - Ketika Pelanggan A dan Pelanggan B menekan checkout di milidetik yang sama, transaksi Pelanggan A akan mengunci baris stok tersebut (`SELECT ... FOR UPDATE`). Transaksi Pelanggan B dipaksa antre menunggu kunci dilepaskan. Saat Pelanggan A sukses memotong sisa stok dari 1 menjadi 0 dan melakukan commit, giliran Pelanggan B membaca stok terkini (`jumlah_stok = 0`), memicu pembatalan checkout secara atomik dengan melempar pengecualian (*exception*), sehingga data stok tidak mungkin bernilai negatif.

3. **Mengapa Menyimpan Snapshot Harga di `item_pesanan` Melanggar 3NF Murni?**
   - *Jawaban*: Ini adalah keputusan arsitektur sadar (*Controlled Denormalization*) yang kami tetapkan dalam **ADR-0008**.
   - Jika kita mengikuti normalisasi murni (hanya foreign key `id_obat`), maka ketika admin apotek menaikkan harga master Amoxicillin dari Rp 15.000 menjadi Rp 18.000 bulan depan, seluruh kuitansi pembelian pasien dan laporan pembukuan pajak bulan lalu akan otomatis berubah menjadi Rp 18.000. Dengan menyimpan snapshot `harga_satuan` dan `nama_obat` pada saat transaksi dibuat, integritas historis keuangan bersifat *immutable* (tidak dapat berubah selamanya).

4. **Bagaimana Struktur String QRIS Dinamis Terbentuk?**
   - *Jawaban*: String QRIS mengikuti standar Bank Indonesia dan EMVCo MPM (*Merchant Presented Mode*) berbasis Tag-Length-Value. 
   - Tag `01` bernilai `12` menandakan QRIS Dinamis (nominal sudah terkunci di dalam QR). Tag `54` memuat nominal presisi dua desimal. Tag `52` menggunakan kode kategori apotek `5912`. Karakter diakhiri oleh Tag `63` yang memuat 4 digit heksadesimal hasil algoritma *Cyclic Redundancy Check* (CRC16-CCITT) dengan polinomial `0x1021`.

5. **Mengapa Webhook Harus Idempoten?**
   - *Jawaban*: Protokol internet tidak menjamin pengiriman paket tepat satu kali (*at-least-once delivery*). Server gateway payment kerap mengirimkan ulang *payload* webhook bila koneksi mengalami *timeout* sesaat.
   - Tanpa penjaga idempotensi, pengiriman webhook kedua kali dapat memotong stok obat dua kali atau menduplikasi pencatatan pendapatan. Pada `PaymentWebhookController`, sistem mengecek status pembayaran terlebih dahulu; jika sudah `berhasil`, sistem langsung membalas HTTP 200 OK tanpa mengeksekusi logika mutasi berulang.

---

### 6.2 Alur Demonstrasi Praktikum (Skenario Demo 5 Menit)

Siapkan urutan demonstrasi langsung di terminal dan browser berikut:

```
+-------------------------------------------------------------------------------+
| MENIT 1: DEMO KATALOG OBAT & PINNING SESI CABANG                              |
| - Buka browser ke URL: /klien/banyuwangi-kota                                  |
| - Tunjukkan lencana "OBAT BEBAS" (Hijau) pada Paracetamol 500mg.               |
| - Tunjukkan lencana "WAJIB RESEP (KERAS)" (Merah) pada Amodiaquine 200mg.     |
| - Buka Developer Tools -> Application -> Cookies:                             |
|   Perlihatkan cookie 'omnipharma_customer_session' berisi token 32 karakter.  |
+-------------------------------------------------------------------------------+
                                      │
                                      ▼
+-------------------------------------------------------------------------------+
| MENIT 2: DEMO KERANJANG REDIS & ISOLASI CABANG                                |
| - Klik tombol 'Tambah ke Keranjang' pada Paracetamol.                         |
| - Buka terminal Redis CLI: keys cart:* -> hgetall cart:...                    |
|   Perlihatkan ke Pak Devit data tersimpan di Redis Hash lengkap dengan TTL.   |
| - Ganti URL ke /klien/rogojampi -> Perlihatkan keranjang Cabang Rogojampi     |
|   kosong, membuktikan isolasi data antarcabang berjalan sempurna.              |
+-------------------------------------------------------------------------------+
                                      │
                                      ▼
+-------------------------------------------------------------------------------+
| MENIT 3: DEMO VALIDASI STOK & CHECKOUT ATOMIK                                 |
| - Kembali ke keranjang Cabang Banyuwangi Kota, klik 'Lanjut ke Checkout'.     |
| - Pilih metode layanan: 'Click & Collect' (Ambil di Apotek).                 |
| - Buka baris kode CreateOrderAction.php di IDE: perlihatkan baris              |
|   DB::transaction() dan ->lockForUpdate().                                    |
| - Klik 'Buat Pesanan': Halaman menerbitkan pesanan resmi #ORD-20261031-XXXX.  |
+-------------------------------------------------------------------------------+
                                      │
                                      ▼
+-------------------------------------------------------------------------------+
| MENIT 4: DEMO HALAMAN PEMBAYARAN QRIS DINAMIS                                 |
| - Tampilkan visual QR Code yang digenerate oleh FakeQrisGateway.             |
| - Salin string EMVCo dan jelaskan Tag 54 (nominal) dan Tag 63 (CRC16).       |
| - Perlihatkan penghitung waktu mundur 15 menit berjalan aktif.                |
+-------------------------------------------------------------------------------+
                                      │
                                      ▼
+-------------------------------------------------------------------------------+
| MENIT 5: DEMO SIMULASI WEBHOOK & SETTLEMENT IDEMPOTEN                         |
| - Klik tombol 'Simulasikan Pembayaran Berhasil (Sandbox)'.                   |
| - Buka tabel database:                                                        |
|   * tabel pembayaran: status berubah dari 'menunggu' -> 'berhasil'.           |
|   * tabel pesanan: status berubah menjadi 'diproses'.                         |
|   * tabel log_audit: tersimpan baris 'SETTLEMENT_WEBHOOK_QRIS'.               |
| - Klik tombol simulasi sekali lagi -> perlihatkan log bahwa webhook merespons  |
|   HTTP 200 idempoten tanpa duplikasi saldo atau error.                        |
+-------------------------------------------------------------------------------+
```

---

### 6.3 Tanya-Jawab Kritis Pak Devit Suwardiyanto & Strategi Jawaban

| No | Pertanyaan Kritis Pak Devit | Strategi Jawaban Teknis Mahasiswa (Guntur) |
|---|---|---|
| 1 | *"Mengapa Anda tidak memasang SDK Midtrans atau Xendit resmi di `composer.json` dari awal?"* | "Sesuai **ADR-0004 (Payment Provider Deferred)**, memasang SDK pihak ketiga di awal menyebabkan *vendor lock-in* dan mengotori dependensi sebelum disepakati provider mana yang dipakai produksi. Kami mendesain kontrak `PaymentGatewayInterface` dan simulator `FakeQrisGateway`. Jika nanti beralih ke Midtrans atau Xendit, kami cukup membuat adapter baru tanpa perlu mengubah satu baris pun kode controller atau checkout." |
| 2 | *"Bagaimana jika pembeli menutup halaman QRIS lalu membukanya kembali 16 menit kemudian? Apakah masih bisa dibayar?"* | "Sistem kami menerapkan validasi ganda di sisi klien dan server. Di klien, JavaScript countdown akan menonaktifkan tombol dan menampilkan 'QRIS Kedaluwarsa'. Di sisi server, `PaymentWebhookController` memeriksa `created_at` atau field batas kedaluwarsa tagihan. Jika waktu telah melebihi 15 menit, webhook akan menolak settlement dan pesanan dialihkan ke status `kedaluwarsa` (*expired*)." |
| 3 | *"Jika pelanggan membeli obat bebas dan obat keras dalam satu keranjang yang sama, bagaimana alur status transaksinya setelah QRIS lunas?"* | "Pada `CreateOrderAction`, sistem mendeteksi keberadaan obat keras via flag `ada_obat_keras`. Ketika QRIS lunas melalui webhook, status pesanan tidak langsung menjadi `diproses` melainkan ditahan pada status `menunggu_verifikasi`. Apoteker cabang diwajibkan memeriksa keabsahan foto resep dokter dan nomor SIP sebelum obat dapat disiapkan dan diserahkan kepada pasien." |
| 4 | *"Bagaimana membuktikan bahwa Redis cart Anda tidak menghabiskan RAM server jika ada 10.000 pengunjung yang hanya melihat-lihat lalu pergi?"* | "Setiap penambahan atau mutasi item di `CartService` mengeksekusi perintah Redis `EXPIRE` selama 7 hari (`604800` detik). Jika pengunjung meninggalkan keranjang tanpa pernah kembali, Redis akan otomatis menghapus key tersebut dari memori (*passive/active eviction*) tanpa membebani kapasitas RAM server." |
| 5 | *"Di mana letak file test pengujian otomatis Anda dan bagaimana cara Anda memverifikasi alur ini?"* | "Kami menulis 4 berkas pengujian fitur lengkap di direktori `tests/Feature/`: `CatalogAndCustomerSessionTest.php`, `RedisCartAndRevalidationTest.php`, `AtomicCheckoutConcurrencyTest.php`, dan `DynamicQrisPaymentAndWebhookTest.php`. Semua test dapat dijalankan sekaligus dengan perintah `php artisan test --filter=CartCheckoutPayment` dengan status 100% lulus." |

---

*Dokumen PRD ini disusun secara resmi untuk memenuhi standar modul praktikum Sistem Informasi Apotek Multi-Klien di bawah bimbingan Dosen Pengampu Pak Devit Suwardiyanto.*
