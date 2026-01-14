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
     * 建立庫存調整表，用於記錄因盤點、損壞、報廢等原因導致的庫存數量變動。
     * 與 stock_movements 不同，這是專門針對調整動作的記錄。
     */
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id()->comment('庫存調整紀錄唯一識別碼');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade')->comment('發生調整的倉庫 ID');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade')->comment('被調整的品項 ID');

            $table->integer('before_qty')->comment('調整前的庫存數量');
            $table->integer('after_qty')->comment('調整後的庫存數量');
            $table->string('reason')->nullable()->comment('調整原因 (例如: 盤虧, 盤盈, 損壞, 報廢)');
            $table->text('note')->nullable()->comment('詳細調整說明');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('執行調整的使用者 ID');
            $table->timestamp('created_at')->useCurrent()->comment('調整記錄建立時間');

            // 索引
            $table->index('warehouse_id');
            $table->index('item_id');
            $table->index('created_at');
        });
        DB::statement("ALTER TABLE `stock_adjustments` comment '庫存調整紀錄表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
