<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestaurantTableSeeder extends Seeder
{
    /**
     * Run the database seeds for Sai Biroba Restaurant dining tables.
     *
     * Layout Specifications:
     * - Room 1: Exactly 10 active tables (T1–T10)
     * - Room 2: Exactly 10 active tables (T11–T20)
     * - Total: Exactly 20 active dining tables
     *
     * Strict Database-Integrity & Operational Safeguards:
     * 1. Active Session Protection: Inspects table_waiter_assignments and table_visits before
     *    archiving or renaming any table.
     * 2. Transaction Abort on Active Sessions: If an active/billed dining visit or active waiter
     *    assignment exists on a legacy or obsolete table, the entire transaction is aborted with
     *    a clear explanatory error. Active customer sessions are never silently archived or disrupted.
     * 3. No Operational Data Transfer: Operational states (active visits, orders, statuses) are NEVER
     *    copied or transferred across different table IDs. Referential integrity remains absolute.
     * 4. Non-Destructive: Never deletes any table record; all historical IDs and foreign keys
     *    in table_visits, orders, and waiter assignments are permanently preserved.
     * 5. Collision Safety: Resolves legacy collisions (e.g. T-01 vs T1) safely without unique key conflicts.
     * 6. Obsolete Table Protection: Applies the same active-session inspection to Garden (G-01..G-04),
     *    Rooftop (R-01..R-02), or any other retired tables before setting is_active=false.
     * 7. Exact Layout Verification: Enforces exactly 10 tables per room and 20 total active tables.
     * 8. Transactional & Idempotent: Entire run executes in DB::transaction(); safe to rerun repeatedly.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // -----------------------------------------------------------------
            // Step 1: Safely resolve legacy table renames (T-01..T-06 -> T1..T6)
            // -----------------------------------------------------------------
            $legacyRenameMap = [
                'T-01' => 'T1',
                'T-02' => 'T2',
                'T-03' => 'T3',
                'T-04' => 'T4',
                'T-05' => 'T5',
                'T-06' => 'T6',
            ];

            foreach ($legacyRenameMap as $oldNumber => $newNumber) {
                $oldTable = DB::table('restaurant_tables')->where('table_number', $oldNumber)->first();
                $newTable = DB::table('restaurant_tables')->where('table_number', $newNumber)->first();

                if (!$oldTable) {
                    continue;
                }

                // Integrity Check: Inspect legacy table for any unresolved active sessions
                $activeIssue = $this->getActiveSessionDetails($oldTable);
                if ($activeIssue !== null) {
                    throw new \RuntimeException(
                        "Cannot migrate legacy table '{$oldNumber}' (ID: {$oldTable->id}): " .
                        "{$activeIssue['detail']}. " .
                        "Active dining sessions and waiter assignments must be settled and closed before restructuring table layouts."
                    );
                }

                if (!$newTable) {
                    // Safe In-Place Rename: Old table has no active session and target number is free.
                    // Renames in-place, preserving exact primary key ID, seating capacity, and history.
                    DB::table('restaurant_tables')->where('id', $oldTable->id)->update([
                        'table_number' => $newNumber,
                        'floor_section' => 'Room 1',
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                } else {
                    // Collision Case: Both old (e.g. T-01) and new (e.g. T1) co-exist.
                    // $oldTable has no active session. Never delete $oldTable (preserves historical FKs).
                    // Archive $oldTable in-place. Do NOT transfer operational records between IDs.
                    DB::table('restaurant_tables')->where('id', $oldTable->id)->update([
                        'is_active' => false,
                        'updated_at' => now(),
                    ]);

                    // Ensure $newTable is active in Room 1 without touching its existing operational records
                    DB::table('restaurant_tables')->where('id', $newTable->id)->update([
                        'floor_section' => 'Room 1',
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                }
            }

            // -----------------------------------------------------------------
            // Step 2: Ensure all 20 active tables exist in their assigned rooms
            // -----------------------------------------------------------------
            $expectedTables = [
                // Room 1 (T1–T10)
                ['table_number' => 'T1',  'floor_section' => 'Room 1'],
                ['table_number' => 'T2',  'floor_section' => 'Room 1'],
                ['table_number' => 'T3',  'floor_section' => 'Room 1'],
                ['table_number' => 'T4',  'floor_section' => 'Room 1'],
                ['table_number' => 'T5',  'floor_section' => 'Room 1'],
                ['table_number' => 'T6',  'floor_section' => 'Room 1'],
                ['table_number' => 'T7',  'floor_section' => 'Room 1'],
                ['table_number' => 'T8',  'floor_section' => 'Room 1'],
                ['table_number' => 'T9',  'floor_section' => 'Room 1'],
                ['table_number' => 'T10', 'floor_section' => 'Room 1'],

                // Room 2 (T11–T20)
                ['table_number' => 'T11', 'floor_section' => 'Room 2'],
                ['table_number' => 'T12', 'floor_section' => 'Room 2'],
                ['table_number' => 'T13', 'floor_section' => 'Room 2'],
                ['table_number' => 'T14', 'floor_section' => 'Room 2'],
                ['table_number' => 'T15', 'floor_section' => 'Room 2'],
                ['table_number' => 'T16', 'floor_section' => 'Room 2'],
                ['table_number' => 'T17', 'floor_section' => 'Room 2'],
                ['table_number' => 'T18', 'floor_section' => 'Room 2'],
                ['table_number' => 'T19', 'floor_section' => 'Room 2'],
                ['table_number' => 'T20', 'floor_section' => 'Room 2'],
            ];

            $activeTableNumbers = [];

            foreach ($expectedTables as $t) {
                $activeTableNumbers[] = $t['table_number'];
                $existing = DB::table('restaurant_tables')->where('table_number', $t['table_number'])->first();

                if (!$existing) {
                    // New Table: Insert with standard default capacity 4 and status 'available'
                    DB::table('restaurant_tables')->insert([
                        'table_number' => $t['table_number'],
                        'seating_capacity' => 4,
                        'floor_section' => $t['floor_section'],
                        'status' => 'available',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Existing Table: Preserve existing seating_capacity and status unchanged.
                    // Only ensure correct room assignment and active status.
                    DB::table('restaurant_tables')->where('id', $existing->id)->update([
                        'floor_section' => $t['floor_section'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                }
            }

            // -----------------------------------------------------------------
            // Step 3: Archive obsolete legacy tables (with active session guard)
            // -----------------------------------------------------------------
            // Identify any table not in the approved 20 active table list (such as
            // G-01..G-04, R-01..R-02, or unrenamed legacy rows).
            $obsoleteTables = DB::table('restaurant_tables')
                ->whereNotIn('table_number', $activeTableNumbers)
                ->where('is_active', true)
                ->get();

            foreach ($obsoleteTables as $obsTable) {
                // Integrity Guard: Abort if any obsolete table currently has an active session
                $activeIssue = $this->getActiveSessionDetails($obsTable);
                if ($activeIssue !== null) {
                    throw new \RuntimeException(
                        "Cannot archive obsolete table '{$obsTable->table_number}' (ID: {$obsTable->id}, Section: '{$obsTable->floor_section}'): " .
                        "{$activeIssue['detail']}. " .
                        "Active dining sessions and waiter assignments must be settled and closed before retiring tables."
                    );
                }

                // Safe Archiving: Mark inactive without deleting historical records
                DB::table('restaurant_tables')->where('id', $obsTable->id)->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
            }

            // -----------------------------------------------------------------
            // Step 4: Strict Verification & Layout Validation
            // -----------------------------------------------------------------
            $report = self::verifyLayout();

            if (!$report['is_valid']) {
                throw new \RuntimeException(
                    "Seeder verification failed! Active table layout does not meet strict specifications:\n" .
                    "- Room 1 Count: {$report['room_1']['count']}/10\n" .
                    "- Room 2 Count: {$report['room_2']['count']}/10\n" .
                    "- Total Active: {$report['total_active']}/20\n" .
                    "Transaction rolled back."
                );
            }

            if ($this->command) {
                $this->command->info("======================================================================");
                $this->command->info("   SAI BIROBA RESTAURANT TABLE SEEDER VERIFICATION REPORT");
                $this->command->info("======================================================================");
                $this->command->info("Room 1 Active Tables (10) : " . implode(', ', $report['room_1']['tables']));
                $this->command->info("Room 2 Active Tables (10) : " . implode(', ', $report['room_2']['tables']));
                $this->command->info("Total Active Tables       : {$report['total_active']} (Target: Exactly 20)");
                $this->command->info("Total Archived Tables     : {$report['total_archived']} (Preserved in DB)");
                $this->command->info("Layout Validation Status  : PASSED & VERIFIED");
                $this->command->info("======================================================================");
            }
        });
    }

    /**
     * Inspect a table for any unresolved active dining sessions or active waiter assignments.
     * Returns an array with issue details if active, or null if clean.
     *
     * @param object $table Row object from restaurant_tables
     * @return array{type: string, detail: string}|null
     */
    protected function getActiveSessionDetails(object $table): ?array
    {
        // 1. Check for active or billed dining visits
        if (Schema::hasTable('table_visits')) {
            $activeVisit = DB::table('table_visits')
                ->where('table_id', $table->id)
                ->whereIn('status', ['active', 'billed'])
                ->first();

            if ($activeVisit) {
                return [
                    'type' => 'active_visit',
                    'detail' => "Active dining visit found [Visit Code: {$activeVisit->visit_code}, Status: '{$activeVisit->status}', Guest Count: {$activeVisit->guest_count}]",
                ];
            }
        }

        // 2. Check for active waiter assignments
        if (Schema::hasTable('table_waiter_assignments')) {
            $activeAssignment = DB::table('table_waiter_assignments')
                ->where('table_id', $table->id)
                ->where('status', 'active')
                ->first();

            if ($activeAssignment) {
                return [
                    'type' => 'active_waiter_assignment',
                    'detail' => "Active waiter assignment found [Assignment ID: {$activeAssignment->id}, Waiter ID: {$activeAssignment->waiter_id}]",
                ];
            }
        }

        // 3. Check for operational table status flag
        if (in_array($table->status, ['occupied', 'billed'])) {
            return [
                'type' => 'active_table_status',
                'detail' => "Table operational status flag is marked as '{$table->status}'",
            ];
        }

        return null;
    }

    /**
     * Audit and verify the restaurant table layout.
     * Can be invoked programmatically or queried directly from Tinker/CLI.
     *
     * @return array{
     *     room_1: array{count: int, tables: list<string>},
     *     room_2: array{count: int, tables: list<string>},
     *     total_active: int,
     *     total_archived: int,
     *     is_valid: bool
     * }
     */
    public static function verifyLayout(): array
    {
        $room1Tables = DB::table('restaurant_tables')
            ->where('is_active', true)
            ->where('floor_section', 'Room 1')
            ->pluck('table_number')
            ->toArray();
        natsort($room1Tables);
        $room1Tables = array_values($room1Tables);

        $room2Tables = DB::table('restaurant_tables')
            ->where('is_active', true)
            ->where('floor_section', 'Room 2')
            ->pluck('table_number')
            ->toArray();
        natsort($room2Tables);
        $room2Tables = array_values($room2Tables);

        $totalActive = DB::table('restaurant_tables')->where('is_active', true)->count();
        $totalArchived = DB::table('restaurant_tables')->where('is_active', false)->count();

        $expectedRoom1 = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10'];
        $expectedRoom2 = ['T11', 'T12', 'T13', 'T14', 'T15', 'T16', 'T17', 'T18', 'T19', 'T20'];

        $isValid = count($room1Tables) === 10
            && count($room2Tables) === 10
            && $totalActive === 20
            && empty(array_diff($expectedRoom1, $room1Tables))
            && empty(array_diff($expectedRoom2, $room2Tables));

        return [
            'room_1' => [
                'count' => count($room1Tables),
                'tables' => $room1Tables,
            ],
            'room_2' => [
                'count' => count($room2Tables),
                'tables' => $room2Tables,
            ],
            'total_active' => $totalActive,
            'total_archived' => $totalArchived,
            'is_valid' => $isValid,
        ];
    }
}
