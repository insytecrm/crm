<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('full_name');
            $table->string('company');
            $table->string('phone');
            $table->string('email');
            $table->string('team_size');
            $table->string('plan')->nullable();
            $table->string('billing_cycle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_submissions');
    }
};
