<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reports\DateRangeReportRequest;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    /**
     * Financial Reports Hub: supports custom range, daily, and monthly views.
     */
    public function index(DateRangeReportRequest $request): View
    {
        $user = $request->user();
        $type = $request->input('report_type', 'custom');

        if ($type === 'daily') {
            $date = $request->filled('date') ? Carbon::parse($request->input('date')) : today();
            $report = $this->reportService->getDailyReport($date);
            return view('reports.daily', [
                'report' => $report,
                'user' => $user,
                'date' => $date,
            ]);
        }

        if ($type === 'monthly') {
            $year = (int) $request->input('year', date('Y'));
            $month = (int) $request->input('month', date('n'));
            $report = $this->reportService->getMonthlyReport($year, $month);
            return view('reports.monthly', [
                'report' => $report,
                'user' => $user,
                'year' => $year,
                'month' => $month,
            ]);
        }

        // Default: Custom Date Range (defaults to Current Month to date)
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : today()->startOfMonth();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : today();

        $report = $this->reportService->getCustomRangeReport($start, $end);

        return view('reports.index', [
            'report' => $report,
            'user' => $user,
            'reportType' => $type,
            'startDate' => $start,
            'endDate' => $end,
            'date' => today(),
            'year' => (int) date('Y'),
            'month' => (int) date('n'),
        ]);
    }

    /**
     * Dedicated Daily Financial Ledger.
     */
    public function daily(Request $request): View
    {
        $user = $request->user();
        $date = $request->filled('date') ? Carbon::parse($request->query('date')) : today();
        $report = $this->reportService->getDailyReport($date);

        return view('reports.daily', [
            'report' => $report,
            'user' => $user,
            'date' => $date,
            'hourlyCollections' => $report['hourly_collections'] ?? [],
        ]);
    }

    /**
     * Dedicated Monthly Financial Ledger.
     */
    public function monthly(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));
        $report = $this->reportService->getMonthlyReport($year, $month);

        return view('reports.monthly', [
            'report' => $report,
            'user' => $user,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Clean printable financial statement for management records (@media print & PDF).
     */
    public function print(DateRangeReportRequest $request): View
    {
        $user = $request->user();
        $type = $request->input('report_type', 'custom');

        if ($type === 'daily') {
            $date = $request->filled('date') ? Carbon::parse($request->input('date')) : today();
            $report = $this->reportService->getDailyReport($date);
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $year = $date->year;
            $month = $date->month;
        } elseif ($type === 'monthly') {
            $year = (int) $request->input('year', date('Y'));
            $month = (int) $request->input('month', date('n'));
            $report = $this->reportService->getMonthlyReport($year, $month);
            $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->endOfDay();
            $date = $start;
        } else {
            $start = $request->filled('start_date') ? Carbon::parse($request->input('start_date')) : today()->startOfMonth();
            $end = $request->filled('end_date') ? Carbon::parse($request->input('end_date')) : today();
            $report = $this->reportService->getCustomRangeReport($start, $end);
            $date = today();
            $year = (int) date('Y');
            $month = (int) date('n');
        }

        return view('reports.print', [
            'report' => $report,
            'user' => $user,
            'reportType' => $type,
            'startDate' => $start,
            'endDate' => $end,
            'date' => $date,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Export complete financial report as native CSV.
     */
    public function exportCsv(DateRangeReportRequest $request): StreamedResponse
    {
        $type = $request->input('report_type', 'custom');

        if ($type === 'daily') {
            $date = $request->filled('date') ? Carbon::parse($request->input('date')) : today();
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $label = 'Daily Report (' . $date->format('d M Y') . ')';
        } elseif ($type === 'monthly') {
            $year = (int) $request->input('year', date('Y'));
            $month = (int) $request->input('month', date('n'));
            $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->endOfDay();
            $label = 'Monthly Report (' . $start->format('F Y') . ')';
        } else {
            $start = $request->filled('start_date') ? Carbon::parse($request->input('start_date')) : today()->startOfMonth();
            $end = $request->filled('end_date') ? Carbon::parse($request->input('end_date')) : today();
            $label = 'Custom Range (' . $start->format('d M Y') . ' - ' . $end->format('d M Y') . ')';
        }

        return $this->reportService->exportCsv($start, $end, $label);
    }
}
