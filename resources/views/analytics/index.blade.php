@extends('layouts.app')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">

    {{-- Header Banner --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
                <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
                <span class="text-slate-650">Analytics Insights</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-teal-500"></i> Store Performance & Growth Analytics
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Evaluate revenue trends, operating expenses, profitability indexes, and AI-driven store logs.</p>
        </div>
        <div class="flex-shrink-0">
            <span class="inline-flex items-center px-3 py-1.5 rounded-2xl text-xs font-bold text-teal-700 bg-teal-50 border border-teal-100">
                <i class="fa-solid fa-calendar-check mr-1.5 animate-pulse text-teal-500"></i> Auto-Updated Ledger
            </span>
        </div>
    </div>

    {{-- 1. KPI Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        {{-- Card 1: Revenue --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md relative overflow-hidden group hover:shadow-lg transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-teal-50 rounded-bl-full -z-10 group-hover:scale-105 transition-transform"></div>
            <span class="block text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2">Sales Revenue (This Month)</span>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900 tracking-tight">₹{{ number_format($currentMonthSales, 2) }}</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                @if($salesGrowth >= 0)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-3xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <i class="fa-solid fa-arrow-trend-up mr-0.5"></i> +{{ number_format($salesGrowth, 1) }}%
                    </span>
                @else
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-3xs font-extrabold bg-rose-50 text-rose-700 border border-rose-100">
                        <i class="fa-solid fa-arrow-trend-down mr-0.5"></i> {{ number_format($salesGrowth, 1) }}%
                    </span>
                @endif
                <span class="text-3xs font-semibold text-slate-400">vs last month</span>
            </div>
        </div>

        {{-- Card 2: Operating Expenses --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md relative overflow-hidden group hover:shadow-lg transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-rose-50 rounded-bl-full -z-10 group-hover:scale-105 transition-transform"></div>
            <span class="block text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2">Expenses Total (This Month)</span>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-slate-900 tracking-tight">₹{{ number_format($currentMonthExpenses, 2) }}</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5">
                @if($expenseGrowth >= 0)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-3xs font-extrabold bg-rose-50 text-rose-600 border border-rose-100">
                        <i class="fa-solid fa-arrow-trend-up mr-0.5"></i> +{{ number_format($expenseGrowth, 1) }}%
                    </span>
                @else
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-3xs font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <i class="fa-solid fa-arrow-trend-down mr-0.5"></i> {{ number_format($expenseGrowth, 1) }}%
                    </span>
                @endif
                <span class="text-3xs font-semibold text-slate-400">vs last month</span>
            </div>
        </div>

        {{-- Card 3: Top Performing Product --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md relative overflow-hidden group hover:shadow-lg transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full -z-10 group-hover:scale-105 transition-transform"></div>
            <span class="block text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2">Most Sold Item (30 Days)</span>
            @if($mostSoldProduct)
                <div class="space-y-1">
                    <span class="block text-base font-extrabold text-slate-800 line-clamp-1 leading-snug">{{ $mostSoldProduct->product_name }}</span>
                    <span class="block text-3xs font-bold text-slate-500">Sales qty: <strong class="text-indigo-600 font-extrabold">{{ $mostSoldProduct->total_quantity }} units</strong></span>
                </div>
            @else
                <span class="block text-sm font-bold text-slate-400 italic">No items sold yet</span>
            @endif
            <div class="mt-4 flex items-center">
                <span class="inline-flex px-1.5 py-0.5 rounded text-3xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <i class="fa-solid fa-star mr-0.5 text-indigo-500"></i> Best Seller
                </span>
            </div>
        </div>

        {{-- Card 4: Top Cost Outlay --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md relative overflow-hidden group hover:shadow-lg transition-shadow">
            <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -z-10 group-hover:scale-105 transition-transform"></div>
            <span class="block text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2">Highest Expense Source</span>
            @if($highestExpenseCategory)
                <div class="space-y-1">
                    <span class="block text-base font-extrabold text-slate-800 line-clamp-1 leading-snug">{{ $highestExpenseCategory->name }}</span>
                    <span class="block text-3xs font-bold text-slate-500">Paid out: <strong class="text-rose-600 font-extrabold">₹{{ number_format($highestExpenseCategory->total, 2) }}</strong></span>
                </div>
            @else
                <span class="block text-sm font-bold text-slate-400 italic">No expenses yet</span>
            @endif
            <div class="mt-4 flex items-center">
                <span class="inline-flex px-1.5 py-0.5 rounded text-3xs font-extrabold bg-amber-50 text-amber-700 border border-amber-100">
                    <i class="fa-solid fa-credit-card mr-0.5 text-amber-500"></i> High Outlay
                </span>
            </div>
        </div>

    </div>

    {{-- 2. Core Section Grid: Chart & Smart Insights --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Left: Revenue vs Expense Chart (Takes 2 Columns) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-100 shadow-md space-y-6">
                <div class="flex items-center justify-between border-b border-slate-150 pb-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 tracking-tight text-sm">Revenue vs Operating Cost</h3>
                        <p class="text-3xs text-slate-450 mt-0.5">Historical comparison mapping income margins against store overheads over the past 6 months.</p>
                    </div>
                    <div class="flex items-center space-x-3 text-3xs font-semibold text-slate-500">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> Sales</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Expenses</span>
                    </div>
                </div>

                {{-- Chart container --}}
                <div class="relative h-72 md:h-80 w-full">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Right: AI Smart Store Insights (Takes 1 Column) --}}
        <div class="space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md h-full flex flex-col">
                <h3 class="font-extrabold text-slate-900 tracking-tight text-sm border-b border-slate-100 pb-3">Smart Performance Logs</h3>
                
                {{-- Insights List --}}
                <div class="flex-1 space-y-4 py-4 overflow-y-auto max-h-96">
                    @forelse($insights as $insight)
                        <div class="flex gap-3.5 p-3 rounded-2xl border transition-all hover:bg-slate-50/50
                            @if($insight['type'] === 'positive') border-emerald-100 bg-emerald-50/20 text-emerald-800
                            @elseif($insight['type'] === 'warning') border-amber-100 bg-amber-50/20 text-amber-800
                            @elseif($insight['type'] === 'negative') border-rose-100 bg-rose-50/20 text-rose-800
                            @else border-slate-250 bg-slate-50/25 text-slate-750
                            @endif">
                            
                            <div class="flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center
                                @if($insight['type'] === 'positive') bg-emerald-100 text-emerald-600
                                @elseif($insight['type'] === 'warning') bg-amber-100 text-amber-600
                                @elseif($insight['type'] === 'negative') bg-rose-100 text-rose-600
                                @else bg-slate-200 text-slate-500
                                @endif">
                                @if($insight['type'] === 'positive')
                                    <i class="fa-solid fa-circle-check"></i>
                                @elseif($insight['type'] === 'warning')
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                @elseif($insight['type'] === 'negative')
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                @else
                                    <i class="fa-solid fa-circle-info"></i>
                                @endif
                            </div>
                            
                            <div class="space-y-0.5">
                                <span class="block text-3xs font-extrabold uppercase tracking-wider
                                    @if($insight['type'] === 'positive') text-emerald-600
                                    @elseif($insight['type'] === 'warning') text-amber-600
                                    @elseif($insight['type'] === 'negative') text-rose-600
                                    @else text-slate-500
                                    @endif">
                                    {{ ucfirst($insight['type']) }} Trend
                                </span>
                                <p class="text-2xs font-semibold leading-relaxed">{{ $insight['message'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fa-solid fa-chart-line text-slate-300 text-3xl mb-2.5 block"></i>
                            <span class="text-xs font-semibold text-slate-400">Generate more store checkouts to populate automated ledger insights.</span>
                        </div>
                    @endforelse
                </div>

                {{-- Bottom Action --}}
                <div class="border-t border-slate-100 pt-4 mt-auto">
                    <a href="{{ route('reports.index') }}" class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 transition-colors">
                        <i class="fa-solid fa-file-excel text-3xs"></i> Audit Ledger Records
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Chart JS Initialization script --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('growthChart').getContext('2d');
        
        // Grab values from blade safely
        const chartLabels = {!! json_encode($chartMonths) !!};
        const salesRevenue = {!! json_encode($salesData) !!};
        const expensesTotal = {!! json_encode($expenseData) !!};

        // Create gradient fills
        const salesGradient = ctx.createLinearGradient(0, 0, 0, 300);
        salesGradient.addColorStop(0, 'rgba(13, 148, 136, 0.25)');
        salesGradient.addColorStop(1, 'rgba(13, 148, 136, 0.01)');

        const expensesGradient = ctx.createLinearGradient(0, 0, 0, 300);
        expensesGradient.addColorStop(0, 'rgba(239, 68, 68, 0.25)');
        expensesGradient.addColorStop(1, 'rgba(239, 68, 68, 0.01)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Sales Revenue',
                        data: salesRevenue,
                        borderColor: '#0d9488',
                        backgroundColor: salesGradient,
                        borderWidth: 3.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#0d9488',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        pointHoverRadius: 6.5
                    },
                    {
                        label: 'Total Expenses',
                        data: expensesTotal,
                        borderColor: '#ef4444',
                        backgroundColor: expensesGradient,
                        borderWidth: 3.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        pointHoverRadius: 6.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // handled in top custom panel
                    },
                    tooltip: {
                        padding: 12,
                        backgroundColor: '#0f172a',
                        titleFont: {
                            family: "'Outfit', sans-serif",
                            size: 11,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Outfit', sans-serif",
                            size: 11,
                            weight: '500'
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: "'Outfit', sans-serif",
                                size: 10,
                                weight: '500'
                            },
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: "'Outfit', sans-serif",
                                size: 10,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
