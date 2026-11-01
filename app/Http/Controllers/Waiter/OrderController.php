<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Waiter\SubmitOrderRequest;
use App\Models\Category;
use App\Models\TableVisit;
use App\Services\DiningAndOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Show the menu and mobile ordering screen for an active dining session.
     */
    public function create(Request $request, TableVisit $visit): View|RedirectResponse
    {
        $user = $request->user();

        // 1. Authorize: Only the visit's waiter or the currently assigned table waiter can place orders
        $isVisitWaiter = ($visit->waiter_id === $user->id);
        $isTableWaiter = ($visit->table && $visit->table->activeAssignment?->waiter_id === $user->id);

        if (! $isVisitWaiter && ! $isTableWaiter) {
            abort(403, 'You are not authorized to place orders for this dining session.');
        }

        // 2. Validate visit can accept orders (active)
        if (! $visit->canAcceptOrders()) {
            return redirect()->route('waiter.visits.show', $visit)
                ->with('error', "Orders cannot be added because this dining session is currently {$visit->status}.");
        }

        $visit->loadMissing('table');

        // 3. Load active categories with available menu items (categories ordered by display_order, items by name)
        $categories = Category::query()
            ->active()
            ->orderBy('display_order', 'asc')
            ->with(['menuItems' => function ($q) {
                $q->available()->orderBy('name', 'asc');
            }])
            ->get();

        $roundNumber = $visit->getNextRoundNumber();

        return view('waiter.orders.create', compact('visit', 'categories', 'roundNumber'));
    }

    /**
     * Submit an order (KOT round) to the kitchen for an active dining session.
     */
    public function store(
        SubmitOrderRequest $request,
        TableVisit $visit,
        DiningAndOrderService $diningService
    ): RedirectResponse {
        $user = $request->user();

        // Authorize: Only the visit's waiter or table waiter can place orders
        $isVisitWaiter = ($visit->waiter_id === $user->id);
        $isTableWaiter = ($visit->table && $visit->table->activeAssignment?->waiter_id === $user->id);

        if (! $isVisitWaiter && ! $isTableWaiter) {
            abort(403, 'You are not authorized to place orders for this dining session.');
        }

        try {
            $order = $diningService->submitOrder($visit, $user, $request->validated());

            return redirect()->route('waiter.visits.show', $visit)
                ->with('success', "Order #{$order->order_number} (Round {$order->round_number}) submitted successfully to the kitchen! (" . count($order->items) . ' items)');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
