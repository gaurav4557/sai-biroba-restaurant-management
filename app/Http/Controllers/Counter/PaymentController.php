<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counter\RecordPaymentRequest;
use App\Models\Bill;
use App\Services\BillingService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    /**
     * Record a payment against an issued or partially paid bill.
     */
    public function store(RecordPaymentRequest $request, Bill $bill): RedirectResponse
    {
        try {
            $payment = $this->billingService->recordPayment(
                $bill,
                $request->validated(),
                $request->user()
            );

            $refreshedBill = $bill->fresh();

            if ($refreshedBill->isPaid()) {
                return redirect()
                    ->route('counter.bills.show', $bill)
                    ->with('success', "Payment of ₹" . number_format($payment->amount, 2) . " confirmed. Bill is fully paid! Table {$bill->visit->table->table_number} has been released.");
            }

            return redirect()
                ->route('counter.bills.show', $bill)
                ->with('success', "Payment of ₹" . number_format($payment->amount, 2) . " recorded successfully. Remaining balance: " . $refreshedBill->formatted_outstanding_balance . ".");
        } catch (DomainException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
