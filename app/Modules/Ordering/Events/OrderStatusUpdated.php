namespace App\Modules\Ordering\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Pesanan $pesanan) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-order.' . $this->pesanan->id_pesanan),
            new PrivateChannel('private-tenant.' . $this->pesanan->id_client),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id_pesanan' => $this->pesanan->id_pesanan,
            'nomor_pesanan' => $this->pesanan->nomor_pesanan,
            'status_pesanan' => $this->pesanan->status_pesanan,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}