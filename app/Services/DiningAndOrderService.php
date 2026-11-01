<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use App\Models\TableWaiterAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DiningAndOrderService
{
    /**
     * Start a new dining session (table visit) on an assigned available table.
     *
     * @param RestaurantTable|int $table
     * @param User|int $waiter
     * @param array<string, mixed>|int $dataOrGuestCount
     * @throws ValidationException
     */
    public function startVisit(
        RestaurantTable|int $table,
        User|int $waiter,
        array|int $dataOrGuestCount,
        ?string $guestName = null,
        ?string $notes = null
    ): TableVisit {
        $tableId = ($table instanceof RestaurantTable) ? $table->id : (int) $table;
        $waiterId = ($waiter instanceof User) ? $waiter->id : (int) $waiter;

        if (is_array($dataOrGuestCount)) {
            $guestCount = (int) ($dataOrGuestCount['guest_count'] ?? 1);
            $guestName = $dataOrGuestCount['guest_name'] ?? null;
            $notes = $dataOrGuestCount['notes'] ?? null;
        } else {
            $guestCount = (int) $dataOrGuestCount;
        }

        return DB::transaction(function () use ($tableId, $waiterId, $guestCount, $guestName, $notes) {
            // 1. Lock and validate table
            $table = RestaurantTable::where('id', $tableId)->lockForUpdate()->first();

            if (!$table || !$table->is_active) {
                throw ValidationException::withMessages([
                    'table_id' => 'The selected dining table is inactive or not found.',
                ]);
            }

            // 2. Validate waiter assignment (Only assigned waiter can seat guests)
            $activeAssignment = TableWaiterAssignment::where('table_id', $table->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$activeAssignment || $activeAssignment->waiter_id !== $waiterId) {
                throw ValidationException::withMessages([
                    'table_id' => "You are not the currently assigned waiter for Table {$table->table_number}.",
                ]);
            }

            // 3. Validate table status (Only available tables can start a new session)
            if ($table->status !== 'available') {
                throw ValidationException::withMessages([
                    'table_id' => "Table {$table->table_number} is currently '{$table->status}' and cannot start a new dining session.",
                ]);
            }

            // 4. Validate guest count against seating capacity
            if ($guestCount < 1) {
                throw ValidationException::withMessages([
                    'guest_count' => 'Guest count must be at least 1.',
                ]);
            }

            if ($guestCount > $table->seating_capacity) {
                throw ValidationException::withMessages([
                    'guest_count' => "Guest count ({$guestCount}) exceeds Table {$table->table_number}'s seating capacity of {$table->seating_capacity} seats.",
                ]);
            }

            // 5. Enforce unique active visit invariant
            $existingActiveVisit = TableVisit::where('table_id', $table->id)
                ->whereIn('status', ['active', 'billed'])
                ->lockForUpdate()
                ->first();

            if ($existingActiveVisit) {
                throw ValidationException::withMessages([
                    'table_id' => "Table {$table->table_number} already has an active dining session (#{$existingActiveVisit->visit_code}).",
                ]);
            }

            // 6. Generate unique visit code: e.g. VIS-T1-261101-A1B2
            $randomSuffix = strtoupper(Str::random(4));
            $visitCode = sprintf('VIS-%s-%s-%s', $table->table_number, now()->format('ymd'), $randomSuffix);

            // 7. Create TableVisit
            $visit = TableVisit::create([
                'table_id' => $table->id,
                'waiter_id' => $waiterId,
                'visit_code' => $visitCode,
                'guest_name' => $guestName ? trim($guestName) : null,
                'guest_count' => $guestCount,
                'status' => 'active',
                'opened_at' => now(),
                'closed_at' => null,
                'notes' => $notes,
            ]);

            // 8. Update table status to 'occupied'
            $table->update(['status' => 'occupied']);

            return $visit;
        });
    }

    /**
     * Submit a food order round (KOT) for an active dining visit.
     *
     * @param TableVisit|int $visit
     * @param User|int $waiter
     * @param array<string, mixed>|array<int, array{menu_item_id: int, quantity: int, special_instructions?: string|null}> $itemsOrData
     * @throws ValidationException
     */
    public function submitOrder(
        TableVisit|int $visit,
        User|int $waiter,
        array $itemsOrData,
        ?string $orderNotes = null,
        ?string $idempotencyToken = null
    ): Order {
        $visitId = ($visit instanceof TableVisit) ? $visit->id : (int) $visit;
        $waiterId = ($waiter instanceof User) ? $waiter->id : (int) $waiter;

        if (isset($itemsOrData['items']) && is_array($itemsOrData['items'])) {
            $items = $itemsOrData['items'];
            $orderNotes = $itemsOrData['order_notes'] ?? $orderNotes;
            $idempotencyToken = $itemsOrData['idempotency_token'] ?? $idempotencyToken;
        } else {
            $items = $itemsOrData;
        }

        return DB::transaction(function () use ($visitId, $waiterId, $items, $orderNotes, $idempotencyToken) {
            // 1. Lock and validate dining visit
            $lockedVisit = TableVisit::where('id', $visitId)->lockForUpdate()->first();

            if (!$lockedVisit || !$lockedVisit->isActive()) {
                throw ValidationException::withMessages([
                    'visit' => "Cannot place orders: this dining session is currently '{$lockedVisit->status}'.",
                ]);
            }

            // 2. Validate waiter assignment for this table
            $activeAssignment = TableWaiterAssignment::where('table_id', $lockedVisit->table_id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$activeAssignment || $activeAssignment->waiter_id !== $waiterId) {
                throw ValidationException::withMessages([
                    'waiter' => 'You are not the currently assigned waiter for this table visit.',
                ]);
            }

            // 3. Idempotency Check (Safeguards against mobile double-tap submissions)
            if ($idempotencyToken) {
                $existingOrder = Order::where('table_visit_id', $lockedVisit->id)
                    ->where('idempotency_token', $idempotencyToken)
                    ->with('items')
                    ->first();

                if ($existingOrder) {
                    return $existingOrder;
                }
            }

            // 4. Validate items array
            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'The order cart is empty. Please select at least one menu item.',
                ]);
            }

            // 5. Query and validate canonical menu item records directly from the database
            $itemIds = array_map(fn ($it) => (int) $it['menu_item_id'], $items);
            $menuItems = MenuItem::whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($items as $itemData) {
                $mId = (int) $itemData['menu_item_id'];
                $menuItem = $menuItems->get($mId);

                if (!$menuItem) {
                    throw ValidationException::withMessages([
                        'items' => "Selected menu item #{$mId} does not exist.",
                    ]);
                }

                if (!$menuItem->is_available) {
                    throw ValidationException::withMessages([
                        'items' => "Item '{$menuItem->name}' is currently unavailable (sold out). Please remove it from the cart.",
                    ]);
                }

                if ((int) $itemData['quantity'] < 1) {
                    throw ValidationException::withMessages([
                        'items' => "Quantity for '{$menuItem->name}' must be at least 1.",
                    ]);
                }
            }

            // 6. Calculate next round number within this visit
            $nextRound = (int) ($lockedVisit->orders()->lockForUpdate()->max('round_number') ?? 0) + 1;

            // 7. Generate unique KOT order number: e.g. KOT-T1-R1-261101-ABCD
            $tableNumber = $lockedVisit->table?->table_number ?? "T{$lockedVisit->table_id}";
            $randomSuffix = strtoupper(Str::random(4));
            $orderNumber = sprintf('KOT-%s-R%d-%s-%s', $tableNumber, $nextRound, now()->format('ymd'), $randomSuffix);

            // 8. Create Order (Note: composite FK requires table_visit_id AND matching table_id)
            $order = Order::create([
                'table_visit_id' => $lockedVisit->id,
                'table_id' => $lockedVisit->table_id,
                'waiter_id' => $waiterId,
                'order_number' => $orderNumber,
                'round_number' => $nextRound,
                'status' => 'submitted',
                'order_notes' => $orderNotes ? trim($orderNotes) : null,
                'idempotency_token' => $idempotencyToken,
                'submitted_at' => now(),
            ]);

            // 9. Create snapshot OrderItem records with database prices
            foreach ($items as $itemData) {
                $mId = (int) $itemData['menu_item_id'];
                $menuItem = $menuItems->get($mId);
                $qty = (int) $itemData['quantity'];
                $unitPrice = (float) $menuItem->price; // Database price snapshot!
                $subtotal = round($unitPrice * $qty, 2);

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name, // Immutable snapshot
                    'unit_price' => $unitPrice,    // Immutable snapshot
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'special_instructions' => !empty($itemData['special_instructions']) ? trim($itemData['special_instructions']) : null,
                    'status' => 'pending',
                ]);

                // 10. Audit history record for the order item
                OrderItemStatusHistory::create([
                    'order_item_id' => $orderItem->id,
                    'previous_status' => null,
                    'new_status' => 'pending',
                    'changed_by' => $waiterId,
                    'reason' => "Initial KOT submission (Round {$nextRound})",
                    'changed_at' => now(),
                ]);
            }

            return $order->load('items');
        });
    }
}
