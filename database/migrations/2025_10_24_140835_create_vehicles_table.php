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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_uuid')->constrained('customers');
            $table->string('code');
            $table->string('name');
            $table->string('type');
            $table->string('brand');
            $table->string('model');
            $table->string('year');
            $table->string('color');
            $table->string('plate_number');
            $table->string('odometer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
