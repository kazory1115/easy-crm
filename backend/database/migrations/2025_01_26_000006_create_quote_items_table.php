<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // quote_items 資料表：儲存報價單中的每一個品項明細
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id()->comment('報價單品項唯一識別碼');
            $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade')->comment('所屬報價單的 ID');
            $table->foreignId('item_id')->nullable()->constrained('items')->onDelete('set null')->comment('關聯的預設品項 ID (如果有的話)');

            $table->integer('sort_order')->default(0)->comment('品項在報價單中的排序');

            // 當時為了快速開發，設計了三種不同的品項輸入方式，未來可以考慮整合成更一致的結構
            $table->enum('type', ['input', 'drop', 'template'])->default('input')
                ->comment('項目類型 (input: 手動輸入, drop: 從品項選擇, template: 使用模板)');

            // 冗餘存儲品項資訊，確保即使原始品項被修改，報價單內容也不會改變
            $table->string('name')->comment('品名規格');
            $table->text('description')->nullable()->comment('描述');
            $table->integer('quantity')->default(1)->comment('數量');
            $table->string('unit')->default('式')->comment('單位');
            $table->decimal('price', 15, 2)->default(0)->comment('單價');
            $table->decimal('amount', 15, 2)->default(0)->comment('複價 (數量 * 單價)');

            $table->json('fields')->nullable()->comment('若類型為 template，這裡儲存模板的欄位資料');
            $table->text('notes')->nullable()->comment('單品項的特別備註');
            $table->timestamps();

            // 索引
            $table->index('quote_id');
            $table->index('item_id');
            $table->index('sort_order');
        });
        DB::statement("ALTER TABLE `quote_items` comment '報價單品項明細表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
