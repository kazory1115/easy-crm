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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('範本名稱');
            $table->text('description')->nullable()->comment('描述');
            $table->string('category')->nullable()->comment('分類');
            $table->enum('type', ['quote', 'invoice', 'general'])->default('quote')->comment('範本類型');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態');
            $table->integer('usage_count')->default(0)->comment('使用次數');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立者');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('更新者');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('category');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
