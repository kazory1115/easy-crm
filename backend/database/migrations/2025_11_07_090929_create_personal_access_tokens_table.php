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
     * 建立個人存取令牌表 (Personal Access Tokens)，主要由 Laravel Sanctum 套件用於 API 認證，
     * 允許使用者為其帳戶產生具有特定權限的 API Token。
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id()->comment('令牌唯一識別碼');
            // 使用 morphs 建立多態關聯，可以關聯到任何可被授權的「擁有者」，通常是 User 模型
            $table->morphs('tokenable');
            $table->text('name')->comment('令牌名稱，方便識別');
            $table->string('token', 64)->unique()->comment('令牌本身，是唯一的加密字串');
            $table->text('abilities')->nullable()->comment('令牌擁有的權限 (JSON 格式)');
            $table->timestamp('last_used_at')->nullable()->comment('上次使用令牌的時間');
            $table->timestamp('expires_at')->nullable()->comment('令牌過期時間');
            $table->timestamps();

            // 索引
            $table->index('expires_at');
        });
        DB::statement("ALTER TABLE `personal_access_tokens` comment '個人存取令牌表 (Sanctum)'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
