<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique()->comment('報價單號');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null')->comment('客戶 ID');
            $table->string('customer_name')->comment('客戶名稱');
            $table->string('contact_phone')->nullable()->comment('聯絡電話');
            $table->string('contact_email')->nullable()->comment('聯絡信箱');
            $table->string('project_name')->nullable()->comment('專案名稱');
            $table->date('quote_date')->comment('報價日期');
            $table->date('valid_until')->nullable()->comment('有效期限');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('小計');
            $table->decimal('tax', 15, 2)->default(0)->comment('稅額');
            $table->decimal('discount', 15, 2)->default(0)->comment('折扣');
            $table->decimal('total', 15, 2)->default(0)->comment('總金額');
            $table->text('notes')->nullable()->comment('備註');
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'expired'])->default('draft')->comment('狀態');
            $table->timestamp('sent_at')->nullable()->comment('發送時間');
            $table->timestamp('approved_at')->nullable()->comment('核准時間');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立者');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('更新者');
            $table->timestamps();
            $table->softDeletes();

            $table->index('quote_number');
            $table->index('customer_id');
            $table->index('customer_name');
            $table->index('quote_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
