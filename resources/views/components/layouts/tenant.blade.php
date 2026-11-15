@props([
    'title' => 'Portal Apoteker Cabang - OmniPharma',
    'showSplash' => true,
])
@php
    $loggedUser = auth()->user();
    $clientSlug = request()->route('client') ?? (method_exists($loggedUser, 'getClientSlug') ? $loggedUser->getClientSlug() : 'banyuwangi-kota');
    $branchTitle = 'Cabang '.ucwords(str_replace('-', ' ', (string) $clientSlug));

    $navItems = [
        [
            'label' => 'Dashboard Cabang',
            'href' => route('tenant.dashboard', ['client' => $clientSlug]),
            'active' => request()->routeIs('tenant.dashboard'),
        ],
        [
            'label' => 'Antrean Obat',
            'href' => route('tenant.antrean.index', ['client' => $clientSlug]),
            'active' => request()->routeIs('tenant.antrean*'),
        ],
        [
            'label' => 'Telaah Resep',
            'href' => route('tenant.resep.antrean', ['client' => $clientSlug]),
            'active' => request()->routeIs('tenant.resep.*'),
        ],
        [
            'label' => 'Stok FEFO',
            'href' => route('tenant.stok.fefo', ['client' => $clientSlug]),
            'active' => request()->routeIs('tenant.stok.*'),
        ],
        [
            'label' => 'Laporan Penjualan',
            'href' => route('tenant.laporan.index', ['client' => $clientSlug]),
            'active' => request()->routeIs('tenant.laporan*'),
        ],
        [
            'label' => 'Rekam Pasien',
            'href' => route('tenant.pasien.index', ['client' => $clientSlug]),
            'active' => request()->routeIs('tenant.pasien*'),
        ],
        [
            'label' => 'SOP & Bantuan',
            'href' => route('tenant.bantuan.index', ['client' => $clientSlug]),
            'active' => request()->routeIs('tenant.bantuan*'),
        ],
    ];
@endphp
<x-layouts.internal-portal
    :title="$title"
    :portal-subtitle="$branchTitle"
    :nav-items="$navItems"
    :portal-home-route="route('tenant.dashboard', ['client' => $clientSlug])"
    portal-home-label="Dashboard Cabang"
    :show-splash="$showSplash"
>
    {{ $slot }}
</x-layouts.internal-portal>
