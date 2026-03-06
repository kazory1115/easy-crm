<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserActivityLogFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_unauthorized_user_cannot_access_users_api(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_user_api_index_and_store_work(): void
    {
        Sanctum::actingAs($this->user);

        User::factory()->count(2)->create();

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);

        $this->postJson('/api/users', [
            'name' => '新員工',
            'email' => 'new-staff@example.com',
            'password' => 'password123',
            'phone' => '0912345678',
            'department' => 'sales',
            'position' => '業務專員',
        ])->assertCreated()
            ->assertJsonPath('message', '員工建立成功')
            ->assertJsonPath('data.email', 'new-staff@example.com');
    }

    public function test_activity_log_index_and_module_endpoint_work(): void
    {
        Sanctum::actingAs($this->user);

        ActivityLog::create([
            'log_name' => 'user',
            'subject_type' => User::class,
            'subject_id' => $this->user->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'event' => 'updated',
            'description' => '更新使用者資料',
            'properties' => ['attributes' => ['name' => '測試']],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->getJson('/api/activity-logs')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);

        $this->getJson('/api/activity-logs/module/user')
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
    }
}
