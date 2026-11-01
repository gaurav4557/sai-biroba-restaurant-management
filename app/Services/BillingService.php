<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use App\Models\TaxConfiguration;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingService
{
    /**
     * Preview bill calculations for a dining visit without persisting.
     */
    public function previewBill(TableVisit $visit, array $options = []): array
    {
        $orders = Order::where('table_visit_id', $visit->id)
            ->where('status', '!=', 'cancelled')
            ->with(['items' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }])
            ->get();

        $subtotal = 0.00;
        $items = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $subtotal += (float) $item->subtotal;
                $items[] = [
                    'order_item_id' => $item->id,
                    'order_number' => $order->order_number,
                    'round_number' => $order->round_number,
                    'item_name' => $item->item_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'special_instructions' => $item->special_instructions,
                ];
            }
        }

        $discountType = $options['discount_type'] ?? 'none';
        $discountValue = max(0.00, (float) ($options['discount_value'] ?? 0.00));
        $discountAmount = 0.00;

        if ($discountType === 'percentage') {
            $cappedPercent = min(100.0, $discountValue);
            $discountAmount = round(($subtotal * $cappedPercent) / 100, 2);
        } elseif ($discountType === 'fixed') {
            $discountAmount = min($subtotal, $discountValue);
        } else {
            $discountType = 'none';
            $discountValue = 0.00;
        }

        $taxableAmount = max(0.00, round($subtotal - $discountAmount, 2));

        // Restaurant Policy: No GST Billing Software Configuration
        // GST is completely disabled. CGST, SGST, and total tax are strictly 0.00.
        $cgstRate = 0.00;
        $sgstRate = 0.00;
        $cgstAmount = 0.00;
        $sgstAmount = 0.00;
        $totalTaxAmount = 0.00;

        $serviceCharge = max(0.00, (float) ($options['service_charge'] ?? 0.00));
        $rawFinal = $taxableAmount + $serviceCharge;
        $finalAmount = round($rawFinal, 0, PHP_ROUND_HALF_UP);
        $roundOff = round($finalAmount - $rawFinal, 2);

        return [
            'visit' => $visit,
            'items' => $items,
            'item_count' => count($items),
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'discount_reason' => $options['discount_reason'] ?? null,
            'taxable_amount' => $taxableAmount,
            'tax_configuration' => null,
            'cgst_rate' => $cgstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_rate' => $sgstRate,
            'sgst_amount' => $sgstAmount,
            'total_tax_amount' => $totalTaxAmount,
            'service_charge' => $serviceCharge,
            'round_off' => $roundOff,
            'final_amount' => $finalAmount,
        ];
    }

    /**
     * Generate and issue a consolidated bill for a dining session.
     * Enforces single bill per visit, server-calculated totals, and database transactions.
     */
    public function generateConsolidatedBill(TableVisit $visit, User $staff, array $options = []): Bill
    {
        return DB::transaction(function () use ($visit, $staff, $options) {
            // Lock the visit row to prevent concurrent bill generation
            $lockedVisit = TableVisit::where('id', $visit->id)->lockForUpdate()->firstOrFail();

            // Enforce single bill per visit invariant
            $existingBill = Bill::where('table_visit_id', $lockedVisit->id)
                ->where('status', '!=', 'voided')
                ->first();

            if ($existingBill) {
                throw new DomainException("A bill (#{$existingBill->bill_number}) has already been generated for this dining visit.");
            }

            if (!in_array($lockedVisit->status, ['active', 'billed'])) {
                throw new DomainException("Cannot generate a bill for a visit with status '{$lockedVisit->status}'.");
            }

            // Fetch all eligible orders and items across all KOT rounds
            $orders = Order::where('table_visit_id', $lockedVisit->id)
                ->where('status', '!=', 'cancelled')
                ->with(['items' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                }])
                ->get();

            $orderItems = [];
            $subtotal = 0.00;

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    $subtotal += (float) $item->subtotal;
                    $orderItems[] = $item;
                }
            }

            if (empty($orderItems)) {
                throw new DomainException("Cannot generate bill: no active or served food items found for this dining session.");
            }

            // Server-side Discount Processing
            $discountType = $options['discount_type'] ?? 'none';
            $discountValue = max(0.00, (float) ($options['discount_value'] ?? 0.00));
            $discountAmount = 0.00;
            $discountReason = $options['discount_reason'] ?? null;
            $discountApprovedBy = $options['discount_approved_by'] ?? null;

            if ($discountType === 'percentage') {
                $cappedPercent = min(100.0, $discountValue);
                $discountAmount = round(($subtotal * $cappedPercent) / 100, 2);
            } elseif ($discountType === 'fixed') {
                $discountAmount = min($subtotal, $discountValue);
            } else {
                $discountType = 'none';
                $discountValue = 0.00;
            }

            if ($discountAmount > 0 && empty($discountReason)) {
                $discountReason = 'Managerial Discount';
            }

            $taxableAmount = max(0.00, round($subtotal - $discountAmount, 2));

            // Restaurant Policy: No GST Billing Software Configuration
            // GST is completely disabled. CGST, SGST, and total tax are strictly 0.00.
            $cgstRate = 0.00;
            $sgstRate = 0.00;
            $cgstAmount = 0.00;
            $sgstAmount = 0.00;
            $totalTaxAmount = 0.00;
            $persistedTaxConfigId = null;

            $serviceCharge = max(0.00, (float) ($options['service_charge'] ?? 0.00));
            $rawFinal = $taxableAmount + $serviceCharge;
            $finalAmount = round($rawFinal, 0, PHP_ROUND_HALF_UP);
            $roundOff = round($finalAmount - $rawFinal, 2);

            // Generate unique Bill Number: BIL-YYMMDD-XXXX
            $datePrefix = now()->format('ymd');
            do {
                $billNumber = 'BIL-' . $datePrefix . '-' . strtoupper(Str::random(5));
            } while (Bill::where('bill_number', $billNumber)->exists());

            // Persist the consolidated Bill
            $bill = Bill::create([
                'table_visit_id' => $lockedVisit->id,
                'bill_number' => $billNumber,
                'tax_configuration_id' => $persistedTaxConfigId,
                'generated_by' => $staff->id,
                'status' => 'issued',
                'subtotal_amount' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'discount_reason' => $discountReason,
                'discount_approved_by' => $discountApprovedBy,
                'taxable_amount' => $taxableAmount,
                'cgst_rate' => $cgstRate,
                'cgst_amount' => $cgstAmount,
                'sgst_rate' => $sgstRate,
                'sgst_amount' => $sgstAmount,
                'total_tax_amount' => $totalTaxAmount,
                'service_charge' => $serviceCharge,
                'round_off' => $roundOff,
                'final_amount' => $finalAmount,
                'total_paid_amount' => 0.00,
                'total_refunded_amount' => 0.00,
                'issued_at' => now(),
                'notes' => $options['notes'] ?? null,
            ]);

            // Persist Bill Line Items from snapshot prices
            foreach ($orderItems as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'order_item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'item_subtotal' => (float) $item->subtotal,
                    'discount_amount' => 0.00,
                    'taxable_amount' => (float) $item->subtotal,
                    'tax_rate_percent' => 0.00,
                    'tax_amount' => 0.00,
                    'line_total' => (float) $item->subtotal,
                    'notes' => $item->special_instructions,
                ]);
            }

            // Update visit and table status to 'billed'
            $lockedVisit->status = 'billed';
            $lockedVisit->save();

            $table = RestaurantTable::where('id', $lockedVisit->table_id)->lockForUpdate()->first();
            if ($table) {
                $table->status = 'billed';
                $table->save();
            }

            return $bill->load(['items', 'visit.table', 'visit.waiter', 'generatedBy', 'taxConfiguration']);
        });
    }

    /**
     * Record a payment against an issued or partially paid bill.
     * Supports Cash (with confirmation), UPI, Credit Card, Debit Card, and split payments.
     */
    public function recordPayment(Bill $bill, array $data, User $staff): Payment
    {
        return DB::transaction(function () use ($bill, $data, $staff) {
            $lockedBill = Bill::where('id', $bill->id)->lockForUpdate()->firstOrFail();

            if ($lockedBill->isPaid()) {
                throw new DomainException("Bill #{$lockedBill->bill_number} has already been fully paid.");
            }

            if ($lockedBill->isVoided()) {
                throw new DomainException("Cannot record payment for a voided bill.");
            }

            $amount = round((float) ($data['amount'] ?? 0.00), 2);
            if ($amount <= 0.00) {
                throw new DomainException("Payment amount must be greater than zero.");
            }

            $outstanding = round((float) $lockedBill->final_amount - (float) $lockedBill->total_paid_amount, 2);
            if ($amount > $outstanding) {
                throw new DomainException("Payment amount (₹" . number_format($amount, 2) . ") exceeds outstanding balance (₹" . number_format($outstanding, 2) . ").");
            }

            $method = $data['payment_method'] ?? 'cash';
            if (!in_array($method, ['cash', 'upi', 'credit_card', 'debit_card', 'other'])) {
                throw new DomainException("Unsupported payment method '{$method}'.");
            }

            // Cash Confirmation Rule
            if ($method === 'cash') {
                $confirmCash = filter_var($data['confirm_cash'] ?? false, FILTER_VALIDATE_BOOLEAN);
                if (!$confirmCash) {
                    throw new DomainException("Counter operator must explicitly confirm physical receipt of cash.");
                }
            }

            // Digital Evidence Requirement
            if (in_array($method, ['upi', 'credit_card', 'debit_card'])) {
                $txnRef = trim($data['transaction_ref'] ?? '');
                if (empty($txnRef)) {
                    throw new DomainException("Transaction reference is required for " . strtoupper(str_replace('_', ' ', $method)) . " payment confirmation.");
                }
            } else {
                $txnRef = trim($data['transaction_ref'] ?? '') ?: null;
            }

            // Generate unique Payment Reference: PAY-YYMMDD-XXXXXX
            $datePrefix = now()->format('ymd');
            do {
                $paymentRef = 'PAY-' . $datePrefix . '-' . strtoupper(Str::random(6));
            } while (Payment::where('payment_ref', $paymentRef)->exists());

            // Create Payment record
            $payment = Payment::create([
                'bill_id' => $lockedBill->id,
                'payment_ref' => $paymentRef,
                'amount' => $amount,
                'payment_method' => $method,
                'transaction_ref' => $txnRef,
                'status' => 'successful',
                'refund_status' => 'none',
                'recorded_by' => $staff->id,
                'paid_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Update Bill total paid amount
            $newPaidTotal = round((float) $lockedBill->total_paid_amount + $amount, 2);
            $lockedBill->total_paid_amount = $newPaidTotal;

            if ($newPaidTotal >= (float) $lockedBill->final_amount) {
                // Bill is fully paid! Settle visit and release table
                $lockedBill->status = 'paid';
                $lockedBill->settled_at = now();
                $lockedBill->save();

                $this->finalizeSettlement($lockedBill);
            } else {
                $lockedBill->status = 'partially_paid';
                $lockedBill->save();
            }

            return $payment;
        });
    }

    /**
     * Settle a bill explicitly when balance is zero.
     */
    public function settleBill(Bill $bill, User $staff): Bill
    {
        return DB::transaction(function () use ($bill) {
            $lockedBill = Bill::where('id', $bill->id)->lockForUpdate()->firstOrFail();

            if ($lockedBill->isPaid()) {
                return $lockedBill;
            }

            $outstanding = round((float) $lockedBill->final_amount - (float) $lockedBill->total_paid_amount, 2);
            if ($outstanding > 0.00) {
                throw new DomainException("Cannot settle bill: outstanding balance of ₹" . number_format($outstanding, 2) . " remains.");
            }

            $lockedBill->status = 'paid';
            $lockedBill->settled_at = now();
            $lockedBill->save();

            $this->finalizeSettlement($lockedBill);

            return $lockedBill;
        });
    }

    /**
     * Complete the dining session visit and release the restaurant table to available.
     * Must be called only from within a database transaction after bill is fully paid.
     */
    protected function finalizeSettlement(Bill $bill): void
    {
        $visit = TableVisit::where('id', $bill->table_visit_id)->lockForUpdate()->first();
        if ($visit) {
            $visit->status = 'completed';
            $visit->closed_at = now();
            $visit->save();

            $table = RestaurantTable::where('id', $visit->table_id)->lockForUpdate()->first();
            if ($table) {
                $table->status = 'available';
                $table->save();
            }
        }
    }

    /**
     * Void an unpaid or issued bill with an authorized reason.
     * Preserves financial audit trail; restores table visit to active status.
     */
    public function voidBill(Bill $bill, string $reason, User $authorizer): Bill
    {
        return DB::transaction(function () use ($bill, $reason, $authorizer) {
            $lockedBill = Bill::where('id', $bill->id)->lockForUpdate()->firstOrFail();

            if ($lockedBill->isPaid()) {
                throw new DomainException("Cannot void an already paid bill (#{$lockedBill->bill_number}). Refunds or credit notes must be issued instead.");
            }

            if ($lockedBill->isVoided()) {
                throw new DomainException("Bill #{$lockedBill->bill_number} is already voided.");
            }

            if (empty(trim($reason))) {
                throw new DomainException("A specific void reason is required to void a bill.");
            }

            $lockedBill->status = 'voided';
            $lockedBill->voided_at = now();
            $lockedBill->voided_by = $authorizer->id;
            $lockedBill->void_reason = trim($reason);
            $lockedBill->save();

            // Revert table visit back to active, and table back to occupied
            $visit = TableVisit::where('id', $lockedBill->table_visit_id)->lockForUpdate()->first();
            if ($visit && $visit->status === 'billed') {
                $visit->status = 'active';
                $visit->save();

                $table = RestaurantTable::where('id', $visit->table_id)->lockForUpdate()->first();
                if ($table && $table->status === 'billed') {
                    $table->status = 'occupied';
                    $table->save();
                }
            }

            return $lockedBill;
        });
    }
}
