<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Mobile-First Waiter Dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Fetch only active tables where the active waiter assignment belongs to this waiter
        $tables = RestaurantTable::query()
            ->active()
            ->whereHas('activeAssignment', function ($q) use ($user) {
                $q->where('waiter_id', $user->id);
            })
            ->with([
                'activeAssignment',
                'activeVisit',
            ])
            ->get();

        // Naturally sort tables by number (T1, T2 ... T10, T11 ... T20)
        $assignedTables = $tables->sort(function ($a, $b) {
            return strnatcasecmp($a->table_number, $b->table_number);
        })->values();

        // Group by room section for mobile readability
        $room1Tables = $assignedTables->filter(fn ($t) => $t->floor_section === 'Room 1')->values();
        $room2Tables = $assignedTables->filter(fn ($t) => $t->floor_section === 'Room 2')->values();

        // Summary metrics for the waiter's shift
        $totalAssigned = $assignedTables->count();
        $occupiedCount = $assignedTables->where('status', 'occupied')->count();
        $availableCount = $assignedTables->where('status', 'available')->count();
        $billedCount = $assignedTables->where('status', 'billed')->count();

        return view('waiter.dashboard', compact(
            'user',
            'assignedTables',
            'room1Tables',
            'room2Tables',
            'totalAssigned',
            'occupiedCount',
            'availableCount',
            'billedCount'
        ));
    }
}
