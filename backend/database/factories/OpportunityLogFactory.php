<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\OpportunityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OpportunityLog>
 */
class OpportunityLogFactory extends Factory
{
    protected $model = OpportunityLog::class;

    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'restored']),
            'old_data' => null,
            'new_data' => null,
            'description' => fake()->boolean(40) ? fake()->sentence() : null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => fake()->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
