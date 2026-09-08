<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheet_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('spreadsheet_url');
            $table->string('spreadsheet_id');
            $table->string('sheet_title')->nullable();
            $table->string('status')->default('draft');
            $table->json('headers')->nullable();
            $table->json('column_map')->nullable();
            $table->unsignedInteger('last_synced_row')->default(1);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'connected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheet_connections');
    }
};
