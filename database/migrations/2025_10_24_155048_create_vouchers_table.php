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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code_voucher')->unique();
            $table->foreignUuid('workshop_uuid')->constrained('workshops');
            $table->string('title');
            $table->text('description');
            $table->string('discount_type');
            $table->decimal('discount_value', 10, 2);
            $table->integer('quota');
            $table->decimal('min_transaction', 10, 2);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
