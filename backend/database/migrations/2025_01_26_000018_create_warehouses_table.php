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
     * 建立倉庫資料表，用於管理實體或虛擬的存貨地點。
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id()->comment('倉庫唯一識別碼');
            $table->string('name')->comment('倉庫名稱');
            $table->string('code')->unique()->comment('倉庫代碼，必須唯一');
            $table->string('location')->nullable()->comment('倉庫的實體位置或描述');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('狀態 (active: 啟用, inactive: 停用)');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新者 ID');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('status');
        });
        DB::statement("ALTER TABLE `warehouses` comment '倉庫基本資料表'");
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
