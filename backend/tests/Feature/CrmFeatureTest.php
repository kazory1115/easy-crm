<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Opportunity;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CrmFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWithAccess(['admin']);
    }

    private function customerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => '測試客戶股份有限公司',
            'contact_person' => '王小明',
            'phone' => '02-12345678',
            'mobile' => '0912345678',
            'email' => 'crm-customer@example.com',
            'tax_id' => '12345678',
            'website' => 'https://example.com',
            'industry' => '科技業',
            'address' => '台北市測試路 100 號',
            'notes' => 'CRM 測試資料',
            'status' => 'active',
        ], $overrides);
    }

    private function opportunityPayload(int $customerId, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customerId,
            'name' => '2026 Q2 系統導入案',
            'stage' => 'proposal',
            'amount' => 120000,
            'expected_close_date' => now()->addDays(30)->toDateString(),
            'status' => 'open',
            'notes' => '預計本季結案',
        ], $overrides);
    }

    public function test_unauthorized_user_cannot_access_customer_api(): void
    {
        $this->getJson('/api/customers')->assertUnauthorized();
    }

    public function test_customer_crud_and_filters_work(): void
    {
        Sanctum::actingAs($this->user);

        $createResponse = $this->postJson('/api/customers', $this->customerPayload());
        $createResponse->assertCreated()
            ->assertJsonPath('message', '客戶建立成功')
            ->assertJsonPath('data.name', '測試客戶股份有限公司');

        $customerId = (int)$createResponse->json('data.id');

        $this->getJson('/api/customers?search=測試客戶&status=active&industry=科技業')
            ->assertOk()
            ->assertJsonPath('total', 1);

        $this->putJson("/api/customers/{$customerId}", [
            'status' => 'inactive',
            'industry' => '製造業',
            'notes' => '已更新狀態',
        ])->assertOk()
            ->assertJsonPath('message', '客戶更新成功')
            ->assertJsonPath('data.status', 'inactive')
            ->assertJsonPath('data.industry', '製造業');

        $this->deleteJson("/api/customers/{$customerId}")
            ->assertOk()
            ->assertJsonPath('message', '客戶刪除成功');

        $this->assertSoftDeleted('customers', [
            'id' => $customerId,
        ]);
    }

    public function test_customer_validation_fail_returns_422(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/customers', [
            'status' => 'unknown-status',
            'email' => 'not-an-email',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status', 'email']);
    }

    public function test_customer_contact_crud_under_customer_works(): void
    {
        Sanctum::actingAs($this->user);

        $customer = Customer::factory()->create();

        $firstContactResponse = $this->postJson("/api/customers/{$customer->id}/contacts", [
            'name' => '主要窗口',
            'title' => '經理',
            'email' => 'primary@example.com',
            'phone' => '02-11112222',
            'is_primary' => true,
        ]);

        $firstContactResponse->assertCreated()
            ->assertJsonPath('message', '聯絡人建立成功')
            ->assertJsonPath('data.is_primary', true);

        $firstContactId = (int)$firstContactResponse->json('data.id');

        $secondContactResponse = $this->postJson("/api/customers/{$customer->id}/contacts", [
            'name' => '採購窗口',
            'title' => '採購',
            'email' => 'purchase@example.com',
            'mobile' => '0922333444',
            'is_primary' => true,
        ]);

        $secondContactResponse->assertCreated()
            ->assertJsonPath('data.is_primary', true);

        $secondContactId = (int)$secondContactResponse->json('data.id');

        $this->assertDatabaseHas('customer_contacts', [
            'id' => $firstContactId,
            'is_primary' => false,
        ]);
        $this->assertDatabaseHas('customer_contacts', [
            'id' => $secondContactId,
            'is_primary' => true,
        ]);

        $this->getJson("/api/customers/{$customer->id}/contacts")
            ->assertOk()
            ->assertJsonPath('total', 2);

        $this->putJson("/api/customers/{$customer->id}/contacts/{$firstContactId}", [
            'title' => '資深經理',
            'is_primary' => true,
        ])->assertOk()
            ->assertJsonPath('message', '聯絡人更新成功')
            ->assertJsonPath('data.title', '資深經理')
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('customer_contacts', [
            'id' => $secondContactId,
            'is_primary' => false,
        ]);

        $this->deleteJson("/api/customers/{$customer->id}/contacts/{$secondContactId}")
            ->assertOk()
            ->assertJsonPath('message', '聯絡人刪除成功');

        $this->assertSoftDeleted('customer_contacts', [
            'id' => $secondContactId,
        ]);
    }

    public function test_customer_activity_store_and_list_work(): void
    {
        Sanctum::actingAs($this->user);

        $customer = Customer::factory()->create();

        $createResponse = $this->postJson("/api/customers/{$customer->id}/activities", [
            'type' => 'call',
            'subject' => '首次電話拜訪',
            'content' => '已確認需求與預算區間',
            'activity_at' => now()->subDay()->toDateTimeString(),
            'next_action_at' => now()->addDays(3)->toDateTimeString(),
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('message', '活動紀錄建立成功')
            ->assertJsonPath('data.type', 'call')
            ->assertJsonPath('data.user_id', $this->user->id);

        $activityId = (int)$createResponse->json('data.id');

        $this->getJson("/api/customers/{$customer->id}/activities?type=call")
            ->assertOk()
            ->assertJsonPath('total', 1);

        $this->getJson("/api/customers/{$customer->id}/activities/{$activityId}")
            ->assertOk()
            ->assertJsonPath('data.id', $activityId)
            ->assertJsonPath('data.type', 'call');
    }

    public function test_opportunity_crud_and_status_update_work(): void
    {
        Sanctum::actingAs($this->user);

        $customer = Customer::factory()->create();

        $createResponse = $this->postJson('/api/opportunities', $this->opportunityPayload($customer->id));
        $createResponse->assertCreated()
            ->assertJsonPath('message', '商機建立成功')
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.status', 'open');

        $opportunityId = (int)$createResponse->json('data.id');

        $this->getJson('/api/opportunities?search=Q2&status=open&stage=proposal')
            ->assertOk()
            ->assertJsonPath('total', 1);

        $this->patchJson("/api/opportunities/{$opportunityId}/status", [
            'status' => 'won',
        ])->assertOk()
            ->assertJsonPath('message', '商機狀態更新成功')
            ->assertJsonPath('data.status', 'won')
            ->assertJsonPath('data.stage', 'won');

        $this->putJson("/api/opportunities/{$opportunityId}", [
            'name' => '2026 Q2 系統導入案（已簽約）',
            'amount' => 150000,
            'status' => 'won',
            'stage' => 'won',
        ])->assertOk()
            ->assertJsonPath('message', '商機更新成功')
            ->assertJsonPath('data.amount', '150000.00');

        $this->deleteJson("/api/opportunities/{$opportunityId}")
            ->assertOk()
            ->assertJsonPath('message', '商機刪除成功');

        $this->assertSoftDeleted('opportunities', [
            'id' => $opportunityId,
        ]);
    }
}
