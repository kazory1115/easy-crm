<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departments = ['sales', 'marketing', 'rd', 'hr', 'finance', 'it'];
        $positions = [
            'sales' => ['業務經理', '業務專員', '業務助理'],
            'marketing' => ['行銷經理', '行銷專員', '社群小編'],
            'rd' => ['技術長', '前端工程師', '後端工程師', '全端工程師'],
            'hr' => ['人資經理', '人資專員', '招募專員'],
            'finance' => ['財務經理', '會計', '出納'],
            'it' => ['IT 經理', '系統管理員', 'MIS 工程師']
        ];

        $department = fake()->randomElement($departments);
        $position = fake()->randomElement($positions[$department]);

        return [
            'name' => fake('zh_TW')->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '09' . fake()->numerify('########'),
            'department' => $department,
            'position' => $position,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // 預設密碼: password
            'remember_token' => Str::random(10),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * 建立管理員帳號
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '系統管理員',
            'email' => 'admin@example.com',
            'department' => 'it',
            'position' => 'IT 經理',
        ]);
    }

    /**
     * 建立測試帳號
     */
    public function testUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '測試用戶',
            'email' => 'test@example.com',
            'department' => 'sales',
            'position' => '業務專員',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
