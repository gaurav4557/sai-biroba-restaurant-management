<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    /**
     * Display the Floor Plan & Table Management Dashboard.
     */
    public function index(Request $request): View
    {
        // 1. Fetch only active tables with eager-loaded relationships
        $tables = RestaurantTable::query()
            ->active()
            ->with([
                'activeAssignment.waiter',
                'activeVisit',
            ])
            ->get();

        // 2. Sort numerically by table number (T1, T2, ... T10, T11, ... T20)
        $sortedTables = $tables->sort(function ($a, $b) {
            return strnatcasecmp($a->table_number, $b->table_number);
        })->values();

        // 3. Group by room section
        $room1Tables = $sortedTables->filter(fn ($t) => $t->floor_section === 'Room 1')->values();
        $room2Tables = $sortedTables->filter(fn ($t) => $t->floor_section === 'Room 2')->values();

        // 4. Calculate operational summary metrics from active tables
        $totalActive = $sortedTables->count();
        $availableCount = $sortedTables->where('status', 'available')->count();
        $occupiedCount = $sortedTables->where('status', 'occupied')->count();
        $billedCount = $sortedTables->where('status', 'billed')->count();
        $outOfServiceCount = $sortedTables->where('status', 'out_of_service')->count();

        // 5. Total archived tables count (historical/retired inventory)
        $archivedCount = RestaurantTable::where('is_active', false)->count();

        return view('admin.tables.index', compact(
            'room1Tables',
            'room2Tables',
            'totalActive',
            'availableCount',
            'occupiedCount',
            'billedCount',
            'outOfServiceCount',
            'archivedCount'
        ));
    }
}
