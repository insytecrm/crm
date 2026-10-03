<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('microsite_enabled')->default(false)->after('show_on_website');
            $table->string('microsite_slug')->nullable()->unique()->after('microsite_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropUnique(['microsite_slug']);
            $table->dropColumn(['microsite_enabled', 'microsite_slug']);
        });
    }
};
