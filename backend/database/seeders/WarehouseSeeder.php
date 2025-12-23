<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        Warehouse::factory()
            ->count(3)
            ->make()
            ->each(function ($warehouse) use ($users) {
                $warehouse->created_by = $users->random()->id;
                $warehouse->updated_by = $users->random()->id;
                $warehouse->save();
            });
    }
}
