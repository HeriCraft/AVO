<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Persistence\Models\User;
use App\Persistence\Models\PlatformSetting;

class AdminSuiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_metrics()
    {
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        User::factory()->count(3)->create(['status' => 'ACTIVE']);
        
        $response = $this->actingAs($admin, 'api')->getJson('/api/admin/dashboard/metrics');

        $response->assertStatus(200)
                 ->assertJsonStructure(['kpis', 'role_distribution', 'registrations_30_days', 'recent_logs']);
    }

    public function test_admin_can_toggle_user_status()
    {
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        $user = User::factory()->create(['status' => 'ACTIVE']);
        
        $response = $this->actingAs($admin, 'api')->patchJson("/api/admin/users/{$user->id}/toggle-status");
        
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'SUSPENDED');
                 
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'SUSPENDED'
        ]);
    }

    public function test_admin_can_manage_settings()
    {
        $admin = User::factory()->create(['role' => 'SUPER_ADMIN']);
        
        // Create
        $response = $this->actingAs($admin, 'api')->postJson('/api/admin/settings', [
            'key' => 'MAINTENANCE_MODE',
            'value' => json_encode(true)
        ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('platform_settings', ['key' => 'MAINTENANCE_MODE']);

        // Update
        $response = $this->actingAs($admin, 'api')->putJson('/api/admin/settings/MAINTENANCE_MODE', [
            'value' => json_encode(false)
        ]);
        $response->assertStatus(200);

        // Delete
        $response = $this->actingAs($admin, 'api')->deleteJson('/api/admin/settings/MAINTENANCE_MODE');
        $response->assertStatus(204);
        $this->assertDatabaseMissing('platform_settings', ['key' => 'MAINTENANCE_MODE']);
    }
}
