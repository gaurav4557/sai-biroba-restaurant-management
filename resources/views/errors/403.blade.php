@extends('layouts.app', ['title' => '403 Forbidden — Access Restricted'])

@section('content')
<div class="flex-1 flex flex-col justify-center items-center px-4 py-12 text-center">
    <div class="max-w-md w-full bg-[#1A1A1A] border border-red-500/30 rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        
        <div class="w-16 h-16 mx-auto rounded-2xl bg-red-950/40 border border-red-500/40 flex items-center justify-center text-red-400 mb-5">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <h1 class="font-display font-bold text-2xl text-[#FDFBF7]">
            403 &bull; Access Restricted
        </h1>
        
        <p class="text-sm text-gray-300 mt-2">
            You do not have permission to access this operational portal.
        </p>

        @auth
            <div class="mt-4 p-3 rounded-lg bg-[#242424] text-xs text-gray-400 border border-[#333333] text-left">
                <div><strong class="text-gray-200">Current User:</strong> {{ auth()->user()->name }}</div>
                <div class="mt-1"><strong class="text-gray-200">Assigned Role:</strong> <span class="uppercase font-semibold text-[#D4AF37]">{{ auth()->user()->role ?? 'None' }}</span></div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-2">
                @php
                    $roleRoutes = [
                        'admin' => route('admin.dashboard'),
                        'waiter' => route('waiter.dashboard'),
                        'kitchen' => route('kitchen.dashboard'),
                        'counter' => route('counter.dashboard'),
                    ];
                    $homeRoute = $roleRoutes[auth()->user()->role ?? ''] ?? route('login');
                @endphp
                <a href="{{ $homeRoute }}" 
                   class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-[#D4AF37] to-[#8C6D23] text-[#121212] font-semibold text-xs uppercase tracking-wider transition hover:brightness-110">
                    Return to Your Portal
                </a>
            </div>
        @else
            <div class="mt-6">
                <a href="{{ route('login') }}" 
                   class="inline-block py-3 px-6 rounded-xl bg-[#D4AF37] text-[#121212] font-semibold text-xs uppercase tracking-wider">
                    Sign In
                </a>
            </div>
        @endauth

    </div>
</div>
@endsection
