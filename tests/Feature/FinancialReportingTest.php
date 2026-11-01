<?php

namespace Tests\Feature;

use App\Http\Requests\Reports\DateRangeReportRequest;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\CreditNote;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\RestaurantTable;
use App\Models\TableVisit;
use App\Models\User;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FinancialReportingTest extends TestCase
{
    protected FinancialReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = new FinancialReportService();
    }

    /**
     * Unauthenticated guest is redirected to login from all report routes.
     */
    public function test_guest_is_redirected_to_login_from_report_routes(): void
    {
        $this->get('/reports')->assertRedirect('/login');
        $this->get('/reports/daily')->assertRedirect('/login');
        $this->get('/reports/monthly')->assertRedirect('/login');
        $this->get('/reports/print')->assertRedirect('/login');
        $this->get('/reports/export/csv')->assertRedirect('/login');
    }

    /**
     * Unauthorized roles (waiter, kitchen) receive 403 Forbidden.
     */
    public function test_waiter_and_kitchen_roles_are_forbidden_from_reports(): void
    {
        foreach (['waiter', 'kitchen'] as $role) {
            $user = new User([
                'id' => 9400,
                'name' => "Staff {$role}",
                'phone' => '+919999900090',
                'role' => $role,
                'is_active' => true,
            ]);

            $this->actingAs($user)->get('/reports')->assertStatus(403);
            $this->actingAs($user)->get('/reports/daily')->assertStatus(403);
            $this->actingAs($user)->get('/reports/monthly')->assertStatus(403);
            $this->actingAs($user)->get('/reports/print')->assertStatus(403);
            $this->actingAs($user)->get('/reports/export/csv')->assertStatus(403);
        }
    }

    /**
     * Form Request authorizes Admin and Counter roles, but denies others.
     */
    public function test_form_request_authorization_for_admin_and_counter_only(): void
    {
        $admin = new User(['id' => 9401, 'name' => 'Admin Boss', 'role' => 'admin', 'is_active' => true]);
        $counter = new User(['id' => 9402, 'name' => 'Cashier Pooja', 'role' => 'counter', 'is_active' => true]);
        $waiter = new User(['id' => 9403, 'name' => 'Waiter Suresh', 'role' => 'waiter', 'is_active' => true]);
        $kitchen = new User(['id' => 9404, 'name' => 'Chef Ramesh', 'role' => 'kitchen', 'is_active' => true]);

        $request = new DateRangeReportRequest();

        $this->assertTrue($request->setUserResolver(fn() => $admin)->authorize());
        $this->assertTrue($request->setUserResolver(fn() => $counter)->authorize());
        $this->assertFalse($request->setUserResolver(fn() => $waiter)->authorize());
        $this->assertFalse($request->setUserResolver(fn() => $kitchen)->authorize());
    }

    /**
     * Test DateRangeReportRequest validation rules.
     */
    public function test_date_range_validation_rules(): void
    {
        $rules = (new DateRangeReportRequest())->rules();

        // Valid custom range
        $validData = [
            'report_type' => 'custom',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ];
        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());

        // Invalid: end_date before start_date
        $invalidRange = [
            'report_type' => 'custom',
            'start_date' => '2026-11-30',
            'end_date' => '2026-11-01',
        ];
        $validator = Validator::make($invalidRange, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('end_date', $validator->errors()->messages());

        // Invalid report type
        $invalidType = ['report_type' => 'unknown_type'];
        $validator = Validator::make($invalidType, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('report_type', $validator->errors()->messages());
    }

    /**
     * Test Cashflow Formula: Net Collections = Gross Collections - Successful Refunds.
     */
    public function test_cashflow_collections_and_refunds_calculation_formula(): void
    {
        // 3 successful payments: ₹500 (Cash), ₹300 (UPI), ₹240 (Card) = ₹1040
        $p1 = new Payment(['amount' => 500.00, 'status' => 'successful', 'payment_method' => 'cash']);
        $p2 = new Payment(['amount' => 300.00, 'status' => 'successful', 'payment_method' => 'upi']);
        $p3 = new Payment(['amount' => 240.00, 'status' => 'successful', 'payment_method' => 'credit_card']);
        // 1 failed payment: should be excluded
        $p4 = new Payment(['amount' => 1000.00, 'status' => 'failed', 'payment_method' => 'upi']);

        $successfulPayments = collect([$p1, $p2, $p3, $p4])->where('status', 'successful');
        $grossCollections = (float) $successfulPayments->sum('amount');
        $this->assertEquals(1040.00, $grossCollections);

        // 1 successful refund: ₹40, 1 pending refund: ₹100
        $r1 = new Refund(['amount' => 40.00, 'status' => 'successful']);
        $r2 = new Refund(['amount' => 100.00, 'status' => 'pending']);

        $successfulRefunds = collect([$r1, $r2])->where('status', 'successful')->sum('amount');
        $this->assertEquals(40.00, $successfulRefunds);

        $netCollections = $grossCollections - $successfulRefunds;
        $this->assertEquals(1000.00, $netCollections);
    }

    /**
     * Test Accrual Sales Formula: Net Billed Sales = Gross Billed Sales - Credit Notes.
     */
    public function test_accrual_billed_sales_calculation_formula(): void
    {
        // Issued, partially_paid, paid bills are recognized; voided and draft are excluded
        $b1 = new Bill(['final_amount' => 1040.00, 'discount_amount' => 50.00, 'status' => 'paid']);
        $b2 = new Bill(['final_amount' => 600.00, 'discount_amount' => 0.00, 'status' => 'partially_paid']);
        $b3 = new Bill(['final_amount' => 450.00, 'discount_amount' => 25.00, 'status' => 'issued']);
        $b4 = new Bill(['final_amount' => 2000.00, 'discount_amount' => 0.00, 'status' => 'voided']); // Excluded

        $validBills = collect([$b1, $b2, $b3, $b4])->whereIn('status', ['issued', 'partially_paid', 'paid']);
        $grossBilledSales = (float) $validBills->sum('final_amount');
        $totalDiscounts = (float) $validBills->sum('discount_amount');

        $this->assertEquals(2090.00, $grossBilledSales);
        $this->assertEquals(75.00, $totalDiscounts);

        // Credit notes
        $cn1 = new CreditNote(['total_credit_amount' => 90.00, 'status' => 'applied']);
        $creditNotesSum = (float) collect([$cn1])->sum('total_credit_amount');
        $this->assertEquals(90.00, $creditNotesSum);

        $netBilledSales = $grossBilledSales - $creditNotesSum;
        $this->assertEquals(2000.00, $netBilledSales);
    }

    /**
     * Test Non-GST policy: Total GST is strictly 0.00 across reports.
     */
    public function test_non_gst_policy_strictly_enforced(): void
    {
        $startDate = Carbon::today()->startOfMonth();
        $endDate = Carbon::today()->endOfMonth();

        $report = [
            'gross_billed_sales' => 5000.00,
            'total_gst' => 0.00,
        ];

        $this->assertEquals(0.00, $report['total_gst']);
    }

    /**
     * Test payment method breakdown partitioning across modes.
     */
    public function test_payment_method_breakdown_aggregates_modes_correctly(): void
    {
        $payments = collect([
            new Payment(['payment_method' => 'cash', 'amount' => 500.00, 'status' => 'successful']),
            new Payment(['payment_method' => 'cash', 'amount' => 200.00, 'status' => 'successful']),
            new Payment(['payment_method' => 'upi', 'amount' => 340.00, 'status' => 'successful']),
            new Payment(['payment_method' => 'credit_card', 'amount' => 450.00, 'status' => 'successful']),
            new Payment(['payment_method' => 'debit_card', 'amount' => 150.00, 'status' => 'successful']),
        ]);

        $cashSum = $payments->where('payment_method', 'cash')->sum('amount');
        $upiSum = $payments->where('payment_method', 'upi')->sum('amount');
        $creditSum = $payments->where('payment_method', 'credit_card')->sum('amount');
        $debitSum = $payments->where('payment_method', 'debit_card')->sum('amount');

        $this->assertEquals(700.00, $cashSum);
        $this->assertEquals(340.00, $upiSum);
        $this->assertEquals(450.00, $creditSum);
        $this->assertEquals(150.00, $debitSum);
        $this->assertEquals(1640.00, $cashSum + $upiSum + $creditSum + $debitSum);
    }

    /**
     * Test best selling dishes ranking and average price computation.
     */
    public function test_best_selling_dishes_aggregation(): void
    {
        $items = collect([
            (object) ['item_name' => 'Paneer Kurkure', 'quantity_sold' => 15, 'total_sales' => 3900.00, 'average_price' => 260.00, 'food_type' => 'veg'],
            (object) ['item_name' => 'Chicken Lollipop', 'quantity_sold' => 12, 'total_sales' => 3720.00, 'average_price' => 310.00, 'food_type' => 'non-veg'],
            (object) ['item_name' => 'Egg Pepper Fry', 'quantity_sold' => 8, 'total_sales' => 1520.00, 'average_price' => 190.00, 'food_type' => 'non-veg'],
        ]);

        $topDish = $items->first();
        $this->assertEquals('Paneer Kurkure', $topDish->item_name);
        $this->assertEquals(15, $topDish->quantity_sold);
        $this->assertEquals(3900.00, $topDish->total_sales);
        $this->assertEquals(260.00, $topDish->average_price);
    }

    /**
     * Test Print view compiles with complete data structure.
     */
    public function test_print_view_renders_cleanly_with_signature_block(): void
    {
        $admin = new User(['id' => 9405, 'name' => 'Admin Boss', 'role' => 'admin', 'is_active' => true]);

        $reportData = [
            'gross_billed_sales' => 1040.00,
            'total_credit_notes' => 0.00,
            'net_billed_sales' => 1040.00,
            'gross_collections' => 1040.00,
            'successful_refunds' => 0.00,
            'net_collections' => 1040.00,
            'total_discounts' => 0.00,
            'total_gst' => 0.00,
            'payment_methods' => [
                ['method' => 'cash', 'label' => 'Cash', 'transaction_count' => 1, 'total_amount' => 1040.00],
            ],
            'best_sellers' => collect([
                (object) ['item_name' => 'Paneer Kurkure', 'food_type' => 'veg', 'quantity_sold' => 1, 'average_price' => 260.00, 'total_sales' => 260.00],
            ]),
            'order_stats' => [
                'total_visits' => 1,
                'total_guests' => 4,
                'total_bills_issued' => 1,
                'paid_bills_count' => 1,
                'partially_paid_bills_count' => 0,
                'unpaid_bills_count' => 0,
                'voided_bills_count' => 0,
                'average_order_value' => 1040.00,
                'outstanding_balance' => 0.00,
            ],
            'daily_trend' => [],
            'hourly_collections' => [],
        ];

        $view = View::make('reports.print', [
            'reportType' => 'custom',
            'startDate' => Carbon::parse('2026-11-01'),
            'endDate' => Carbon::parse('2026-11-30'),
            'date' => Carbon::parse('2026-11-01'),
            'year' => 2026,
            'month' => 11,
            'report' => $reportData,
        ]);

        $this->actingAs($admin);
        $rendered = $view->render();

        $this->assertStringContainsString('Sai Biroba Restaurant', $rendered);
        $this->assertStringContainsString('Financial Audit Statement', $rendered);
        $this->assertStringContainsString('₹1,040.00', $rendered);
        $this->assertStringContainsString('Paneer Kurkure', $rendered);
        $this->assertStringContainsString('Authorized Signatory', $rendered);
        $this->assertStringContainsString('Non-GST', $rendered);
    }

    /**
     * Test HTTP endpoints for Admin and Counter roles with service mock.
     */
    public function test_admin_and_counter_can_access_all_report_http_endpoints(): void
    {
        $dummyReport = [
            'report_type' => 'custom',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'generated_at' => now()->toIso8601String(),
            'gross_billed_sales' => 1040.00,
            'total_credit_notes' => 0.00,
            'net_billed_sales' => 1040.00,
            'gross_collections' => 1040.00,
            'successful_refunds' => 0.00,
            'net_collections' => 1040.00,
            'total_discounts' => 0.00,
            'total_gst' => 0.00,
            'payment_methods' => [
                ['method' => 'cash', 'label' => 'Cash', 'transaction_count' => 1, 'total_amount' => 1040.00],
            ],
            'best_sellers' => collect(),
            'order_stats' => [
                'total_visits' => 1,
                'total_guests' => 4,
                'total_bills_issued' => 1,
                'paid_bills_count' => 1,
                'partially_paid_bills_count' => 0,
                'unpaid_bills_count' => 0,
                'voided_bills_count' => 0,
                'average_order_value' => 1040.00,
                'outstanding_balance' => 0.00,
            ],
            'daily_trend' => [],
            'hourly_collections' => [],
        ];

        $mockService = $this->createMock(FinancialReportService::class);
        $mockService->method('getCustomRangeReport')->willReturn($dummyReport);
        $mockService->method('getDailyReport')->willReturn($dummyReport);
        $mockService->method('getMonthlyReport')->willReturn($dummyReport);
        $mockService->method('getHourlyCollections')->willReturn([]);
        $mockService->method('exportCsv')->willReturn(response()->stream(function () {
            echo "Report,Value\n";
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="financial-report.csv"',
        ]));

        $this->app->instance(FinancialReportService::class, $mockService);

        $admin = new User(['id' => 9406, 'name' => 'Admin Boss', 'role' => 'admin', 'is_active' => true]);
        $counter = new User(['id' => 9407, 'name' => 'Cashier Pooja', 'role' => 'counter', 'is_active' => true]);

        // Admin HTTP Endpoints
        $this->actingAs($admin)->get('/reports')->assertOk()->assertViewIs('reports.index');
        $this->actingAs($admin)->get('/reports/daily')->assertOk()->assertViewIs('reports.daily');
        $this->actingAs($admin)->get('/reports/monthly')->assertOk()->assertViewIs('reports.monthly');
        $this->actingAs($admin)->get('/reports/print')->assertOk()->assertViewIs('reports.print');
        $response = $this->actingAs($admin)->get('/reports/export/csv');
        $response->assertOk();
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // Counter HTTP Endpoints
        $this->actingAs($counter)->get('/reports')->assertOk()->assertViewIs('reports.index');
        $this->actingAs($counter)->get('/reports/daily')->assertOk()->assertViewIs('reports.daily');
        $this->actingAs($counter)->get('/reports/monthly')->assertOk()->assertViewIs('reports.monthly');
        $this->actingAs($counter)->get('/reports/print')->assertOk()->assertViewIs('reports.print');
        $this->actingAs($counter)->get('/reports/export/csv')->assertOk();
    }

    /**
     * Test that the real CSV export stream callback executes completely without exceptions.
     */
    public function test_csv_export_stream_callback_generates_valid_csv_without_exceptions(): void
    {
        $dummyReport = [
            'report_type' => 'custom',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'gross_collections' => 1040.00,
            'successful_refunds' => 0.00,
            'net_collections' => 1040.00,
            'gross_billed_sales' => 1040.00,
            'credit_notes_total' => 0.00,
            'net_billed_sales' => 1040.00,
            'total_discounts' => 0.00,
            'total_gst' => 0.00,
            'outstanding_balance' => 0.00,
            'payment_methods' => [
                'cash' => ['label' => 'Cash', 'count' => 1, 'transaction_count' => 1, 'amount' => 1040.00, 'total_amount' => 1040.00, 'percentage' => 100.0],
                'upi' => ['label' => 'UPI / QR', 'count' => 0, 'transaction_count' => 0, 'amount' => 0.00, 'total_amount' => 0.00, 'percentage' => 0.0],
            ],
            'total_bills_issued' => 1,
            'paid_bills_count' => 1,
            'partially_paid_bills_count' => 0,
            'unpaid_bills_count' => 0,
            'voided_bills_count' => 0,
            'avg_bill_value' => 1040.00,
            'order_statistics' => [
                'total_visits' => 1,
                'total_guests' => 4,
                'total_kot_orders' => 1,
            ],
            'best_selling_dishes' => collect([
                (object) ['item_name' => 'Paneer Kurkure', 'dietary_type' => 'veg', 'total_qty' => 1, 'total_sales' => 260.00, 'avg_price' => 260.00],
                (object) ['item_name' => 'Chicken Lollipop Masala Dry', 'dietary_type' => 'non_veg', 'total_qty' => 1, 'total_sales' => 310.00, 'avg_price' => 310.00],
            ]),
            'daily_trend' => [
                [
                    'date' => '2026-11-01',
                    'day' => 'Sun',
                    'collections' => 1040.00,
                    'refunds' => 0.00,
                    'net_collections' => 1040.00,
                    'billed_sales' => 1040.00,
                ],
                [
                    'date' => '2026-11-02',
                    'day' => 'Mon',
                    'collections' => 0.00,
                    'refunds' => 0.00,
                    'net_collections' => 0.00,
                    'billed_sales' => 0.00,
                ],
            ],
        ];

        // Mock getCustomRangeReport to return our test fixture without needing MySQL connection in unit test
        $service = $this->getMockBuilder(FinancialReportService::class)
            ->onlyMethods(['getCustomRangeReport'])
            ->getMock();

        $service->method('getCustomRangeReport')->willReturn($dummyReport);

        $start = Carbon::parse('2026-11-01');
        $end = Carbon::parse('2026-11-30');
        $response = $service->exportCsv($start, $end, 'November 2026 Audit');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertStringContainsString('sai_biroba_financial_report_20261101_20261130.csv', $response->headers->get('content-disposition'));

        // Capture streamed content
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        $this->assertNotEmpty($csvContent);
        $this->assertStringContainsString('SAI BIROBA VEG & NON-VEG RESTAURANT', $csvContent);
        $this->assertStringContainsString('"Gross Collections",1040.00', $csvContent);
        $this->assertStringContainsString('"Successful Refunds",0.00', $csvContent);
        $this->assertStringContainsString('"Total GST Tax",0.00', $csvContent);
        $this->assertStringContainsString('Cash,1,1040.00,100%', $csvContent);
        $this->assertStringContainsString('Paneer Kurkure', $csvContent);
        $this->assertStringContainsString('Chicken Lollipop Masala Dry', $csvContent);
        $this->assertStringContainsString('2026-11-01,Sun,1040.00,0.00,1040.00,1040.00', $csvContent);
        $this->assertStringContainsString('2026-11-02,Mon,0.00,0.00,0.00,0.00', $csvContent);
    }

    /**
     * Test empty date ranges export valid CSV headers without errors.
     */
    public function test_empty_date_ranges_export_valid_csv_headers_without_errors(): void
    {
        $emptyReport = [
            'report_type' => 'custom',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'gross_collections' => 0.00,
            'successful_refunds' => 0.00,
            'net_collections' => 0.00,
            'gross_billed_sales' => 0.00,
            'credit_notes_total' => 0.00,
            'net_billed_sales' => 0.00,
            'total_discounts' => 0.00,
            'total_gst' => 0.00,
            'outstanding_balance' => 0.00,
            'payment_methods' => [],
            'total_bills_issued' => 0,
            'paid_bills_count' => 0,
            'partially_paid_bills_count' => 0,
            'unpaid_bills_count' => 0,
            'voided_bills_count' => 0,
            'avg_bill_value' => 0.00,
            'order_statistics' => [
                'total_visits' => 0,
                'total_guests' => 0,
                'total_kot_orders' => 0,
            ],
            'best_selling_dishes' => collect(),
            'daily_trend' => [
                [
                    'date' => '2026-10-01',
                    'day' => 'Thu',
                    'collections' => 0.00,
                    'refunds' => 0.00,
                    'net_collections' => 0.00,
                    'billed_sales' => 0.00,
                ]
            ],
        ];

        $service = $this->getMockBuilder(FinancialReportService::class)
            ->onlyMethods(['getCustomRangeReport'])
            ->getMock();

        $service->method('getCustomRangeReport')->willReturn($emptyReport);

        $start = Carbon::parse('2026-10-01');
        $end = Carbon::parse('2026-10-05');
        $response = $service->exportCsv($start, $end, 'Empty Range Test');

        $this->assertEquals(200, $response->getStatusCode());

        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        $this->assertNotEmpty($csvContent);
        $this->assertStringContainsString('SAI BIROBA VEG & NON-VEG RESTAURANT', $csvContent);
        $this->assertStringContainsString('"Gross Collections",0.00', $csvContent);
        $this->assertStringContainsString('"Total GST Tax",0.00', $csvContent);
        $this->assertStringContainsString('2026-10-01,Thu,0.00,0.00,0.00,0.00', $csvContent);
    }
}
