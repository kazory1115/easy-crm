<?php

namespace Database\Factories;

use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportExport>
 */
class ReportExportFactory extends Factory
{
    protected $model = ReportExport::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['queued', 'processing', 'done', 'failed']);

        return [
            'user_id' => User::factory(),
            'report_key' => fake()->randomElement(['quote_summary', 'sales_summary', 'inventory_status']),
            'format' => fake()->randomElement(['csv', 'xlsx', 'pdf']),
            'filters' => fake()->boolean(50) ? ['range' => '30d'] : null,
            'file_path' => $status === 'done' ? 'exports/' . fake()->uuid() . '.xlsx' : null,
            'status' => $status,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'completed_at' => $status === 'done' ? fake()->dateTimeBetween('-30 days', 'now') : null,
        ];
    }
}
