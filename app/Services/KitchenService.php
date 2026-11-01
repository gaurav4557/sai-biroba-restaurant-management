<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KitchenService
{
    /**
     * Start preparation for an entire KOT order.
     * Transitions order to 'preparing' and all pending items to 'preparing'.
     *
     * @throws ValidationException
     */
    public function startOrderPrep(Order $order, User $kitchenStaff): Order
    {
        return DB::transaction(function () use ($order, $kitchenStaff) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Only submitted or already preparing orders can be acted upon
            if ($lockedOrder->isCancelled() || in_array($lockedOrder->status, ['ready', 'served'], true)) {
                throw ValidationException::withMessages([
                    'order' => "Cannot start preparation: KOT #{$lockedOrder->order_number} is already '{$lockedOrder->status}'.",
                ]);
            }

            // Update order status if submitted
            if ($lockedOrder->status === 'submitted') {
                $lockedOrder->update(['status' => 'preparing']);
            }

            // Lock and transition all pending items of this order to 'preparing'
            $pendingItems = OrderItem::where('order_id', $lockedOrder->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            foreach ($pendingItems as $item) {
                $item->update(['status' => 'preparing']);

                // Audit history record
                OrderItemStatusHistory::create([
                    'order_item_id' => $item->id,
                    'previous_status' => 'pending',
                    'new_status' => 'preparing',
                    'changed_by' => $kitchenStaff->id,
                    'reason' => "Kitchen staff {$kitchenStaff->name} started preparation (KOT #{$lockedOrder->order_number})",
                    'changed_at' => now(),
                ]);
            }

            return $lockedOrder->fresh(['items.statusHistory', 'table', 'waiter']);
        });
    }

    /**
     * Update an individual order item's preparation status.
     * Transitions item and synchronizes parent order status.
     *
     * @throws ValidationException
     */
    public function updateItemStatus(
        OrderItem $item,
        string $newStatus,
        User $kitchenStaff,
        ?string $reason = null
    ): OrderItem {
        $allowedStatuses = ['preparing', 'ready', 'served', 'cancelled'];
        if (!in_array($newStatus, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => "Invalid preparation status '{$newStatus}'.",
            ]);
        }

        return DB::transaction(function () use ($item, $newStatus, $kitchenStaff, $reason) {
            $lockedItem = OrderItem::where('id', $item->id)->lockForUpdate()->firstOrFail();
            $lockedOrder = Order::where('id', $lockedItem->order_id)->lockForUpdate()->firstOrFail();

            $currentStatus = $lockedItem->status;

            // Idempotent: if already at target status, return cleanly
            if ($currentStatus === $newStatus) {
                return $lockedItem;
            }

            // Enforce valid forward state transitions
            $this->validateStateTransition($currentStatus, $newStatus, $lockedItem->item_name);

            // Update item status
            $lockedItem->update(['status' => $newStatus]);

            // Record audit history
            $transitionReason = $reason ?? match ($newStatus) {
                'preparing' => "Kitchen staff {$kitchenStaff->name} started cooking {$lockedItem->item_name}",
                'ready'     => "Kitchen staff {$kitchenStaff->name} marked {$lockedItem->item_name} ready to serve",
                'served'    => "Staff {$kitchenStaff->name} marked {$lockedItem->item_name} served to table",
                'cancelled' => "Item {$lockedItem->item_name} cancelled in kitchen",
                default     => "Status changed to {$newStatus}",
            };

            OrderItemStatusHistory::create([
                'order_item_id'   => $lockedItem->id,
                'previous_status' => $currentStatus,
                'new_status'      => $newStatus,
                'changed_by'      => $kitchenStaff->id,
                'reason'          => $transitionReason,
                'changed_at'      => now(),
            ]);

            // Synchronize parent Order status based on all its items
            $this->syncOrderStatus($lockedOrder, $kitchenStaff);

            return $lockedItem->fresh(['order', 'statusHistory']);
        });
    }

    /**
     * Mark an entire KOT order as 'ready' (food ready for pickup by waiter).
     * Transitions all active items to 'ready'.
     *
     * @throws ValidationException
     */
    public function markOrderReady(Order $order, User $kitchenStaff): Order
    {
        return DB::transaction(function () use ($order, $kitchenStaff) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->isCancelled() || $lockedOrder->status === 'served') {
                throw ValidationException::withMessages([
                    'order' => "Cannot mark ready: KOT #{$lockedOrder->order_number} is already '{$lockedOrder->status}'.",
                ]);
            }

            // Lock and transition all active, non-served, non-cancelled items to 'ready'
            $itemsToUpdate = OrderItem::where('order_id', $lockedOrder->id)
                ->whereIn('status', ['pending', 'preparing'])
                ->lockForUpdate()
                ->get();

            foreach ($itemsToUpdate as $item) {
                $prevStatus = $item->status;
                $item->update(['status' => 'ready']);

                OrderItemStatusHistory::create([
                    'order_item_id'   => $item->id,
                    'previous_status' => $prevStatus,
                    'new_status'      => 'ready',
                    'changed_by'      => $kitchenStaff->id,
                    'reason'          => "Kitchen staff {$kitchenStaff->name} marked all items ready (KOT #{$lockedOrder->order_number})",
                    'changed_at'      => now(),
                ]);
            }

            // Ensure parent order is updated to 'ready'
            $lockedOrder->update(['status' => 'ready']);

            return $lockedOrder->fresh(['items.statusHistory', 'table', 'waiter']);
        });
    }

    /**
     * Mark an entire KOT order as 'served' (all food delivered to table).
     * Transitions all active items to 'served'.
     *
     * @throws ValidationException
     */
    public function markOrderServed(Order $order, User $kitchenStaff): Order
    {
        return DB::transaction(function () use ($order, $kitchenStaff) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->isCancelled()) {
                throw ValidationException::withMessages([
                    'order' => "Cannot mark served: KOT #{$lockedOrder->order_number} is cancelled.",
                ]);
            }

            $itemsToUpdate = OrderItem::where('order_id', $lockedOrder->id)
                ->where('status', '!=', 'served')
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->get();

            foreach ($itemsToUpdate as $item) {
                $prevStatus = $item->status;
                $item->update(['status' => 'served']);

                OrderItemStatusHistory::create([
                    'order_item_id'   => $item->id,
                    'previous_status' => $prevStatus,
                    'new_status'      => 'served',
                    'changed_by'      => $kitchenStaff->id,
                    'reason'          => "Staff {$kitchenStaff->name} marked order served to table (KOT #{$lockedOrder->order_number})",
                    'changed_at'      => now(),
                ]);
            }

            $lockedOrder->update(['status' => 'served']);

            return $lockedOrder->fresh(['items.statusHistory', 'table', 'waiter']);
        });
    }

    /**
     * Synchronize parent Order status dynamically based on current item statuses.
     */
    public function syncOrderStatus(Order $order, ?User $staff = null): Order
    {
        $items = OrderItem::where('order_id', $order->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($items->isEmpty()) {
            return $order;
        }

        $allServed = $items->every(fn ($item) => $item->status === 'served');
        $allReadyOrServed = $items->every(fn ($item) => in_array($item->status, ['ready', 'served'], true));
        $anyPreparingOrReady = $items->contains(fn ($item) => in_array($item->status, ['preparing', 'ready'], true));

        $targetStatus = match (true) {
            $allServed => 'served',
            $allReadyOrServed => 'ready',
            $anyPreparingOrReady => 'preparing',
            default => 'submitted',
        };

        if ($order->status !== $targetStatus) {
            $order->update(['status' => $targetStatus]);
        }

        return $order;
    }

    /**
     * Validate allowable forward state transitions for food preparation.
     *
     * @throws ValidationException
     */
    protected function validateStateTransition(string $from, string $to, string $itemName): void
    {
        // Cancelled or served items cannot be transitioned further
        if ($from === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => "Cannot update {$itemName}: this item was cancelled.",
            ]);
        }

        if ($from === 'served' && $to !== 'served') {
            throw ValidationException::withMessages([
                'status' => "Cannot update {$itemName}: this item has already been served to the customer.",
            ]);
        }

        // Allowable transitions:
        // pending   -> preparing, ready, cancelled
        // preparing -> ready, cancelled
        // ready     -> served, cancelled
        $validMap = [
            'pending'   => ['preparing', 'ready', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready'     => ['served', 'cancelled'],
            'served'    => [],
        ];

        $allowed = $validMap[$from] ?? [];

        if (!in_array($to, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Invalid transition for '{$itemName}' from '{$from}' to '{$to}'.",
            ]);
        }
    }
}
