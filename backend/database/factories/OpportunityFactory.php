<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        $stage = fake()->randomElement(['new', 'qualified', 'proposal', 'negotiation', 'won', 'lost']);
        $status = in_array($stage, ['won', 'lost'], true) ? $stage : 'open';

        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->sentence(3),
            'stage' => $stage,
            'amount' => fake()->numberBetween(10000, 500000),
            'expected_close_date' => fake()->boolean(70) ? fake()->dateTimeBetween('now', '+6 months') : null,
            'status' => $status,
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
