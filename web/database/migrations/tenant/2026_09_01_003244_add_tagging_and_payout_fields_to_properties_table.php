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
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedSmallInteger('tagging_period_days')->nullable()->after('price_to');
            $table->decimal('payout_percent', 5, 2)->nullable()->after('tagging_period_days');
            $table->string('sourcing_manager_name')->nullable()->after('payout_percent');
            $table->string('sourcing_manager_contact', 30)->nullable()->after('sourcing_manager_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'tagging_period_days',
                'payout_percent',
                'sourcing_manager_name',
                'sourcing_manager_contact',
            ]);
        });
    }
};
