<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Warehouse',
            'code' => strtoupper(fake()->bothify('WH-###')),
            'location' => fake()->city(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
