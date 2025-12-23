<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['call', 'email', 'meeting', 'note', 'follow_up', 'other'])->default('note');
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('activity_at')->nullable();
            $table->timestamp('next_action_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_activities');
    }
};
