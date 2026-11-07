<!DOCTYPE html>
<html>
<head>
    <title>Customer Purchase Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* --- GENERAL STYLES --- */
        body {
            font-family: sans-serif;
            font-size: 14px;
            color: #212529;
            background: #fff;
        }

        .report-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        /* --- TABLE STYLING (Bootstrap Mimic for PDF) --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            text-align: left;
            vertical-align: middle;
        }

        /* Dark Header (.table-dark) */
        th {
            background-color: #212529;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        /* Total Row (.table-secondary) */
        .total-row td {
            background-color: #e2e3e5;
            font-weight: bold;
        }

        /* --- UTILITY CLASSES --- */
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .fw-bold { font-weight: bold; }
        .mb-0 { margin-bottom: 0; }
        .mb-5 { margin-bottom: 3rem; }

        /* Customer Header Box */
        .customer-header {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        /* Badges (.badge) */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .bg-success { background-color: #198754; color: white; }
        .bg-secondary { background-color: #6c757d; color: white; }

        /* --- PRINT/PDF SPECIFICS --- */
        @media print {
            .no-print { display: none !important; }
            .report-container { width: 100%; max-width: 100%; padding: 0; }
        }

        /* Page break prevention for PDF */
        .customer-section {
            page-break-inside: avoid;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="report-container">

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1>Customer Sales Report</h1>
            <p class="text-muted">Generated on: {{ now()->format('M d, Y H:i') }}</p>
        </div>

        {{-- CONTROLS: Only visible in Browser, Hidden in PDF --}}
        @if(!isset($isPdf))
        <div class="d-flex gap-2 no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                🖨️ Print View
            </button>
            <a href="{{ route('admin.reports.customers.download') }}" class="btn btn-primary">
                ⬇️ Download PDF
            </a>
        </div>
        @endif
    </div>

    @foreach($customers as $customer)
    <div class="customer-section">

        <div class="customer-header">
            <h4 class="mb-0">{{ $customer->name }}</h4>
            <div class="text-muted small">
                Email: {{ $customer->email }} <br>
                Member Since: {{ $customer->created_at->format('M d, Y') }}
            </div>
        </div>

        @if($customer->orders->isEmpty())
            <p class="text-muted ms-2">No purchase history found.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%">Date</th>
                        <th style="width: 35%">Product</th>
                        <th style="width: 10%" class="text-center">Qty</th>
                        <th style="width: 15%">Unit Price</th>
                        <th style="width: 10%" class="text-center">Discount</th>
                        <th style="width: 15%">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->orders as $order)
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td class="text-center">
                                @if($item->discount_applied > 0)
                                    <span class="badge bg-success">-{{ $item->discount_applied }}%</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    @endforeach

                    <tr class="total-row">
                        <td colspan="5" class="text-end">Total Lifetime Spend:</td>
                        <td>${{ number_format($customer->orders->sum('total_amount'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>
    @endforeach

</div>

</body>
</html>
