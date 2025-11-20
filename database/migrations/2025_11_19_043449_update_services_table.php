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
        Schema::table('services', function (Blueprint $table) {

            if (!Schema::hasColumn('services', 'customer_uuid')) {
                $table->foreignUuid('customer_uuid')->after('workshop_uuid')->constrained('customers');
            }

            if (!Schema::hasColumn('services', 'vehicle_uuid')) {
                $table->foreignUuid('vehicle_uuid')->after('customer_uuid')->constrained('vehicles');
            }

            if (!Schema::hasColumn('services', 'mechanic_uuid')) {
                $table->foreignUuid('mechanic_uuid')->nullable()->after('vehicle_uuid')->constrained('employments');
            }

            if (!Schema::hasColumn('services', 'acceptance_status')) {
                $table->enum('acceptance_status', ['pending','accepted','rejected'])
                    ->default('pending')->after('status');
            }
        });
    }

};
