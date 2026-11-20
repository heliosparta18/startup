<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoApotekSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSlidingNavbarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoApotekSeeder::class);
    }

    /**
     * Memastikan portal Super Admin merender navbar sliding beranimasi dengan persistensi Livewire,
     * seluruh 7 checkpoint navigasi, wire:navigate.hover, dan indikator pill bg-emerald-700.
     */
    public function test_super_admin_portal_renders_persisted_animated_sliding_navbar_with_checkpoints(): void
    {
        $admin = User::where('role', 'super_admin')->first()
            ?? User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();

        // 1. Persistensi SPA Navigation
        $response->assertSee('x-persist="super-admin-desktop-navbar"', false);

        // 2. 7 Checkpoint lengkap
        $response->assertSee('Ringkasan Pusat');
        $response->assertSee('Cek Apoteker');
        $response->assertSee('Monitoring Obat');
        $response->assertSee('Katalog &amp; Obat', false);
        $response->assertSee('Staf &amp; APJ Cabang', false);
        $response->assertSee('Kasir &amp; Ledger', false);
        $response->assertSee('Pengaturan Cabang');

        // 3. Instant prefetch Livewire SPA
        $response->assertSee('wire:navigate.hover', false);

        // 4. Indikator pill dengan styling dan timing cubic-bezier
        $response->assertSee('bg-emerald-700 rounded-xl', false);
        $response->assertSee('cubic-bezier(0.22,1,0.36,1)', false);
        $response->assertSee('duration-380', false);
    }

    /**
     * Memastikan optimasi rute /admin/dashboard tidak lagi eager-loading relasi stok.obat yang mubazir.
     */
    public function test_dashboard_route_does_not_eager_load_unused_stok_obat_relation(): void
    {
        $admin = User::where('role', 'super_admin')->first()
            ?? User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $viewClients = $response->viewData('clients');
        $this->assertNotNull($viewClients);
        $this->assertGreaterThan(0, $viewClients->count());

        // Verifikasi bahwa relasi stok TIDAK di-load (menghemat alokasi ribuan model ke memori)
        $firstClient = $viewClients->first();
        $this->assertFalse($firstClient->relationLoaded('stok'));
    }

    /**
     * Memastikan isolasi ketat peran: portal tenant/apoteker tidak terkontaminasi oleh navbar super admin.
     */
    public function test_non_admin_portals_preserve_isolated_static_nav_without_custom_nav_interference(): void
    {
        $apoteker = User::where('role', 'apoteker')->first()
            ?? User::factory()->create(['role' => 'apoteker']);

        $response = $this->actingAs($apoteker)->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']));
        $response->assertOk();

        // Navbar tenant tidak memuat persistensi super admin
        $response->assertDontSee('super-admin-desktop-navbar');
    }
}
