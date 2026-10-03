<?php

use App\Enums\TaskStatus;
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
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->string('status')->default(TaskStatus::Pending->value)->after('due_at');
        });

        DB::table('lead_tasks')
            ->whereNotNull('completed_at')
            ->update(['status' => TaskStatus::Complete->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
