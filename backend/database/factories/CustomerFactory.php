<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyTypes = ['股份有限公司', '有限公司', '工作室', '商行', '企業社'];
        $industries = ['科技業', '製造業', '服務業', '零售業', '餐飲業', '建築業', '金融業'];

        $isCompany = fake()->boolean(70); // 70% 機率是公司

        if ($isCompany) {
            $name = fake('zh_TW')->company() . fake()->randomElement($companyTypes);
            $taxId = fake()->numerify('########'); // 統一編號
        } else {
            $name = fake('zh_TW')->name();
            $taxId = null;
        }

        return [
            'name' => $name,
            'contact_person' => fake('zh_TW')->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->boolean(70) ? '02-' . fake()->numerify('########') : '09' . fake()->numerify('########'),
            'mobile' => '09' . fake()->numerify('########'),
            'tax_id' => $taxId,
            'address' => fake('zh_TW')->address(),
            'website' => fake()->boolean(40) ? fake()->url() : null,
            'industry' => fake()->randomElement($industries),
            'status' => fake()->randomElement(['active', 'inactive']),
            'notes' => fake()->boolean(30) ? fake('zh_TW')->realText(100) : null,
            'created_by' => null, // 將在 Seeder 中設定
            'updated_by' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
