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
        // cache 資料表：用於儲存應用程式的快取資料
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary()->comment('快取鍵');
            $table->mediumText('value')->comment('快取值');
            $table->integer('expiration')->comment('快取過期時間的時間戳記');
        });
        DB::statement("ALTER TABLE `cache` comment '應用程式快取資料表'");

        // cache_locks 資料表：用於管理快取的原子鎖，防止競爭條件
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary()->comment('鎖的鍵');
            $table->string('owner')->comment('鎖的擁有者');
            $table->integer('expiration')->comment('鎖的過期時間');
        });
        DB::statement("ALTER TABLE `cache_locks` comment '應用程式快取鎖資料表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
