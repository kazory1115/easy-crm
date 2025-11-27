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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('品名規格');
            $table->string('code')->unique()->comment('品項代碼');

            $table->text('description')->nullable()->comment('描述');
            $table->string('unit')->default('式')->comment('單位');
            $table->decimal('price', 15, 2)->default(0)->comment('單價');
            $table->integer('quantity')->default(1)->comment('預設數量');
            $table->string('category')->nullable()->comment('分類');
            $table->json('specifications')->nullable()->comment('規格/其他資訊');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立者');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('更新者');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
