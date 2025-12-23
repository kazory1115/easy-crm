<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerContact>
 */
class CustomerContactFactory extends Factory
{
    protected $model = CustomerContact::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->name(),
            'title' => fake()->jobTitle(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'is_primary' => fake()->boolean(20),
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
