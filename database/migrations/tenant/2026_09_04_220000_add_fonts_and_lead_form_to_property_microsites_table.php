<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_microsites', function (Blueprint $table) {
            $table->string('font_primary', 40)->default('outfit')->after('theme_accent');
            $table->string('font_secondary', 40)->default('cormorant_garamond')->after('font_primary');
            $table->json('lead_form')->nullable()->after('cta_label');
        });
    }

    public function down(): void
    {
        Schema::table('property_microsites', function (Blueprint $table) {
            $table->dropColumn(['font_primary', 'font_secondary', 'lead_form']);
        });
    }
};
