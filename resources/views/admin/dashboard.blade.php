@extends('layouts.app', ['title' => 'Admin Executive Dashboard'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Executive Header Banner -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 sm:p-8 mb-8 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#D4AF37]/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30">
                    Administrator Control Center
                </span>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-[#FDFBF7] mt-3">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="text-sm text-gray-400 mt-1">
                    Sai Biroba Veg &amp; Non-Veg Restaurant &bull; System Administration &amp; Operations
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.tables.index') }}" 
                   class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-[#D4AF37]/15 hover:bg-[#D4AF37]/25 text-[#D4AF37] border border-[#D4AF37]/30 text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Floor Plan
                </a>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#242424] text-xs text-gray-300 border border-[#333333]">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                    System Active
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Operational KPI Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        
        <a href="{{ route('admin.tables.index') }}" class="bg-[#1A1A1A] border border-[#2B2B2B] rounded-xl p-5 hover:border-[#D4AF37]/60 hover:bg-[#1E1E1E] transition group block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 group-hover:text-gray-300">Total Tables</span>
                <span class="text-xs text-[#D4AF37] opacity-0 group-hover:opacity-100 transition">&rarr;</span>
            </div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-[#FDFBF7] mt-2">{{ $totalTables }}</div>
            <div class="text-[11px] text-[#D4AF37] mt-1 font-medium">Room 1 &bull; Room 2 Active Layout</div>
        </a>

        <div class="bg-[#1A1A1A] border border-[#2B2B2B] rounded-xl p-5 hover:border-[#D4AF37]/50 transition">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active Staff</div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-[#FDFBF7] mt-2">{{ $totalStaff }}</div>
            <div class="text-[11px] text-emerald-400 mt-1 font-medium">Waiters, Kitchen &amp; Counter</div>
        </div>

        <div class="bg-[#1A1A1A] border border-[#2B2B2B] rounded-xl p-5 hover:border-[#D4AF37]/50 transition">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Menu Dishes</div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-[#FDFBF7] mt-2">{{ $totalMenuItems }}</div>
            <div class="text-[11px] text-sky-400 mt-1 font-medium">Available Menu Catalog</div>
        </div>

        <div class="bg-[#1A1A1A] border border-[#2B2B2B] rounded-xl p-5 hover:border-[#D4AF37]/50 transition">
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Live Dining Sessions</div>
            <div class="text-2xl sm:text-3xl font-display font-bold text-[#FDFBF7] mt-2">{{ $activeVisits }}</div>
            <div class="text-[11px] text-amber-400 mt-1 font-medium">Occupied / Billed Tables</div>
        </div>

    </div>

    <!-- Operational Modules Overview -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-6 sm:p-8">
        <h2 class="font-display font-bold text-lg sm:text-xl text-[#FDFBF7] mb-4">
            Management Navigation
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.tables.index') }}" class="p-4 rounded-xl bg-[#222222] border border-[#333333] hover:border-[#D4AF37]/50 hover:bg-[#262626] flex flex-col justify-between transition group">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-sm text-[#FDFBF7] group-hover:text-[#D4AF37] transition">Tables &amp; Floor Sections</h3>
                        <span class="text-xs text-[#D4AF37] opacity-0 group-hover:opacity-100 transition">&rarr;</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">View Room 1 and Room 2 dining tables, capacity and live statuses.</p>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[11px] text-[#D4AF37] font-semibold">20 Active Tables</span>
                    <span class="text-[11px] text-gray-400 group-hover:text-[#D4AF37] transition">View Floor Plan &rarr;</span>
                </div>
            </a>

            <a href="{{ route('admin.assignments.index') }}" class="p-4 rounded-xl bg-[#222222] border border-[#333333] hover:border-[#D4AF37]/50 hover:bg-[#262626] flex flex-col justify-between transition group">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-sm text-[#FDFBF7] group-hover:text-[#D4AF37] transition">Waiter Assignments</h3>
                        <span class="text-xs text-[#D4AF37] opacity-0 group-hover:opacity-100 transition">&rarr;</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Assign active waiters to dining tables with 1-waiter constraint.</p>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[11px] text-[#D4AF37] font-semibold">Active Shift Allocation</span>
                    <span class="text-[11px] text-gray-400 group-hover:text-[#D4AF37] transition">Manage &rarr;</span>
                </div>
            </a>

            <div class="p-4 rounded-xl bg-[#222222] border border-[#333333] flex flex-col justify-between">
                <div>
                    <h3 class="font-semibold text-sm text-[#FDFBF7]">Menu &amp; Categories</h3>
                    <p class="text-xs text-gray-400 mt-1">Manage dishes, prices, veg/non-veg tags and availability.</p>
                </div>
                <span class="mt-4 text-[11px] text-[#D4AF37] font-semibold">Configured &amp; Migrated</span>
            </div>

            <a href="{{ route('reports.index') }}" class="p-4 rounded-xl bg-[#222222] border border-[#333333] hover:border-[#D4AF37]/50 transition group flex flex-col justify-between">
                <div>
                    <h3 class="font-semibold text-sm text-[#FDFBF7] group-hover:text-[#D4AF37] transition">Financial Reports &amp; Analytics</h3>
                    <p class="text-xs text-gray-400 mt-1">Collections, daily/monthly sales, payment modes &amp; best sellers.</p>
                </div>
                <span class="mt-4 text-[11px] text-[#D4AF37] font-semibold flex items-center gap-1">
                    Open Reports Dashboard &rarr;
                </span>
            </a>
        </div>
    </div>

</div>
@endsection
