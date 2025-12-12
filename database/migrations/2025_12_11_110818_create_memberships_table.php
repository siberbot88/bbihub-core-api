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
        Schema::create('memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workshop_id');
            $table->string('name'); // 'Bronze', 'Silver', 'Gold', 'Platinum'
            $table->text('description')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0); // e.g., 5.00 = 5%
            $table->decimal('points_multiplier', 5, 2)->default(1.0); // e.g., 1.5 = 150%
            $table->decimal('price', 10, 2); // Monthly/yearly price
            $table->integer('duration_months')->default(1); // 1, 3, 6, 12 months
            $table->boolean('is_active')->default(true);
            $table->json('benefits')->nullable(); // Extra benefits JSON
            $table->timestamps();
            
            // Foreign key
            $table->foreign('workshop_id')->references('id')->on('workshops')->onDelete('cascade');
            
            // Indexes
            $table->index('workshop_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
