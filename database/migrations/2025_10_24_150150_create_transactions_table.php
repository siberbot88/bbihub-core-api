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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_uuid')->constrained('customers');
            $table->foreignUuid('workshop_uuid')->constrained('workshops');
            $table->foreignUuid('admin_uuid')->constrained('users');
            $table->foreignUuid('mechanic_uuid')->nullable()->constrained('employments')->restrictOnDelete();
            $table->enum('status', ['pending', 'process', 'success'])->default('pending')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
