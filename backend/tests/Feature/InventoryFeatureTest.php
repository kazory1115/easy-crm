<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StockLevel;
use App\Models\User;
use App\Models\Warehouse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWithAccess(['admin']);
    }

    private function warehousePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => '台北主倉',
            'code' => 'WH-TPE-MAIN',
            'location' => '台北市內湖區',
            'status' => 'active',
        ], $overrides);
    }

    public function test_unauthorized_user_cannot_access_inventory_api(): void
    {
        $this->getJson('/api/warehouses')->assertUnauthorized();
        $this->getJson('/api/stock-levels')->assertUnauthorized();
    }

    public function test_warehouse_crud_and_stock_level_query_work(): void
    {
        Sanctum::actingAs($this->user);

        $createResponse = $this->postJson('/api/warehouses', $this->warehousePayload());
        $createResponse->assertCreated()
            ->assertJsonPath('message', '倉庫建立成功')
            ->assertJsonPath('data.code', 'WH-TPE-MAIN');

        $warehouseId = (int) $createResponse->json('data.id');

        $this->putJson("/api/warehouses/{$warehouseId}", [
            'location' => '台北市南港區',
            'status' => 'inactive',
        ])->assertOk()
            ->assertJsonPath('message', '倉庫更新成功')
            ->assertJsonPath('data.status', 'inactive');

        $item = Item::factory()->create([
            'name' => '測試庫存品項',
            'status' => 'active',
        ]);

        StockLevel::factory()->create([
            'warehouse_id' => $warehouseId,
            'item_id' => $item->id,
            'quantity' => 12,
            'reserved' => 2,
            'min_level' => 5,
        ]);

        $this->getJson('/api/warehouses?search=台北主倉&status=inactive')
            ->assertOk()
            ->assertJsonPath('total', 1);

        $this->getJson("/api/stock-levels?warehouse_id={$warehouseId}&item_id={$item->id}")
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.quantity', 12)
            ->assertJsonPath('data.0.available_quantity', 10);

        $emptyWarehouseResponse = $this->postJson('/api/warehouses', $this->warehousePayload([
            'name' => '高雄備援倉',
            'code' => 'WH-KHH-BACKUP',
        ]));

        $emptyWarehouseId = (int) $emptyWarehouseResponse->json('data.id');

        $this->deleteJson("/api/warehouses/{$emptyWarehouseId}")
            ->assertOk()
            ->assertJsonPath('message', '倉庫刪除成功');
    }

    public function test_inbound_stock_movement_updates_stock_level_and_item_total(): void
    {
        Sanctum::actingAs($this->user);

        $warehouse = Warehouse::factory()->create(['status' => 'active']);
        $item = Item::factory()->create([
            'quantity' => 0,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/stock-movements', [
            'type' => 'inbound',
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 15,
            'note' => '首次入庫',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', '入庫成功')
            ->assertJsonPath('data.type', 'inbound')
            ->assertJsonPath('data.quantity', 15);

        $this->assertDatabaseHas('stock_levels', [
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 15,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'quantity' => 15,
        ]);
    }

    public function test_transfer_stock_updates_source_and_target_levels(): void
    {
        Sanctum::actingAs($this->user);

        $sourceWarehouse = Warehouse::factory()->create([
            'name' => '來源倉',
            'status' => 'active',
        ]);
        $targetWarehouse = Warehouse::factory()->create([
            'name' => '目標倉',
            'status' => 'active',
        ]);
        $item = Item::factory()->create([
            'quantity' => 23,
            'status' => 'active',
        ]);

        StockLevel::factory()->create([
            'warehouse_id' => $sourceWarehouse->id,
            'item_id' => $item->id,
            'quantity' => 20,
            'reserved' => 0,
        ]);

        StockLevel::factory()->create([
            'warehouse_id' => $targetWarehouse->id,
            'item_id' => $item->id,
            'quantity' => 3,
            'reserved' => 0,
        ]);

        $response = $this->postJson('/api/stock-movements', [
            'type' => 'transfer',
            'item_id' => $item->id,
            'source_warehouse_id' => $sourceWarehouse->id,
            'target_warehouse_id' => $targetWarehouse->id,
            'quantity' => 7,
            'note' => '主倉調撥到門市倉',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', '庫存調撥成功')
            ->assertJsonPath('data.source_stock_level.quantity', 13)
            ->assertJsonPath('data.target_stock_level.quantity', 10);

        $this->assertDatabaseHas('stock_levels', [
            'warehouse_id' => $sourceWarehouse->id,
            'item_id' => $item->id,
            'quantity' => 13,
        ]);

        $this->assertDatabaseHas('stock_levels', [
            'warehouse_id' => $targetWarehouse->id,
            'item_id' => $item->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $sourceWarehouse->id,
            'item_id' => $item->id,
            'type' => 'transfer',
            'quantity' => -7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $targetWarehouse->id,
            'item_id' => $item->id,
            'type' => 'transfer',
            'quantity' => 7,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'quantity' => 23,
        ]);
    }

    public function test_adjustment_updates_stock_and_creates_adjustment_record(): void
    {
        Sanctum::actingAs($this->user);

        $warehouse = Warehouse::factory()->create(['status' => 'active']);
        $item = Item::factory()->create([
            'quantity' => 8,
            'status' => 'active',
        ]);

        StockLevel::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 8,
            'reserved' => 0,
        ]);

        $response = $this->postJson('/api/stock-adjustments', [
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'after_qty' => 5,
            'reason' => 'count',
            'note' => '盤點修正',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', '庫存調整成功')
            ->assertJsonPath('data.adjustment.before_qty', 8)
            ->assertJsonPath('data.adjustment.after_qty', 5)
            ->assertJsonPath('data.stock_level.quantity', 5)
            ->assertJsonPath('data.movement.quantity', -3);

        $this->assertDatabaseHas('stock_adjustments', [
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'before_qty' => 8,
            'after_qty' => 5,
            'reason' => 'count',
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'quantity' => 5,
        ]);
    }

    public function test_outbound_stock_movement_rolls_back_when_stock_is_insufficient(): void
    {
        Sanctum::actingAs($this->user);

        $warehouse = Warehouse::factory()->create(['status' => 'active']);
        $item = Item::factory()->create([
            'quantity' => 3,
            'status' => 'active',
        ]);

        StockLevel::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 3,
            'reserved' => 0,
        ]);

        $this->postJson('/api/stock-movements', [
            'type' => 'outbound',
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 5,
            'note' => '超額出庫',
        ])->assertStatus(422)
            ->assertJsonPath('message', '庫存異動失敗');

        $this->assertDatabaseHas('stock_levels', [
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseMissing('stock_movements', [
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'note' => '超額出庫',
        ]);
    }
}
