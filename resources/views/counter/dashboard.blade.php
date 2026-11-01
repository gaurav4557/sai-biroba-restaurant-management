@extends('layouts.app', ['title' => 'Counter POS & Billing Console'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full space-y-6">

    <!-- Counter Header Banner with KPI Cards -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-sky-500/20 text-sky-400 border border-sky-500/30">
                    POS Billing Command Center
                </span>
                <span class="text-xs text-gray-500">&bull; {{ now()->format('D, d M Y') }}</span>
            </div>
            <h1 class="font-display font-bold text-2xl text-[#FDFBF7] mt-2">
                Counter Staff &bull; {{ $user->name }}
            </h1>
            <p class="text-xs text-gray-400 mt-1">
                Multi-Round Consolidation &bull; Real-time Invoicing &bull; Split Payments &bull; Thermal Receipts
            </p>
            <div class="mt-3 flex items-center gap-2">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-[#D4AF37]/15 hover:bg-[#D4AF37]/25 text-[#D4AF37] border border-[#D4AF37]/40 text-xs font-semibold transition shadow-sm">
                    <svg class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Financial Reports &amp; Analytics &rarr;</span>
                </a>
            </div>
        </div>

        <!-- 4 KPI Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-4 py-3 text-center">
                <div class="text-2xl font-bold font-display text-amber-400">{{ $occupiedTablesCount }}</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider mt-0.5">Occupied</div>
            </div>

            <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-4 py-3 text-center">
                <div class="text-2xl font-bold font-display text-sky-400">{{ $billedTablesCount }}</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider mt-0.5">Bill Issued</div>
            </div>

            <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-4 py-3 text-center">
                <div class="text-2xl font-bold font-display text-purple-400">{{ $pendingBillsCount }}</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider mt-0.5">Unsettled</div>
            </div>

            <div class="bg-[#1E1E1E] border border-[#2D2D2D] rounded-xl px-4 py-3 text-center">
                <div class="text-2xl font-bold font-display text-emerald-400">₹{{ number_format($todayRevenue, 2) }}</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider mt-0.5">Today Paid</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm flex items-center space-x-3 shadow-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-sky-950/60 border border-sky-500/40 text-sky-300 px-4 py-3 rounded-xl text-sm flex items-center space-x-3 shadow-lg">
            <svg class="w-5 h-5 flex-shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('info') }}</span>
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

    <!-- ACTIVE DINING SESSIONS & BILLING QUEUE -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-[#2B2B2B] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-display font-bold text-lg text-[#FDFBF7] flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Active Dining Sessions &bull; Ready for Billing</span>
                </h2>
                <p class="text-xs text-gray-400 mt-1">
                    Occupied or billed tables awaiting consolidated invoice generation or final payment settlement.
                </p>
            </div>
            <div class="text-xs text-gray-400">
                Total Active: <span class="text-white font-bold">{{ $activeVisits->count() }}</span>
            </div>
        </div>

        @if($activeVisits->isEmpty())
            <div class="p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-gray-800/60 border border-gray-700 flex items-center justify-center text-gray-400 mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-200">All Tables Settled</h3>
                <p class="text-xs text-gray-400 mt-1">No active dining sessions or pending bills on the floor.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#1E1E1E] border-b border-[#2D2D2D] text-[11px] uppercase tracking-wider text-gray-400 font-semibold">
                            <th class="py-3.5 px-6">Table &bull; Room</th>
                            <th class="py-3.5 px-4">Visit Code</th>
                            <th class="py-3.5 px-4">Guest Info</th>
                            <th class="py-3.5 px-4">Assigned Waiter</th>
                            <th class="py-3.5 px-4 text-center">KOT Orders</th>
                            <th class="py-3.5 px-4 text-right">Order Subtotal</th>
                            <th class="py-3.5 px-4 text-center">Billing Status</th>
                            <th class="py-3.5 px-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262626] text-xs">
                        @foreach($activeVisits as $visit)
                            <tr class="hover:bg-[#1E1E1E]/60 transition-colors">
                                <!-- Table & Room -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2D2D2D] to-[#1E1E1E] border border-[#3D3D3D] flex items-center justify-center font-display font-bold text-base text-[#D4AF37] shadow-inner">
                                            {{ $visit->table->table_number }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-[#FDFBF7] text-sm">
                                                Table {{ $visit->table->table_number }}
                                            </div>
                                            <div class="text-[11px] text-gray-400">
                                                {{ $visit->table->floor_section }} &bull; {{ $visit->table->seating_capacity }} Seats
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Visit Code -->
                                <td class="py-4 px-4 font-mono text-gray-300">
                                    {{ $visit->visit_code }}
                                    <div class="text-[10px] text-gray-400 font-sans">
                                        Opened {{ $visit->opened_at ? $visit->opened_at->diffForHumans() : 'Recently' }}
                                    </div>
                                </td>

                                <!-- Guest Info -->
                                <td class="py-4 px-4">
                                    <div class="text-white font-medium">
                                        {{ $visit->guest_name ?: 'Walk-in Guests' }}
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $visit->guest_count }} {{ Str::plural('guest', $visit->guest_count) }}
                                    </div>
                                </td>

                                <!-- Assigned Waiter -->
                                <td class="py-4 px-4 text-gray-300">
                                    <div class="font-medium text-white">{{ $visit->waiter->name ?? 'Unassigned' }}</div>
                                    <div class="text-[10px] text-gray-400">Staff #{{ $visit->waiter->id ?? '-' }}</div>
                                </td>

                                <!-- KOT Orders -->
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium bg-[#282828] text-gray-200 border border-[#3A3A3A]">
                                        {{ $visit->orders_count }} {{ Str::plural('Round', $visit->orders_count) }} ({{ $visit->items_count }} items)
                                    </span>
                                </td>

                                <!-- Order Subtotal -->
                                <td class="py-4 px-4 text-right">
                                    <div class="font-mono font-bold text-sm text-[#FDFBF7]">
                                        ₹{{ number_format($visit->computed_subtotal, 2) }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">Snapshot food sum</div>
                                </td>

                                <!-- Billing Status -->
                                <td class="py-4 px-4 text-center">
                                    @if($visit->bill)
                                        @if($visit->bill->isPaid())
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                Paid &bull; Settled
                                            </span>
                                        @elseif($visit->bill->isPartiallyPaid())
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                                Partially Paid
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-sky-500/15 text-sky-400 border border-sky-500/30">
                                                Bill Issued
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-yellow-500/15 text-yellow-400 border border-yellow-500/30">
                                            Dining Active
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 px-6 text-right">
                                    @if($visit->bill && !$visit->bill->isVoided())
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('counter.bills.show', $visit->bill) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-sky-600 hover:bg-sky-500 text-white shadow transition-all">
                                                <span>View &amp; Settle</span>
                                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('counter.bills.receipt', $visit->bill) }}" target="_blank" class="p-1.5 rounded-lg bg-[#282828] hover:bg-[#333] text-gray-300 hover:text-white border border-[#3A3A3A] transition-colors" title="Print Receipt">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ route('counter.bills.create', $visit) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-r from-[#D4AF37] to-[#B38F26] hover:brightness-110 text-gray-950 shadow-md transition-all">
                                            <span>Generate Bill</span>
                                            <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- RECENT INVOICES & AUDIT LOG -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 border-b border-[#2B2B2B] flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-lg text-[#FDFBF7]">
                    Recent Invoices &bull; Historical Register
                </h2>
                <p class="text-xs text-gray-400 mt-1">
                    Complete immutable billing records, payment tracking, and audit statuses.
                </p>
            </div>
        </div>

        @if($recentBills->isEmpty())
            <div class="p-8 text-center text-xs text-gray-500">
                No invoices have been generated yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#1E1E1E] border-b border-[#2D2D2D] text-[11px] uppercase tracking-wider text-gray-400 font-semibold">
                            <th class="py-3 px-6">Bill Number</th>
                            <th class="py-3 px-4">Table</th>
                            <th class="py-3 px-4">Generated By</th>
                            <th class="py-3 px-4">Issued At</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                            <th class="py-3 px-4 text-right">Final Amount</th>
                            <th class="py-3 px-4 text-right">Paid Amount</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#262626] text-xs">
                        @foreach($recentBills as $bill)
                            <tr class="hover:bg-[#1E1E1E]/60 transition-colors">
                                <td class="py-3 px-6 font-mono font-bold text-[#FDFBF7]">
                                    {{ $bill->bill_number }}
                                </td>
                                <td class="py-3 px-4 text-white">
                                    Table {{ $bill->visit->table->table_number ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-gray-300">
                                    {{ $bill->generatedBy->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-4 text-gray-400">
                                    {{ $bill->issued_at ? $bill->issued_at->format('d M, h:i A') : $bill->created_at->format('d M, h:i A') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-gray-300">
                                    ₹{{ number_format((float) $bill->subtotal_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-white">
                                    ₹{{ number_format((float) $bill->final_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-emerald-400">
                                    ₹{{ number_format((float) $bill->total_paid_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($bill->status === 'paid')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            Paid
                                        </span>
                                    @elseif($bill->status === 'partially_paid')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                            Partially Paid
                                        </span>
                                    @elseif($bill->status === 'voided')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-500/15 text-red-400 border border-red-500/30">
                                            Voided
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-500/15 text-sky-400 border border-sky-500/30">
                                            Issued
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('counter.bills.show', $bill) }}" class="text-sky-400 hover:text-sky-300 font-medium text-xs">
                                            View
                                        </a>
                                        @if(!$bill->isVoided())
                                            <span class="text-gray-600">&bull;</span>
                                            <a href="{{ route('counter.bills.receipt', $bill) }}" target="_blank" class="text-gray-400 hover:text-white font-medium text-xs">
                                                Receipt
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
