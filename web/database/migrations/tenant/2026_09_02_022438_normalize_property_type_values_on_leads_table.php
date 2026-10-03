<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $propertyTypeMap = [
            'Apartment' => 'apartment',
            'Villa' => 'villa',
            'Plot' => 'plot',
            'Shop' => 'shop',
            'Office' => 'office',
            'Commercial' => 'shop',
        ];

        foreach ($propertyTypeMap as $from => $to) {
            DB::table('leads')->where('property_type', $from)->update(['property_type' => $to]);
        }

        DB::table('leads')
            ->whereNotNull('property_type')
            ->whereNotIn('property_type', ['apartment', 'villa', 'plot', 'shop', 'office'])
            ->update(['property_type' => DB::raw('LOWER(property_type)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
