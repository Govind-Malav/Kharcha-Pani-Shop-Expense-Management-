@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ledger Reports & Financial Audits</h1>
            <p class="text-sm text-slate-500 mt-1">Export transaction logs, tax audits, operational costs, and balance statements</p>
        </div>
    </div>

    {{-- Report Period Selectors --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-1.5 bg-slate-50 p-1 rounded-xl">
                <a href="{{ route('reports.index') }}?type=daily" 
                   class="px-4 py-2 text-xs font-bold rounded-lg transition-all border uppercase tracking-wider {{ $type === 'daily' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                    Daily
                </a>
                <a href="{{ route('reports.index') }}?type=weekly" 
                   class="px-4 py-2 text-xs font-bold rounded-lg transition-all border uppercase tracking-wider {{ $type === 'weekly' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                    Weekly
                </a>
                <a href="{{ route('reports.index') }}?type=monthly" 
                   class="px-4 py-2 text-xs font-bold rounded-lg transition-all border uppercase tracking-wider {{ $type === 'monthly' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                    Monthly
                </a>
                <a href="{{ route('reports.index') }}?type=yearly" 
                   class="px-4 py-2 text-xs font-bold rounded-lg transition-all border uppercase tracking-wider {{ $type === 'yearly' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                    Yearly
                </a>
                <a href="{{ route('reports.index') }}?type=custom" 
                   class="px-4 py-2 text-xs font-bold rounded-lg transition-all border uppercase tracking-wider {{ $type === 'custom' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                    Custom Range
                </a>
            </div>
            
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider bg-slate-100 border border-slate-150 px-3.5 py-2 rounded-xl">
                <i class="fa-regular fa-calendar-check text-slate-500 mr-1.5"></i>Period: 
                <span class="text-slate-800">{{ $startDate->format('d M Y') }}</span> to <span class="text-slate-800">{{ $endDate->format('d M Y') }}</span>
            </div>
        </div>

        {{-- Custom Date Form Selector --}}
        @if($type === 'custom')
            <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100 mt-3.5">
                <input type="hidden" name="type" value="custom">
                <div>
                    <label for="start_date" class="block text-3xs font-bold text-slate-450 uppercase mb-1.5">Start Sourced Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                           class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:border-teal-500">
                </div>
                <div>
                    <label for="end_date" class="block text-3xs font-bold text-slate-450 uppercase mb-1.5">End Sourced Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                           class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:border-teal-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-1.5 rounded-lg text-xs font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-100 transition-colors">
                        Generate Statement
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- Stats Cards Row --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        
        {{-- Total Sales --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Sales Revenue</span>
            <div class="text-2xl font-black text-slate-900">₹{{ number_format($totalSales, 2) }}</div>
            <span class="block text-3xs text-emerald-600 font-semibold mt-1"><i class="fa-solid fa-arrow-trend-up mr-1"></i>From {{ count($sales) }} transactions</span>
        </div>

        {{-- Total Spend --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider mb-2">Operational Outflows</span>
            <div class="text-2xl font-black text-slate-900">₹{{ number_format($totalExpenses, 2) }}</div>
            <span class="block text-3xs text-rose-600 font-semibold mt-1"><i class="fa-solid fa-arrow-trend-down mr-1"></i>From {{ count($expenses) }} payments</span>
        </div>

        {{-- Net Income profit margin --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider mb-2">Net Period Profit</span>
            <div class="text-2xl font-black {{ $profit >= 0 ? 'text-teal-600' : 'text-rose-600' }}">
                ₹{{ number_format($profit, 2) }}
            </div>
            <span class="block text-3xs text-slate-400 font-semibold mt-1">Net profit position</span>
        </div>

        {{-- Inventory health status --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider mb-2">Catalog Inventory Health</span>
            <div class="text-2xl font-black text-slate-900">
                {{ $totalInventoryItems }} <span class="text-xs font-semibold text-slate-500">items</span>
            </div>
            @if($lowStockItemsCount > 0)
                <span class="block text-3xs text-amber-600 font-bold mt-1 animate-pulse"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $lowStockItemsCount }} items low stock limit!</span>
            @else
                <span class="block text-3xs text-emerald-600 font-semibold mt-1"><i class="fa-solid fa-circle-check mr-1 animate-bounce"></i>All stocks healthy</span>
            @endif
        </div>

    </div>

    {{-- Side-by-side transaction audits grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Sales records audit --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-[400px]">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-800"><i class="fa-solid fa-cart-shopping text-teal-500 mr-2"></i> Sales Revenue Log</h3>
                <span class="text-xs font-bold text-slate-500">₹{{ number_format($totalSales, 0) }} total</span>
            </div>
            
            <div class="divide-y divide-slate-50 overflow-y-auto flex-1">
                @forelse($sales as $sale)
                    <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/10 text-xs">
                        <div class="min-w-0">
                            <span class="block font-bold text-slate-800">{{ $sale->invoice_number }}</span>
                            <div class="flex items-center gap-2 mt-0.5 text-3xs text-slate-400 font-semibold">
                                <span>{{ $sale->customer_name }}</span>
                                <span>•</span>
                                <span class="uppercase">{{ $sale->payment_method }}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="block font-black text-slate-900 text-sm">₹{{ number_format($sale->total_amount, 2) }}</span>
                            <span class="block text-3xs text-slate-400 font-semibold mt-0.5">{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 h-full flex flex-col items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-3xl mb-2 text-slate-200"></i>
                        <p class="text-xs font-medium">No sales recorded this period</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Expense payout records audit --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-[400px]">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-800"><i class="fa-solid fa-receipt text-rose-500 mr-2"></i> Expenditure Cost Log</h3>
                <span class="text-xs font-bold text-slate-500">₹{{ number_format($totalExpenses, 0) }} total</span>
            </div>
            
            <div class="divide-y divide-slate-50 overflow-y-auto flex-1">
                @forelse($expenses as $expense)
                    <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/10 text-xs">
                        <div class="min-w-0">
                            <span class="block font-bold text-slate-800">{{ $expense->title }}</span>
                            <div class="flex items-center gap-2 mt-0.5 text-3xs text-slate-400 font-semibold">
                                <span class="bg-slate-100 text-slate-600 px-1 rounded">{{ $expense->category->name }}</span>
                                <span>•</span>
                                <span class="uppercase">{{ $expense->payment_type }}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="block font-black text-slate-900 text-sm">₹{{ number_format($expense->amount, 2) }}</span>
                            <span class="block text-3xs text-slate-400 font-semibold mt-0.5">{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 h-full flex flex-col items-center justify-center">
                        <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-200"></i>
                        <p class="text-xs font-medium">No expenses recorded this period</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Export / File generator console + Historical log Sized grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        
        {{-- File Export Panel Console --}}
        <div class="xl:col-span-1 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 class="font-extrabold text-slate-900 border-b border-slate-150 pb-2.5 flex items-center text-base">
                <i class="fa-solid fa-file-export text-teal-500 mr-2 text-lg"></i> Compile Statement
            </h3>
            
            <form method="POST" action="{{ route('reports.export') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Export format file</label>
                    <div class="grid grid-cols-2 gap-3" x-data="{ format: 'csv' }">
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-colors"
                               :class="format === 'csv' ? 'border-teal-400 bg-teal-50/20 text-slate-900' : 'border-slate-200 hover:border-slate-300 text-slate-450 bg-slate-50/50'">
                            <input type="radio" name="format" value="csv" x-model="format" class="hidden" checked>
                            <i class="fa-solid fa-file-csv text-2xl mb-1 text-emerald-600"></i>
                            <span class="text-2xs font-extrabold uppercase tracking-wider">CSV Spreadsheet</span>
                        </label>
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-colors"
                               :class="format === 'pdf' ? 'border-teal-400 bg-teal-50/20 text-slate-900' : 'border-slate-200 hover:border-slate-300 text-slate-450 bg-slate-50/50'">
                            <input type="radio" name="format" value="pdf" x-model="format" class="hidden">
                            <i class="fa-solid fa-file-pdf text-2xl mb-1 text-rose-600"></i>
                            <span class="text-2xs font-extrabold uppercase tracking-wider">PDF Report Sheet</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl text-xs font-black text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 shadow-md shadow-emerald-500/10 transition-colors uppercase tracking-wider">
                    Compile & Download
                </button>
            </form>
        </div>

        {{-- Export Logs history --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-800"><i class="fa-solid fa-history text-indigo-500 mr-2"></i> Export compilation logs</h3>
                <span class="text-2xs text-slate-400 font-bold" x-text="'Audit history log'"></span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($reportsLog as $log)
                    <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/10 text-xs">
                        <div class="min-w-0">
                            <span class="block font-bold text-slate-850 truncate">{{ $log->title }}</span>
                            <div class="flex items-center gap-1.5 text-3xs text-slate-400 mt-1 font-semibold">
                                <span><i class="fa-solid fa-user-circle mr-1"></i>{{ $log->user->name ?? 'System' }}</span>
                                <span>•</span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <span class="flex-shrink-0 text-3xs font-extrabold uppercase tracking-wider bg-slate-100 border border-slate-200 text-slate-600 px-2 py-0.5 rounded">
                            {{ $log->file_path }}
                        </span>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400">
                        <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-200 animate-pulse"></i>
                        <p class="text-xs font-semibold text-slate-700">No exports conducted recently</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
