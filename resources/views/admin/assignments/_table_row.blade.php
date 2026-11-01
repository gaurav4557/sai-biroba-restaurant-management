@php
    $activeAssignment = $table->activeAssignment;
    $assignedWaiter = $activeAssignment?->waiter;
    $isAssignable = $table->isAssignable();

    $statusColors = [
        'available' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
        'occupied' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
        'billed' => 'bg-[#D4AF37]/15 text-[#D4AF37] border-[#D4AF37]/40',
        'out_of_service' => 'bg-gray-800 text-gray-400 border-gray-700',
    ];
    $statusBadge = $statusColors[$table->status] ?? $statusColors['out_of_service'];
@endphp

<div class="bg-[#1F1F1F] border border-[#2D2D2D] rounded-xl p-4 transition hover:border-[#3D3D3D]">
    
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        
        <!-- Left: Table Identifiers & Status -->
        <div class="flex items-center space-x-4 min-w-[220px]">
            <div class="w-12 h-12 rounded-xl bg-[#282828] border border-[#383838] flex items-center justify-center font-display font-bold text-xl text-[#FDFBF7] shadow-inner">
                {{ $table->table_number }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold text-gray-300">{{ $table->seating_capacity }} Guests</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $statusBadge }}">
                        {{ ucfirst(str_replace('_', ' ', $table->status)) }}
                    </span>
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">
                    {{ $table->floor_section }}
                    @if ($table->activeVisit)
                        &bull; <span class="text-amber-400">{{ $table->activeVisit->guest_count }} Guests dining</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Center: Currently Assigned Waiter State -->
        <div class="flex-1 min-w-[240px]">
            @if ($assignedWaiter)
                <div class="flex items-center space-x-3 bg-[#262626] border border-[#333333] px-3.5 py-2 rounded-lg">
                    <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-xs">
                        {{ strtoupper(substr($assignedWaiter->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 truncate">
                        <div class="text-xs font-semibold text-[#FDFBF7] truncate">
                            {{ $assignedWaiter->name }}
                        </div>
                        <div class="text-[10px] text-gray-400">
                            Assigned {{ \Carbon\Carbon::parse($activeAssignment->assigned_at)->format('h:i A (d M)') }}
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        Active
                    </span>
                </div>
            @else
                <div class="bg-[#242424] border border-[#2E2E2E] px-3.5 py-2 rounded-lg flex items-center justify-between text-xs text-gray-400">
                    <span class="italic">No waiter actively assigned</span>
                    @if (!$isAssignable)
                        <span class="text-[10px] text-red-400 font-semibold uppercase">
                            Blocked ({{ ucfirst($table->status) }})
                        </span>
                    @else
                        <span class="text-[10px] text-amber-400 font-medium">Ready for allocation</span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Right: Actions (Assign / Reassign / Unassign) -->
        <div class="flex flex-wrap items-center gap-2 lg:justify-end">
            @if ($isAssignable)
                @if ($assignedWaiter)
                    <!-- Reassign Form (Inline dropdown) -->
                    <form method="POST" action="{{ route('admin.assignments.reassign', $table) }}" class="flex items-center space-x-2">
                        @csrf
                        @method('PUT')
                        <select name="waiter_id" required class="bg-[#262626] border border-[#3A3A3A] text-xs text-[#FDFBF7] rounded-lg px-2.5 py-1.5 focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37]">
                            <option value="">Reassign to...</option>
                            @foreach ($waiters as $w)
                                @if ($w->id !== $assignedWaiter->id)
                                    <option value="{{ $w->id }}">
                                        {{ $w->name }} ({{ $w->active_waiter_assignments_count }} tables)
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" 
                                class="px-3 py-1.5 rounded-lg bg-[#D4AF37]/20 hover:bg-[#D4AF37]/30 text-[#D4AF37] border border-[#D4AF37]/40 text-xs font-semibold transition">
                            Reassign
                        </button>
                    </form>

                    <!-- Unassign Form -->
                    <form method="POST" action="{{ route('admin.assignments.destroy', $table) }}" onsubmit="return confirm('Remove active assignment for {{ $table->table_number }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-3 py-1.5 rounded-lg bg-red-950/40 hover:bg-red-900/50 text-red-300 border border-red-500/40 text-xs font-medium transition"
                                title="End active assignment">
                            Unassign
                        </button>
                    </form>
                @else
                    <!-- Initial Assign Form -->
                    <form method="POST" action="{{ route('admin.assignments.store') }}" class="flex items-center space-x-2 w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                        <select name="waiter_id" required class="bg-[#262626] border border-[#3A3A3A] text-xs text-[#FDFBF7] rounded-lg px-2.5 py-1.5 focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] flex-1 sm:flex-initial">
                            <option value="">Select Waiter...</option>
                            @foreach ($waiters as $w)
                                <option value="{{ $w->id }}">
                                    {{ $w->name }} ({{ $w->active_waiter_assignments_count }} tables)
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" 
                                class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-sm">
                            Assign Waiter
                        </button>
                    </form>
                @endif
            @else
                <span class="text-xs text-gray-500 italic py-1.5 px-3 rounded bg-[#181818] border border-[#2B2B2B]">
                    Table cannot be assigned while {{ $table->status }}.
                </span>
            @endif
        </div>

    </div>

    <!-- Assignment History Drawer -->
    @if ($table->assignments->isNotEmpty())
        <details class="mt-3 pt-2.5 border-t border-[#292929] text-xs group">
            <summary class="cursor-pointer text-gray-400 hover:text-[#D4AF37] font-medium flex items-center space-x-1.5 transition select-none">
                <svg class="w-3.5 h-3.5 transform group-open:rotate-90 transition-transform text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>Assignment History ({{ $table->assignments->count() }} records preserved)</span>
            </summary>
            
            <div class="mt-2.5 overflow-x-auto">
                <table class="w-full text-left text-[11px] text-gray-300">
                    <thead class="bg-[#242424] text-gray-400 uppercase text-[10px] tracking-wider border-b border-[#303030]">
                        <tr>
                            <th class="py-1.5 px-3">Status</th>
                            <th class="py-1.5 px-3">Waiter</th>
                            <th class="py-1.5 px-3">Assigned At</th>
                            <th class="py-1.5 px-3">Unassigned At</th>
                            <th class="py-1.5 px-3">Assigned By</th>
                            <th class="py-1.5 px-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2B2B2B]">
                        @foreach ($table->assignments as $hist)
                            <tr class="hover:bg-[#252525]/60">
                                <td class="py-1.5 px-3">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase {{ $hist->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : ($hist->status === 'reassigned' ? 'bg-amber-500/20 text-amber-400' : 'bg-gray-700 text-gray-400') }}">
                                        {{ $hist->status }}
                                    </span>
                                </td>
                                <td class="py-1.5 px-3 font-medium text-[#FDFBF7]">{{ $hist->waiter?->name ?? 'User #' . $hist->waiter_id }}</td>
                                <td class="py-1.5 px-3 text-gray-400 font-mono">{{ \Carbon\Carbon::parse($hist->assigned_at)->format('Y-m-d h:i A') }}</td>
                                <td class="py-1.5 px-3 text-gray-400 font-mono">{{ $hist->unassigned_at ? \Carbon\Carbon::parse($hist->unassigned_at)->format('Y-m-d h:i A') : '—' }}</td>
                                <td class="py-1.5 px-3 text-gray-400">{{ $hist->assignedBy?->name ?? 'System' }}</td>
                                <td class="py-1.5 px-3 text-gray-400 italic">{{ $hist->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endif

</div>
