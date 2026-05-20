<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
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
        .invoice-title-container {
            margin-bottom: 20px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .invoice-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .invoice-meta {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }
        .meta-grid {
            width: 100%;
            margin-bottom: 25px;
        }
        .meta-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            width: 48%;
            vertical-align: top;
        }
        .meta-label {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .meta-value {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }
        .meta-subvalue {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .section-title {
            font-size: 10px;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
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
        .totals-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .totals-table td {
            padding: 5px 0;
            font-size: 10px;
        }
        .totals-table tr.grand-total td {
            font-size: 12px;
            font-weight: bold;
            color: #0d9488;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        .terms-box {
            width: 50%;
            font-size: 9px;
            color: #64748b;
            vertical-align: top;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="brand-section" style="width: 100%;">
        <tr>
            <td style="vertical-align: top;">
                <div class="brand-logo">{{ $shopSettings?->shop_name ?? 'Kharcha Pani' }}</div>
                <div class="brand-subtitle">Shop Expense & Management</div>
            </td>
            <td class="company-details" style="vertical-align: top;">
                @if($shopSettings?->shop_address)
                    <div>{{ $shopSettings->shop_address }}</div>
                @endif
                @if($shopSettings?->shop_phone)
                    <div>Phone: {{ $shopSettings->shop_phone }}</div>
                @endif
                @if($shopSettings?->gst_number)
                    <div style="font-weight: bold; margin-top: 2px;">GSTIN: {{ $shopSettings->gst_number }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="header"></div>

    <!-- Invoice Header Info -->
    <div class="invoice-title-container">
        <h1 class="invoice-title">INVOICE: {{ $sale->invoice_number }}</h1>
        <div class="invoice-meta">
            Billing Date: {{ \Carbon\Carbon::parse($sale->date)->format('d F Y') }} | Checkout Clerk: {{ $sale->user->name ?? 'Store Admin' }}
        </div>
    </div>

    <!-- Client Info + Sourced details -->
    <table class="meta-grid">
        <tr>
            <td class="meta-card">
                <div class="meta-label">Billed To Customer</div>
                <div class="meta-value">{{ $sale->customer_name ?? 'Walk-in Customer' }}</div>
                @if($sale->customer)
                    <div class="meta-subvalue">
                        @if($sale->customer->phone)
                            <div>Phone: {{ $sale->customer->phone }}</div>
                        @endif
                        @if($sale->customer->email)
                            <div>Email: {{ $sale->customer->email }}</div>
                        @endif
                        @if($sale->customer->address)
                            <div>Address: {{ $sale->customer->address }}</div>
                        @endif
                    </div>
                @else
                    <div class="meta-subvalue" style="font-style: italic;">Anonymous Checkout / Walk-in</div>
                @endif
            </td>
            <td style="width: 4%;"></td>
            <td class="meta-card">
                <div class="meta-label">Invoice Checkout Records</div>
                <div class="meta-value" style="text-transform: uppercase;">{{ str_replace('_', ' ', $sale->payment_method) }}</div>
                <div class="meta-subvalue" style="margin-top: 5px;">
                    <div>Payment Status: 
                        <span class="badge {{ $sale->payment_method === 'credit' ? 'badge-warning' : 'badge-success' }}">
                            {{ $sale->payment_method === 'credit' ? 'Udhaar Pending' : 'Fully Settled' }}
                        </span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <div class="section-title">Invoice Checkout Items</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: center;">#</th>
                <th>Product Description</th>
                <th style="width: 18%; text-align: right;">Unit Price</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 20%; text-align: right;">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
                <tr>
                    <td class="text-center" style="font-weight: bold; color: #64748b;">{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $item->product_name }}</td>
                    <td class="text-right">₹{{ number_format($item->selling_price, 2) }}</td>
                    <td class="text-center font-bold" style="font-weight: bold;">{{ $item->quantity }}</td>
                    <td class="text-right" style="font-weight: bold;">₹{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pricing Summary Section -->
    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td class="terms-box">
                <div style="font-weight: bold; color: #475569; margin-bottom: 4px; text-transform: uppercase; font-size: 8px;">Terms & Conditions</div>
                <div>Goods once sold are non-refundable. For ledger balance updates or credit adjustments, please consult with the shop supervisor. Thank you for your continued business!</div>
            </td>
            <td style="width: 10%;"></td>
            <td>
                <table class="totals-table">
                    <tr>
                        <td style="color: #64748b;">Taxable Subtotal:</td>
                        <td class="text-right" style="font-weight: bold;">₹{{ number_format($sale->total_amount - $sale->tax_amount, 2) }}</td>
                    </tr>
                    @if($sale->tax_amount > 0)
                        <tr>
                            <td style="color: #64748b;">GST/Sales Tax (Sourced):</td>
                            <td class="text-right" style="font-weight: bold;">₹{{ number_format($sale->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td>Grand Total:</td>
                        <td class="text-right">₹{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        Invoice electronically generated and audited by <strong>Kharcha Pani</strong> ledger systems.<br>
        &copy; {{ date('Y') }} {{ $shopSettings?->shop_name ?? 'Kharcha Pani' }}.
    </div>

</body>
</html>
