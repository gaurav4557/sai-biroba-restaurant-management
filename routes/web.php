<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TableController as AdminTableController;
use App\Http\Controllers\Admin\WaiterAssignmentController as AdminWaiterAssignmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Counter\BillController as CounterBillController;
use App\Http\Controllers\Counter\DashboardController as CounterDashboardController;
use App\Http\Controllers\Counter\PaymentController as CounterPaymentController;
use App\Http\Controllers\Kitchen\DashboardController as KitchenDashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Waiter\DashboardController as WaiterDashboardController;
use App\Http\Controllers\Waiter\OrderController as WaiterOrderController;
use App\Http\Controllers\Waiter\VisitController as WaiterVisitController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Sai Biroba Restaurant Management System
|--------------------------------------------------------------------------
*/

// Root redirector: Routes guest to login, and authenticated staff to their specific dashboard
Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'waiter' => redirect()->route('waiter.dashboard'),
            'kitchen' => redirect()->route('kitchen.dashboard'),
            'counter' => redirect()->route('counter.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Role-Protected Portals
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/tables', [AdminTableController::class, 'index'])->name('tables.index');
    Route::get('/assignments', [AdminWaiterAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AdminWaiterAssignmentController::class, 'store'])->name('assignments.store');
    Route::put('/assignments/{table}', [AdminWaiterAssignmentController::class, 'reassign'])->name('assignments.reassign');
    Route::delete('/assignments/{table}', [AdminWaiterAssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::get('/assignments/{table}/history', [AdminWaiterAssignmentController::class, 'history'])->name('assignments.history');
});

Route::middleware(['auth', 'role:waiter'])->prefix('waiter')->as('waiter.')->group(function () {
    Route::get('/dashboard', [WaiterDashboardController::class, 'index'])->name('dashboard');

    // Seating & Table Visits
    Route::get('/tables/{table}/seat', [WaiterVisitController::class, 'create'])->name('visits.create');
    Route::post('/tables/{table}/seat', [WaiterVisitController::class, 'store'])->name('visits.store');
    Route::get('/visits/{visit}', [WaiterVisitController::class, 'show'])->name('visits.show');

    // Food Ordering (KOT)
    Route::get('/visits/{visit}/order', [WaiterOrderController::class, 'create'])->name('orders.create');
    Route::post('/visits/{visit}/order', [WaiterOrderController::class, 'store'])->name('orders.store');
});

Route::middleware(['auth', 'role:kitchen'])->prefix('kitchen')->as('kitchen.')->group(function () {
    Route::get('/dashboard', [KitchenDashboardController::class, 'index'])->name('dashboard');
    Route::post('/orders/{order}/start-prep', [KitchenDashboardController::class, 'startPrep'])->name('orders.start-prep');
    Route::post('/orders/{order}/mark-ready', [KitchenDashboardController::class, 'markOrderReady'])->name('orders.mark-ready');
    Route::post('/orders/{order}/mark-served', [KitchenDashboardController::class, 'markOrderServed'])->name('orders.mark-served');
    Route::post('/items/{item}/status', [KitchenDashboardController::class, 'updateItemStatus'])->name('items.status');
});

Route::middleware(['auth', 'role:counter'])->prefix('counter')->as('counter.')->group(function () {
    Route::get('/dashboard', [CounterDashboardController::class, 'index'])->name('dashboard');

    // Consolidated Billing
    Route::get('/visits/{visit}/bill/create', [CounterBillController::class, 'create'])->name('bills.create');
    Route::post('/visits/{visit}/bill', [CounterBillController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}', [CounterBillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/receipt', [CounterBillController::class, 'printReceipt'])->name('bills.receipt');
    Route::post('/bills/{bill}/void', [CounterBillController::class, 'void'])->name('bills.void');

    // Payment Processing
    Route::post('/bills/{bill}/payments', [CounterPaymentController::class, 'store'])->name('payments.store');
});

// Financial Reports & Analytics (Admin & Counter Staff)
Route::middleware(['auth', 'role:admin,counter'])->prefix('reports')->as('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
    Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
    Route::get('/print', [ReportController::class, 'print'])->name('print');
    Route::get('/export/csv', [ReportController::class, 'exportCsv'])->name('export.csv');
});
