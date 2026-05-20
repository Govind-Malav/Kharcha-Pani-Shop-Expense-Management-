<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Credit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Limited dashboard view for Staff
        if ($user->isStaff()) {
            return $this->staffDashboard();
        }

        // --- OWNER & ACCOUNTANT FULL DASHBOARD DATA ---
        
        // 1. KPI Cards
        $totalIncome = Sale::sum('total_amount');
        $totalExpenses = Expense::sum('amount');
        
        $profitOrLoss = $totalIncome - $totalExpenses;
        $netProfit = $profitOrLoss > 0 ? $profitOrLoss : 0;
        $netLoss = $profitOrLoss < 0 ? abs($profitOrLoss) : 0;

        // Customer outstanding credit (udhaar)
        $totalCreditAmount = Credit::where('type', 'customer')
            ->selectRaw('SUM(amount - paid_amount) as outstanding')
            ->first()->outstanding ?? 0.00;

        // Supplier outstanding dues
        $totalSupplierDues = Credit::where('type', 'supplier')
            ->selectRaw('SUM(amount - paid_amount) as outstanding')
            ->first()->outstanding ?? 0.00;

        // Low stock count
        $lowStockProducts = Product::all()->filter(function ($product) {
            return $product->isLowStock();
        });
        $lowStockCount = $lowStockProducts->count();

        // 2. Charts Data
        $now = Carbon::now();
        $currentYear = $now->year;

        $dbDriver = DB::connection()->getDriverName();
        if ($dbDriver === 'pgsql') {
            $monthExpr = 'EXTRACT(MONTH FROM date) as month';
            $groupByExpr = DB::raw('EXTRACT(MONTH FROM date)');
        } elseif ($dbDriver === 'sqlite') {
            $monthExpr = 'cast(strftime("%m", date) as integer) as month';
            $groupByExpr = DB::raw('cast(strftime("%m", date) as integer)');
        } else {
            $monthExpr = 'MONTH(date) as month';
            $groupByExpr = DB::raw('MONTH(date)');
        }

        // A. Monthly Sales for Current Year (Bar Chart)
        $monthlySalesData = Sale::selectRaw($monthExpr . ', SUM(total_amount) as total')
            ->whereYear('date', $currentYear)
            ->groupBy($groupByExpr)
            ->pluck('total', 'month')
            ->toArray();

        $salesChartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $val = $monthlySalesData[$i] ?? $monthlySalesData[str_pad($i, 2, '0', STR_PAD_LEFT)] ?? 0.00;
            $salesChartData[] = (float) $val;
        }

        // B. Monthly Expenses for Current Year (Line Chart Helper)
        $monthlyExpensesData = Expense::selectRaw($monthExpr . ', SUM(amount) as total')
            ->whereYear('date', $currentYear)
            ->groupBy($groupByExpr)
            ->pluck('total', 'month')
            ->toArray();

        $expensesChartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $val = $monthlyExpensesData[$i] ?? $monthlyExpensesData[str_pad($i, 2, '0', STR_PAD_LEFT)] ?? 0.00;
            $expensesChartData[] = (float) $val;
        }

        // C. Monthly Profit Trends (Line Chart)
        $profitChartData = [];
        for ($i = 0; $i < 12; $i++) {
            $profitChartData[] = max(0, $salesChartData[$i] - $expensesChartData[$i]);
        }

        // D. Expense Categories breakdown (Pie Chart)
        $categoriesBreakdown = Expense::join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category_name, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.name')
            ->get();

        $pieLabels = $categoriesBreakdown->pluck('category_name')->toArray();
        $pieValues = $categoriesBreakdown->pluck('total')->toArray();

        // 3. Recent Transactions (Merge Sales & Expenses)
        $recentSales = Sale::latest()->take(5)->get()->map(function($sale) {
            return [
                'type' => 'sale',
                'title' => 'Sale: ' . $sale->invoice_number . ($sale->customer_name ? ' (' . $sale->customer_name . ')' : ''),
                'amount' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'date' => $sale->date,
                'ref_id' => $sale->id
            ];
        });

        $recentExpenses = Expense::with('category')->latest()->take(5)->get()->map(function($expense) {
            return [
                'type' => 'expense',
                'title' => $expense->title . ' (' . $expense->category->name . ')',
                'amount' => $expense->amount,
                'payment_method' => $expense->payment_type,
                'date' => $expense->date,
                'ref_id' => $expense->id
            ];
        });

        $recentTransactions = $recentSales->concat($recentExpenses)
            ->sortByDesc('date')
            ->take(8);

        return view('dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'netLoss',
            'totalCreditAmount',
            'totalSupplierDues',
            'lowStockCount',
            'recentTransactions',
            'salesChartData',
            'expensesChartData',
            'profitChartData',
            'pieLabels',
            'pieValues',
            'lowStockProducts'
        ));
    }

    private function staffDashboard()
    {
        // Limited dashboard for Staff: only shows sales, expenses, and low stock counts
        $totalIncome = Sale::where('user_id', auth()->id())->sum('total_amount');
        $totalExpenses = Expense::where('user_id', auth()->id())->sum('amount');
        $lowStockCount = Product::all()->filter(function ($product) {
            return $product->isLowStock();
        })->count();

        // Recent transactions by the logged in staff
        $recentSales = Sale::where('user_id', auth()->id())->latest()->take(5)->get()->map(function($sale) {
            return [
                'type' => 'sale',
                'title' => 'Sale: ' . $sale->invoice_number,
                'amount' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'date' => $sale->date,
                'ref_id' => $sale->id
            ];
        });

        $recentExpenses = Expense::with('category')->where('user_id', auth()->id())->latest()->take(5)->get()->map(function($expense) {
            return [
                'type' => 'expense',
                'title' => $expense->title . ' (' . $expense->category->name . ')',
                'amount' => $expense->amount,
                'payment_method' => $expense->payment_type,
                'date' => $expense->date,
                'ref_id' => $expense->id
            ];
        });

        $recentTransactions = $recentSales->concat($recentExpenses)
            ->sortByDesc('date')
            ->take(5);

        return view('dashboard_staff', compact(
            'totalIncome',
            'totalExpenses',
            'lowStockCount',
            'recentTransactions'
        ));
    }
}
