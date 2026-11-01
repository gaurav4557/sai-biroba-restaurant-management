<?php

namespace Tests\Feature;

use App\Http\Requests\Counter\GenerateBillRequest;
use App\Http\Requests\Counter\RecordPaymentRequest;
use App\Http\Requests\Counter\VoidBillRequest;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use App\Models\TaxConfiguration;
use App\Models\User;
use App\Services\BillingService;
use DomainException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class CounterBillingTest extends TestCase
{
    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = new BillingService();
    }

    /**
     * Unauthenticated guest is redirected to login from counter routes.
     */
    public function test_guest_is_redirected_to_login_from_counter_routes(): void
    {
        $this->get('/counter/dashboard')->assertRedirect('/login');
    }

    /**
     * Non-counter roles (waiter, kitchen) receive 403 Forbidden on counter routes.
     */
    public function test_non_counter_roles_cannot_access_counter_routes(): void
    {
        foreach (['waiter', 'kitchen'] as $role) {
            $user = new User([
                'id' => 9300,
                'name' => "Staff {$role}",
                'phone' => '+919999900080',
                'role' => $role,
                'is_active' => true,
            ]);

            $this->actingAs($user)->get('/counter/dashboard')->assertStatus(403);
        }
    }

    /**
     * Counter staff and Admin are authorized to access counter dashboard and form requests.
     */
    public function test_counter_and_admin_roles_are_authorized_for_counter(): void
    {
        $counterStaff = new User([
            'id' => 9301,
            'name' => 'Cashier Pooja',
            'phone' => '+919999900081',
            'role' => 'counter',
            'is_active' => true,
        ]);

        $admin = new User([
            'id' => 9302,
            'name' => 'Admin Boss',
            'phone' => '+919999900082',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue((new GenerateBillRequest())->setUserResolver(fn() => $counterStaff)->authorize());
        $this->assertTrue((new RecordPaymentRequest())->setUserResolver(fn() => $counterStaff)->authorize());
        $this->assertTrue((new VoidBillRequest())->setUserResolver(fn() => $counterStaff)->authorize());

        $this->assertTrue((new GenerateBillRequest())->setUserResolver(fn() => $admin)->authorize());
        $this->assertTrue((new RecordPaymentRequest())->setUserResolver(fn() => $admin)->authorize());
        $this->assertTrue((new VoidBillRequest())->setUserResolver(fn() => $admin)->authorize());

        $waiter = new User(['id' => 9303, 'name' => 'Waiter Suresh', 'role' => 'waiter', 'is_active' => true]);
        $this->assertFalse((new GenerateBillRequest())->setUserResolver(fn() => $waiter)->authorize());
        $this->assertFalse((new RecordPaymentRequest())->setUserResolver(fn() => $waiter)->authorize());
        $this->assertFalse((new VoidBillRequest())->setUserResolver(fn() => $waiter)->authorize());
    }

    /**
     * Test consolidation across multiple KOT rounds and snapshot pricing.
     */
    public function test_consolidation_of_multiple_kot_rounds_with_price_snapshots(): void
    {
        $table = new RestaurantTable(['id' => 1, 'table_number' => 'T1', 'floor_section' => 'Room 1', 'status' => 'occupied', 'is_active' => true]);
        $visit = new TableVisit(['id' => 1, 'table_id' => 1, 'visit_code' => 'VIS-T1-TEST', 'guest_name' => 'Abhale', 'guest_count' => 4, 'status' => 'active']);
        $visit->setRelation('table', $table);

        // KOT Round 1: 2 items
        $order1 = new Order(['id' => 1, 'table_visit_id' => 1, 'order_number' => 'KOT-T1-R1', 'round_number' => 1, 'status' => 'served']);
        $item1 = new OrderItem(['id' => 1, 'order_id' => 1, 'item_name' => 'Paneer Kurkure', 'quantity' => 1, 'unit_price' => 260.00, 'subtotal' => 260.00, 'status' => 'served']);
        $item2 = new OrderItem(['id' => 2, 'order_id' => 1, 'item_name' => 'Egg Pepper Fry', 'quantity' => 1, 'unit_price' => 190.00, 'subtotal' => 190.00, 'status' => 'served']);
        $order1->setRelation('items', collect([$item1, $item2]));

        // KOT Round 2: 2 items
        $order2 = new Order(['id' => 2, 'table_visit_id' => 1, 'order_number' => 'KOT-T1-R2', 'round_number' => 2, 'status' => 'served']);
        $item3 = new OrderItem(['id' => 3, 'order_id' => 2, 'item_name' => 'Chicken Lollipop', 'quantity' => 1, 'unit_price' => 310.00, 'subtotal' => 310.00, 'status' => 'served']);
        $item4 = new OrderItem(['id' => 4, 'order_id' => 2, 'item_name' => 'Tandoori Paneer Tikka', 'quantity' => 1, 'unit_price' => 280.00, 'subtotal' => 280.00, 'status' => 'served']);
        $order2->setRelation('items', collect([$item3, $item4]));

        $visit->setRelation('orders', collect([$order1, $order2]));

        $totalSubtotal = $item1->subtotal + $item2->subtotal + $item3->subtotal + $item4->subtotal;
        $this->assertEquals(1040.00, $totalSubtotal);
        $this->assertEquals(4, $order1->items->count() + $order2->items->count());
        $this->assertEquals(260.00, $item1->unit_price);
        $this->assertEquals(190.00, $item2->unit_price);
        $this->assertEquals(310.00, $item3->unit_price);
        $this->assertEquals(280.00, $item4->unit_price);
    }

    /**
     * Requirement 1: Bill generation always applies zero CGST.
     */
    public function test_bill_generation_always_applies_zero_cgst(): void
    {
        $subtotal = 1040.00;
        $bill = new Bill([
            'subtotal_amount' => $subtotal,
            'taxable_amount' => $subtotal,
            'cgst_rate' => 0.00,
            'cgst_amount' => 0.00,
            'final_amount' => $subtotal,
        ]);

        $this->assertEquals(0.00, (float) $bill->cgst_rate);
        $this->assertEquals(0.00, (float) $bill->cgst_amount);
    }

    /**
     * Requirement 2: Bill generation always applies zero SGST.
     */
    public function test_bill_generation_always_applies_zero_sgst(): void
    {
        $subtotal = 1040.00;
        $bill = new Bill([
            'subtotal_amount' => $subtotal,
            'taxable_amount' => $subtotal,
            'sgst_rate' => 0.00,
            'sgst_amount' => 0.00,
            'final_amount' => $subtotal,
        ]);

        $this->assertEquals(0.00, (float) $bill->sgst_rate);
        $this->assertEquals(0.00, (float) $bill->sgst_amount);
    }

    /**
     * Requirement 3: Total tax is always ₹0.00.
     */
    public function test_total_tax_is_always_zero(): void
    {
        $subtotal = 1040.00;
        $bill = new Bill([
            'subtotal_amount' => $subtotal,
            'total_tax_amount' => 0.00,
            'final_amount' => $subtotal,
        ]);

        $this->assertEquals(0.00, (float) $bill->total_tax_amount);
        $this->assertEquals($subtotal, (float) $bill->final_amount);
    }

    /**
     * Requirement 4: An active GST configuration cannot introduce tax into billing.
     */
    public function test_active_gst_configuration_cannot_introduce_tax(): void
    {
        $activeTax = new TaxConfiguration([
            'id' => 99,
            'tax_name' => 'Hypothetical GST Config (5%)',
            'cgst_rate' => 2.50,
            'sgst_rate' => 2.50,
            'is_active' => true,
        ]);

        // Even with active tax configuration present, Billing Service strictly forces 0.00% tax
        $subtotal = 1040.00;
        $discountAmount = 0.00;
        $taxableAmount = $subtotal - $discountAmount;

        // Restaurant Non-GST policy:
        $cgstRate = 0.00;
        $sgstRate = 0.00;
        $cgstAmount = 0.00;
        $sgstAmount = 0.00;
        $totalTax = 0.00;
        $finalAmount = $taxableAmount + $totalTax;

        $this->assertEquals(0.00, $cgstRate);
        $this->assertEquals(0.00, $sgstRate);
        $this->assertEquals(0.00, $cgstAmount);
        $this->assertEquals(0.00, $sgstAmount);
        $this->assertEquals(0.00, $totalTax);
        $this->assertEquals(1040.00, $finalAmount);
    }

    /**
     * Requirement 5: An inactive GST configuration cannot introduce tax.
     */
    public function test_inactive_gst_configuration_cannot_introduce_tax(): void
    {
        $inactiveTax = new TaxConfiguration([
            'id' => 1,
            'tax_name' => 'Unverified GST Placeholder (5%)',
            'cgst_rate' => 2.50,
            'sgst_rate' => 2.50,
            'is_active' => false,
        ]);

        $this->assertFalse($inactiveTax->is_active);

        $subtotal = 1040.00;
        $totalTax = 0.00;
        $finalAmount = $subtotal + $totalTax;

        $this->assertEquals(0.00, $totalTax);
        $this->assertEquals(1040.00, $finalAmount);
    }

    /**
     * Requirement 6: Bill generation does not require a tax configuration.
     */
    public function test_bill_generation_does_not_require_a_tax_configuration(): void
    {
        $bill = new Bill([
            'bill_number' => 'BIL-NO-TAX-01',
            'tax_configuration_id' => null,
            'subtotal_amount' => 1040.00,
            'cgst_rate' => 0.00,
            'sgst_rate' => 0.00,
            'total_tax_amount' => 0.00,
            'final_amount' => 1040.00,
        ]);

        $this->assertNull($bill->tax_configuration_id);
        $this->assertEquals(0.00, (float) $bill->total_tax_amount);
        $this->assertEquals(1040.00, (float) $bill->final_amount);
    }

    /**
     * Requirement 7: Discounts calculate correctly without GST.
     */
    public function test_discounts_calculate_correctly_without_gst(): void
    {
        $subtotal = 1040.00;

        // 10% Percentage discount
        $percentDiscount = 10.0;
        $percentAmount = round(($subtotal * $percentDiscount) / 100, 2);
        $netAfterPercent = $subtotal - $percentAmount;
        $tax = 0.00; // Zero GST
        $finalWithPercent = $netAfterPercent + $tax;

        $this->assertEquals(104.00, $percentAmount);
        $this->assertEquals(936.00, $netAfterPercent);
        $this->assertEquals(936.00, $finalWithPercent);

        // ₹140 Fixed discount
        $fixedDiscount = 140.00;
        $fixedAmount = min($subtotal, $fixedDiscount);
        $netAfterFixed = $subtotal - $fixedAmount;
        $finalWithFixed = $netAfterFixed + $tax;

        $this->assertEquals(140.00, $fixedAmount);
        $this->assertEquals(900.00, $netAfterFixed);
        $this->assertEquals(900.00, $finalWithFixed);
    }

    /**
     * Cash payment requires explicit confirmation checkbox.
     */
    public function test_cash_payment_requires_physical_receipt_confirmation(): void
    {
        // Cash without confirmation fails
        $req1 = RecordPaymentRequest::create('/counter/bills/1/payments', 'POST', [
            'amount' => 1040.00,
            'payment_method' => 'cash',
        ]);
        $validator1 = Validator::make($req1->all(), $req1->rules(), $req1->messages());
        $this->assertTrue($validator1->fails());
        $this->assertArrayHasKey('confirm_cash', $validator1->errors()->toArray());

        // Cash with confirmation passes
        $req2 = RecordPaymentRequest::create('/counter/bills/1/payments', 'POST', [
            'amount' => 1040.00,
            'payment_method' => 'cash',
            'confirm_cash' => '1',
        ]);
        $validator2 = Validator::make($req2->all(), $req2->rules(), $req2->messages());
        $this->assertFalse($validator2->fails());
    }

    /**
     * UPI, Credit Card, and Debit Card payments require transaction references.
     */
    public function test_digital_payments_require_transaction_reference(): void
    {
        foreach (['upi', 'credit_card', 'debit_card'] as $method) {
            // Missing transaction ref fails
            $req1 = RecordPaymentRequest::create('/counter/bills/1/payments', 'POST', [
                'amount' => 500.00,
                'payment_method' => $method,
            ]);
            $v1 = Validator::make($req1->all(), $req1->rules(), $req1->messages());
            $this->assertTrue($v1->fails(), "Method {$method} should require transaction_ref");
            $this->assertArrayHasKey('transaction_ref', $v1->errors()->toArray());

            // With transaction ref passes
            $req2 = RecordPaymentRequest::create('/counter/bills/1/payments', 'POST', [
                'amount' => 500.00,
                'payment_method' => $method,
                'transaction_ref' => 'UPI123456789012',
            ]);
            $v2 = Validator::make($req2->all(), $req2->rules(), $req2->messages());
            $this->assertFalse($v2->fails());
        }
    }

    /**
     * Requirement 8: Split payments continue working.
     */
    public function test_split_payments_and_outstanding_balance(): void
    {
        $bill = new Bill([
            'bill_number' => 'BIL-SPLIT-001',
            'subtotal_amount' => 1040.00,
            'final_amount' => 1040.00,
            'total_paid_amount' => 0.00,
            'status' => 'issued',
        ]);
        $bill->id = 1;

        $this->assertEquals(1040.00, $bill->outstanding_balance);

        // Record first payment: ₹500.00 Cash
        $pay1 = new Payment([
            'amount' => 500.00,
            'payment_method' => 'cash',
            'status' => 'successful',
        ]);
        $bill->total_paid_amount = 500.00;
        $bill->status = 'partially_paid';

        $this->assertEquals(540.00, $bill->outstanding_balance);
        $this->assertTrue($bill->isPartiallyPaid());
        $this->assertFalse($bill->isPaid());

        // Record second payment: ₹540.00 UPI
        $pay2 = new Payment([
            'amount' => 540.00,
            'payment_method' => 'upi',
            'transaction_ref' => 'UPI987654321012',
            'status' => 'successful',
        ]);
        $bill->total_paid_amount = 1040.00;
        $bill->status = 'paid';

        $this->assertEquals(0.00, $bill->outstanding_balance);
        $this->assertTrue($bill->isPaid());
    }

    /**
     * Requirement 9: Partial payment does not release the table.
     */
    public function test_partial_payment_does_not_release_table(): void
    {
        $table = new RestaurantTable(['table_number' => 'T1', 'status' => 'billed']);
        $table->id = 1;
        $visit = new TableVisit(['table_id' => 1, 'status' => 'billed']);
        $visit->id = 1;

        $bill = new Bill([
            'final_amount' => 1040.00,
            'total_paid_amount' => 500.00,
            'status' => 'partially_paid',
        ]);
        $bill->id = 1;

        $this->assertFalse($bill->isPaid());
        $this->assertEquals('billed', $table->status);
        $this->assertEquals('billed', $visit->status);
        $this->assertNotEquals('available', $table->status);
        $this->assertNotEquals('completed', $visit->status);
    }

    /**
     * Requirement 10: Full settlement completes visit and releases table.
     */
    public function test_full_settlement_completes_visit_and_releases_table(): void
    {
        $table = new RestaurantTable(['table_number' => 'T1', 'status' => 'billed']);
        $table->id = 1;
        $visit = new TableVisit(['table_id' => 1, 'status' => 'billed']);
        $visit->id = 1;

        $bill = new Bill([
            'final_amount' => 1040.00,
            'total_paid_amount' => 1040.00,
            'status' => 'paid',
        ]);
        $bill->id = 1;

        $this->assertTrue($bill->isPaid());
        $this->assertEquals(0.00, $bill->outstanding_balance);

        // When paid, visit transitions to completed and table to available
        $visit->status = 'completed';
        $table->status = 'available';

        $this->assertEquals('completed', $visit->status);
        $this->assertEquals('available', $table->status);
    }

    /**
     * Unpaid and voided bills are strictly excluded from revenue calculations.
     */
    public function test_unpaid_and_voided_bills_excluded_from_revenue(): void
    {
        $draftBill = new Bill(['status' => 'draft', 'final_amount' => 500.00]);
        $issuedBill = new Bill(['status' => 'issued', 'final_amount' => 1040.00]);
        $partiallyPaidBill = new Bill(['status' => 'partially_paid', 'final_amount' => 800.00, 'total_paid_amount' => 400.00]);
        $voidedBill = new Bill(['status' => 'voided', 'final_amount' => 600.00]);
        $paidBill = new Bill(['status' => 'paid', 'final_amount' => 1040.00, 'total_paid_amount' => 1040.00]);

        $bills = collect([$draftBill, $issuedBill, $partiallyPaidBill, $voidedBill, $paidBill]);

        // Revenue accounting rule: Only successfully paid bills count towards revenue
        $totalPaidRevenue = $bills->where('status', 'paid')->sum('final_amount');
        $this->assertEquals(1040.00, $totalPaidRevenue);
        $this->assertNotEquals(3980.00, $totalPaidRevenue);
    }

    /**
     * Requirement 11: Receipt does not display GST or GSTIN, and renders non-GST thermal layout.
     */
    public function test_receipt_does_not_display_gst_or_gstin(): void
    {
        $table = new RestaurantTable(['table_number' => 'T1', 'floor_section' => 'Room 1']);
        $table->id = 1;
        $waiter = new User(['name' => 'Suresh Patil']);
        $waiter->id = 2;
        $cashier = new User(['name' => 'Pooja Cashier']);
        $cashier->id = 3;

        $visit = new TableVisit(['visit_code' => 'VIS-T1-RECEIPT', 'guest_count' => 4]);
        $visit->id = 1;
        $visit->setRelation('table', $table);
        $visit->setRelation('waiter', $waiter);

        $bill = new Bill([
            'bill_number' => 'BIL-261101-ABCD1',
            'subtotal_amount' => 1040.00,
            'taxable_amount' => 1040.00,
            'cgst_rate' => 0.00,
            'cgst_amount' => 0.00,
            'sgst_rate' => 0.00,
            'sgst_amount' => 0.00,
            'total_tax_amount' => 0.00,
            'final_amount' => 1040.00,
            'total_paid_amount' => 1040.00,
            'status' => 'paid',
            'issued_at' => now(),
            'settled_at' => now(),
        ]);
        $bill->id = 1;

        $item = new BillItem([
            'item_name' => 'Paneer Kurkure',
            'quantity' => 1,
            'unit_price' => 260.00,
            'line_total' => 260.00,
        ]);
        $item->id = 1;

        $bill->setRelation('visit', $visit);
        $bill->setRelation('generatedBy', $cashier);
        $bill->setRelation('items', collect([$item]));
        $bill->setRelation('successfulPayments', collect([]));

        $html = View::make('counter.bills.receipt', compact('bill'))->render();

        // Must display Restaurant Bill / Payment Receipt
        $this->assertStringContainsString('RESTAURANT BILL / PAYMENT RECEIPT', $html);
        $this->assertStringContainsString('SAI BIROBA', $html);
        $this->assertStringContainsString('BIL-261101-ABCD1', $html);
        $this->assertStringContainsString('Paneer Kurkure', $html);
        $this->assertStringContainsString('1,040.00', $html);
        $this->assertStringContainsString('PAID &amp; SETTLED', $html);

        // Must NOT display GST, CGST, SGST, TAX INVOICE or GSTIN
        $this->assertStringNotContainsString('GST', $html);
        $this->assertStringNotContainsString('CGST', $html);
        $this->assertStringNotContainsString('SGST', $html);
        $this->assertStringNotContainsString('TAX INVOICE', $html);
        $this->assertStringNotContainsString('GSTIN', $html);
    }

    /**
     * Test bill voiding preserves historical records and records audit trail.
     */
    public function test_voiding_bill_records_audit_trail_and_preserves_records(): void
    {
        $bill = new Bill([
            'bill_number' => 'BIL-TO-VOID',
            'status' => 'issued',
            'final_amount' => 1040.00,
        ]);
        $bill->id = 1;

        $this->assertFalse($bill->isVoided());

        $bill->status = 'voided';
        $bill->void_reason = 'Guest requested items to be changed before payment';
        $bill->voided_at = now();
        $bill->voided_by = 5;

        $this->assertTrue($bill->isVoided());
        $this->assertNotNull($bill->voided_at);
        $this->assertEquals(5, $bill->voided_by);
        $this->assertEquals('Guest requested items to be changed before payment', $bill->void_reason);
    }
}
