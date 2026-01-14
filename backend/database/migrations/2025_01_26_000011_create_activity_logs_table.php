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
     * 註：此資料表結構類似 spatie/laravel-activitylog 套件，用於提供一個通用的、
     * 全系統範圍的活動日誌記錄功能。它可以記錄任何模型的變動、使用者登入登出等各種事件。
     */
    public function up(): void
    {
        // activity_logs 資料表：記錄系統中各種操作活動的日誌
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id()->comment('日誌唯一識別碼');
            $table->string('log_name')->nullable()->comment('日誌分類，方便對日誌進行分組 (例如: "default", "auth")');

            // --- 主體 (Subject) ---
            // 被操作的對象，例如被修改的 Customer 或 Quote 模型。
            // 使用多態關聯 (Polymorphic Relations) 來實現。
            $table->string('subject_type')->nullable()->comment('被操作物件的模型類別 (例如: "App\\Models\\Customer")');
            $table->unsignedBigInteger('subject_id')->nullable()->comment('被操作物件的 ID');

            // --- 觸發者 (Causer) ---
            // 執行操作的對象，通常是 User 模型。
            // 同樣使用多態關聯。
            $table->string('causer_type')->nullable()->comment('操作者的模型類別 (例如: "App\\Models\\User")');
            $table->unsignedBigInteger('causer_id')->nullable()->comment('操作者的 ID');

            // --- 日誌內容 ---
            $table->string('event')->comment('事件名稱 (例如: "created", "updated", "login")');
            $table->text('description')->nullable()->comment('此活動的文字描述');
            $table->json('properties')->nullable()->comment('儲存額外屬性，通常包含變動前後的資料 (JSON 格式)');

            // --- 附加資訊 ---
            $table->string('batch_uuid')->nullable()->comment('若操作在一個批次中完成，此為批次的 UUID');
            $table->string('ip_address', 45)->nullable()->comment('操作者 IP 位址');
            $table->text('user_agent')->nullable()->comment('操作者 User Agent');
            $table->timestamp('created_at')->useCurrent();

            // 複合索引與一般索引
            $table->index('log_name');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index('batch_uuid');
        });
        DB::statement("ALTER TABLE `activity_logs` comment '全域活動日誌表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
