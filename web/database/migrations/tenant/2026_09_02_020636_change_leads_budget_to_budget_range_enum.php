<?php

use App\Enums\LeadBudget;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('leads', 'budget')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table->string('budget_range')->nullable();
        });

        foreach (DB::table('leads')->whereNotNull('budget')->orderBy('id')->cursor() as $lead) {
            $budget = is_numeric($lead->budget)
                ? LeadBudget::fromAmount((int) $lead->budget)?->value
                : LeadBudget::tryFromMixed($lead->budget)?->value;

            DB::table('leads')
                ->where('id', $lead->id)
                ->update(['budget_range' => $budget]);
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn('budget');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->string('budget')->nullable();
        });

        foreach (DB::table('leads')->whereNotNull('budget_range')->orderBy('id')->cursor() as $lead) {
            DB::table('leads')
                ->where('id', $lead->id)
                ->update(['budget' => $lead->budget_range]);
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn('budget_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
