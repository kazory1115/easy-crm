<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Template;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemTemplateFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithAccess(['admin']);
        Sanctum::actingAs($this->user);
    }

    public function test_item_crud_flow(): void
    {
        $storeResponse = $this->postJson('/api/quote-items', [
            'name' => '測試品項',
            'description' => '測試描述',
            'unit' => '式',
            'price' => 1200,
            'quantity' => 2,
            'category' => '服務',
            'status' => 'active',
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('message', '項目建立成功');

        $itemId = $storeResponse->json('data.id');

        $this->putJson("/api/quote-items/{$itemId}", [
            'name' => '更新後品項',
            'price' => 1500,
            'quantity' => 3,
        ])->assertOk()
            ->assertJsonPath('message', '項目更新成功')
            ->assertJsonPath('data.name', '更新後品項');

        $this->deleteJson("/api/quote-items/{$itemId}")
            ->assertOk()
            ->assertJsonPath('message', '項目刪除成功');
    }

    public function test_template_crud_flow(): void
    {
        $storeResponse = $this->postJson('/api/templates', [
            'name' => '測試範本',
            'description' => '測試範本描述',
            'category' => '服務',
            'type' => 'quote',
            'status' => 'active',
            'fields' => [
                [
                    'field_key' => 'spec',
                    'field_label' => '規格',
                    'field_type' => 'text',
                    'field_value' => 'A1',
                    'is_required' => true,
                ],
            ],
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('message', '範本建立成功');

        $templateId = $storeResponse->json('data.id');

        $this->putJson("/api/templates/{$templateId}", [
            'name' => '更新後範本',
            'type' => 'quote',
        ])->assertOk()
            ->assertJsonPath('message', '範本更新成功')
            ->assertJsonPath('data.name', '更新後範本');

        $this->deleteJson("/api/templates/{$templateId}")
            ->assertOk()
            ->assertJsonPath('message', '範本刪除成功');
    }

    public function test_can_get_item_and_template_index(): void
    {
        Item::factory()->count(2)->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Template::factory()->count(2)->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $this->getJson('/api/quote-items?paginate=false')
            ->assertOk()
            ->assertJsonCount(2);

        $this->getJson('/api/templates?paginate=false')
            ->assertOk()
            ->assertJsonCount(2);
    }
}
