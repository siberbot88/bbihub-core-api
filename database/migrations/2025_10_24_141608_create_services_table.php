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
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code');
            $table->foreignUuid('workshop_uuid')->constrained('workshops');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 8, 2)->default(0);
            $table->date('scheduled_date');
            $table->date('estimated_time');
            $table->enum('status', ['pending', 'in progress', 'completed'])->default('pending');
            $table->enum('acceptance_status', ['pending', 'accepted','decline']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
