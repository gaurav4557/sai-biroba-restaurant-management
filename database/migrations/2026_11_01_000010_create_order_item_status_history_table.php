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
        Schema::create('order_item_status_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('restrict');
            $table->enum('previous_status', ['pending', 'preparing', 'ready', 'served', 'cancelled'])->nullable();
            $table->enum('new_status', ['pending', 'preparing', 'ready', 'served', 'cancelled']);
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->string('reason', 255)->nullable();
            $table->dateTime('changed_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();

            $table->index(['order_item_id', 'changed_at'], 'idx_status_history_item');
            $table->index('changed_by', 'idx_status_history_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_status_history');
    }
};
