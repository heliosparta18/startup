@php
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('super_admin') || $user->role === 'super_admin') {
            header('Location: ' . route('admin.dashboard'));
            exit;
        }
        if ($user->hasRole('apoteker') || $user->role === 'apoteker') {
            header('Location: ' . route('tenant.dashboard', ['client' => 'banyuwangi-kota']));
            exit;
        }
        header('Location: ' . route('dashboard'));
        exit;
    }
    header('Location: ' . route('customer.home', ['client' => 'banyuwangi-kota']));
    exit;
@endphp
