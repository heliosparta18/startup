@props([
    'title' => 'Super Admin Pusat - OmniPharma',
    'showSplash' => true,
])
@php
    $navItems = [
        [
            'label' => 'Ringkasan Pusat',
            'href' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard'),
        ],
        [
            'label' => 'Cek Apoteker',
            'href' => route('admin.apoteker.index'),
            'active' => request()->routeIs('admin.apoteker.*'),
        ],
        [
            'label' => 'Monitoring Obat',
            'href' => route('admin.progres.index'),
            'active' => request()->routeIs('admin.progres.index*'),
        ],
        [
            'label' => 'Katalog & Obat',
            'href' => route('admin.obat.index'),
            'active' => request()->routeIs('admin.obat.*'),
        ],
        [
            'label' => 'Staf & APJ Cabang',
            'href' => route('admin.cabang.staf', ['client' => request()->route('client') ?? 'banyuwangi-kota']),
            'active' => request()->routeIs('admin.cabang.staf*'),
        ],
        [
            'label' => 'Kasir & Ledger',
            'href' => route('admin.kasir.ledger'),
            'active' => request()->routeIs('admin.kasir.ledger*'),
        ],
        [
            'label' => 'Pengaturan Cabang',
            'href' => route('admin.cabang.pengaturan', ['client' => request()->route('client') ?? 'banyuwangi-kota']),
            'active' => request()->routeIs('admin.cabang.pengaturan*') || request()->routeIs('admin.cabang.store') || request()->routeIs('admin.cabang.destroy'),
        ],
    ];
@endphp
<x-layouts.internal-portal
    :title="$title"
    portal-subtitle="Pusat Multi-Cabang"
    :nav-items="$navItems"
    :portal-home-route="route('admin.dashboard')"
    portal-home-label="Executive Dashboard"
    :show-splash="$showSplash"
>
    {{ $slot }}
</x-layouts.internal-portal>
