<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Quote;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffFeatureTest extends TestCase
{
    public function test_unauthorized_user_cannot_access_staff_endpoints(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
        $this->getJson('/api/users/stats')->assertUnauthorized();
    }

    public function test_user_without_staff_permission_cannot_access_staff_endpoints(): void
    {
        $user = $this->createUserWithAccess(['staff']);
        Sanctum::actingAs($user);

        $this->getJson('/api/users')->assertForbidden();
        $this->getJson('/api/users/stats')->assertForbidden();
    }

    public function test_staff_index_show_and_stats_endpoints_return_expected_payloads(): void
    {
        $user = $this->createUserWithAccess(['admin']);
        Sanctum::actingAs($user);

        User::factory()->count(2)->create();
        Quote::factory()->count(2)->create(['created_by' => $user->id]);
        Customer::factory()->count(3)->create(['created_by' => $user->id]);
        ActivityLog::create([
            'log_name' => 'user',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'event' => 'updated',
            'description' => 'Updated own profile',
            'properties' => ['attributes' => ['name' => $user->name]],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'user_id' => $user->id,
        ]);

        $staff = $this->createUserWithAccess(['staff']);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total'])
            ->assertJsonPath('data.0.roles.0', 'staff');

        $this->getJson("/api/users/{$staff->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $staff->id)
            ->assertJsonPath('data.roles.0', 'staff');

        $this->getJson('/api/users/stats')
            ->assertOk()
            ->assertJsonPath('data.total_quotes_created', 2)
            ->assertJsonPath('data.total_customers', 3)
            ->assertJsonCount(1, 'data.recent_activities');
    }

    public function test_staff_store_update_and_delete_flow_works(): void
    {
        $user = $this->createUserWithAccess(['admin']);
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/users', [
            'name' => 'New Staff',
            'email' => 'new-staff@example.com',
            'password' => 'password123',
            'phone' => '0912345678',
            'department' => 'sales',
            'position' => 'Sales Manager',
            'roles' => ['staff'],
            'direct_permissions' => ['crm.view'],
        ])->assertCreated()
            ->assertJsonPath('data.email', 'new-staff@example.com')
            ->assertJsonPath('data.roles.0', 'staff')
            ->assertJsonPath('data.direct_permissions.0', 'crm.view');

        $staffId = $createResponse->json('data.id');

        $this->putJson("/api/users/{$staffId}", [
            'name' => 'Updated Staff',
            'department' => 'marketing',
            'position' => 'Marketing Lead',
            'roles' => ['manager'],
            'direct_permissions' => ['crm.view', 'order.view'],
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Staff')
            ->assertJsonPath('data.department', 'marketing');

        $this->deleteJson("/api/users/{$staffId}")
            ->assertOk()
            ->assertJsonPath('message', '員工刪除成功');

        $this->assertDatabaseMissing('users', ['id' => $staffId]);
    }

    public function test_admin_can_manage_user_roles_and_permissions(): void
    {
        $admin = $this->createUserWithAccess(['admin']);
        $staff = $this->createUserWithAccess(['staff']);
        Sanctum::actingAs($admin);

        $this->getJson("/api/users/{$staff->id}/roles")
            ->assertOk()
            ->assertJsonPath('data.roles.0', 'staff');

        $this->putJson("/api/users/{$staff->id}/roles", [
            'roles' => ['manager'],
        ])->assertOk()
            ->assertJsonPath('data.roles.0', 'manager');

        $this->putJson("/api/users/{$staff->id}/permissions", [
            'direct_permissions' => ['order.view', 'order.create'],
        ])->assertOk()
            ->assertJsonPath('data.direct_permissions.0', 'order.view');

        $this->getJson('/api/permissions/modules')
            ->assertOk()
            ->assertJsonPath('data.roles.0', 'admin');

        $this->getJson("/api/users/{$staff->id}")
            ->assertOk()
            ->assertJsonPath('data.roles.0', 'manager');
    }

    public function test_non_admin_cannot_manage_user_roles_and_permissions(): void
    {
        $manager = $this->createUserWithAccess(['manager']);
        $staff = $this->createUserWithAccess(['staff']);
        Sanctum::actingAs($manager);

        $this->putJson("/api/users/{$staff->id}/roles", [
            'roles' => ['manager'],
        ])->assertForbidden();

        $this->putJson("/api/users/{$staff->id}/permissions", [
            'direct_permissions' => ['order.view'],
        ])->assertForbidden();
    }

    public function test_user_cannot_delete_self(): void
    {
        $user = $this->createUserWithAccess(['admin']);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/users/{$user->id}")
            ->assertForbidden();
    }
}
