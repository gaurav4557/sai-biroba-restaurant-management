@php
    $visit = $table->activeVisit;

    $statusConfig = [
        'available' => [
            'label' => 'Available',
            'border' => 'border-emerald-500/40',
            'badge' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
            'dot' => 'bg-emerald-400',
        ],
        'occupied' => [
            'label' => 'Occupied',
            'border' => 'border-amber-500/40',
            'badge' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
            'dot' => 'bg-amber-400',
        ],
        'billed' => [
            'label' => 'Billed',
            'border' => 'border-[#D4AF37]/40',
            'badge' => 'bg-[#D4AF37]/15 text-[#D4AF37] border-[#D4AF37]/30',
            'dot' => 'bg-[#D4AF37]',
        ],
        'out_of_service' => [
            'label' => 'Out of Service',
            'border' => 'border-gray-700',
            'badge' => 'bg-gray-800 text-gray-400 border-gray-700',
            'dot' => 'bg-gray-500',
        ],
    ];

    $cfg = $statusConfig[$table->status] ?? $statusConfig['out_of_service'];
@endphp

<div class="bg-[#1C1C1C] border {{ $cfg['border'] }} rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-md transition hover:border-[#D4AF37]/50">
    
    <div>
        <!-- Card Header: Table Number & Status Pill -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-baseline space-x-2">
                <span class="font-display font-bold text-2xl text-[#FDFBF7]">
                    {{ $table->table_number }}
                </span>
                <span class="text-xs text-gray-400">
                    ({{ $table->floor_section }})
                </span>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $cfg['badge'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }} mr-1.5"></span>
                {{ $cfg['label'] }}
            </span>
        </div>

        <!-- Seating Capacity -->
        <div class="flex items-center space-x-1.5 text-xs text-gray-300 mb-3 bg-[#242424] px-2.5 py-1 rounded-md border border-[#2E2E2E] w-fit">
            <svg class="w-3.5 h-3.5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="font-medium">{{ $table->seating_capacity }} Guests Capacity</span>
        </div>

        <!-- Session Status Container -->
        <div class="pt-2.5 border-t border-[#292929] mb-3">
            @if ($visit)
                <div class="bg-[#242424] rounded-lg p-2.5 border border-[#303030]">
                    <div class="flex items-center justify-between text-xs text-[#FDFBF7] font-semibold">
                        <span>{{ $visit->guest_count }} {{ Str::plural('Guest', $visit->guest_count) }} Seated</span>
                        <span class="text-[10px] font-mono text-[#D4AF37]">{{ $visit->visit_code }}</span>
                    </div>
                    @if ($visit->guest_name)
                        <div class="text-[11px] text-gray-400 mt-0.5 truncate" title="{{ $visit->guest_name }}">
                            Guest: {{ $visit->guest_name }}
                        </div>
                    @endif
                    @if ($visit->opened_at)
                        <div class="text-[10px] text-gray-500 mt-0.5">
                            Active since {{ \Carbon\Carbon::parse($visit->opened_at)->format('h:i A') }}
                        </div>
                    @endif
                </div>
            @elseif ($table->status === 'available')
                <div class="text-xs text-emerald-400/90 flex items-center space-x-1.5 py-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Ready for guest seating</span>
                </div>
            @elseif ($table->status === 'billed')
                <div class="text-xs text-[#D4AF37] py-1">
                    Bill requested &bull; Awaiting cashier settlement
                </div>
            @else
                <div class="text-xs text-gray-500 py-1">
                    Table temporarily out of service
                </div>
            @endif
        </div>
    </div>

    <!-- Action Button -->
    <div class="pt-2">
        @if ($table->status === 'occupied')
            @if ($visit)
                <a href="{{ route('waiter.visits.show', $visit) }}" 
                   class="w-full py-2.5 px-3 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 text-xs font-semibold transition flex items-center justify-center space-x-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>View Visit &amp; Add Orders</span>
                </a>
            @else
                <button type="button" disabled
                        class="w-full py-2.5 px-3 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 text-xs font-semibold cursor-not-allowed">
                    Occupied (Syncing...)
                </button>
            @endif
        @elseif ($table->status === 'available')
            <a href="{{ route('waiter.visits.create', $table) }}" 
               class="w-full py-2.5 px-3 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 text-xs font-semibold transition flex items-center justify-center space-x-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Seat Guests &amp; Order</span>
            </a>
        @elseif ($table->status === 'billed')
            @if ($visit)
                <a href="{{ route('waiter.visits.show', $visit) }}" 
                   class="w-full py-2.5 px-3 rounded-xl bg-[#D4AF37]/20 hover:bg-[#D4AF37]/30 text-[#D4AF37] border border-[#D4AF37]/40 text-xs font-semibold transition flex items-center justify-center space-x-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Billed &bull; View Summary</span>
                </a>
            @else
                <button type="button" disabled
                        class="w-full py-2.5 px-3 rounded-xl bg-[#242424] text-gray-500 border border-[#2E2E2E] text-xs font-semibold cursor-not-allowed">
                    Settling at Counter
                </button>
            @endif
        @else
            <button type="button" disabled
                    class="w-full py-2.5 px-3 rounded-xl bg-[#242424] text-gray-500 border border-[#2E2E2E] text-xs font-semibold cursor-not-allowed">
                Out of Service
            </button>
        @endif
    </div>

</div>
