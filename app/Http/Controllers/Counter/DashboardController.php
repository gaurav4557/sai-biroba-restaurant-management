<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Counter POS & Billing Command Center.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // High-level counts
        $occupiedTablesCount = RestaurantTable::occupied()->count();
        $billedTablesCount = RestaurantTable::billed()->count();
        $pendingBillsCount = Bill::unpaid()->count();

        // Strict revenue calculation: Only successful payments count towards revenue
        $todayRevenue = (float) Payment::successful()
            ->whereDate('paid_at', today())
            ->sum('amount');

        // Fetch all active or billed dining sessions with eager loaded relationships
        $activeVisits = TableVisit::whereIn('status', ['active', 'billed'])
            ->with([
                'table',
                'waiter',
                'bill',
                'orders' => function ($q) {
                    $q->where('status', '!=', 'cancelled')->with(['items' => function ($iq) {
                        $iq->where('status', '!=', 'cancelled');
                    }]);
                },
            ])
            ->orderBy('opened_at', 'asc')
            ->get();

        // Compute subtotal per visit
        $activeVisits->each(function ($visit) {
            $visit->orders_count = $visit->orders->count();
            $visit->items_count = $visit->orders->flatMap->items->count();
            $visit->computed_subtotal = (float) $visit->orders->flatMap->items->sum('subtotal');
        });

        // Recent bills (latest 15)
        $recentBills = Bill::with(['visit.table', 'generatedBy'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return view('counter.dashboard', compact(
            'user',
            'occupiedTablesCount',
            'billedTablesCount',
            'pendingBillsCount',
            'todayRevenue',
            'activeVisits',
            'recentBills'
        ));
    }
}
