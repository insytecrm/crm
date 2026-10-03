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
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('agreement_date')->nullable()->after('booking_date');
            $table->date('invoice_date')->nullable()->after('agreement_date');
            $table->timestamp('invoiced_at')->nullable()->after('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['agreement_date', 'invoice_date', 'invoiced_at']);
        });
    }
};
