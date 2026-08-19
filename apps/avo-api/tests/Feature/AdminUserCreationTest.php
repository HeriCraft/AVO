<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\ActivityLog;
use App\Users\Events\UserCreatedByAdmin;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class AdminUserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_mandatory_fields()
    {
        Event::fake([UserCreatedByAdmin::class]);

        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        
        $response = $this->actingAs($admin, 'api')->postJson('/api/admin/users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'role' => 'USER'
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('username', 'newuser')
                 ->assertJsonPath('email', 'newuser@example.com');
                 
        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'role' => 'USER',
            'name' => 'newuser' // Fallback to username when first/last name empty
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'USER_CREATED',
        ]);

        Event::assertDispatched(UserCreatedByAdmin::class);
    }

    public function test_admin_can_create_user_with_optional_fields()
    {
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        
        $response = $this->actingAs($admin, 'api')->postJson('/api/admin/users', [
            'username' => 'johndoe',
            'email' => 'johndoe@example.com',
            'password' => 'Password123!',
            'role' => 'USER',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'Acme Corp',
            'company_role' => 'Developer'
        ]);

        $response->assertStatus(201);
                 
        $this->assertDatabaseHas('users', [
            'username' => 'johndoe',
            'name' => 'John Doe',
            'company' => 'Acme Corp',
            'company_role' => 'Developer'
        ]);
    }

    public function test_fails_if_mandatory_fields_missing()
    {
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        
        $response = $this->actingAs($admin, 'api')->postJson('/api/admin/users', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['username', 'email', 'password', 'role']);
    }

    public function test_fails_if_role_is_not_user()
    {
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        
        $response = $this->actingAs($admin, 'api')->postJson('/api/admin/users', [
            'username' => 'badrole',
            'email' => 'badrole@example.com',
            'password' => 'Password123!',
            'role' => 'SUPER_ADMIN' // Should fail
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['role']);
    }

    public function test_fails_if_duplicate_username_or_email()
    {
        User::factory()->create(['username' => 'existing', 'email' => 'existing@example.com']);
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        
        $response = $this->actingAs($admin, 'api')->postJson('/api/admin/users', [
            'username' => 'existing',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'role' => 'USER'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['username', 'email']);
    }
}
