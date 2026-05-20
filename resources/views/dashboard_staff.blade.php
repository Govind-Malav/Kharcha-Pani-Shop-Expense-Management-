@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Staff Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Manage daily transactions and track your recorded activity</p>
        </div>
        <span class="text-xs text-slate-400 font-medium bg-slate-100 px-3 py-1.5 rounded-lg">
            <i class="fa-regular fa-calendar mr-1.5"></i>{{ now()->format('D, d M Y') }}
        </span>
    </div>

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

        {{-- My Recorded Sales --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">My Recorded Sales</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-indian-rupee-sign text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900">₹{{ number_format($totalIncome, 0) }}</div>
            <p class="text-xs text-emerald-600 font-semibold mt-2">
                <i class="fa-solid fa-arrow-trend-up mr-1"></i>Your all-time sales input
            </p>
        </div>

        {{-- My Recorded Expenses --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">My Recorded Expenses</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-900">₹{{ number_format($totalExpenses, 0) }}</div>
            <p class="text-xs text-rose-600 font-semibold mt-2">
                <i class="fa-solid fa-arrow-trend-down mr-1"></i>Your total recorded expenses
            </p>
        </div>

        {{-- Store Low Stock Alert --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Low Stock Alert</span>
                <div class="w-10 h-10 rounded-xl {{ $lowStockCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-teal-50 text-teal-600' }} flex items-center justify-center">
                    <i class="fa-solid fa-box-open text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-extrabold {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-teal-700' }}">{{ $lowStockCount }}</div>
            <p class="text-xs {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-teal-600' }} font-semibold mt-2">
                @if($lowStockCount > 0)
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>Needs restocking attention
                @else
                    <i class="fa-solid fa-circle-check mr-1"></i>All inventory well stocked
                @endif
            </p>
        </div>

    </div>

    {{-- Content Layout --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Recent Transactions by the Staff --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800">My Recent Activity</h3>
                    <span class="text-xs text-slate-400 font-medium">Your last 5 transactions</span>
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
                        <div class="py-12 text-center text-slate-400">
                            <i class="fa-regular fa-file-lines text-4xl mb-3 block"></i>
                            <p class="text-sm font-medium">You haven't recorded any transactions yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('sales.index') }}" class="font-bold text-slate-600 hover:text-teal-600">View All Sales</a>
                <a href="{{ route('expenses.index') }}" class="font-bold text-slate-600 hover:text-rose-600">View All Expenses</a>
            </div>
        </div>

        {{-- Quick Actions Panel --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100">Quick Actions</h3>
                <div class="space-y-4">
                    {{-- New Sale Action --}}
                    <a href="{{ route('sales.create') }}" class="group flex items-center p-3 rounded-xl border border-slate-100 hover:border-emerald-100 hover:bg-emerald-50/20 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-cart-plus text-lg"></i>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">Record New Sale</p>
                            <p class="text-xs text-slate-400">Create a bill for custom products</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-emerald-500 transition-colors ml-2"></i>
                    </a>

                    {{-- Record Expense Action --}}
                    <a href="{{ route('expenses.create') }}" class="group flex items-center p-3 rounded-xl border border-slate-100 hover:border-rose-100 hover:bg-rose-50/20 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-bold text-slate-800 group-hover:text-rose-700 transition-colors">Add Expense</p>
                            <p class="text-xs text-slate-400">Record a shop daily spend</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-rose-500 transition-colors ml-2"></i>
                    </a>

                    {{-- View Inventory Action --}}
                    <a href="{{ route('inventory.index') }}" class="group flex items-center p-3 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/20 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mr-3 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-boxes-stacked text-lg"></i>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-700 transition-colors">Check Inventory</p>
                            <p class="text-xs text-slate-400">View stock levels & products</p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-indigo-500 transition-colors ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <p class="text-2xs text-slate-400">Log in as Owner or Accountant to see full financial analytics and profit margins.</p>
            </div>
        </div>

    </div>

</div>
@endsection
