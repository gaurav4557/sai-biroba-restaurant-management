<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * COMPLIANCE & SAFETY NOTE:
     * This 5.00% GST configuration (2.50% CGST + 2.50% SGST) is seeded with is_active = false.
     * It is an UNVERIFIED PLACEHOLDER and is NOT assumed to be legally applicable.
     * The billing application must refuse to issue a tax invoice until authorized management
     * verifies statutory compliance and explicitly activates the configuration.
     */
    public function run(): void
    {
        $taxes = [
            [
                'tax_name' => 'Unverified GST Placeholder (5%)',
                'cgst_rate' => 2.50,
                'sgst_rate' => 2.50,
                'effective_from' => '2026-01-01',
                'effective_to' => null,
                'is_active' => false, // Inactive by default: Requires explicit managerial verification before activation
                'notes' => 'UNVERIFIED PLACEHOLDER (2.5% CGST + 2.5% SGST). Management and tax consultant must verify applicable statutory rates and activate before issuing commercial invoices.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($taxes as $tax) {
            DB::table('tax_configurations')->updateOrInsert(
                ['tax_name' => $tax['tax_name']],
                $tax
            );
        }
    }
}
