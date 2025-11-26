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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('客戶名稱');

            // 聯絡人資訊
            $table->string('contact_person')->nullable()->comment('聯絡人');
            $table->string('phone')->nullable()->comment('聯絡電話');   // ← 修改
            $table->string('mobile')->nullable()->comment('手機');       // ← 新增
            $table->string('email')->nullable()->comment('信箱');        // ← 原本多一個 contact_email，建議統一

            // 公司資訊
            $table->string('tax_id')->nullable()->comment('統編');        // ← 修改
            $table->string('website')->nullable()->comment('官網');      // ← 新增
            $table->string('industry')->nullable()->comment('產業別');   // ← 新增

            $table->text('address')->nullable()->comment('地址');
            $table->text('notes')->nullable()->comment('備註');

            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立者');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('更新者');

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('phone');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
