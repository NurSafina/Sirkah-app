<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_the_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-test',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk()->assertViewIs('dashboard');
    }
}
