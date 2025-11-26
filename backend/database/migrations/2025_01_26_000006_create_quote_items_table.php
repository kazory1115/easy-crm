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
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade')->comment('報價單 ID');
            $table->foreignId('item_id')->nullable()->constrained('items')->onDelete('set null')->comment('項目 ID');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('type', ['input', 'drop', 'template'])->default('input')->comment('項目類型');
            $table->string('name')->comment('品名規格');
            $table->text('description')->nullable()->comment('描述');
            $table->integer('quantity')->default(1)->comment('數量');
            $table->string('unit')->default('式')->comment('單位');
            $table->decimal('price', 15, 2)->default(0)->comment('單價');
            $table->decimal('amount', 15, 2)->default(0)->comment('複價（小計）');
            $table->json('fields')->nullable()->comment('模板欄位資料');
            $table->text('notes')->nullable()->comment('備註');
            $table->timestamps();

            $table->index('quote_id');
            $table->index('item_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
