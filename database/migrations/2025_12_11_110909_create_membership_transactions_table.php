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
        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_membership_id');
            $table->uuid('customer_id');
            $table->uuid('membership_id');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->timestamp('transaction_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Midtrans specific fields
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_snap_token')->nullable();
            $table->string('payment_type')->nullable(); // bank_transfer, credit_card, gopay, etc
            $table->text('midtrans_response')->nullable(); // Store full response as JSON
            $table->timestamp('confirmed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes (before foreign keys)  
            $table->index('payment_status');
            $table->index('midtrans_order_id');
            $table->index('midtrans_transaction_id');
            
            // Foreign keys (indexes created automatically)
            $table->foreign('customer_membership_id')->references('id')->on('customer_memberships')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_transactions');
    }
};
