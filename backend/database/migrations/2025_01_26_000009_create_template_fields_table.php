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
        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade')->comment('範本 ID');
            $table->string('field_key')->comment('欄位鍵值');
            $table->string('field_label')->comment('欄位標籤');
            $table->enum('field_type', ['text', 'number', 'date', 'select', 'textarea'])->default('text')->comment('欄位類型');
            $table->text('field_value')->nullable()->comment('欄位值');
            $table->json('field_options')->nullable()->comment('欄位選項（用於 select 類型）');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('is_required')->default(false)->comment('是否必填');
            $table->timestamps();

            $table->index('template_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_fields');
    }
};
