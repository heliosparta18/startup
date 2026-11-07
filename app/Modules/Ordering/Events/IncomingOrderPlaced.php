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

    /**
     * Inisialisasi instance event dengan model Pesanan.
     */
    public function __construct(public Pesanan $pesanan) {}

    /**
     * Tentukan channel siaran WebSocket.
     * Hanya disiarkan ke private channel cabang apotek terkait (tenant).
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.'.$this->pesanan->id_client),
        ];
    }

    /**
     * Payload data ringkas pesanan baru untuk antrean dasbor apoteker.
     */
    public function broadcastWith(): array
    {
        return [
            'id_pesanan' => $this->pesanan->id_pesanan,
            'nomor_pesanan' => $this->pesanan->nomor_pesanan,
            'total_harga' => $this->pesanan->total_harga,
            'wajib_resep' => (bool) ($this->pesanan->wajib_resep ?? false),
            'status_pesanan' => $this->pesanan->status_pesanan,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
