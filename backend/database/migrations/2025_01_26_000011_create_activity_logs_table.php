<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('操作者');
            $table->string('log_name')->nullable()->comment('日誌名稱/分類');
            $table->string('subject_type')->nullable()->comment('主體類型（模型類別）');
            $table->unsignedBigInteger('subject_id')->nullable()->comment('主體 ID');
            $table->string('causer_type')->nullable()->comment('觸發者類型');
            $table->unsignedBigInteger('causer_id')->nullable()->comment('觸發者 ID');
            $table->string('event')->comment('事件類型 (created/updated/deleted/restored/login/logout 等)');
            $table->text('description')->nullable()->comment('操作描述');
            $table->json('properties')->nullable()->comment('屬性資料（包含 old/new 資料）');
            $table->string('ip_address', 45)->nullable()->comment('IP 位址');
            $table->text('user_agent')->nullable()->comment('User Agent');
            $table->string('batch_uuid')->nullable()->comment('批次 UUID（批次操作時使用）');
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('log_name');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index('event');
            $table->index('batch_uuid');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
