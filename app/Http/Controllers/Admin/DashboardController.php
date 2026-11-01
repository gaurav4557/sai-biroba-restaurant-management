<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin Executive Dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // High-level operational counts for the dashboard overview
        $totalTables = DB::table('restaurant_tables')->where('is_active', true)->count();
        $totalStaff = DB::table('users')->where('is_active', true)->whereNotNull('role')->count();
        $totalMenuItems = DB::table('menu_items')->where('is_available', true)->count();
        $activeVisits = DB::table('table_visits')->whereIn('status', ['active', 'billed'])->count();

        return view('admin.dashboard', compact('user', 'totalTables', 'totalStaff', 'totalMenuItems', 'activeVisits'));
    }
}
