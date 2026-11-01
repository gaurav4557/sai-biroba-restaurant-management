@extends('layouts.app', ['title' => 'Bill #' . $bill->bill_number . ' — Table ' . ($bill->visit->table->table_number ?? '')])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full space-y-6">

    <!-- Top Action Breadcrumb Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('counter.dashboard') }}" class="inline-flex items-center text-xs text-gray-400 hover:text-white transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to POS Floor
            </a>
            <span class="text-gray-600">/</span>
            <span class="font-mono text-xs text-white font-bold">{{ $bill->bill_number }}</span>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('counter.bills.receipt', $bill) }}" target="_blank" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-[#262626] hover:bg-[#333] text-white border border-[#3A3A3A] shadow transition-all">
                <svg class="w-4 h-4 mr-1.5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Thermal Receipt</span>
            </a>
        </div>
    </div>

    <!-- Alert Banners -->
    @if(session('success'))
        <div class="bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm flex items-center space-x-3 shadow-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-950/60 border border-red-500/40 text-red-300 px-4 py-3 rounded-xl text-sm flex items-center space-x-3 shadow-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Header Card -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#2D2D2D] to-[#1E1E1E] border border-[#3D3D3D] flex items-center justify-center font-display font-bold text-2xl text-[#D4AF37] shadow-inner">
                {{ $bill->visit->table->table_number ?? '-' }}
            </div>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="font-display font-bold text-xl text-[#FDFBF7]">
                        Bill #{{ $bill->bill_number }}
                    </h1>
                    @if($bill->status === 'paid')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                            Paid &bull; Settled
                        </span>
                    @elseif($bill->status === 'partially_paid')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-amber-500/15 text-amber-400 border border-amber-500/30">
                            Partially Paid
                        </span>
                    @elseif($bill->status === 'voided')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-red-500/15 text-red-400 border border-red-500/30">
                            Voided
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-sky-500/15 text-sky-400 border border-sky-500/30">
                            Issued &bull; Awaiting Payment
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    Table {{ $bill->visit->table->table_number ?? '-' }} &bull; {{ $bill->visit->table->floor_section ?? '' }} &bull;
                    Session: <span class="font-mono text-gray-300">{{ $bill->visit->visit_code }}</span> &bull;
                    Issued by {{ $bill->generatedBy->name ?? 'System' }} on {{ $bill->issued_at ? $bill->issued_at->format('d M Y, h:i A') : '' }}
                </p>
            </div>
        </div>

        <!-- Financial KPI Pill Trio -->
        <div class="flex items-center space-x-3">
            <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-4 py-2.5 text-right">
                <div class="text-[10px] text-gray-400 uppercase tracking-wider">Final Bill</div>
                <div class="font-mono font-bold text-lg text-white">
                    ₹{{ number_format((float) $bill->final_amount, 2) }}
                </div>
            </div>

            <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-4 py-2.5 text-right">
                <div class="text-[10px] text-gray-400 uppercase tracking-wider">Total Paid</div>
                <div class="font-mono font-bold text-lg text-emerald-400">
                    ₹{{ number_format((float) $bill->total_paid_amount, 2) }}
                </div>
            </div>

            <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-4 py-2.5 text-right">
                <div class="text-[10px] text-gray-400 uppercase tracking-wider">Outstanding</div>
                <div class="font-mono font-bold text-lg {{ $bill->outstanding_balance > 0 ? 'text-amber-400' : 'text-gray-400' }}">
                    ₹{{ number_format($bill->outstanding_balance, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Banner if Fully Paid -->
    @if($bill->isPaid())
        <div class="bg-gradient-to-r from-emerald-950/40 via-emerald-900/30 to-emerald-950/40 border border-emerald-500/40 rounded-2xl p-5 flex items-center justify-between shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-white text-base">Dining Session Completed &amp; Settle Verified</h3>
                    <p class="text-xs text-emerald-300/80 mt-0.5">
                        Settled at {{ $bill->settled_at ? $bill->settled_at->format('d M Y, h:i A') : 'Recently' }}. Table {{ $bill->visit->table->table_number ?? '-' }} is now available for new guests.
                    </p>
                </div>
            </div>
            <a href="{{ route('counter.bills.receipt', $bill) }}" target="_blank" class="px-4 py-2 rounded-xl font-bold text-xs bg-emerald-500 hover:bg-emerald-400 text-gray-950 shadow transition-all">
                Print Bill Receipt
            </a>
        </div>
    @endif

    <!-- Two-Column Section: Items & Summary vs Payment Ledger -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Line Items & Summary -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Itemized Table -->
            <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl overflow-hidden">
                <div class="p-5 border-b border-[#2B2B2B] flex items-center justify-between bg-[#1D1D1D]">
                    <h2 class="font-display font-bold text-base text-[#FDFBF7]">
                        Billed Items &bull; {{ $bill->items->count() }} Dishes
                    </h2>
                    <span class="text-xs text-gray-400">Merged from all KOT rounds</span>
                </div>

                <div class="divide-y divide-[#262626]">
                    @foreach($bill->items as $item)
                        <div class="p-4 flex items-center justify-between hover:bg-[#1E1E1E]/50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <span class="w-7 h-7 rounded-lg bg-[#282828] border border-[#3A3A3A] flex items-center justify-center font-mono font-bold text-xs text-gray-200">
                                    {{ $item->quantity }}x
                                </span>
                                <div>
                                    <div class="font-semibold text-white text-xs">{{ $item->item_name }}</div>
                                    @if($item->notes)
                                        <div class="text-[10px] text-amber-400/80 italic">{{ $item->notes }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-mono font-bold text-xs text-white">
                                    ₹{{ number_format((float) $item->line_total, 2) }}
                                </div>
                                <div class="text-[10px] text-gray-400 font-mono">
                                    @ ₹{{ number_format((float) $item->unit_price, 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Detailed Financial Breakdown Box -->
            <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl space-y-3">
                <h3 class="font-display font-bold text-sm text-[#FDFBF7] border-b border-[#2B2B2B] pb-2">
                    Bill Price Breakdown
                </h3>

                <div class="flex justify-between text-xs text-gray-400">
                    <span>Food Subtotal</span>
                    <span class="font-mono text-white">₹{{ number_format((float) $bill->subtotal_amount, 2) }}</span>
                </div>

                @if((float) $bill->discount_amount > 0)
                    <div class="flex justify-between text-xs text-emerald-400">
                        <span>Discount ({{ ucfirst($bill->discount_type) }}: {{ $bill->discount_reason ?: 'Applied' }})</span>
                        <span class="font-mono">-₹{{ number_format((float) $bill->discount_amount, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-xs text-gray-400">
                    <span>Net Food Amount</span>
                    <span class="font-mono text-white">₹{{ number_format((float) $bill->taxable_amount, 2) }}</span>
                </div>

                <div class="flex justify-between text-xs text-gray-400">
                    <span>GST (CGST 0% + SGST 0%)</span>
                    <span class="font-mono text-gray-400">Not Applied (₹0.00)</span>
                </div>

                @if((float) $bill->service_charge > 0)
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Service Charge</span>
                        <span class="font-mono text-white">₹{{ number_format((float) $bill->service_charge, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-xs text-gray-500">
                    <span>Round Off</span>
                    <span class="font-mono">{{ (float) $bill->round_off >= 0 ? '+₹' : '-₹' }}{{ number_format(abs((float) $bill->round_off), 2) }}</span>
                </div>

                <div class="pt-3 border-t border-[#333] flex justify-between items-center text-sm font-bold">
                    <span class="text-white uppercase tracking-wider text-xs">Total Amount</span>
                    <span class="font-mono text-xl text-[#D4AF37]">₹{{ number_format((float) $bill->final_amount, 2) }}</span>
                </div>
            </div>

        </div>

        <!-- Right: Payment Processing & Payment History -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Payment Form (if not paid and not voided) -->
            @if(!$bill->isPaid() && !$bill->isVoided())
                <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl p-6 space-y-5">
                    <div class="border-b border-[#2B2B2B] pb-3 flex items-center justify-between">
                        <h2 class="font-display font-bold text-base text-[#FDFBF7]">
                            Record Payment
                        </h2>
                        <span class="font-mono font-bold text-amber-400 text-sm">
                            Balance: ₹{{ number_format($bill->outstanding_balance, 2) }}
                        </span>
                    </div>

                    <form action="{{ route('counter.payments.store', $bill) }}" method="POST" id="paymentForm" class="space-y-4">
                        @csrf

                        <!-- Payment Amount -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                                Payment Amount (₹)
                            </label>
                            <input type="number" step="0.01" min="0.01" max="{{ $bill->outstanding_balance }}" name="amount" id="payAmount" value="{{ old('amount', $bill->outstanding_balance) }}" required class="w-full bg-[#1E1E1E] border border-[#3D3D3D] rounded-xl px-3.5 py-2.5 font-mono font-bold text-white text-base focus:outline-none focus:border-[#D4AF37]" oninput="updateChangeCalculation()">
                            <p class="text-[10px] text-gray-400 mt-1">Supports full or partial / split payments.</p>
                        </div>

                        <!-- Payment Method Radios -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                                Payment Method
                            </label>

                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="cash" class="sr-only peer" {{ old('payment_method', 'cash') === 'cash' ? 'checked' : '' }} onchange="togglePaymentFields()">
                                    <div class="py-2.5 px-3 rounded-xl border border-[#3A3A3A] bg-[#242424] text-xs font-semibold text-gray-300 peer-checked:border-[#D4AF37] peer-checked:bg-[#D4AF37]/15 peer-checked:text-[#D4AF37] flex items-center space-x-2 transition-all">
                                        <span>💵 Cash</span>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="upi" class="sr-only peer" {{ old('payment_method') === 'upi' ? 'checked' : '' }} onchange="togglePaymentFields()">
                                    <div class="py-2.5 px-3 rounded-xl border border-[#3A3A3A] bg-[#242424] text-xs font-semibold text-gray-300 peer-checked:border-sky-500 peer-checked:bg-sky-500/15 peer-checked:text-sky-400 flex items-center space-x-2 transition-all">
                                        <span>📱 UPI / QR</span>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="credit_card" class="sr-only peer" {{ old('payment_method') === 'credit_card' ? 'checked' : '' }} onchange="togglePaymentFields()">
                                    <div class="py-2.5 px-3 rounded-xl border border-[#3A3A3A] bg-[#242424] text-xs font-semibold text-gray-300 peer-checked:border-purple-500 peer-checked:bg-purple-500/15 peer-checked:text-purple-400 flex items-center space-x-2 transition-all">
                                        <span>💳 Credit Card</span>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="debit_card" class="sr-only peer" {{ old('payment_method') === 'debit_card' ? 'checked' : '' }} onchange="togglePaymentFields()">
                                    <div class="py-2.5 px-3 rounded-xl border border-[#3A3A3A] bg-[#242424] text-xs font-semibold text-gray-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-500/15 peer-checked:text-indigo-400 flex items-center space-x-2 transition-all">
                                        <span>💳 Debit Card</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Cash Specific Controls -->
                        <div id="cashFields" class="space-y-3 bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl p-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-300 uppercase tracking-wider mb-1">
                                    Cash Tendered by Guest (₹)
                                </label>
                                <input type="number" step="1" min="0" id="cashTendered" placeholder="e.g. 500, 2000" class="w-full bg-[#181818] border border-[#3D3D3D] rounded-lg px-3 py-2 font-mono text-sm text-white focus:outline-none focus:border-[#D4AF37]" oninput="updateChangeCalculation()">
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1 border-t border-[#2D2D2D]">
                                <span class="text-gray-400">Change Due to Guest:</span>
                                <span class="font-mono font-bold text-sm text-emerald-400" id="changeDue">₹0.00</span>
                            </div>

                            <label class="flex items-start space-x-2.5 pt-2 border-t border-[#2D2D2D] cursor-pointer">
                                <input type="checkbox" name="confirm_cash" value="1" id="confirmCash" required class="mt-0.5 rounded border-gray-600 text-[#D4AF37] focus:ring-[#D4AF37]">
                                <span class="text-xs text-gray-300">
                                    I explicitly confirm physical receipt of cash from the guest.
                                </span>
                            </label>
                        </div>

                        <!-- Digital Evidence Ref Field (UPI / Card) -->
                        <div id="digitalFields" class="hidden space-y-2 bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl p-4">
                            <label class="block text-[11px] font-semibold text-gray-300 uppercase tracking-wider">
                                Transaction Ref / Auth Slip Code <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="transaction_ref" id="transactionRef" placeholder="Enter UPI UTR (12 digits) or Card Auth Code" class="w-full bg-[#181818] border border-[#3D3D3D] rounded-lg px-3 py-2 text-xs font-mono text-white focus:outline-none focus:border-[#D4AF37]">
                            <p class="text-[10px] text-gray-400">Mandatory verification evidence for cashless payments.</p>
                        </div>

                        <!-- Payment Notes -->
                        <input type="text" name="notes" placeholder="Payment notes (optional)" class="w-full bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-3 py-2 text-xs text-gray-300 focus:outline-none focus:border-[#D4AF37]">

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-xs bg-gradient-to-r from-emerald-600 to-emerald-500 hover:brightness-110 text-white shadow-lg transition-all flex items-center justify-center space-x-2">
                            <span>Confirm &amp; Record Payment</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </form>
                </div>
            @endif

            <!-- Recorded Payment History Ledger -->
            <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl overflow-hidden">
                <div class="p-5 border-b border-[#2B2B2B] flex items-center justify-between bg-[#1D1D1D]">
                    <h2 class="font-display font-bold text-base text-[#FDFBF7]">
                        Payment Ledger
                    </h2>
                    <span class="text-xs text-emerald-400 font-mono font-bold">
                        ₹{{ number_format((float) $bill->total_paid_amount, 2) }} Paid
                    </span>
                </div>

                @if($bill->payments->isEmpty())
                    <div class="p-6 text-center text-xs text-gray-500">
                        No payments recorded yet for this bill.
                    </div>
                @else
                    <div class="divide-y divide-[#262626]">
                        @foreach($bill->payments as $payment)
                            <div class="p-4 flex items-center justify-between text-xs">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-mono font-bold text-white text-xs">
                                            ₹{{ number_format((float) $payment->amount, 2) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-[#2B2B2B] text-gray-300 border border-[#3A3A3A]">
                                            {{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-1 font-mono">
                                        Ref: {{ $payment->payment_ref }}
                                        @if($payment->transaction_ref)
                                            &bull; Txn: {{ $payment->transaction_ref }}
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                    <div class="text-[10px] text-gray-500 mt-1">
                                        {{ $payment->paid_at ? $payment->paid_at->format('h:i A') : '' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Void Bill Option (Only for unpaid bills) -->
            @if(!$bill->isPaid() && !$bill->isVoided())
                <div class="bg-[#181818] border border-red-500/30 rounded-2xl p-5 shadow-xl space-y-3">
                    <h3 class="font-display font-bold text-xs text-red-400 uppercase tracking-wider">
                        Void Bill &bull; Administrative Control
                    </h3>
                    <p class="text-[11px] text-gray-400 leading-relaxed">
                        If bill details need correction before settlement, voiding the bill will revert the dining visit back to active state while preserving all audit logs.
                    </p>

                    <form action="{{ route('counter.bills.void', $bill) }}" method="POST" onsubmit="return confirm('Are you sure you want to void this bill? This action cannot be undone.');">
                        @csrf
                        <div class="space-y-2">
                            <input type="text" name="void_reason" required placeholder="Mandatory reason for voiding bill..." class="w-full bg-[#1E1E1E] border border-red-500/40 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-400">
                            <button type="submit" class="w-full py-2 px-3 rounded-lg text-xs font-semibold bg-red-950/80 hover:bg-red-900 border border-red-500/50 text-red-300 transition-colors">
                                Void This Bill
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>

<script>
function togglePaymentFields() {
    const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
    const cashFields = document.getElementById('cashFields');
    const digitalFields = document.getElementById('digitalFields');
    const confirmCash = document.getElementById('confirmCash');
    const txnRef = document.getElementById('transactionRef');

    if (method === 'cash') {
        cashFields?.classList.remove('hidden');
        digitalFields?.classList.add('hidden');
        if (confirmCash) confirmCash.required = true;
        if (txnRef) txnRef.required = false;
    } else {
        cashFields?.classList.add('hidden');
        digitalFields?.classList.remove('hidden');
        if (confirmCash) confirmCash.required = false;
        if (txnRef) txnRef.required = true;
    }
}

function updateChangeCalculation() {
    const payAmount = parseFloat(document.getElementById('payAmount')?.value) || 0;
    const tendered = parseFloat(document.getElementById('cashTendered')?.value) || 0;
    const change = Math.max(0, tendered - payAmount);
    const changeDueEl = document.getElementById('changeDue');
    if (changeDueEl) {
        changeDueEl.textContent = '₹' + change.toFixed(2);
    }
}

document.addEventListener('DOMContentLoaded', togglePaymentFields);
</script>
@endsection
