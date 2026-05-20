<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Accountant and Owner can view analytics
        if (!auth()->user()->isOwner() && !auth()->user()->isAccountant()) {
            abort(403, 'Unauthorized action. Analytics are reserved for Owners and Accountants.');
        }

        $now = Carbon::now();
        $startOfCurrentMonth = $now->copy()->startOfMonth();
        $endOfCurrentMonth = $now->copy()->endOfMonth();
        
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Sales Growth comparison
        $currentMonthSales = Sale::whereBetween('date', [$startOfCurrentMonth, $endOfCurrentMonth])->sum('total_amount');
        $lastMonthSales = Sale::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->sum('total_amount');
        
        $salesGrowth = 0;
        if ($lastMonthSales > 0) {
            $salesGrowth = (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100;
        }

        // 2. Expense Growth comparison
        $currentMonthExpenses = Expense::whereBetween('date', [$startOfCurrentMonth, $endOfCurrentMonth])->sum('amount');
        $lastMonthExpenses = Expense::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->sum('amount');
        
        $expenseGrowth = 0;
        if ($lastMonthExpenses > 0) {
            $expenseGrowth = (($currentMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100;
        }

        // 3. Highest Cost Category
        $highestExpenseCategory = Expense::join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->first();

        // 4. Most Sold Product (last 30 days)
        $mostSoldProduct = SaleItem::select('product_name', DB::raw('SUM(quantity) as total_quantity'))
            ->whereBetween('created_at', [$now->copy()->subDays(30), $now])
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->first();

        // 5. Generate Smart Insights List
        $insights = [];

        // A. Sales Insight
        if ($lastMonthSales > 0) {
            $trend = $salesGrowth >= 0 ? 'increased' : 'decreased';
            $percent = number_format(abs($salesGrowth), 1);
            $insights[] = [
                'type' => $salesGrowth >= 0 ? 'positive' : 'negative',
                'message' => "Monthly shop revenue has {$trend} by {$percent}% compared to last month."
            ];
        } else {
            $insights[] = [
                'type' => 'neutral',
                'message' => "Insufficient historical sales data to draw a monthly revenue growth trend."
            ];
        }

        // B. Expense Insight
        if ($lastMonthExpenses > 0) {
            $trend = $expenseGrowth >= 0 ? 'increased' : 'decreased';
            $percent = number_format(abs($expenseGrowth), 1);
            $insights[] = [
                'type' => $expenseGrowth >= 0 ? 'warning' : 'positive',
                'message' => "Total business expenses have {$trend} by {$percent}% this month."
            ];
        }

        // C. Specific Utility (Electricity / Internet) Category comparison
        $elecCategory = ExpenseCategory::where('name', 'Electricity')->first();
        if ($elecCategory) {
            $currElec = Expense::where('category_id', $elecCategory->id)->whereBetween('date', [$startOfCurrentMonth, $endOfCurrentMonth])->sum('amount');
            $prevElec = Expense::where('category_id', $elecCategory->id)->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])->sum('amount');
            if ($prevElec > 0) {
                $elecGrowth = (($currElec - $prevElec) / $prevElec) * 100;
                if (abs($elecGrowth) > 5) {
                    $trend = $elecGrowth >= 0 ? 'increased' : 'decreased';
                    $insights[] = [
                        'type' => $elecGrowth >= 0 ? 'warning' : 'positive',
                        'message' => "Electricity bill expenses {$trend} by " . number_format(abs($elecGrowth), 1) . "% compared to last month."
                    ];
                }
            }
        }

        // D. Most Sold Product Insight (if sold this month)
        if ($mostSoldProduct) {
            $insights[] = [
                'type' => 'positive',
                'message' => "Product '{$mostSoldProduct->product_name}' is the most popular item this month, selling {$mostSoldProduct->total_quantity} units."
            ];
        }

        // E. Profitability trend
        $currProfit = $currentMonthSales - $currentMonthExpenses;
        $prevProfit = $lastMonthSales - $lastMonthExpenses;
        if ($prevProfit > 0) {
            $profitGrowth = (($currProfit - $prevProfit) / $prevProfit) * 100;
            $trend = $profitGrowth >= 0 ? 'increased' : 'decreased';
            $insights[] = [
                'type' => $profitGrowth >= 0 ? 'positive' : 'negative',
                'message' => "Net business profitability {$trend} by " . number_format(abs($profitGrowth), 1) . "% this month."
            ];
        }

        // 6. Detailed Monthly Sales and Expense Comparison (Current vs Last Year or Last 6 Months)
        $chartMonths = [];
        $salesData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $chartMonths[] = $monthDate->format('M Y');
            
            $salesData[] = Sale::whereMonth('date', $monthDate->month)
                ->whereYear('date', $monthDate->year)
                ->sum('total_amount');
                
            $expenseData[] = Expense::whereMonth('date', $monthDate->month)
                ->whereYear('date', $monthDate->year)
                ->sum('amount');
        }

        return view('analytics.index', compact(
            'currentMonthSales',
            'lastMonthSales',
            'salesGrowth',
            'currentMonthExpenses',
            'lastMonthExpenses',
            'expenseGrowth',
            'highestExpenseCategory',
            'mostSoldProduct',
            'insights',
            'chartMonths',
            'salesData',
            'expenseData'
        ));
    }
}
