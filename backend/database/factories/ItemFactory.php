<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['軟體開發', '網站設計', '行銷服務', '顧問諮詢', '硬體設備', '維護服務'];
        $units = ['件', '台', '組', '套', '月', '年', '小時', '天', '式'];

        $productNames = [
            '軟體開發' => ['客製化系統開發', 'APP 開發', 'API 整合服務', '系統維護服務'],
            '網站設計' => ['企業官網設計', '電商網站建置', 'RWD 響應式網頁', 'UI/UX 設計'],
            '行銷服務' => ['SEO 優化服務', '社群媒體行銷', 'Google 廣告投放', '內容行銷'],
            '顧問諮詢' => ['數位轉型顧問', 'IT 架構規劃', '資安顧問服務', '專案管理顧問'],
            '硬體設備' => ['伺服器主機', '網路設備', '筆記型電腦', '桌上型電腦'],
            '維護服務' => ['系統維護合約', '設備保固服務', '24/7 技術支援', '遠端監控服務']
        ];

        $category = fake()->randomElement($categories);
        $name = fake()->randomElement($productNames[$category]);
        $price = fake()->randomElement([5000, 10000, 15000, 20000, 25000, 30000, 50000, 80000, 100000, 150000]);

        return [
            'code' => 'ITEM' . fake()->unique()->numerify('######'),
            'name' => $name,
            'description' => fake('zh_TW')->realText(200),
            'unit' => fake()->randomElement($units),
            'price' => $price,
            'quantity' => fake()->numberBetween(1, 10),
            'category' => $category,
            'specifications' => fake()->boolean(60) ? json_encode([
                '規格' => fake()->words(3, true),
                '交付時間' => fake()->numberBetween(1, 30) . ' 個工作天',
                '保固期限' => fake()->randomElement(['無', '1年', '2年', '3年'])
            ]) : null,
            'status' => fake()->randomElement(['active', 'inactive']),
            'created_by' => null, // 將在 Seeder 中設定
            'updated_by' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
