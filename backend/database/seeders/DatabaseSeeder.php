<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('開始建立測試資料...');

            // 0. 角色與權限
            $this->command->info('建立角色與權限...');
            $this->call(RoleAndPermissionSeeder::class);

            // 1. 建立使用者與員工
            $this->command->info('建立使用者..');

            // 管理員
            $admin = User::factory()->admin()->create();
            $admin->assignRole('admin');
            $this->command->info("✅ 管理員: {$admin->email} (密碼: password)");

            // 測試使用者
            $testUser = User::factory()->testUser()->create();
            $testUser->assignRole('staff');
            $this->command->info("✅ 測試使用者: {$testUser->email} (密碼: password)");

            // 其他員工
            User::factory(8)->create();
            $this->command->info('✅ 建立 8 位員工');

            $allUsers = User::all();

            // 2. 建立客戶
            $this->command->info('建立客戶...');
            $customers = Customer::factory(10)->create([
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
            $this->command->info('✅ 建立 10 位客戶');

            // CRM 擴充資料
            $this->command->info('建立 CRM 擴充資料...');
            $this->call([
                CustomerContactSeeder::class,
                CustomerActivitySeeder::class,
                OpportunitySeeder::class,
                OpportunityLogSeeder::class,
            ]);

            // 3. 建立項目/產品
            $this->command->info('建立項目/產品...');
            $items = Item::factory(15)->create([
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
            $this->command->info('✅ 建立 15 個項目');

            // Inventory 擴充資料
            $this->command->info('建立庫存相關資料...');
            $this->call([
                WarehouseSeeder::class,
                StockLevelSeeder::class,
                StockMovementSeeder::class,
                StockAdjustmentSeeder::class,
            ]);

            // 4. 建立報價範本
            $this->command->info('建立報價範本...');

            // 預設範本
            $defaultTemplate = Template::factory()->default()->create([
                'name' => '標準報價範本',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
            $this->command->info("✅ 預設範本: {$defaultTemplate->name}");

            // 其他範本
            Template::factory(4)->create([
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
            $this->command->info('✅ 建立 4 個其他範本');

            // 5. 建立報價單
            $this->command->info('建立報價單...');

            foreach (range(1, 10) as $index) {
                $customer = $customers->random();
                $creator = $allUsers->random();

                $quote = Quote::factory()->create([
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'contact_email' => $customer->email,
                    'contact_phone' => $customer->phone,
                    'created_by' => $creator->id,
                    'updated_by' => $creator->id,
                ]);

                // 每張報價單塞入 2-5 個項目
                $itemCount = rand(2, 5);
                $selectedItems = $items->random($itemCount);

                foreach ($selectedItems as $item) {
                    $quantity = $item->quantity > 0 ? rand(1, $item->quantity) : rand(1, 10);
                    $price = $item->price;

                    QuoteItem::create([
                        'quote_id' => $quote->id,
                        'item_id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'quantity' => $quantity,
                        'unit' => $item->unit,
                        'price' => $price,
                        'amount' => $quantity * $price,
                        'sort_order' => 0,
                    ]);
                }

                // 重新載入後計算報價單總額
                $quote->refresh();
                $quote->recalculateTotal();

                $this->command->info("✅ 報價單 #{$quote->quote_number} ({$quote->status})");
            }

            // 6. 報表匯出紀錄
            $this->call(ReportExportSeeder::class);

            $this->command->info('');
            $this->command->info('========================================');
            $this->command->info('測試資料建立完成！');
            $this->command->info('========================================');
            $this->command->info('');
            $this->command->info('登入資訊：');
            $this->command->info("管理員: {$admin->email} / password");
            $this->command->info("測試使用者: {$testUser->email} / password");
            $this->command->info('');
            $this->command->info('資料統計：');
            $this->command->info('- 使用者: ' . User::count() . ' 位');
            $this->command->info('- 客戶: ' . Customer::count() . ' 位');
            $this->command->info('- 項目: ' . Item::count() . ' 筆');
            $this->command->info('- 範本: ' . Template::count() . ' 筆');
            $this->command->info('- 報價單: ' . Quote::count() . ' 張');
            $this->command->info('- 報價單項目: ' . QuoteItem::count() . ' 筆');
            $this->command->info('========================================');
        });
    }
}
