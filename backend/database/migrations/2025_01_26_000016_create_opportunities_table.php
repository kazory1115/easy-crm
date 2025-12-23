<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('name');
            $table->enum('stage', ['new', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->enum('status', ['open', 'won', 'lost'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('stage');
            $table->index('status');
            $table->index('expected_close_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
