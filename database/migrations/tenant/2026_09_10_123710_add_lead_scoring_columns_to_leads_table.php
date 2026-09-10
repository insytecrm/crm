<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedInteger('lead_score_intent')->default(0)->after('lead_score');
            $table->dateTime('latest_positive_outcome_at')->nullable()->after('lead_score_intent');
            $table->string('latest_positive_outcome')->nullable()->after('latest_positive_outcome_at');

            $table->index('lead_score_intent');
            $table->index('latest_positive_outcome_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['lead_score_intent']);
            $table->dropIndex(['latest_positive_outcome_at']);
            $table->dropColumn([
                'lead_score_intent',
                'latest_positive_outcome_at',
                'latest_positive_outcome',
            ]);
        });
    }
};
