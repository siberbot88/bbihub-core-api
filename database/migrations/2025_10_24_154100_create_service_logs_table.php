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
        Schema::create('service_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_uuid')->constrained('services');
            $table->foreignUuid('mechanic_uuid')->nullable()->constrained('users');
            $table->foreignUuid('transaction_uuid')->constrained('transactions');
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_logs');
    }
};
