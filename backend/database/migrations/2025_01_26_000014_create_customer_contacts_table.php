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
     * 建立客戶聯絡人資料表。一個客戶可以有多個聯絡人，
     * 這張表將客戶的主要聯絡資訊與多個不同窗口的聯絡人分開，使結構更清晰。
     */
    public function up(): void
    {
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id()->comment('聯絡人唯一識別碼');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('所屬客戶的 ID');

            $table->string('name')->comment('聯絡人姓名');
            $table->string('title')->nullable()->comment('職稱');
            $table->string('phone')->nullable()->comment('公司電話');
            $table->string('mobile')->nullable()->comment('手機');
            $table->string('email')->nullable()->comment('電子郵件');
            $table->boolean('is_primary')->default(false)->comment('是否為主要聯絡人');
            $table->text('notes')->nullable()->comment('備註');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立此聯絡人的使用者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新此聯絡人的使用者 ID');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('customer_id');
            $table->index('email');
        });
        DB::statement("ALTER TABLE `customer_contacts` comment '客戶聯絡人資訊表'");
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_contacts');
    }
};
