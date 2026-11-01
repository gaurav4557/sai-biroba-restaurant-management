<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Waiter\StartVisitRequest;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use App\Services\DiningAndOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    /**
     * Show the form for seating guests at an assigned available table.
     */
    public function create(Request $request, RestaurantTable $table): View|RedirectResponse
    {
        $user = $request->user();

        // 1. Authorize: Only the currently assigned active waiter can seat guests
        $assignment = $table->activeAssignment;
        if (! $assignment || $assignment->waiter_id !== $user->id) {
            abort(403, 'You are not assigned to manage Table ' . $table->table_number . '.');
        }

        // 2. If table is already occupied with an active visit, redirect to the visit hub
        if ($table->status === 'occupied' || $table->status === 'billed') {
            $activeVisit = $table->activeVisit;
            if ($activeVisit) {
                return redirect()->route('waiter.visits.show', $activeVisit)
                    ->with('info', "Table {$table->table_number} already has an active dining session.");
            }
        }

        // 3. Table must be available to seat guests
        if ($table->status !== 'available') {
            return redirect()->route('waiter.dashboard')
                ->with('error', "Table {$table->table_number} cannot be seated because its status is {$table->status}.");
        }

        return view('waiter.visits.create', compact('table'));
    }

    /**
     * Start a new dining session for an assigned available table.
     */
    public function store(
        StartVisitRequest $request,
        RestaurantTable $table,
        DiningAndOrderService $diningService
    ): RedirectResponse {
        $user = $request->user();

        // Verify waiter assignment
        $assignment = $table->activeAssignment;
        if (! $assignment || $assignment->waiter_id !== $user->id) {
            abort(403, 'You are not assigned to manage Table ' . $table->table_number . '.');
        }

        try {
            $visit = $diningService->startVisit($table, $user, $request->validated());

            return redirect()->route('waiter.visits.show', $visit)
                ->with('success', "Dining session {$visit->visit_code} started for Table {$table->table_number}. ({$visit->guest_count} guests seated)");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the Visit Hub showing table status, guest details, and KOT order rounds.
     */
    public function show(Request $request, TableVisit $visit): View
    {
        $user = $request->user();

        // Authorize: Only the visit's waiter or the currently assigned table waiter can view
        $isVisitWaiter = ($visit->waiter_id === $user->id);
        $isTableWaiter = ($visit->table && $visit->table->activeAssignment?->waiter_id === $user->id);

        if (! $isVisitWaiter && ! $isTableWaiter) {
            abort(403, 'You are not authorized to view this dining session.');
        }

        $visit->load([
            'table',
            'waiter',
            'orders' => function ($q) {
                $q->orderBy('round_number', 'asc')->with([
                    'items.menuItem.category',
                    'items.statusHistory',
                    'cancelledBy',
                ]);
            },
        ]);

        return view('waiter.visits.show', compact('visit'));
    }
}
