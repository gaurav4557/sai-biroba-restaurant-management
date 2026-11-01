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
        Schema::create('bills', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('table_visit_id')->unique('uq_bills_visit')->constrained('table_visits')->onDelete('restrict');
            $table->string('bill_number', 40)->unique('uq_bills_number');
            $table->foreignId('tax_configuration_id')->nullable()->constrained('tax_configurations')->onDelete('restrict');
            $table->foreignId('generated_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['draft', 'issued', 'partially_paid', 'paid', 'voided'])->default('draft');
            $table->decimal('subtotal_amount', 10, 2)->default(0.00);
            $table->enum('discount_type', ['none', 'percentage', 'fixed'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('discount_reason', 255)->nullable();
            $table->foreignId('discount_approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->decimal('taxable_amount', 10, 2)->default(0.00);

            // GST Safety: Tax rates default to 0.00. Rates and amounts must be populated from an
            // explicitly selected, verified, and activated tax_configurations record upon issuance.
            $table->decimal('cgst_rate', 5, 2)->default(0.00);
            $table->decimal('cgst_amount', 10, 2)->default(0.00);
            $table->decimal('sgst_rate', 5, 2)->default(0.00);
            $table->decimal('sgst_amount', 10, 2)->default(0.00);
            $table->decimal('total_tax_amount', 10, 2)->default(0.00);

            $table->decimal('service_charge', 10, 2)->default(0.00);
            $table->decimal('round_off', 6, 2)->default(0.00);
            $table->decimal('final_amount', 10, 2)->default(0.00);
            $table->decimal('total_paid_amount', 10, 2)->default(0.00);
            $table->decimal('total_refunded_amount', 10, 2)->default(0.00);
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('settled_at')->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->string('void_reason', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index('status', 'idx_bills_status');
            $table->index('issued_at', 'idx_bills_issued_at');
            $table->index(['voided_at', 'voided_by'], 'idx_bills_voided');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
