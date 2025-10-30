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
        Schema::table('employments', function (Blueprint $table) {
            if (Schema::hasColumn('employments', 'description')) {
                $table->dropColumn('description');
            }

            $table->string('specialist')->nullable()->after('code');
            $table->text('jobdesk')->nullable()->after('specialist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employments', function (Blueprint $table) {
            $table->dropColumn('specialist');
            $table->dropColumn('jobdesk');
            $table->text('description')->nullable()->after('code');
        });
    }
};
