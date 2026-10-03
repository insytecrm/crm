<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utility_mail_settings', function (Blueprint $table) {
            $table->string('reply_to_email')->nullable()->after('from_name');
            $table->string('reply_to_name')->nullable()->after('reply_to_email');
            $table->text('notes')->nullable()->after('reply_to_name');
            $table->boolean('is_active')->default(true)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('utility_mail_settings', function (Blueprint $table) {
            $table->dropColumn(['reply_to_email', 'reply_to_name', 'notes', 'is_active']);
        });
    }
};
