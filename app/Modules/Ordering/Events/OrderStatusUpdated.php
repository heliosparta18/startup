<?php

namespace App\Modules\Ordering\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Pesanan $pesanan) {}

    public function broadcastOn(): array
    {
        // Cukup 'order.' dan 'tenant.', Laravel otomatis menambahkan prefix 'private-'
        return [
            new PrivateChannel('order.'.$this->pesanan->id_pesanan),
            new PrivateChannel('tenant.'.$this->pesanan->id_client),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $status = $this->pesanan->status ?? $this->pesanan->status_pesanan;

        return [
            'id_pesanan' => $this->pesanan->id_pesanan,
            'nomor_pesanan' => $this->pesanan->nomor_pesanan,
            'status_pesanan' => $status,
            'status' => $status,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
