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
        // items 資料表：儲存所有可供報價或訂單使用的品項資料
        Schema::create('items', function (Blueprint $table) {
            $table->id()->comment('品項唯一識別碼');
            $table->string('name')->comment('品名或服務名稱');
            $table->string('code')->unique()->comment('品項的唯一代碼，方便系統識別');

            $table->text('description')->nullable()->comment('詳細描述');
            $table->string('unit')->default('式')->comment('計量單位 (例如: 個, 份, 式)');
            $table->decimal('price', 15, 2)->default(0)->comment('標準單價');
            $table->integer('quantity')->default(1)->comment('加入報價或訂單時的預設數量');

            $table->string('category')->nullable()->comment('品項分類');
            $table->json('specifications')->nullable()->comment('儲存額外的規格資訊 (JSON 格式)');

            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態 (active: 可用, inactive: 停用)');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立此品項的使用者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新此品項的使用者 ID');

            $table->timestamps();
            $table->softDeletes();

            // 常用查詢欄位，建立索引以優化查詢效能
            $table->index('name');
            $table->index('category');
            $table->index('status');
        });

        DB::statement("ALTER TABLE `items` comment '銷售品項資料表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
