<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('report_key');
            $table->enum('format', ['csv', 'xlsx', 'pdf'])->default('xlsx');
            $table->json('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['queued', 'processing', 'done', 'failed'])->default('done');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index('user_id');
            $table->index('report_key');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
