<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counter\GenerateBillRequest;
use App\Http\Requests\Counter\VoidBillRequest;
use App\Models\Bill;
use App\Models\TableVisit;
use App\Models\TaxConfiguration;
use App\Services\BillingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Show the consolidated bill generation and preview screen for a dining visit.
     */
    public function create(Request $request, TableVisit $visit): View|RedirectResponse
    {
        // If a non-voided bill already exists for this visit, redirect directly to it
        $existingBill = Bill::where('table_visit_id', $visit->id)
            ->where('status', '!=', 'voided')
            ->first();

        if ($existingBill) {
            return redirect()
                ->route('counter.bills.show', $existingBill)
                ->with('info', "A bill (#{$existingBill->bill_number}) has already been generated for this visit.");
        }

        $visit->load(['table', 'waiter', 'orders.items']);

        // Check if visit has any eligible items
        $hasItems = $visit->orders
            ->where('status', '!=', 'cancelled')
            ->flatMap->items
            ->where('status', '!=', 'cancelled')
            ->isNotEmpty();

        if (!$hasItems) {
            return redirect()
                ->route('counter.dashboard')
                ->with('error', 'Cannot generate bill: Table ' . ($visit->table->table_number ?? '') . ' has no active or served food items.');
        }

        $options = [
            'discount_type' => $request->query('discount_type', 'none'),
            'discount_value' => (float) $request->query('discount_value', 0.00),
            'discount_reason' => $request->query('discount_reason'),
            'service_charge' => (float) $request->query('service_charge', 0.00),
        ];

        $preview = $this->billingService->previewBill($visit, $options);

        return view('counter.bills.create', compact('visit', 'preview', 'options'));
    }

    /**
     * Store and issue the consolidated bill.
     */
    public function store(GenerateBillRequest $request, TableVisit $visit): RedirectResponse
    {
        try {
            $bill = $this->billingService->generateConsolidatedBill(
                $visit,
                $request->user(),
                $request->validated()
            );

            return redirect()
                ->route('counter.bills.show', $bill)
                ->with('success', "Bill #{$bill->bill_number} generated successfully for Table {$visit->table->table_number}.");
        } catch (DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the bill hub, payment recorder, and settlement status.
     */
    public function show(Bill $bill): View
    {
        $bill->load([
            'visit.table',
            'visit.waiter',
            'items.orderItem',
            'payments.recordedBy',
            'taxConfiguration',
            'generatedBy',
            'discountApprovedBy',
            'voidedBy',
        ]);

        return view('counter.bills.show', compact('bill'));
    }

    /**
     * Render the 80mm thermal receipt and standard printable invoice.
     */
    public function printReceipt(Bill $bill): View
    {
        $bill->load([
            'visit.table',
            'visit.waiter',
            'items',
            'successfulPayments.recordedBy',
            'taxConfiguration',
            'generatedBy',
        ]);

        return view('counter.bills.receipt', compact('bill'));
    }

    /**
     * Void an unpaid or issued bill with an authorized reason.
     */
    public function void(VoidBillRequest $request, Bill $bill): RedirectResponse
    {
        try {
            $voidedBill = $this->billingService->voidBill(
                $bill,
                $request->validated('void_reason'),
                $request->user()
            );

            return redirect()
                ->route('counter.dashboard')
                ->with('success', "Bill #{$voidedBill->bill_number} has been voided. Table and dining visit have been restored.");
        } catch (DomainException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
