<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_microsites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('theme_preset', 32)->default('noir');
            $table->string('theme_accent', 7)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('cta_label', 80)->nullable();
            $table->json('content')->nullable();
            $table->json('media')->nullable();
            $table->timestamps();

            $table->unique('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_microsites');
    }
};
