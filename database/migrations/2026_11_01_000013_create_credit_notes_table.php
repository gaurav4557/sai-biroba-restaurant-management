<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('credit_note_number', 40)->unique('uq_credit_notes_number');
            $table->foreignId('bill_id')->constrained('bills')->onDelete('restrict');
            $table->foreignId('table_visit_id')->constrained('table_visits')->onDelete('restrict');
            $table->foreignId('authorized_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('issued_by')->constrained('users')->onDelete('restrict');
            $table->decimal('subtotal_credited', 10, 2)->default(0.00);

            // GST Safety: Tax rates default to 0.00, snapshotted directly from the parent bill record
            $table->decimal('cgst_rate', 5, 2)->default(0.00);
            $table->decimal('cgst_amount', 10, 2)->default(0.00);
            $table->decimal('sgst_rate', 5, 2)->default(0.00);
            $table->decimal('sgst_amount', 10, 2)->default(0.00);
            $table->decimal('total_tax_credited', 10, 2)->default(0.00);

            $table->decimal('total_credit_amount', 10, 2);
            $table->string('reason', 255);
            $table->dateTime('issued_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();

            $table->index('bill_id', 'idx_credit_notes_bill');
            $table->index('issued_at', 'idx_credit_notes_issued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
