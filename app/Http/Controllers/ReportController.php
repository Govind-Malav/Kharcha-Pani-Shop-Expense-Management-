<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Report;
use App\Models\ShopSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Accountant and Owner can view reports
        if (!auth()->user()->isOwner() && !auth()->user()->isAccountant()) {
            abort(403, 'Unauthorized action. Reports are only accessible by Owners and Accountants.');
        }

        $type = $request->get('type', 'monthly'); // daily, weekly, monthly, yearly, custom
        $startDate = null;
        $endDate = null;

        $now = Carbon::now();

        switch ($type) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'weekly':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'yearly':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
                    $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
                } else {
                    $startDate = $now->copy()->startOfMonth();
                    $endDate = $now->copy()->endOfMonth();
                }
                break;
            case 'monthly':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        // Fetch sales and expenses in date range
        $sales = Sale::with('customer')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        $expenses = Expense::with('category')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate summaries
        $totalSales = $sales->sum('total_amount');
        $totalExpenses = $expenses->sum('amount');
        $profit = $totalSales - $totalExpenses;

        // Inventory status
        $totalInventoryItems = Product::sum('quantity');
        $lowStockItemsCount = Product::all()->filter(fn($p) => $p->isLowStock())->count();

        // Historical reports log
        $reportsLog = Report::with('user')->latest()->take(10)->get();

        return view('reports.index', compact(
            'sales',
            'expenses',
            'totalSales',
            'totalExpenses',
            'profit',
            'totalInventoryItems',
            'lowStockItemsCount',
            'startDate',
            'endDate',
            'type',
            'reportsLog'
        ));
    }

    public function export(Request $request)
    {
        if (!auth()->user()->isOwner() && !auth()->user()->isAccountant()) {
            abort(403);
        }

        $request->validate([
            'format' => 'required|in:csv,pdf',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $format = $request->format;
        $type = $request->type;

        $sales = Sale::whereBetween('date', [$startDate, $endDate])->orderBy('date')->get();
        $expenses = Expense::with('category')->whereBetween('date', [$startDate, $endDate])->orderBy('date')->get();
        
        $totalSales = $sales->sum('total_amount');
        $totalExpenses = $expenses->sum('amount');
        $profit = $totalSales - $totalExpenses;
        $shopSettings = ShopSetting::first();

        // Title for report log
        $title = ucfirst($type) . " Report (" . $startDate->format('d M Y') . " - " . $endDate->format('d M Y') . ")";

        // Log the report in DB
        Report::create([
            'title' => $title,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => auth()->id(),
            'file_path' => "Exported as " . strtoupper($format),
        ]);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', compact('sales', 'expenses', 'totalSales', 'totalExpenses', 'profit', 'startDate', 'endDate', 'shopSettings', 'title'));
            return $pdf->download(Str::slug($title) . ".pdf");
        }

        // CSV export
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=" . Str::slug($title) . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($sales, $expenses, $totalSales, $totalExpenses, $profit, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Header Info
            fputcsv($file, ["SHOP REPORT SUMMARY"]);
            fputcsv($file, ["Date Range", $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d')]);
            fputcsv($file, ["Total Sales (Revenue)", $totalSales]);
            fputcsv($file, ["Total Expenses", $totalExpenses]);
            fputcsv($file, ["Net Profit/Loss", $profit]);
            fputcsv($file, []);

            // Sales breakdown
            fputcsv($file, ["SALES RECEIPTS"]);
            fputcsv($file, ["Invoice Number", "Customer Name", "Payment Method", "Date", "Total Amount"]);
            foreach ($sales as $sale) {
                fputcsv($file, [$sale->invoice_number, $sale->customer_name, strtoupper($sale->payment_method), $sale->date->format('Y-m-d'), $sale->total_amount]);
            }
            fputcsv($file, []);

            // Expenses breakdown
            fputcsv($file, ["EXPENSES RECORDS"]);
            fputcsv($file, ["Title", "Category", "Payment Type", "Date", "Amount", "Description"]);
            foreach ($expenses as $expense) {
                fputcsv($file, [$expense->title, $expense->category->name, strtoupper($expense->payment_type), $expense->date->format('Y-m-d'), $expense->amount, $expense->description]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
