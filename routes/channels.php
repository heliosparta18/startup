<?php

use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.{id_pesanan}', function (?User $user, string $idPesanan) {
    $pesanan = Pesanan::withoutGlobalScopes()->where('id_pesanan', $idPesanan)->first();

    if (! $pesanan) {
        return false;
    }

    if ($user) {
        $userEmail = $user->email;
        $idPengguna = $user->id_pengguna ?? ($userEmail ? Pengguna::where('telepon_email', $userEmail)->value('id_pengguna') : null);
        if ($idPengguna && $pesanan->id_pengguna === $idPengguna) {
            return true;
        }
    }

    $sessionToken = request()->cookie('omnipharma_customer_session');
    if ($sessionToken && $pesanan->token_sesi === $sessionToken) {
        return true;
    }

    return false;
});

Broadcast::channel('tenant.{id_client}', function (User $user, string $idClient) {
    if ($user->hasRole('super_admin') || $user->hasRole('apoteker')) {
        return true;
    }

    $pengguna = Pengguna::where('telepon_email', $user->email)->first();
    if ($pengguna && $pengguna->peran === 'super_admin') {
        return true;
    }

    if ($pengguna) {
        return $pengguna->peranClients()
            ->where('id_client', $idClient)
            ->where('aktif', true)
            ->exists();
    }

    return false;
});
