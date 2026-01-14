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
        // users 資料表：儲存所有系統使用者資料
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('使用者唯一識別碼');
            $table->string('name')->comment('使用者名稱');
            $table->string('email')->unique()->comment('使用者電子郵件，用於登入與通知，必須唯一');
            $table->timestamp('email_verified_at')->nullable()->comment('電子郵件驗證時間');
            $table->string('password')->comment('使用者密碼（已加密）');
            $table->rememberToken()->comment('「記住我」功能的 Token');
            $table->timestamps(); // Laravel 自動維護的 created_at 和 updated_at 時間戳記
        });
        DB::statement("ALTER TABLE `users` comment '系統使用者資料表'");

        // password_reset_tokens 資料表：儲存密碼重設請求的 Token
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('使用者電子郵件');
            $table->string('token')->comment('密碼重設 Token');
            $table->timestamp('created_at')->nullable()->comment('Token 建立時間');
        });
        DB::statement("ALTER TABLE `password_reset_tokens` comment '密碼重設 Token 資料表'");

        // sessions 資料表：儲存使用者登入的 Session 資訊
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('Session 唯一識別碼');
            $table->foreignId('user_id')->nullable()->index()->comment('關聯的使用者 ID');
            $table->string('ip_address', 45)->nullable()->comment('使用者 IP 位址');
            $table->text('user_agent')->nullable()->comment('使用者的 User Agent');
            $table->longText('payload')->comment('Session 酬載資料');
            $table->integer('last_activity')->index()->comment('最後活動時間戳記');
        });
        DB::statement("ALTER TABLE `sessions` comment '使用者 Session 資料表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
