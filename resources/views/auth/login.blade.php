@extends('layouts.app', ['title' => 'Staff Login'])

@section('content')
<div class="flex-1 flex flex-col justify-center items-center px-4 py-8 sm:px-6 lg:px-8">
    
    <div class="w-full max-w-md">
        
        <!-- Brand Crest & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[#D4AF37] via-[#C5A059] to-[#8C6D23] text-[#121212] font-display font-bold text-2xl shadow-xl shadow-[#D4AF37]/10 mb-4 border border-[#F3E5AB]/40">
                SB
            </div>
            <h1 class="font-display font-bold text-2xl sm:text-3xl tracking-wide gold-gradient-text uppercase">
                Sai Biroba
            </h1>
            <p class="text-xs uppercase tracking-widest text-[#D4AF37] font-semibold mt-1">
                Veg &amp; Non-Veg Restaurant
            </p>
            <p class="text-xs text-gray-400 mt-2">
                Authorized Dine-In Staff Access Only
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-[#1A1A1A] border border-[#2D2D2D] rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-sm relative overflow-hidden">
            <!-- Decorative Gold Accent Line -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#D4AF37] to-transparent"></div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Phone / Username Field -->
                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-gray-300 mb-2">
                        Mobile Phone Number
                    </label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#D4AF37]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input id="phone" 
                               name="phone" 
                               type="tel" 
                               inputmode="tel"
                               autocomplete="tel" 
                               required 
                               value="{{ old('phone') }}"
                               placeholder="e.g. +91XXXXXXXXXX"
                               class="block w-full pl-11 pr-4 py-3 bg-[#121212] border border-[#333333] rounded-xl text-[#FDFBF7] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-sm font-mono tracking-wide transition duration-150">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-300">
                            Password
                        </label>
                    </div>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#D4AF37]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               autocomplete="current-password" 
                               required 
                               placeholder="••••••••••••"
                               class="block w-full pl-11 pr-11 py-3 bg-[#121212] border border-[#333333] rounded-xl text-[#FDFBF7] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:border-[#D4AF37] text-sm tracking-wide transition duration-150">
                        
                        <button type="button" 
                                id="togglePasswordBtn"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-[#D4AF37] transition">
                            <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Toggle -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2.5 cursor-pointer">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember"
                               class="w-4 h-4 rounded bg-[#121212] border-[#333333] text-[#D4AF37] focus:ring-[#D4AF37] focus:ring-offset-0">
                        <span class="text-xs text-gray-400 select-none">Remember this device</span>
                    </label>
                    <span class="text-[11px] text-gray-500">Internal Use Only</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-[#D4AF37] via-[#C5A059] to-[#8C6D23] hover:from-[#E5C158] hover:to-[#A3812E] text-[#121212] font-semibold text-sm tracking-wider uppercase shadow-lg shadow-[#D4AF37]/20 transition duration-150 transform active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:ring-offset-2 focus:ring-offset-[#121212]">
                    Authenticate &amp; Enter Portal
                </button>
            </form>

        </div>

        <!-- Demonstration Quick Fill Accordion (For Local Demonstration) -->
        <div class="mt-6 bg-[#161616] border border-[#262626] rounded-xl p-4 text-xs">
            <details class="cursor-pointer group">
                <summary class="font-semibold text-gray-400 group-hover:text-[#D4AF37] flex items-center justify-between select-none">
                    <span>Demo Role Credentials (Phase 2 Testing)</span>
                    <svg class="w-4 h-4 transition-transform group-open:rotate-180 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                
                <div class="mt-3 pt-3 border-t border-[#222222] space-y-2 text-gray-400">
                    <p class="text-[11px] text-gray-500 mb-2">Click any role button below to prefill test credentials:</p>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" 
                                onclick="fillCredentials('+910000000001', '{{ env('STAFF_PASSWORD', '') }}')" 
                                class="p-2 rounded bg-[#222222] hover:bg-[#2A2A2A] text-left border border-[#333333] hover:border-emerald-500/50 transition">
                            <div class="font-semibold text-emerald-400">Waiter 1</div>
                            <div class="text-[10px] text-gray-500 font-mono">+910000000001</div>
                        </button>

                        <button type="button" 
                                onclick="fillCredentials('+910000000002', '{{ env('STAFF_PASSWORD', '') }}')" 
                                class="p-2 rounded bg-[#222222] hover:bg-[#2A2A2A] text-left border border-[#333333] hover:border-emerald-500/50 transition">
                            <div class="font-semibold text-emerald-400">Waiter 2</div>
                            <div class="text-[10px] text-gray-500 font-mono">+910000000002</div>
                        </button>

                        <button type="button" 
                                onclick="fillCredentials('+910000000003', '{{ env('STAFF_PASSWORD', '') }}')" 
                                class="p-2 rounded bg-[#222222] hover:bg-[#2A2A2A] text-left border border-[#333333] hover:border-amber-500/50 transition">
                            <div class="font-semibold text-amber-400">Kitchen Staff</div>
                            <div class="text-[10px] text-gray-500 font-mono">+910000000003</div>
                        </button>

                        <button type="button" 
                                onclick="fillCredentials('+910000000004', '{{ env('STAFF_PASSWORD', '') }}')" 
                                class="p-2 rounded bg-[#222222] hover:bg-[#2A2A2A] text-left border border-[#333333] hover:border-sky-500/50 transition">
                            <div class="font-semibold text-sky-400">Counter Cashier</div>
                            <div class="text-[10px] text-gray-500 font-mono">+910000000004</div>
                        </button>
                    </div>

                    <div class="mt-2 pt-2 border-t border-[#222222]">
                        <button type="button" 
                                onclick="fillCredentials('{{ env('ADMIN_PHONE', '') }}', '{{ env('ADMIN_PASSWORD', '') }}')" 
                                class="w-full p-2 rounded bg-[#222222] hover:bg-[#2A2A2A] text-left border border-[#333333] hover:border-[#D4AF37]/50 transition flex items-center justify-between">
                            <div>
                                <span class="font-semibold text-[#D4AF37]">Admin Account</span>
                                <span class="text-[10px] text-gray-500 font-mono ml-2">({{ env('ADMIN_PHONE', 'Defined in .env') }})</span>
                            </div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider">Fill Admin</span>
                        </button>
                    </div>
                </div>
            </details>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    // Password visibility toggle
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        });
    }

    // Helper to auto-fill demo credentials
    function fillCredentials(phone, password) {
        const phoneInput = document.getElementById('phone');
        const passInput = document.getElementById('password');
        if (phoneInput) phoneInput.value = phone;
        if (passInput && password) passInput.value = password;
        if (passInput) passInput.focus();
    }
</script>
@endpush
