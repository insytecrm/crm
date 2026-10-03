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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('price_monthly');
            $table->unsignedInteger('price_annual');
            $table->string('currency', 3)->default('INR');
            $table->boolean('trial_enabled')->default(true);
            $table->unsignedSmallInteger('trial_days')->default(7);
            $table->json('features');
            $table->json('packs');
            $table->json('capabilities');
            $table->json('limits');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
