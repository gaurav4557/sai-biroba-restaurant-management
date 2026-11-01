@extends('layouts.app', ['title' => 'Floor Plan & Table Management'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Breadcrumb & Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <nav class="flex items-center space-x-2 text-xs text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-[#D4AF37] transition">Dashboard</a>
            <span>&bull;</span>
            <span class="text-[#D4AF37] font-semibold">Floor &amp; Tables</span>
        </nav>
        <div class="flex items-center space-x-3">
            <button type="button" 
                    onclick="window.location.reload();" 
                    title="Refresh Live Status"
                    class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-[#222222] hover:bg-[#2B2B2B] text-gray-300 hover:text-[#D4AF37] border border-[#333333] text-xs font-medium transition shadow-sm">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Manual Refresh
            </button>
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
                    Floor Plan Operations
                </span>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-[#FDFBF7] mt-3">
                    Floor Plan &amp; Table Management
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    Sai Biroba Veg &amp; Non-Veg Restaurant &bull; Live Dining Layout (Room 1 &amp; Room 2)
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#242424] text-xs text-gray-300 border border-[#333333]">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                    20 Active Tables Configured
                </span>
            </div>
        </div>
    </div>

    <!-- Operational Summary KPI Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-8">
        
        <!-- Total Active Tables -->
        <div class="bg-[#1A1A1A] border border-[#2B2B2B] rounded-xl p-4">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Active</div>
            <div class="text-2xl font-display font-bold text-[#FDFBF7] mt-1">{{ $totalActive }}</div>
            <div class="text-[11px] text-[#D4AF37] font-medium mt-0.5">Room 1 &amp; Room 2</div>
        </div>

        <!-- Available Tables -->
        <div class="bg-[#1A1A1A] border border-emerald-500/30 rounded-xl p-4">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">Available</div>
            <div class="text-2xl font-display font-bold text-emerald-400 mt-1">{{ $availableCount }}</div>
            <div class="text-[11px] text-gray-400 font-medium mt-0.5">Ready for Seating</div>
        </div>

        <!-- Occupied Tables -->
        <div class="bg-[#1A1A1A] border border-amber-500/30 rounded-xl p-4">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-amber-400">Occupied</div>
            <div class="text-2xl font-display font-bold text-amber-400 mt-1">{{ $occupiedCount }}</div>
            <div class="text-[11px] text-gray-400 font-medium mt-0.5">Guests Dining</div>
        </div>

        <!-- Billed Tables -->
        <div class="bg-[#1A1A1A] border border-[#D4AF37]/30 rounded-xl p-4">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-[#D4AF37]">Billed</div>
            <div class="text-2xl font-display font-bold text-[#D4AF37] mt-1">{{ $billedCount }}</div>
            <div class="text-[11px] text-gray-400 font-medium mt-0.5">Pending Settlement</div>
        </div>

        <!-- Out of Service -->
        <div class="bg-[#1A1A1A] border border-[#2B2B2B] rounded-xl p-4 col-span-2 sm:col-span-1">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Out of Service</div>
            <div class="text-2xl font-display font-bold text-gray-400 mt-1">{{ $outOfServiceCount }}</div>
            <div class="text-[11px] text-gray-500 font-medium mt-0.5">Maintenance / Blocked</div>
        </div>

    </div>

    <!-- Status Legend -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-xl px-5 py-3 mb-8 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center space-x-2 text-gray-400 font-medium">
            <span>Status Legend:</span>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <span class="inline-flex items-center text-gray-300">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 mr-2"></span>
                Available (Ready)
            </span>
            <span class="inline-flex items-center text-gray-300">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 mr-2"></span>
                Occupied (Dining)
            </span>
            <span class="inline-flex items-center text-gray-300">
                <span class="w-2.5 h-2.5 rounded-full bg-[#D4AF37] mr-2"></span>
                Billed (Awaiting Payment)
            </span>
            <span class="inline-flex items-center text-gray-400">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-500 mr-2"></span>
                Out of Service
            </span>
            @if ($archivedCount > 0)
                <span class="inline-flex items-center text-gray-500 border-l border-[#333333] pl-4">
                    {{ $archivedCount }} Archived Legacy Tables (Hidden from Floor)
                </span>
            @endif
        </div>
    </div>

    <!-- ROOM 1 SECTION -->
    <section class="mb-10">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#262626]">
            <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full bg-[#D4AF37]"></div>
                <h2 class="font-display font-bold text-lg sm:text-xl text-[#FDFBF7]">
                    Room 1 &bull; Tables T1 to T10
                </h2>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <span class="px-2.5 py-1 rounded-md bg-[#222222] text-[#D4AF37] border border-[#333333] font-semibold">
                    {{ $room1Tables->count() }} Tables
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @forelse ($room1Tables as $table)
                @include('admin.tables._card', ['table' => $table])
            @empty
                <div class="col-span-full p-6 text-center text-gray-400 bg-[#1A1A1A] rounded-xl border border-[#2B2B2B]">
                    No tables configured for Room 1.
                </div>
            @endforelse
        </div>
    </section>

    <!-- ROOM 2 SECTION -->
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#262626]">
            <div class="flex items-center space-x-3">
                <div class="w-3 h-3 rounded-full bg-[#C5A059]"></div>
                <h2 class="font-display font-bold text-lg sm:text-xl text-[#FDFBF7]">
                    Room 2 &bull; Tables T11 to T20
                </h2>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <span class="px-2.5 py-1 rounded-md bg-[#222222] text-[#D4AF37] border border-[#333333] font-semibold">
                    {{ $room2Tables->count() }} Tables
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @forelse ($room2Tables as $table)
                @include('admin.tables._card', ['table' => $table])
            @empty
                <div class="col-span-full p-6 text-center text-gray-400 bg-[#1A1A1A] rounded-xl border border-[#2B2B2B]">
                    No tables configured for Room 2.
                </div>
            @endforelse
        </div>
    </section>

</div>
@endsection
