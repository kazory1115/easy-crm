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
        // template_fields 資料表：定義範本中的每一個可自訂欄位
        Schema::create('template_fields', function (Blueprint $table) {
            $table->id()->comment('範本欄位唯一識別碼');
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade')->comment('所屬範本的 ID');

            $table->string('field_key')->comment('欄位的機器可讀鍵值 (例如: "project_duration")');
            $table->string('field_label')->comment('顯示給使用者看的欄位標籤 (例如: "專案時長")');
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'textarea'])->default('text')->comment('欄位輸入類型');
            $table->text('field_value')->nullable()->comment('欄位的預設值');
            $table->json('field_options')->nullable()->comment('若為 select 類型，儲存可選項目 (JSON 格式)');
            $table->integer('sort_order')->default(0)->comment('欄位在範本中的顯示順序');
            $table->boolean('is_required')->default(false)->comment('此欄位是否為必填');
            $table->timestamps();

            // 索引
            $table->index('template_id');
            $table->index('sort_order');
        });
        DB::statement("ALTER TABLE `template_fields` comment '共用範本欄位定義表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_fields');
    }
};
