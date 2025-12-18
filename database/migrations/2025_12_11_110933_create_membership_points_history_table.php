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
        Schema::create('membership_points_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_membership_id');
            $table->uuid('customer_id');
            $table->uuid('transaction_id')->nullable(); // Reference to service transaction
            $table->integer('points'); // Positive for earning, negative for redemption
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted'])->default('earned');
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes (before foreign keys)
            $table->index('type');
            
            // Foreign keys (indexes created automatically)
            $table->foreign('customer_membership_id')->references('id')->on('customer_memberships')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_points_history');
    }
};
