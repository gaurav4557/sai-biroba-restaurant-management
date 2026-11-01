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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('table_number', 20)->unique('uq_tables_number');
            $table->unsignedSmallInteger('seating_capacity')->default(4);
            $table->string('floor_section', 50)->default('Main Hall');
            $table->enum('status', ['available', 'occupied', 'billed', 'out_of_service'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('status', 'idx_tables_status');
            $table->index('floor_section', 'idx_tables_section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
