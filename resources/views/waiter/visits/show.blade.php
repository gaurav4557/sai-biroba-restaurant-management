@extends('layouts.app', ['title' => 'Table ' . $visit->table->table_number . ' — Visit Hub'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 w-full">

    <!-- Back to Waiter Dashboard -->
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('waiter.dashboard') }}" 
           class="inline-flex items-center text-xs font-semibold text-gray-400 hover:text-[#D4AF37] transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Assigned Tables
        </a>
        <button type="button" onclick="window.location.reload();" 
                class="inline-flex items-center text-xs text-gray-400 hover:text-[#D4AF37] transition">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh Status
        </button>
    </div>

    <!-- Dining Session Overview Header -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 sm:p-6 mb-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-[#D4AF37]/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#D4AF37]/15 text-[#D4AF37] border border-[#D4AF37]/30">
                        {{ $visit->table->floor_section }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider 
                        {{ $visit->status === 'active' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : ($visit->status === 'billed' ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30' : 'bg-gray-700 text-gray-300') }}">
                        Visit Status: {{ ucfirst($visit->status) }}
                    </span>
                    <span class="text-xs font-mono text-gray-400">
                        #{{ $visit->visit_code }}
                    </span>
                </div>

                <div class="flex items-baseline space-x-3 mt-2">
                    <h1 class="font-display font-bold text-3xl text-[#FDFBF7]">
                        Table {{ $visit->table->table_number }}
                    </h1>
                    <span class="text-sm font-semibold text-gray-300">
                        &bull; {{ $visit->guest_count }} {{ Str::plural('Guest', $visit->guest_count) }} Seated
                    </span>
                </div>

                @if ($visit->guest_name)
                    <p class="text-xs text-gray-300 mt-1">
                        Party: <span class="text-[#FDFBF7] font-semibold">{{ $visit->guest_name }}</span>
                    </p>
                @endif

                @if ($visit->notes)
                    <p class="text-xs text-gray-400 mt-0.5 italic">
                        "{{ $visit->notes }}"
                    </p>
                @endif
            </div>

            <!-- Header Quick Stats -->
            <div class="flex items-center space-x-4 bg-[#202020] px-4 py-3 rounded-xl border border-[#2D2D2D]">
                <div class="text-center pr-3 border-r border-[#333333]">
                    <div class="text-[10px] uppercase text-gray-400 font-semibold">Orders</div>
                    <div class="text-xl font-bold font-display text-[#D4AF37]">{{ $visit->orders->count() }}</div>
                </div>
                <div class="text-center pr-3 border-r border-[#333333]">
                    <div class="text-[10px] uppercase text-gray-400 font-semibold">Total Items</div>
                    <div class="text-xl font-bold font-display text-[#FDFBF7]">
                        {{ $visit->orders->sum(fn($o) => $o->items->sum('quantity')) }}
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-[10px] uppercase text-gray-400 font-semibold">Table Total</div>
                    <div class="text-xl font-bold font-display text-emerald-400">
                        ₹{{ number_format($visit->calculateSubtotal(), 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Timing & Primary Action -->
        <div class="mt-5 pt-4 border-t border-[#262626] flex flex-col sm:flex-row items-center justify-between gap-3 relative z-10">
            <div class="text-xs text-gray-400">
                Seated at: <span class="text-gray-200">{{ \Carbon\Carbon::parse($visit->opened_at)->format('h:i A (d M)') }}</span>
                &bull; Waiter: <span class="text-gray-200 font-semibold">{{ $visit->waiter->name ?? 'Staff' }}</span>
            </div>

            @if ($visit->canAcceptOrders())
                <a href="{{ route('waiter.orders.create', $visit) }}" 
                   class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#D4AF37] to-[#C5A059] hover:from-[#E5C158] hover:to-[#D4AF37] active:scale-95 text-[#121212] font-bold text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Add Order (Round {{ $visit->getNextRoundNumber() }})</span>
                </a>
            @else
                <span class="text-xs text-[#D4AF37] font-semibold bg-[#D4AF37]/10 px-3 py-1.5 rounded-lg border border-[#D4AF37]/30">
                    Visit {{ ucfirst($visit->status) }} &bull; Billing in Progress at Counter
                </span>
            @endif
        </div>
    </div>

    <!-- KOT Orders Chronological List -->
    <div class="space-y-6">
        <div class="flex items-center justify-between pb-2 border-b border-[#292929]">
            <h2 class="font-display font-bold text-base sm:text-lg text-[#FDFBF7]">
                Kitchen Order Tickets (KOT) &bull; {{ $visit->orders->count() }} {{ Str::plural('Round', $visit->orders->count()) }}
            </h2>
            <span class="text-xs text-gray-400">Merged into Consolidated Final Bill</span>
        </div>

        @if ($visit->orders->isEmpty())
            <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-8 text-center shadow-md">
                <div class="w-12 h-12 mx-auto rounded-full bg-[#242424] border border-[#333333] flex items-center justify-center text-[#D4AF37] mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="font-display font-bold text-base text-[#FDFBF7]">No Food Orders Placed Yet</h3>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    Guests are seated. Tap the button below to browse the restaurant menu and transmit Round 1 KOT to the kitchen.
                </p>
                @if ($visit->canAcceptOrders())
                    <a href="{{ route('waiter.orders.create', $visit) }}" 
                       class="mt-4 inline-flex items-center px-4 py-2 rounded-xl bg-[#D4AF37] hover:bg-[#E5C158] text-[#121212] font-semibold text-xs transition shadow-sm">
                        + Place Round 1 Food Order
                    </a>
                @endif
            </div>
        @else
            @foreach ($visit->orders as $order)
                @php
                    $orderStatusConfig = [
                        'submitted'  => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30', 'label' => 'Sent to Kitchen'],
                        'preparing'  => ['bg' => 'bg-blue-500/15', 'text' => 'text-blue-400', 'border' => 'border-blue-500/30', 'label' => 'Cooking in Kitchen'],
                        'ready'      => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30', 'label' => 'Ready to Serve'],
                        'served'     => ['bg' => 'bg-gray-700/30', 'text' => 'text-gray-300', 'border' => 'border-gray-600/30', 'label' => 'Served'],
                        'cancelled'  => ['bg' => 'bg-red-500/15', 'text' => 'text-red-400', 'border' => 'border-red-500/30', 'label' => 'Cancelled'],
                    ];
                    $osc = $orderStatusConfig[$order->status] ?? $orderStatusConfig['submitted'];
                @endphp

                <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl overflow-hidden shadow-md">
                    <!-- Order Header -->
                    <div class="bg-[#202020] px-4 sm:px-5 py-3 border-b border-[#292929] flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-6 h-6 rounded-full bg-[#D4AF37] text-[#121212] font-bold text-xs flex items-center justify-center">
                                {{ $order->round_number }}
                            </span>
                            <div>
                                <span class="font-display font-bold text-sm text-[#FDFBF7]">
                                    Round {{ $order->round_number }} &bull; KOT #{{ $order->order_number }}
                                </span>
                                <span class="text-[11px] text-gray-400 ml-2">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $osc['bg'] }} {{ $osc['text'] }} {{ $osc['border'] }}">
                                {{ $osc['label'] }}
                            </span>
                            <span class="text-xs font-bold font-display text-emerald-400">
                                ₹{{ number_format($order->calculateTotal(), 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Order Notes if present -->
                    @if ($order->notes)
                        <div class="px-4 sm:px-5 py-2 bg-[#1C1C1C] border-b border-[#262626] text-xs text-amber-300/90 italic flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Kitchen Note: {{ $order->notes }}</span>
                        </div>
                    @endif

                    <!-- Items Table -->
                    <div class="divide-y divide-[#222222]">
                        @foreach ($order->items as $item)
                            @php
                                $itemStatusBadge = [
                                    'pending'   => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'label' => 'Pending'],
                                    'preparing' => ['bg' => 'bg-blue-500/10', 'text' => 'text-blue-400', 'border' => 'border-blue-500/20', 'label' => 'Cooking'],
                                    'ready'     => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20', 'label' => 'Ready to Serve'],
                                    'served'    => ['bg' => 'bg-gray-700/30', 'text' => 'text-gray-300', 'border' => 'border-gray-600/30', 'label' => 'Served'],
                                    'cancelled' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/20', 'label' => 'Cancelled'],
                                ];
                                $isb = $itemStatusBadge[$item->status] ?? $itemStatusBadge['pending'];
                            @endphp
                            <div class="p-3 sm:px-5 sm:py-3.5 flex items-center justify-between hover:bg-[#1D1D1D] transition">
                                <div class="flex items-start space-x-3">
                                    <!-- Veg/Non-Veg icon -->
                                    <div class="mt-0.5">
                                        @if ($item->menuItem?->food_type === 'veg')
                                            <div class="w-4 h-4 rounded border border-emerald-500 flex items-center justify-center p-0.5" title="Vegetarian">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                            </div>
                                        @else
                                            <div class="w-4 h-4 rounded border border-red-500 flex items-center justify-center p-0.5" title="Non-Vegetarian">
                                                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-sm font-semibold text-[#FDFBF7]">
                                            {{ $item->item_name }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            ₹{{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}
                                        </div>
                                        @if ($item->special_instructions)
                                            <div class="text-[11px] text-[#D4AF37] italic mt-0.5">
                                                Note: "{{ $item->special_instructions }}"
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right flex flex-col items-end">
                                    <div class="text-sm font-bold font-display text-[#FDFBF7]">
                                        ₹{{ number_format($item->subtotal, 2) }}
                                    </div>
                                    <span class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $isb['bg'] }} {{ $isb['text'] }} {{ $isb['border'] }}">
                                        {{ $isb['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

</div>
@endsection
