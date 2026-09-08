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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('source')->nullable();
            $table->string('budget')->nullable();
            $table->string('location')->nullable();
            $table->string('property_type')->nullable();
            $table->string('configuration')->nullable();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('new');
            $table->unsignedTinyInteger('lead_score')->default(0);
            $table->dateTime('next_follow_up_at')->nullable();
            $table->dateTime('upcoming_site_visit_at')->nullable();
            $table->string('next_action')->nullable();
            $table->dateTime('last_activity_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->string('closing_reason')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('next_follow_up_at');
            $table->index('upcoming_site_visit_at');
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
