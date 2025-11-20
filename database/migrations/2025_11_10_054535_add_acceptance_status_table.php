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
            $table->enum('status', ['pending', 'in progress', 'completed'])->default('pending')->change();
            $table->enum('acceptance_status', ['pending', 'accepted', 'decline'])->default('pending')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accept', 'in progress', 'completed', 'cancelled'])->default('pending')->nullable()->change();
            $table->dropColumn('acceptance_status');
        });
    }
};
