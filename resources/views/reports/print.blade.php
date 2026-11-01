<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financial Statement — {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }} — Sai Biroba</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1a1a1a;
            background-color: #f7f7f8;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 32px 36px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .no-print {
            max-width: 900px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-primary {
            background-color: #121212;
            color: #ffffff;
        }
        .btn-secondary {
            background-color: #ffffff;
            color: #374151;
            border-color: #d1d5db;
        }
        .btn-primary:hover { background-color: #2a2a2a; }
        .btn-secondary:hover { background-color: #f9fafb; }

        /* Document Header */
        .header {
            border-bottom: 2px solid #111827;
            padding-bottom: 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .restaurant-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #111827;
            text-transform: uppercase;
        }
        .restaurant-sub {
            font-size: 11px;
            font-weight: 600;
            color: #854d0e;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .statement-title {
            text-align: right;
        }
        .statement-title h1 {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .statement-title p {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Metadata Banner */
        .meta-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px 16px;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .meta-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .meta-value {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            margin-top: 2px;
        }

        /* Sections */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
            margin-bottom: 12px;
            margin-top: 20px;
        }

        /* KPI Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .kpi-box {
            border: 1px solid #d1d5db;
            padding: 12px;
            border-radius: 4px;
        }
        .kpi-box.highlight {
            background: #fefce8;
            border-color: #ca8a04;
        }
        .kpi-box-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            color: #4b5563;
        }
        .kpi-box-value {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
            margin-top: 4px;
            font-family: "Courier New", Courier, monospace;
        }
        .kpi-box-sub {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 16px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        td {
            padding: 7px 10px;
            border: 1px solid #e5e7eb;
            color: #1f2937;
        }
        tr:nth-child(even) td {
            background-color: #fcfcfc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: "Courier New", Courier, monospace; }
        .font-bold { font-weight: 700; }

        /* Signatures */
        .signature-section {
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            text-align: center;
        }
        .sig-line {
            border-top: 1px dashed #9ca3af;
            margin-top: 44px;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #374151;
        }
        .sig-role {
            font-size: 10px;
            color: #6b7280;
        }

        /* Non-GST Notice */
        .tax-notice {
            margin-top: 20px;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #f3f4f6;
            padding-top: 10px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                font-size: 10.5px;
            }
            .container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .kpi-box.highlight {
                background: #fffbeb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <!-- Controls Bar (Screen Only) -->
    <div class="no-print">
        <a href="{{ url()->previous() ?: route('reports.index') }}" class="btn btn-secondary">
            &larr; Back
        </a>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                Print Statement / Save PDF
            </button>
        </div>
    </div>

    <!-- Printable Paper Container -->
    <div class="container">

        <!-- Restaurant & Statement Header -->
        <div class="header">
            <div>
                <div class="restaurant-name">Sai Biroba Restaurant</div>
                <div class="restaurant-sub">Pure Veg &amp; Authentic Non-Veg Dining</div>
                <div style="font-size: 11px; color: #4b5563; margin-top: 4px;">
                    Commercial Dining Operations &bull; Non-GST Commercial Enterprise
                </div>
            </div>
            <div class="statement-title">
                <h1>Financial Audit Statement</h1>
                <p>Official Revenue Reconciliation</p>
                <p style="font-weight: 600; color: #111827; margin-top: 4px;">
                    Statement Ref: STMT-{{ now()->format('ymd-His') }}
                </p>
            </div>
        </div>

        <!-- Metadata Strip -->
        <div class="meta-strip">
            <div>
                <div class="meta-label">Audit Period</div>
                <div class="meta-value">{{ $startDate->format('d M Y') }} &ndash; {{ $endDate->format('d M Y') }}</div>
            </div>
            <div>
                <div class="meta-label">Scope Duration</div>
                <div class="meta-value">{{ $startDate->diffInDays($endDate) + 1 }} Calendar {{ Str::plural('Day', $startDate->diffInDays($endDate) + 1) }}</div>
            </div>
            <div>
                <div class="meta-label">Generated By</div>
                <div class="meta-value">{{ auth()->user()->name }} ({{ strtoupper(auth()->user()->role) }})</div>
            </div>
            <div>
                <div class="meta-label">Printed At</div>
                <div class="meta-value">{{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Primary KPI Metrics Grid -->
        <div class="kpi-grid">
            <div class="kpi-box">
                <div class="kpi-box-label">Gross Collections (Inflow)</div>
                <div class="kpi-box-value">₹{{ number_format($report['gross_collections'], 2) }}</div>
                <div class="kpi-box-sub">Total successful cash/digital payments</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-box-label">Successful Refunds</div>
                <div class="kpi-box-value">₹{{ number_format($report['successful_refunds'], 2) }}</div>
                <div class="kpi-box-sub">Disbursed customer reversals</div>
            </div>
            <div class="kpi-box highlight">
                <div class="kpi-box-label" style="color: #854d0e;">Net Collections (Retained)</div>
                <div class="kpi-box-value" style="color: #854d0e;">₹{{ number_format($report['net_collections'], 2) }}</div>
                <div class="kpi-box-sub" style="color: #a16207; font-weight: 600;">Actual Banked Restaurant Funds</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-box-label">Gross Billed Sales</div>
                <div class="kpi-box-value">₹{{ number_format($report['gross_billed_sales'], 2) }}</div>
                <div class="kpi-box-sub">Accrual value of issued invoices</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-box-label">Credit Notes Issued</div>
                <div class="kpi-box-value">₹{{ number_format($report['total_credit_notes'], 2) }}</div>
                <div class="kpi-box-sub">Post-billing invoice adjustments</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-box-label">Net Billed Sales</div>
                <div class="kpi-box-value">₹{{ number_format($report['net_billed_sales'], 2) }}</div>
                <div class="kpi-box-sub">Gross Billed &minus; Credit Notes</div>
            </div>
        </div>

        <!-- Supplementary Financials Summary Table -->
        <div class="section-title">Accounting Breakdown &amp; Concessions</div>
        <table>
            <thead>
                <tr>
                    <th>Ledger Description</th>
                    <th>Accounting Basis</th>
                    <th>Classification</th>
                    <th class="text-right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Gross Collections</strong> (Cash, UPI, Cards)</td>
                    <td>Cashflow / Settlement</td>
                    <td>Receipts</td>
                    <td class="text-right font-mono font-bold">₹{{ number_format($report['gross_collections'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Disbursed Customer Refunds</strong></td>
                    <td>Cashflow / Settlement</td>
                    <td>Outflow Reversal</td>
                    <td class="text-right font-mono font-bold" style="color: #b91c1c;">&minus; ₹{{ number_format($report['successful_refunds'], 2) }}</td>
                </tr>
                <tr style="background-color: #fefce8;">
                    <td><strong>NET REALIZED COLLECTIONS</strong></td>
                    <td>Cashflow / Bank Inflow</td>
                    <td><strong>Retained Cash</strong></td>
                    <td class="text-right font-mono font-bold" style="color: #854d0e;">₹{{ number_format($report['net_collections'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Gross Billed Invoices</strong></td>
                    <td>Accrual / Issued Invoices</td>
                    <td>Revenue Ledger</td>
                    <td class="text-right font-mono">₹{{ number_format($report['gross_billed_sales'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Approved Concessions &amp; Discounts</strong></td>
                    <td>Promotional Reductions</td>
                    <td>Pre-Tax Concession</td>
                    <td class="text-right font-mono">₹{{ number_format($report['total_discounts'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Commercial GST / Tax</strong></td>
                    <td>Tax Authority</td>
                    <td>Non-GST Operation</td>
                    <td class="text-right font-mono">₹0.00</td>
                </tr>
                <tr>
                    <td><strong>Unsettled Balance (Accounts Receivable)</strong></td>
                    <td>Pending Bills</td>
                    <td>Outstanding</td>
                    <td class="text-right font-mono">₹{{ number_format($report['order_stats']['outstanding_balance'], 2) }}</td>
                </tr>
            </tbody>
        </div>

        <!-- Payment Method Distribution -->
        <div class="section-title">Collections by Payment Method</div>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-right">Total Amount (₹)</th>
                    <th class="text-right">Share of Inflow</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalInflow = $report['gross_collections'] > 0 ? $report['gross_collections'] : 1;
                @endphp
                @forelse($report['payment_methods'] as $pm)
                    <tr>
                        <td><strong>{{ $pm['label'] }}</strong></td>
                        <td class="text-center font-mono">{{ $pm['transaction_count'] }}</td>
                        <td class="text-right font-mono font-bold">₹{{ number_format($pm['total_amount'], 2) }}</td>
                        <td class="text-right font-mono">{{ number_format(($pm['total_amount'] / $totalInflow) * 100, 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color: #6b7280; font-style: italic;">No payment transactions recorded</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f3f4f6; font-weight: 700;">
                    <td>TOTAL GROSS INFLOW</td>
                    <td class="text-center font-mono">{{ array_sum(array_column($report['payment_methods'], 'transaction_count')) }}</td>
                    <td class="text-right font-mono">₹{{ number_format($report['gross_collections'], 2) }}</td>
                    <td class="text-right font-mono">100.0%</td>
                </tr>
            </tfoot>
        </table>

        <!-- Operations & Guest Cover Summary -->
        <div class="section-title">Dining Sessions &amp; Operational Statistics</div>
        <table style="margin-bottom: 24px;">
            <thead>
                <tr>
                    <th>Metric Name</th>
                    <th class="text-right">Quantity / Value</th>
                    <th>Audit Context</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Seated Table Visits</td>
                    <td class="text-right font-mono font-bold">{{ $report['order_stats']['total_visits'] }}</td>
                    <td>Dining room sessions registered</td>
                </tr>
                <tr>
                    <td>Total Guest Covers Served</td>
                    <td class="text-right font-mono font-bold">{{ $report['order_stats']['total_guests'] }}</td>
                    <td>Individual patrons accommodated</td>
                </tr>
                <tr>
                    <td>Consolidated Bills Issued</td>
                    <td class="text-right font-mono font-bold">{{ $report['order_stats']['total_bills_issued'] }}</td>
                    <td>Tax &amp; commercial invoices generated</td>
                </tr>
                <tr>
                    <td>Fully Settled Bills</td>
                    <td class="text-right font-mono font-bold" style="color: #047857;">{{ $report['order_stats']['paid_bills_count'] }}</td>
                    <td>Paid in full and closed</td>
                </tr>
                <tr>
                    <td>Partially Paid Bills</td>
                    <td class="text-right font-mono font-bold" style="color: #7c3aed;">{{ $report['order_stats']['partially_paid_bills_count'] }}</td>
                    <td>Split payments pending remainder</td>
                </tr>
                <tr>
                    <td>Voided / Cancelled Invoices</td>
                    <td class="text-right font-mono font-bold" style="color: #b91c1c;">{{ $report['order_stats']['voided_bills_count'] }}</td>
                    <td>Voided before payment settlement</td>
                </tr>
                <tr>
                    <td>Average Order Value (AOV)</td>
                    <td class="text-right font-mono font-bold">₹{{ number_format($report['order_stats']['average_order_value'], 2) }}</td>
                    <td>Average ticket per finalized bill</td>
                </tr>
            </tbody>
        </table>

        <!-- Top 10 Best Sellers -->
        <div class="section-title">Top 10 Menu Items by Volume</div>
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">Rank</th>
                    <th>Dish Name</th>
                    <th class="text-center">Dietary</th>
                    <th class="text-center">Qty Sold</th>
                    <th class="text-right">Avg Unit Price</th>
                    <th class="text-right">Total Revenue (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['best_sellers'] as $idx => $dish)
                    <tr>
                        <td class="text-center font-mono">{{ $idx + 1 }}</td>
                        <td><strong>{{ $dish->item_name }}</strong></td>
                        <td class="text-center" style="font-size: 10px;">{{ strtoupper($dish->food_type ?? 'ITEM') }}</td>
                        <td class="text-center font-mono font-bold">{{ $dish->quantity_sold }}</td>
                        <td class="text-right font-mono">₹{{ number_format($dish->average_price, 2) }}</td>
                        <td class="text-right font-mono font-bold">₹{{ number_format($dish->total_sales, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: #6b7280; font-style: italic;">No items sold in this period</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Non-GST Notice -->
        <div class="tax-notice">
            Commercial Dining Operations &bull; Sai Biroba Restaurant operates with zero GST (Non-GST enterprise). No input tax credit or GST charged.
        </div>

        <!-- Verification & Signatures -->
        <div class="signature-section">
            <div>
                <div class="sig-line">Prepared By (Staff)</div>
                <div class="sig-role">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</div>
            </div>
            <div>
                <div class="sig-line">Counter Supervisor</div>
                <div class="sig-role">Cash &amp; POS Audit</div>
            </div>
            <div>
                <div class="sig-line">Authorized Signatory</div>
                <div class="sig-role">Management &bull; Sai Biroba</div>
            </div>
        </div>

    </div>

</body>
</html>
