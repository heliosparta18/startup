@props([
    'title' => 'Portal Kasir Cabang - OmniPharma',
    'branchName' => null,
    'showSplash' => true,
])
@php
    $loggedUser = auth()->user();
    $clientSlug = method_exists($loggedUser, 'getClientSlug') ? $loggedUser->getClientSlug() : 'banyuwangi-kota';
    $branchTitle = $branchName ?: ('Kasir Cabang '.ucwords(str_replace('-', ' ', (string) $clientSlug)));

    $navItems = [
        [
            'label' => 'Kasir & POS Loket',
            'href' => route('kasir.dashboard'),
            'active' => request()->routeIs('kasir.dashboard'),
        ],
        [
            'label' => 'Buku Ledger',
            'href' => route('kasir.ledger'),
            'active' => request()->routeIs('kasir.ledger') || request()->routeIs('kasir.struk'),
        ],
    ];
@endphp
<x-layouts.internal-portal
    :title="$title"
    :portal-subtitle="$branchTitle"
    :nav-items="$navItems"
    :portal-home-route="route('kasir.dashboard')"
    portal-home-label="Dashboard Kasir"
    :show-splash="$showSplash"
>
    {{ $slot }}
</x-layouts.internal-portal>
