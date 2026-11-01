@extends('layouts.app', ['title' => 'Daily Financial Ledger — ' . $date->format('d M Y')])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full space-y-6">

    <!-- Header & Date Navigation -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.index') }}" class="text-xs text-gray-400 hover:text-[#D4AF37] transition">
                    &larr; Reports Dashboard
                </a>
                <span class="text-gray-600">&bull;</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    Day Ledger
                </span>
            </div>
            <h1 class="font-display font-bold text-2xl text-[#FDFBF7] mt-2">
                Daily Sales &amp; Collections Ledger
            </h1>
            <p class="text-xs text-gray-400 mt-1">
                Showing transactions for <strong class="text-[#D4AF37]">{{ $date->format('l, d F Y') }}</strong>
            </p>
        </div>

        <!-- Date Controls & Action Buttons -->
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('reports.daily') }}" class="flex items-center space-x-2">
                <a href="{{ route('reports.daily', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" 
                   class="px-3 py-1.5 rounded-xl bg-[#222222] hover:bg-[#2A2A2A] text-gray-300 border border-[#333333] text-xs font-semibold transition"
                   title="Previous Day">
                    &larr; Prev
                </a>

                <input type="date" 
                       name="date" 
                       value="{{ $date->format('Y-m-d') }}" 
                       onchange="this.form.submit()"
                       class="bg-[#121212] border border-[#333333] rounded-xl px-3 py-1.5 text-xs text-gray-200 focus:outline-none focus:border-[#D4AF37] transition">

                <a href="{{ route('reports.daily', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" 
                   class="px-3 py-1.5 rounded-xl bg-[#222222] hover:bg-[#2A2A2A] text-gray-300 border border-[#333333] text-xs font-semibold transition {{ $date->isToday() ? 'opacity-40 pointer-events-none' : '' }}"
                   title="Next Day">
                    Next &rarr;
                </a>
            </form>

            <a href="{{ route('reports.print', ['report_type' => 'daily', 'date' => $date->format('Y-m-d')]) }}" 
               target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-[#222222] hover:bg-[#2A2A2A] text-sky-400 border border-sky-500/30 text-xs font-semibold transition shadow-sm">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print</span>
            </a>

            <a href="{{ route('reports.export.csv', ['report_type' => 'daily', 'date' => $date->format('Y-m-d')]) }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-[#222222] hover:bg-[#2A2A2A] text-emerald-400 border border-emerald-500/30 text-xs font-semibold transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Daily KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-4 shadow-lg text-center">
            <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Gross Collections</div>
            <div class="text-2xl font-bold font-display text-emerald-400 mt-1">₹{{ number_format($report['gross_collections'], 2) }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Successful inflows</div>
        </div>

        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-4 shadow-lg text-center">
            <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Refunds Disbursed</div>
            <div class="text-2xl font-bold font-display text-rose-400 mt-1">₹{{ number_format($report['successful_refunds'], 2) }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Disbursed reversals</div>
        </div>

        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-4 shadow-lg text-center bg-gradient-to-b from-[#1E1B14] to-[#181818]">
            <div class="text-xs text-[#D4AF37] uppercase tracking-wider font-semibold">Net Collections</div>
            <div class="text-2xl font-bold font-display text-[#D4AF37] mt-1">₹{{ number_format($report['net_collections'], 2) }}</div>
            <div class="text-[10px] text-[#D4AF37]/80 mt-0.5">Banked revenue</div>
        </div>

        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-4 shadow-lg text-center">
            <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Gross Billed Sales</div>
            <div class="text-2xl font-bold font-display text-sky-400 mt-1">₹{{ number_format($report['gross_billed_sales'], 2) }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">{{ $report['order_stats']['total_bills_issued'] }} Invoices Issued</div>
        </div>

    </div>

    <!-- Hourly Distribution Chart (Rush Hours Analysis) -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-[#262626] pb-3">
            <div>
                <h2 class="font-display font-bold text-base text-[#FDFBF7]">Hourly Collections Velocity (Rush Hours)</h2>
                <p class="text-xs text-gray-400 mt-0.5">Revenue collected hour by hour from 08:00 AM to 11:59 PM</p>
            </div>
            <span class="text-xs text-emerald-400 font-mono font-semibold">Peak Hour Tracker</span>
        </div>

        <div class="relative w-full h-64">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    <!-- Payment Breakdown & Best Sellers for the Day -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Payment Breakdown -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#262626] pb-3">
                <h2 class="font-display font-bold text-base text-[#FDFBF7]">Payment Methods</h2>
                <span class="text-xs text-gray-500">{{ $date->format('d M Y') }}</span>
            </div>

            <table class="w-full text-left text-xs">
                <thead class="text-gray-400 uppercase tracking-wider border-b border-[#262626]">
                    <tr>
                        <th class="py-2.5 font-semibold">Method</th>
                        <th class="py-2.5 text-center font-semibold">Txns</th>
                        <th class="py-2.5 text-right font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#242424] text-gray-300">
                    @forelse($report['payment_methods'] as $pm)
                        <tr class="hover:bg-[#202020] transition">
                            <td class="py-3 font-semibold text-[#FDFBF7]">{{ $pm['label'] }}</td>
                            <td class="py-3 text-center font-mono">{{ $pm['transaction_count'] }}</td>
                            <td class="py-3 text-right font-mono font-bold text-[#FDFBF7]">₹{{ number_format($pm['total_amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">No payments received on this day</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Top Dishes for the Day -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#262626] pb-3">
                <h2 class="font-display font-bold text-base text-[#FDFBF7]">Top Dishes Today</h2>
                <span class="text-xs text-[#D4AF37] font-semibold">Quantity Sold</span>
            </div>

            <table class="w-full text-left text-xs">
                <thead class="text-gray-400 uppercase tracking-wider border-b border-[#262626]">
                    <tr>
                        <th class="py-2.5 font-semibold">Dish</th>
                        <th class="py-2.5 text-center font-semibold">Qty</th>
                        <th class="py-2.5 text-right font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#242424] text-gray-300">
                    @forelse($report['best_sellers']->take(6) as $dish)
                        <tr class="hover:bg-[#202020] transition">
                            <td class="py-3 font-semibold text-[#FDFBF7]">{{ $dish->item_name }}</td>
                            <td class="py-3 text-center font-mono font-bold text-sky-400">{{ $dish->quantity_sold }}</td>
                            <td class="py-3 text-right font-mono font-bold text-[#D4AF37]">₹{{ number_format($dish->total_sales, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">No dishes recorded for this day</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Chart.js for Hourly Curve -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hourlyData = @json(array_values($hourlyCollections ?? $report['hourly_collections'] ?? []));
        const labels = hourlyData.map(h => h.label);
        const amounts = hourlyData.map(h => h.collections);

        const ctx = document.getElementById('hourlyChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hourly Collections (₹)',
                        data: amounts,
                        backgroundColor: 'rgba(212, 175, 55, 0.75)',
                        borderColor: '#D4AF37',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₹' + Number(context.raw).toLocaleString('en-IN', {minimumFractionDigits: 2});
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
                                callback: function(v) { return '₹' + v; }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
