<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_and_documentation_links_are_absent_from_sidebar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertDontSee('https://github.com/laravel/livewire-starter-kit', false);
        $response->assertDontSee('https://laravel.com/docs/starter-kits#livewire', false);
        $response->assertDontSee('Repository');
        $response->assertDontSee('Documentation');
    }

    public function test_default_user_role_customer_is_displayed_in_profile_widget(): void
    {
        $user = User::factory()->create([
            'role' => null,
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertDontSee('Customer');
        $response->assertDontSee('PASIEN');
    }

    public function test_assigned_user_role_is_displayed_in_profile_widget(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertDontSee('PUSAT');
    }

    public function test_apoteker_role_is_displayed_in_profile_widget(): void
    {
        $user = User::factory()->create([
            'role' => 'apoteker',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertDontSee('APJ');
    }
}
