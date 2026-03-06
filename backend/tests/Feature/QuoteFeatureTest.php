<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QuoteFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function quotePayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => '測試客戶',
            'contact_phone' => '0912345678',
            'contact_email' => 'customer@example.com',
            'project_name' => '測試專案',
            'quote_date' => now()->toDateString(),
            'notes' => '測試備註',
            'items' => [
                [
                    'type' => 'input',
                    'name' => '測試項目',
                    'description' => '測試描述',
                    'quantity' => 2,
                    'unit' => '式',
                    'price' => 100,
                ],
            ],
        ], $overrides);
    }

    public function test_unauthorized_user_cannot_access_quote_index(): void
    {
        $this->getJson('/api/quotes')->assertUnauthorized();
    }

    public function test_can_get_quote_index(): void
    {
        Sanctum::actingAs($this->user);

        Quote::factory()->count(2)->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/quotes');

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'total'])
            ->assertJsonPath('total', 2);
    }

    public function test_can_store_quote(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/quotes', $this->quotePayload());

        $response->assertCreated()
            ->assertJsonPath('message', '報價單建立成功')
            ->assertJsonPath('data.customer_name', '測試客戶');

        $this->assertDatabaseHas('quotes', [
            'customer_name' => '測試客戶',
            'created_by' => $this->user->id,
        ]);
        $this->assertDatabaseCount('quote_items', 1);
    }

    public function test_can_update_quote(): void
    {
        Sanctum::actingAs($this->user);

        $quote = Quote::factory()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'type' => 'input',
            'name' => '舊項目',
            'quantity' => 1,
            'unit' => '式',
            'price' => 50,
            'sort_order' => 1,
        ]);

        $payload = $this->quotePayload([
            'customer_name' => '更新後客戶',
            'items' => [
                [
                    'type' => 'input',
                    'name' => '更新後項目',
                    'quantity' => 3,
                    'unit' => '式',
                    'price' => 200,
                ],
            ],
        ]);

        $response = $this->putJson("/api/quotes/{$quote->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('message', '報價單更新成功')
            ->assertJsonPath('data.customer_name', '更新後客戶');

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'customer_name' => '更新後客戶',
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_can_delete_quote(): void
    {
        Sanctum::actingAs($this->user);

        $quote = Quote::factory()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->deleteJson("/api/quotes/{$quote->id}")
            ->assertOk()
            ->assertJsonPath('message', '報價單刪除成功');

        $this->assertSoftDeleted('quotes', ['id' => $quote->id]);
    }

    public function test_can_update_quote_status(): void
    {
        Sanctum::actingAs($this->user);

        $quote = Quote::factory()->draft()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $response = $this->patchJson("/api/quotes/{$quote->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', '狀態更新成功')
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => 'approved',
        ]);
    }
}

