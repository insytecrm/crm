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
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->string('priority')->default('normal')->after('scheduled_at');
            $table->string('completion_method')->nullable()->after('completed_at');
            $table->string('completion_outcome')->nullable()->after('completion_method');
            $table->string('next_step_type')->nullable()->after('completion_outcome');
            $table->text('completion_notes')->nullable()->after('next_step_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->dropColumn([
                'priority',
                'completion_method',
                'completion_outcome',
                'next_step_type',
                'completion_notes',
            ]);
        });
    }
};
