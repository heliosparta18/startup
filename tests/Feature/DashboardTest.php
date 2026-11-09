<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_root_url_shows_omnipharma_catalog_not_laravel_welcome(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('OmniPharma');
        $response->assertDontSee('Laravel has an incredibly rich ecosystem');
        $response->assertDontSee('Laracasts');
    }

    public function test_root_url_redirects_authenticated_user_to_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'pelanggan']);
        $response = $this->actingAs($user)->get(route('home'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_root_url_redirects_super_admin_to_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($user)->get(route('home'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_root_url_redirects_apoteker_to_tenant_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'apoteker']);
        $response = $this->actingAs($user)->get(route('home'));
        $response->assertRedirect(route('tenant.dashboard', ['client' => 'banyuwangi-kota']));
    }

    public function test_login_ignores_intended_root_to_prevent_landing_on_laravel(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'password' => 'password123',
        ]);

        $response = $this->withSession(['url.intended' => url('/')])
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_customer_portal_pages_contain_navigation_back_to_dashboard(): void
    {
        $resHome = $this->get(route('customer.home', ['client' => 'banyuwangi-kota']));
        $resHome->assertOk();
        $resHome->assertSee(route('dashboard'));
        $resHome->assertSee('Dashboard Utama');

        $resCart = $this->get(route('customer.cart', ['client' => 'banyuwangi-kota']));
        $resCart->assertOk();
        $resCart->assertSee(route('dashboard'));
        $resCart->assertSee('Dashboard Utama');

        $resDetail = $this->get(route('customer.obat.detail', ['client' => 'banyuwangi-kota', 'id' => 'sample-id']));
        $resDetail->assertOk();
        $resDetail->assertSee(route('dashboard'));
        $resDetail->assertSee('Dashboard Utama');
    }

    public function test_tenant_and_admin_portals_contain_navigation_back_to_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $resAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
        $resAdmin->assertOk();
        $resAdmin->assertSee(route('dashboard'));
        $resAdmin->assertSee('Dashboard Utama');

        $apoteker = User::factory()->create(['role' => 'apoteker']);
        $resTenant = $this->actingAs($apoteker)->get(route('tenant.dashboard', ['client' => 'banyuwangi-kota']));
        $resTenant->assertOk();
        $resTenant->assertSee(route('dashboard'));
        $resTenant->assertSee('Dashboard Utama');
    }
}
