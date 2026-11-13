<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_appearance_tab_is_absent_from_settings_navigation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('security.edit'));
        $response->assertDontSee(route('appearance.edit'));
        $response->assertDontSee('Appearance');
    }

    public function test_security_page_also_omits_appearance_tab(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'));

        $response->assertOk();
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('security.edit'));
        $response->assertDontSee(route('appearance.edit'));
        $response->assertDontSee('Appearance');
    }
}
