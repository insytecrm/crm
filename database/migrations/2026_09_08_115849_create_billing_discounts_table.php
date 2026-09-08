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
        Schema::create('billing_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('billing_invoice_id')->nullable()->constrained('billing_invoices')->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->string('reason');
            $table->timestamp('applied_at');
            $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('applied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_discounts');
    }
};
