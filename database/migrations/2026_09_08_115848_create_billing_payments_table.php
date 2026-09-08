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
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('billing_invoice_id')->nullable()->constrained('billing_invoices')->nullOnDelete();
            $table->foreignId('partner_subscription_id')->nullable()->constrained('partner_subscriptions')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->string('type')->default('subscription');
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('retried_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->string('gateway')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('response_code')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('webhook_status')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('payment_date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_payments');
    }
};
