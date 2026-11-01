<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates existing users table without recreating it or removing existing Laravel columns.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adds phone number with unique constraint. Nullable allows existing user records to migrate cleanly.
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->unique('uq_users_phone')->after('name');
            }

            // Role is strictly nullable without any default. Pre-existing accounts remain with role = NULL,
            // preventing unauthorized access until explicitly assigned a role by an administrator.
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'waiter', 'kitchen', 'counter'])->nullable()->after('email');
            }

            // Adds account status toggle
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password');
            }

            // Preserves original Laravel email length (255) while allowing nullability for phone-first authentication
            $table->string('email', 255)->nullable()->change();

            // Adds composite index for role-based status queries
            $table->index(['role', 'is_active'], 'idx_users_role_status');
        });
    }

    /**
     * Reverse the migrations.
     * Safely rolls back only the newly added columns/indexes without removing existing Laravel user records or original columns.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_status');
            $table->dropUnique('uq_users_phone');
            $table->dropColumn(['phone', 'role', 'is_active']);
            $table->string('email', 255)->nullable(false)->change();
        });
    }
};
