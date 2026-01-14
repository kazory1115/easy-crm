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
     * 建立銷售機會日誌表，用於記錄銷售機會的每一次變動，例如階段變更、金額調整等。
     */
    public function up(): void
    {
        Schema::create('opportunity_logs', function (Blueprint $table) {
            $table->id()->comment('日誌唯一識別碼');
            $table->foreignId('opportunity_id')->constrained('opportunities')->onDelete('cascade')->comment('所屬銷售機會的 ID');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('執行此操作的使用者 ID');

            $table->string('action')->comment('操作類型 (例如: stage_changed, amount_updated)');
            $table->json('old_data')->nullable()->comment('變動前的資料 (JSON)');
            $table->json('new_data')->nullable()->comment('變動後的資料 (JSON)');
            $table->text('description')->nullable()->comment('手動記錄的操作描述');

            $table->string('ip_address', 45)->nullable()->comment('執行操作時的 IP 位址');
            $table->text('user_agent')->nullable()->comment('執行操作時的 User Agent');
            $table->timestamp('created_at')->useCurrent()->comment('日誌建立時間');

            // 索引
            $table->index('opportunity_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
        DB::statement("ALTER TABLE `opportunity_logs` comment '銷售機會變動日誌表'");
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_logs');
    }
};
