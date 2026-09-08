<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicateLeadIds = DB::table('bookings')
            ->select('lead_id')
            ->whereNotNull('lead_id')
            ->groupBy('lead_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('lead_id');

        foreach ($duplicateLeadIds as $leadId) {
            $bookingIds = DB::table('bookings')
                ->where('lead_id', $leadId)
                ->orderByDesc('invoiced_at')
                ->orderByDesc('agreement_date')
                ->orderByDesc('id')
                ->pluck('id');

            $keepId = $bookingIds->first();
            $duplicateBookingIds = $bookingIds->slice(1);

            if ($duplicateBookingIds->isEmpty()) {
                continue;
            }

            DB::table('bookings')
                ->whereIn('id', $duplicateBookingIds->all())
                ->update(['lead_id' => null]);
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->unique('lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['lead_id']);
        });
    }
};
