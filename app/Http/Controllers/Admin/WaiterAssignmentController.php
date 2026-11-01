<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignWaiterRequest;
use App\Http\Requests\Admin\ReassignWaiterRequest;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\WaiterAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaiterAssignmentController extends Controller
{
    public function __construct(
        protected WaiterAssignmentService $assignmentService
    ) {}

    /**
     * Display the Waiter-to-Table Assignment Management Console.
     */
    public function index(Request $request): View
    {
        // 1. Fetch all active dining tables with current active assignment & active dining visit
        $tables = RestaurantTable::query()
            ->active()
            ->with([
                'activeAssignment.waiter',
                'activeVisit',
                'assignments' => function ($q) {
                    $q->with(['waiter', 'assignedBy'])->orderByDesc('assigned_at');
                },
            ])
            ->get();

        // 2. Sort naturally by table number (T1, T2 ... T10, T11 ... T20)
        $sortedTables = $tables->sort(function ($a, $b) {
            return strnatcasecmp($a->table_number, $b->table_number);
        })->values();

        // 3. Group tables by Room
        $room1Tables = $sortedTables->filter(fn ($t) => $t->floor_section === 'Room 1')->values();
        $room2Tables = $sortedTables->filter(fn ($t) => $t->floor_section === 'Room 2')->values();

        // 4. Fetch all active waiters with their current active table assignment count
        $waiters = User::query()
            ->waiters()
            ->withCount('activeWaiterAssignments')
            ->orderBy('name')
            ->get();

        // 5. Calculate assignment summary metrics
        $totalTables = $sortedTables->count();
        $assignedCount = $sortedTables->filter(fn ($t) => $t->activeAssignment !== null)->count();
        $unassignedCount = $sortedTables->filter(fn ($t) => $t->activeAssignment === null)->count();
        $activeWaitersCount = $waiters->count();

        return view('admin.assignments.index', compact(
            'room1Tables',
            'room2Tables',
            'waiters',
            'totalTables',
            'assignedCount',
            'unassignedCount',
            'activeWaitersCount'
        ));
    }

    /**
     * Assign a waiter to a table.
     */
    public function store(AssignWaiterRequest $request): RedirectResponse
    {
        $assignment = $this->assignmentService->assign(
            tableId: (int) $request->validated('table_id'),
            waiterId: (int) $request->validated('waiter_id'),
            assignedBy: (int) $request->user()->id,
            notes: $request->validated('notes')
        );

        $tableNumber = $assignment->table?->table_number ?? "Table #{$assignment->table_id}";
        $waiterName = $assignment->waiter?->name ?? "Waiter #{$assignment->waiter_id}";

        return redirect()->route('admin.assignments.index')
            ->with('status', "{$waiterName} was successfully assigned to {$tableNumber}.");
    }

    /**
     * Reassign a table to another waiter.
     */
    public function reassign(ReassignWaiterRequest $request, RestaurantTable $table): RedirectResponse
    {
        $assignment = $this->assignmentService->reassign(
            tableId: (int) $table->id,
            newWaiterId: (int) $request->validated('waiter_id'),
            assignedBy: (int) $request->user()->id,
            notes: $request->validated('notes')
        );

        $tableNumber = $table->table_number;
        $newWaiterName = $assignment->waiter?->name ?? "Waiter #{$assignment->waiter_id}";

        return redirect()->route('admin.assignments.index')
            ->with('status', "{$tableNumber} was successfully reassigned to {$newWaiterName}.");
    }

    /**
     * Unassign / remove an active waiter assignment from a table.
     */
    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $this->assignmentService->unassign(
            tableId: (int) $table->id,
            unassignedBy: (int) auth()->id(),
            notes: 'Unassigned by administrator via control panel'
        );

        return redirect()->route('admin.assignments.index')
            ->with('status', "{$table->table_number} active waiter assignment was completed/removed.");
    }

    /**
     * View assignment history for a specific table (JSON endpoint or modal support).
     */
    public function history(RestaurantTable $table)
    {
        $history = $table->assignments()
            ->with(['waiter:id,name,phone', 'assignedBy:id,name'])
            ->orderByDesc('assigned_at')
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'table_number' => $table->table_number,
                'floor_section' => $table->floor_section,
                'history' => $history,
            ]);
        }

        return redirect()->route('admin.assignments.index');
    }
}
