<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->string('purpose', 32)->default('crm')->after('domain');
            $table->string('verification_token', 64)->nullable()->after('purpose');
            $table->timestamp('verified_at')->nullable()->after('verification_token');

            $table->unique(['tenant_id', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'purpose']);
            $table->dropColumn(['purpose', 'verification_token', 'verified_at']);
        });
    }
};
