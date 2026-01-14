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
     * 建立客戶互動紀錄表。這是 CRM 的核心功能之一，用於記錄與客戶的每一次接觸，
     * 例如電話、會議、Email 或只是一般的備註。
     */
    public function up(): void
    {
        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id()->comment('活動紀錄唯一識別碼');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('所屬客戶的 ID');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('負責此活動的使用者 ID');

            $table->enum('type', ['call', 'email', 'meeting', 'note', 'follow_up', 'other'])->default('note')
                ->comment('活動類型 (call: 電話, email: 電子郵件, meeting: 會議, note: 備註, follow_up: 跟進, other: 其他)');
            $table->string('subject')->nullable()->comment('活動主旨');
            $table->text('content')->nullable()->comment('活動詳細內容');
            $table->timestamp('activity_at')->nullable()->comment('活動發生的時間');
            $table->timestamp('next_action_at')->nullable()->comment('預計下次跟進的時間');
            $table->timestamps();

            // 索引
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('activity_at');
        });
        DB::statement("ALTER TABLE `customer_activities` comment '客戶互動紀錄表'");
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_activities');
    }
};
