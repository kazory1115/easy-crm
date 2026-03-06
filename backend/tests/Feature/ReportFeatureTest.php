<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Order;
use App\Models\Quote;
use App\Models\ReportExport;
use App\Models\StockAdjustment;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportFeatureTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_unauthorized_user_cannot_access_report_api(): void
    {
        $this->getJson('/api/reports/dashboard')->assertUnauthorized();
        $this->getJson('/api/reports/exports')->assertUnauthorized();
        $this->postJson('/api/reports/exports', [
            'report_key' => 'quote_summary',
            'format' => 'xlsx',
        ])->assertUnauthorized();
    }

    public function test_dashboard_returns_aggregated_data(): void
    {
        Sanctum::actingAs($this->user);

        Quote::factory()->approved()->create([
            'total' => 1200,
            'quote_date' => now()->subDays(2)->toDateString(),
        ]);
        Quote::factory()->draft()->create([
            'total' => 500,
            'quote_date' => now()->subDay()->toDateString(),
        ]);

        Order::factory()->create([
            'total_amount' => 3600,
            'status' => 'completed',
            'payment_status' => 'paid',
            'order_date' => now()->subDay()->toDateString(),
        ]);

        $warehouse = Warehouse::factory()->create(['status' => 'active']);
        $item = Item::factory()->create(['name' => '主力商品', 'status' => 'active']);

        StockLevel::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 4,
            'reserved' => 1,
            'min_level' => 5,
        ]);

        StockMovement::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'type' => 'inbound',
            'quantity' => 8,
            'occurred_at' => now()->subHours(6),
            'created_by' => $this->user->id,
        ]);

        StockAdjustment::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'before_qty' => 6,
            'after_qty' => 4,
            'created_by' => $this->user->id,
            'created_at' => now()->subHours(3),
        ]);

        ReportExport::factory()->create([
            'user_id' => $this->user->id,
            'report_key' => 'quote_summary',
            'status' => 'done',
            'created_at' => now()->subDay(),
            'completed_at' => now()->subHours(20),
        ]);

        $response = $this->getJson('/api/reports/dashboard?range_days=30');

        $response->assertOk()
            ->assertJsonPath('data.quote.summary.total', 2)
            ->assertJsonPath('data.quote.summary.approved', 1)
            ->assertJsonPath('data.quote.summary.total_amount', 1700)
            ->assertJsonPath('data.order.summary.total', 1)
            ->assertJsonPath('data.order.summary.completed', 1)
            ->assertJsonPath('data.order.summary.total_amount', 3600)
            ->assertJsonPath('data.inventory.summary.warehouses', 1)
            ->assertJsonPath('data.inventory.summary.low_stock_count', 1)
            ->assertJsonPath('data.inventory.low_stock_items.0.item_name', '主力商品')
            ->assertJsonPath('data.exports.summary.done', 1)
            ->assertJsonPath('data.exports.recent_records.0.report_key', 'quote_summary');
    }

    public function test_export_records_can_be_listed_and_created(): void
    {
        Sanctum::actingAs($this->user);

        ReportExport::factory()->create([
            'user_id' => $this->user->id,
            'report_key' => 'inventory_snapshot',
            'format' => 'csv',
            'status' => 'queued',
        ]);

        $this->getJson('/api/reports/exports?report_key=inventory_snapshot&status=queued')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.report_key', 'inventory_snapshot')
            ->assertJsonPath('data.0.status', 'queued');

        $this->postJson('/api/reports/exports', [
            'report_key' => 'order_summary',
            'format' => 'xlsx',
            'filters' => [
                'range_days' => 90,
                'status' => 'completed',
            ],
        ])->assertCreated()
            ->assertJsonPath('message', '匯出任務已建立')
            ->assertJsonPath('data.report_key', 'order_summary')
            ->assertJsonPath('data.status', 'queued')
            ->assertJsonPath('data.user.id', $this->user->id);

        $this->assertDatabaseHas('report_exports', [
            'user_id' => $this->user->id,
            'report_key' => 'order_summary',
            'format' => 'xlsx',
            'status' => 'queued',
        ]);
    }
}
