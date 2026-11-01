@extends('layouts.app', ['title' => 'Financial Reports & Analytics'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full space-y-6">

    <!-- Header & Quick View Switcher -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30">
                    Audit &amp; Financial Analytics
                </span>
                <span class="text-xs text-gray-500">&bull; Generated {{ now()->format('d M Y, h:i A') }}</span>
            </div>
            <h1 class="font-display font-bold text-2xl text-[#FDFBF7] mt-2 flex items-center gap-2">
                Financial Reports Dashboard
            </h1>
            <p class="text-xs text-gray-400 mt-1">
                Accrual vs. Cashflow Analytics &bull; Zero GST Commercial Billing &bull; Non-Destructive Ledger
            </p>
        </div>

        <!-- Quick Switcher Tabs & Action Links -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('reports.index') }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold transition border {{ $reportType === 'custom' || !request()->has('report_type') ? 'bg-[#D4AF37] text-[#121212] border-[#D4AF37] shadow-md' : 'bg-[#222222] text-gray-300 border-[#333333] hover:text-[#D4AF37]' }}">
                Summary Dashboard
            </a>
            <a href="{{ route('reports.daily', ['date' => $date->format('Y-m-d')]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold transition border {{ request()->routeIs('reports.daily') ? 'bg-[#D4AF37] text-[#121212] border-[#D4AF37]' : 'bg-[#222222] text-gray-300 border-[#333333] hover:text-[#D4AF37]' }}">
                Daily Ledger
            </a>
            <a href="{{ route('reports.monthly', ['year' => $year, 'month' => $month]) }}" 
               class="px-3.5 py-2 rounded-xl text-xs font-semibold transition border {{ request()->routeIs('reports.monthly') ? 'bg-[#D4AF37] text-[#121212] border-[#D4AF37]' : 'bg-[#222222] text-gray-300 border-[#333333] hover:text-[#D4AF37]' }}">
                Monthly Ledger
            </a>
            <div class="h-6 w-px bg-gray-700 mx-1 hidden sm:block"></div>
            <a href="{{ route('reports.print', array_merge(request()->all(), ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')])) }}" 
               target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-[#222222] hover:bg-[#2A2A2A] text-sky-400 border border-sky-500/30 text-xs font-semibold transition shadow-sm"
               title="Print or Save PDF Statement">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Statement</span>
            </a>
            <a href="{{ route('reports.export.csv', array_merge(request()->all(), ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')])) }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-[#222222] hover:bg-[#2A2A2A] text-emerald-400 border border-emerald-500/30 text-xs font-semibold transition shadow-sm"
               title="Download CSV Spreadsheet">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Date Range & Preset Filter Bar -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 shadow-lg">
        <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                
                <!-- Quick Date Presets -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wider mr-1">Presets:</span>
                    <a href="{{ route('reports.index', ['report_type' => 'custom', 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]) }}" 
                       class="px-2.5 py-1 rounded-lg text-xs font-medium bg-[#222222] hover:bg-[#2D2D2D] text-gray-300 border border-[#333333] transition">
                        Today
                    </a>
                    <a href="{{ route('reports.index', ['report_type' => 'custom', 'start_date' => now()->subDay()->toDateString(), 'end_date' => now()->subDay()->toDateString()]) }}" 
                       class="px-2.5 py-1 rounded-lg text-xs font-medium bg-[#222222] hover:bg-[#2D2D2D] text-gray-300 border border-[#333333] transition">
                        Yesterday
                    </a>
                    <a href="{{ route('reports.index', ['report_type' => 'custom', 'start_date' => now()->startOfWeek()->toDateString(), 'end_date' => now()->endOfWeek()->toDateString()]) }}" 
                       class="px-2.5 py-1 rounded-lg text-xs font-medium bg-[#222222] hover:bg-[#2D2D2D] text-gray-300 border border-[#333333] transition">
                        This Week
                    </a>
                    <a href="{{ route('reports.index', ['report_type' => 'custom', 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]) }}" 
                       class="px-2.5 py-1 rounded-lg text-xs font-medium bg-[#222222] hover:bg-[#2D2D2D] text-gray-300 border border-[#333333] transition">
                        This Month
                    </a>
                    <a href="{{ route('reports.index', ['report_type' => 'custom', 'start_date' => now()->subMonth()->startOfMonth()->toDateString(), 'end_date' => now()->subMonth()->endOfMonth()->toDateString()]) }}" 
                       class="px-2.5 py-1 rounded-lg text-xs font-medium bg-[#222222] hover:bg-[#2D2D2D] text-gray-300 border border-[#333333] transition">
                        Last Month
                    </a>
                </div>

                <!-- Custom Date Inputs -->
                <div class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="report_type" value="custom">

                    <div class="flex items-center space-x-2">
                        <label for="start_date" class="text-xs text-gray-400 font-medium">From:</label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ $startDate->format('Y-m-d') }}"
                               class="bg-[#121212] border border-[#333333] rounded-lg px-3 py-1.5 text-xs text-gray-200 focus:outline-none focus:border-[#D4AF37] transition">
                    </div>

                    <div class="flex items-center space-x-2">
                        <label for="end_date" class="text-xs text-gray-400 font-medium">To:</label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               value="{{ $endDate->format('Y-m-d') }}"
                               class="bg-[#121212] border border-[#333333] rounded-lg px-3 py-1.5 text-xs text-gray-200 focus:outline-none focus:border-[#D4AF37] transition">
                    </div>

                    <button type="submit" 
                            class="px-4 py-1.5 bg-[#D4AF37] hover:bg-[#C5A059] text-[#121212] font-semibold text-xs rounded-lg shadow transition">
                        Apply Filter
                    </button>

                    <a href="{{ route('reports.index') }}" 
                       class="px-3 py-1.5 bg-[#222222] hover:bg-[#2C2C2C] text-gray-400 hover:text-gray-200 text-xs rounded-lg border border-[#333333] transition"
                       title="Reset to This Month">
                        Reset
                    </a>
                </div>
            </div>

            <!-- Active Date Scope Indicator -->
            <div class="pt-3 border-t border-[#262626] flex items-center justify-between text-xs text-gray-400">
                <div>
                    Active Scope: <strong class="text-[#D4AF37]">{{ $startDate->format('d M Y') }}</strong> to <strong class="text-[#D4AF37]">{{ $endDate->format('d M Y') }}</strong>
                    <span class="text-gray-500 ml-1">({{ $startDate->diffInDays($endDate) + 1 }} {{ Str::plural('day', $startDate->diffInDays($endDate) + 1) }})</span>
                </div>
                <div class="text-[11px] text-gray-500 font-mono">
                    Zero GST Billing Active &bull; Non-GST Commercial Enterprise
                </div>
            </div>
        </form>
    </div>

    <!-- 6 Executive Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        
        <!-- 1. Gross Collections -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between text-gray-400 text-xs">
                <span class="uppercase tracking-wider font-semibold text-emerald-400">Gross Cashflow Inflow</span>
                <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Received</span>
            </div>
            <div class="mt-3 text-3xl font-bold font-display text-emerald-400 tracking-tight">
                ₹{{ number_format($report['gross_collections'], 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-400 flex items-center justify-between">
                <span>Total successful payments received</span>
                <span class="text-gray-500 font-mono">Cashflow</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-400"></div>
        </div>

        <!-- 2. Successful Refunds -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-rose-500/40 transition">
            <div class="flex items-center justify-between text-gray-400 text-xs">
                <span class="uppercase tracking-wider font-semibold text-rose-400">Disbursed Refunds</span>
                <span class="px-2 py-0.5 rounded text-[10px] bg-rose-500/10 text-rose-400 border border-rose-500/20">Outflow</span>
            </div>
            <div class="mt-3 text-3xl font-bold font-display text-rose-400 tracking-tight">
                ₹{{ number_format($report['successful_refunds'], 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-400 flex items-center justify-between">
                <span>Successful returned transactions</span>
                <span class="text-gray-500 font-mono">Deduction</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-400"></div>
        </div>

        <!-- 3. Net Collections (Actual Money Kept) -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-[#D4AF37]/60 transition bg-gradient-to-b from-[#1C1A14] to-[#181818]">
            <div class="flex items-center justify-between text-gray-400 text-xs">
                <span class="uppercase tracking-wider font-semibold text-[#D4AF37]">Net Cashflow Kept</span>
                <span class="px-2 py-0.5 rounded text-[10px] bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/40 font-semibold">Net Cash</span>
            </div>
            <div class="mt-3 text-3xl font-bold font-display text-[#D4AF37] tracking-tight">
                ₹{{ number_format($report['net_collections'], 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-400 flex items-center justify-between">
                <span>Gross Inflow &minus; Refunds</span>
                <span class="text-[#D4AF37] font-semibold text-[11px]">Primary Bank Ledger</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#D4AF37] to-[#F3E5AB]"></div>
        </div>

        <!-- 4. Gross Billed Sales (Accrual) -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-sky-500/40 transition">
            <div class="flex items-center justify-between text-gray-400 text-xs">
                <span class="uppercase tracking-wider font-semibold text-sky-400">Gross Billed Sales</span>
                <span class="px-2 py-0.5 rounded text-[10px] bg-sky-500/10 text-sky-400 border border-sky-500/20">Accrual</span>
            </div>
            <div class="mt-3 text-3xl font-bold font-display text-sky-400 tracking-tight">
                ₹{{ number_format($report['gross_billed_sales'], 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-400 flex items-center justify-between">
                <span>Total issued &amp; finalized bills</span>
                <span class="text-gray-500 font-mono">{{ $report['order_stats']['total_bills_issued'] }} Invoices</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-sky-400"></div>
        </div>

        <!-- 5. Credit Notes -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="flex items-center justify-between text-gray-400 text-xs">
                <span class="uppercase tracking-wider font-semibold text-amber-400">Credit Notes Issued</span>
                <span class="px-2 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/20">Adjustments</span>
            </div>
            <div class="mt-3 text-3xl font-bold font-display text-amber-400 tracking-tight">
                ₹{{ number_format($report['total_credit_notes'], 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-400 flex items-center justify-between">
                <span>Post-billing line adjustments</span>
                <span class="text-gray-500 font-mono">Invoice Reductions</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-400"></div>
        </div>

        <!-- 6. Net Billed Sales -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 shadow-lg relative overflow-hidden group hover:border-indigo-500/40 transition">
            <div class="flex items-center justify-between text-gray-400 text-xs">
                <span class="uppercase tracking-wider font-semibold text-indigo-400">Net Billed Sales</span>
                <span class="px-2 py-0.5 rounded text-[10px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Accounting</span>
            </div>
            <div class="mt-3 text-3xl font-bold font-display text-indigo-400 tracking-tight">
                ₹{{ number_format($report['net_billed_sales'], 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-400 flex items-center justify-between">
                <span>Gross Billed &minus; Credit Notes</span>
                <span class="text-indigo-400 font-semibold text-[11px]">Accrual Net Revenue</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-400"></div>
        </div>

    </div>

    <!-- Supplementary Financial Indicators Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-[#181818] border border-[#2B2B2B] rounded-2xl p-4 shadow-lg text-center">
        <div class="border-r border-[#262626] last:border-r-0">
            <div class="text-xs text-gray-400 uppercase tracking-wider font-medium">Total Discounts</div>
            <div class="text-lg font-bold font-display text-amber-300 mt-1">₹{{ number_format($report['total_discounts'], 2) }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Approved promotional concessions</div>
        </div>

        <div class="border-r border-[#262626] last:border-r-0">
            <div class="text-xs text-gray-400 uppercase tracking-wider font-medium">Commercial Tax (GST)</div>
            <div class="text-lg font-bold font-display text-gray-400 mt-1">₹0.00</div>
            <div class="text-[10px] text-emerald-400 mt-0.5">Zero GST Configuration Active</div>
        </div>

        <div class="border-r border-[#262626] last:border-r-0">
            <div class="text-xs text-gray-400 uppercase tracking-wider font-medium">Average Order Value</div>
            <div class="text-lg font-bold font-display text-sky-400 mt-1">₹{{ number_format($report['order_stats']['average_order_value'], 2) }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Average ticket per settled bill</div>
        </div>

        <div>
            <div class="text-xs text-gray-400 uppercase tracking-wider font-medium">Unsettled Balance</div>
            <div class="text-lg font-bold font-display text-rose-400 mt-1">₹{{ number_format($report['order_stats']['outstanding_balance'], 2) }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Pending collection on issued bills</div>
        </div>
    </div>

    <!-- Interactive Visual Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Daily Trend Bar/Line Chart (2 Cols) -->
        <div class="lg:col-span-2 bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-[#262626] pb-4 mb-4">
                <div>
                    <h2 class="font-display font-bold text-base text-[#FDFBF7]">Collections &amp; Billed Sales Trend</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Day-by-day comparison of gross cashflow vs accrual sales</p>
                </div>
                <div class="flex items-center space-x-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-emerald-400">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Collections
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sky-400">
                        <span class="w-3 h-3 rounded-full bg-sky-500"></span> Billed Sales
                    </span>
                </div>
            </div>

            <div class="relative w-full h-72">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Payment Method Distribution Doughnut Chart (1 Col) -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl flex flex-col justify-between">
            <div class="border-b border-[#262626] pb-4 mb-4">
                <h2 class="font-display font-bold text-base text-[#FDFBF7]">Payment Methods</h2>
                <p class="text-xs text-gray-400 mt-0.5">Breakdown of collections across modes</p>
            </div>

            <div class="relative w-full h-56 flex items-center justify-center">
                <canvas id="paymentPieChart"></canvas>
            </div>

            <div class="mt-4 pt-3 border-t border-[#262626] text-center text-xs text-gray-400">
                Gross Inflow: <strong class="text-emerald-400">₹{{ number_format($report['gross_collections'], 2) }}</strong>
            </div>
        </div>

    </div>

    <!-- Two-Column Breakdown: Payment Methods & Operational Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Payment Method Breakdown Table -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#262626] pb-3">
                <h2 class="font-display font-bold text-base text-[#FDFBF7]">Payment Method Breakdown</h2>
                <span class="text-xs text-gray-500">Successful payments only</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-gray-400 uppercase tracking-wider border-b border-[#262626]">
                        <tr>
                            <th class="py-2.5 font-semibold">Method</th>
                            <th class="py-2.5 text-center font-semibold">Transactions</th>
                            <th class="py-2.5 text-right font-semibold">Total Collected</th>
                            <th class="py-2.5 text-right font-semibold">Share (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#242424] text-gray-300">
                        @php
                            $totalCollected = $report['gross_collections'] > 0 ? $report['gross_collections'] : 1;
                        @endphp
                        @forelse($report['payment_methods'] as $pm)
                            @php
                                $percentage = ($pm['total_amount'] / $totalCollected) * 100;
                            @endphp
                            <tr class="hover:bg-[#202020] transition">
                                <td class="py-3 font-semibold text-[#FDFBF7] flex items-center gap-2">
                                    @if($pm['method'] === 'cash')
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    @elseif($pm['method'] === 'upi')
                                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                                    @elseif($pm['method'] === 'credit_card')
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                    @elseif($pm['method'] === 'debit_card')
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    @else
                                        <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
                                    @endif
                                    {{ $pm['label'] }}
                                </td>
                                <td class="py-3 text-center font-mono">{{ $pm['transaction_count'] }}</td>
                                <td class="py-3 text-right font-mono font-bold text-[#FDFBF7]">
                                    ₹{{ number_format($pm['total_amount'], 2) }}
                                </td>
                                <td class="py-3 text-right font-mono text-gray-400">
                                    {{ number_format($percentage, 1) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-500 italic">No payments recorded in this period</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-[#333333] font-semibold text-xs">
                        <tr class="text-[#D4AF37]">
                            <td class="pt-3">Total Inflow</td>
                            <td class="pt-3 text-center font-mono">{{ array_sum(array_column($report['payment_methods'], 'transaction_count')) }}</td>
                            <td class="pt-3 text-right font-mono font-bold">₹{{ number_format($report['gross_collections'], 2) }}</td>
                            <td class="pt-3 text-right font-mono">100.0%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Operational & Bill Audit Statistics -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#262626] pb-3">
                <h2 class="font-display font-bold text-base text-[#FDFBF7]">Operational &amp; Invoicing Audit</h2>
                <span class="text-xs text-gray-500">Dining Session Metrics</span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-[#202020] border border-[#2D2D2D] rounded-xl p-3">
                    <div class="text-gray-400">Total Dining Visits</div>
                    <div class="text-xl font-bold font-display text-[#FDFBF7] mt-1">{{ $report['order_stats']['total_visits'] }}</div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Seated table sessions</div>
                </div>

                <div class="bg-[#202020] border border-[#2D2D2D] rounded-xl p-3">
                    <div class="text-gray-400">Total Guests Served</div>
                    <div class="text-xl font-bold font-display text-sky-400 mt-1">{{ $report['order_stats']['total_guests'] }}</div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Covers recorded at table</div>
                </div>

                <div class="bg-[#202020] border border-[#2D2D2D] rounded-xl p-3">
                    <div class="text-gray-400">Invoices Issued</div>
                    <div class="text-xl font-bold font-display text-[#D4AF37] mt-1">{{ $report['order_stats']['total_bills_issued'] }}</div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Consolidated visit bills</div>
                </div>

                <div class="bg-[#202020] border border-[#2D2D2D] rounded-xl p-3">
                    <div class="text-gray-400">Fully Settled Bills</div>
                    <div class="text-xl font-bold font-display text-emerald-400 mt-1">{{ $report['order_stats']['paid_bills_count'] }}</div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Paid in full and released</div>
                </div>

                <div class="bg-[#202020] border border-[#2D2D2D] rounded-xl p-3">
                    <div class="text-gray-400">Partially Paid Bills</div>
                    <div class="text-xl font-bold font-display text-purple-400 mt-1">{{ $report['order_stats']['partially_paid_bills_count'] }}</div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Split payments in progress</div>
                </div>

                <div class="bg-[#202020] border border-[#2D2D2D] rounded-xl p-3">
                    <div class="text-gray-400">Voided Invoices</div>
                    <div class="text-xl font-bold font-display text-rose-400 mt-1">{{ $report['order_stats']['voided_bills_count'] }}</div>
                    <div class="text-[10px] text-gray-500 mt-0.5">Excluded from revenue</div>
                </div>
            </div>

            <!-- Average Revenue Metric Banner -->
            <div class="bg-[#222222] border border-[#333333] rounded-xl p-3.5 flex items-center justify-between text-xs">
                <div>
                    <div class="font-semibold text-gray-300">Average Spend per Guest (SPH)</div>
                    <div class="text-[11px] text-gray-500">Gross Sales / Total Guest Covers</div>
                </div>
                <div class="text-base font-bold font-mono text-[#D4AF37]">
                    @if($report['order_stats']['total_guests'] > 0)
                        ₹{{ number_format($report['gross_billed_sales'] / $report['order_stats']['total_guests'], 2) }}
                    @else
                        ₹0.00
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Top 10 Best-Selling Menu Items -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-[#262626] pb-3">
            <div>
                <h2 class="font-display font-bold text-base text-[#FDFBF7]">Top 10 Best-Selling Dishes</h2>
                <p class="text-xs text-gray-400 mt-0.5">Ranked by total quantity sold from settled and issued invoices</p>
            </div>
            <span class="text-xs text-[#D4AF37] font-semibold">Kitchen &amp; Menu Performance</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="text-gray-400 uppercase tracking-wider border-b border-[#262626]">
                    <tr>
                        <th class="py-2.5 font-semibold w-12 text-center">Rank</th>
                        <th class="py-2.5 font-semibold">Dish Name</th>
                        <th class="py-2.5 font-semibold text-center">Dietary</th>
                        <th class="py-2.5 font-semibold text-center">Quantity Sold</th>
                        <th class="py-2.5 font-semibold text-right">Avg Price</th>
                        <th class="py-2.5 font-semibold text-right">Total Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#242424] text-gray-300">
                    @forelse($report['best_sellers'] as $index => $item)
                        <tr class="hover:bg-[#202020] transition">
                            <td class="py-3 text-center font-mono font-bold text-gray-500">
                                #{{ $index + 1 }}
                            </td>
                            <td class="py-3 font-semibold text-[#FDFBF7]">
                                {{ $item->item_name }}
                            </td>
                            <td class="py-3 text-center">
                                @if($item->food_type === 'veg')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        VEG
                                    </span>
                                @elseif($item->food_type === 'non-veg')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        NON-VEG
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-700 text-gray-300">
                                        {{ strtoupper($item->food_type ?? 'ITEM') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 text-center font-mono font-bold text-sky-400">
                                {{ $item->quantity_sold }}
                            </td>
                            <td class="py-3 text-right font-mono text-gray-400">
                                ₹{{ number_format($item->average_price, 2) }}
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-[#D4AF37]">
                                ₹{{ number_format($item->total_sales, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500 italic">
                                No sales data recorded in this selected range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Script Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Daily Trend Data from Backend
        const dailyTrend = @json($report['daily_trend']);
        const trendLabels = dailyTrend.map(item => item.date.substring(5)); // 'MM-DD'
        const collectionsData = dailyTrend.map(item => item.collections);
        const billedSalesData = dailyTrend.map(item => item.billed_sales);

        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: 'Collections (₹)',
                            data: collectionsData,
                            backgroundColor: 'rgba(16, 185, 129, 0.75)',
                            borderColor: '#10B981',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Billed Sales (₹)',
                            data: billedSalesData,
                            backgroundColor: 'rgba(56, 189, 248, 0.65)',
                            borderColor: '#38BDF8',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#9CA3AF', font: { size: 10 } }
                        },
                        y: {
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { 
                                color: '#9CA3AF', 
                                font: { size: 10 },
                                callback: function(value) { return '₹' + value; }
                            }
                        }
                    }
                }
            });
        }

        // Payment Method Doughnut Chart
        const paymentData = @json($report['payment_methods']);
        const pieLabels = paymentData.map(p => p.label);
        const pieValues = paymentData.map(p => p.total_amount);
        
        const pieColors = {
            'Cash': '#10B981',
            'UPI / QR Code': '#38BDF8',
            'Credit Card': '#A855F7',
            'Debit Card': '#F59E0B',
            'Other': '#6B7280'
        };
        const bgColors = pieLabels.map(l => pieColors[l] || '#D4AF37');

        const pieCtx = document.getElementById('paymentPieChart');
        if (pieCtx && pieValues.some(v => v > 0)) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieValues,
                        backgroundColor: bgColors,
                        borderWidth: 2,
                        borderColor: '#181818'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#9CA3AF',
                                font: { size: 10 },
                                boxWidth: 10,
                                padding: 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ₹' + Number(context.raw).toLocaleString('en-IN', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endsection
