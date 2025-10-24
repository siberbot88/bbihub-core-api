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
        Schema::create('workshops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_uuid')->constrained('users');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            $table->string('photo');
            $table->string('city');
            $table->string('province');
            $table->string('country');
            $table->string('postal_code');
            $table->decimal('latitude', 15, 8);
            $table->decimal('longitude', 15, 8);
            $table->time('opening_time');
            $table->time('closing_time');
            $table->string('operational_days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
