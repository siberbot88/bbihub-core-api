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
            if (!Schema::hasColumn('services', 'assigned_to_user_id')) {
                $table->foreignUuid('assigned_to_user_id')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('services', 'technician_name')) {
                $table->string('technician_name')->nullable();
            }
            if (!Schema::hasColumn('services', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });

        Schema::table('services', function (Blueprint $table) {
            // Add indexes. If they exist, this might fail, but usually we can assume they don't if migration failed early.
            // To be safe, we could drop them first in down() or just try to add.
            // Let's try to add them.
            try {
                $table->index('assigned_to_user_id', 'idx_services_assigned_to');
                $table->index(['status', 'assigned_to_user_id'], 'idx_services_status_assigned');
                $table->index('completed_at', 'idx_services_completed_at');
            } catch (\Exception $e) {
                // Ignore index already exists error
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // We should check if they exist before dropping to avoid errors in rollback too
            if (Schema::hasColumn('services', 'assigned_to_user_id')) {
                 // Drop foreign key first if it exists. 
                 // Note: dropping foreign key by array syntax guesses the name.
                 try {
                    $table->dropForeign(['assigned_to_user_id']);
                 } catch (\Exception $e) {}
                 
                 try {
                    $table->dropIndex('idx_services_assigned_to');
                 } catch (\Exception $e) {}
                 
                 try {
                    $table->dropIndex('idx_services_status_assigned');
                 } catch (\Exception $e) {}

                 $table->dropColumn('assigned_to_user_id');
            }

            if (Schema::hasColumn('services', 'technician_name')) {
                $table->dropColumn('technician_name');
            }

            if (Schema::hasColumn('services', 'completed_at')) {
                 try {
                    $table->dropIndex('idx_services_completed_at');
                 } catch (\Exception $e) {}
                $table->dropColumn('completed_at');
            }
        });
    }
};
