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
     * 建立銷售機會表。用於追蹤潛在的生意機會從初步接觸到最終成交或失敗的整個銷售過程。
     */
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id()->comment('銷售機會唯一識別碼');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade')->comment('關聯的客戶 ID');

            $table->string('name')->comment('機會名稱 (例如: "網站改版專案")');

            // 銷售流程管理
            $table->enum('stage', ['new', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new')
                ->comment('銷售階段 (new: 新機會, qualified: 已確認資格, proposal: 已提案, negotiation: 談判中, won: 成交, lost: 未成交)');
            $table->decimal('amount', 15, 2)->default(0)->comment('預估成交金額');
            $table->date('expected_close_date')->nullable()->comment('預計結案日期');

            // 最終狀態
            $table->enum('status', ['open', 'won', 'lost'])->default('open')
                ->comment('機會狀態 (open: 進行中, won: 已成交, lost: 已失敗)');

            $table->text('notes')->nullable()->comment('備註');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('建立此機會的使用者 ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('最後更新此機會的使用者 ID');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('customer_id');
            $table->index('stage');
            $table->index('status');
            $table->index('expected_close_date');
        });
        DB::statement("ALTER TABLE `opportunities` comment '銷售機會追蹤表'");
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
