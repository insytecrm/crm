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
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('total_land_parcel_acres', 10, 2)->nullable()->after('possession_date');
            $table->unsignedSmallInteger('total_towers')->nullable()->after('total_land_parcel_acres');
            $table->string('total_floors', 50)->nullable()->after('total_towers');
            $table->unsignedInteger('carpet_area_from_sqft')->nullable()->after('total_floors');
            $table->unsignedInteger('carpet_area_to_sqft')->nullable()->after('carpet_area_from_sqft');
            $table->unsignedBigInteger('price_from')->nullable()->after('carpet_area_to_sqft');
            $table->unsignedBigInteger('price_to')->nullable()->after('price_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'total_land_parcel_acres',
                'total_towers',
                'total_floors',
                'carpet_area_from_sqft',
                'carpet_area_to_sqft',
                'price_from',
                'price_to',
            ]);
        });
    }
};
