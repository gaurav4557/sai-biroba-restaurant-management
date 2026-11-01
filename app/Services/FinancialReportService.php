<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\CreditNote;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\TableVisit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReportService
{
    /**
     * Generate daily financial report for a specific calendar date.
     */
    public function getDailyReport(Carbon $date): array
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $report = $this->getCustomRangeReport($start, $end);
        $report['report_type'] = 'daily';
        $report['selected_date'] = $date->format('Y-m-d');
        $report['period_label'] = 'Daily Report — ' . $date->format('D, d M Y');

        // Hourly collections breakdown for intraday analysis
        $report['hourly_collections'] = $this->getHourlyCollections($start, $end);

        return $report;
    }

    /**
     * Generate monthly financial report for a specific calendar month.
     */
    public function getMonthlyReport(int $year, int $month): array
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        $report = $this->getCustomRangeReport($start, $end);
        $report['report_type'] = 'monthly';
        $report['selected_year'] = $year;
        $report['selected_month'] = $month;
        $report['period_label'] = 'Monthly Report — ' . $start->format('F Y');

        return $report;
    }

    /**
     * Generate financial report for an arbitrary custom date range.
     * Central calculation engine enforcing all Non-GST financial invariants.
     */
    public function getCustomRangeReport(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // ---------------------------------------------------------------------
        // 1. Collections Metrics (Cashflow Basis: paid_at & refunded_at)
        // ---------------------------------------------------------------------
        $grossCollections = (float) Payment::successful()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $successfulRefunds = (float) Refund::successful()
            ->whereBetween('refunded_at', [$start, $end])
            ->sum('amount');

        $netCollections = max(0.00, round($grossCollections - $successfulRefunds, 2));

        $successfulPaymentsCount = Payment::successful()
            ->whereBetween('paid_at', [$start, $end])
            ->count();

        $successfulRefundsCount = Refund::successful()
            ->whereBetween('refunded_at', [$start, $end])
            ->count();

        // ---------------------------------------------------------------------
        // 2. Billed Sales Metrics (Accrual / Invoice Basis: issued_at)
        // ---------------------------------------------------------------------
        $billedSalesQuery = Bill::billedSales()->whereBetween('issued_at', [$start, $end]);

        $grossBilledSales = (float) (clone $billedSalesQuery)->sum('final_amount');
        $billedSubtotal = (float) (clone $billedSalesQuery)->sum('subtotal_amount');
        $totalDiscounts = (float) (clone $billedSalesQuery)->sum('discount_amount');
        $totalServiceCharge = (float) (clone $billedSalesQuery)->sum('service_charge');
        $totalRoundOff = (float) (clone $billedSalesQuery)->sum('round_off');

        // Credit Notes Issued within period
        $creditNotesSum = (float) CreditNote::whereBetween('issued_at', [$start, $end])
            ->sum('total_credit_amount');

        $creditNotesCount = CreditNote::whereBetween('issued_at', [$start, $end])
            ->count();

        $netBilledSales = max(0.00, round($grossBilledSales - $creditNotesSum, 2));

        // ---------------------------------------------------------------------
        // 3. Bill Counts and Status Ledger
        // ---------------------------------------------------------------------
        $allBillsInRange = Bill::whereBetween('issued_at', [$start, $end]);

        $totalBillsIssued = (clone $billedSalesQuery)->count();
        $paidBillsCount = (clone $allBillsInRange)->where('status', 'paid')->count();
        $partiallyPaidBillsCount = (clone $allBillsInRange)->where('status', 'partially_paid')->count();
        $unpaidBillsCount = (clone $allBillsInRange)->where('status', 'issued')->count();
        $voidedBillsCount = (clone $allBillsInRange)->where('status', 'voided')->count();

        $totalPaidOnIssuedBills = (float) (clone $billedSalesQuery)->sum('total_paid_amount');
        $outstandingBalance = max(0.00, round($grossBilledSales - $totalPaidOnIssuedBills, 2));

        // Average Order Value / Average Ticket Size (Avoid division by zero)
        $avgBillValue = $totalBillsIssued > 0
            ? round($grossBilledSales / $totalBillsIssued, 2)
            : 0.00;

        // ---------------------------------------------------------------------
        // 4. Breakdown & Trend Modules
        // ---------------------------------------------------------------------
        $paymentMethods = $this->getPaymentMethodBreakdown($start, $end, $grossCollections);
        $bestSellingDishes = $this->getBestSellingDishes($start, $end, 10);
        $orderStats = $this->getOrderStatistics($start, $end, $grossBilledSales, $totalBillsIssued);
        $orderStats['total_bills_issued'] = $totalBillsIssued;
        $orderStats['paid_bills_count'] = $paidBillsCount;
        $orderStats['partially_paid_bills_count'] = $partiallyPaidBillsCount;
        $orderStats['unpaid_bills_count'] = $unpaidBillsCount;
        $orderStats['voided_bills_count'] = $voidedBillsCount;
        $orderStats['outstanding_balance'] = $outstandingBalance;
        $orderStats['average_order_value'] = $avgBillValue;

        $dailyTrend = $this->getDailyTrendData($start, $end);

        return [
            'report_type' => 'custom',
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'period_label' => 'Custom Range: ' . $start->format('d M Y') . ' &ndash; ' . $end->format('d M Y'),

            // Financial KPIs
            'gross_collections' => $grossCollections,
            'successful_refunds' => $successfulRefunds,
            'net_collections' => $netCollections,
            'gross_billed_sales' => $grossBilledSales,
            'credit_notes_total' => $creditNotesSum,
            'net_billed_sales' => $netBilledSales,
            'total_discounts' => $totalDiscounts,
            'billed_subtotal' => $billedSubtotal,
            'total_service_charge' => $totalServiceCharge,
            'total_round_off' => $totalRoundOff,
            'total_gst' => 0.00, // Non-GST Commercial Policy invariant

            // Counts & Audit Ledger
            'payments_count' => $successfulPaymentsCount,
            'refunds_count' => $successfulRefundsCount,
            'credit_notes_count' => $creditNotesCount,
            'total_bills_issued' => $totalBillsIssued,
            'paid_bills_count' => $paidBillsCount,
            'partially_paid_bills_count' => $partiallyPaidBillsCount,
            'unpaid_bills_count' => $unpaidBillsCount,
            'voided_bills_count' => $voidedBillsCount,
            'outstanding_balance' => $outstandingBalance,
            'avg_bill_value' => $avgBillValue,

            // Submodules
            'payment_methods' => $paymentMethods,
            'best_selling_dishes' => $bestSellingDishes,
            'order_statistics' => $orderStats,
            'daily_trend' => $dailyTrend,

            // Convenient aliases for Blade views
            'total_credit_notes' => $creditNotesSum,
            'best_sellers' => $bestSellingDishes,
            'order_stats' => $orderStats,
        ];
    }

    /**
     * Compute payment method distribution strictly from successful payments.
     */
    public function getPaymentMethodBreakdown(Carbon $startDate, Carbon $endDate, ?float $totalCollections = null): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $rows = Payment::successful()
            ->whereBetween('paid_at', [$start, $end])
            ->select('payment_method', DB::raw('COUNT(*) as tx_count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('payment_method')
            ->get();

        $gross = $totalCollections ?? (float) $rows->sum('total_amount');

        $methods = [
            'cash' => ['method' => 'cash', 'label' => 'Cash', 'icon' => '💵', 'amount' => 0.00, 'total_amount' => 0.00, 'count' => 0, 'transaction_count' => 0, 'percentage' => 0.0],
            'upi' => ['method' => 'upi', 'label' => 'UPI / QR', 'icon' => '📱', 'amount' => 0.00, 'total_amount' => 0.00, 'count' => 0, 'transaction_count' => 0, 'percentage' => 0.0],
            'credit_card' => ['method' => 'credit_card', 'label' => 'Credit Card', 'icon' => '💳', 'amount' => 0.00, 'total_amount' => 0.00, 'count' => 0, 'transaction_count' => 0, 'percentage' => 0.0],
            'debit_card' => ['method' => 'debit_card', 'label' => 'Debit Card', 'icon' => '💳', 'amount' => 0.00, 'total_amount' => 0.00, 'count' => 0, 'transaction_count' => 0, 'percentage' => 0.0],
            'other' => ['method' => 'other', 'label' => 'Other', 'icon' => '🔄', 'amount' => 0.00, 'total_amount' => 0.00, 'count' => 0, 'transaction_count' => 0, 'percentage' => 0.0],
        ];

        foreach ($rows as $row) {
            $m = $row->payment_method;
            if (isset($methods[$m])) {
                $methods[$m]['amount'] = (float) $row->total_amount;
                $methods[$m]['total_amount'] = (float) $row->total_amount;
                $methods[$m]['count'] = (int) $row->tx_count;
                $methods[$m]['transaction_count'] = (int) $row->tx_count;
                $methods[$m]['percentage'] = $gross > 0
                    ? round(($row->total_amount / $gross) * 100, 1)
                    : 0.0;
            }
        }

        return $methods;
    }

    /**
     * Compute top-selling dishes by quantity and sales volume from valid non-voided bills.
     */
    public function getBestSellingDishes(Carbon $startDate, Carbon $endDate, int $limit = 10): Collection
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        return BillItem::select(
                'bill_items.item_name',
                DB::raw('COALESCE(MAX(menu_items.dietary_type), "veg") as dietary_type'),
                DB::raw('COALESCE(MAX(menu_items.dietary_type), "veg") as food_type'),
                DB::raw('SUM(bill_items.quantity) as total_qty'),
                DB::raw('SUM(bill_items.quantity) as quantity_sold'),
                DB::raw('SUM(bill_items.line_total) as total_sales'),
                DB::raw('AVG(bill_items.unit_price) as avg_price'),
                DB::raw('AVG(bill_items.unit_price) as average_price')
            )
            ->leftJoin('order_items', 'bill_items.order_item_id', '=', 'order_items.id')
            ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('bills', 'bill_items.bill_id', '=', 'bills.id')
            ->whereIn('bills.status', ['issued', 'partially_paid', 'paid'])
            ->whereBetween('bills.issued_at', [$start, $end])
            ->groupBy('bill_items.item_name')
            ->orderByDesc('total_qty')
            ->take($limit)
            ->get();
    }

    /**
     * Compute restaurant dining operational and table statistics.
     */
    public function getOrderStatistics(Carbon $startDate, Carbon $endDate, float $grossSales = 0.00, int $billsCount = 0): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $visitsQuery = TableVisit::whereBetween('opened_at', [$start, $end]);

        $totalVisits = (clone $visitsQuery)->count();
        $completedVisits = (clone $visitsQuery)->where('status', 'completed')->count();
        $activeVisits = (clone $visitsQuery)->whereIn('status', ['active', 'billed'])->count();
        $totalGuestsSeated = (int) (clone $visitsQuery)->sum('guest_count');

        $totalKotOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->count();

        $avgGuestsPerTable = $totalVisits > 0
            ? round($totalGuestsSeated / $totalVisits, 1)
            : 0.0;

        $avgSpendPerGuest = $totalGuestsSeated > 0
            ? round($grossSales / $totalGuestsSeated, 2)
            : 0.00;

        return [
            'total_visits' => $totalVisits,
            'completed_visits' => $completedVisits,
            'active_visits' => $activeVisits,
            'total_guests' => $totalGuestsSeated,
            'total_kot_orders' => $totalKotOrders,
            'avg_guests_per_table' => $avgGuestsPerTable,
            'avg_spend_per_guest' => $avgSpendPerGuest,
        ];
    }

    /**
     * Generate daily series for Chart.js visualization and daily reconciliation tables.
     */
    public function getDailyTrendData(Carbon $startDate, Carbon $endDate): array
    {
        $days = [];
        $current = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        // Aggregate daily collections via MySQL
        $collectionsByDay = Payment::successful()
            ->whereBetween('paid_at', [$startDate->copy()->startOfDay(), $end])
            ->select(DB::raw('DATE(paid_at) as pay_date'), DB::raw('SUM(amount) as day_total'))
            ->groupBy('pay_date')
            ->pluck('day_total', 'pay_date')
            ->toArray();

        // Aggregate daily refunds via MySQL
        $refundsByDay = Refund::successful()
            ->whereBetween('refunded_at', [$startDate->copy()->startOfDay(), $end])
            ->select(DB::raw('DATE(refunded_at) as ref_date'), DB::raw('SUM(amount) as day_total'))
            ->groupBy('ref_date')
            ->pluck('day_total', 'ref_date')
            ->toArray();

        // Aggregate daily billed sales via MySQL
        $billedByDay = Bill::billedSales()
            ->whereBetween('issued_at', [$startDate->copy()->startOfDay(), $end])
            ->select(DB::raw('DATE(issued_at) as bill_date'), DB::raw('SUM(final_amount) as day_total'))
            ->groupBy('bill_date')
            ->pluck('day_total', 'bill_date')
            ->toArray();

        while ($current->lte($end)) {
            $dateKey = $current->format('Y-m-d');
            $collections = (float) ($collectionsByDay[$dateKey] ?? 0.00);
            $billed = (float) ($billedByDay[$dateKey] ?? 0.00);
            $refunds = (float) ($refundsByDay[$dateKey] ?? 0.00);
            $net = max(0.00, round($collections - $refunds, 2));

            $days[] = [
                'date' => $dateKey,
                'day' => $current->format('D'),
                'collections' => $collections,
                'billed_sales' => $billed,
                'refunds' => $refunds,
                'net_collections' => $net,
            ];

            $current->addDays(1);
        }

        return $days;
    }

    /**
     * Intraday hourly breakdown for daily reports.
     */
    public function getHourlyCollections(Carbon $startDate, Carbon $endDate): array
    {
        $hourly = Payment::successful()
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->select(DB::raw('HOUR(paid_at) as pay_hour'), DB::raw('SUM(amount) as hour_total'), DB::raw('COUNT(*) as tx_count'))
            ->groupBy('pay_hour')
            ->pluck('hour_total', 'pay_hour')
            ->toArray();

        $result = [];

        // 8 AM to 11 PM restaurant operating hours
        for ($h = 8; $h <= 23; $h++) {
            $hourLabel = Carbon::createFromTime($h, 0)->format('h A');
            $result[] = [
                'hour' => $h,
                'label' => $hourLabel,
                'collections' => (float) ($hourly[$h] ?? 0.00),
            ];
        }

        return $result;
    }

    /**
     * Export complete financial report as native CSV via StreamedResponse.
     * Complies with lean dependency rule (no external packages required).
     */
    public function exportCsv(Carbon $startDate, Carbon $endDate, string $periodLabel): StreamedResponse
    {
        $report = $this->getCustomRangeReport($startDate, $endDate);
        $filename = 'sai_biroba_financial_report_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($report, $periodLabel) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compliance
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Banner
            fputcsv($handle, ['SAI BIROBA VEG & NON-VEG RESTAURANT']);
            fputcsv($handle, ['FINANCIAL REPORT & COMMERCIAL AUDIT (NON-GST)']);
            fputcsv($handle, ['Report Period', $periodLabel]);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Report Type', strtoupper($report['report_type'] ?? 'CUSTOM')]);
            fputcsv($handle, []);

            // 1. Executive Summary KPIs
            fputcsv($handle, ['--- EXECUTIVE SUMMARY KPIS ---']);
            fputcsv($handle, ['Metric', 'Amount (INR)', 'Definition & Rule']);
            fputcsv($handle, ['Gross Collections', number_format($report['gross_collections'] ?? 0, 2, '.', ''), 'Sum of all successful payments (Cash + Digital)']);
            fputcsv($handle, ['Successful Refunds', number_format($report['successful_refunds'] ?? 0, 2, '.', ''), 'Total settled customer refunds']);
            fputcsv($handle, ['Net Collections', number_format($report['net_collections'] ?? 0, 2, '.', ''), 'Gross Collections minus Successful Refunds']);
            fputcsv($handle, ['Gross Billed Sales', number_format($report['gross_billed_sales'] ?? 0, 2, '.', ''), 'Total final amount from issued non-voided bills']);
            fputcsv($handle, ['Credit Notes Issued', number_format($report['credit_notes_total'] ?? $report['total_credit_notes'] ?? 0, 2, '.', ''), 'Total credit notes issued against bills']);
            fputcsv($handle, ['Net Billed Sales', number_format($report['net_billed_sales'] ?? 0, 2, '.', ''), 'Gross Billed Sales minus Credit Notes']);
            fputcsv($handle, ['Total Discounts', number_format($report['total_discounts'] ?? 0, 2, '.', ''), 'Managerial & promotional discounts granted']);
            fputcsv($handle, ['Total GST Tax', '0.00', 'Strict Non-GST Commercial Policy']);
            fputcsv($handle, ['Outstanding Balance', number_format($report['outstanding_balance'] ?? 0, 2, '.', ''), 'Uncollected balance on issued bills']);
            fputcsv($handle, []);

            // 2. Payment Method Distribution
            fputcsv($handle, ['--- PAYMENT METHOD BREAKDOWN (COLLECTIONS) ---']);
            fputcsv($handle, ['Payment Method', 'Transactions', 'Amount (INR)', 'Share (%)']);
            foreach ($report['payment_methods'] as $m) {
                fputcsv($handle, [
                    $m['label'],
                    $m['transaction_count'] ?? $m['count'] ?? 0,
                    number_format($m['amount'] ?? $m['total_amount'] ?? 0, 2, '.', ''),
                    ($m['percentage'] ?? 0) . '%',
                ]);
            }
            fputcsv($handle, []);

            // 3. Operational & Bill Statistics
            fputcsv($handle, ['--- ORDER & INVOICE STATISTICS ---']);
            fputcsv($handle, ['Statistic', 'Count / Value']);
            fputcsv($handle, ['Total Bills Issued', $report['total_bills_issued'] ?? 0]);
            fputcsv($handle, ['Fully Paid Bills', $report['paid_bills_count'] ?? 0]);
            fputcsv($handle, ['Partially Paid Bills', $report['partially_paid_bills_count'] ?? 0]);
            fputcsv($handle, ['Unpaid / Issued Bills', $report['unpaid_bills_count'] ?? 0]);
            fputcsv($handle, ['Voided Bills', $report['voided_bills_count'] ?? 0]);
            fputcsv($handle, ['Average Bill Value (AOV)', 'INR ' . number_format($report['avg_bill_value'] ?? $report['average_order_value'] ?? 0, 2, '.', '')]);
            fputcsv($handle, ['Total Dining Visits', $report['order_statistics']['total_visits'] ?? $report['order_stats']['total_visits'] ?? 0]);
            fputcsv($handle, ['Total Guests Seated', $report['order_statistics']['total_guests'] ?? $report['order_stats']['total_guests'] ?? 0]);
            fputcsv($handle, ['Total KOT Rounds Placed', $report['order_statistics']['total_kot_orders'] ?? $report['order_stats']['total_kot_orders'] ?? 0]);
            fputcsv($handle, []);

            // 4. Best-Selling Food Items
            fputcsv($handle, ['--- TOP SELLING DISHES (REVENUE & VOLUME) ---']);
            fputcsv($handle, ['Dish Name', 'Dietary Type', 'Quantity Sold', 'Total Sales (INR)', 'Avg Unit Price (INR)']);
            $bestDishes = $report['best_sellers'] ?? $report['best_selling_dishes'] ?? collect();
            foreach ($bestDishes as $dish) {
                fputcsv($handle, [
                    $dish->item_name,
                    strtoupper($dish->dietary_type ?? $dish->food_type ?? 'ITEM'),
                    $dish->quantity_sold ?? $dish->total_qty ?? 0,
                    number_format($dish->total_sales ?? 0, 2, '.', ''),
                    number_format($dish->average_price ?? $dish->avg_price ?? 0, 2, '.', ''),
                ]);
            }
            fputcsv($handle, []);

            // 5. Daily Aggregation Ledger
            fputcsv($handle, ['--- DAILY AGGREGATION LEDGER ---']);
            fputcsv($handle, ['Date', 'Day', 'Collections (INR)', 'Refunds (INR)', 'Net Collections (INR)', 'Billed Sales (INR)']);
            foreach ($report['daily_trend'] as $dayRow) {
                fputcsv($handle, [
                    $dayRow['date'],
                    $dayRow['day'],
                    number_format($dayRow['collections'] ?? 0, 2, '.', ''),
                    number_format($dayRow['refunds'] ?? 0, 2, '.', ''),
                    number_format($dayRow['net_collections'] ?? 0, 2, '.', ''),
                    number_format($dayRow['billed_sales'] ?? 0, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }
}
