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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('restrict');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('restrict');
            $table->string('item_name', 150);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('item_subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('taxable_amount', 10, 2);

            // GST Safety: Tax rate defaults to 0.00. Populated strictly from the active, verified tax_configurations record.
            $table->decimal('tax_rate_percent', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);

            $table->decimal('line_total', 10, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index('bill_id', 'idx_bill_items_bill');
            $table->index('order_item_id', 'idx_bill_items_order_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};
