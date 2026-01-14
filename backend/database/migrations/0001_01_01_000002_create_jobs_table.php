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
        // jobs 資料表：儲存所有待處理的隊列任務
        Schema::create('jobs', function (Blueprint $table) {
            $table->id()->comment('任務唯一識別碼');
            $table->string('queue')->index()->comment('任務所屬的隊列名稱');
            $table->longText('payload')->comment('任務的酬載資料，通常是序列化的任務物件');
            $table->unsignedTinyInteger('attempts')->comment('已嘗試執行的次數');
            $table->unsignedInteger('reserved_at')->nullable()->comment('任務被保留（取出執行）的時間戳記');
            $table->unsignedInteger('available_at')->comment('任務可被執行的時間戳記');
            $table->unsignedInteger('created_at')->comment('任務建立時間的時間戳記');
        });
        DB::statement("ALTER TABLE `jobs` comment '隊列任務資料表'");

        // job_batches 資料表：用於管理批次處理的隊列任務
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary()->comment('批次唯一識別碼');
            $table->string('name')->comment('批次名稱');
            $table->integer('total_jobs')->comment('批次中的任務總數');
            $table->integer('pending_jobs')->comment('待處理的任務數量');
            $table->integer('failed_jobs')->comment('失敗的任務數量');
            $table->longText('failed_job_ids')->comment('所有失敗任務的 ID');
            $table->mediumText('options')->nullable()->comment('批次的選項');
            $table->integer('cancelled_at')->nullable()->comment('批次被取消的時間');
            $table->integer('created_at')->comment('批次建立時間');
            $table->integer('finished_at')->nullable()->comment('批次完成時間');
        });
        DB::statement("ALTER TABLE `job_batches` comment '隊列任務批次資料表'");

        // failed_jobs 資料表：記錄所有執行失敗的隊列任務
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id()->comment('失敗任務的唯一識別碼');
            $table->string('uuid')->unique()->comment('任務的通用唯一識別碼');
            $table->text('connection')->comment('隊列連接名稱');
            $table->text('queue')->comment('隊列名稱');
            $table->longText('payload')->comment('任務的酬載資料');
            $table->longText('exception')->comment('導致失敗的異常資訊');
            $table->timestamp('failed_at')->useCurrent()->comment('任務失敗的時間');
        });
        DB::statement("ALTER TABLE `failed_jobs` comment '失敗的隊列任務資料表'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
