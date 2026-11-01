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
        Schema::create('table_waiter_assignments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('table_id')->constrained('restaurant_tables')->onDelete('restrict');
            $table->foreignId('waiter_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['active', 'completed', 'reassigned'])->default('active');
            $table->dateTime('assigned_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('unassigned_at')->nullable();

            // MySQL 8.0 Virtual Generated Column: Enforces 1 active waiter per table
            $table->unsignedBigInteger('active_table_id')
                ->virtualAs("IF(status = 'active', table_id, NULL)")
                ->nullable();

            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique('active_table_id', 'uq_active_waiter_per_table');
            $table->index(['waiter_id', 'status'], 'idx_assignments_waiter');
            $table->index(['table_id', 'assigned_at'], 'idx_assignments_table_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_waiter_assignments');
    }
};
