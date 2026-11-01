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
        Schema::create('table_visits', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('table_id')->constrained('restaurant_tables')->onDelete('restrict');
            $table->foreignId('waiter_id')->constrained('users')->onDelete('restrict');
            $table->string('visit_code', 32)->unique('uq_visits_code');
            $table->string('guest_name', 100)->nullable();
            $table->unsignedSmallInteger('guest_count')->default(1);
            $table->enum('status', ['active', 'billed', 'completed', 'cancelled'])->default('active');
            $table->dateTime('opened_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('closed_at')->nullable();

            // MySQL 8.0 Virtual Generated Column: Prevents concurrent active sessions on the same table
            $table->unsignedBigInteger('active_table_id')
                ->virtualAs("IF(status IN ('active', 'billed'), table_id, NULL)")
                ->nullable();

            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique('active_table_id', 'uq_active_visit_per_table');
            // Composite unique index enabling composite foreign key from orders
            $table->unique(['id', 'table_id'], 'uq_visit_table');
            $table->index(['table_id', 'status'], 'idx_visits_table_status');
            $table->index(['waiter_id', 'opened_at'], 'idx_visits_waiter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_visits');
    }
};
