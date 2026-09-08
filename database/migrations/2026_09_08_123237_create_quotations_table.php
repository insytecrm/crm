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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('tenant_id');
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('billing_cycle');
            $table->unsignedInteger('plan_price');
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('tax_amount')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->boolean('trial_enabled')->default(false);
            $table->unsignedSmallInteger('trial_days')->nullable();
            $table->date('valid_until');
            $table->string('status')->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('accepted_by_name')->nullable();
            $table->foreignId('partner_subscription_id')->nullable()->constrained('partner_subscriptions')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('valid_until');
            $table->index(['status', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
