<?php

namespace App\Services;

use App\Models\RestaurantTable;
use App\Models\TableWaiterAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaiterAssignmentService
{
    /**
     * Assign an active waiter to an active, assignable dining table.
     *
     * @throws ValidationException
     */
    public function assign(int $tableId, int $waiterId, int $assignedBy, ?string $notes = null): TableWaiterAssignment
    {
        return DB::transaction(function () use ($tableId, $waiterId, $assignedBy, $notes) {
            // 1. Lock and validate table
            $table = RestaurantTable::where('id', $tableId)->lockForUpdate()->first();

            if (!$table || !$table->is_active) {
                throw ValidationException::withMessages([
                    'table_id' => 'The selected table is inactive or not found.',
                ]);
            }

            if (!$table->isAssignable()) {
                throw ValidationException::withMessages([
                    'table_id' => "Table {$table->table_number} cannot be assigned because its status is '{$table->status}'.",
                ]);
            }

            // 2. Lock and validate waiter
            $waiter = User::where('id', $waiterId)->lockForUpdate()->first();

            if (!$waiter || !$waiter->is_active) {
                throw ValidationException::withMessages([
                    'waiter_id' => 'The selected staff member is inactive or not found.',
                ]);
            }

            if ($waiter->role !== 'waiter') {
                throw ValidationException::withMessages([
                    'waiter_id' => "User '{$waiter->name}' does not have the waiter role.",
                ]);
            }

            // 3. Ensure no concurrent active assignment exists for this table
            $existingAssignment = TableWaiterAssignment::where('table_id', $table->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if ($existingAssignment) {
                throw ValidationException::withMessages([
                    'table_id' => "Table {$table->table_number} already has an active assigned waiter. Please use Reassign instead.",
                ]);
            }

            // 4. Create new active assignment (audit history preserved)
            return TableWaiterAssignment::create([
                'table_id' => $table->id,
                'waiter_id' => $waiter->id,
                'assigned_by' => $assignedBy,
                'status' => 'active',
                'assigned_at' => now(),
                'unassigned_at' => null,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Reassign an active dining table from one waiter to another.
     * Atomically marks the previous assignment 'reassigned' and opens a new 'active' assignment.
     *
     * @throws ValidationException
     */
    public function reassign(int $tableId, int $newWaiterId, int $assignedBy, ?string $notes = null): TableWaiterAssignment
    {
        return DB::transaction(function () use ($tableId, $newWaiterId, $assignedBy, $notes) {
            // 1. Lock and validate table
            $table = RestaurantTable::where('id', $tableId)->lockForUpdate()->first();

            if (!$table || !$table->is_active) {
                throw ValidationException::withMessages([
                    'table_id' => 'The selected table is inactive or not found.',
                ]);
            }

            if (!$table->isAssignable()) {
                throw ValidationException::withMessages([
                    'table_id' => "Table {$table->table_number} cannot be reassigned because its status is '{$table->status}'.",
                ]);
            }

            // 2. Lock and validate new waiter
            $newWaiter = User::where('id', $newWaiterId)->lockForUpdate()->first();

            if (!$newWaiter || !$newWaiter->is_active) {
                throw ValidationException::withMessages([
                    'waiter_id' => 'The selected staff member is inactive or not found.',
                ]);
            }

            if ($newWaiter->role !== 'waiter') {
                throw ValidationException::withMessages([
                    'waiter_id' => "User '{$newWaiter->name}' does not have the waiter role.",
                ]);
            }

            // 3. Find current active assignment
            $currentAssignment = TableWaiterAssignment::where('table_id', $table->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$currentAssignment) {
                throw ValidationException::withMessages([
                    'table_id' => "Table {$table->table_number} does not have an active assignment to reassign. Use Assign instead.",
                ]);
            }

            if ($currentAssignment->waiter_id === $newWaiter->id) {
                throw ValidationException::withMessages([
                    'waiter_id' => "Table {$table->table_number} is already actively assigned to {$newWaiter->name}.",
                ]);
            }

            // 4. Close the old assignment
            $oldWaiterName = $currentAssignment->waiter?->name ?? "Waiter #{$currentAssignment->waiter_id}";
            $currentAssignment->update([
                'status' => 'reassigned',
                'unassigned_at' => now(),
                'notes' => $notes ? "{$notes} (Reassigned to {$newWaiter->name})" : "Reassigned to {$newWaiter->name}",
            ]);

            // 5. Create new assignment
            return TableWaiterAssignment::create([
                'table_id' => $table->id,
                'waiter_id' => $newWaiter->id,
                'assigned_by' => $assignedBy,
                'status' => 'active',
                'assigned_at' => now(),
                'unassigned_at' => null,
                'notes' => $notes ? "{$notes} (Reassigned from {$oldWaiterName})" : "Reassigned from {$oldWaiterName}",
            ]);
        });
    }

    /**
     * Remove an active waiter assignment from a table.
     * Safely closes the assignment by setting status to 'completed' and recording unassigned_at.
     * Records are never deleted.
     *
     * @throws ValidationException
     */
    public function unassign(int $tableId, int $unassignedBy, ?string $notes = null): TableWaiterAssignment
    {
        return DB::transaction(function () use ($tableId, $unassignedBy, $notes) {
            $table = RestaurantTable::where('id', $tableId)->lockForUpdate()->first();

            if (!$table || !$table->is_active) {
                throw ValidationException::withMessages([
                    'table_id' => 'The selected table is inactive or not found.',
                ]);
            }

            $currentAssignment = TableWaiterAssignment::where('table_id', $table->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$currentAssignment) {
                throw ValidationException::withMessages([
                    'table_id' => "Table {$table->table_number} has no active assignment to remove.",
                ]);
            }

            $currentAssignment->update([
                'status' => 'completed',
                'unassigned_at' => now(),
                'notes' => $notes ?? 'Unassigned by administrator',
            ]);

            return $currentAssignment;
        });
    }
}
