<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Rules:
     * 1. Requires ADMIN_PHONE to be explicitly configured in .env.
     * 2. Preserves existing passwords when accounts already exist.
     * 3. Configures 4 roles: admin, waiter, kitchen, and counter.
     * 4. Demo staff accounts are only seeded in local/development/testing when SEED_SAMPLE_STAFF=true.
     */
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // 1. Primary Administrator Account
        // ---------------------------------------------------------------------
        $adminPhone = env('ADMIN_PHONE');
        if (empty($adminPhone)) {
            throw new \RuntimeException(
                "SECURITY ERROR: The 'ADMIN_PHONE' environment variable is required in your .env file before running AdminUserSeeder. " .
                "Please configure ADMIN_PHONE=+91XXXXXXXXXX in .env and retry."
            );
        }

        $adminEmail = env('ADMIN_EMAIL', 'admin@saibiroba.com');
        $adminName = env('ADMIN_NAME', 'System Administrator');

        // Check if the administrator account already exists by phone or email
        $existingAdmin = DB::table('users')
            ->where('phone', $adminPhone)
            ->orWhere('email', $adminEmail)
            ->first();

        if (!$existingAdmin) {
            // Retrieve password from environment or generate a cryptographically strong 16-character password
            $adminPasswordPlain = env('ADMIN_PASSWORD') ?? env('ADMIN_DEFAULT_PASSWORD');
            if (empty($adminPasswordPlain)) {
                $adminPasswordPlain = Str::password(16, letters: true, numbers: true, symbols: true, spaces: false);
                if ($this->command) {
                    $this->command->warn("----------------------------------------------------------------------");
                    $this->command->warn("SECURITY NOTICE: No ADMIN_PASSWORD provided in .env.");
                    $this->command->warn("Generated random secure password for Admin: {$adminPasswordPlain}");
                    $this->command->warn("Save this password or configure ADMIN_PASSWORD in your .env file.");
                    $this->command->warn("----------------------------------------------------------------------");
                }
            }

            DB::table('users')->insert([
                'name' => $adminName,
                'phone' => $adminPhone,
                'email' => $adminEmail,
                'role' => 'admin',
                'password' => Hash::make($adminPasswordPlain),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($this->command) {
                $this->command->info("Administrator account created successfully for {$adminPhone}.");
            }
        } else {
            // Idempotent safeguard: Existing admin password is NEVER overwritten on rerun
            DB::table('users')->where('id', $existingAdmin->id)->update([
                'name' => $adminName,
                'phone' => $adminPhone,
                'email' => $adminEmail,
                'role' => 'admin',
                'is_active' => true,
                'updated_at' => now(),
            ]);

            if ($this->command) {
                $this->command->info("Existing administrator account ({$existingAdmin->phone}) retained. Password was preserved.");
            }
        }

        // ---------------------------------------------------------------------
        // 2. Demo Staff Accounts (Waiter, Kitchen, Counter)
        // ---------------------------------------------------------------------
        // Allowed only in local, development, or testing environments when SEED_SAMPLE_STAFF=true
        $isAllowedEnvironment = app()->environment('local', 'development', 'testing');
        $seedStaffFlag = filter_var(env('SEED_SAMPLE_STAFF', false), FILTER_VALIDATE_BOOLEAN);
        $allowSampleStaff = $isAllowedEnvironment && $seedStaffFlag;

        if ($allowSampleStaff) {
            $demoStaffUsers = [
                [
                    'name' => '[DEMO] Ramesh Kumar (Waiter)',
                    'phone' => '+910000000001',
                    'email' => 'demo.waiter1@saibiroba.local',
                    'role' => 'waiter',
                ],
                [
                    'name' => '[DEMO] Suresh Patil (Waiter)',
                    'phone' => '+910000000002',
                    'email' => 'demo.waiter2@saibiroba.local',
                    'role' => 'waiter',
                ],
                [
                    'name' => '[DEMO] Chef Vikram (Kitchen)',
                    'phone' => '+910000000003',
                    'email' => 'demo.kitchen@saibiroba.local',
                    'role' => 'kitchen',
                ],
                [
                    'name' => '[DEMO] Pooja Cashier (Counter)',
                    'phone' => '+910000000004',
                    'email' => 'demo.counter@saibiroba.local',
                    'role' => 'counter',
                ],
            ];

            $staffPasswordPlain = env('STAFF_PASSWORD') ?? env('STAFF_DEFAULT_PASSWORD');
            if (empty($staffPasswordPlain)) {
                $staffPasswordPlain = Str::password(14, letters: true, numbers: true, symbols: false, spaces: false);
                if ($this->command) {
                    $this->command->warn("SECURITY NOTICE: Generated random secure password for sample staff: {$staffPasswordPlain}");
                }
            }

            foreach ($demoStaffUsers as $staff) {
                $existingStaff = DB::table('users')->where('phone', $staff['phone'])->first();

                if (!$existingStaff) {
                    DB::table('users')->insert([
                        'name' => $staff['name'],
                        'phone' => $staff['phone'],
                        'email' => $staff['email'],
                        'role' => $staff['role'],
                        'password' => Hash::make($staffPasswordPlain),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Idempotent safeguard: Existing staff password is NEVER overwritten on rerun
                    DB::table('users')->where('id', $existingStaff->id)->update([
                        'name' => $staff['name'],
                        'email' => $staff['email'],
                        'role' => $staff['role'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($this->command) {
                $this->command->info("Demo development staff accounts verified/seeded with non-routable demo numbers (+91000000000X).");
            }
        } elseif ($this->command && !$seedStaffFlag) {
            $this->command->comment("Demo staff seeding skipped. Set SEED_SAMPLE_STAFF=true in .env to enable.");
        }
    }
}
