<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Kitchen Display System (KDS) Live Ticket Board.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');

        // Base query for KDS orders, ordered FIFO (oldest submitted first)
        $ordersQuery = Order::query()
            ->with([
                'table',
                'visit',
                'waiter',
                'items.menuItem',
                'items.statusHistory',
            ])
            ->orderBy('submitted_at', 'asc')
            ->orderBy('id', 'asc');

        // Apply tab filter
        match ($filter) {
            'new'       => $ordersQuery->where('status', 'submitted'),
            'preparing' => $ordersQuery->where('status', 'preparing'),
            'ready'     => $ordersQuery->where('status', 'ready'),
            'completed' => $ordersQuery->where('status', 'served')->whereDate('updated_at', today()),
            default     => $ordersQuery->whereIn('status', ['submitted', 'preparing', 'ready']),
        };

        $orders = $ordersQuery->get();

        // Queue counts for KPI header pills
        $totalActiveCount = Order::whereIn('status', ['submitted', 'preparing', 'ready'])->count();
        $submittedCount   = Order::where('status', 'submitted')->count();
        $preparingCount   = Order::where('status', 'preparing')->count();
        $readyCount       = Order::where('status', 'ready')->count();
        $completedCount   = Order::where('status', 'served')->whereDate('updated_at', today())->count();

        return view('kitchen.dashboard', compact(
            'user',
            'orders',
            'filter',
            'totalActiveCount',
            'submittedCount',
            'preparingCount',
            'readyCount',
            'completedCount'
        ));
    }

    /**
     * Start preparation for an entire KOT ticket.
     */
    public function startPrep(Request $request, Order $order, KitchenService $kitchenService): JsonResponse|RedirectResponse
    {
        try {
            $updatedOrder = $kitchenService->startOrderPrep($order, $request->user());

            $msg = "Started cooking KOT #{$order->order_number} for Table " . ($order->table?->table_number ?? $order->table_id) . ".";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'order'   => $updatedOrder,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an individual item's status (preparing, ready, served).
     */
    public function updateItemStatus(Request $request, OrderItem $item, KitchenService $kitchenService): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:preparing,ready,served,cancelled',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $updatedItem = $kitchenService->updateItemStatus(
                $item,
                $validated['status'],
                $request->user(),
                $validated['reason'] ?? null
            );

            $statusLabel = match ($validated['status']) {
                'preparing' => 'Cooking',
                'ready'     => 'Ready to Serve',
                'served'    => 'Served',
                'cancelled' => 'Cancelled',
                default     => ucfirst($validated['status']),
            };

            $msg = "Item '{$item->item_name}' updated to {$statusLabel}.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'item'    => $updatedItem,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark an entire KOT order as ready for pickup.
     */
    public function markOrderReady(Request $request, Order $order, KitchenService $kitchenService): JsonResponse|RedirectResponse
    {
        try {
            $updatedOrder = $kitchenService->markOrderReady($order, $request->user());

            $msg = "KOT #{$order->order_number} (Table " . ($order->table?->table_number ?? $order->table_id) . ") is Ready to Serve!";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'order'   => $updatedOrder,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mark an entire KOT order as served to table.
     */
    public function markOrderServed(Request $request, Order $order, KitchenService $kitchenService): JsonResponse|RedirectResponse
    {
        try {
            $updatedOrder = $kitchenService->markOrderServed($order, $request->user());

            $msg = "KOT #{$order->order_number} marked as Served to Table " . ($order->table?->table_number ?? $order->table_id) . ".";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'order'   => $updatedOrder,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
