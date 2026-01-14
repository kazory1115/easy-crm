<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 為 users 資料表擴充更多與 CRM 業務相關的欄位
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 在 'email' 欄位後面新增
            $table->string('phone')->nullable()->after('email')->comment('使用者聯絡電話');
            $table->string('department')->nullable()->after('phone')->comment('所屬部門');
            $table->string('position')->nullable()->after('department')->comment('職位');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'department', 'position']);
        });
    }
};
