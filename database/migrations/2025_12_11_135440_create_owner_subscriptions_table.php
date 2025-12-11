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
        Schema::create('owner_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('plan_id');
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->string('billing_cycle'); // monthly, yearly
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            
            // Transaction Details
            $table->string('transaction_id')->nullable()->index(); // Midtrans Transaction ID
            $table->string('order_id')->nullable()->index(); // Our internal Order ID
            $table->string('payment_type')->nullable(); // bank_transfer, credit_card
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->string('snap_token')->nullable();
            $table->string('pdf_url')->nullable(); // Link to payment proof
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_subscriptions');
    }
};
