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
        // customer_logs 資料表：記錄客戶資料的變動歷史，用於追蹤與稽核
        Schema::create('customer_logs', function (Blueprint $table) {
            $table->id()->comment('日誌唯一識別碼');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('所屬客戶的 ID');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('執行此操作的使用者 ID');
            $table->string('action')->comment('操作類型 (例如: created, updated, deleted, restored)');
            $table->json('old_data')->nullable()->comment('變動前的資料內容 (JSON 格式)');
            $table->json('new_data')->nullable()->comment('變動後的資料內容 (JSON 格式)');
            $table->text('description')->nullable()->comment('手動記錄的操作描述');
            $table->string('ip_address', 45)->nullable()->comment('執行操作時的 IP 位址');
            $table->text('user_agent')->nullable()->comment('執行操作時的 User Agent');
            $table->timestamp('created_at')->useCurrent()->comment('日誌建立時間');

            // 建立索引以優化查詢效能
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });

        DB::statement("ALTER TABLE `customer_logs` comment '客戶資料變動日誌表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_logs');
    }
};
