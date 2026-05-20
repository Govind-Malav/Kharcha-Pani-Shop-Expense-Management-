<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .brand-section {
            width: 100%;
        }
        .brand-logo {
            font-size: 20px;
            font-weight: 800;
            color: #0d9488;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .company-details {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        .report-title-container {
            margin-bottom: 20px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .report-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .report-dates {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: left;
            width: 31%;
        }
        .kpi-card.profit {
            border-left: 4px solid #0d9488;
        }
        .kpi-card.sales {
            border-left: 4px solid #3b82f6;
        }
        .kpi-card.expenses {
            border-left: 4px solid #ef4444;
        }
        .kpi-label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        .section-title {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
            margin-top: 25px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-align: left;
            padding: 7px 10px;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
        }
        .data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 750;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #dcfce7;
        }
        .badge-warning {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fef3c7;
        }
        .badge-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }
        .badge-info {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #dbeafe;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="brand-section" style="width: 100%;">
        <tr>
            <td style="vertical-align: top;">
                <div class="brand-logo">{{ $shopSettings->shop_name ?? 'Kharcha Pani' }}</div>
                <div class="brand-subtitle">Shop Expense & Management</div>
            </td>
            <td class="company-details" style="vertical-align: top;">
                @if($shopSettings->shop_address)
                    <div>{{ $shopSettings->shop_address }}</div>
                @endif
                @if($shopSettings->shop_phone)
                    <div>Phone: {{ $shopSettings->shop_phone }}</div>
                @endif
                @if($shopSettings->gst_number)
                    <div style="font-weight: bold; margin-top: 2px;">GSTIN: {{ $shopSettings->gst_number }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="header"></div>

    <!-- Report Info -->
    <div class="report-title-container">
        <h1 class="report-title">{{ $title }}</h1>
        <div class="report-dates">
            Statement Period: {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }} | Generated on: {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <table style="width: 100%; margin-bottom: 25px;">
        <tr>
            <td class="kpi-card sales">
                <div class="kpi-label">Total Sales (Revenue)</div>
                <div class="kpi-value">₹{{ number_format($totalSales, 2) }}</div>
            </td>
            <td style="width: 3.5%;"></td>
            <td class="kpi-card expenses">
                <div class="kpi-label">Total Expenses</div>
                <div class="kpi-value">₹{{ number_format($totalExpenses, 2) }}</div>
            </td>
            <td style="width: 3.5%;"></td>
            <td class="kpi-card profit">
                <div class="kpi-label">Net Profit / Loss</div>
                <div class="kpi-value" style="color: {{ $profit >= 0 ? '#0d9488' : '#ef4444' }};">
                    ₹{{ number_format($profit, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Sales breakdown Section -->
    <div class="section-title">Sales Transactions ({{ $sales->count() }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Invoice #</th>
                <th>Customer</th>
                <th style="width: 20%;">Date</th>
                <th style="width: 15%;" class="text-center">Method</th>
                <th style="width: 20%;" class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td style="font-weight: bold;">{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->customer_name ?? 'Walk-in Customer' }}</td>
                    <td>{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $sale->payment_method === 'credit' ? 'badge-warning' : 'badge-success' }}">
                            {{ str_replace('_', ' ', $sale->payment_method) }}
                        </span>
                    </td>
                    <td class="text-right" style="font-weight: bold;">₹{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 15px 0;">No sales transactions recorded in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Expenses breakdown Section -->
    <div class="section-title">Expenses Records ({{ $expenses->count() }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Expense</th>
                <th>Category</th>
                <th style="width: 20%;">Date</th>
                <th style="width: 15%;" class="text-center">Payment</th>
                <th style="width: 20%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td style="font-weight: bold;">{{ $expense->title }}</td>
                    <td>{{ $expense->category->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
                    <td class="text-center">
                        <span class="badge badge-info">
                            {{ str_replace('_', ' ', $expense->payment_type) }}
                        </span>
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #ef4444;">₹{{ number_format($expense->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 15px 0;">No expense records registered in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        This document is an autogenerated financial statement compiled by <strong>Kharcha Pani</strong> ledger systems.<br>
        &copy; {{ date('Y') }} {{ $shopSettings->shop_name ?? 'Kharcha Pani' }}. All rights reserved.
    </div>

</body>
</html>
