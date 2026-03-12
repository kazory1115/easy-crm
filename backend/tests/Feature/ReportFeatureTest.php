<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Order;
use App\Models\Quote;
use App\Models\ReportExport;
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
        $this->user = $this->createUserWithAccess(['admin']);
    }

    public function test_unauthorized_user_cannot_access_report_api(): void
    {
        $this->getJson('/api/reports/dashboard')->assertUnauthorized();
        $this->getJson('/api/reports/exports')->assertUnauthorized();
        $this->postJson('/api/reports/exports', [])->assertUnauthorized();
    }

    public function test_dashboard_returns_quote_order_inventory_and_export_stats(): void
    {
        Sanctum::actingAs($this->user);

        Quote::factory()->create([
            'quote_date' => now()->toDateString(),
            'status' => 'approved',
            'total' => 8000,
        ]);

        Quote::factory()->create([
            'quote_date' => now()->subDays(2)->toDateString(),
            'status' => 'draft',
            'total' => 2500,
        ]);

        Order::factory()->create([
            'order_date' => now()->toDateString(),
            'status' => 'completed',
            'payment_status' => 'paid',
            'total_amount' => 16800,
        ]);

        $warehouse = Warehouse::factory()->create(['status' => 'active']);
        $item = Item::factory()->create(['status' => 'active', 'quantity' => 20]);

        StockLevel::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 5,
            'reserved' => 1,
            'min_level' => 10,
        ]);

        StockMovement::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'type' => 'outbound',
            'quantity' => 4,
            'occurred_at' => now()->subDay(),
            'created_by' => $this->user->id,
        ]);

        ReportExport::factory()->create([
            'user_id' => $this->user->id,
            'report_key' => 'quote_summary',
            'format' => 'xlsx',
            'status' => 'done',
            'created_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
        ]);

        $this->getJson('/api/reports/dashboard?range_days=30')
            ->assertOk()
            ->assertJsonPath('data.quote.summary.total', 2)
            ->assertJsonPath('data.quote.summary.approved', 1)
            ->assertJsonPath('data.order.summary.total', 1)
            ->assertJsonPath('data.order.summary.completed', 1)
            ->assertJsonPath('data.inventory.summary.warehouses', 1)
            ->assertJsonPath('data.inventory.summary.low_stock_count', 1)
            ->assertJsonPath('data.exports.summary.total', 1)
            ->assertJsonCount(1, 'data.exports.recent_records');
    }

    public function test_export_records_can_be_listed_and_created(): void
    {
        Sanctum::actingAs($this->user);

        ReportExport::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'report_key' => 'order_summary',
            'format' => 'xlsx',
        ]);

        $this->getJson('/api/reports/exports?report_key=order_summary&per_page=15')
            ->assertOk()
            ->assertJsonPath('total', 2);

        $this->postJson('/api/reports/exports', [
            'report_key' => 'inventory_snapshot',
            'format' => 'csv',
            'filters' => [
                'source' => 'report_export_list',
                'range_days' => 30,
            ],
        ])->assertCreated()
            ->assertJsonPath('message', '匯出任務已建立')
            ->assertJsonPath('data.report_key', 'inventory_snapshot')
            ->assertJsonPath('data.format', 'csv')
            ->assertJsonPath('data.status', 'queued')
            ->assertJsonPath('data.user.id', $this->user->id);

        $this->assertDatabaseHas('report_exports', [
            'report_key' => 'inventory_snapshot',
            'format' => 'csv',
            'status' => 'queued',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_export_record_validation_returns_422(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/reports/exports', [
            'report_key' => '',
            'format' => 'docx',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['report_key', 'format']);
    }
}
