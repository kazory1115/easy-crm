<?php

namespace Database\Factories;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-6 months', 'now');
        $validUntil = (clone $createdAt)->modify('+30 days');

        return [
            // quote_number 會由 Model 自動生成
            'customer_id' => null, // 將在 Seeder 中設定
            'customer_name' => fake('zh_TW')->company() . '有限公司',
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => '02-' . fake()->numerify('########'),

            'project_name' => fake()->randomElement([
                '企業官網建置專案',
                '電商平台開發案',
                'CRM 系統客製化',
                'APP 開發專案',
                '數位行銷服務合約',
                'IT 顧問服務專案'
            ]),
            'quote_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),

            'subtotal' => 0, // 將在加入 items 後計算
            'tax' => 0,
            'discount' => fake()->randomElement([0, 5, 10, 15]),
            'total' => 0,

            'status' => fake()->randomElement(['draft', 'sent', 'approved', 'rejected', 'expired']),
            'valid_until' => $validUntil,

            'notes' => fake()->boolean(40) ? fake('zh_TW')->realText(100) : '1. 報價有效期限如上所示\n2. 價格不含稅\n3. 付款方式：簽約後付款50%，驗收後付款50%\n4. 交付時間以簽約日起算',

            'created_by' => null, // 將在 Seeder 中設定
            'updated_by' => null,
            'created_at' => $createdAt,
            'updated_at' => now(),
        ];
    }

    /**
     * 設定為草稿狀態
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * 設定為已發送狀態
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    /**
     * 設定為已核准狀態
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }
}
