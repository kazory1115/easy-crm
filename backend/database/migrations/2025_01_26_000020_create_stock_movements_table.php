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
     * 建立庫存移動紀錄表，用於追蹤所有品項進出倉庫的歷史，提供完整的庫存軌跡。
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id()->comment('庫存移動紀錄唯一識別碼');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade')->comment('發生移動的倉庫 ID');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade')->comment('移動的品項 ID');

            $table->enum('type', ['inbound', 'outbound', 'transfer', 'adjustment'])->default('inbound')
                ->comment('移動類型 (inbound: 入庫, outbound: 出庫, transfer: 調撥, adjustment: 調整)');
            $table->integer('quantity')->comment('移動數量（入庫為正，出庫為負）');

            // 關聯到觸發這次庫存移動的來源單據 (例如: 訂單、採購單、盤點單)
            // 使用多態關聯
            $table->string('reference_type')->nullable()->comment('參考單據的模型類別 (例如: "App\\Models\\Order")');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('參考單據的 ID');

            $table->text('note')->nullable()->comment('庫存移動備註');
            $table->timestamp('occurred_at')->nullable()->comment('庫存移動發生的時間');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('記錄此移動的使用者 ID');
            $table->timestamps(); // created_at 記錄資料建立時間，updated_at 其實在這裡用途不大，但 Laravel 預設會建立

            // 索引
            $table->index('warehouse_id');
            $table->index('item_id');
            $table->index('type');
            $table->index('occurred_at');
            $table->index(['reference_type', 'reference_id']); // 多態關聯的複合索引
        });
        DB::statement("ALTER TABLE `stock_movements` comment '庫存移動日誌表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
