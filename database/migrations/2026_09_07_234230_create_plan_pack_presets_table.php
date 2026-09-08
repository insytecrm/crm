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
        Schema::create('plan_pack_presets', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('pack');
            $table->json('capabilities');
            $table->timestamps();

            $table->unique(['module', 'pack']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_pack_presets');
    }
};
