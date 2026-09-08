<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->foreignId('property_id')
                ->nullable()
                ->after('lead_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lead_scheduled_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_id');
        });
    }
};
