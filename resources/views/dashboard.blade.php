@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Business Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Real-time overview of your shop's financial health</p>
        </div>
        <span class="text-xs text-slate-400 font-medium bg-slate-100 px-3 py-1.5 rounded-lg">
            <i class="fa-regular fa-calendar mr-1.5"></i>{{ now()->format('D, d M Y') }}
        </span>
    </div>

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Total Revenue --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Revenue</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-indian-rupee-sign text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900">₹{{ number_format($totalIncome, 0) }}</div>
            <p class="text-xs text-emerald-600 font-semibold mt-2">
                <i class="fa-solid fa-arrow-trend-up mr-1"></i>All time sales income
            </p>
        </div>

        {{-- Total Expenses --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Expenses</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900">₹{{ number_format($totalExpenses, 0) }}</div>
            <p class="text-xs text-rose-600 font-semibold mt-2">
                <i class="fa-solid fa-arrow-trend-down mr-1"></i>All time total spend
            </p>
        </div>

        {{-- Net Profit / Loss --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Net {{ $netProfit > 0 ? 'Profit' : 'Loss' }}</span>
                <div class="w-10 h-10 rounded-xl {{ $netProfit > 0 ? 'bg-teal-50 text-teal-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center">
                    <i class="fa-solid fa-scale-balanced text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold {{ $netProfit > 0 ? 'text-teal-700' : 'text-amber-700' }}">
                ₹{{ number_format($netProfit > 0 ? $netProfit : $netLoss, 0) }}
            </div>
            <p class="text-xs {{ $netProfit > 0 ? 'text-teal-600' : 'text-amber-600' }} font-semibold mt-2">
                {{ $netProfit > 0 ? 'Business is profitable' : 'Expenses exceed income' }}
            </p>
        </div>

        {{-- Udhaar Outstanding --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Udhaar Pending</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fa-solid fa-handshake-angle text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900">₹{{ number_format($totalCreditAmount, 0) }}</div>
            <p class="text-xs text-indigo-600 font-semibold mt-2">
                <i class="fa-solid fa-clock-rotate-left mr-1"></i>Customer outstanding dues
            </p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Monthly Sales vs Expenses Bar Chart --}}
        <div class="xl:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-bold text-slate-800">Revenue vs Expenses</h3>
                    <p class="text-xs text-slate-500">Monthly comparison for {{ date('Y') }}</p>
                </div>
                <span class="text-xs text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                    <i class="fa-solid fa-chart-column mr-1"></i>Bar Chart
                </span>
            </div>
            <canvas id="salesExpenseChart" height="120"></canvas>
        </div>

        {{-- Expense Categories Pie --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-bold text-slate-800">Expense Breakdown</h3>
                    <p class="text-xs text-slate-500">By category</p>
                </div>
                <span class="text-xs text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                    <i class="fa-solid fa-chart-pie mr-1"></i>Pie
                </span>
            </div>
            @if(count($pieValues) > 0)
                <canvas id="expensePieChart" height="220"></canvas>
            @else
                <div class="flex flex-col items-center justify-center h-48 text-slate-400">
                    <i class="fa-regular fa-chart-pie text-4xl mb-3"></i>
                    <p class="text-sm font-medium">No expense data yet</p>
                    <a href="{{ route('expenses.create') }}" class="mt-3 text-xs font-bold text-teal-600 hover:text-teal-700">Add first expense →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Row: Recent Transactions + Low Stock Alerts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Recent Transactions --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Recent Transactions</h3>
                <div class="flex gap-2">
                    <a href="{{ route('expenses.index') }}" class="text-xs font-semibold text-slate-500 hover:text-teal-600 transition-colors">Expenses</a>
                    <span class="text-slate-300">|</span>
                    <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-slate-500 hover:text-teal-600 transition-colors">Sales</a>
                </div>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentTransactions as $tx)
                    <div class="flex items-center px-6 py-3.5 hover:bg-slate-50/50 transition-colors">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 mr-4 {{ $tx['type'] === 'sale' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            <i class="fa-solid {{ $tx['type'] === 'sale' ? 'fa-arrow-down' : 'fa-arrow-up' }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $tx['title'] }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($tx['date'])->format('d M Y') }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-2xs font-semibold uppercase bg-slate-100 text-slate-500">
                                    {{ $tx['payment_method'] }}
                                </span>
                            </div>
                        </div>
                        <span class="text-sm font-bold {{ $tx['type'] === 'sale' ? 'text-emerald-600' : 'text-rose-600' }} ml-3">
                            {{ $tx['type'] === 'sale' ? '+' : '-' }}₹{{ number_format($tx['amount'], 0) }}
                        </span>
                    </div>
                @empty
                    <div class="py-10 text-center text-slate-400">
                        <i class="fa-regular fa-file-lines text-4xl mb-3 block"></i>
                        <p class="text-sm font-medium">No transactions recorded yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Low Stock Alert Panel --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Low Stock Alerts</h3>
                @if($lowStockCount > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                        {{ $lowStockCount }} items
                    </span>
                @endif
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($lowStockProducts->take(6) as $product)
                    <div class="flex items-center px-6 py-3 hover:bg-slate-50/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-slate-400">SKU: {{ $product->sku }}</p>
                        </div>
                        <span class="ml-2 text-sm font-extrabold text-rose-600">{{ $product->quantity }}</span>
                    </div>
                @empty
                    <div class="py-10 text-center text-slate-400">
                        <i class="fa-solid fa-box-open text-4xl mb-3 block text-emerald-200"></i>
                        <p class="text-sm font-medium text-emerald-600">All products well stocked!</p>
                    </div>
                @endforelse
            </div>
            @if($lowStockCount > 0)
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100">
                    <a href="{{ route('inventory.index') }}?low_stock=1" class="text-xs font-bold text-amber-600 hover:text-amber-700">
                        View all low stock items →
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Monthly Sales vs Expenses Bar Chart
    const salesCtx = document.getElementById('salesExpenseChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            datasets: [
                {
                    label: 'Sales Revenue',
                    data: @json($salesChartData),
                    backgroundColor: 'rgba(20, 184, 166, 0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Expenses',
                    data: @json($expensesChartData),
                    backgroundColor: 'rgba(244, 63, 94, 0.7)',
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { font: { family: 'Outfit', size: 12 }, color: '#64748b' }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₹' + ctx.raw.toLocaleString('en-IN')
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { family: 'Outfit' } } },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'Outfit' },
                        callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v)
                    }
                }
            }
        }
    });

    @if(count($pieValues) > 0)
    // Expense Pie Chart
    const pieCtx = document.getElementById('expensePieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: @json($pieLabels),
            datasets: [{
                data: @json($pieValues),
                backgroundColor: [
                    '#14b8a6','#6366f1','#f43f5e','#f59e0b','#22c55e','#8b5cf6','#0ea5e9','#ec4899'
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { family: 'Outfit', size: 11 }, color: '#64748b', padding: 12 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ₹' + ctx.raw.toLocaleString('en-IN')
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
@endsection
