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
        Schema::create('platform_leads', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('location')->nullable();
            $table->string('source');
            $table->string('stage')->default('new_lead');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('next_action_label')->nullable();
            $table->timestamp('next_action_at')->nullable();
            $table->date('demo_date')->nullable();
            $table->time('demo_time')->nullable();
            $table->string('tenant_id')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('stage');
            $table->index('source');
            $table->index('owner_id');
            $table->index('tenant_id');
            $table->index('created_at');
        });

        Schema::create('platform_lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_lead_id')->constrained('platform_leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['platform_lead_id', 'created_at']);
        });

        Schema::create('platform_lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_lead_id')->constrained('platform_leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['platform_lead_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_lead_activities');
        Schema::dropIfExists('platform_lead_notes');
        Schema::dropIfExists('platform_leads');
    }
};
