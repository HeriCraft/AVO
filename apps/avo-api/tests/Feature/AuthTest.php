<?php

namespace Tests\Feature;

use App\Persistence\Models\User;
use App\Users\Events\UserLoggedIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_seeder_creates_admin_user(): void
    {
        $this->seed(\App\Persistence\SuperAdminSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'granix@yopmail.io',
            'role' => 'SUPER_ADMIN',
        ]);
    }

    public function test_login_returns_jwt_token_and_dispatches_event(): void
    {
        Event::fake([UserLoggedIn::class]);

        $this->seed(\App\Persistence\SuperAdminSeeder::class);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'granix@yopmail.io',
            'password' => 'Admin@1234',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user' => ['name', 'email', 'role'], 'expires_in']);

        Event::assertDispatched(UserLoggedIn::class, function ($event) {
            return $event->user->email === 'granix@yopmail.io';
        });
    }

    public function test_login_rejects_invalid_password(): void
    {
        $this->seed(\App\Persistence\SuperAdminSeeder::class);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'granix@yopmail.io',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_rejects_nonexistent_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@yopmail.io',
            'password' => 'Admin@1234',
        ]);

        $response->assertStatus(401);
    }
}
