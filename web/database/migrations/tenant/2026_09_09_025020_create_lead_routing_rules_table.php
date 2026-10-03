<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_routing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('sub_source')->nullable();
            $table->foreignId('sales_team_id')->constrained('sales_teams')->cascadeOnDelete();
            $table->string('distribution');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('distribution_cursor')->default(0);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source', 'is_active']);
        });

        Schema::create('lead_routing_rule_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_routing_rule_id')->constrained('lead_routing_rules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('weight')->default(1);
            $table->timestamps();

            $table->unique(['lead_routing_rule_id', 'user_id'], 'lead_routing_rule_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_routing_rule_user');
        Schema::dropIfExists('lead_routing_rules');
    }
};
