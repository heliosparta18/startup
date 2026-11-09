# Requirements Traceability Matrix (RTM) — OmniPharma Network
### Sistem Informasi Penjualan Obat Online Apotek Multi-Klien
> **Dokumen Sumber Requirement:** `references/SRS Apotek.docx` / `references/srs_apotek_extracted.txt` (Draf Fase 0 – Revisi 2)  
> **Basis Arsitektur:** Modular Monolith (6 Bounded Contexts), MariaDB 11.4, Redis 7.2, Laravel Reverb (WebSockets)  
> **Tingkat Normalisasi:** 3NF s/d 5NF (PDM Revisi `references/PDM revisi.pdf`)

---

## 🎯 Matriks Ketertelusuran Kebutuhan Fungsional (FR) & Use Case (UC)

### 1. Peran Pelanggan (Customer)

| Kode Req | Ringkasan Kebutuhan SRS | Use Case | Bounded Context | File Implementasi | File Automated Test | Status |
|---|---|---|---|---|---|:---:|
| **FR-PEL-01** | Katalog & Pencarian Obat per Cabang | UC-01 | `Catalog` | `CatalogService.php`, `customer/home.blade.php`, `customer/obat-detail.blade.php` | `GuestSessionAndCatalogTest.php` | **LULUS** |
| **FR-PEL-02** | Unggah Resep Dokter & Checkout Atomik | UC-02 | `Prescription`, `Ordering` | `UnggahResepRequest.php`, `CreateOrderAction.php`, `customer/checkout.blade.php` | `CartCheckoutPaymentTest.php`, `EndToEndOrderFlowTest.php` | **LULUS** |
| **FR-PEL-03** | Riwayat Pesanan Pribadi Pelanggan | UC-03 | `Ordering` | `routes/customer.php`, `customer/order-track.blade.php` | `EndToEndOrderFlowTest.php` | **SEBAGIAN** (Track by ID) |
| **FR-PEL-04** | Lacak Status Pesanan Real-Time (WebSockets) | UC-04 | `Ordering`, `Prescription` | `OrderTrackingController.php`, `customer/order-track.blade.php`, `OrderStatusUpdated.php` | `ReverbOrderStatusBroadcastTest.php` | **LULUS** |
| **FR-PEL-05** | Cek Status Resep (Badge Bebas/Wajib Resep) & Stok | UC-10 | `Catalog` | `customer/home.blade.php`, `customer/obat-detail.blade.php`, `KategoriObat.php` | `GuestSessionAndCatalogTest.php` | **LULUS** |
| **FR-PEL-06** | Reservasi & Ambil Tanpa Antre (*Click & Collect*) | UC-11 | `Ordering` | `checkout.blade.php`, `CreateOrderAction.php` (`metode_layanan: click_collect`) | `CartCheckoutPaymentTest.php` | **LULUS** |

---

### 2. Peran Apoteker & Staf Cabang (Tenant)

| Kode Req | Ringkasan Kebutuhan SRS | Use Case | Bounded Context | File Implementasi | File Automated Test | Status |
|---|---|---|---|---|---|:---:|
| **FR-APT-01** | Papan Pesanan Masuk & Antrean Pemrosesan | UC-05 | `Ordering`, `Prescription` | `tenant/dashboard.blade.php`, `routes/tenant.php` | `EndToEndOrderFlowTest.php` | **LULUS** |
| **FR-APT-02** | Verifikasi Resep Dokter & Keabsahan SIP | UC-02 | `Prescription` | `VerifyPrescriptionAction.php`, `VerifikasiResepRequest.php`, `tenant/verifikasi-resep.blade.php` | `EndToEndOrderFlowTest.php` | **LULUS** |
| **FR-APT-03** | Kelola Stok & Rotasi FEFO (*First Expired First Out*) | UC-06 | `Catalog` | `StockFefoService.php`, `tenant/stok-fefo.blade.php`, `tenant/dashboard.blade.php` | `FefoStockRotationTest.php` | **LULUS** |
| **FR-APT-04** | Peringatan Stok Kritis & Kedaluwarsa Dini (H-30/H-7) | UC-12 | `Catalog` | `StockFefoService.php`, `tenant/stok-fefo.blade.php` | `FefoStockRotationTest.php` | **LULUS** |

---

### 3. Peran Super Admin Eksekutif (Admin)

| Kode Req | Ringkasan Kebutuhan SRS | Use Case | Bounded Context | File Implementasi | File Automated Test | Status |
|---|---|---|---|---|---|:---:|
| **FR-ADM-01** | Manajemen Cabang & Penugasan Staf APJ (5NF) | UC-07 | `Admin` | `StaffManagementService.php`, `admin/pengaturan-cabang.blade.php`, `admin/staff-management.blade.php` | `StaffHierarchy5NFTest.php` | **LULUS** |
| **FR-ADM-02** | Rekap Penjualan Multi-Klien & Snapshot Periode | UC-08 | `Reporting` | `SalesReportService.php`, `CloseSalesPeriodAction.php`, `admin/kasir-ledger.blade.php` | `SalesReportAndPeriodLockTest.php` | **LULUS** |
| **FR-ADM-03** | Ekspor Laporan Finansial (CSV/PDF) Aman Formula Injection | UC-09 | `Reporting` | `SalesReportExportService.php`, `admin/dashboard.blade.php`, `laporan-penjualan-template.blade.php` | `SalesReportAndPeriodLockTest.php` | **LULUS** |
| **FR-ADM-04** | Pantauan Stok Kritis & Kedaluwarsa Lintas Cabang | UC-13 | `Reporting`, `Catalog` | `admin/dashboard.blade.php`, `routes/admin.php` | `ApotekFoundationTest.php` | **LULUS** |

---

## 🔒 Ketertelusuran Non-Fungsional (NFR) & Arsitektur
- **NFR-SEC-01 (Isolasi Multi-Tenant Context & Scope):** `TenantContext.php`, `TenantScope.php`, `ResolveTenantContext.php` $\rightarrow$ Diuji di `TenantContextIsolationTest.php`.
- **NFR-SEC-02 (Anti-Stored XSS & File Upload Magic Bytes):** `UnggahResepRequest.php`, `VerifikasiResepRequest.php`, `SecurityHeadersMiddleware.php` $\rightarrow$ Diuji di `SecurityHardeningTest.php`.
- **NFR-PAY-01 (Dynamic QRIS Sandbox & Webhook Idempotency):** `FakeQrisGateway.php`, `routes/api.php` (`POST /api/webhooks/qris`) $\rightarrow$ Diuji di `CartCheckoutPaymentTest.php`.
