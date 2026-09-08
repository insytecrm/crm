<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_custom_column_values', function (Blueprint $table) {
            $table->id();
            $table->string('table_key');
            $table->unsignedBigInteger('record_id');
            $table->string('column_key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['table_key', 'record_id', 'column_key']);
            $table->index(['table_key', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_custom_column_values');
    }
};
