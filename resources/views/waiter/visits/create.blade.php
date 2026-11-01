@extends('layouts.app', ['title' => 'Seat Guests & Start Visit'])

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 py-6 w-full">

    <!-- Back to Dashboard Link -->
    <div class="mb-5">
        <a href="{{ route('waiter.dashboard') }}" 
           class="inline-flex items-center text-xs font-semibold text-gray-400 hover:text-[#D4AF37] transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Waiter Dashboard
        </a>
    </div>

    <!-- Seating Card -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl shadow-xl overflow-hidden relative">
        <div class="absolute top-0 right-0 w-48 h-48 bg-[#D4AF37]/5 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Table Information Header -->
        <div class="p-6 border-b border-[#262626] relative z-10 flex items-center justify-between">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                    {{ $table->floor_section }} &bull; Available
                </span>
                <h1 class="font-display font-bold text-2xl text-[#FDFBF7] mt-2">
                    Table {{ $table->table_number }}
                </h1>
                <p class="text-xs text-gray-400 mt-0.5">
                    Maximum Capacity: <span class="text-[#D4AF37] font-semibold">{{ $table->seating_capacity }} Guests</span>
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-[#222222] border border-[#333333] flex flex-col items-center justify-center text-[#D4AF37] shadow-inner">
                <span class="text-[10px] uppercase font-semibold text-gray-400">Cap</span>
                <span class="font-display font-bold text-xl leading-none text-[#FDFBF7]">{{ $table->seating_capacity }}</span>
            </div>
        </div>

        <!-- Seating Form -->
        <form method="POST" action="{{ route('waiter.visits.store', $table) }}" class="p-6 space-y-5 relative z-10">
            @csrf

            <!-- Guest Count -->
            <div>
                <label for="guest_count" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">
                    Guest Count <span class="text-red-400">*</span>
                    <span class="text-[11px] font-normal text-gray-400 lowercase">(1 to {{ $table->seating_capacity }} seats)</span>
                </label>

                <!-- Guest Count Stepper -->
                <div class="flex items-center space-x-3">
                    <button type="button" 
                            onclick="adjustGuests(-1, {{ $table->seating_capacity }})"
                            class="w-12 h-12 rounded-xl bg-[#242424] hover:bg-[#2C2C2C] active:scale-95 text-[#FDFBF7] border border-[#333333] flex items-center justify-center text-xl font-bold transition">
                        -
                    </button>
                    <input type="number" 
                           id="guest_count" 
                           name="guest_count" 
                           min="1" 
                           max="{{ $table->seating_capacity }}" 
                           value="{{ old('guest_count', 2) }}" 
                           required 
                           class="w-full h-12 text-center text-xl font-bold font-display bg-[#1F1F1F] border border-[#383838] focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] rounded-xl text-[#FDFBF7] transition">
                    <button type="button" 
                            onclick="adjustGuests(1, {{ $table->seating_capacity }})"
                            class="w-12 h-12 rounded-xl bg-[#242424] hover:bg-[#2C2C2C] active:scale-95 text-[#FDFBF7] border border-[#333333] flex items-center justify-center text-xl font-bold transition">
                        +
                    </button>
                </div>

                <!-- Quick Presets -->
                <div class="flex items-center space-x-2 mt-2.5">
                    <span class="text-[11px] text-gray-400">Quick set:</span>
                    @for ($i = 1; $i <= min(6, $table->seating_capacity); $i++)
                        <button type="button" 
                                onclick="setGuests({{ $i }})"
                                class="px-2.5 py-1 rounded-lg bg-[#222222] hover:bg-[#2C2C2C] text-xs font-semibold text-gray-300 hover:text-[#D4AF37] border border-[#303030] transition">
                            {{ $i }}
                        </button>
                    @endfor
                </div>

                @error('guest_count')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Guest Name (Optional) -->
            <div>
                <label for="guest_name" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-1.5">
                    Customer / Party Name <span class="text-[11px] font-normal text-gray-400 lowercase">(optional)</span>
                </label>
                <input type="text" 
                       id="guest_name" 
                       name="guest_name" 
                       value="{{ old('guest_name') }}" 
                       placeholder="e.g. Deshmukh Family, Dr. Patil" 
                       maxlength="100" 
                       class="w-full px-4 py-2.5 bg-[#1F1F1F] border border-[#383838] focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] rounded-xl text-sm text-[#FDFBF7] placeholder-gray-500 transition">
                @error('guest_name')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dining Notes (Optional) -->
            <div>
                <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-1.5">
                    Session Notes <span class="text-[11px] font-normal text-gray-400 lowercase">(optional)</span>
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="2" 
                          placeholder="e.g. High chair needed, anniversary dinner, preference for less spicy" 
                          maxlength="255" 
                          class="w-full px-4 py-2 bg-[#1F1F1F] border border-[#383838] focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] rounded-xl text-xs text-[#FDFBF7] placeholder-gray-500 transition">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-[#D4AF37] to-[#C5A059] hover:from-[#E5C158] hover:to-[#D4AF37] active:scale-[0.99] text-[#121212] font-semibold text-sm transition shadow-lg flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4 text-[#121212]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Confirm Seating &amp; Open Table Visit</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function adjustGuests(delta, maxCapacity) {
        const input = document.getElementById('guest_count');
        let current = parseInt(input.value) || 1;
        let next = current + delta;
        if (next >= 1 && next <= maxCapacity) {
            input.value = next;
        }
    }

    function setGuests(val) {
        document.getElementById('guest_count').value = val;
    }
</script>
@endsection
