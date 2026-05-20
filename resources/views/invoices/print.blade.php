<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice {{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            padding: 15px;
            width: 80mm; /* standard thermal receipt width */
            box-sizing: border-box;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .header {
            margin-bottom: 15px;
        }
        .shop-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .meta-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            text-align: left;
        }
        .items-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .totals-section {
            margin-top: 10px;
            font-size: 11px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
        }
    </style>
</head>
<body onload="window.print();">

    <div class="header text-center">
        <div class="shop-name">{{ $shopSettings->shop_name ?? 'Kharcha Pani' }}</div>
        @if($shopSettings->shop_address)
            <div style="font-size: 10px; margin-top: 2px;">{{ $shopSettings->shop_address }}</div>
        @endif
        @if($shopSettings->shop_phone)
            <div style="font-size: 10px;">Phone: {{ $shopSettings->shop_phone }}</div>
        @endif
        @if($shopSettings->gst_number)
            <div style="font-size: 10px; font-weight: bold;">GSTIN: {{ $shopSettings->gst_number }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td class="bold">INV:</td>
            <td>{{ $sale->invoice_number }}</td>
        </tr>
        <tr>
            <td class="bold">DATE:</td>
            <td>{{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td class="bold">CUST:</td>
            <td>{{ $sale->customer_name ?? 'Walk-in Customer' }}</td>
        </tr>
        <tr>
            <td class="bold">MODE:</td>
            <td style="text-transform: uppercase;">{{ str_replace('_', ' ', $sale->payment_method) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th>ITEM</th>
                <th class="text-center" style="width: 15%;">QTY</th>
                <th class="text-right" style="width: 25%;">PRICE</th>
                <th class="text-right" style="width: 25%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ substr($item->product_name, 0, 16) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals-section">
        <div class="total-row">
            <span>SUBTOTAL:</span>
            <span>₹{{ number_format($sale->total_amount - $sale->tax_amount, 2) }}</span>
        </div>
        @if($sale->tax_amount > 0)
            <div class="total-row">
                <span>TAX/GST:</span>
                <span>₹{{ number_format($sale->tax_amount, 2) }}</span>
            </div>
        @endif
        <div class="total-row bold" style="font-size: 13px; border-top: 1px dashed #000; padding-top: 4px; margin-top: 4px;">
            <span>GRAND TOTAL:</span>
            <span>₹{{ number_format($sale->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="footer text-center">
        <div class="bold">THANK YOU FOR YOUR VISIT!</div>
        <div style="font-size: 9px; margin-top: 4px;">SYSTEM POWERED BY KHARCHA PANI</div>
    </div>

</body>
</html>
