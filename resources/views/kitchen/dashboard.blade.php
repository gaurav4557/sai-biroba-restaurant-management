@extends('layouts.app', ['title' => 'Kitchen Display System (KDS)'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">

    <!-- KDS Header & Metric Strip -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 sm:p-6 mb-6 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-80 h-80 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        Live Kitchen Display System (KDS)
                    </span>
                    <span class="text-xs text-gray-400">&bull;</span>
                    <span class="text-xs text-gray-300 font-semibold">{{ $user->name }} (Chef)</span>
                </div>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-[#FDFBF7] mt-1.5">
                    Kitchen Order Queue
                </h1>
                <p class="text-xs text-gray-400 mt-0.5">
                    Real-time KOT tickets &bull; FIFO order preparation &bull; Item status lifecycle
                </p>
            </div>

            <!-- Auto-refresh & Manual Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-[#202020] border border-[#2F2F2F] rounded-xl px-3 py-2 flex items-center space-x-2.5 text-xs text-gray-300">
                    <div id="refreshIndicatorDot" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span id="refreshTimerLabel">Auto-refresh: 20s</span>
                    <button type="button" 
                            id="toggleAutoRefreshBtn" 
                            onclick="toggleAutoRefresh()" 
                            class="text-gray-400 hover:text-[#D4AF37] font-semibold text-[11px] underline ml-1">
                        Pause
                    </button>
                </div>

                <button type="button" 
                        onclick="window.location.reload();" 
                        class="px-3.5 py-2 rounded-xl bg-[#222222] hover:bg-[#2C2C2C] active:scale-95 text-xs font-semibold text-[#D4AF37] border border-[#383838] transition flex items-center space-x-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh Now</span>
                </button>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 mt-5 pt-4 border-t border-[#262626] relative z-10">
            <a href="{{ route('kitchen.dashboard', ['filter' => 'all']) }}" 
               class="p-2.5 rounded-xl border text-center transition {{ $filter === 'all' ? 'bg-[#222222] border-[#D4AF37] shadow-sm' : 'bg-[#1C1C1C] border-[#2B2B2B] hover:border-gray-600' }}">
                <div class="text-[10px] uppercase font-semibold text-gray-400">All Active</div>
                <div class="text-xl font-display font-bold text-[#FDFBF7] mt-0.5">{{ $totalActiveCount }}</div>
            </a>
            <a href="{{ route('kitchen.dashboard', ['filter' => 'new']) }}" 
               class="p-2.5 rounded-xl border text-center transition {{ $filter === 'new' ? 'bg-amber-500/10 border-amber-500 shadow-sm' : 'bg-[#1C1C1C] border-[#2B2B2B] hover:border-gray-600' }}">
                <div class="text-[10px] uppercase font-semibold text-amber-400">New Queue</div>
                <div class="text-xl font-display font-bold text-amber-400 mt-0.5">{{ $submittedCount }}</div>
            </a>
            <a href="{{ route('kitchen.dashboard', ['filter' => 'preparing']) }}" 
               class="p-2.5 rounded-xl border text-center transition {{ $filter === 'preparing' ? 'bg-blue-500/10 border-blue-500 shadow-sm' : 'bg-[#1C1C1C] border-[#2B2B2B] hover:border-gray-600' }}">
                <div class="text-[10px] uppercase font-semibold text-blue-400">Cooking</div>
                <div class="text-xl font-display font-bold text-blue-400 mt-0.5">{{ $preparingCount }}</div>
            </a>
            <a href="{{ route('kitchen.dashboard', ['filter' => 'ready']) }}" 
               class="p-2.5 rounded-xl border text-center transition {{ $filter === 'ready' ? 'bg-emerald-500/10 border-emerald-500 shadow-sm' : 'bg-[#1C1C1C] border-[#2B2B2B] hover:border-gray-600' }}">
                <div class="text-[10px] uppercase font-semibold text-emerald-400">Ready to Serve</div>
                <div class="text-xl font-display font-bold text-emerald-400 mt-0.5">{{ $readyCount }}</div>
            </a>
            <a href="{{ route('kitchen.dashboard', ['filter' => 'completed']) }}" 
               class="p-2.5 rounded-xl border text-center transition {{ $filter === 'completed' ? 'bg-[#222222] border-gray-500 shadow-sm' : 'bg-[#1C1C1C] border-[#2B2B2B] hover:border-gray-600' }}">
                <div class="text-[10px] uppercase font-semibold text-gray-400">Served Today</div>
                <div class="text-xl font-display font-bold text-gray-300 mt-0.5">{{ $completedCount }}</div>
            </a>
        </div>
    </div>

    <!-- Ticket Queue Grid -->
    @if ($orders->isEmpty())
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-10 text-center shadow-lg">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="font-display font-bold text-lg text-[#FDFBF7]">Kitchen Queue Clear</h2>
            <p class="text-xs text-gray-400 max-w-sm mx-auto mt-1.5 leading-relaxed">
                @if ($filter !== 'all')
                    No tickets found matching the '{{ ucfirst($filter) }}' filter.
                @else
                    All incoming food orders have been prepared and served. Waiting for waiters to place new KOT orders.
                @endif
            </p>
            @if ($filter !== 'all')
                <a href="{{ route('kitchen.dashboard', ['filter' => 'all']) }}" 
                   class="mt-4 inline-flex items-center px-4 py-2 rounded-xl bg-[#242424] hover:bg-[#2C2C2C] text-xs font-semibold text-[#D4AF37] border border-[#383838] transition">
                    View All Active Tickets
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($orders as $order)
                @php
                    $submittedCarbon = \Carbon\Carbon::parse($order->submitted_at ?? $order->created_at);
                    $elapsedMinutes = $submittedCarbon->diffInMinutes();
                    
                    // Color code urgency based on wait time
                    if ($elapsedMinutes >= 20) {
                        $urgencyBorder = 'border-red-500/60 shadow-red-500/10';
                        $timerBadge = 'bg-red-500/20 text-red-400 border-red-500/40';
                    } elseif ($elapsedMinutes >= 10) {
                        $urgencyBorder = 'border-amber-500/60 shadow-amber-500/10';
                        $timerBadge = 'bg-amber-500/20 text-amber-300 border-amber-500/40';
                    } else {
                        $urgencyBorder = 'border-[#2E2E2E]';
                        $timerBadge = 'bg-[#262626] text-gray-300 border-[#383838]';
                    }

                    $orderStatusBadges = [
                        'submitted'  => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30', 'label' => 'New / In Queue'],
                        'preparing' => ['bg' => 'bg-blue-500/15', 'text' => 'text-blue-400', 'border' => 'border-blue-500/30', 'label' => 'Cooking in Kitchen'],
                        'ready'     => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30', 'label' => 'Ready to Serve'],
                        'served'    => ['bg' => 'bg-gray-700/30', 'text' => 'text-gray-400', 'border' => 'border-gray-600/30', 'label' => 'Served'],
                    ];
                    $osb = $orderStatusBadges[$order->status] ?? $orderStatusBadges['submitted'];

                    $pendingItemCount = $order->items->where('status', 'pending')->count();
                    $preparingItemCount = $order->items->where('status', 'preparing')->count();
                    $readyItemCount = $order->items->where('status', 'ready')->count();
                    $servedItemCount = $order->items->where('status', 'served')->count();
                    $totalItemCount = $order->items->where('status', '!=', 'cancelled')->count();
                @endphp

                <div class="bg-[#181818] border {{ $urgencyBorder }} rounded-2xl shadow-xl flex flex-col justify-between overflow-hidden transition hover:border-[#D4AF37]/40">
                    
                    <div>
                        <!-- Ticket Card Header -->
                        <div class="bg-[#202020] p-4 border-b border-[#2B2B2B]">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-display font-bold text-2xl text-[#FDFBF7]">
                                            Table {{ $order->table?->table_number ?? $order->table_id }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-[#2A2A2A] text-gray-300 border border-[#3A3A3A]">
                                            {{ $order->table?->floor_section ?? 'Dining' }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#D4AF37] text-[#121212]">
                                            R{{ $order->round_number }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-mono text-gray-400 mt-1">
                                        #{{ $order->order_number }}
                                    </div>
                                </div>

                                <div class="text-right flex flex-col items-end">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $timerBadge }}" 
                                          title="Submitted at {{ $submittedCarbon->format('h:i:s A') }}">
                                        {{ $elapsedMinutes }}m ago
                                    </span>
                                    <span class="mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $osb['bg'] }} {{ $osb['text'] }} {{ $osb['border'] }}">
                                        {{ $osb['label'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Waiter Info Bar -->
                            <div class="flex items-center justify-between text-[11px] text-gray-400 mt-2.5 pt-2 border-t border-[#2B2B2B]">
                                <div class="flex items-center space-x-1.5 truncate">
                                    <svg class="w-3.5 h-3.5 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-gray-300 font-medium truncate">{{ $order->waiter?->name ?? 'Waiter' }}</span>
                                </div>
                                <span class="text-[10px] text-gray-500">
                                    {{ $submittedCarbon->format('h:i A') }}
                                </span>
                            </div>
                        </div>

                        <!-- Special Kitchen Order Notes -->
                        @if ($order->order_notes)
                            <div class="px-4 py-2.5 bg-amber-500/10 border-b border-amber-500/20 text-xs text-amber-300 flex items-start space-x-2">
                                <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold italic">Note: {{ $order->order_notes }}</span>
                            </div>
                        @endif

                        <!-- Items List -->
                        <div class="p-3 sm:p-4 divide-y divide-[#242424]">
                            @foreach ($order->items as $item)
                                @php
                                    $itemDiet = $item->menuItem?->dietary_type ?? 'veg';
                                    $itemStatus = $item->status;
                                @endphp

                                <div class="py-2.5 flex items-center justify-between gap-3">
                                    <div class="flex items-start space-x-2.5 min-w-0">
                                        <!-- Diet Dot -->
                                        <div class="mt-1 shrink-0">
                                            @if ($itemDiet === 'veg')
                                                <div class="w-4 h-4 rounded border border-emerald-500 flex items-center justify-center p-0.5" title="Vegetarian">
                                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                </div>
                                            @elseif ($itemDiet === 'non_veg')
                                                <div class="w-4 h-4 rounded border border-red-500 flex items-center justify-center p-0.5" title="Non-Vegetarian">
                                                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                                </div>
                                            @else
                                                <div class="w-4 h-4 rounded border border-amber-500 flex items-center justify-center p-0.5" title="Egg">
                                                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Item Quantity & Name -->
                                        <div class="min-w-0">
                                            <div class="flex items-baseline space-x-1.5">
                                                <span class="font-display font-bold text-base text-[#D4AF37]">
                                                    {{ $item->quantity }}&times;
                                                </span>
                                                <span class="text-sm font-semibold text-[#FDFBF7] truncate">
                                                    {{ $item->item_name }}
                                                </span>
                                            </div>

                                            @if ($item->special_instructions)
                                                <div class="text-[11px] text-amber-300 font-medium italic mt-0.5">
                                                    &ldquo;{{ $item->special_instructions }}&rdquo;
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Item Action Controls -->
                                    <div class="shrink-0 flex items-center space-x-2">
                                        @if ($item->isPending())
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                Pending
                                            </span>
                                            <form method="POST" action="{{ route('kitchen.items.status', $item) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="preparing">
                                                <button type="submit" 
                                                        title="Start Cooking {{ $item->item_name }}"
                                                        class="px-2 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-[10px] font-bold transition flex items-center space-x-1 active:scale-95 shadow-sm">
                                                    <span>Start Cooking &rarr;</span>
                                                </button>
                                            </form>
                                        @elseif ($item->isPreparing())
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                                Cooking
                                            </span>
                                            <form method="POST" action="{{ route('kitchen.items.status', $item) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="ready">
                                                <button type="submit" 
                                                        title="Mark {{ $item->item_name }} Ready to Serve"
                                                        class="px-2 py-1 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold transition flex items-center space-x-1 active:scale-95 shadow-sm">
                                                    <span>Mark Ready &rarr;</span>
                                                </button>
                                            </form>
                                        @elseif ($item->isReady())
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                Ready to Serve
                                            </span>
                                            <form method="POST" action="{{ route('kitchen.items.status', $item) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="served">
                                                <button type="submit" 
                                                        title="Mark {{ $item->item_name }} Served to Table"
                                                        class="px-2 py-1 rounded-lg bg-[#282828] hover:bg-[#333333] text-gray-300 hover:text-white border border-[#404040] text-[10px] font-semibold transition flex items-center space-x-1 active:scale-95 shadow-sm">
                                                    <span>Mark Served &rarr;</span>
                                                </button>
                                            </form>
                                        @elseif ($item->isServed())
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-800 text-gray-400 border border-gray-700">
                                                Served &check;
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Ticket Footer Action Bar -->
                    <div class="p-3 bg-[#1D1D1D] border-t border-[#262626]">
                        @if ($order->status === 'submitted')
                            <form method="POST" action="{{ route('kitchen.orders.start-prep', $order) }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 active:scale-98 text-[#121212] font-bold text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center space-x-1.5">
                                    <svg class="w-4 h-4 text-[#121212]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                    </svg>
                                    <span>Start Cooking KOT</span>
                                </button>
                            </form>
                        @elseif ($order->status === 'preparing')
                            <form method="POST" action="{{ route('kitchen.orders.mark-ready', $order) }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 active:scale-98 text-[#121212] font-bold text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center space-x-1.5">
                                    <svg class="w-4 h-4 text-[#121212]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Mark All Ready to Serve</span>
                                </button>
                            </form>
                        @elseif ($order->status === 'ready')
                            <form method="POST" action="{{ route('kitchen.orders.mark-served', $order) }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 active:scale-98 text-white font-bold text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center space-x-1.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Mark Entire KOT as Served &rarr;</span>
                                </button>
                            </form>
                        @else
                            <div class="py-2 text-center text-xs text-gray-500 font-semibold">
                                Completed &bull; All Items Served
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
    // Live Auto-Refresh Controller (20s interval)
    let autoRefreshActive = true;
    let refreshSecondsRemaining = 20;
    let countdownInterval = null;

    function startRefreshCountdown() {
        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            if (!autoRefreshActive) return;

            refreshSecondsRemaining--;
            const label = document.getElementById('refreshTimerLabel');
            if (label) {
                label.textContent = `Auto-refresh: ${refreshSecondsRemaining}s`;
            }

            if (refreshSecondsRemaining <= 0) {
                // Do not reload if user is currently interacting with or submitting a form
                const activeForms = document.querySelectorAll('form button:disabled');
                if (activeForms.length === 0) {
                    window.location.reload();
                } else {
                    refreshSecondsRemaining = 10;
                }
            }
        }, 1000);
    }

    function toggleAutoRefresh() {
        autoRefreshActive = !autoRefreshActive;
        const btn = document.getElementById('toggleAutoRefreshBtn');
        const dot = document.getElementById('refreshIndicatorDot');
        const label = document.getElementById('refreshTimerLabel');

        if (autoRefreshActive) {
            btn.textContent = 'Pause';
            dot.classList.remove('bg-gray-500');
            dot.classList.add('bg-emerald-400', 'animate-pulse');
            refreshSecondsRemaining = 20;
            label.textContent = `Auto-refresh: 20s`;
        } else {
            btn.textContent = 'Resume';
            dot.classList.remove('bg-emerald-400', 'animate-pulse');
            dot.classList.add('bg-gray-500');
            label.textContent = 'Auto-refresh: Paused';
        }
    }

    // Disable buttons on form submission to prevent double-tap submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            autoRefreshActive = false;
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
            }
        });
    });

    // Start timer on page load
    startRefreshCountdown();
</script>
@endsection
