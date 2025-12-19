<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at'); // Critical for reporting date ranges
            $table->index(['workshop_uuid', 'created_at']); // Compound index for workshop reporting
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('status');
            $table->index('scheduled_date'); // Critical for calendar/occupancy
            $table->index(['workshop_uuid', 'scheduled_date']); // Compound index
            $table->index('created_at');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('plate_number'); // Frequent search
            $table->index('customer_uuid'); // Often filtered by customer (explicit index for speed)
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->index('rating');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['workshop_uuid', 'created_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['scheduled_date']);
            $table->dropIndex(['workshop_uuid', 'scheduled_date']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['plate_number']);
            $table->dropIndex(['customer_uuid']);
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->dropIndex(['rating']);
            $table->dropIndex(['submitted_at']);
        });
    }
};
