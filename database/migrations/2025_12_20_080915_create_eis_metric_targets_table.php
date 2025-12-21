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
        Schema::create('eis_metric_targets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('metric_key')->index(); // e.g., 'total_revenue', 'user_growth', 'churn_rate'
            $table->decimal('target_value', 20, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('period_type')->default('monthly'); // monthly, quarterly, yearly
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent duplicate targets for same metric in same period
            $table->unique(['metric_key', 'start_date', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eis_metric_targets');
    }
};
