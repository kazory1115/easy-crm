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
     * 建立報表匯出紀錄表，用於追蹤使用者發起的報表匯出任務，特別適用於需背景處理的大型報表。
     */
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id()->comment('報表匯出紀錄唯一識別碼');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('發起匯出任務的使用者 ID');
            $table->string('report_key')->comment('匯出的報表類型或識別鍵 (例如: "customer_list", "sales_report")');
            $table->enum('format', ['csv', 'xlsx', 'pdf'])->default('xlsx')->comment('匯出的檔案格式');
            $table->json('filters')->nullable()->comment('匯出時所使用的篩選條件 (JSON 格式)');
            $table->string('file_path')->nullable()->comment('匯出檔案在儲存系統中的路徑');
            $table->enum('status', ['queued', 'processing', 'done', 'failed'])->default('queued')->comment('匯出任務狀態 (queued: 等待中, processing: 處理中, done: 已完成, failed: 失敗)');
            $table->timestamp('created_at')->useCurrent()->comment('任務建立時間');
            $table->timestamp('completed_at')->nullable()->comment('任務完成或失敗時間');

            // 索引
            $table->index('user_id');
            $table->index('report_key');
            $table->index('status');
            $table->index('created_at');
        });
        DB::statement("ALTER TABLE `report_exports` comment '報表匯出紀錄表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
