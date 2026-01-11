<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);

        return [
            'order_id' => Order::factory(),
            'item_id' => Item::factory(),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement(['input', 'drop', 'template']),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'quantity' => $quantity,
            'unit'            => $this->faker->randomElement(['pcs', 'kg', 'box']),
            'unit_price' => $unitPrice,
            'subtotal' => round($quantity * $unitPrice, 2),
            'fields' => null,
            'notes' => null,
        ];
    }
}
