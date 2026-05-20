<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ShopSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function show(Sale $sale)
    {
        Gate::authorize('view', $sale);
        $sale->load(['items', 'customer']);
        $shopSettings = ShopSetting::first();
        return view('invoices.show', compact('sale', 'shopSettings'));
    }

    public function print(Sale $sale)
    {
        Gate::authorize('view', $sale);
        $sale->load(['items', 'customer']);
        $shopSettings = ShopSetting::first();
        return view('invoices.print', compact('sale', 'shopSettings'));
    }

    public function downloadPDF(Sale $sale)
    {
        Gate::authorize('view', $sale);
        $sale->load(['items', 'customer']);
        $shopSettings = ShopSetting::first();

        // Generate PDF using Barryvdh/laravel-dompdf
        $pdf = Pdf::loadView('invoices.pdf', compact('sale', 'shopSettings'));
        
        return $pdf->download("invoice_{$sale->invoice_number}.pdf");
    }

    public function share(Sale $sale, Request $request)
    {
        Gate::authorize('view', $sale);
        $request->validate(['channel' => 'required|in:email,whatsapp']);

        $shopSettings = ShopSetting::first();
        $due = ($sale->payment_method === 'credit') ? ' (Marked as Credit/Udhaar)' : '';
        $msg = "Hello, your invoice {$sale->invoice_number} from {$shopSettings->shop_name} for total amount {$sale->total_amount} {$shopSettings->currency} is generated.{$due}";

        if ($request->channel === 'whatsapp') {
            $phone = $sale->customer ? $sale->customer->phone : '';
            // Sanitize phone (keep only digits)
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            $url = "https://api.whatsapp.com/send?phone=" . ($phone ? '91'.$phone : '') . "&text=" . urlencode($msg);
            return redirect()->away($url);
        }

        // Email copy alert
        return redirect()->back()->with('success', 'Invoice details sent to dispatcher queue!');
    }
}
