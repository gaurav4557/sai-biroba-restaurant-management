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
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('credit_note_id')->constrained('credit_notes')->onDelete('restrict');
            $table->foreignId('bill_item_id')->nullable()->constrained('bill_items')->onDelete('restrict');
            $table->string('item_name', 150);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('line_total', 10, 2);
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->index('credit_note_id', 'idx_credit_note_items_cn');
            $table->index('bill_item_id', 'idx_credit_note_items_bill_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
    }
};
