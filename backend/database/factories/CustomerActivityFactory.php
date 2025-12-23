<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerActivity>
 */
class CustomerActivityFactory extends Factory
{
    protected $model = CustomerActivity::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['call', 'email', 'meeting', 'note', 'follow_up', 'other']),
            'subject' => fake()->sentence(4),
            'content' => fake()->boolean(70) ? fake()->paragraph() : null,
            'activity_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'next_action_at' => fake()->boolean(40) ? fake()->dateTimeBetween('now', '+30 days') : null,
        ];
    }
}
