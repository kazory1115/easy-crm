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
        // templates 資料表：儲存可重複使用的範本，例如報價單的品項組合
        Schema::create('templates', function (Blueprint $table) {
            $table->id()->comment('範本唯一識別碼');
            $table->string('name')->comment('範本名稱');
            $table->text('description')->nullable()->comment('範本內容或用途的詳細描述');
            $table->string('category')->nullable()->comment('範本分類');
            $table->enum('type', ['quote', 'invoice', 'general'])->default('quote')
                ->comment('範本類型 (quote: 報價單用, invoice: 發票用, general: 通用)');

            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態 (active: 可用, inactive: 停用)');
            $table->integer('usage_count')->default(0)->comment('被使用的次數統計');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立此範本的使用者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新此範本的使用者 ID');

            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('name');
            $table->index('category');
            $table->index('type');
            $table->index('status');
        });

        DB::statement("ALTER TABLE `templates` comment '共用範本主檔'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
