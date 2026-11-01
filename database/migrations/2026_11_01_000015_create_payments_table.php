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
        Schema::create('payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('restrict');
            $table->string('payment_ref', 50)->unique('uq_payments_ref');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'upi', 'credit_card', 'debit_card', 'other'])->default('cash');
            $table->string('transaction_ref', 100)->nullable();
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->enum('refund_status', ['none', 'partially_refunded', 'fully_refunded'])->default('none');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->dateTime('paid_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index('bill_id', 'idx_payments_bill');
            $table->index('payment_method', 'idx_payments_method');
            // High-Speed Composite Index for Cashflow & Revenue aggregations
            $table->index(['status', 'paid_at', 'amount'], 'idx_payments_revenue_audit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
