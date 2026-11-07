<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Modules\Ordering\Events\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReverbOrderStatusBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_updated_broadcasts_to_correct_channels(): void
    {
        Event::fake([OrderStatusUpdated::class]);

        $pesanan = Pesanan::factory()->create([
            'status' => 'diproses',
        ]);

        OrderStatusUpdated::dispatch($pesanan);

        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($pesanan) {
            $channels = $event->broadcastOn();
            $this->assertEquals('private-order.'.$pesanan->id_pesanan, $channels[0]->name);
            $this->assertEquals('private-tenant.'.$pesanan->id_client, $channels[1]->name);

            return true;
        });
    }

    public function test_order_status_updated_broadcast_payload_structure(): void
    {
        $pesanan = Pesanan::factory()->create([
            'status' => 'siap_diambil',
        ]);

        $event = new OrderStatusUpdated($pesanan);
        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('id_pesanan', $payload);
        $this->assertArrayHasKey('nomor_pesanan', $payload);
        $this->assertArrayHasKey('status_pesanan', $payload);
        $this->assertArrayHasKey('updated_at', $payload);
        $this->assertEquals('siap_diambil', $payload['status_pesanan']);
    }
}
