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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('lead_api_token_hash', 64)->nullable()->unique()->after('status');
            $table->text('lead_api_token_encrypted')->nullable()->after('lead_api_token_hash');
            $table->string('lead_api_token_last_four', 4)->nullable()->after('lead_api_token_encrypted');
            $table->timestamp('lead_api_token_generated_at')->nullable()->after('lead_api_token_last_four');
            $table->timestamp('lead_api_token_last_used_at')->nullable()->after('lead_api_token_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'lead_api_token_hash',
                'lead_api_token_encrypted',
                'lead_api_token_last_four',
                'lead_api_token_generated_at',
                'lead_api_token_last_used_at',
            ]);
        });
    }
};
