@php
    $statusConfig = [
        'available' => [
            'label' => 'Available',
            'border' => 'border-emerald-500/40 hover:border-emerald-500/70',
            'bg' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            'dot' => 'bg-emerald-400',
        ],
        'occupied' => [
            'label' => 'Occupied',
            'border' => 'border-amber-500/40 hover:border-amber-500/70',
            'bg' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            'dot' => 'bg-amber-400',
        ],
        'billed' => [
            'label' => 'Billed',
            'border' => 'border-[#D4AF37]/50 hover:border-[#D4AF37]/80',
            'bg' => 'bg-[#D4AF37]/15 text-[#D4AF37] border-[#D4AF37]/40',
            'dot' => 'bg-[#D4AF37]',
        ],
        'out_of_service' => [
            'label' => 'Out of Service',
            'border' => 'border-gray-700 hover:border-gray-600',
            'bg' => 'bg-gray-800 text-gray-400 border-gray-700',
            'dot' => 'bg-gray-500',
        ],
    ];

    $cfg = $statusConfig[$table->status] ?? $statusConfig['out_of_service'];
    $waiter = $table->activeAssignment?->waiter;
    $visit = $table->activeVisit;
@endphp

<div class="bg-[#1A1A1A] border {{ $cfg['border'] }} rounded-xl p-4 sm:p-5 flex flex-col justify-between transition-all duration-200 shadow-md relative group">
    
    <!-- Top Header: Table Number & Status Pill -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-baseline space-x-1.5">
                <span class="font-display font-bold text-2xl text-[#FDFBF7] tracking-wide">
                    {{ $table->table_number }}
                </span>
                <span class="text-[11px] text-gray-400">
                    ({{ $table->floor_section }})
                </span>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $cfg['bg'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $cfg['dot'] }} mr-1.5"></span>
                {{ $cfg['label'] }}
            </span>
        </div>

        <!-- Seating Capacity Badge -->
        <div class="flex items-center space-x-1.5 text-xs text-gray-300 mb-3 bg-[#222222] px-2.5 py-1 rounded-md border border-[#2F2F2F] w-fit">
            <svg class="w-3.5 h-3.5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="font-medium">{{ $table->seating_capacity }} Seater</span>
        </div>
    </div>

    <!-- Middle: Assigned Waiter Section -->
    <div class="pt-3 border-t border-[#262626] my-2">
        <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-1">
            Assigned Waiter
        </div>
        @if ($waiter)
            <div class="flex items-center space-x-2 text-xs text-[#FDFBF7]">
                <div class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-[10px] font-bold">
                    {{ strtoupper(substr($waiter->name, 0, 1)) }}
                </div>
                <span class="font-medium truncate" title="{{ $waiter->name }}">{{ $waiter->name }}</span>
            </div>
        @else
            <span class="inline-block text-[11px] text-gray-500 italic bg-[#222222] px-2 py-0.5 rounded border border-[#2C2C2C]">
                Unassigned
            </span>
        @endif
    </div>

    <!-- Bottom: Active Dining Session Details -->
    <div class="pt-2 border-t border-[#262626] mt-1 text-[11px]">
        @if ($visit)
            <div class="bg-[#222222] rounded-md p-2 border border-[#2F2F2F]">
                <div class="flex items-center justify-between text-gray-300 font-medium">
                    <span>{{ $visit->guest_count }} {{ Str::plural('Guest', $visit->guest_count) }}</span>
                    <span class="text-[10px] font-mono text-[#D4AF37]">{{ $visit->visit_code }}</span>
                </div>
                @if ($visit->guest_name)
                    <div class="text-[10px] text-gray-400 truncate mt-0.5" title="{{ $visit->guest_name }}">
                        {{ $visit->guest_name }}
                    </div>
                @endif
                @if ($visit->opened_at)
                    <div class="text-[10px] text-gray-400 mt-0.5">
                        Since {{ \Carbon\Carbon::parse($visit->opened_at)->format('h:i A') }}
                    </div>
                @endif
            </div>
        @elseif ($table->status === 'available')
            <div class="text-emerald-400/80 text-[11px] flex items-center space-x-1 py-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Ready for guests</span>
            </div>
        @elseif ($table->status === 'out_of_service')
            <div class="text-gray-500 text-[11px] py-1">
                Not in service
            </div>
        @else
            <div class="text-gray-400 text-[11px] py-1">
                No active session logged
            </div>
        @endif
    </div>

</div>
