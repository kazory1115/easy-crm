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
     * 建立訂單主表，用於記錄客戶的購買訂單資訊。
     * 訂單可以從報價單轉換而來，也可以直接建立。
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->comment('訂單唯一識別碼');
            $table->string('order_number')->unique()->comment('訂單編號，必須唯一');
            $table->foreignId('quote_id')->nullable()->constrained('quotes')->onDelete('set null')->comment('關聯的報價單 ID (如果訂單是從報價單轉換而來)');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('關聯的客戶 ID');
            $table->string('customer_name')->comment('客戶名稱 (冗餘儲存，便於查詢)');
            $table->string('contact_phone')->nullable()->comment('訂單聯絡電話');
            $table->string('contact_email')->nullable()->comment('訂單聯絡電子郵件');
            $table->string('project_name')->nullable()->comment('專案名稱 (如果訂單屬於某個專案)');
            $table->date('order_date')->comment('訂單日期');
            $table->date('due_date')->nullable()->comment('預計交付日期或付款到期日');

            // 金額相關資訊
            $table->decimal('subtotal', 10, 2)->default(0)->comment('訂單商品小計 (未稅)');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('稅額');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('折扣金額');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('訂單總金額 (含稅含折扣)');
            $table->decimal('tax_rate', 5, 4)->default(0)->comment('訂單套用的稅率，例如 0.05 代表 5%');

            // 訂單狀態與付款狀態
            $table->string('status')->default('pending')->comment('訂單狀態 (pending: 待處理, confirmed: 已確認, processing: 處理中, shipped: 已出貨, completed: 已完成, cancelled: 已取消)');
            $table->string('payment_status')->default('unpaid')->comment('付款狀態 (unpaid: 未付款, partially_paid: 部分付款, paid: 已付款, refunded: 已退款)');
            
            $table->timestamp('shipped_at')->nullable()->comment('實際出貨時間');
            $table->timestamp('completed_at')->nullable()->comment('訂單完成時間');
            $table->text('notes')->nullable()->comment('訂單備註');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立訂單的使用者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新訂單的使用者 ID');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE `orders` comment '訂單主檔'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
