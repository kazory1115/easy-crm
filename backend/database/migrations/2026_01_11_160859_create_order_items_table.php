<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 建立訂單品項明細表，用於記錄每張訂單中包含的具體商品或服務。
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id()->comment('訂單品項唯一識別碼');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->comment('所屬訂單的 ID');
            $table->foreignId('item_id')->nullable()->constrained('items')->onDelete('set null')->comment('關聯的品項 ID (如果品項是從預設品項庫中選擇)');

            $table->integer('sort_order')->default(0)->comment('品項在訂單中的顯示順序');
            $table->string('type')->default('input')->comment('品項類型 (input: 手動輸入, drop: 從品項庫選擇, template: 使用範本)');
            $table->string('name')->comment('品名或服務名稱');
            $table->text('description')->nullable()->comment('詳細描述');
            $table->integer('quantity')->comment('購買數量');
            $table->string('unit')->nullable()->comment('計量單位');
            $table->decimal('unit_price', 10, 2)->comment('商品單價');
            $table->decimal('subtotal', 10, 2)->comment('品項小計 (數量 * 單價)');

            $table->json('fields')->nullable()->comment('額外欄位或配置 (JSON 格式)'); // For custom fields/configurations
            $table->text('notes')->nullable()->comment('單個品項的備註');
            $table->timestamps();

            // 索引
            $table->index('order_id');
            $table->index('item_id');
            $table->index('sort_order');
        });
        DB::statement("ALTER TABLE `order_items` comment '訂單品項明細表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
