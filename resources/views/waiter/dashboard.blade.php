@extends('layouts.app', ['title' => 'Waiter Mobile Portal'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 w-full">

    <!-- Mobile-First Shift Header -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-5 mb-6 shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        Active Waiter Shift
                    </span>
                    <button type="button" onclick="window.location.reload();" class="text-gray-400 hover:text-[#D4AF37] transition" title="Refresh Tables">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
                <h1 class="font-display font-bold text-xl sm:text-2xl text-[#FDFBF7] mt-2">
                    {{ $user->name }}
                </h1>
                <p class="text-xs text-gray-400">
                    Sai Biroba &bull; Assigned Tables &bull; Mobile Floor Service
                </p>
            </div>
            <div class="text-right">
                <div class="text-2xl sm:text-3xl font-display font-bold text-[#D4AF37]">{{ $totalAssigned }}</div>
                <div class="text-[10px] uppercase text-gray-400 tracking-wider">Assigned Tables</div>
            </div>
        </div>
    </div>

    <!-- Quick Shift Status Overview Bar -->
    <div class="grid grid-cols-4 gap-2 sm:gap-3 mb-6">
        <div class="bg-[#1C1C1C] border border-[#2B2B2B] rounded-xl p-3 text-center">
            <div class="text-[10px] uppercase font-semibold text-gray-400">Assigned</div>
            <div class="text-lg font-bold font-display text-[#FDFBF7] mt-0.5">{{ $totalAssigned }}</div>
        </div>
        <div class="bg-[#1C1C1C] border border-emerald-500/30 rounded-xl p-3 text-center">
            <div class="text-[10px] uppercase font-semibold text-emerald-400">Available</div>
            <div class="text-lg font-bold font-display text-emerald-400 mt-0.5">{{ $availableCount }}</div>
        </div>
        <div class="bg-[#1C1C1C] border border-amber-500/30 rounded-xl p-3 text-center">
            <div class="text-[10px] uppercase font-semibold text-amber-400">Occupied</div>
            <div class="text-lg font-bold font-display text-amber-400 mt-0.5">{{ $occupiedCount }}</div>
        </div>
        <div class="bg-[#1C1C1C] border border-[#D4AF37]/30 rounded-xl p-3 text-center">
            <div class="text-[10px] uppercase font-semibold text-[#D4AF37]">Billed</div>
            <div class="text-lg font-bold font-display text-[#D4AF37] mt-0.5">{{ $billedCount }}</div>
        </div>
    </div>

    <!-- Assigned Tables Content -->
    @if ($assignedTables->isEmpty())
        <!-- Helpful Empty State -->
        <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-8 sm:p-10 text-center shadow-md">
            <div class="w-14 h-14 mx-auto rounded-full bg-[#242424] border border-[#333333] flex items-center justify-center text-[#D4AF37] mb-4 shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="font-display font-bold text-lg text-[#FDFBF7]">No Tables Assigned Yet</h3>
            <p class="text-xs text-gray-400 mt-2 max-w-md mx-auto leading-relaxed">
                An administrator has not allocated active dining tables to your shift. Once the manager assigns tables to you, they will appear here automatically.
            </p>
            <button type="button" 
                    onclick="window.location.reload();" 
                    class="mt-5 inline-flex items-center px-4 py-2 rounded-lg bg-[#242424] hover:bg-[#2C2C2C] text-xs font-semibold text-[#D4AF37] border border-[#3A3A3A] transition shadow-sm">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Check for New Assignments
            </button>
        </div>
    @else
        <!-- Room 1 Section (if any assigned in Room 1) -->
        @if ($room1Tables->isNotEmpty())
            <div class="mb-6">
                <div class="flex items-center space-x-2 mb-3 pb-1 border-b border-[#292929]">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#D4AF37]"></div>
                    <h2 class="font-display font-bold text-sm sm:text-base text-[#FDFBF7]">
                        Room 1 &bull; Assigned Tables ({{ $room1Tables->count() }})
                    </h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @foreach ($room1Tables as $table)
                        @include('waiter._table_card', ['table' => $table])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Room 2 Section (if any assigned in Room 2) -->
        @if ($room2Tables->isNotEmpty())
            <div class="mb-6">
                <div class="flex items-center space-x-2 mb-3 pb-1 border-b border-[#292929]">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#C5A059]"></div>
                    <h2 class="font-display font-bold text-sm sm:text-base text-[#FDFBF7]">
                        Room 2 &bull; Assigned Tables ({{ $room2Tables->count() }})
                    </h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @foreach ($room2Tables as $table)
                        @include('waiter._table_card', ['table' => $table])
                    @endforeach
                </div>
            </div>
        @endif
    @endif

</div>
@endsection
