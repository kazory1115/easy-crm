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
     * 註：此資料表專門用於記錄與「使用者」本身相關的操作，例如登入、登出、修改密碼、權限變更等。
     * 在設計上，它的功能與通用的 `activity_logs` 表有部分重疊。
     * `user_id` 是被操作的使用者，而 `operator_id` 是執行操作的使用者。
     * 在 `activity_logs` 的概念中，這分別對應 `subject` 和 `causer`。
     * 維護此表是為了能更快速、簡單地查詢特定於使用者的活動歷史，而無需過濾通用的活動日誌。
     */
    public function up(): void
    {
        // user_logs 資料表：記錄使用者帳號相關的變動與活動歷史
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id()->comment('日誌唯一識別碼');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('被操作的使用者 ID');
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null')->comment('執行此操作的使用者 ID (如果為 null，可能是系統自動觸發)');
            $table->string('action')->comment('操作類型 (例如: login, logout, password_changed, profile_updated)');
            $table->json('old_data')->nullable()->comment('變動前的資料 (JSON 格式)');
            $table->json('new_data')->nullable()->comment('變動後的資料 (JSON 格式)');
            $table->text('description')->nullable()->comment('手動記錄的操作描述');
            $table->string('ip_address', 45)->nullable()->comment('執行操作時的 IP 位址');
            $table->text('user_agent')->nullable()->comment('執行操作時的 User Agent');
            $table->timestamp('created_at')->useCurrent()->comment('日誌建立時間');

            // 索引
            $table->index('user_id');
            $table->index('operator_id');
            $table->index('action');
            $table->index('created_at');
        });
        DB::statement("ALTER TABLE `user_logs` comment '使用者活動日誌表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
