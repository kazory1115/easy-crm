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
     * 建立訂單日誌表，用於記錄訂單的每一次變動，例如狀態變更、金額調整等，便於追溯。
     */
    public function up(): void
    {
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id()->comment('訂單日誌唯一識別碼');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->comment('所屬訂單的 ID');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('執行此操作的使用者 ID');

            $table->string('action')->comment('操作類型 (例如: created, updated, status_changed, item_added)');
            $table->string('description')->nullable()->comment('操作的簡短描述');
            $table->json('old_data')->nullable()->comment('變動前的資料快照 (JSON 格式)');
            $table->json('new_data')->nullable()->comment('變動後的資料快照 (JSON 格式)');

            $table->string('ip_address')->nullable()->comment('執行操作時的 IP 位址');
            $table->text('user_agent')->nullable()->comment('執行操作時的 User Agent');
            $table->timestamps(); // created_at 記錄日誌建立時間，updated_at 通常在這裡不使用

            // 索引
            $table->index('order_id');
            $table->index('user_id');
            $table->index('action');
        });
        DB::statement("ALTER TABLE `order_logs` comment '訂單變動日誌表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logs');
    }
};
