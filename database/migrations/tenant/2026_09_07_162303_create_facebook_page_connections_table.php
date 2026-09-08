<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_page_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('page_id')->unique();
            $table->string('page_name')->nullable();
            $table->string('status')->default('draft');
            $table->text('page_access_token')->nullable();
            $table->json('campaigns')->nullable();
            $table->json('lead_forms')->nullable();
            $table->json('selected_form_ids')->nullable();
            $table->json('form_fields')->nullable();
            $table->json('field_map')->nullable();
            $table->unsignedInteger('total_synced')->default(0);
            $table->unsignedInteger('total_skipped')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_lead_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'connected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_page_connections');
    }
};
