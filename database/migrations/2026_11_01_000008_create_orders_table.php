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
        Schema::create('orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('table_visit_id');
            $table->unsignedBigInteger('table_id');
            $table->foreignId('waiter_id')->constrained('users')->onDelete('restrict');
            $table->string('order_number', 40)->unique('uq_orders_number');
            $table->unsignedSmallInteger('round_number')->default(1);
            $table->enum('status', ['submitted', 'preparing', 'ready', 'served', 'cancelled'])->default('submitted');
            $table->string('order_notes', 255)->nullable();
            $table->string('idempotency_token', 64)->nullable();
            $table->dateTime('submitted_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->string('cancellation_reason', 255)->nullable();
            $table->timestamps();

            // Composite Foreign Key: Simultaneously provides referential integrity to table_visits
            // and guarantees orders.table_id cannot differ from table_visits.table_id
            $table->foreign(['table_visit_id', 'table_id'], 'fk_orders_visit_table')
                ->references(['id', 'table_id'])
                ->on('table_visits')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('table_id')->references('id')->on('restaurant_tables')->onDelete('restrict');

            $table->unique(['table_visit_id', 'idempotency_token'], 'uq_orders_idempotency');
            $table->index('table_visit_id', 'idx_orders_visit');
            $table->index(['status', 'submitted_at'], 'idx_orders_kds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
