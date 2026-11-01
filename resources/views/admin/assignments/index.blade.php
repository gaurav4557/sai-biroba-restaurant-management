@extends('layouts.app', ['title' => 'Waiter Table Assignments'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Breadcrumb & Top Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <nav class="flex items-center space-x-2 text-xs text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-[#D4AF37] transition">Dashboard</a>
            <span>&bull;</span>
            <a href="{{ route('admin.tables.index') }}" class="hover:text-[#D4AF37] transition">Floor Plan</a>
            <span>&bull;</span>
            <span class="text-[#D4AF37] font-semibold">Waiter Assignments</span>
        </nav>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.tables.index') }}" 
               class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-[#222222] hover:bg-[#2B2B2B] text-gray-300 hover:text-[#D4AF37] border border-[#333333] text-xs font-medium transition">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                Floor Plan View
            </a>
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-[#D4AF37]/15 hover:bg-[#D4AF37]/25 text-[#D4AF37] border border-[#D4AF37]/30 text-xs font-semibold transition">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Executive Title Banner -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 sm:p-8 mb-8 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#D4AF37]/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30">
                    Phase 4 &bull; Service &amp; Shift Management
                </span>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-[#FDFBF7] mt-3">
                    Waiter-to-Table Assignments
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    Sai Biroba Veg &amp; Non-Veg Restaurant &bull; Shift Allocation &bull; 1-Waiter Strict Invariant
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#242424] text-xs text-gray-300 border border-[#333333]">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                    Assignments Active
                </span>
            </div>
        </div>
    </div>

    <!-- Summary KPI Bar -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <div class="bg-[#1A1A1A] border border-[#2B2B2B] rounded-xl p-4 sm:p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Tables</div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-[#FDFBF7] mt-1">{{ $totalTables }}</div>
            <div class="text-[11px] text-[#D4AF37] font-medium mt-0.5">Room 1 &bull; Room 2</div>
        </div>

        <div class="bg-[#1A1A1A] border border-emerald-500/30 rounded-xl p-4 sm:p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">Assigned Tables</div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-emerald-400 mt-1">{{ $assignedCount }}</div>
            <div class="text-[11px] text-gray-400 font-medium mt-0.5">Active Service Coverage</div>
        </div>

        <div class="bg-[#1A1A1A] border border-amber-500/30 rounded-xl p-4 sm:p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-amber-400">Unassigned Tables</div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-amber-400 mt-1">{{ $unassignedCount }}</div>
            <div class="text-[11px] text-gray-400 font-medium mt-0.5">Awaiting Waiter Allocation</div>
        </div>

        <div class="bg-[#1A1A1A] border border-sky-500/30 rounded-xl p-4 sm:p-5">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-sky-400">Waiters on Duty</div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-sky-400 mt-1">{{ $activeWaitersCount }}</div>
            <div class="text-[11px] text-gray-400 font-medium mt-0.5">Available for Assignment</div>
        </div>

    </div>

    <!-- Active Waiters Workload Overview -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 mb-8 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="font-display font-bold text-base sm:text-lg text-[#FDFBF7]">
                    Waiter Workload &amp; Shift Distribution
                </h2>
            </div>
            <span class="text-xs text-gray-400">One waiter can handle multiple tables</span>
        </div>

        @if ($waiters->isEmpty())
            <div class="p-4 rounded-xl bg-[#222222] border border-[#333333] text-center text-sm text-gray-400">
                No active waiter accounts found in system. Please ensure waiter accounts exist with active status.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach ($waiters as $w)
                    <div class="p-3.5 rounded-xl bg-[#202020] border border-[#2F2F2F] flex items-center justify-between hover:border-[#D4AF37]/40 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($w->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-[#FDFBF7] truncate max-w-[130px]" title="{{ $w->name }}">
                                    {{ $w->name }}
                                </div>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $w->phone }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-md {{ $w->active_waiter_assignments_count > 0 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-gray-800 text-gray-400 border border-gray-700' }}">
                                {{ $w->active_waiter_assignments_count }} {{ Str::plural('Table', $w->active_waiter_assignments_count) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ROOM 1 ASSIGNMENT TABLE -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 mb-8 shadow-md">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#2A2A2A]">
            <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full bg-[#D4AF37]"></div>
                <h2 class="font-display font-bold text-lg text-[#FDFBF7]">
                    Room 1 &bull; Tables T1 to T10
                </h2>
            </div>
            <span class="text-xs text-gray-400">{{ $room1Tables->count() }} Dining Tables</span>
        </div>

        <div class="space-y-3">
            @foreach ($room1Tables as $table)
                @include('admin.assignments._table_row', ['table' => $table, 'waiters' => $waiters])
            @endforeach
        </div>
    </div>

    <!-- ROOM 2 ASSIGNMENT TABLE -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#2A2A2A]">
            <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full bg-[#C5A059]"></div>
                <h2 class="font-display font-bold text-lg text-[#FDFBF7]">
                    Room 2 &bull; Tables T11 to T20
                </h2>
            </div>
            <span class="text-xs text-gray-400">{{ $room2Tables->count() }} Dining Tables</span>
        </div>

        <div class="space-y-3">
            @foreach ($room2Tables as $table)
                @include('admin.assignments._table_row', ['table' => $table, 'waiters' => $waiters])
            @endforeach
        </div>
    </div>

</div>
@endsection
