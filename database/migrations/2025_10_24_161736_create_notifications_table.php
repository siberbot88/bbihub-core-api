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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_uuid')->constrained('users');
            $table->string('title');
            $table->text('message');
            $table->enum('type', [
                'transaction',
                'task_assignment',
                'task_completed',
                'feedback_received',
                'voucher_active',
                'service_logged',
                'report_ready',
                'reminder',
                'system'
            ]);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
