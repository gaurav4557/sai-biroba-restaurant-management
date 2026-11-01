<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $bill->bill_number }} — Sai Biroba Restaurant</title>
    <style>
        /* Base Screen Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #1a1a1a;
            color: #111;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .screen-toolbar {
            width: 100%;
            max-width: 400px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            font-family: sans-serif;
            text-decoration: none;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease-in-out;
        }

        .btn-print {
            background-color: #D4AF37;
            color: #000;
        }

        .btn-print:hover {
            background-color: #c5a059;
        }

        .btn-back {
            background-color: #333;
            color: #ddd;
        }

        .btn-back:hover {
            background-color: #444;
            color: #fff;
        }

        /* 80mm Thermal Receipt Container */
        .thermal-receipt {
            width: 80mm;
            max-width: 80mm;
            background-color: #fff;
            padding: 12px 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            font-size: 11px;
            line-height: 1.35;
            color: #000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        .header-title {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .header-meta {
            font-size: 9px;
            margin-bottom: 6px;
            line-height: 1.25;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .divider-double {
            border-top: 2px double #000;
            margin: 6px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 2px;
        }

        /* Table of Items */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin: 4px 0;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 3px 0;
            font-weight: bold;
        }

        .items-table td {
            padding: 2.5px 0;
            vertical-align: top;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .grand-total {
            font-size: 14px;
            font-weight: 900;
            margin: 4px 0;
        }

        .settle-badge {
            margin: 8px 0;
            padding: 3px 6px;
            border: 1px solid #000;
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 8px;
            font-size: 9px;
            line-height: 1.3;
        }

        /* Print Specific CSS */
        @media print {
            body {
                background-color: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
            }

            .screen-toolbar {
                display: none !important;
            }

            .thermal-receipt {
                width: 78mm !important;
                max-width: 78mm !important;
                box-shadow: none !important;
                padding: 4px 2px !important;
                margin: 0 auto !important;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- On-Screen Action Bar -->
    <div class="screen-toolbar">
        <a href="{{ route('counter.bills.show', $bill) }}" class="btn btn-back">&larr; Back to Bill</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Receipt</button>
    </div>

    <!-- 80mm Thermal Receipt Structure -->
    <div class="thermal-receipt">

        <!-- Restaurant Branding Header -->
        <div class="text-center">
            <div class="header-title">SAI BIROBA</div>
            <div class="header-subtitle">Veg &amp; Non-Veg Restaurant</div>
            <div class="header-meta">
                Authentic Maharashtrian &amp; Indian Cuisine<br>
                Commercial Dine-In &bull; Pune / Nashik Highway<br>
                Phone: +91 98765 43210
            </div>
            <div class="divider"></div>
            <div class="bold" style="font-size: 11px; letter-spacing: 0.5px;">
                RESTAURANT BILL / PAYMENT RECEIPT
            </div>
        </div>

        <div class="divider"></div>

        <!-- Bill & Table Meta -->
        <div class="info-row">
            <span>Bill No: <span class="bold">{{ $bill->bill_number }}</span></span>
            <span>Date: {{ $bill->issued_at ? $bill->issued_at->format('d/m/y') : date('d/m/y') }}</span>
        </div>
        <div class="info-row">
            <span>Table: <span class="bold">{{ $bill->visit->table->table_number ?? '-' }}</span> ({{ $bill->visit->table->floor_section ?? '' }})</span>
            <span>Time: {{ $bill->issued_at ? $bill->issued_at->format('h:i A') : date('h:i A') }}</span>
        </div>
        <div class="info-row">
            <span>Waiter: {{ $bill->visit->waiter->name ?? 'Staff' }}</span>
            <span>Guests: {{ $bill->visit->guest_count ?? 1 }}</span>
        </div>
        <div class="info-row">
            <span>Visit Ref: <span class="font-mono">{{ $bill->visit->visit_code ?? '-' }}</span></span>
            <span>Cashier: {{ $bill->generatedBy->name ?? 'Counter' }}</span>
        </div>

        <div class="divider"></div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 50%;">ITEM</th>
                    <th class="text-center" style="width: 15%;">QTY</th>
                    <th class="text-right" style="width: 17%;">RATE</th>
                    <th class="text-right" style="width: 18%;">AMT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->items as $item)
                    <tr>
                        <td class="text-left bold">{{ $item->item_name }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-right bold">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Summary Totals -->
        <div class="total-row">
            <span>Subtotal:</span>
            <span class="bold">₹{{ number_format((float) $bill->subtotal_amount, 2) }}</span>
        </div>

        @if((float) $bill->discount_amount > 0)
            <div class="total-row">
                <span>Discount ({{ ucfirst($bill->discount_type) }}):</span>
                <span>-₹{{ number_format((float) $bill->discount_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Net Amount:</span>
                <span>₹{{ number_format((float) $bill->taxable_amount, 2) }}</span>
            </div>
        @endif

        @if((float) $bill->service_charge > 0)
            <div class="total-row">
                <span>Service Charge:</span>
                <span>₹{{ number_format((float) $bill->service_charge, 2) }}</span>
            </div>
        @endif

        @if((float) $bill->round_off != 0)
            <div class="total-row">
                <span>Round Off:</span>
                <span>{{ (float) $bill->round_off >= 0 ? '+' : '' }}₹{{ number_format((float) $bill->round_off, 2) }}</span>
            </div>
        @endif

        <div class="divider-double"></div>

        <div class="total-row grand-total">
            <span>TOTAL AMOUNT:</span>
            <span>₹{{ number_format((float) $bill->final_amount, 2) }}</span>
        </div>

        <div class="divider-double"></div>

        <!-- Payment Settlement Status -->
        <div class="total-row">
            <span>Paid Amount:</span>
            <span class="bold">₹{{ number_format((float) $bill->total_paid_amount, 2) }}</span>
        </div>

        @if($bill->outstanding_balance > 0)
            <div class="total-row bold" style="color: #c00;">
                <span>Balance Due:</span>
                <span>₹{{ number_format($bill->outstanding_balance, 2) }}</span>
            </div>
        @endif

        @if($bill->successfulPayments->isNotEmpty())
            <div class="divider"></div>
            <div class="bold" style="font-size: 9px; text-transform: uppercase; margin-bottom: 2px;">Payment Details:</div>
            @foreach($bill->successfulPayments as $pay)
                <div class="info-row" style="font-size: 9px;">
                    <span>{{ strtoupper(str_replace('_', ' ', $pay->payment_method)) }}: ₹{{ number_format((float) $pay->amount, 2) }}</span>
                    <span>Ref: {{ $pay->payment_ref }}</span>
                </div>
            @endforeach
        @endif

        <div class="divider"></div>

        <div class="text-center">
            @if($bill->isPaid())
                <div class="settle-badge">PAID &amp; SETTLED</div>
            @else
                <div class="settle-badge" style="border-style: dashed;">PAYMENT DUE</div>
            @endif

            <div class="footer">
                Thank you for dining with us!<br>
                Please visit us again.<br>
                *** Have a Nice Day ***
            </div>
        </div>

    </div>

</body>
</html>
