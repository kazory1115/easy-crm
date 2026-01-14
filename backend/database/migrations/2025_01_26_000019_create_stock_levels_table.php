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
     * 建立庫存水平表，用於記錄每個倉庫中各個品項的即時庫存數量及相關設定。
     */
    public function up(): void
    {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id()->comment('庫存水平唯一識別碼');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade')->comment('所屬倉庫的 ID');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade')->comment('所屬品項的 ID');

            $table->integer('quantity')->default(0)->comment('當前在庫數量');
            $table->integer('reserved')->default(0)->comment('已預留數量（例如已被訂單佔用）');
            $table->integer('min_level')->default(0)->comment('最低庫存量警示線');
            $table->integer('max_level')->nullable()->comment('最高庫存量建議值');
            $table->timestamps();

            // 複合唯一索引，確保每個倉庫的每個品項只有一條庫存記錄
            $table->unique(['warehouse_id', 'item_id']);
            // 針對 item_id 建立索引，方便查詢某個品項在所有倉庫的庫存情況
            $table->index('item_id');
        });
        DB::statement("ALTER TABLE `stock_levels` comment '品項庫存水平表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
