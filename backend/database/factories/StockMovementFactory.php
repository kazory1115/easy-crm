<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'item_id' => Item::factory(),
            'type' => fake()->randomElement(['inbound', 'outbound', 'transfer', 'adjustment']),
            'quantity' => fake()->numberBetween(1, 100),
            'reference_type' => fake()->boolean(40) ? 'manual' : null,
            'reference_id' => null,
            'note' => fake()->boolean(30) ? fake()->sentence() : null,
            'occurred_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'created_by' => User::factory(),
        ];
    }
}
