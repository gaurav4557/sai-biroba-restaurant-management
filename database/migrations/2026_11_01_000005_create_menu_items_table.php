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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('item_code', 30)->nullable()->unique('uq_menu_items_code');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('dietary_type', ['veg', 'non_veg', 'egg'])->default('veg');
            $table->unsignedTinyInteger('spice_level')->default(1);
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('prep_time_minutes')->default(15);
            $table->timestamps();

            $table->index(['category_id', 'is_available'], 'idx_menu_items_category');
            $table->index('dietary_type', 'idx_menu_items_dietary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
