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
        Schema::table('users', function (Blueprint $table) {
            // Trial period tracking
            $table->timestamp('trial_ends_at')->nullable()->after('email_verified_at');
            $table->boolean('trial_used')->default(false)->after('trial_ends_at');
            
            // Index for efficient queries on trial expiration checks
            $table->index('trial_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['trial_ends_at']);
            $table->dropColumn(['trial_ends_at', 'trial_used']);
        });
    }
};
