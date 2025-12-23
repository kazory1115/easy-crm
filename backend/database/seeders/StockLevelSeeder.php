<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\StockLevel;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockLevelSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $items = Item::all();

        if ($warehouses->isEmpty() || $items->isEmpty()) {
            return;
        }

        foreach ($warehouses as $warehouse) {
            $selected = $items->random(min(10, $items->count()));
            foreach ($selected as $item) {
                StockLevel::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'item_id' => $item->id,
                ]);
            }
        }
    }
}
