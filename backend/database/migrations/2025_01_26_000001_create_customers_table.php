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
        // customers 資料表：儲存客戶基本資料
        Schema::create('customers', function (Blueprint $table) {
            $table->id()->comment('客戶唯一識別碼');
            $table->string('name')->comment('客戶名稱');

            // 聯絡人資訊
            $table->string('contact_person')->nullable()->comment('主要聯絡人姓名');
            $table->string('phone')->nullable()->comment('公司聯絡電話');
            $table->string('mobile')->nullable()->comment('聯絡人手機');
            $table->string('email')->nullable()->comment('聯絡人電子郵件');

            // 公司資訊
            $table->string('tax_id')->nullable()->comment('公司統一編號');
            $table->string('website')->nullable()->comment('公司官方網站');
            $table->string('industry')->nullable()->comment('所屬產業類別');

            $table->text('address')->nullable()->comment('公司地址');
            $table->text('notes')->nullable()->comment('內部備註事項');

            // 系統追蹤資訊
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('客戶狀態 (active: 活躍, inactive: 不活躍)');

            // 軟刪除使用者時，希望能保留紀錄是誰建立的，所以用 set null
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立此客戶的使用者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新此客戶的使用者 ID');

            $table->timestamps(); // 建立時間與更新時間
            $table->softDeletes(); // 軟刪除時間戳記

            // 常用查詢欄位，建立索引以優化查詢效能
            $table->index('name');
            $table->index('phone');
            $table->index('status');
        });

        DB::statement("ALTER TABLE `customers` comment '客戶基本資料表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
