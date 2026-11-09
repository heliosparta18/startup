<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Apotek OmniPharma Network - Sistem Farmasi Terpadu</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    
    <!-- Alpine.js (Local bundle offline-ready & CSP compliant) -->
    <script defer src="/vendor/alpine.min.js"></script>

    <style>
        /* Hanya sembunyikan elemen modal dan notifikasi sebelum Alpine aktif, jangan sembunyikan main content */
        [x-cloak].modal-dialog,
        [x-cloak].toast-notif { 
            display: none !important; 
        }
    </style>
</head>
@php
    $photoUrl = session('firebase_photo') ?? null;
    $provider = session('firebase_provider', 'password');
    $user = auth()->user();
    $userName = $user->name ?? ($user->nama_lengkap ?? 'Apoteker OmniPharma');
    $userEmail = $user->email ?? ($user->telepon_email ?? 'staff@omnipharma.test');
    $rawRole = $user->role ?? ($user->peran ?? 'pelanggan');

    // Normalisasi role pengguna
    $detectedRole = match (true) {
        in_array($rawRole, ['super_admin', 'admin', 'admin_pusat', 'superadmin']) => 'super_admin',
        in_array($rawRole, ['apoteker', 'tenant', 'staf_klien', 'staff']) => 'apoteker',
        default => 'pelanggan',
    };

    // Dukungan override role melalui URL parameter untuk pratinjau instan (?role=super_admin / ?role=apoteker / ?role=pelanggan)
    $activeRole = in_array(request('role'), ['super_admin', 'apoteker', 'pelanggan']) 
        ? request('role') 
        : $detectedRole;
@endphp
<body class="min-h-full bg-slate-100/90 text-slate-800 antialiased flex flex-col font-sans" x-data="{
    currentRole: '{{ $activeRole }}',
    detectedRole: '{{ $detectedRole }}',
    adminTab: 'cabang',
    apotekerTab: 'stok_bahan',
    pelangganTab: 'upload_resep',
    kategoriObatBebas: 'semua',
    searchObatBebas: '',
    dosisSiangDiminum: false,
    keranjangPelanggan: [
        { id: 1, nama: 'Paracetamol 500mg Kaplet', harga: 8500, qty: 1, kemasan: 'Strip 10 Kaplet' },
        { id: 3, nama: 'Vitamin C 500mg IPI Strip', harga: 12000, qty: 2, kemasan: 'Strip 10 Tablet' }
    ],
    tambahKeranjang(id, nama, harga, kemasan) {
        let item = this.keranjangPelanggan.find(i => i.id === id);
        if (item) {
            item.qty++;
        } else {
            this.keranjangPelanggan.push({ id, nama, harga, qty: 1, kemasan });
        }
        this.tampilkanNotif(nama + ' berhasil ditambahkan ke keranjang belanja Anda.', 'sukses');
    },
    hapusKeranjang(index) {
        this.keranjangPelanggan.splice(index, 1);
        this.tampilkanNotif('Item berhasil dihapus dari keranjang belanja.', 'sukses');
    },
    totalKeranjang() {
        return this.keranjangPelanggan.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    },
    modalCopyResep: false,
    modalTambahJadwal: false,
    chatInputText: '',
    chatMessages: [
        { sender: 'apoteker', nama: 'apt. Siti Nurhaliza, S.Farm (APJ Banyuwangi Kota)', waktu: '09:45 WIB', text: 'Halo Ibu/Bapak {{ $userName }}, salam sehat dari Apotek OmniPharma. Ada yang bisa kami bantu seputar aturan minum, dosis, atau efek samping obat resep Anda hari ini?' },
        { sender: 'user', nama: '{{ $userName }}', waktu: '09:48 WIB', text: 'Selamat pagi Bu Apoteker, mau tanya apakah obat Amodiaquine untuk malaria ini harus diminum sesudah makan atau sebelum makan ya?' },
        { sender: 'apoteker', nama: 'apt. Siti Nurhaliza, S.Farm (APJ Banyuwangi Kota)', waktu: '09:50 WIB', text: 'Baik Ibu/Bapak, obat Amodiaquine sebaiknya diminum sesudah makan (post coenam) dengan segelas air putih hangat untuk mencegah rasa mual di lambung dan memaksimalkan penyerapan obat ke dalam tubuh.' }
    ],
    kirimChatKonsultasi() {
        if (!this.chatInputText || !this.chatInputText.trim()) return;
        let pesanUser = this.chatInputText.trim();
        this.chatMessages.push({
            sender: 'user',
            nama: '{{ $userName }}',
            waktu: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' WIB',
            text: pesanUser
        });
        this.chatInputText = '';
        this.tampilkanNotif('Pesan konsultasi Anda telah terkirim ke Apoteker Cabang.', 'sukses');
        setTimeout(() => {
            this.chatMessages.push({
                sender: 'apoteker',
                nama: 'apt. Siti Nurhaliza, S.Farm (APJ Banyuwangi Kota)',
                waktu: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ' WIB',
                text: 'Terima kasih atas pertanyaannya. Anjuran apoteker: Obat wajib diminum secara teratur pada jam yang sama hingga tuntas sesuai anjuran resep dokter ya Bu/Pak.'
            });
        }, 1200);
    },
    searchKeyword: '',
    filterKategoriBahan: 'semua',
    modalResepDetail: false,
    selectedResep: {
        id: '#ORD-20260917-0042',
        pasien: 'Candi Chier',
        usia: '88 tahun',
        dokter: 'dr. Budi, Sp.PD',
        sip: '503/SIP/2024',
        resep: 'R/ Amodiaquine 200mg No. X (S 3 dd tab I d.c)',
        cabang: 'Cabang Banyuwangi Kota',
        status: 'menunggu',
        tanggal: '17 Sep 2026, 09:30 WIB',
        diagnosa: 'Malaria Falciparum Tanpa Komplikasi'
    },
    modalStockOpname: false,
    selectedBahan: {
        id: 'SKU-BHN-001',
        nama: 'Paracetamol Pure Powder (Bahan Baku)',
        kategori: 'Bahan Baku Serbuk Racikan',
        batch: 'BATCH-2026-PC01',
        stokFisik: 2500,
        satuan: 'Gram',
        expired: '15 Des 2026',
        sisaHari: 97,
        status: 'Aman'
    },
    modalUploadResep: false,
    modalColdChain: false,
    selectedColdChain: {
        faktur: 'FAK-ENS-2026-1188',
        pbf: 'PT Enseval Putera Megatrading',
        produk: 'Insulin Glargine 100 IU/mL (Cold Chain 2-8°C)',
        suhuTiba: '4.2°C',
        statusSuhu: 'Lolos Toleransi Suhu Dingin (2°C - 8°C)',
        loggerStatus: 'Normal (Tidak Ada Freeze Spike)',
        statusVerifikasi: 'menunggu'
    },
    verifikasiColdChain() {
        this.selectedColdChain.statusVerifikasi = 'diverifikasi';
        this.modalColdChain = false;
        this.tampilkanNotif('Faktur Cold Chain ' + this.selectedColdChain.faktur + ' (' + this.selectedColdChain.produk + ') berhasil diverifikasi APJ dengan suhu aman ' + this.selectedColdChain.suhuTiba + '!', 'sukses');
    },
    modalTutupPeriode: false,
    modalRoleSwitcher: false,
    notifPesan: '',
    notifTipe: 'sukses',
    tampilkanNotif(pesan, tipe = 'sukses') {
        this.notifPesan = pesan;
        this.notifTipe = tipe;
        setTimeout(() => this.notifPesan = '', 5000);
    },
    verifikasiResep(aksi) {
        this.selectedResep.status = aksi;
        this.modalResepDetail = false;
        this.tampilkanNotif('Resep ' + this.selectedResep.id + ' berhasil ditandai: ' + aksi.toUpperCase() + ' oleh Apoteker Penanggung Jawab.');
    },
    simpanStockOpname() {
        this.modalStockOpname = false;
        this.tampilkanNotif('Penyesuaian stok bahan obat ' + this.selectedBahan.nama + ' berhasil diperbarui ke kartu stok sistem.');
    },
    eksekusiTutupPeriode() {
        this.modalTutupPeriode = false;
        this.tampilkanNotif('Periode penjualan 01-16 Sep 2026 berhasil ditutup dan seluruh transaksi lintas cabang terkunci.');
    }
}">

    <!-- TOAST NOTIFIKASI -->
    <div x-cloak x-show="notifPesan" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-3"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="toast-notif fixed bottom-6 right-6 z-50 max-w-md w-full shadow-2xl rounded-2xl border p-4 flex items-start gap-3 backdrop-blur-md"
         :class="notifTipe === 'sukses' ? 'bg-[#0A231E]/95 border-emerald-500/40 text-white' : 'bg-red-950/95 border-red-500/40 text-white'">
        <div class="h-6 w-6 rounded-full flex items-center justify-center shrink-0 mt-0.5"
             :class="notifTipe === 'sukses' ? 'bg-[#059669] text-white' : 'bg-red-500 text-white'">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1 text-sm font-medium leading-snug" x-text="notifPesan"></div>
        <button @click="notifPesan = ''" class="text-slate-400 hover:text-white transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- HEADER / TOP NAVBAR ATAS (Deep Spruce Pharmacy Header Sesuai Tema Login OmniPharma) -->
    <header class="bg-[#0A231E] border-b border-emerald-800/40 sticky top-0 z-30 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Brand & Identitas OmniPharma -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 p-1.5 shadow-xs border border-emerald-500/30 group-hover:scale-105 transition">
                        <img src="/logo.png" alt="OmniPharma Logo" class="h-full w-full object-contain drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="block text-xs font-black tracking-widest text-emerald-400 uppercase">OmniPharma</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-600 text-white shadow-2xs">NETWORK</span>
                        </div>
                        <span class="block text-sm font-extrabold text-white leading-tight">Dashboard Farmasi Multi-Peran</span>
                    </div>
                </a>
            </div>

            <!-- ROLE SELECTOR PILL (Latar Deep Spruce #0A231E, Border Emerald Halus, Tombol Aktif Emerald #059669, Inaktif Emerald-100) -->
            <div class="hidden md:flex items-center bg-[#071A15] p-1 rounded-2xl border border-emerald-500/30 shadow-inner">
                <!-- Tombol Super Admin -->
                <a href="{{ url()->current() }}?role=super_admin"
                   @click.prevent="currentRole = 'super_admin'"
                   class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs transition-all min-h-[36px] {{ $activeRole === 'super_admin' ? 'bg-[#059669] text-white font-extrabold shadow-sm' : 'text-emerald-100 font-semibold hover:text-white hover:bg-white/10' }}"
                   :class="currentRole === 'super_admin' 
                       ? 'bg-[#059669] text-white font-extrabold shadow-sm' 
                       : 'text-emerald-100 font-semibold hover:text-white hover:bg-white/10'">
                    <span class="h-2 w-2 rounded-full {{ $activeRole === 'super_admin' ? 'bg-white' : 'bg-emerald-700' }}" 
                          :class="currentRole === 'super_admin' ? 'bg-white' : 'bg-emerald-700'"></span>
                    <span>Super Admin</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold {{ $activeRole === 'super_admin' ? 'bg-emerald-700/80 text-white' : 'bg-white/10 text-emerald-200' }}"
                          :class="currentRole === 'super_admin' ? 'bg-emerald-700/80 text-white' : 'bg-white/10 text-emerald-200'">Kelola Semua</span>
                </a>

                <!-- Tombol Apoteker -->
                <a href="{{ url()->current() }}?role=apoteker"
                   @click.prevent="currentRole = 'apoteker'"
                   class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs transition-all min-h-[36px] {{ $activeRole === 'apoteker' ? 'bg-[#059669] text-white font-extrabold shadow-sm' : 'text-emerald-100 font-semibold hover:text-white hover:bg-white/10' }}"
                   :class="currentRole === 'apoteker' 
                       ? 'bg-[#059669] text-white font-extrabold shadow-sm' 
                       : 'text-emerald-100 font-semibold hover:text-white hover:bg-white/10'">
                    <span class="h-2 w-2 rounded-full {{ $activeRole === 'apoteker' ? 'bg-white' : 'bg-emerald-700' }}" 
                          :class="currentRole === 'apoteker' ? 'bg-white' : 'bg-emerald-700'"></span>
                    <span>Apoteker (APJ)</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold {{ $activeRole === 'apoteker' ? 'bg-emerald-700/80 text-white' : 'bg-white/10 text-emerald-200' }}"
                          :class="currentRole === 'apoteker' ? 'bg-emerald-700/80 text-white' : 'bg-white/10 text-emerald-200'">Stok &amp; Resep</span>
                </a>

                <!-- Tombol Pasien / Pelanggan -->
                <a href="{{ url()->current() }}?role=pelanggan"
                   @click.prevent="currentRole = 'pelanggan'"
                   class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs transition-all min-h-[36px] {{ $activeRole === 'pelanggan' ? 'bg-[#059669] text-white font-extrabold shadow-sm' : 'text-emerald-100 font-semibold hover:text-white hover:bg-white/10' }}"
                   :class="currentRole === 'pelanggan' 
                       ? 'bg-[#059669] text-white font-extrabold shadow-sm' 
                       : 'text-emerald-100 font-semibold hover:text-white hover:bg-white/10'">
                    <span class="h-2 w-2 rounded-full {{ $activeRole === 'pelanggan' ? 'bg-white' : 'bg-emerald-700' }}" 
                          :class="currentRole === 'pelanggan' ? 'bg-white' : 'bg-emerald-700'"></span>
                    <span>Pasien / Pelanggan</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold {{ $activeRole === 'pelanggan' ? 'bg-emerald-700/80 text-white' : 'bg-white/10 text-emerald-200' }}"
                          :class="currentRole === 'pelanggan' ? 'bg-emerald-700/80 text-white' : 'bg-white/10 text-emerald-200'">Beli &amp; Resep</span>
                </a>
            </div>

            <!-- Profile & Aksi Cepat -->
            <div class="flex items-center gap-3">
                <!-- Tombol Role Switcher Mobile -->
                <button type="button" 
                        @click="modalRoleSwitcher = true"
                        class="md:hidden inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 text-emerald-100 border border-emerald-600/40 hover:bg-white/15 hover:text-white text-xs font-bold min-h-[40px] transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span x-text="currentRole === 'super_admin' ? 'Admin' : (currentRole === 'apoteker' ? 'Apoteker' : 'Pelanggan')">
                        {{ $activeRole === 'super_admin' ? 'Admin' : ($activeRole === 'apoteker' ? 'Apoteker' : 'Pelanggan') }}
                    </span>
                </button>

                <!-- Status Role Asli Akun -->
                <div class="hidden xl:flex flex-col text-right">
                    <span class="text-xs font-bold text-white">{{ $userName }}</span>
                    <span class="text-[11px] text-emerald-300 font-medium">Akun: {{ strtoupper($rawRole) }}</span>
                </div>

                <!-- User Dropdown Profile -->
                <div class="relative flex items-center gap-2">
                    <div class="h-10 w-10 rounded-full ring-2 ring-emerald-500/40 overflow-hidden bg-gradient-to-tr from-[#059669] to-emerald-400 flex items-center justify-center text-white font-bold text-sm shadow-xs">
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $userName }}" class="h-full w-full object-cover">
                        @else
                            {{ strtoupper(substr($userName, 0, 2)) }}
                        @endif
                    </div>

                    <!-- Tombol Keluar -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                title="Keluar dari akun"
                                class="inline-flex items-center justify-center h-10 w-10 rounded-xl border border-emerald-800/80 bg-white/5 text-emerald-200 hover:text-white hover:bg-red-600/80 hover:border-red-500 transition"
                                aria-label="Keluar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- BANNER PERINGATAN PRATINJAU ROLE JIKA BEDA DENGAN ROLE ASLI AKUN -->
    <div x-show="currentRole !== detectedRole"
         class="{{ $activeRole !== $detectedRole ? 'flex' : 'hidden' }} bg-amber-500 text-slate-950 px-4 py-2 text-xs font-bold text-center border-b border-amber-600 items-center justify-center gap-2"
         :class="currentRole !== detectedRole ? 'flex' : 'hidden'">
        <svg class="h-4 w-4 text-slate-950 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Anda sedang melihat pratinjau antarmuka peran: <strong class="uppercase" x-text="currentRole">{{ strtoupper($activeRole) }}</strong> (Role akun Anda: <strong class="uppercase">{{ $rawRole }}</strong>).</span>
        <a href="{{ url()->current() }}" @click.prevent="currentRole = detectedRole" class="underline hover:text-white transition ml-2 font-black">Kembali ke Role Asli</a>
    </div>

    <!-- ========================================================================= -->
    <!-- DASHBOARD VIEW 1: SUPER ADMIN (Enterprise Executive Console)              -->
    <!-- Tata Kelola Multi-Cabang, Finansial Konsolidasi & Kepatuhan Regulasi       -->
    <!-- ========================================================================= -->
    <main x-show="currentRole === 'super_admin'" 
          :class="currentRole === 'super_admin' ? 'block' : 'hidden'" 
          x-data="{
              periodeTerkunci: false,
              isPinging: false,
              pingSpeed: 28,
              testKoneksiGateway() {
                  this.isPinging = true;
                  setTimeout(() => {
                      this.isPinging = false;
                      this.pingSpeed = Math.floor(Math.random() * 10) + 24;
                      tampilkanNotif('Uji diagnostik koneksi gateway SATUSEHAT (FHIR) & SIPNAP berhasil (' + this.pingSpeed + ' ms). Server beroperasi optimal.', 'sukses');
                  }, 750);
              },
              tutupPeriodeSekarang() {
                  this.periodeTerkunci = true;
                  this.modalTutupPeriode = false;
                  tampilkanNotif('Periode penjualan 01-16 Sep 2026 berhasil ditutup dan seluruh transaksi lintas cabang terkunci sesuai ADR-05.', 'sukses');
              },
              bukaKunciPeriode() {
                  this.periodeTerkunci = false;
                  tampilkanNotif('Kunci periode penjualan dibuka sementara dengan otorisasi Super Admin Pusat.', 'sukses');
              },
              eksporKonsolidasi() {
                  tampilkanNotif('Mengunduh berkas laporan konsolidasi multi-cabang: Rekap_Penjualan_01-16_Sep_2026.xlsx...', 'sukses');
              }
          }"
          class="{{ $activeRole === 'super_admin' ? 'block' : 'hidden' }} flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        
        <!-- HERO / COMMAND CENTER HEADER: ENTERPRISE EXECUTIVE CONSOLE -->
        <section class="rounded-3xl bg-[#0A231E] text-white p-6 sm:p-8 shadow-xl border border-emerald-900/60 relative overflow-hidden">
            <div class="absolute -right-24 -top-24 w-80 h-80 bg-emerald-600/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-3 max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-800/80 text-emerald-100 border border-emerald-600/40 text-xs font-bold tracking-wide uppercase">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Enterprise Executive Console
                        </span>
                        <span class="text-xs font-medium text-emerald-200/90 bg-white/10 px-3 py-1 rounded-full border border-white/10">
                            Pusat Kendali &amp; Tata Kelola Jaringan OmniPharma Network
                        </span>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            Pusat Otoritas &amp; Kendali Finansial Lintas Cabang
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed mt-1">
                            Konsol eksekutif terpadu untuk monitoring agregasi omzet cabang, penguncian periode fiskal akuntansi, pengawasan izin SIA Kemenkes, otorisasi staf APJ, serta kepatuhan integrasi digital SATUSEHAT &amp; SIPNAP.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-emerald-200/80 pt-1">
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>2 Unit Cabang Beroperasi</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>100% Kepatuhan SIA &amp; CDOB</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Periode Fiskal: 01-16 Sep 2026</span>
                        </div>
                    </div>
                </div>

                <!-- TOMBOL AKSI EKSEKUTIF UTAMA -->
                <div class="flex flex-col sm:flex-row lg:flex-col xl:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    <button type="button" 
                            @click="modalTutupPeriode = true"
                            :class="periodeTerkunci ? 'bg-slate-700 text-slate-300 hover:bg-slate-600' : 'bg-rose-700 hover:bg-rose-800 text-white'"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs shadow-md transition min-h-[44px]">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span x-text="periodeTerkunci ? 'Periode Sudah Terkunci' : 'Tutup Buku & Kunci Transaksi'">Tutup Buku &amp; Kunci Transaksi</span>
                    </button>

                    <button type="button"
                            @click="eksporKonsolidasi()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition min-h-[44px]">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Ekspor Laporan (.XLSX)</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- KPI STATS EKSEKUTIF (4 KARTU METRIK UTAMA) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Omzet Konsolidasi -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:border-emerald-600/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Omzet Konsolidasi Multi-Cabang</span>
                    <span class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">Rp 142.850.000</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-emerald-700">
                        <span>↑ +14.2%</span>
                        <span class="text-slate-500 font-normal">vs periode sebelumnya</span>
                    </div>
                </div>
            </div>

            <!-- 2. Total Pesanan Jaringan -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:border-emerald-600/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Pesanan Transaksi</span>
                    <span class="h-9 w-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">1.840 Pesanan</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-500">
                        <span>820 BWI • 610 Rogojampi • 410 Digital</span>
                    </div>
                </div>
            </div>

            <!-- 3. Legalitas SIA / Kemenkes RI -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:border-emerald-600/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jaringan Cabang &amp; Izin SIA</span>
                    <span class="h-9 w-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">2 Unit Aktif</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                        <span>100% SIA Kemenkes Terverifikasi</span>
                    </div>
                </div>
            </div>

            <!-- 4. Integritas Transaksi & Audit -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs hover:border-emerald-600/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status Buku Periode</span>
                    <span class="h-9 w-9 rounded-xl flex items-center justify-center"
                          :class="periodeTerkunci ? 'bg-[#0A231E] text-emerald-300' : 'bg-amber-50 text-amber-700'">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight"
                         x-text="periodeTerkunci ? 'Terkunci' : 'Siap Ditutup'">
                        Siap Ditutup
                    </div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-medium"
                         :class="periodeTerkunci ? 'text-emerald-700' : 'text-amber-700'">
                        <span class="h-2 w-2 rounded-full" :class="periodeTerkunci ? 'bg-emerald-600' : 'bg-amber-500'"></span>
                        <span x-text="periodeTerkunci ? 'Snapshot ADR-05 Append-Only' : '01-16 Sep 2026 Menunggu Kunci'">01-16 Sep 2026 Menunggu Kunci</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- GRID EKSEKUTIF 2 KOLOM                                                -->
        <!-- Kolom Kiri: Finansial, Cabang SIA, Master Data Obat                    -->
        <!-- Kolom Kanan: Tutup Periode (Lock), Otorisasi APJ, Integrasi Server    -->
        <!-- ===================================================================== -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ================================================================= -->
            <!-- KOLOM KIRI (7/12): FINANSIAL & MASTER REGULASI                    -->
            <!-- ================================================================= -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. PANEL AGREGASI FINANSIAL MULTI-CABANG -->
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-[#0A231E] text-white flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Agregasi Finansial Multi-Cabang</h2>
                                <p class="text-xs text-slate-500">Perbandingan kontribusi omzet riil dan volume order antar unit apotek.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                                Total: Rp 142.850.000
                            </span>
                        </div>
                    </div>

                    <!-- Visual Proporsi Kontribusi Bar (Deep Spruce & Emerald Palette) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                            <span>Distribusi Kontribusi Omzet</span>
                            <span class="text-slate-400">1.840 Transaksi Selesai</span>
                        </div>
                        <div class="h-3.5 w-full bg-slate-100 rounded-full overflow-hidden flex p-0.5 gap-0.5">
                            <div class="bg-[#0A231E] h-full rounded-l-full transition-all" style="width: 47.7%" title="Banyuwangi Kota: 47.7%"></div>
                            <div class="bg-emerald-600 h-full transition-all" style="width: 31.8%" title="Rogojampi: 31.8%"></div>
                            <div class="bg-slate-400 h-full rounded-r-full transition-all" style="width: 20.5%" title="Digital & Delivery: 20.5%"></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-600 pt-1">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#0A231E]"></span>
                                <span>Banyuwangi Kota: <strong>47.7%</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                                <span>Rogojampi: <strong>31.8%</strong></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                                <span>Digital Portal: <strong>20.5%</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Perbandingan Finansial Multi-Cabang -->
                    <div class="space-y-3 pt-1">
                        <!-- Unit 1: Banyuwangi Kota -->
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:border-emerald-600/30 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-[#0A231E] text-white font-mono text-[10px] font-bold">CL-BWI-01</span>
                                    <span class="font-extrabold text-sm text-slate-900">Cabang Banyuwangi Kota</span>
                                </div>
                                <div class="text-xs text-slate-500">
                                    APJ: <strong class="text-slate-700">apt. Daffa Kurnia, S.Farm</strong> • 820 order (Rata-rata order Rp 83.170)
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0">
                                <div class="text-lg font-black text-slate-900 font-mono">Rp 68.200.000</div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                    <span>Target 108%</span>
                                    <span>(Tercapai)</span>
                                </span>
                            </div>
                        </div>

                        <!-- Unit 2: Rogojampi -->
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:border-emerald-600/30 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-700 text-white font-mono text-[10px] font-bold">CL-RGJ-02</span>
                                    <span class="font-extrabold text-sm text-slate-900">Cabang Rogojampi</span>
                                </div>
                                <div class="text-xs text-slate-500">
                                    APJ: <strong class="text-slate-700">apt. Dewi Sartika, S.Farm</strong> • 610 order (Rata-rata order Rp 74.426)
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0">
                                <div class="text-lg font-black text-slate-900 font-mono">Rp 45.400.000</div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                    <span>Target 95%</span>
                                    <span>(Stabil)</span>
                                </span>
                            </div>
                        </div>

                        <!-- Unit 3: Omni Digital Direct Portal -->
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:border-emerald-600/30 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-600 text-white font-mono text-[10px] font-bold">DIGITAL-00</span>
                                    <span class="font-extrabold text-sm text-slate-900">Omni Digital Resep &amp; Delivery</span>
                                </div>
                                <div class="text-xs text-slate-500">
                                    Kanal Konsolidasi Digital • 410 order via Upload Resep Pasien Web
                                </div>
                            </div>
                            <div class="text-left sm:text-right shrink-0">
                                <div class="text-lg font-black text-slate-900 font-mono">Rp 29.250.000</div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600">
                                    <span>Kanal Online</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Agregasi -->
                    <div class="p-4 rounded-2xl bg-[#0A231E]/5 border border-emerald-900/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="text-xs text-slate-700">
                            Total Penjualan Bersih Jaringan: <strong class="text-[#0A231E] font-extrabold">Rp 142.850.000</strong> (1.840 Transaksi Bersih)
                        </div>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:text-emerald-900 transition">
                            <span>Buka Buku Besar Finansial</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- 2. PANEL MANAJEMEN CABANG & IZIN SIA KEMENKES RI -->
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Manajemen Cabang &amp; Izin SIA Kemenkes RI</h2>
                                <p class="text-xs text-slate-500">Legalitas operasional sarana kefarmasian, registrasi OSS, dan apoteker penanggung jawab.</p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="tampilkanNotif('Formulir pengajuan pendaftaran izin SIA cabang baru dibuka.', 'sukses')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shrink-0 min-h-[36px]">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Cabang Baru</span>
                        </button>
                    </div>

                    <!-- Daftar Cabang Berizin -->
                    <div class="space-y-4">
                        <!-- Cabang 1: Banyuwangi Kota -->
                        <div class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-600/40 transition space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-extrabold text-sm text-slate-900">Apotek OmniPharma - Cabang Banyuwangi Kota</h3>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                            SIA Aktif
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">Jl. Ahmad Yani No. 45, Banyuwangi Kota, Jawa Timur</p>
                                </div>
                                <a href="{{ route('tenant.dashboard', ['client' => 'banyuwangi-kota']) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#0A231E] hover:bg-[#133830] text-emerald-200 font-bold text-xs transition shrink-0">
                                    <span>Inspeksi Cabang</span>
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2 border-t border-slate-100 text-xs">
                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">No. Izin SIA Kemenkes</span>
                                    <span class="font-mono font-bold text-slate-800">503/SIA/35.10/2023</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Apoteker Penanggung Jawab</span>
                                    <span class="font-bold text-slate-800 truncate block">apt. Daffa Kurnia, S.Farm</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Standar Kepatuhan CDOB</span>
                                    <span class="font-bold text-emerald-700">100% Terverifikasi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cabang 2: Rogojampi -->
                        <div class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-600/40 transition space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-extrabold text-sm text-slate-900">Apotek OmniPharma - Cabang Rogojampi</h3>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                            SIA Aktif
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">Jl. Raya Rogojampi No. 88, Rogojampi, Jawa Timur</p>
                                </div>
                                <a href="{{ route('tenant.dashboard', ['client' => 'rogojampi']) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#0A231E] hover:bg-[#133830] text-emerald-200 font-bold text-xs transition shrink-0">
                                    <span>Inspeksi Cabang</span>
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2 border-t border-slate-100 text-xs">
                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">No. Izin SIA Kemenkes</span>
                                    <span class="font-mono font-bold text-slate-800">503/SIA/35.10/2024</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Apoteker Penanggung Jawab</span>
                                    <span class="font-bold text-slate-800 truncate block">apt. Dewi Sartika, S.Farm</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Standar Kepatuhan CDOB</span>
                                    <span class="font-bold text-emerald-700">100% Terverifikasi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. MASTER DATA OBAT NASIONAL & KEBIJAKAN MARGIN HARGA -->
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-slate-800 text-white flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Master Data Obat &amp; Kebijakan Margin Harga</h2>
                                <p class="text-xs text-slate-500">Standarisasi HET Kemenkes, katalog obat induk, dan formula margin laba.</p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="tampilkanNotif('Sinkronisasi katalog E-Report BPOM nasional berhasil diperbarui.', 'sukses')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-emerald-600 hover:bg-slate-50 text-slate-700 font-bold text-xs transition shrink-0 min-h-[36px]">
                            <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Sinkron BPOM</span>
                        </button>
                    </div>

                    <!-- Tabel Master Data Obat Eksekutif -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-600 min-w-[36rem]">
                            <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Obat / Sediaan Farmasi</th>
                                    <th class="px-4 py-3">Golongan</th>
                                    <th class="px-4 py-3">Beli PBF</th>
                                    <th class="px-4 py-3">HJA Standar</th>
                                    <th class="px-4 py-3">Margin Laba</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 font-bold text-slate-900">
                                        Amodiaquine 200mg Tablet
                                        <span class="block text-[11px] text-slate-400 font-normal">SKU-OMNI-001 • Box 10 Strip</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded font-bold bg-rose-50 text-rose-800 border border-rose-200 text-[10px]">
                                            Keras (Resep)
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono">Rp 38.000</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">Rp 45.000</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-700">+18.4%</td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="tampilkanNotif('Kebijakan margin Amodiaquine dibuka untuk penyesuaian.', 'sukses')" 
                                                class="text-emerald-700 hover:text-emerald-900 font-bold">Edit</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 font-bold text-slate-900">
                                        Paracetamol 500mg Kaplet
                                        <span class="block text-[11px] text-slate-400 font-normal">SKU-OMNI-002 • Strip 10 Kaplet</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px]">
                                            Bebas (Hijau)
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono">Rp 6.500</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">Rp 8.500</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-700">+30.7%</td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="tampilkanNotif('Kebijakan margin Paracetamol dibuka untuk penyesuaian.', 'sukses')" 
                                                class="text-emerald-700 hover:text-emerald-900 font-bold">Edit</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-4 py-3 font-bold text-slate-900">
                                        Sirupus Simplex 1 Liter
                                        <span class="block text-[11px] text-slate-400 font-normal">SKU-BHN-002 • Botol Farmasetis</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded font-bold bg-slate-100 text-slate-800 border border-slate-200 text-[10px]">
                                            Bahan Racikan
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono">Rp 22.000</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">Rp 30.000</td>
                                    <td class="px-4 py-3 font-semibold text-emerald-700">+36.3%</td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="tampilkanNotif('Kebijakan bahan baku dibuka untuk penyesuaian.', 'sukses')" 
                                                class="text-emerald-700 hover:text-emerald-900 font-bold">Edit</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- ================================================================= -->
            <!-- KOLOM KANAN (5/12): KONTROL PERIODE, OTORISASI & SERVER INTEGRASI -->
            <!-- ================================================================= -->
            <div class="lg:col-span-5 space-y-6">

                <!-- 4. PANEL TUTUP BUKU & KUNCI TRANSAKSI (LOCK PERIOD) -->
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl flex items-center justify-center shrink-0"
                                 :class="periodeTerkunci ? 'bg-[#0A231E] text-emerald-300' : 'bg-rose-100 text-rose-700'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Tutup Buku &amp; Kunci Transaksi</h2>
                                <p class="text-xs text-slate-500">Protokol audit integritas snapshot keuangan ADR-05.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold"
                              :class="periodeTerkunci ? 'bg-[#0A231E] text-emerald-300 border border-emerald-800' : 'bg-amber-100 text-amber-900 border border-amber-300'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="periodeTerkunci ? 'bg-emerald-400' : 'bg-amber-600'"></span>
                            <span x-text="periodeTerkunci ? 'Terkunci' : 'Terbuka'">Terbuka</span>
                        </span>
                    </div>

                    <!-- Detail Status Penguncian -->
                    <div class="p-4 rounded-2xl space-y-3"
                         :class="periodeTerkunci ? 'bg-emerald-50/50 border border-emerald-200' : 'bg-slate-50 border border-slate-200'">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase">Periode Fiskal Penjualan</span>
                            <span class="font-mono text-xs font-bold text-slate-900">01 - 16 Sep 2026</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase">Snapshot Nilai Terkunci</span>
                            <span class="font-mono text-sm font-black text-slate-900">Rp 142.850.000</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500 uppercase">Total Pesanan Kasir</span>
                            <span class="text-xs font-bold text-slate-800">1.840 Order Bersih</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200/80 text-[11px] leading-relaxed text-slate-600">
                            <template x-if="!periodeTerkunci">
                                <span>Saat dikunci, seluruh kasir cabang dilarang mengubah transaksi, menerapkan diskon baru, atau membatalkan pesanan pada rentang tanggal ini.</span>
                            </template>
                            <template x-if="periodeTerkunci">
                                <div class="text-emerald-800 font-medium">
                                    ✓ Snapshot periode berhasil dikunci permanen. Hash Integritas: <code class="font-mono text-[10px] bg-white px-1 py-0.5 rounded">SHA256:7f83b...a109</code>.
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Tombol Aksi Penguncian -->
                    <div class="space-y-2">
                        <template x-if="!periodeTerkunci">
                            <button type="button" 
                                    @click="tutupPeriodeSekarang()"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs shadow-xs transition min-h-[44px]">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Kunci Periode Penjualan Sekarang</span>
                            </button>
                        </template>

                        <template x-if="periodeTerkunci">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="bukaKunciPeriode()"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition min-h-[44px]">
                                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                    <span>Buka Kunci (Super Admin)</span>
                                </button>
                                <button type="button" 
                                        @click="tampilkanNotif('Salinan audit trail tutup buku PDF diunduh.', 'sukses')"
                                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#0A231E] hover:bg-[#133830] text-emerald-200 font-bold text-xs transition min-h-[44px]">
                                    <span>Audit PDF</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 5. PANEL OTORISASI STAF & PENUGASAN APOTEKER PENANGGUNG JAWAB (APJ) -->
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Otorisasi Staf &amp; Penugasan APJ</h2>
                                <p class="text-xs text-slate-500">Penetapan wewenang farmasi, nomor SIPA, dan hak akses kasir.</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-500">8 Personel</span>
                    </div>

                    <!-- Matriks Staf & APJ -->
                    <div class="space-y-3">
                        <!-- APJ Banyuwangi Kota -->
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white transition space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-slate-900">apt. Daffa Kurnia, S.Farm</span>
                                <span class="px-2 py-0.5 rounded font-black bg-emerald-100 text-emerald-800 text-[10px]">APJ BANYUWANGI</span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono">SIPA: 19940812/SIPA_35.10/2024</div>
                            <div class="text-[11px] text-emerald-700 font-medium">
                                Wewenang: Validasi Resep Dokter, Narkotika/OOT, Stock Opname, Racikan
                            </div>
                        </div>

                        <!-- APJ Rogojampi -->
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white transition space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-slate-900">apt. Dewi Sartika, S.Farm</span>
                                <span class="px-2 py-0.5 rounded font-black bg-emerald-100 text-emerald-800 text-[10px]">APJ ROGOJAMPI</span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono">SIPA: 19960215/SIPA_35.10/2024</div>
                            <div class="text-[11px] text-emerald-700 font-medium">
                                Wewenang: Validasi Resep Dokter, Narkotika/OOT, Stock Opname, Racikan
                            </div>
                        </div>

                        <!-- Staf Kasir POS -->
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white transition space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs text-slate-900">Staf Kasir &amp; TTK (4 Staf Cabang)</span>
                                <span class="px-2 py-0.5 rounded font-bold bg-slate-200 text-slate-700 text-[10px]">KASIR POS</span>
                            </div>
                            <div class="text-[11px] text-slate-500">
                                Wewenang Terbatas: Transaksi Kasir, Cetak Struk, Entry Resep (Non-Void)
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Kelola Staf -->
                    <button type="button" 
                            @click="tampilkanNotif('Panel wewenang dan penugasan apoteker penanggung jawab dibuka.', 'sukses')"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 hover:border-emerald-600 hover:bg-slate-50 text-slate-700 font-bold text-xs transition min-h-[40px]">
                        <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <span>Kelola Wewenang &amp; Penugasan Staf</span>
                    </button>
                </div>

                <!-- 6. STATUS INTEGRASI SERVER SATUSEHAT KEMENKES & SIPNAP BPOM -->
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-[#0A231E] text-white flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Integrasi Server SATUSEHAT &amp; SIPNAP</h2>
                                <p class="text-xs text-slate-500">Kepatuhan regulasi digital Kemenkes RI dan Badan POM.</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            Live (3/3)
                        </span>
                    </div>

                    <!-- Status Node Server -->
                    <div class="space-y-3 text-xs">
                        <!-- Node 1: SATUSEHAT Kemenkes -->
                        <div class="p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/30 flex items-start justify-between gap-3">
                            <div>
                                <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
                                    <span>Kemenkes SATUSEHAT (FHIR HL7 v4.0.1)</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    1.840 transaksi resep terunggah • Sinkronisasi otomatis
                                </div>
                            </div>
                            <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md shrink-0">
                                <span x-text="pingSpeed + ' ms'">28 ms</span> • Terhubung
                            </span>
                        </div>

                        <!-- Node 2: SIPNAP BPOM -->
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-start justify-between gap-3">
                            <div>
                                <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
                                    <span>SIPNAP BPOM (Narkotika &amp; Psikotropika)</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Laporan berkala periode aktif terverifikasi valid (Nol deviasi)
                                </div>
                            </div>
                            <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md shrink-0">
                                Terkirim
                            </span>
                        </div>

                        <!-- Node 3: BPJS Bridging PRB -->
                        <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-start justify-between gap-3">
                            <div>
                                <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
                                    <span>BPJS Kesehatan Bridge (Apotek PRB)</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Bridging resep Program Rujuk Balik standby online
                                </div>
                            </div>
                            <span class="font-mono text-[11px] font-bold text-slate-600 bg-slate-200/80 px-2 py-0.5 rounded-md shrink-0">
                                Siaga
                            </span>
                        </div>
                    </div>

                    <!-- Tombol Uji Konektivitas Gateway -->
                    <button type="button" 
                            @click="testKoneksiGateway()"
                            :disabled="isPinging"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#0A231E] hover:bg-[#133830] text-emerald-200 font-bold text-xs transition shadow-2xs min-h-[44px] disabled:opacity-50">
                        <svg class="h-4 w-4 text-emerald-400" :class="isPinging ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isPinging ? 'Menguji Latensi API Gateway...' : 'Uji Diagnostik Koneksi Gateway (Ping)'">
                            Uji Diagnostik Koneksi Gateway (Ping)
                        </span>
                    </button>
                </div>

            </div>

        </section>

    </main>

    <!-- ========================================================================= -->
    <!-- DASHBOARD VIEW 2: APOTEKER (Cek Stok Bahan Obat, FEFO, Resep SIPA)        -->
    <!-- ========================================================================= -->
    <main x-show="currentRole === 'apoteker'" 
          :class="currentRole === 'apoteker' ? 'block' : 'hidden'" 
          class="{{ $activeRole === 'apoteker' ? 'block' : 'hidden' }} flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        
        <!-- HERO APOTEKER: Pengawasan Farmasi Klinis & Stok Bahan Baku Obat -->
        <section class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#124B40] text-white p-6 sm:p-8 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-2.5 max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Badge APJ Emerald Resmi (Tanpa Hijau Stabilo Menyilaukan) -->
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-600 text-white text-xs font-black tracking-wide uppercase shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-white animate-pulse"></span>
                            Apoteker Penanggung Jawab (APJ)
                        </span>
                        <!-- Lisensi SIPA Resmi Permenkes RI No. 73/2016 -->
                        <span class="text-xs font-semibold text-emerald-200/90 bg-emerald-950/70 px-3 py-1.5 rounded-full border border-emerald-500/30 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            SIPA Resmi: 19940812/SIPA_35.10/2024/2001 • Permenkes RI No. 73/2016
                        </span>
                        <span class="text-xs font-medium text-slate-300 bg-white/5 px-3 py-1.5 rounded-full border border-white/10">
                            apt. Sulistyaningrum, S.Farm • Cabang Banyuwangi Kota
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        Pemeriksaan Bahan Baku Obat, Rotasi Stok FEFO &amp; Validasi Resep
                    </h1>
                    <p class="text-sm text-emerald-100/80 leading-relaxed">
                        Pusat kendali mutu farmasi apoteker: audit bahan baku racikan murni, rotasi sistem First Expired First Out (FEFO), telaah administratif, farmasetis &amp; klinis resep dokter geriatri/pediatri, compounding etiket digital, penerimaan faktur distributor PBF resmi &amp; cold chain, serta buku register narkotika SIPNAP BPOM.
                    </p>
                </div>

                <!-- Tombol Aksi Cepat Apoteker (Diselaraskan dengan Emerald & Deep Spruce) -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button" 
                            @click="modalStockOpname = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition min-h-[44px]">
                        <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span>Stock Opname Bahan</span>
                    </button>

                    <!-- Tombol Antrean Resep: GANTI warna stabilo cerah dengan Emerald (#059669) -->
                    <button type="button"
                            @click="apotekerTab = 'verifikasi_resep'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-lg shadow-emerald-950/40 transition min-h-[44px] border border-emerald-400/30">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Antrean Resep (3 Pending)</span>
                    </button>

                    <button type="button" 
                            @click="modalColdChain = true"
                            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-[#0A231E]/80 hover:bg-[#0A231E] text-emerald-300 font-bold text-xs border border-emerald-600/40 transition min-h-[44px]">
                        <svg class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Cek Cold Chain PBF</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- 4 KPI CARDS: APOTEKER (Kendali Mutu Farmasi) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Resep Menunggu Telaah -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Antrean Resep Dokter</span>
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">3 Resep Menunggu</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>1 Resep Geriatri (Tn. Candi Chier, 88 th)</span>
                    </div>
                </div>
            </div>

            <!-- Total Stok Bahan Obat & Jadi -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Bahan Obat &amp; Jadi</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">428 SKU Terdaftar</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-emerald-700">
                        <span>38 Bahan Baku • 390 Obat Jadi</span>
                    </div>
                </div>
            </div>

            <!-- Batch Kedaluwarsa Kritis (FEFO) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-red-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Peringatan FEFO (&lt;90 Hari)</span>
                    <span class="p-2 rounded-xl bg-red-50 text-red-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-red-600 tracking-tight">3 Batch Prioritas</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-red-700">
                        <span>#1 Amodiaquine (Sisa 31 Hari)</span>
                    </div>
                </div>
            </div>

            <!-- Kepatuhan Narkotika & SIPNAP -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-500/40 transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Register Narkotika SIPNAP</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">100% Klop Fisik</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-emerald-700">
                        <span>Lemari Ganda APJ • Siap Sinkron BPOM</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- SUB-NAVIGASI 5 TAB KHUSUS APOTEKER (Mandiri & Terpisah Penuh) -->
        <section class="border-b border-slate-200 bg-white rounded-2xl p-2 shadow-xs flex flex-wrap gap-1.5">
            <!-- Tab 1 -->
            <button type="button" 
                    @click="apotekerTab = 'stok_bahan'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="apotekerTab === 'stok_bahan' ? 'bg-[#0A231E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span>Cek Stok Bahan Obat &amp; Batch FEFO</span>
            </button>

            <!-- Tab 2 -->
            <button type="button" 
                    @click="apotekerTab = 'verifikasi_resep'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="apotekerTab === 'verifikasi_resep' ? 'bg-[#0A231E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Telaah Resep Dokter SIPA (No. ORD-0042)</span>
            </button>

            <!-- Tab 3 -->
            <button type="button" 
                    @click="apotekerTab = 'racikan_etiket'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="apotekerTab === 'racikan_etiket' ? 'bg-[#0A231E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                <span>Compounding &amp; Cetak Etiket (PIO)</span>
            </button>

            <!-- Tab 4 -->
            <button type="button" 
                    @click="apotekerTab = 'faktur_pbf'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="apotekerTab === 'faktur_pbf' ? 'bg-[#0A231E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                </svg>
                <span>Penerimaan Faktur PBF &amp; Cold Chain</span>
            </button>

            <!-- Tab 5 -->
            <button type="button" 
                    @click="apotekerTab = 'sipnap'"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="apotekerTab === 'sipnap' ? 'bg-[#0A231E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Buku Register Narkotika &amp; SIPNAP</span>
            </button>
        </section>

        <!-- ===================================================================== -->
        <!-- KONTEN TAB 1 APOTEKER: CEK STOK BAHAN OBAT & BATCH FEFO               -->
        <!-- ===================================================================== -->
        <div x-show="apotekerTab === 'stok_bahan'" :class="apotekerTab === 'stok_bahan' ? 'block' : 'hidden'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                
                <!-- Header & Search Toolbar -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Pemeriksaan Stok Bahan Baku Obat &amp; Rotasi FEFO</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pantau nomor batch, tanggal kedaluwarsa, buffer minimum, dan sisa bahan baku racikan di apotek cabang.</p>
                    </div>

                    <!-- Search Bar & Filter Cepat Bahan Obat -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative">
                            <input type="text" 
                                   x-model="searchKeyword" 
                                   placeholder="Cari bahan / obat / batch..." 
                                   class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs w-48 sm:w-64 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <svg class="h-3.5 w-3.5 text-slate-400 absolute left-2.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <select x-model="filterKategoriBahan" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white font-semibold">
                            <option value="semua">Semua Sediaan Farmasi</option>
                            <option value="bahan_racikan">Bahan Baku Racikan (Serbuk/Sirup/Salep)</option>
                            <option value="obat_keras">Obat Keras (Wajib Resep)</option>
                            <option value="fefo_kritis">Prioritas FEFO (&lt;90 Hari)</option>
                            <option value="narkotika">Lemari Narkotika APJ</option>
                        </select>

                        <button type="button" 
                                @click="modalStockOpname = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Input Penyesuaian</span>
                        </button>
                    </div>
                </div>

                <!-- Banner Edukasi Standar FEFO Kemenkes -->
                <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200 text-xs text-emerald-900 flex items-start gap-3">
                    <svg class="h-5 w-5 text-emerald-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="leading-relaxed">
                        <strong class="font-extrabold text-emerald-950">Aturan Pengeluaran Obat FEFO (First Expired First Out):</strong>
                        Obat dan bahan racikan dengan tanggal kedaluwarsa paling dekat wajib diletakkan di barisan depan rak farmasi dan dikeluarkan lebih dulu saat peracikan resep untuk mencegah obat kedaluwarsa tertinggal di gudang apotek.
                    </div>
                </div>

                <!-- TABEL DETAIL STOK BAHAN OBAT & BATCH FEFO -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Nama Bahan / Obat</th>
                                <th class="px-4 py-3">Bentuk &amp; Kategori</th>
                                <th class="px-4 py-3">No. Batch &amp; Distributor PBF</th>
                                <th class="px-4 py-3">Tgl. Expired &amp; Sisa Hari</th>
                                <th class="px-4 py-3">Stok Fisik Tersedia</th>
                                <th class="px-4 py-3">Urutan Rotasi FEFO</th>
                                <th class="px-4 py-3 text-right">Aksi APJ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Bahan 1: Amodiaquine (FEFO Kritis #1) -->
                            <tr x-show="(filterKategoriBahan === 'semua' || filterKategoriBahan === 'obat_keras' || filterKategoriBahan === 'fefo_kritis') && (!searchKeyword || 'amodiaquine'.includes(searchKeyword.toLowerCase()) || 'batch-2026-001'.includes(searchKeyword.toLowerCase()))"
                                class="hover:bg-slate-50/80 transition bg-red-50/20">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Amodiaquine 200mg
                                    <span class="block text-[11px] text-slate-400 font-normal">Antimalaria Lini Pertama • Tablet Utuh</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-red-100 text-red-800 text-[10px]">OBAT KERAS (G)</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700">
                                    BATCH-2026-001
                                    <span class="block text-[10px] text-slate-400 font-sans">PBF Kimia Farma Trading</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-red-600">10 Okt 2026</span>
                                    <span class="block text-[10px] text-red-500 font-bold">Sisa 31 Hari (Kritis)</span>
                                </td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    48 Tablet
                                    <span class="block text-[10px] text-slate-400 font-normal">Buffer aman: 20</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-black text-[10px] bg-red-600 text-white shadow-xs">
                                        #1 PRIORITAS KELUAR
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-1">
                                    <button @click="selectedBahan = { nama: 'Amodiaquine 200mg', batch: 'BATCH-2026-001', stokFisik: 48, satuan: 'Tablet', expired: '10 Okt 2026' }; modalStockOpname = true;"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition text-[11px]">
                                        <span>Opname</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Bahan 2: Paracetamol Pure Powder (Bahan Baku Racikan) -->
                            <tr x-show="(filterKategoriBahan === 'semua' || filterKategoriBahan === 'bahan_racikan' || filterKategoriBahan === 'fefo_kritis') && (!searchKeyword || 'paracetamol'.includes(searchKeyword.toLowerCase()) || 'batch-2026-pc01'.includes(searchKeyword.toLowerCase()))"
                                class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Paracetamol Pure Powder (Bahan Baku)
                                    <span class="block text-[11px] text-slate-400 font-normal">Serbuk Baku Mikronis untuk Puyer/Kapsul</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-purple-100 text-purple-800 text-[10px]">BAHAN RACIKAN</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700">
                                    BATCH-2026-PC01
                                    <span class="block text-[10px] text-slate-400 font-sans">PBF Mensa Binasukses</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-800">15 Des 2026</span>
                                    <span class="block text-[10px] text-amber-600 font-semibold">Sisa 97 Hari</span>
                                </td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    2.500 Gram
                                    <span class="block text-[10px] text-slate-400 font-normal">Cukup ~5.000 bungkus</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-100 text-amber-900">
                                        Waspada (&lt;100 hari)
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-1">
                                    <button @click="selectedBahan = { nama: 'Paracetamol Pure Powder', batch: 'BATCH-2026-PC01', stokFisik: 2500, satuan: 'Gram', expired: '15 Des 2026' }; modalStockOpname = true;"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition text-[11px]">
                                        <span>Opname</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Bahan 3: Sirupus Simplex 65% (Bahan Pembawa Sirup Racik) -->
                            <tr x-show="(filterKategoriBahan === 'semua' || filterKategoriBahan === 'bahan_racikan') && (!searchKeyword || 'sirupus'.includes(searchKeyword.toLowerCase()) || 'batch-2026-ss12'.includes(searchKeyword.toLowerCase()))"
                                class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Sirupus Simplex 65% Farmasetis
                                    <span class="block text-[11px] text-slate-400 font-normal">Zat Pembawa Sirup Racikan Anak</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-purple-100 text-purple-800 text-[10px]">BAHAN RACIKAN</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700">
                                    BATCH-2026-SS12
                                    <span class="block text-[10px] text-slate-400 font-sans">Lab Farmasi OmniPharma</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-800">20 Jan 2027</span>
                                    <span class="block text-[10px] text-emerald-600 font-semibold">Sisa 133 Hari</span>
                                </td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    12 Botol (1.000 mL)
                                    <span class="block text-[10px] text-slate-400 font-normal">Stok Aman (Buffer: 3)</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                        Stok Aman
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-1">
                                    <button @click="selectedBahan = { nama: 'Sirupus Simplex 65%', batch: 'BATCH-2026-SS12', stokFisik: 12, satuan: 'Botol', expired: '20 Jan 2027' }; modalStockOpname = true;"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition text-[11px]">
                                        <span>Opname</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Bahan 4: Amoxicillin Trihydrate Powder (Antibiotik Racikan) -->
                            <tr x-show="(filterKategoriBahan === 'semua' || filterKategoriBahan === 'bahan_racikan' || filterKategoriBahan === 'obat_keras') && (!searchKeyword || 'amoxicillin'.includes(searchKeyword.toLowerCase()) || 'batch-2026-am04'.includes(searchKeyword.toLowerCase()))"
                                class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Amoxicillin Trihydrate Powder
                                    <span class="block text-[11px] text-slate-400 font-normal">Bahan Baku Antibiotik untuk Suspensi Kering</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-red-100 text-red-800 text-[10px]">ANTIBIOTIK KERAS</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700">
                                    BATCH-2026-AM04
                                    <span class="block text-[10px] text-slate-400 font-sans">PBF Enseval Putera</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-800">18 Feb 2027</span>
                                    <span class="block text-[10px] text-emerald-600 font-semibold">Sisa 162 Hari</span>
                                </td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    850 Gram
                                    <span class="block text-[10px] text-slate-400 font-normal">Buffer min: 500g</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                        Stok Aman
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-1">
                                    <button @click="selectedBahan = { nama: 'Amoxicillin Powder', batch: 'BATCH-2026-AM04', stokFisik: 850, satuan: 'Gram', expired: '18 Feb 2027' }; modalStockOpname = true;"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition text-[11px]">
                                        <span>Opname</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Bahan 5: Dexamethasone Powder BP (FEFO Prioritas #2) -->
                            <tr x-show="(filterKategoriBahan === 'semua' || filterKategoriBahan === 'bahan_racikan' || filterKategoriBahan === 'fefo_kritis') && (!searchKeyword || 'dexamethasone'.includes(searchKeyword.toLowerCase()) || 'batch-2026-dx02'.includes(searchKeyword.toLowerCase()))"
                                class="hover:bg-slate-50/80 transition bg-amber-50/20">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Dexamethasone Micronized Powder BP 0.5mg
                                    <span class="block text-[11px] text-slate-400 font-normal">Bahan Baku Antiinflamasi Kortikosteroid Racikan</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-purple-100 text-purple-800 text-[10px]">BAHAN RACIKAN</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700">
                                    BATCH-2026-DX02
                                    <span class="block text-[10px] text-slate-400 font-sans">PBF Mensa Binasukses</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-red-600">02 Nov 2026</span>
                                    <span class="block text-[10px] text-red-500 font-semibold">Sisa 54 Hari</span>
                                </td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    120 Gram
                                    <span class="block text-[10px] text-slate-400 font-normal">Buffer min: 50g</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-black text-[10px] bg-amber-600 text-white shadow-xs">
                                        #2 PRIORITAS KELUAR
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-1">
                                    <button @click="selectedBahan = { nama: 'Dexamethasone Powder BP', batch: 'BATCH-2026-DX02', stokFisik: 120, satuan: 'Gram', expired: '02 Nov 2026' }; modalStockOpname = true;"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition text-[11px]">
                                        <span>Opname</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Bahan 6: Salep Basis Hidrokarbon / Vaseline Album USP -->
                            <tr x-show="(filterKategoriBahan === 'semua' || filterKategoriBahan === 'bahan_racikan') && (!searchKeyword || 'vaseline'.includes(searchKeyword.toLowerCase()) || 'batch-2026-va99'.includes(searchKeyword.toLowerCase()))"
                                class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Vaseline Album USP (Salep Basis Putih)
                                    <span class="block text-[11px] text-slate-400 font-normal">Bahan Baku Dasar Salep &amp; Krim Obat Luar</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-blue-100 text-blue-800 text-[10px]">BASIS OBAT LUAR</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700">
                                    BATCH-2026-VA99
                                    <span class="block text-[10px] text-slate-400 font-sans">PBF Kimia Farma Trading</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-800">14 Jun 2027</span>
                                    <span class="block text-[10px] text-emerald-600 font-semibold">Sisa 278 Hari</span>
                                </td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    4.000 Gram
                                    <span class="block text-[10px] text-slate-400 font-normal">Buffer min: 1.000g</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                        Stok Aman
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-1">
                                    <button @click="selectedBahan = { nama: 'Vaseline Album USP', batch: 'BATCH-2026-VA99', stokFisik: 4000, satuan: 'Gram', expired: '14 Jun 2027' }; modalStockOpname = true;"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition text-[11px]">
                                        <span>Opname</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Bahan 7: Codein HCl Pure Powder (Narkotika Golongan III Lemari Ganda) -->
                            <tr x-show="(filterKategoriBahan === 'semua' || filterKategoriBahan === 'narkotika') && (!searchKeyword || 'codein'.includes(searchKeyword.toLowerCase()) || 'batch-2026-cd09'.includes(searchKeyword.toLowerCase()))"
                                class="hover:bg-slate-50/80 transition bg-emerald-50/30">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Codein HCl 10mg (Narkotika Golongan III)
                                    <span class="block text-[11px] text-slate-400 font-normal">Antitusif Narkotika • Lemari Kunci Ganda APJ</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-rose-100 text-rose-800 text-[10px]">NARKOTIKA GOL. III</span>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-700">
                                    BATCH-2026-CD09
                                    <span class="block text-[10px] text-slate-400 font-sans">PBF Kimia Farma (SP Khusus)</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-800">05 Agu 2027</span>
                                    <span class="block text-[10px] text-emerald-600 font-semibold">Sisa 330 Hari</span>
                                </td>
                                <td class="px-4 py-3.5 font-extrabold text-slate-900">
                                    140 Tablet
                                    <span class="block text-[10px] text-emerald-700 font-bold">100% Klop Lemari Fisik</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] bg-slate-800 text-white">
                                        TERKUNCI APJ
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right space-x-1">
                                    <button @click="selectedBahan = { nama: 'Codein HCl 10mg', batch: 'BATCH-2026-CD09', stokFisik: 140, satuan: 'Tablet', expired: '05 Agu 2027' }; modalStockOpname = true;"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition text-[11px]">
                                        <span>Opname</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- KONTEN TAB 2 APOTEKER: VERIFIKASI RESEP DOKTER (SIPA)                 -->
        <!-- ===================================================================== -->
        <div x-show="apotekerTab === 'verifikasi_resep'" :class="apotekerTab === 'verifikasi_resep' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Meja Telaah Klinis Resep Dokter (SIPA Permenkes RI No. 73/2016)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kajian wajib 3 pilar: Administratif, Farmasetis, dan Klinis oleh Apoteker Penanggung Jawab sebelum resep disetujui (ACC) dan disiapkan.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                            3 Resep Dalam Antrean
                        </span>
                    </div>
                </div>

                <!-- Kartu Resep Masuk Utama (#ORD-20260917-0042) -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-900 flex items-center justify-center font-black text-base shadow-xs">Rx</span>
                            <div>
                                <div class="text-sm font-black text-slate-900 flex items-center gap-2">
                                    <span>No. Resep: #ORD-20260917-0042</span>
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800">PASIEN GERIATRI</span>
                                </div>
                                <div class="text-xs text-slate-500">Masuk: 17 Sep 2026, 09:30 WIB • Apotek OmniPharma Cabang Banyuwangi Kota</div>
                            </div>
                        </div>

                        <!-- Status Interaktif Resep -->
                        <div>
                            <span x-show="selectedResep.status === 'menunggu'" class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                Status: Menunggu Verifikasi Apoteker
                            </span>
                            <span x-show="selectedResep.status === 'disetujui'" class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                Status: ✓ Resep Disetujui (ACC APJ)
                            </span>
                            <span x-show="selectedResep.status === 'ditolak'" class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-900 border border-red-300">
                                Status: ✗ Resep Ditolak (Catatan Klinis)
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 bg-white rounded-xl border border-slate-200/70 space-y-1.5 shadow-2xs">
                            <span class="font-extrabold text-slate-500 uppercase text-[10px] tracking-wider">Data Pasien &amp; Dokter Peresep</span>
                            <div class="font-black text-slate-900 text-sm">Tn. Candi Chier (88 tahun / Pasien Geriatri)</div>
                            <div class="text-slate-600 font-medium">Dokter: <strong class="text-slate-800">dr. Budi, Sp.PD</strong> (Spesialis Penyakit Dalam)</div>
                            <div class="text-slate-600 font-mono text-[11px]">SIP Dokter: 503/SIP/2024 (Status KKI: Aktif &amp; Terdaftar)</div>
                            <div class="text-slate-700 font-semibold bg-slate-50 p-2 rounded-lg border border-slate-100">
                                Diagnosa Klinis: <span class="text-emerald-800 font-bold">Malaria Falciparum Tanpa Komplikasi</span>
                            </div>
                        </div>

                        <div class="p-3.5 bg-white rounded-xl border border-slate-200/70 space-y-1.5 shadow-2xs">
                            <span class="font-extrabold text-slate-500 uppercase text-[10px] tracking-wider">Item Resep Sediaan Farmasi (e-Prescription)</span>
                            <div class="font-mono font-black text-emerald-900 text-base">R/ Amodiaquine 200mg No. X</div>
                            <div class="text-slate-700 font-bold">Signa: S 3 dd tab I d.c (3 x sehari 1 tablet sewaktu makan)</div>
                            <div class="text-[11px] text-slate-500">Jumlah: 10 Tablet (Regimen pengobatan tuntas malaria selama 3 hari)</div>
                            <div class="text-[11px] text-amber-800 font-semibold bg-amber-50 p-2 rounded-lg border border-amber-200/70">
                                Catatan Klinis APJ: Pada lansia 88 tahun, klirens ginjal menurun. Pantau efek gastrointestinal dan hidrasi cairan cukup.
                            </div>
                        </div>
                    </div>

                    <!-- Checklist Telaah 3 Aspek Permenkes RI No. 73/2016 -->
                    <div class="p-4 bg-emerald-50/70 rounded-xl border border-emerald-200 space-y-2 text-xs text-emerald-950">
                        <div class="flex items-center justify-between">
                            <span class="font-black uppercase text-[11px] text-emerald-900 tracking-wider">Checklist Telaah Permenkes RI No. 73/2016:</span>
                            <span class="text-[10px] font-bold text-emerald-700">Wajib Diverifikasi Sebelum ACC</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-white p-2.5 rounded-lg border border-emerald-200/60 space-y-1">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                                    <input type="checkbox" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span>1. Telaah Administratif</span>
                                </label>
                                <p class="text-[10px] text-slate-500 pl-5 leading-tight">Nama &amp; SIP dr. Budi Sp.PD valid, identitas Tn. Candi Chier (88 th) lengkap.</p>
                            </div>
                            <div class="bg-white p-2.5 rounded-lg border border-emerald-200/60 space-y-1">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                                    <input type="checkbox" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span>2. Telaah Farmasetis</span>
                                </label>
                                <p class="text-[10px] text-slate-500 pl-5 leading-tight">Bentuk sediaan tablet 200mg, stabilitas sediaan baik, signa 3x1 d.c jelas.</p>
                            </div>
                            <div class="bg-white p-2.5 rounded-lg border border-emerald-200/60 space-y-1">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                                    <input type="checkbox" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span>3. Telaah Klinis</span>
                                </label>
                                <p class="text-[10px] text-slate-500 pl-5 leading-tight">Ketepatan indikasi malaria falciparum, tidak ada duplikasi / kontraindikasi obat fatal.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Verifikasi Resep -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <div class="text-[11px] text-slate-500 italic">
                            Verifikasi ditandatangani secara digital oleh: <strong>apt. Sulistyaningrum, S.Farm (APJ)</strong>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="verifikasiResep('ditolak')"
                                    class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs border border-red-200 transition">
                                Tolak Resep (Catatan Klinis)
                            </button>
                            <button type="button" 
                                    @click="verifikasiResep('disetujui')"
                                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>✓ Setujui Resep (ACC &amp; Lanjut Peracikan)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Antrean Resep Masuk Lainnya -->
                <div class="space-y-3 pt-2">
                    <h3 class="text-xs font-black uppercase text-slate-500 tracking-wider">Antrean Resep Masuk Berikutnya</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-3.5 bg-white rounded-xl border border-slate-200 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-xs text-slate-900">#ORD-20260917-0045 • Ny. Siti Rahma (54 th)</div>
                                <div class="text-[11px] text-slate-500">dr. Anita Sp.KK • Salep Racikan Hidrokortison 1% + Vaseline 15g</div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-1 rounded bg-amber-50 text-amber-800 border border-amber-200">Menunggu Telaah</span>
                        </div>
                        <div class="p-3.5 bg-white rounded-xl border border-slate-200 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-xs text-slate-900">#ORD-20260917-0048 • An. Kenzo (5 th)</div>
                                <div class="text-[11px] text-slate-500">dr. Hendra Sp.A • Puyer Paracetamol 120mg + Sirupus Simplex No. X</div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-1 rounded bg-amber-50 text-amber-800 border border-amber-200">Menunggu Telaah</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- KONTEN TAB 3 APOTEKER: COMPOUNDING & CETAK ETIKET (PIO)               -->
        <!-- ===================================================================== -->
        <div x-show="apotekerTab === 'racikan_etiket'" :class="apotekerTab === 'racikan_etiket' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-6">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 mb-1">Pelayanan Compounding (Peracikan) &amp; Cetak Etiket Digital</h2>
                    <p class="text-xs text-slate-500">Pemberian Informasi Obat (PIO) klinis dan pencetakan etiket resmi Indonesia: Etiket Putih (Obat Dalam/Oral) dan Etiket Biru (Obat Luar/Topikal).</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- PREVIEW ETIKET 1: ETIKET PUTIH (OBAT DALAM / ORAL) -->
                    <div class="p-5 rounded-2xl border-2 border-slate-300 bg-white shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b pb-2 border-slate-200">
                            <div>
                                <span class="font-black text-xs text-[#0A231E] tracking-wider uppercase">APOTEK OMNIPHARMA BANYUWANGI</span>
                                <span class="block text-[10px] text-slate-500">SIA: 503/SIA/35.10/2023 • SIPA APJ: 19940812/SIPA_35.10/2024/2001</span>
                            </div>
                            <span class="px-2.5 py-1 rounded text-[10px] font-black bg-slate-100 text-slate-900 border border-slate-300">
                                ETIKET PUTIH (ORAL)
                            </span>
                        </div>

                        <div class="text-xs space-y-2 bg-slate-50/50 p-4 rounded-xl border border-slate-200">
                            <div class="flex justify-between text-slate-600">
                                <span>No. Resep: <strong class="text-slate-900">#ORD-20260917-0042</strong></span>
                                <span>Tgl: <strong>17 Sep 2026</strong></span>
                            </div>
                            <div class="text-slate-900 font-extrabold text-sm">
                                Pasien: Tn. Candi Chier (88 Th / Geriatri)
                            </div>
                            
                            <!-- Box Aturan Pakai Besar -->
                            <div class="p-4 bg-white rounded-xl text-center border-2 border-emerald-600 my-2 shadow-2xs">
                                <div class="text-lg font-black text-slate-900 tracking-wide">3 x Sehari 1 Tablet</div>
                                <div class="text-xs text-emerald-800 font-bold mt-0.5">(Sewaktu / Sesudah Makan)</div>
                            </div>

                            <div class="text-[11px] text-slate-700">
                                <strong>Nama Obat:</strong> Amodiaquine 200mg (10 Tablet)
                            </div>
                            <div class="text-[10px] text-red-600 font-bold flex items-center justify-between">
                                <span>Beyond Use Date (BUD): 30 Hari sejak dibuka</span>
                                <span>Apoteker: apt. Sulistyaningrum</span>
                            </div>
                        </div>

                        <button type="button" 
                                @click="tampilkanNotif('Etiket putih oral #ORD-0042 berhasil dikirim ke printer label farmasi thermal.', 'sukses')"
                                class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Etiket Putih (Oral)</span>
                        </button>
                    </div>

                    <!-- PREVIEW ETIKET 2: ETIKET BIRU (OBAT LUAR - TIDAK BOLEH DITELAN) -->
                    <div class="p-5 rounded-2xl border-2 border-blue-400 bg-white shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b pb-2 border-blue-200">
                            <div>
                                <span class="font-black text-xs text-blue-950 tracking-wider uppercase">APOTEK OMNIPHARMA BANYUWANGI</span>
                                <span class="block text-[10px] text-slate-500">SIA: 503/SIA/35.10/2023 • SIPA APJ: 19940812/SIPA_35.10/2024/2001</span>
                            </div>
                            <span class="px-2.5 py-1 rounded text-[10px] font-black bg-blue-600 text-white">
                                ETIKET BIRU (LUAR)
                            </span>
                        </div>

                        <div class="text-xs space-y-2 bg-blue-50/50 p-4 rounded-xl border border-blue-200">
                            <!-- Banner Biru Tebal Khas Farmasi -->
                            <div class="py-1.5 px-3 bg-blue-700 text-white rounded-lg text-center font-black text-xs tracking-wider uppercase shadow-2xs">
                                OBAT LUAR — TIDAK BOLEH DITELAN
                            </div>

                            <div class="flex justify-between text-slate-600 pt-1">
                                <span>No. Resep: <strong class="text-slate-900">#ORD-20260917-0045</strong></span>
                                <span>Tgl: <strong>17 Sep 2026</strong></span>
                            </div>
                            <div class="text-slate-900 font-extrabold text-sm">
                                Pasien: Ny. Siti Rahma (54 Th)
                            </div>
                            
                            <!-- Box Aturan Pakai Biru -->
                            <div class="p-4 bg-white rounded-xl text-center border-2 border-blue-600 my-2 shadow-2xs">
                                <div class="text-lg font-black text-blue-950 tracking-wide">Oleskan Tipis pada Kulit Gatal</div>
                                <div class="text-xs text-blue-800 font-bold mt-0.5">(2 x Sehari Pagi dan Malam)</div>
                            </div>

                            <div class="text-[11px] text-slate-700">
                                <strong>Nama Racikan:</strong> Salep Hidrokortison 1% + Vaseline Album (Pot 15g)
                            </div>
                            <div class="text-[10px] text-blue-800 font-bold flex items-center justify-between">
                                <span>BUD Racikan Salep: 14 Hari sejak peracikan</span>
                                <span>Apoteker: apt. Sulistyaningrum</span>
                            </div>
                        </div>

                        <button type="button" 
                                @click="tampilkanNotif('Etiket biru obat luar #ORD-0045 berhasil dikirim ke printer label farmasi thermal.', 'sukses')"
                                class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Etiket Biru (Obat Luar)</span>
                        </button>
                    </div>
                </div>

                <!-- Modul Panduan PIO (Pemberian Informasi Obat) Klinis Resmi APJ -->
                <div class="p-5 rounded-2xl border border-emerald-200 bg-emerald-50/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-emerald-700 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <h3 class="font-black text-xs text-emerald-950 uppercase tracking-wide">Lembar Konseling PIO (Pemberian Informasi Obat) Pasien Geriatri:</h3>
                        </div>
                        <button type="button" 
                                @click="tampilkanNotif('Lembar konseling PIO klinis dicetak untuk diserahkan ke keluarga pasien.', 'sukses')"
                                class="px-3 py-1 rounded-lg bg-emerald-700 text-white font-bold text-xs hover:bg-emerald-800 transition">
                            Cetak Lembar PIO Pasien
                        </button>
                    </div>

                    <ul class="text-xs text-slate-700 space-y-2 list-disc pl-5 leading-relaxed">
                        <li><strong>Cara Minum:</strong> Telan obat utuh dengan segelas air mineral hangat sewaktu sarapan/makan untuk meminimalisasi iritasi lambung.</li>
                        <li><strong>Kepatuhan Regimen:</strong> Minum obat tepat pada jam yang sama setiap 8 jam selama 3 hari berturut-turut hingga seluruh 10 tablet habis.</li>
                        <li><strong>Pengawasan Gejala Efek Samping:</strong> Pada geriatri, apabila timbul rasa lemas berlebih atau mual berkepanjangan, segera hubungi kontak WhatsApp farmasi cabang apotek.</li>
                        <li><strong>Penyimpanan:</strong> Simpan obat di tempat sejuk kering suhu ruang di bawah 30°C, terlindung dari paparan cahaya matahari langsung.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- KONTEN TAB 4 APOTEKER: PENERIMAAN FAKTUR PBF RESMI & COLD CHAIN        -->
        <!-- ===================================================================== -->
        <div x-show="apotekerTab === 'faktur_pbf'" :class="apotekerTab === 'faktur_pbf' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Penerimaan Faktur Pedagang Besar Farmasi (PBF) &amp; Cold Chain</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pemeriksaan fisik barang datang, nomor izin edar BPOM, batch, tanggal kedaluwarsa, dan suhu rantai dingin (2°C - 8°C) standar CDOB Kemenkes RI.</p>
                    </div>
                    <button type="button" 
                            @click="tampilkanNotif('Sinkronisasi data faktur distributor PBF Kemenkes selesai.', 'sukses')"
                            class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Sinkronkan Faktur PBF
                    </button>
                </div>

                <!-- DAFTAR FAKTUR PBF MASUK -->
                <div class="space-y-4">
                    <!-- Faktur 1: PBF Kimia Farma (Obat Jadi & Bahan Dasar Salep) -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b pb-2 border-slate-200">
                            <div>
                                <span class="font-extrabold text-slate-900 text-sm">PT Kimia Farma Trading &amp; Distribution</span>
                                <span class="block text-[11px] text-slate-500 font-mono">Faktur: #FAK-KF-2026-9901 • SP Resmi: #SP-REG-0941</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 w-fit">
                                ✓ Selesai Diterima (Lolos Cek Fisik APJ)
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Item Diterima</span>
                                <strong class="text-slate-800">Amodiaquine 200mg (5 Box @ 100 Tab)</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Bahan Tambahan</span>
                                <strong class="text-slate-800">Vaseline Album USP (4 Pot @ 1.000g)</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Tgl. Terima &amp; Verifikator</span>
                                <span class="text-slate-700">16 Sep 2026 • apt. Sulistyaningrum</span>
                            </div>
                        </div>
                    </div>

                    <!-- Faktur 2: PBF Mensa Binasukses (Bahan Baku Racikan Murni + COA) -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b pb-2 border-slate-200">
                            <div>
                                <span class="font-extrabold text-slate-900 text-sm">PT Mensa Binasukses (Distributor Bahan Farmasetis)</span>
                                <span class="block text-[11px] text-slate-500 font-mono">Faktur: #FAK-MBS-2026-4412 • Dilengkapi COA Resmi Pabrik</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-purple-100 text-purple-800 border border-purple-300 w-fit">
                                ✓ Terverifikasi (Sertifikat Analisis Valid)
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Item Diterima</span>
                                <strong class="text-slate-800">Paracetamol Pure Powder (5.000g Drum)</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Bahan Kortikosteroid</span>
                                <strong class="text-slate-800">Dexamethasone Powder BP (500g Botol)</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Pemeriksaan Mutu</span>
                                <span class="text-purple-900 font-semibold">Kadar 99.8% sesuai Farmakope Indonesia</span>
                            </div>
                        </div>
                    </div>

                    <!-- Faktur 3: PBF Enseval Putera Megatrading (COLD CHAIN 2-8°C INSULIN & VAKSIN) -->
                    <div class="p-4 rounded-2xl border-2 border-cyan-400 bg-cyan-50/30 space-y-3 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b pb-2 border-cyan-200">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-cyan-600 text-white">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="font-extrabold text-slate-900 text-sm">PT Enseval Putera Megatrading (PRODUK COLD CHAIN 2°C - 8°C)</span>
                                    <span class="block text-[11px] text-slate-500 font-mono">Faktur: #FAK-ENS-2026-1188 • Cool Box Ice Pack Tersegel</span>
                                </div>
                            </div>

                            <div>
                                <span x-show="selectedColdChain.statusVerifikasi === 'menunggu'" class="px-3 py-1 rounded-full text-[11px] font-black bg-cyan-100 text-cyan-900 border border-cyan-300">
                                    Menunggu Verifikasi Suhu APJ
                                </span>
                                <span x-show="selectedColdChain.statusVerifikasi === 'diverifikasi'" class="px-3 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    ✓ Suhu 4.2°C Lolos Verifikasi Cold Chain
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Produk Rantai Dingin</span>
                                <strong class="text-slate-800">Insulin Glargine 100 IU/mL (10 Pen)</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Data Logger Suhu Tiba</span>
                                <strong class="text-cyan-700 font-mono text-sm">4.2°C (Rentang Aman 2°C s/d 8°C)</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Kondisi Fisik Pengiriman</span>
                                <span class="text-emerald-700 font-semibold">Ice Gel Masuk Beku • Segel Termo Utuh</span>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="button" 
                                    @click="modalColdChain = true"
                                    class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-black text-xs transition shadow-xs flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Cek Data Logger &amp; ACC Suhu Cold Chain</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- KONTEN TAB 5 APOTEKER: REGISTER NARKOTIKA & SIPNAP BPOM                -->
        <!-- ===================================================================== -->
        <div x-show="apotekerTab === 'sipnap'" :class="apotekerTab === 'sipnap' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Buku Register Narkotika, Psikotropika &amp; SIPNAP BPOM</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pencatatan mutasi sediaan khusus sesuai UU No. 35/2009 dan Permenkes RI No. 3/2015. Wajib disimpan di lemari ganda dengan kunci dipegang APJ.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="tampilkanNotif('File laporan SIPNAP September 2026 diexport ke format resmi Excel Kemenkes.', 'sukses')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Export SIPNAP (.xlsx)</span>
                        </button>
                    </div>
                </div>

                <!-- Info Lemari Dobel Kunci APJ -->
                <div class="p-4 rounded-xl bg-[#0A231E] text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 rounded-xl bg-emerald-600 text-white shadow-xs">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <div>
                            <div class="font-extrabold text-sm">Status Lemari Penyimpanan Narkotika: Terkunci Ganda</div>
                            <div class="text-xs text-emerald-200/90">Kunci Utama: apt. Sulistyaningrum, S.Farm (APJ) • Kunci Sekunder: Apoteker Pendamping</div>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold px-3 py-1 rounded-lg bg-emerald-800 text-emerald-100 border border-emerald-600/50">
                        Periode: September 2026
                    </span>
                </div>

                <!-- TABEL REGISTER NARKOTIKA & PSIKOTROPIKA -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Nama Sediaan</th>
                                <th class="px-4 py-3">Golongan</th>
                                <th class="px-4 py-3 text-center">Saldo Awal</th>
                                <th class="px-4 py-3 text-center">Pemasukan (SP)</th>
                                <th class="px-4 py-3 text-center">Pengeluaran Resep</th>
                                <th class="px-4 py-3 text-center">Saldo Akhir Sistem</th>
                                <th class="px-4 py-3 text-center">Fisik Lemari</th>
                                <th class="px-4 py-3 text-right">Status Audit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Codein HCl 10mg Tablet
                                    <span class="block text-[10px] text-slate-400 font-normal">Antitusif Narkotika • PBF Kimia Farma</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-rose-100 text-rose-800 text-[10px]">NARKOTIKA GOL III</span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-slate-800">150 Tab</td>
                                <td class="px-4 py-3.5 text-center text-slate-500 font-mono">0</td>
                                <td class="px-4 py-3.5 text-center font-bold text-rose-600 font-mono">10 Tab (Resep dr. Budi)</td>
                                <td class="px-4 py-3.5 text-center font-black text-slate-900 font-mono text-sm">140 Tab</td>
                                <td class="px-4 py-3.5 text-center font-black text-emerald-700 font-mono text-sm">140 Tab</td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-black text-[10px] bg-emerald-100 text-emerald-800">
                                        ✓ 100% KLOP
                                    </span>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Diazepam 2mg Tablet
                                    <span class="block text-[10px] text-slate-400 font-normal">Ansiolitik / Penenang • PBF Kimia Farma</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-indigo-100 text-indigo-800 text-[10px]">PSIKOTROPIKA GOL IV</span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-slate-800">80 Tab</td>
                                <td class="px-4 py-3.5 text-center text-emerald-700 font-bold font-mono">+50 Tab (SP-PSI-021)</td>
                                <td class="px-4 py-3.5 text-center font-bold text-rose-600 font-mono">20 Tab</td>
                                <td class="px-4 py-3.5 text-center font-black text-slate-900 font-mono text-sm">110 Tab</td>
                                <td class="px-4 py-3.5 text-center font-black text-emerald-700 font-mono text-sm">110 Tab</td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-black text-[10px] bg-emerald-100 text-emerald-800">
                                        ✓ 100% KLOP
                                    </span>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    Fentanyl 25 mcg/jam Patch
                                    <span class="block text-[10px] text-slate-400 font-normal">Analgesik Narkotika Kuat Pasien Kanker</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded font-black bg-rose-100 text-rose-800 text-[10px]">NARKOTIKA GOL II</span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-slate-800">5 Patch</td>
                                <td class="px-4 py-3.5 text-center text-slate-500 font-mono">0</td>
                                <td class="px-4 py-3.5 text-center text-slate-500 font-mono">0</td>
                                <td class="px-4 py-3.5 text-center font-black text-slate-900 font-mono text-sm">5 Patch</td>
                                <td class="px-4 py-3.5 text-center font-black text-emerald-700 font-mono text-sm">5 Patch</td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-black text-[10px] bg-emerald-100 text-emerald-800">
                                        ✓ 100% KLOP
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Aksi Kepatuhan SIPNAP -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                    <span class="text-slate-500">
                        Batas pelaporan SIPNAP: Maksimal tanggal 10 bulan berikutnya melalui portal <a href="https://sipnap.kemkes.go.id" target="_blank" class="text-emerald-700 underline font-bold">sipnap.kemkes.go.id</a>.
                    </span>
                    <button type="button" 
                            @click="tampilkanNotif('Sinkronisasi API SIPNAP Kemenkes berhasil. Token enkripsi tersambung.', 'sukses')"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs transition">
                        Sinkronkan API SIPNAP Kemenkes
                    </button>
                </div>
            </div>
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- DASHBOARD VIEW 3: USER / PELANGGAN (Katalog, Beli Obat, Upload Resep)     -->
    <!-- ========================================================================= -->
    <main x-show="currentRole === 'pelanggan'" 
          :class="currentRole === 'pelanggan' ? 'block' : 'hidden'" 
          class="{{ $activeRole === 'pelanggan' ? 'block' : 'hidden' }} flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        
        <!-- HERO PELANGGAN: Beli Obat & Unggah Resep Dokter -->
        <section class="rounded-3xl bg-gradient-to-br from-[#0A231E] via-[#0E352E] to-[#124B40] text-white p-6 sm:p-8 shadow-xl border border-emerald-500/20 relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-3 max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-600/30 text-emerald-200 border border-emerald-500/40 text-xs font-extrabold tracking-wide uppercase">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Portal Pasien &amp; Pelanggan Resmi
                        </span>
                        <span class="text-xs font-semibold text-emerald-100 bg-white/10 px-3 py-1 rounded-full border border-white/15">
                            Apotek Terdekat: Cabang Banyuwangi Kota (1.2 km • Buka 24 Jam) &amp; Cabang Rogojampi (14 km)
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                        Layanan Pemesanan Obat Resmi &amp; Tebus Resep Dokter
                    </h1>
                    <p class="text-sm text-slate-200 leading-relaxed">
                        Unggah foto lembar resep dokter Anda untuk diverifikasi langsung oleh Apoteker Penanggung Jawab (APJ), pesan vitamin dan obat bebas berizin BPOM tanpa antre, serta pantau jadwal minum obat keluarga secara real-time.
                    </p>
                </div>

                <!-- Tombol Aksi Cepat Pelanggan -->
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <button type="button" 
                            @click="pelangganTab = 'upload_resep'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md transition min-h-[44px]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>+ Tebus Resep Dokter</span>
                    </button>

                    <button type="button"
                            @click="pelangganTab = 'katalog_beli'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition min-h-[44px]">
                        <svg class="h-4 w-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Beli Obat Bebas &amp; Vitamin</span>
                    </button>

                    <button type="button"
                            @click="pelangganTab = 'konsultasi_chat'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#0E352E] hover:bg-[#14443b] text-emerald-100 font-bold text-xs border border-emerald-500/30 transition min-h-[44px]">
                        <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span>Tanya Apoteker Cabang</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- 4 KPI CARDS: PELANGGAN -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Status Resep Aktif -->
            <div @click="pelangganTab = 'upload_resep'" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-500/50 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Resep Sedang Ditelaah</span>
                    <span class="p-2 rounded-xl bg-amber-50 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">1 Resep Aktif</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>#ORD-20260917-0042 (Telaah APJ)</span>
                    </div>
                </div>
            </div>

            <!-- Pesanan Obat Selesai -->
            <div @click="pelangganTab = 'riwayat_resep'" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-500/50 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Riwayat Pembelian</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">5 Pesanan Selesai</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-emerald-700">
                        <span>100% Produk Farmasi Asli &amp; Salinan Resep</span>
                    </div>
                </div>
            </div>

            <!-- Pengingat Minum Obat Hari Ini -->
            <div @click="pelangganTab = 'jadwal_obat'" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-500/50 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jadwal Minum Obat</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900 tracking-tight">3 Jadwal Hari Ini</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-emerald-700">
                        <span x-show="dosisSiangDiminum">Pagi ✓ • Siang ✓ • Malam</span>
                        <span x-show="!dosisSiangDiminum">Pagi ✓ • Siang [Waktunya Minum!] • Malam</span>
                    </div>
                </div>
            </div>

            <!-- Cabang Langganan & Apoteker Online -->
            <div @click="pelangganTab = 'konsultasi_chat'" class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-500/50 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Apoteker Cabang</span>
                    <span class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-xl font-black text-slate-900 tracking-tight">apt. Siti Nurhaliza</div>
                    <div class="flex items-center gap-1.5 mt-1 text-xs font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Online di Cabang Banyuwangi Kota</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- SUB-NAVIGASI TAB KHUSUS PELANGGAN -->
        <section class="border-b border-slate-200 bg-white rounded-2xl p-2 shadow-xs flex flex-wrap gap-1">
            <button type="button" 
                    @click="pelangganTab = 'upload_resep'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="pelangganTab === 'upload_resep' ? 'bg-[#0A231E] text-white' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Tebus Resep Dokter &amp; Lacak Status</span>
            </button>

            <button type="button" 
                    @click="pelangganTab = 'katalog_beli'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="pelangganTab === 'katalog_beli' ? 'bg-[#0A231E] text-white' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span>Beli Obat Bebas &amp; Vitamin</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                      :class="pelangganTab === 'katalog_beli' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700'"
                      x-text="keranjangPelanggan.length"></span>
            </button>

            <button type="button" 
                    @click="pelangganTab = 'jadwal_obat'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="pelangganTab === 'jadwal_obat' ? 'bg-[#0A231E] text-white' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Jadwal Pengingat Minum Obat</span>
            </button>

            <button type="button" 
                    @click="pelangganTab = 'riwayat_resep'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="pelangganTab === 'riwayat_resep' ? 'bg-[#0A231E] text-white' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Riwayat Resep &amp; Salinan Kwitansi</span>
            </button>

            <button type="button" 
                    @click="pelangganTab = 'konsultasi_chat'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"
                    :class="pelangganTab === 'konsultasi_chat' ? 'bg-[#0A231E] text-white' : 'text-slate-600 hover:bg-slate-100'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span>Tanya Apoteker Cabang</span>
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </button>
        </section>

        <!-- KONTEN TAB 1 PELANGGAN: UNGGAH RESEP & LACAK STATUS -->
        <div x-show="pelangganTab === 'upload_resep'" :class="pelangganTab === 'upload_resep' ? 'block' : 'hidden'" class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- FORM UNGGAH RESEP -->
                <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-900">Form Tebus Resep Dokter Online</h2>
                            <p class="text-[11px] text-slate-500">Unggah foto resep asli untuk ditelaah Apoteker Penanggung Jawab.</p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 text-xs text-emerald-900 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 text-emerald-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Jaminan Keaslian &amp; Legalitas Farmasi</span>
                        </div>
                        <p class="text-[11px] text-emerald-700 leading-relaxed">
                            Resep dokter akan ditelaah kesesuaian dosis, indikasi klinis, dan riwayat alergi obat sesuai Standar Pelayanan Kefarmasian Permenkes No. 73/2016.
                        </p>
                    </div>

                    <form @submit.prevent="tampilkanNotif('Resep dokter Anda berhasil dikirim ke Apoteker Penanggung Jawab! Kami sedang menelaah resep Anda.', 'sukses')" class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Lengkap Pasien</label>
                            <input type="text" value="{{ $userName }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-900">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Usia Pasien</label>
                                <input type="text" value="34 Tahun" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                                <select class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                                    <option>Laki-laki</option>
                                    <option>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Dokter Peresep &amp; RS / Klinik</label>
                            <input type="text" placeholder="Contoh: dr. Budi, Sp.PD (RSUD Blambangan)" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Pilih Cabang Apotek Penyiapan</label>
                            <select class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-medium">
                                <option>Apotek OmniPharma Cabang Banyuwangi Kota (1.2 km - Buka 24 Jam)</option>
                                <option>Apotek OmniPharma Cabang Rogojampi (14 km - Buka 07:00 - 22:00)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Metode Pengambilan Obat</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:border-emerald-500 cursor-pointer bg-slate-50">
                                    <input type="radio" name="metode_kirim" value="ambil" checked class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-bold text-slate-800 text-[11px]">Ambil di Apotek</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:border-emerald-500 cursor-pointer bg-slate-50">
                                    <input type="radio" name="metode_kirim" value="antar" class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-bold text-slate-800 text-[11px]">Diantar Kurir</span>
                                </label>
                            </div>
                        </div>

                        <!-- Dropzone Foto Resep -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Unggah Foto / Scan Lembar Resep</label>
                            <div class="border-2 border-dashed border-emerald-300 rounded-2xl p-4 text-center bg-emerald-50/40 hover:bg-emerald-50/80 transition cursor-pointer">
                                <svg class="h-8 w-8 text-emerald-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="block text-xs font-bold text-slate-700 mt-2">Ambil Foto / Pilih File Resep</span>
                                <span class="block text-[10px] text-slate-500">Format JPG, PNG, atau PDF (Maks. 5 MB)</span>
                                <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-emerald-200 text-[10px] text-emerald-800 font-semibold">
                                    <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Contoh_Resep_drBudi_SpPD.jpg (1.8 MB)</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Riwayat Alergi Obat / Keluhan Pasien</label>
                            <textarea rows="2" placeholder="Contoh: Alergi penisilin, minta sediaan sirup bila memungkinkan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                        </div>

                        <button type="submit" 
                                class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                            <span>Kirim Resep ke Apoteker APJ</span>
                        </button>
                    </form>
                </div>

                <!-- LIVE TRACKING STATUS RESEP AKTIF -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-900">Pelacakan Live Status Resep Terkini</h2>
                            <p class="text-[11px] text-slate-500">Pantau proses peracikan dan telaah apoteker secara real-time.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                No. Order: #ORD-20260917-0042
                            </span>
                        </div>
                    </div>

                    <!-- Step Tracker 4 Tahap Visual -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="grid grid-cols-4 gap-2 text-center text-xs relative">
                            <!-- Tahap 1 -->
                            <div class="space-y-1.5">
                                <div class="h-9 w-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold mx-auto text-xs shadow-xs">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <span class="font-extrabold text-slate-900 block text-[11px]">1. Resep Terkirim</span>
                                <span class="text-[10px] text-slate-500 font-medium">09:30 WIB</span>
                            </div>

                            <!-- Tahap 2 (Active) -->
                            <div class="space-y-1.5">
                                <div class="h-9 w-9 rounded-full bg-[#0A231E] text-emerald-300 ring-4 ring-emerald-200 flex items-center justify-center font-black mx-auto text-xs animate-pulse">
                                    2
                                </div>
                                <span class="font-extrabold text-emerald-800 block text-[11px]">2. Telaah APJ</span>
                                <span class="text-[10px] text-emerald-700 font-bold bg-emerald-100 px-2 py-0.5 rounded-full inline-block">Sedang Ditelaah</span>
                            </div>

                            <!-- Tahap 3 -->
                            <div class="space-y-1.5 opacity-60">
                                <div class="h-9 w-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold mx-auto text-xs">
                                    3
                                </div>
                                <span class="font-semibold text-slate-600 block text-[11px]">3. Diracik &amp; Etiket</span>
                                <span class="text-[10px] text-slate-400">Pemberian Aturan</span>
                            </div>

                            <!-- Tahap 4 -->
                            <div class="space-y-1.5 opacity-60">
                                <div class="h-9 w-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold mx-auto text-xs">
                                    4
                                </div>
                                <span class="font-semibold text-slate-600 block text-[11px]">4. Siap Diambil</span>
                                <span class="text-[10px] text-slate-400">Estimasi 10:15 WIB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Resep & Farmasi -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                            <span class="text-slate-500 font-medium">Obat yang Sedang Disiapkan:</span>
                            <span class="font-extrabold text-slate-900 text-sm">Amodiaquine 200mg (10 Tablet)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Diagnosa Dokter:</span>
                            <span class="font-bold text-slate-800">Malaria Falciparum Tanpa Komplikasi</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Dokter Peresep:</span>
                            <span class="font-semibold text-slate-800">dr. Budi, Sp.PD (SIP: 503/SIP/2024 - RSUD Blambangan)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Apoteker Penelaah (APJ):</span>
                            <span class="font-semibold text-emerald-800">apt. Siti Nurhaliza, S.Farm (SIPA: 19890412/SIPA_35.10/2021)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Aturan Pakai Resmi (Signa):</span>
                            <span class="font-bold text-slate-900 bg-white px-2.5 py-1 rounded-lg border border-slate-200">S 3 dd tab I d.c (Sehari 3x1 tablet sesudah makan)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Cabang Penyiapan:</span>
                            <span class="font-semibold text-slate-800">OmniPharma Cabang Banyuwangi Kota (1.2 km)</span>
                        </div>
                        <div class="flex justify-between items-center pt-2.5 border-t border-slate-200">
                            <div>
                                <span class="text-slate-700 font-bold block">Estimasi Total Biaya Obat:</span>
                                <span class="text-[11px] text-slate-500">Sudah termasuk jasa telaah apoteker &amp; kemasan bersegel</span>
                            </div>
                            <span class="text-emerald-700 font-black text-base">Rp 45.000</span>
                        </div>
                    </div>

                    <!-- Aksi Cepat Pesanan -->
                    <div class="flex flex-wrap items-center justify-end gap-2 pt-2">
                        <button type="button" 
                                @click="modalCopyResep = true"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center gap-1.5 transition">
                            <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span>Lihat Salinan Resep (Copy Resep)</span>
                        </button>

                        <button type="button" 
                                @click="pelangganTab = 'konsultasi_chat'"
                                class="px-3.5 py-2 rounded-xl bg-[#0A231E] hover:bg-[#133c34] text-white font-bold text-xs flex items-center gap-1.5 transition shadow-xs">
                            <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            <span>Tanya Apoteker Soal Resep Ini</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- KONTEN TAB 2 PELANGGAN: BELI OBAT BEBAS & VITAMIN -->
        <div x-show="pelangganTab === 'katalog_beli'" :class="pelangganTab === 'katalog_beli' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Katalog Obat Bebas, Vitamin &amp; P3K Resmi</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Dapat dibeli langsung tanpa perlu resep dokter, 100% asli dari distributor resmi Pedagang Besar Farmasi (PBF).</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input type="text" 
                                   x-model="searchObatBebas" 
                                   placeholder="Cari obat bebas, vitamin, P3K..." 
                                   class="pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 w-56">
                            <svg class="h-4 w-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Sub-Filter Kategori Obat Bebas -->
                <div class="flex flex-wrap gap-2 text-xs">
                    <button type="button" 
                            @click="kategoriObatBebas = 'semua'"
                            class="px-3 py-1.5 rounded-xl font-bold transition"
                            :class="kategoriObatBebas === 'semua' ? 'bg-[#0A231E] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                        Semua Produk
                    </button>
                    <button type="button" 
                            @click="kategoriObatBebas = 'demam_nyeri'"
                            class="px-3 py-1.5 rounded-xl font-bold transition"
                            :class="kategoriObatBebas === 'demam_nyeri' ? 'bg-[#0A231E] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                        Demam &amp; Nyeri
                    </button>
                    <button type="button" 
                            @click="kategoriObatBebas = 'lambung'"
                            class="px-3 py-1.5 rounded-xl font-bold transition"
                            :class="kategoriObatBebas === 'lambung' ? 'bg-[#0A231E] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                        Lambung &amp; Pencernaan
                    </button>
                    <button type="button" 
                            @click="kategoriObatBebas = 'vitamin'"
                            class="px-3 py-1.5 rounded-xl font-bold transition"
                            :class="kategoriObatBebas === 'vitamin' ? 'bg-[#0A231E] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                        Vitamin &amp; Imunitas
                    </button>
                    <button type="button" 
                            @click="kategoriObatBebas = 'p3k'"
                            class="px-3 py-1.5 rounded-xl font-bold transition"
                            :class="kategoriObatBebas === 'p3k' ? 'bg-[#0A231E] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                        Kotak P3K &amp; Pertolongan Pertama
                    </button>
                </div>

                <!-- Grid Produk Obat Bebas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Produk 1: Paracetamol -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-md transition space-y-2 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800">OBAT BEBAS</span>
                                <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Stok Tersedia
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-900">Paracetamol 500mg Kaplet</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">Meredakan sakit kepala, sakit gigi, demam pasca vaksinasi, dan nyeri ringan.</p>
                            <div class="text-[11px] text-slate-400">BPOM: DBL1234567801A1 • Strip 10 Kaplet</div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga Resmi:</span>
                                <span class="text-emerald-700 font-black text-sm">Rp 8.500</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="tambahKeranjang(1, 'Paracetamol 500mg Kaplet', 8500, 'Strip 10 Kaplet')"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    + Keranjang
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Produk 2: Antasida Doen -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-md transition space-y-2 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-800">BEBAS TERBATAS</span>
                                <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Stok Tersedia
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-900">Antasida Doen Tablet Kunyah</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">Menetralkan asam lambung berlebih, mengatasi sakit maag, nyeri ulu hati, dan kembung begah.</p>
                            <div class="text-[11px] text-slate-400">BPOM: DBL9876543210A1 • Strip 10 Tablet</div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga Resmi:</span>
                                <span class="text-emerald-700 font-black text-sm">Rp 6.000</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="tambahKeranjang(2, 'Antasida Doen Tablet Kunyah', 6000, 'Strip 10 Tablet')"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    + Keranjang
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Produk 3: Vitamin C 500mg -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-md transition space-y-2 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800">SUPLEMEN / VITAMIN</span>
                                <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Stok Tersedia
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-900">Vitamin C 500mg IPI Strip</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">Membantu memelihara daya tahan tubuh, antioksidan harian, dan mempercepat pemulihan stamina.</p>
                            <div class="text-[11px] text-slate-400">BPOM: SD123456789 • Strip 10 Tablet</div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga Resmi:</span>
                                <span class="text-emerald-700 font-black text-sm">Rp 12.000</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="tambahKeranjang(3, 'Vitamin C 500mg IPI Strip', 12000, 'Strip 10 Tablet')"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    + Keranjang
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Produk 4: Betadine Antiseptik -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-md transition space-y-2 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800">P3K &amp; ANTISEPTIK</span>
                                <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Stok Tersedia
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-900">Betadine Antiseptic Solution 30ml</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">Povidone Iodine 10% untuk mencegah infeksi pada luka gores, luka bakar ringan, dan lecet.</p>
                            <div class="text-[11px] text-slate-400">Kemenkes RI PKD • Botol 30ml</div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga Resmi:</span>
                                <span class="text-emerald-700 font-black text-sm">Rp 21.000</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="tambahKeranjang(4, 'Betadine Antiseptic Solution 30ml', 21000, 'Botol 30ml')"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    + Keranjang
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Produk 5: Minyak Kayu Putih -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-md transition space-y-2 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800">HERBAL ALAMI</span>
                                <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Stok Tersedia
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-900">Minyak Kayu Putih Cajuput 60ml</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">Menghangatkan badan, meredakan gejala masuk angin, perut kembung, dan gatal gigitan serangga.</p>
                            <div class="text-[11px] text-slate-400">POM TR182618991 • Botol 60ml</div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga Resmi:</span>
                                <span class="text-emerald-700 font-black text-sm">Rp 24.000</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="tambahKeranjang(5, 'Minyak Kayu Putih Cajuput 60ml', 24000, 'Botol 60ml')"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    + Keranjang
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Produk 6: Termometer Digital -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-md transition space-y-2 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-100 text-purple-800">ALAT KESEHATAN</span>
                                <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Stok Tersedia
                                </span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-900">Termometer Digital Cepat &amp; Akurat</h3>
                            <p class="text-xs text-slate-500 line-clamp-2">Pengukur suhu tubuh digital cepat dengan bunyi bip penanda dan layar LCD yang jelas untuk keluarga.</p>
                            <div class="text-[11px] text-slate-400">Kemenkes AKL • Unit Box Bergaransi</div>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-3">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga Resmi:</span>
                                <span class="text-emerald-700 font-black text-sm">Rp 35.000</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="tambahKeranjang(6, 'Termometer Digital Cepat & Akurat', 35000, 'Unit Box Bergaransi')"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    + Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RINGKASAN KERANJANG OBAT PELANGGAN (Interactive Mini Cart) -->
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-emerald-200">
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-xl bg-emerald-600 text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-black text-slate-900">Keranjang Belanja Obat Pelanggan</h3>
                                <p class="text-[11px] text-slate-500">Pemesanan obat bebas &amp; vitamin siap disiapkan apotek.</p>
                            </div>
                        </div>
                        <div class="text-xs">
                            <span class="text-slate-600">Total Belanja: </span>
                            <span class="font-black text-emerald-800 text-base" x-text="'Rp ' + totalKeranjang().toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <!-- List item di keranjang -->
                    <template x-if="keranjangPelanggan.length === 0">
                        <div class="text-center py-4 text-xs text-slate-500">
                            Keranjang belanja obat Anda masih kosong. Silakan pilih obat di etalase di atas.
                        </div>
                    </template>

                    <div class="space-y-2">
                        <template x-for="(item, index) in keranjangPelanggan" :key="item.id">
                            <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 text-xs">
                                <div>
                                    <div class="font-bold text-slate-900" x-text="item.nama"></div>
                                    <div class="text-[11px] text-slate-500" x-text="item.kemasan + ' • ' + item.qty + ' pcs'"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="font-black text-emerald-700" x-text="'Rp ' + (item.harga * item.qty).toLocaleString('id-ID')"></div>
                                    <button type="button" 
                                            @click="hapusKeranjang(index)"
                                            class="text-red-500 hover:text-red-700 text-xs font-bold"
                                            title="Hapus item">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Tombol Checkout -->
                    <div class="flex justify-end pt-2">
                        <button type="button" 
                                @click="tampilkanNotif('Pesanan obat bebas Anda berhasil diproses ke Kasir Apotek OmniPharma. Siap diambil atau diantar!', 'sukses')"
                                :disabled="keranjangPelanggan.length === 0"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs shadow-md transition flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span>Lanjut ke Kasir / Checkout Pembelian Obat</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- KONTEN TAB 3 PELANGGAN: JADWAL PENGINGAT MINUM OBAT -->
        <div x-show="pelangganTab === 'jadwal_obat'" :class="pelangganTab === 'jadwal_obat' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Jadwal Pengingat Minum Obat Pasien (Medication Tracker)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pantau kepatuhan terapi obat harian keluarga sesuai aturan resmi Apoteker Penanggung Jawab.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="modalTambahJadwal = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>+ Tambah Jadwal Obat</span>
                        </button>
                    </div>
                </div>

                <!-- Banner Kepatuhan Obat (Adherence) -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-emerald-100/40 to-slate-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                            95%
                        </div>
                        <div>
                            <div class="text-xs font-black text-emerald-950 uppercase tracking-wide">Kepatuhan Terapi Obat Pasien: Sangat Baik &amp; Disiplin</div>
                            <p class="text-[11px] text-slate-600 leading-snug mt-0.5">
                                Minum obat sesuai waktu anjuran apoteker sangat penting untuk mencegah resistensi obat dan mempercepat pemulihan kesehatan Anda.
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[11px] text-slate-500 block">Jadwal Hari Ini:</span>
                        <span class="text-xs font-extrabold text-emerald-800">2 dari 3 Dosis Terpenuhi</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- Dosis Pagi -->
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">✓</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Pukul 07:00 WIB (Pagi) • Amodiaquine 200mg (1 Tablet)</div>
                                <div class="text-[11px] text-slate-600">Aturan: 1 tablet sesudah sarapan pagi • Status: <strong class="text-emerald-700 font-bold">Sudah Diminum (07:15 WIB)</strong></div>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full">Selesai</span>
                    </div>

                    <!-- Dosis Siang (Interactive) -->
                    <div class="p-4 rounded-xl border transition"
                         :class="dosisSiangDiminum ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/40'">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="h-8 w-8 rounded-full flex items-center justify-center font-bold text-xs text-white"
                                      :class="dosisSiangDiminum ? 'bg-emerald-600' : 'bg-amber-500 animate-pulse'">
                                    <span x-show="!dosisSiangDiminum">!</span>
                                    <span x-show="dosisSiangDiminum">✓</span>
                                </span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Pukul 13:00 WIB (Siang) • Amodiaquine 200mg (1 Tablet)</div>
                                    <div class="text-[11px] text-slate-600">
                                        Aturan: 1 tablet sesudah makan siang • Status: 
                                        <template x-if="!dosisSiangDiminum">
                                            <strong class="text-amber-800 font-bold">Waktunya Minum Obat Sekarang!</strong>
                                        </template>
                                        <template x-if="dosisSiangDiminum">
                                            <strong class="text-emerald-700 font-bold">Sudah Diminum (13:05 WIB)</strong>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <template x-if="!dosisSiangDiminum">
                                    <button type="button" 
                                            @click="dosisSiangDiminum = true; tampilkanNotif('Alhamdulillah! Dosis siang berhasil dicatat sudah diminum.', 'sukses')"
                                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-xs transition flex items-center gap-1.5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Tandai Sudah Minum</span>
                                    </button>
                                </template>
                                <template x-if="dosisSiangDiminum">
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">Selesai</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Dosis Malam -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between opacity-80">
                        <div class="flex items-center gap-3">
                            <span class="h-8 w-8 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center font-bold text-xs">3</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Pukul 20:00 WIB (Malam) • Amodiaquine 200mg + Vitamin C 500mg</div>
                                <div class="text-[11px] text-slate-600">Aturan: 1 tablet sesudah makan malam • Menunggu waktu dosis malam</div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">Malam Nanti (20:00 WIB)</span>
                    </div>
                </div>

                <!-- Pesan Edukasi Apoteker -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-start gap-2.5">
                    <svg class="h-5 w-5 text-emerald-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    <div>
                        <strong class="text-slate-800">Catatan Apoteker Penanggung Jawab (APJ):</strong> Hindari mengonsumsi obat antibiotik atau antimalaria bersama susu atau teh kental karena dapat mengurangi penyerapan zat aktif obat di saluran cerna.
                    </div>
                </div>
            </div>
        </div>

        <!-- KONTEN TAB 4 PELANGGAN: RIWAYAT TRANSAKSI & RESEP -->
        <div x-show="pelangganTab === 'riwayat_resep'" :class="pelangganTab === 'riwayat_resep' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Riwayat Resep &amp; Salinan Kwitansi Resmi Apotek</h2>
                        <p class="text-xs text-slate-500">Daftar resep dokter dan pembelian obat bebas yang pernah Anda tebus di OmniPharma.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">No. Transaksi / Resep</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Daftar Obat</th>
                                <th class="px-4 py-3">Cabang Apotek</th>
                                <th class="px-4 py-3">Total Biaya</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Transaksi 1 -->
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 font-bold text-slate-900">#ORD-20260917-0042</td>
                                <td class="px-4 py-3 text-slate-500">17 Sep 2026</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">Amodiaquine 200mg (10 Tab) • Resep dr. Budi, Sp.PD</td>
                                <td class="px-4 py-3">Cabang Banyuwangi Kota</td>
                                <td class="px-4 py-3 font-bold text-emerald-700">Rp 45.000</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900">Diproses APJ</span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <button type="button" @click="modalCopyResep = true" class="text-emerald-700 hover:text-emerald-900 font-bold hover:underline">Copy Resep</button>
                                    <span class="text-slate-300">•</span>
                                    <button type="button" @click="tampilkanNotif('Kwitansi resmi apotek diunduh dalam format PDF.', 'sukses')" class="text-slate-600 hover:text-slate-900 font-medium">Kwitansi</button>
                                </td>
                            </tr>

                            <!-- Transaksi 2 -->
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 font-bold text-slate-900">#ORD-20260905-0018</td>
                                <td class="px-4 py-3 text-slate-500">05 Sep 2026</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">Paracetamol 500mg (1 Strip) + Vitamin C (1 Strip)</td>
                                <td class="px-4 py-3">Cabang Banyuwangi Kota</td>
                                <td class="px-4 py-3 font-bold text-emerald-700">Rp 20.500</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" @click="tampilkanNotif('Kwitansi resmi apotek diunduh dalam format PDF.', 'sukses')" class="text-emerald-700 hover:text-emerald-900 font-bold hover:underline">Kwitansi PDF</button>
                                </td>
                            </tr>

                            <!-- Transaksi 3 -->
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 font-bold text-slate-900">#ORD-20260820-0089</td>
                                <td class="px-4 py-3 text-slate-500">20 Ags 2026</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">Sirup Paracetamol Racikan + Amoxicillin 125mg • Resep dr. Siti, Sp.A</td>
                                <td class="px-4 py-3">Cabang Rogojampi</td>
                                <td class="px-4 py-3 font-bold text-emerald-700">Rp 68.000</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <button type="button" @click="modalCopyResep = true" class="text-emerald-700 hover:text-emerald-900 font-bold hover:underline">Copy Resep</button>
                                    <span class="text-slate-300">•</span>
                                    <button type="button" @click="tampilkanNotif('Kwitansi resmi apotek diunduh dalam format PDF.', 'sukses')" class="text-slate-600 hover:text-slate-900 font-medium">Kwitansi</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KONTEN TAB 5 PELANGGAN: KONSULTASI CHAT APOTEKER CABANG -->
        <div x-show="pelangganTab === 'konsultasi_chat'" :class="pelangganTab === 'konsultasi_chat' ? 'block' : 'hidden'" class="hidden space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <!-- Header Chat Apoteker -->
                <div class="p-4 sm:p-5 bg-gradient-to-r from-[#0A231E] to-[#124B40] text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-emerald-600 ring-2 ring-emerald-300 flex items-center justify-center font-bold text-white shadow-xs">
                            APJ
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-extrabold text-white">apt. Siti Nurhaliza, S.Farm</h3>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Online
                                </span>
                            </div>
                            <p class="text-xs text-emerald-200">Apoteker Penanggung Jawab • Cabang Banyuwangi Kota (SIPA: 19890412/SIPA_35.10/2021)</p>
                        </div>
                    </div>
                    <div class="text-xs text-emerald-200/80">
                        Standar Pelayanan Informasi Obat (PIO) Bebas Biaya
                    </div>
                </div>

                <!-- Chat Messages Feed -->
                <div class="p-4 sm:p-6 space-y-4 max-h-[380px] overflow-y-auto bg-slate-50/70">
                    <template x-for="(msg, idx) in chatMessages" :key="idx">
                        <div class="flex flex-col" :class="msg.sender === 'user' ? 'items-end' : 'items-start'">
                            <div class="flex items-center gap-1.5 mb-1 text-[11px] text-slate-500">
                                <span class="font-bold" :class="msg.sender === 'user' ? 'text-slate-700' : 'text-emerald-800'" x-text="msg.nama"></span>
                                <span>•</span>
                                <span x-text="msg.waktu"></span>
                            </div>
                            <div class="max-w-xl p-3.5 rounded-2xl text-xs leading-relaxed shadow-2xs"
                                 :class="msg.sender === 'user' 
                                    ? 'bg-[#0A231E] text-white rounded-tr-none' 
                                    : 'bg-white text-slate-800 border border-slate-200 rounded-tl-none'">
                                <p x-text="msg.text"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Input Chat Bar -->
                <div class="p-4 bg-white border-t border-slate-200 space-y-2">
                    <!-- Quick chips -->
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                        <span class="text-slate-400 font-semibold">Tanya Cepat:</span>
                        <button type="button" 
                                @click="chatInputText = 'Apakah obat ini aman dikonsumsi jika ada riwayat maag?'"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Aman untuk sakit maag?
                        </button>
                        <button type="button" 
                                @click="chatInputText = 'Bagaimana jika lupa minum dosis siang, apakah boleh digabung di malam hari?'"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Lupa minum dosis siang?
                        </button>
                        <button type="button" 
                                @click="chatInputText = 'Berapa hari sisa obat sirup boleh disimpan setelah segel dibuka?'"
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Penyimpanan sisa sirup?
                        </button>
                    </div>

                    <form @submit.prevent="kirimChatKonsultasi()" class="flex items-center gap-2">
                        <input type="text" 
                               x-model="chatInputText" 
                               placeholder="Tuliskan pertanyaan seputar obat, aturan pakai, atau efek samping di sini..." 
                               class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-medium">
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5 shrink-0">
                            <span>Kirim Pertanyaan</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL 1: STOCK OPNAME / PENYESUAIAN STAF BAHAN OBAT (APOTEKER)            -->
    <!-- ========================================================================= -->
    <div x-cloak x-show="modalStockOpname" 
         class="modal-dialog fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="modalStockOpname = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Stock Opname Bahan Obat &amp; Batch</h3>
                        <p class="text-[11px] text-slate-500">Penyesuaian stok fisik bahan racikan / obat jadi.</p>
                    </div>
                </div>
                <button @click="modalStockOpname = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="text-xs space-y-3">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500 text-[10px] uppercase font-bold">Bahan / Obat:</span>
                    <div class="font-extrabold text-slate-900 text-sm" x-text="selectedBahan.nama"></div>
                    <div class="text-slate-600 font-mono mt-0.5">Nomor Batch: <span x-text="selectedBahan.batch"></span> • ED: <span x-text="selectedBahan.expired"></span></div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah Stok Fisik Riil Terhitung</label>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model="selectedBahan.stokFisik" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-bold">
                        <span class="px-3 py-2 rounded-xl bg-slate-100 font-bold text-slate-700" x-text="selectedBahan.satuan"></span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alasan Penyesuaian (Audit Log Apoteker)</label>
                    <select class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        <option>Hasil Stock Opname Fisik Rutin Bulanan</option>
                        <option>Pengurangan Pemakaian Bahan Baku Racikan</option>
                        <option>Penyusutan Alami / Kerusakan Wadah</option>
                        <option>Retur Barang ke Distributor PBF</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" @click="modalStockOpname = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="simpanStockOpname()" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-black text-xs shadow-xs">
                    Simpan Penyesuaian Stok
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL KHUSUS APOTEKER: VERIFIKASI COLD CHAIN (2°C - 8°C) STANDAR CDOB     -->
    <!-- ========================================================================= -->
    <div x-cloak x-show="modalColdChain" 
         class="modal-dialog fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="modalColdChain = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="p-2.5 rounded-xl bg-cyan-100 text-cyan-900">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Verifikasi Rantai Dingin (Cold Chain 2°C - 8°C)</h3>
                        <p class="text-[11px] text-slate-500">Standar CDOB Kemenkes RI: Pemeriksaan Suhu Kedatangan Produk Biologis.</p>
                    </div>
                </div>
                <button @click="modalColdChain = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="text-xs space-y-3">
                <div class="p-3.5 rounded-xl bg-cyan-50 border border-cyan-200 space-y-1">
                    <div class="flex justify-between items-center text-cyan-950">
                        <span class="text-[10px] uppercase font-bold text-cyan-800">Distributor PBF Resmi:</span>
                        <span class="font-mono font-bold text-[11px]">No. Faktur: #FAK-ENS-2026-1188</span>
                    </div>
                    <div class="font-extrabold text-slate-900 text-sm">PT Enseval Putera Megatrading</div>
                    <div class="text-slate-700">Item: <strong class="text-cyan-900">Insulin Glargine 100 IU/mL (10 Pen) &amp; Vaksin TT</strong></div>
                </div>

                <!-- Display Suhu Digital Data Logger -->
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 font-bold block uppercase">Suhu Tiba Terukur</span>
                        <span class="text-xl font-mono font-black text-cyan-700">4.2°C</span>
                        <span class="block text-[10px] text-emerald-700 font-bold mt-0.5">✓ Lolos (Rentang 2°C - 8°C)</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-500 font-bold block uppercase">Kondisi Ice Gel</span>
                        <span class="text-sm font-extrabold text-slate-900 block mt-1">Beku Sempurna</span>
                        <span class="block text-[10px] text-slate-500">Freeze-tag: Tidak Rusak</span>
                    </div>
                </div>

                <!-- 3 Checklist APJ Cold Chain -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <span class="font-bold text-[11px] text-slate-800 block">Checklist Penerimaan APJ:</span>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                        <input type="checkbox" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span>Grafik data logger suhu konstan tanpa lonjakan beku ekstrem.</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                        <input type="checkbox" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span>Segel boks berinsulasi utuh dan tanggal kedaluwarsa &gt; 18 bulan.</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
                        <input type="checkbox" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span>Langsung dipindahkan ke lemari pendingin farmasi khusus (Chiller 2-8°C).</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" @click="modalColdChain = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="verifikasiColdChain()" class="px-4 py-2 rounded-xl bg-cyan-700 hover:bg-cyan-800 text-white font-black text-xs shadow-xs flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>ACC Suhu &amp; Masukkan Chiller Farmasi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: TUTUP BUKU / KUNCI PERIODE TRANSAKSI (SUPER ADMIN)               -->
    <!-- ========================================================================= -->
    <div x-cloak x-show="modalTutupPeriode" 
         class="modal-dialog fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="modalTutupPeriode = false">
            <div class="flex items-center gap-3 text-red-600">
                <div class="h-10 w-10 rounded-2xl bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Konfirmasi Tutup Periode Penjualan</h3>
                    <p class="text-[11px] text-slate-500">Tindakan ini akan mengunci seluruh transaksi cabang.</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin menutup periode penjualan <strong>01-16 Sep 2026</strong>? Setelah dikunci, data omzet sebesar <strong>Rp 142.850.000 (1.840 pesanan)</strong> tidak dapat diubah oleh staf kasir maupun apoteker cabang.
            </p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="modalTutupPeriode = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                    Batal
                </button>
                <button type="button" @click="eksekusiTutupPeriode()" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-md">
                    Kunci Periode Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: ROLE SWITCHER POPUP MOBILE                                       -->
        <!-- ========================================================================= -->
    <!-- MODAL 4: SALINAN RESEP RESMI APOTEK (COPY RESEP / APOGRAPH)               -->
    <!-- ========================================================================= -->
    <div x-cloak x-show="modalCopyResep" 
         class="modal-dialog fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="modalCopyResep = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Salinan Resep Resmi Apotek (Apograph)</h3>
                        <p class="text-[11px] text-slate-500">Dokumen legal farmasi salinan resep dokter yang sah.</p>
                    </div>
                </div>
                <button @click="modalCopyResep = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Lembar Salinan Resep (Kop Resmi & Format Kefarmasian) -->
            <div class="p-6 rounded-2xl border-2 border-emerald-800/20 bg-emerald-50/20 space-y-4 text-xs font-serif text-slate-800 relative">
                <!-- Kop Apotek -->
                <div class="text-center pb-3 border-b-2 border-slate-800/20 font-sans">
                    <div class="text-xs font-black tracking-widest text-[#0A231E] uppercase">APOTEK OMNIPHARMA NETWORK</div>
                    <div class="text-[11px] text-slate-600 font-medium">Cabang Banyuwangi Kota • Jl. Ahmad Yani No. 45, Banyuwangi</div>
                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">SIA: 503/SIA/012/2023 • SIPA APJ: 19890412/SIPA_35.10/2021</div>
                    <div class="mt-2 text-xs font-black uppercase tracking-wider text-emerald-900 border-t border-b border-emerald-900/20 py-1 inline-block">
                        SALINAN RESEP (APOGRAPH / COPY RESEP)
                    </div>
                </div>

                <div class="space-y-1 font-sans text-xs">
                    <div class="grid grid-cols-3">
                        <span class="text-slate-500">Dari Dokter:</span>
                        <span class="col-span-2 font-bold text-slate-900">dr. Budi, Sp.PD (SIP: 503/SIP/2024)</span>
                    </div>
                    <div class="grid grid-cols-3">
                        <span class="text-slate-500">Ditulis Tanggal:</span>
                        <span class="col-span-2 font-semibold">17 September 2026</span>
                    </div>
                    <div class="grid grid-cols-3">
                        <span class="text-slate-500">Pro (Pasien):</span>
                        <span class="col-span-2 font-bold text-slate-900">{{ $userName }} (34 Tahun)</span>
                    </div>
                    <div class="grid grid-cols-3">
                        <span class="text-slate-500">No. Registrasi:</span>
                        <span class="col-span-2 font-mono text-emerald-800 font-bold">#ORD-20260917-0042</span>
                    </div>
                </div>

                <!-- Resep Asli -->
                <div class="p-4 rounded-xl bg-white border border-slate-200 space-y-2">
                    <div class="font-serif italic text-sm text-slate-900">
                        R/ Amodiaquine 200mg Tab No. X
                    </div>
                    <div class="text-xs font-mono text-slate-700 pl-4">
                        S 3 d.d tab I d.c (post coenam)
                    </div>
                    <div class="text-xs font-mono text-emerald-800 font-bold pl-4">
                        - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - det. (Diserahkan 10 Tablet)
                    </div>
                </div>

                <!-- Cap Stempel & Tanda Tangan APJ -->
                <div class="flex items-center justify-between pt-2 font-sans text-[11px]">
                    <div class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-full border border-dashed border-emerald-700/60 flex items-center justify-center text-center text-[9px] text-emerald-800 font-bold uppercase rotate-[-12deg]">
                            CAP APOTEK<br>RESMI
                        </div>
                        <span class="text-[10px] text-slate-400">Tervalidasi Digital OmniPharma</span>
                    </div>
                    <div class="text-center">
                        <div class="text-slate-500">Banyuwangi, 17 Sep 2026</div>
                        <div class="text-slate-500">Apoteker Penanggung Jawab,</div>
                        <div class="font-bold text-slate-900 mt-6 underline">apt. Siti Nurhaliza, S.Farm</div>
                        <div class="text-[10px] text-slate-500 font-mono">SIPA: 19890412/SIPA_35.10/2021</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="modalCopyResep = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                    Tutup
                </button>
                <button type="button" @click="tampilkanNotif('Salinan Resep Resmi Apograph berhasil diunduh.', 'sukses')" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    <span>Unduh Dokumen PDF</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 5: TAMBAH PENGINGAT JADWAL MINUM OBAT PASIEN                        -->
    <!-- ========================================================================= -->
    <div x-cloak x-show="modalTambahJadwal" 
         class="modal-dialog fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="modalTambahJadwal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Tambah Pengingat Minum Obat</h3>
                        <p class="text-[11px] text-slate-500">Pasang alarm pengingat dosis harian keluarga Anda.</p>
                    </div>
                </div>
                <button @click="modalTambahJadwal = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form @submit.prevent="modalTambahJadwal = false; tampilkanNotif('Jadwal pengingat minum obat baru berhasil ditambahkan.', 'sukses')" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Obat &amp; Dosis</label>
                    <input type="text" placeholder="Contoh: Amoxicillin 500mg" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Frekuensi Harian</label>
                        <select class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                            <option>1x Sehari (Tiap 24 Jam)</option>
                            <option>2x Sehari (Tiap 12 Jam)</option>
                            <option selected>3x Sehari (Tiap 8 Jam)</option>
                            <option>4x Sehari (Tiap 6 Jam)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Aturan Makan</label>
                        <select class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                            <option>Sesudah Makan</option>
                            <option>Sebelum Makan</option>
                            <option>Bersama Makan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Waktu Dosis Pertama (Jam Alarm)</label>
                    <input type="time" value="07:00" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 font-bold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan Apoteker</label>
                    <input type="text" placeholder="Contoh: Wajib dihabiskan walau sudah sembuh" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalTambahJadwal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-xs">
                        Pasang Pengingat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <div x-cloak x-show="modalRoleSwitcher" 
         class="modal-dialog fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="modalRoleSwitcher = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900">Pilih Mode Tampilan Dashboard Role</h3>
                <button @click="modalRoleSwitcher = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <a href="{{ url()->current() }}?role=super_admin"
                   @click="currentRole = 'super_admin'; modalRoleSwitcher = false;"
                   class="w-full text-left p-3 rounded-2xl border transition flex items-center justify-between"
                   :class="currentRole === 'super_admin' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 hover:bg-slate-50'">
                    <div>
                        <div class="font-extrabold text-slate-900">👑 Super Admin</div>
                        <div class="text-[11px] text-slate-500">Bisa mengatur segalanya: Multi-cabang, Staf, Master Obat, Tutup Buku.</div>
                    </div>
                    <span x-show="currentRole === 'super_admin'" class="text-emerald-700 font-black text-base">✓</span>
                </a>

                <a href="{{ url()->current() }}?role=apoteker"
                   @click="currentRole = 'apoteker'; modalRoleSwitcher = false;"
                   class="w-full text-left p-3 rounded-2xl border transition flex items-center justify-between"
                   :class="currentRole === 'apoteker' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 hover:bg-slate-50'">
                    <div>
                        <div class="font-extrabold text-slate-900">🩺 Apoteker Penanggung Jawab (APJ)</div>
                        <div class="text-[11px] text-slate-500">Mengecek stok bahan obat, rotasi FEFO, verifikasi resep dokter, compounding.</div>
                    </div>
                    <span x-show="currentRole === 'apoteker'" class="text-emerald-700 font-black text-base">✓</span>
                </a>

                <a href="{{ url()->current() }}?role=pelanggan"
                   @click="currentRole = 'pelanggan'; modalRoleSwitcher = false;"
                   class="w-full text-left p-3 rounded-2xl border transition flex items-center justify-between"
                   :class="currentRole === 'pelanggan' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 hover:bg-slate-50'">
                    <div>
                        <div class="font-extrabold text-slate-900">👤 Pasien / Pelanggan</div>
                        <div class="text-[11px] text-slate-500">Unggah foto resep dokter, beli obat bebas &amp; vitamin, jadwal minum obat.</div>
                    </div>
                    <span x-show="currentRole === 'pelanggan'" class="text-emerald-700 font-black text-base">✓</span>
                </a>
            </div>
        </div>
    </div>

    <!-- FOOTER RESMI APOTEK OMNIPHARMA NETWORK -->
    <footer class="bg-white border-t border-slate-200/80 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-black text-slate-800 tracking-wider uppercase">OmniPharma Network</span>
                <span>• Standar Pelayanan Kefarmasian Permenkes RI No. 73/2016</span>
            </div>
            <div>
                <span>Sistem Farmasi Multi-Tenant Terpadu • Lisensi SIA &amp; SIPA Terdaftar</span>
            </div>
        </div>
    </footer>

</body>
</html>
