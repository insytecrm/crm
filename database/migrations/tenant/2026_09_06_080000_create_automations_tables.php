<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->string('trigger')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('automation_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->string('field');
            $table->string('operator');
            $table->json('value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('automation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('config')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trigger')->nullable();
            $table->string('status');
            $table->boolean('dry_run')->default(false);
            $table->json('context')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();

            $table->index(['automation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_actions');
        Schema::dropIfExists('automation_conditions');
        Schema::dropIfExists('automations');
    }
};
