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
        Schema::create('portal_webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('portal');
            $table->string('webhook_id')->unique();
            $table->string('secret_hash', 64);
            $table->text('secret_encrypted');
            $table->string('secret_last_four', 4);
            $table->boolean('is_active')->default(true);
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'portal']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_webhook_endpoints');
    }
};
