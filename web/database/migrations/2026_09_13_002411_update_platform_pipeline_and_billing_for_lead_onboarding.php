<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_leads', function (Blueprint $table) {
            $table->string('rera_number')->nullable()->after('location');
            $table->string('gst_number')->nullable()->after('rera_number');
            $table->timestamp('onboarded_at')->nullable()->after('tenant_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->string('rera_number')->nullable()->after('phone');
            $table->string('gst_number')->nullable()->after('rera_number');
        });

        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->change();
            $table->foreignId('quotation_id')->nullable()->after('number')->constrained('quotations')->nullOnDelete();
        });

        $stageMap = [
            'demo_scheduled' => 'demo',
            'demo_completed' => 'demo',
            'quotation_sent' => 'quoted',
            'quotation_accepted' => 'quoted',
            'handover' => 'onboarding',
            'client_live' => 'live',
        ];

        foreach ($stageMap as $from => $to) {
            DB::table('platform_leads')->where('stage', $from)->update(['stage' => $to]);
        }
    }

    public function down(): void
    {
        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
            $table->string('tenant_id')->nullable(false)->change();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['rera_number', 'gst_number']);
        });

        Schema::table('platform_leads', function (Blueprint $table) {
            $table->dropColumn(['rera_number', 'gst_number', 'onboarded_at']);
        });
    }
};
