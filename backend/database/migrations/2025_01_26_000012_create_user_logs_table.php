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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('用戶 ID');
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null')->comment('操作者 ID');
            $table->string('action')->comment('操作類型 (created/updated/deleted/login/logout/password_changed 等)');
            $table->json('old_data')->nullable()->comment('修改前資料');
            $table->json('new_data')->nullable()->comment('修改後資料');
            $table->text('description')->nullable()->comment('操作描述');
            $table->string('ip_address', 45)->nullable()->comment('IP 位址');
            $table->text('user_agent')->nullable()->comment('User Agent');
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('operator_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
