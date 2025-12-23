<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockLevel;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockLevel>
 */
class StockLevelFactory extends Factory
{
    protected $model = StockLevel::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(0, 500);
        $reserved = fake()->numberBetween(0, min(50, $qty));

        return [
            'warehouse_id' => Warehouse::factory(),
            'item_id' => Item::factory(),
            'quantity' => $qty,
            'reserved' => $reserved,
            'min_level' => fake()->numberBetween(0, 30),
            'max_level' => fake()->boolean(50) ? fake()->numberBetween(50, 500) : null,
        ];
    }
}
