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
        Schema::create('refunds', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('restrict');
            $table->foreignId('bill_id')->constrained('bills')->onDelete('restrict');
            $table->string('refund_ref', 50)->unique('uq_refunds_ref');
            $table->decimal('amount', 10, 2);
            $table->enum('refund_method', ['cash', 'upi', 'card', 'original_payment_method'])->default('cash');
            $table->string('reason', 255);
            $table->string('transaction_ref', 100)->nullable();
            $table->enum('status', ['pending', 'successful', 'failed'])->default('pending');
            $table->foreignId('processed_by')->constrained('users')->onDelete('restrict');
            $table->dateTime('refunded_at')->nullable();
            $table->timestamps();

            $table->index('payment_id', 'idx_refunds_payment');
            $table->index('bill_id', 'idx_refunds_bill');
            // Composite Index for Revenue Deductions
            $table->index(['status', 'refunded_at', 'amount'], 'idx_refunds_revenue_deduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
