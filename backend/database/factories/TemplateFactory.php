<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $templateNames = [
            '標準報價單範本',
            '軟體開發專案範本',
            '網站設計範本',
            '行銷服務範本',
            '顧問服務範本',
            '硬體設備報價範本'
        ];

        return [
            'name' => fake()->randomElement($templateNames),
            'description' => fake('zh_TW')->realText(150),
            'category' => fake()->randomElement(['軟體', '硬體', '服務']),
            'type' => fake()->randomElement(['quote', 'invoice', 'general']),
            'status' => fake()->randomElement(['active', 'inactive']),
            'usage_count' => fake()->numberBetween(0, 100),
            'created_by' => null, // 將在 Seeder 中設定
            'updated_by' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * 設定為預設範本
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '預設報價單範本',
            'status' => 'active',
        ]);
    }
}
