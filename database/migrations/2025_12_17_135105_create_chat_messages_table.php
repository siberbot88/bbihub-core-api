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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('room_id')->index(); // Format: support_owner_{owner_id}
            $table->uuid('user_id');
            $table->enum('user_type', ['owner', 'admin'])->default('owner');
            $table->text('message');
            $table->string('attachment_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['room_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
