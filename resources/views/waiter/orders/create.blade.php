@extends('layouts.app', ['title' => 'Table ' . $visit->table->table_number . ' — Take Food Order'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 pb-32 w-full" x-data="orderApp()">

    <!-- Header: Table Info & Round Number -->
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('waiter.visits.show', $visit) }}" 
           class="inline-flex items-center text-xs font-semibold text-gray-400 hover:text-[#D4AF37] transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Visit Hub
        </a>

        <div class="flex items-center space-x-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-[#D4AF37] text-[#121212] shadow-sm">
                Round {{ $roundNumber }} KOT
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#222222] text-[#FDFBF7] border border-[#333333]">
                Table {{ $visit->table->table_number }}
            </span>
        </div>
    </div>

    <!-- Quick Ordering Banner -->
    <div class="bg-[#181818] border border-[#2B2B2B] rounded-2xl p-4 sm:p-5 mb-5 shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="text-[10px] uppercase font-semibold text-[#D4AF37] tracking-wider">
                {{ $visit->table->floor_section }} &bull; Session #{{ $visit->visit_code }}
            </div>
            <h1 class="font-display font-bold text-xl sm:text-2xl text-[#FDFBF7] mt-0.5">
                Food Menu &bull; Order Placement
            </h1>
            <p class="text-xs text-gray-400">
                Seated: {{ $visit->guest_count }} {{ Str::plural('Guest', $visit->guest_count) }}
                @if ($visit->guest_name)
                    ({{ $visit->guest_name }})
                @endif
            </p>
        </div>

        <div class="text-xs text-gray-400 bg-[#202020] px-3.5 py-2 rounded-xl border border-[#2C2C2C]">
            Server-verified pricing &bull; KOT auto-transmitted to kitchen
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="sticky top-16 z-30 bg-[#121212]/95 backdrop-blur-md pt-2 pb-4 mb-4 border-b border-[#242424] space-y-3">
        <!-- Search & Diet Filters -->
        <div class="flex flex-col sm:flex-row items-center gap-2.5">
            <!-- Search Input -->
            <div class="relative w-full sm:flex-1">
                <input type="text" 
                       id="menuSearch" 
                       oninput="filterMenuItems()" 
                       placeholder="Search dishes (e.g. Biryani, Paneer, Tandoori)..." 
                       class="w-full pl-9 pr-4 py-2.5 bg-[#1C1C1C] border border-[#333333] focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] rounded-xl text-xs text-[#FDFBF7] placeholder-gray-500 transition">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Diet Filter Pills -->
            <div class="flex items-center space-x-1.5 w-full sm:w-auto shrink-0">
                <button type="button" 
                        onclick="setDietFilter('all')" 
                        id="diet-filter-all" 
                        class="diet-pill active flex-1 sm:flex-initial px-3 py-2 rounded-xl text-xs font-semibold border transition bg-[#D4AF37] text-[#121212] border-[#D4AF37]">
                    All
                </button>
                <button type="button" 
                        onclick="setDietFilter('veg')" 
                        id="diet-filter-veg" 
                        class="diet-pill flex-1 sm:flex-initial px-3 py-2 rounded-xl text-xs font-semibold border transition bg-[#1C1C1C] text-gray-300 border-[#333333] hover:border-emerald-500 flex items-center justify-center space-x-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>Veg</span>
                </button>
                <button type="button" 
                        onclick="setDietFilter('non-veg')" 
                        id="diet-filter-non-veg" 
                        class="diet-pill flex-1 sm:flex-initial px-3 py-2 rounded-xl text-xs font-semibold border transition bg-[#1C1C1C] text-gray-300 border-[#333333] hover:border-red-500 flex items-center justify-center space-x-1">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                    <span>Non-Veg</span>
                </button>
            </div>
        </div>

        <!-- Category Horizontal Scroll Bar -->
        <div class="flex items-center space-x-2 overflow-x-auto no-scrollbar py-1">
            <button type="button" 
                    onclick="setCategoryFilter('all')" 
                    id="cat-pill-all" 
                    class="cat-pill active shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold border transition bg-[#2A2A2A] text-[#D4AF37] border-[#D4AF37]">
                All Categories ({{ $categories->sum(fn($c) => $c->menuItems->count()) }})
            </button>
            @foreach ($categories as $cat)
                @if ($cat->menuItems->isNotEmpty())
                    <button type="button" 
                            onclick="setCategoryFilter('cat-{{ $cat->id }}')" 
                            id="cat-pill-cat-{{ $cat->id }}" 
                            class="cat-pill shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold border transition bg-[#1A1A1A] text-gray-300 border-[#2D2D2D] hover:border-gray-500">
                        {{ $cat->name }} ({{ $cat->menuItems->count() }})
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Menu Items List Grouped by Category -->
    <div id="menuContainer" class="space-y-6">
        @foreach ($categories as $category)
            @if ($category->menuItems->isNotEmpty())
                <div class="category-block" id="block-cat-{{ $category->id }}">
                    <div class="flex items-center space-x-2 mb-3 pb-1 border-b border-[#252525]">
                        <h2 class="font-display font-bold text-sm sm:text-base text-[#D4AF37]">
                            {{ $category->name }}
                        </h2>
                        <span class="text-[11px] text-gray-500">({{ $category->menuItems->count() }} items)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($category->menuItems as $item)
                            <div class="menu-item-card bg-[#181818] border border-[#2B2B2B] hover:border-[#383838] rounded-xl p-3.5 flex flex-col justify-between transition"
                                 data-name="{{ strtolower($item->name) }}"
                                 data-category="cat-{{ $category->id }}"
                                 data-diet="{{ $item->food_type }}"
                                 data-item-id="{{ $item->id }}">
                                
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-start space-x-2">
                                        <!-- Diet Icon -->
                                        <div class="mt-0.5 shrink-0">
                                            @if ($item->food_type === 'veg')
                                                <div class="w-4 h-4 rounded border border-emerald-500 flex items-center justify-center p-0.5" title="Vegetarian">
                                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                </div>
                                            @else
                                                <div class="w-4 h-4 rounded border border-red-500 flex items-center justify-center p-0.5" title="Non-Vegetarian">
                                                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <h3 class="font-semibold text-sm text-[#FDFBF7] leading-tight">
                                                {{ $item->name }}
                                            </h3>
                                            @if ($item->description)
                                                <p class="text-[11px] text-gray-400 mt-0.5 line-clamp-2 leading-relaxed">
                                                    {{ $item->description }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <div class="text-sm font-bold font-display text-emerald-400">
                                            ₹{{ number_format($item->price, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Stepper & Special Instructions Button -->
                                <div class="mt-3 pt-2.5 border-t border-[#222222] flex items-center justify-between">
                                    <button type="button" 
                                            onclick="openInstructionPrompt({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                            class="text-[11px] text-gray-400 hover:text-[#D4AF37] transition flex items-center space-x-1"
                                            id="note-btn-{{ $item->id }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span id="note-label-{{ $item->id }}">Special request</span>
                                    </button>

                                    <!-- Stepper Controls -->
                                    <div class="flex items-center space-x-2">
                                        <button type="button" 
                                                onclick="updateCartItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, -1)"
                                                class="w-7 h-7 rounded-lg bg-[#242424] hover:bg-[#2F2F2F] active:scale-95 text-[#FDFBF7] border border-[#333333] flex items-center justify-center font-bold text-sm transition">
                                            -
                                        </button>
                                        <span id="qty-badge-{{ $item->id }}" 
                                              class="w-6 text-center text-xs font-bold font-display text-[#FDFBF7]">
                                            0
                                        </span>
                                        <button type="button" 
                                                onclick="updateCartItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, 1)"
                                                class="w-7 h-7 rounded-lg bg-[#D4AF37] hover:bg-[#E5C158] active:scale-95 text-[#121212] flex items-center justify-center font-bold text-sm transition">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Sticky Bottom Cart Bar -->
    <div id="stickyCartBar" class="fixed bottom-0 left-0 right-0 z-40 bg-[#161616]/98 backdrop-blur-lg border-t border-[#2C2C2C] shadow-2xl p-4 hidden">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">
                    <span id="cartCountBadge">0 Items</span> &bull; Round {{ $roundNumber }}
                </div>
                <div class="text-xl font-bold font-display text-[#D4AF37]">
                    ₹<span id="cartTotalBadge">0.00</span>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button type="button" 
                        onclick="toggleCartModal(true)" 
                        class="px-4 py-2.5 rounded-xl bg-[#262626] hover:bg-[#303030] text-xs font-semibold text-[#FDFBF7] border border-[#383838] transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Review KOT</span>
                </button>

                <button type="button" 
                        onclick="toggleCartModal(true)" 
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#D4AF37] to-[#C5A059] hover:from-[#E5C158] hover:to-[#D4AF37] active:scale-95 text-[#121212] font-bold text-xs uppercase tracking-wider transition shadow-lg flex items-center space-x-1.5">
                    <span>Send to Kitchen &rarr;</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Review & Submit Cart Modal / Drawer -->
    <div id="cartModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-[#181818] border border-[#2E2E2E] w-full max-w-xl rounded-t-3xl sm:rounded-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-in fade-in duration-200">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-[#262626] flex items-center justify-between">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30">
                        Round {{ $roundNumber }} KOT Preview
                    </span>
                    <h3 class="font-display font-bold text-lg text-[#FDFBF7] mt-1">
                        Table {{ $visit->table->table_number }} &bull; Review Items
                    </h3>
                </div>
                <button type="button" 
                        onclick="toggleCartModal(false)" 
                        class="w-8 h-8 rounded-full bg-[#242424] hover:bg-[#2C2C2C] text-gray-400 hover:text-white flex items-center justify-center transition">
                    &times;
                </button>
            </div>

            <!-- Modal Content (Cart Items & Notes) -->
            <form id="orderForm" method="POST" action="{{ route('waiter.orders.store', $visit) }}" class="flex-1 flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="idempotency_token" id="idempotency_token" value="{{ Str::uuid()->toString() }}">

                <!-- Scrollable Item List -->
                <div id="cartReviewList" class="p-5 overflow-y-auto divide-y divide-[#242424] flex-1 space-y-3">
                    <!-- Populated dynamically via JS -->
                </div>

                <!-- Order / Kitchen Notes -->
                <div class="px-5 py-3 bg-[#1C1C1C] border-t border-[#262626]">
                    <label for="order_notes" class="block text-[11px] uppercase font-semibold text-gray-300 mb-1">
                        Kitchen Order Notes <span class="text-gray-500 lowercase">(optional)</span>
                    </label>
                    <input type="text" 
                           id="order_notes" 
                           name="order_notes" 
                           placeholder="e.g. Serve drinks first, less spicy overall, urgent starter" 
                           maxlength="255" 
                           class="w-full px-3 py-2 bg-[#222222] border border-[#333333] focus:border-[#D4AF37] rounded-xl text-xs text-[#FDFBF7] placeholder-gray-500 transition">
                </div>

                <!-- Modal Footer -->
                <div class="p-5 border-t border-[#262626] bg-[#161616] flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div>
                        <div class="text-[10px] uppercase text-gray-400 font-semibold">Estimated KOT Subtotal</div>
                        <div class="text-xl font-bold font-display text-emerald-400">
                            ₹<span id="modalTotalBadge">0.00</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 w-full sm:w-auto">
                        <button type="button" 
                                onclick="toggleCartModal(false)" 
                                class="flex-1 sm:flex-initial px-4 py-3 rounded-xl bg-[#242424] hover:bg-[#2C2C2C] text-xs font-semibold text-gray-300 border border-[#333333] transition">
                            Modify Items
                        </button>
                        <button type="submit" 
                                id="submitKotBtn"
                                class="flex-1 sm:flex-initial px-6 py-3 rounded-xl bg-gradient-to-r from-[#D4AF37] to-[#C5A059] hover:from-[#E5C158] hover:to-[#D4AF37] active:scale-95 text-[#121212] font-bold text-xs uppercase tracking-wider transition shadow-lg flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 text-[#121212]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Transmit KOT to Kitchen</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    // In-memory Cart State: { [itemId]: { id, name, price, qty, instructions } }
    const cart = {};
    let activeDiet = 'all';
    let activeCategory = 'all';

    function updateCartItem(id, name, price, delta) {
        if (!cart[id]) {
            cart[id] = { id: id, name: name, price: price, qty: 0, instructions: '' };
        }
        cart[id].qty = Math.max(0, Math.min(50, cart[id].qty + delta));

        if (cart[id].qty === 0 && !cart[id].instructions) {
            delete cart[id];
        }

        renderCartState();
    }

    function openInstructionPrompt(id, name) {
        const current = cart[id]?.instructions || '';
        const instruction = prompt(`Special instructions for ${name}:`, current);
        if (instruction !== null) {
            if (!cart[id]) {
                // If not in cart yet, add 1 with this instruction
                const card = document.querySelector(`[data-item-id="${id}"]`);
                const price = card ? parseFloat(card.querySelector('.text-emerald-400').textContent.replace(/[^\d.]/g, '')) : 0;
                cart[id] = { id: id, name: name, price: price, qty: 1, instructions: instruction.trim() };
            } else {
                cart[id].instructions = instruction.trim();
            }
            renderCartState();
        }
    }

    function renderCartState() {
        let totalCount = 0;
        let totalPrice = 0.0;

        // Reset badge counters on cards
        document.querySelectorAll('[id^="qty-badge-"]').forEach(el => el.textContent = '0');
        document.querySelectorAll('[id^="note-label-"]').forEach(el => el.textContent = 'Special request');

        // Update active cart counts
        Object.values(cart).forEach(item => {
            if (item.qty > 0) {
                totalCount += item.qty;
                totalPrice += (item.qty * item.price);

                const badge = document.getElementById(`qty-badge-${item.id}`);
                if (badge) badge.textContent = item.qty;

                const noteLabel = document.getElementById(`note-label-${item.id}`);
                if (noteLabel && item.instructions) {
                    noteLabel.textContent = item.instructions.length > 15 ? item.instructions.substring(0, 15) + '...' : item.instructions;
                    noteLabel.classList.add('text-[#D4AF37]', 'font-semibold');
                }
            }
        });

        // Update bottom bar
        const stickyBar = document.getElementById('stickyCartBar');
        if (totalCount > 0) {
            stickyBar.classList.remove('hidden');
            document.getElementById('cartCountBadge').textContent = `${totalCount} ${totalCount === 1 ? 'Item' : 'Items'}`;
            document.getElementById('cartTotalBadge').textContent = totalPrice.toFixed(2);
            document.getElementById('modalTotalBadge').textContent = totalPrice.toFixed(2);
        } else {
            stickyBar.classList.add('hidden');
        }

        renderCartReviewList();
    }

    function renderCartReviewList() {
        const reviewContainer = document.getElementById('cartReviewList');
        if (!reviewContainer) return;

        reviewContainer.innerHTML = '';
        let index = 0;

        const activeItems = Object.values(cart).filter(i => i.qty > 0);

        if (activeItems.length === 0) {
            reviewContainer.innerHTML = `<p class="text-xs text-gray-400 text-center py-6">Your cart is empty. Select menu items to transmit.</p>`;
            return;
        }

        activeItems.forEach(item => {
            const row = document.createElement('div');
            row.className = 'py-3 flex flex-col space-y-2';

            row.innerHTML = `
                <input type="hidden" name="items[${index}][menu_item_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
                <input type="hidden" name="items[${index}][special_instructions]" value="${item.instructions || ''}">

                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-[#FDFBF7]">${item.name}</div>
                        <div class="text-xs text-gray-400">₹${item.price.toFixed(2)} &times; ${item.qty}</div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-sm font-bold font-display text-emerald-400">
                            ₹${(item.qty * item.price).toFixed(2)}
                        </div>
                        <div class="flex items-center space-x-1.5 bg-[#222222] px-2 py-1 rounded-lg border border-[#303030]">
                            <button type="button" onclick="updateCartItem(${item.id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, -1)" class="w-5 h-5 text-gray-300 hover:text-white font-bold text-xs">-</button>
                            <span class="text-xs font-bold text-[#FDFBF7] w-4 text-center">${item.qty}</span>
                            <button type="button" onclick="updateCartItem(${item.id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, 1)" class="w-5 h-5 text-[#D4AF37] hover:text-white font-bold text-xs">+</button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <input type="text" 
                           placeholder="Item instructions (e.g. less oil, extra crispy)..." 
                           value="${item.instructions || ''}" 
                           onchange="updateItemInstruction(${item.id}, this.value)"
                           class="w-full px-2.5 py-1 text-xs bg-[#242424] border border-[#333333] focus:border-[#D4AF37] rounded-lg text-gray-200 placeholder-gray-500">
                </div>
            `;

            reviewContainer.appendChild(row);
            index++;
        });
    }

    function updateItemInstruction(id, value) {
        if (cart[id]) {
            cart[id].instructions = value.trim();
            renderCartState();
        }
    }

    function toggleCartModal(show) {
        const modal = document.getElementById('cartModal');
        if (show) {
            renderCartReviewList();
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }

    // Filter Logic
    function setDietFilter(diet) {
        activeDiet = diet;
        document.querySelectorAll('.diet-pill').forEach(btn => {
            btn.classList.remove('bg-[#D4AF37]', 'text-[#121212]', 'border-[#D4AF37]');
            btn.classList.add('bg-[#1C1C1C]', 'text-gray-300', 'border-[#333333]');
        });

        const activeBtn = document.getElementById(`diet-filter-${diet}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-[#1C1C1C]', 'text-gray-300', 'border-[#333333]');
            activeBtn.classList.add('bg-[#D4AF37]', 'text-[#121212]', 'border-[#D4AF37]');
        }

        filterMenuItems();
    }

    function setCategoryFilter(category) {
        activeCategory = category;
        document.querySelectorAll('.cat-pill').forEach(btn => {
            btn.classList.remove('bg-[#2A2A2A]', 'text-[#D4AF37]', 'border-[#D4AF37]');
            btn.classList.add('bg-[#1A1A1A]', 'text-gray-300', 'border-[#2D2D2D]');
        });

        const activeBtn = document.getElementById(`cat-pill-${category}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-[#1A1A1A]', 'text-gray-300', 'border-[#2D2D2D]');
            activeBtn.classList.add('bg-[#2A2A2A]', 'text-[#D4AF37]', 'border-[#D4AF37]');
        }

        filterMenuItems();
    }

    function filterMenuItems() {
        const query = (document.getElementById('menuSearch')?.value || '').toLowerCase().trim();

        document.querySelectorAll('.category-block').forEach(block => {
            let visibleInBlock = 0;
            const blockCatId = block.id.replace('block-', '');

            // Category filter check
            const catMatch = (activeCategory === 'all' || activeCategory === blockCatId);

            block.querySelectorAll('.menu-item-card').forEach(card => {
                const name = card.dataset.name || '';
                const diet = card.dataset.diet || '';

                const searchMatch = !query || name.includes(query);
                const dietMatch = (activeDiet === 'all' || diet === activeDiet);

                if (catMatch && searchMatch && dietMatch) {
                    card.classList.remove('hidden');
                    visibleInBlock++;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (visibleInBlock > 0) {
                block.classList.remove('hidden');
            } else {
                block.classList.add('hidden');
            }
        });
    }

    // Safeguard double submission
    document.getElementById('orderForm')?.addEventListener('submit', function (e) {
        const activeItems = Object.values(cart).filter(i => i.qty > 0);
        if (activeItems.length === 0) {
            e.preventDefault();
            alert('Please add at least one item before submitting the order.');
            return false;
        }

        const btn = document.getElementById('submitKotBtn');
        btn.disabled = true;
        btn.innerHTML = `<span class="inline-block animate-spin mr-2">&#9696;</span> Transmitting KOT...`;
    });
</script>
@endsection
