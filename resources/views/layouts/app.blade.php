<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#121212] text-[#FDFBF7]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Staff Portal' }} — Sai Biroba Restaurant</title>

    <!-- Google Fonts: Instrument Sans & Cinzel (Royal Display) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vite bundle with fallback styling) -->
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            gold: {
                                300: '#F3E5AB',
                                400: '#E5C158',
                                500: '#D4AF37',
                                600: '#C5A059',
                                700: '#997A2E',
                            },
                            charcoal: {
                                800: '#222222',
                                900: '#181818',
                                950: '#121212',
                            },
                            cream: {
                                50: '#FAF8F5',
                                100: '#F5F0E8',
                                200: '#EBE2D5',
                            }
                        },
                        fontFamily: {
                            sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            display: ['Cinzel', 'Georgia', 'serif'],
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        body {
            font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
            background-color: #121212;
            color: #FDFBF7;
        }
        .font-display {
            font-family: 'Cinzel', Georgia, serif;
        }
        .gold-gradient-text {
            background: linear-gradient(135deg, #F3E5AB 0%, #D4AF37 50%, #C5A059 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .gold-border-gradient {
            border-image: linear-gradient(to right, #D4AF37, #66501E) 1;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#121212] selection:bg-[#D4AF37] selection:text-[#121212]">

    @auth
    <!-- Staff Header Navigation -->
    <header class="sticky top-0 z-50 bg-[#181818]/95 backdrop-blur-md border-b border-[#2C2C2C] shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand & Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#D4AF37] to-[#8C6D23] flex items-center justify-center text-[#121212] font-display font-bold text-lg shadow-md">
                        SB
                    </div>
                    <div>
                        <div class="font-display font-bold text-base sm:text-lg tracking-wider text-[#FDFBF7]">
                            SAI BIROBA
                        </div>
                        <div class="text-[10px] tracking-widest uppercase text-[#D4AF37] font-semibold">
                            Veg & Non-Veg Restaurant
                        </div>
                    </div>
                </div>

                <!-- Admin Center Navigation (Admin Role Only) -->
                @if (auth()->check() && auth()->user()->role === 'admin')
                <nav class="hidden sm:flex items-center space-x-1 lg:space-x-2">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#242424] text-[#D4AF37] border border-[#D4AF37]/40 shadow-sm' : 'text-gray-300 hover:text-[#D4AF37] hover:bg-[#202020]' }}">
                        Overview
                    </a>
                    <a href="{{ route('admin.tables.index') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.tables.*') ? 'bg-[#242424] text-[#D4AF37] border border-[#D4AF37]/40 shadow-sm' : 'text-gray-300 hover:text-[#D4AF37] hover:bg-[#202020]' }}">
                        Floor &amp; Tables
                    </a>
                    <a href="{{ route('admin.assignments.index') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.assignments.*') ? 'bg-[#242424] text-[#D4AF37] border border-[#D4AF37]/40 shadow-sm' : 'text-gray-300 hover:text-[#D4AF37] hover:bg-[#202020]' }}">
                        Assignments
                    </a>
                    <a href="{{ route('reports.index') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-[#242424] text-[#D4AF37] border border-[#D4AF37]/40 shadow-sm' : 'text-gray-300 hover:text-[#D4AF37] hover:bg-[#202020]' }}">
                        Reports
                    </a>
                </nav>
                @endif

                <!-- Counter Center Navigation (Counter Role Only) -->
                @if (auth()->check() && auth()->user()->role === 'counter')
                <nav class="hidden sm:flex items-center space-x-1 lg:space-x-2">
                    <a href="{{ route('counter.dashboard') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('counter.dashboard') ? 'bg-[#242424] text-[#D4AF37] border border-[#D4AF37]/40 shadow-sm' : 'text-gray-300 hover:text-[#D4AF37] hover:bg-[#202020]' }}">
                        POS Console
                    </a>
                    <a href="{{ route('reports.index') }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-[#242424] text-[#D4AF37] border border-[#D4AF37]/40 shadow-sm' : 'text-gray-300 hover:text-[#D4AF37] hover:bg-[#202020]' }}">
                        Reports
                    </a>
                </nav>
                @endif

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Role Badge -->
                    @php
                        $roleColors = [
                            'admin' => 'bg-[#D4AF37]/20 text-[#D4AF37] border-[#D4AF37]/40',
                            'waiter' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40',
                            'kitchen' => 'bg-amber-500/20 text-amber-400 border-amber-500/40',
                            'counter' => 'bg-sky-500/20 text-sky-400 border-sky-500/40',
                        ];
                        $badgeClass = $roleColors[auth()->user()->role ?? ''] ?? 'bg-gray-700 text-gray-300 border-gray-600';
                    @endphp
                    
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-semibold text-[#FDFBF7]">{{ auth()->user()->name }}</span>
                        <span class="text-[11px] text-gray-400 font-mono">{{ auth()->user()->phone ?? auth()->user()->email }}</span>
                    </div>

                    <span class="px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider rounded-full border {{ $badgeClass }}">
                        {{ auth()->user()->role }}
                    </span>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Confirm staff logout?')"
                                title="Sign Out" 
                                class="p-2 rounded-lg bg-[#242424] hover:bg-[#2F2F2F] text-gray-300 hover:text-[#D4AF37] border border-[#333333] transition duration-150">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>
    @endauth

    <!-- Flash Messages / Notifications -->
    @if (session('status') || session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-sm flex items-center justify-between shadow-lg">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') ?? session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl bg-red-950/80 border border-red-500/40 text-red-200 text-sm flex items-center justify-between shadow-lg">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl bg-sky-950/80 border border-sky-500/40 text-sky-200 text-sm flex items-center justify-between shadow-lg">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-lg bg-red-950/80 border border-red-500/40 text-red-200 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#121212] border-t border-[#222222] py-4 text-center text-xs text-gray-500">
        <p>&copy; {{ date('Y') }} Sai Biroba Restaurant Management System &bull; Confidential Staff Portal</p>
    </footer>

    @stack('scripts')
</body>
</html>
