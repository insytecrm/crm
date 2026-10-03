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
        Schema::create('billing_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('billing_payment_id')->constrained('billing_payments')->restrictOnDelete();
            $table->unsignedInteger('amount');
            $table->string('reason');
            $table->string('status')->default('requested');
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_refunds');
    }
};
