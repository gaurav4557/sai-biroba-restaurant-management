@extends('layouts.app', ['title' => 'Generate Consolidated Bill — Table ' . ($visit->table->table_number ?? '')])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full space-y-6">

    <!-- Top Navigation Breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('counter.dashboard') }}" class="inline-flex items-center text-xs text-gray-400 hover:text-white transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to POS Floor
            </a>
            <span class="text-gray-600">/</span>
            <span class="text-xs text-[#D4AF37] font-semibold">Generate Bill</span>
        </div>
        <div class="text-xs text-gray-400">
            Session: <span class="font-mono text-white font-bold">{{ $visit->visit_code }}</span>
        </div>
    </div>

    <!-- Error Banner -->
    @if(session('error'))
        <div class="bg-red-950/60 border border-red-500/40 text-red-300 px-4 py-3 rounded-xl text-sm flex items-center space-x-3 shadow-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column (7 cols): Order Consolidation Breakdown -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Table Info Card -->
            <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#2D2D2D] to-[#1E1E1E] border border-[#3D3D3D] flex items-center justify-center font-display font-bold text-2xl text-[#D4AF37] shadow-inner">
                        {{ $visit->table->table_number }}
                    </div>
                    <div>
                        <h1 class="font-display font-bold text-xl text-[#FDFBF7]">
                            Table {{ $visit->table->table_number }} &bull; {{ $visit->table->floor_section }}
                        </h1>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Waiter: <span class="text-gray-200 font-medium">{{ $visit->waiter->name ?? 'Unassigned' }}</span> &bull;
                            Guest: <span class="text-gray-200 font-medium">{{ $visit->guest_name ?: 'Walk-in' }}</span> ({{ $visit->guest_count }} pax)
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-amber-500/15 text-amber-400 border border-amber-500/30">
                        {{ $preview['item_count'] }} Items &bull; {{ $visit->orders->count() }} {{ Str::plural('Round', $visit->orders->count()) }}
                    </span>
                    <div class="text-[11px] text-gray-500 mt-1">
                        Opened {{ $visit->opened_at ? $visit->opened_at->format('h:i A') : '' }}
                    </div>
                </div>
            </div>

            <!-- Merged KOT Order Items by Round -->
            <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl overflow-hidden">
                <div class="p-5 border-b border-[#2B2B2B] flex items-center justify-between bg-[#1D1D1D]">
                    <h2 class="font-display font-bold text-base text-[#FDFBF7]">
                        Consolidated Kitchen Order Tickets (KOT)
                    </h2>
                    <span class="text-xs text-gray-400">Read-only Snapshot Prices</span>
                </div>

                <div class="divide-y divide-[#262626]">
                    @foreach($visit->orders->where('status', '!=', 'cancelled') as $order)
                        <div class="p-4 bg-[#181818]/80">
                            <!-- Round Header -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-[#2B2B2B] text-amber-400 border border-[#3A3A3A]">
                                        Round {{ $order->round_number }}
                                    </span>
                                    <span class="font-mono text-xs text-gray-400 font-semibold">{{ $order->order_number }}</span>
                                </div>
                                <span class="text-[11px] text-gray-500">
                                    {{ $order->submitted_at ? $order->submitted_at->format('h:i A') : '' }}
                                </span>
                            </div>

                            <!-- Items in this Round -->
                            <div class="space-y-2">
                                @foreach($order->items->where('status', '!=', 'cancelled') as $item)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-[#202020] border border-[#2D2D2D] text-xs">
                                        <div class="flex items-center space-x-3">
                                            <span class="w-6 h-6 rounded-lg bg-[#2B2B2B] flex items-center justify-center font-mono font-bold text-gray-300">
                                                {{ $item->quantity }}x
                                            </span>
                                            <div>
                                                <div class="font-semibold text-white">{{ $item->item_name }}</div>
                                                @if($item->special_instructions)
                                                    <div class="text-[10px] text-amber-400/80 italic">
                                                        Note: {{ $item->special_instructions }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div class="font-mono font-bold text-white">
                                                ₹{{ number_format((float) $item->subtotal, 2) }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 font-mono">
                                                @ ₹{{ number_format((float) $item->unit_price, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Right Column (5 cols): Bill Adjustment Form & Final Summary -->
        <div class="lg:col-span-5 space-y-6">

            <form action="{{ route('counter.bills.store', $visit) }}" method="POST" id="billForm">
                @csrf

                <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl p-6 space-y-5">
                    <h2 class="font-display font-bold text-lg text-[#FDFBF7] border-b border-[#2B2B2B] pb-3">
                        Bill Calculation &amp; Adjustments
                    </h2>

                    <!-- Base Subtotal Display -->
                    <div class="flex items-center justify-between py-2 border-b border-[#262626]">
                        <span class="text-xs text-gray-400">Total Food Subtotal</span>
                        <span class="font-mono font-bold text-base text-white">
                            ₹{{ number_format($preview['subtotal'], 2) }}
                        </span>
                    </div>

                    <!-- Discount Selector -->
                    <div class="space-y-3 bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl p-4">
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">
                            Apply Discount
                        </label>

                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="discount_type" value="none" class="sr-only peer" {{ old('discount_type', $options['discount_type']) === 'none' ? 'checked' : '' }} onchange="calculateBill()">
                                <div class="py-2 text-center rounded-lg border border-[#3A3A3A] bg-[#242424] text-xs font-medium text-gray-300 peer-checked:border-[#D4AF37] peer-checked:bg-[#D4AF37]/15 peer-checked:text-[#D4AF37] transition-all">
                                    None (0%)
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="discount_type" value="percentage" class="sr-only peer" {{ old('discount_type', $options['discount_type']) === 'percentage' ? 'checked' : '' }} onchange="calculateBill()">
                                <div class="py-2 text-center rounded-lg border border-[#3A3A3A] bg-[#242424] text-xs font-medium text-gray-300 peer-checked:border-[#D4AF37] peer-checked:bg-[#D4AF37]/15 peer-checked:text-[#D4AF37] transition-all">
                                    Percentage %
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="discount_type" value="fixed" class="sr-only peer" {{ old('discount_type', $options['discount_type']) === 'fixed' ? 'checked' : '' }} onchange="calculateBill()">
                                <div class="py-2 text-center rounded-lg border border-[#3A3A3A] bg-[#242424] text-xs font-medium text-gray-300 peer-checked:border-[#D4AF37] peer-checked:bg-[#D4AF37]/15 peer-checked:text-[#D4AF37] transition-all">
                                    Fixed (₹)
                                </div>
                            </label>
                        </div>

                        <!-- Discount Value Input -->
                        <div id="discountValueContainer" class="space-y-2 {{ old('discount_type', $options['discount_type']) === 'none' ? 'hidden' : '' }}">
                            <div class="flex items-center space-x-2">
                                <input type="number" step="0.01" min="0" name="discount_value" id="discountValue" value="{{ old('discount_value', $options['discount_value'] ?: '') }}" placeholder="Enter discount value" class="w-full bg-[#181818] border border-[#3D3D3D] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#D4AF37]" oninput="calculateBill()">
                            </div>

                            <input type="text" name="discount_reason" id="discountReason" value="{{ old('discount_reason', $options['discount_reason'] ?? '') }}" placeholder="Mandatory discount reason (e.g., Managerial Goodwill)" class="w-full bg-[#181818] border border-[#3D3D3D] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#D4AF37]">
                        </div>
                    </div>

                    <!-- GST Policy Information -->
                    <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl p-4 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-300 uppercase tracking-wider block">
                                Goods &amp; Services Tax (GST)
                            </span>
                            <span class="text-[11px] text-gray-400 mt-0.5 block">
                                Non-GST Commercial Billing &bull; CGST (₹0.00) + SGST (₹0.00)
                            </span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-[#282828] text-gray-300 border border-[#3A3A3A]">
                            Not Applied (₹0.00)
                        </span>
                    </div>

                    <!-- Optional Service Charge -->
                    <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl p-4">
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                            Service Charge (Optional ₹)
                        </label>
                        <input type="number" step="0.01" min="0" name="service_charge" id="serviceCharge" value="{{ old('service_charge', $options['service_charge'] ?: '') }}" placeholder="0.00" class="w-full bg-[#181818] border border-[#3D3D3D] rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-[#D4AF37]" oninput="calculateBill()">
                    </div>

                    <!-- Notes -->
                    <div>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Bill notes (optional)" class="w-full bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-3 py-2 text-xs text-gray-300 focus:outline-none focus:border-[#D4AF37]">
                    </div>

                    <!-- Dynamic Final Summary Box -->
                    <div class="bg-gradient-to-br from-[#242424] to-[#181818] border border-[#3D3D3D] rounded-xl p-5 space-y-3">
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>Food Subtotal</span>
                            <span class="font-mono text-white" id="summarySubtotal">₹{{ number_format($preview['subtotal'], 2) }}</span>
                        </div>

                        <div class="flex justify-between text-xs text-gray-400" id="summaryDiscountRow">
                            <span>Discount</span>
                            <span class="font-mono text-emerald-400" id="summaryDiscount">-₹0.00</span>
                        </div>

                        <div class="flex justify-between text-xs text-gray-400">
                            <span>Net Food Amount</span>
                            <span class="font-mono text-white" id="summaryNet">₹{{ number_format($preview['subtotal'], 2) }}</span>
                        </div>

                        <div class="flex justify-between text-xs text-gray-400">
                            <span>GST</span>
                            <span class="font-mono text-gray-400">Not Applied (₹0.00)</span>
                        </div>

                        <div class="flex justify-between text-xs text-gray-400" id="summaryServiceRow">
                            <span>Service Charge</span>
                            <span class="font-mono text-white" id="summaryService">₹0.00</span>
                        </div>

                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Round Off Adjustment</span>
                            <span class="font-mono" id="summaryRoundOff">₹0.00</span>
                        </div>

                        <div class="pt-3 border-t border-[#3D3D3D] flex items-center justify-between">
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Total Payable</div>
                                <div class="text-[10px] text-gray-500">Includes all dishes (No GST)</div>
                            </div>
                            <div class="font-mono font-bold text-2xl text-[#D4AF37]" id="summaryFinal">
                                ₹{{ number_format($preview['final_amount'], 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-bold text-sm bg-gradient-to-r from-[#D4AF37] via-[#E6CA65] to-[#B38F26] text-gray-950 shadow-xl hover:brightness-110 active:scale-[0.99] transition-all flex items-center justify-center space-x-2">
                        <span>Generate &amp; Issue Final Bill</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>

        </div>

    </div>

</div>

<script>
const baseSubtotal = {{ (float) $preview['subtotal'] }};

function calculateBill() {
    const discountType = document.querySelector('input[name="discount_type"]:checked')?.value || 'none';
    const discountValueInput = document.getElementById('discountValue');
    const discountContainer = document.getElementById('discountValueContainer');

    if (discountType === 'none') {
        discountContainer.classList.add('hidden');
    } else {
        discountContainer.classList.remove('hidden');
    }

    let discountVal = parseFloat(discountValueInput?.value) || 0;
    let discountAmount = 0;

    if (discountType === 'percentage') {
        discountVal = Math.min(100, Math.max(0, discountVal));
        discountAmount = Math.round(((baseSubtotal * discountVal) / 100) * 100) / 100;
    } else if (discountType === 'fixed') {
        discountAmount = Math.min(baseSubtotal, Math.max(0, discountVal));
    }

    const netAmount = Math.max(0, Math.round((baseSubtotal - discountAmount) * 100) / 100);
    const serviceCharge = Math.max(0, parseFloat(document.getElementById('serviceCharge')?.value) || 0);

    const rawFinal = netAmount + serviceCharge;
    const finalAmount = Math.round(rawFinal);
    const roundOff = Math.round((finalAmount - rawFinal) * 100) / 100;

    document.getElementById('summaryDiscount').textContent = '-₹' + discountAmount.toFixed(2);
    document.getElementById('summaryNet').textContent = '₹' + netAmount.toFixed(2);
    document.getElementById('summaryService').textContent = '₹' + serviceCharge.toFixed(2);
    document.getElementById('summaryRoundOff').textContent = (roundOff >= 0 ? '+₹' : '-₹') + Math.abs(roundOff).toFixed(2);
    document.getElementById('summaryFinal').textContent = '₹' + finalAmount.toFixed(2);
}

document.addEventListener('DOMContentLoaded', calculateBill);
</script>
@endsection
