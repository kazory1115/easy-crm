<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        $orderDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $dueDate = (clone $orderDate)->modify('+30 days');

        return [
            'order_number' => Order::generateOrderNumber(),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'contact_phone' => $customer->contact_phone,
            'contact_email' => $customer->contact_email,
            'project_name' => $this->faker->sentence(),
            'order_date' => $orderDate,
            'due_date' => $dueDate,
            'subtotal' => $this->faker->randomFloat(2, 100, 1000),
            'tax_amount' => $this->faker->randomFloat(2, 10, 100),
            'discount_amount' => $this->faker->randomFloat(2, 0, 50),
            'total_amount' => $this->faker->randomFloat(2, 110, 1100),
            'tax_rate' => $this->faker->randomFloat(2, 0.05, 0.15),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'shipped', 'completed', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'partially_paid', 'paid']),
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'notes' => $this->faker->paragraph(),
        ];
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'completed_at' => now(),
        ]);
    }
}
