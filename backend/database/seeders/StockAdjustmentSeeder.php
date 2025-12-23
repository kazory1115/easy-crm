<?php

namespace Database\Seeders;

use App\Models\StockAdjustment;
use App\Models\StockLevel;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $levels = StockLevel::all();
        $users = User::all();

        if ($levels->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($levels as $level) {
            if (rand(1, 100) > 30) {
                continue;
            }

            StockAdjustment::factory()->create([
                'warehouse_id' => $level->warehouse_id,
                'item_id' => $level->item_id,
                'created_by' => $users->random()->id,
            ]);
        }
    }
}
