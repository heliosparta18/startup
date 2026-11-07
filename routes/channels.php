<?php

use App\Models\Pengguna;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('order.{id_pesanan}', function (?Pengguna $user, string $idPesanan) {
    $pesanan = Pesanan::withoutGlobalScopes()->where('id_pesanan', $idPesanan)->first();

    if (! $pesanan) {
        return false;
    }


    if ($user && $pesanan->id_pengguna === $user->id_pengguna) {
        return true;
    }

    
    $sessionToken = request()->cookie('omnipharma_customer_session');
    if ($sessionToken && $pesanan->token_sesi === $sessionToken) {
        return true;
    }

    return false;
});


Broadcast::channel('tenant.{id_client}', function (Pengguna $user, string $idClient) {
    if ($user->peran === 'super_admin') {
        return true;
    }

    return $user->peranClients()
        ->where('id_client', $idClient)
        ->where('aktif', true)
        ->exists();
});