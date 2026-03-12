<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWithAccess(['admin']);
    }

    private function createCustomer(): Customer
    {
        return Customer::factory()->create([
            'name' => '測試客戶有限公司',
        ]);
    }

    private function orderPayload(int $customerId, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customerId,
            'order_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'project_name' => '測試訂單專案',
            'notes' => '測試建立訂單',
            'tax_rate' => 0.05,
            'items' => [
                [
                    'name' => '測試品項 A',
                    'quantity' => 2,
                    'unit' => '式',
                    'unit_price' => 100,
                ],
            ],
        ], $overrides);
    }

    public function test_unauthorized_user_cannot_create_order(): void
    {
        $customer = $this->createCustomer();

        $this->postJson('/api/orders', $this->orderPayload($customer->id))
            ->assertUnauthorized();
    }

    public function test_can_create_order_with_items_and_logs(): void
    {
        Sanctum::actingAs($this->user);
        $customer = $this->createCustomer();

        $response = $this->postJson('/api/orders', $this->orderPayload($customer->id));

        $response->assertCreated()
            ->assertJsonPath('message', '訂單建立成功')
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.payment_status', 'unpaid');

        $orderId = $response->json('data.id');
        $order = Order::with('items')->findOrFail($orderId);

        $this->assertSame(1, $order->items->count());
        $this->assertEquals(200.00, (float)$order->subtotal);
        $this->assertEquals(10.00, (float)$order->tax_amount);
        $this->assertEquals(210.00, (float)$order->total_amount);

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        $this->assertDatabaseHas('order_logs', [
            'order_id' => $orderId,
            'action' => 'created',
        ]);
    }

    public function test_create_order_validation_fail(): void
    {
        Sanctum::actingAs($this->user);
        $customer = $this->createCustomer();

        $payload = $this->orderPayload($customer->id, [
            'items' => [],
        ]);

        $this->postJson('/api/orders', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_can_update_order_and_replace_items(): void
    {
        Sanctum::actingAs($this->user);
        $customer = $this->createCustomer();

        $createResponse = $this->postJson('/api/orders', $this->orderPayload($customer->id));
        $createResponse->assertCreated();
        $orderId = $createResponse->json('data.id');

        $updatePayload = [
            'project_name' => '更新後專案名稱',
            'status' => 'processing',
            'payment_status' => 'partially_paid',
            'tax_rate' => 0.1,
            'items' => [
                [
                    'name' => '更新品項 A',
                    'quantity' => 3,
                    'unit' => '式',
                    'unit_price' => 120,
                ],
                [
                    'name' => '更新品項 B',
                    'quantity' => 1,
                    'unit' => '式',
                    'unit_price' => 80,
                ],
            ],
        ];

        $response = $this->putJson("/api/orders/{$orderId}", $updatePayload);

        $response->assertOk()
            ->assertJsonPath('message', '訂單更新成功')
            ->assertJsonPath('data.status', 'processing')
            ->assertJsonPath('data.payment_status', 'partially_paid')
            ->assertJsonPath('data.project_name', '更新後專案名稱');

        $order = Order::with('items')->findOrFail($orderId);
        $this->assertSame(2, $order->items->count());
        $this->assertEquals(440.00, (float)$order->subtotal);
        $this->assertEquals(44.00, (float)$order->tax_amount);
        $this->assertEquals(484.00, (float)$order->total_amount);

        $this->assertDatabaseHas('order_logs', [
            'order_id' => $orderId,
            'action' => 'updated',
        ]);
    }

    public function test_update_order_validation_fail(): void
    {
        Sanctum::actingAs($this->user);
        $customer = $this->createCustomer();

        $createResponse = $this->postJson('/api/orders', $this->orderPayload($customer->id));
        $createResponse->assertCreated();
        $orderId = $createResponse->json('data.id');

        $this->putJson("/api/orders/{$orderId}", [
            'payment_status' => 'invalid_status',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['payment_status']);
    }

    public function test_create_order_rolls_back_when_item_overflow(): void
    {
        Sanctum::actingAs($this->user);
        $customer = $this->createCustomer();

        $payload = $this->orderPayload($customer->id, [
            'items' => [
                [
                    'name' => '正常品項',
                    'quantity' => 1,
                    'unit' => '式',
                    'unit_price' => 100,
                ],
                [
                    'name' => '超限品項',
                    'quantity' => 1,
                    'unit' => '式',
                    'unit_price' => 100000000,
                ],
            ],
        ]);

        $this->postJson('/api/orders', $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', '訂單建立失敗');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('order_logs', 0);
    }

    public function test_quote_convert_and_direct_create_use_consistent_order_structure(): void
    {
        Sanctum::actingAs($this->user);
        $customer = $this->createCustomer();

        $quote = Quote::factory()->approved()->create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
            'discount' => 10,
        ]);

        QuoteItem::create([
            'quote_id' => $quote->id,
            'item_id' => null,
            'sort_order' => 1,
            'type' => 'input',
            'name' => '轉單品項',
            'quantity' => 2,
            'unit' => '式',
            'price' => 150,
        ]);

        $convertResponse = $this->postJson("/api/quotes/{$quote->id}/convert-to-order");
        $convertResponse->assertCreated()
            ->assertJsonPath('message', '報價單已成功轉換為訂單');

        $convertedOrder = Order::where('quote_id', $quote->id)->with('items')->firstOrFail();
        $convertedItem = $convertedOrder->items->first();

        $directCreateResponse = $this->postJson('/api/orders', $this->orderPayload($customer->id, [
            'items' => [
                [
                    'name' => '直接建立品項',
                    'quantity' => 2,
                    'unit' => '式',
                    'unit_price' => 150,
                ],
            ],
        ]));
        $directCreateResponse->assertCreated();

        $directOrder = Order::findOrFail($directCreateResponse->json('data.id'));

        $this->assertNotNull($convertedOrder->order_number);
        $this->assertNotNull($directOrder->order_number);
        $this->assertNotNull($convertedOrder->created_by);
        $this->assertNotNull($directOrder->created_by);
        $this->assertSame('confirmed', $convertedOrder->status);
        $this->assertSame('pending', $directOrder->status);
        $this->assertNotNull($convertedItem);
        $this->assertNotNull($convertedItem->unit_price);
        $this->assertNotNull($convertedItem->subtotal);
    }
}
