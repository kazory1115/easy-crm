<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

    public function definition(): array
    {
        $before = fake()->numberBetween(0, 200);
        $after = fake()->numberBetween(0, 200);

        return [
            'warehouse_id' => Warehouse::factory(),
            'item_id' => Item::factory(),
            'before_qty' => $before,
            'after_qty' => $after,
            'reason' => fake()->randomElement(['count', 'damage', 'loss', 'other']),
            'note' => fake()->boolean(30) ? fake()->sentence() : null,
            'created_by' => User::factory(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
