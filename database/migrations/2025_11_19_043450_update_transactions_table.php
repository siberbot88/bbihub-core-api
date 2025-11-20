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
        Schema::table('transactions', function (Blueprint $table) {

            // connect transaction -> service
            if (!Schema::hasColumn('transactions', 'service_uuid')) {
                $table->foreignUuid('service_uuid')->unique()->after('id')->constrained('services')->onDelete('cascade');
            }

            if (!Schema::hasColumn('transactions', 'customer_uuid')) {
                $table->foreignUuid('customer_uuid')->after('service_uuid')->constrained('customers')->onDelete('cascade');
            }

            if (!Schema::hasColumn('transactions', 'workshop_uuid')) {
                $table->foreignUuid('workshop_uuid')->after('customer_uuid')->constrained('workshops')->onDelete('cascade');
            }

            if (!Schema::hasColumn('transactions', 'mechanic_uuid')) {
                $table->foreignUuid('mechanic_uuid')->nullable()->after('workshop_uuid')->constrained('employments');
            }

            if (!Schema::hasColumn('transactions', 'admin_uuid')) {
                $table->foreignUuid('admin_uuid')->nullable()->after('mechanic_uuid')->constrained('users');
            }
        });
    }

};
