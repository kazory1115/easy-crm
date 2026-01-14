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
        // quotes 資料表：儲存報價單主體資訊
        Schema::create('quotes', function (Blueprint $table) {
            $table->id()->comment('報價單唯一識別碼');
            $table->string('quote_number')->unique()->comment('報價單號，由系統自動生成，確保唯一性');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null')->comment('關聯的客戶 ID');
            $table->string('customer_name')->comment('客戶名稱 (為方便查詢，做資料冗餘)');
            $table->string('contact_phone')->nullable()->comment('報價當下的聯絡電話');
            $table->string('contact_email')->nullable()->comment('報價當下的聯絡信箱');
            $table->string('project_name')->nullable()->comment('專案名稱');
            $table->date('quote_date')->comment('報價日期');
            $table->date('valid_until')->nullable()->comment('報價單有效期限');

            // 金額相關資訊
            $table->decimal('subtotal', 15, 2)->default(0)->comment('未稅小計 (所有品項加總)');
            $table->decimal('tax', 15, 2)->default(0)->comment('稅額');
            $table->decimal('discount', 15, 2)->default(0)->comment('折扣金額');
            $table->decimal('total', 15, 2)->default(0)->comment('含稅總金額');

            $table->text('notes')->nullable()->comment('備註或注意事項');

            // 狀態管理
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'expired'])->default('draft')
                ->comment('報價單狀態 (draft: 草稿, sent: 已發送, approved: 客戶已核准, rejected: 客戶已拒絕, expired: 已過期)');
            $table->timestamp('sent_at')->nullable()->comment('發送給客戶的時間');
            $table->timestamp('approved_at')->nullable()->comment('客戶核准的時間');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立此報價單的使用者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新此報價單的使用者 ID');

            $table->timestamps();
            $table->softDeletes();

            // 常用查詢欄位，建立索引以優化查詢效能
            $table->index('quote_number');
            $table->index('customer_id');
            $table->index('customer_name');
            $table->index('quote_date');
            $table->index('status');
        });

        DB::statement("ALTER TABLE `quotes` comment '報價單主檔'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
