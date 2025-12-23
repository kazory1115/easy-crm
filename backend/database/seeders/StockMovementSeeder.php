<?php

namespace Database\Seeders;

use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $levels = StockLevel::all();
        $users = User::all();

        if ($levels->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($levels as $level) {
            StockMovement::factory()
                ->count(rand(1, 3))
                ->make([
                    'warehouse_id' => $level->warehouse_id,
                    'item_id' => $level->item_id,
                    'created_by' => $users->random()->id,
                ])
                ->each->save();
        }
    }
}
