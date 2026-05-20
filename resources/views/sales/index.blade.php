@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sales Orders & Invoice Logs</h1>
            <p class="text-sm text-slate-500 mt-1">Review historical invoices, checkout logs, tax receipts, and payment logs</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isOwner() || auth()->user()->isStaff())
                <a href="{{ route('sales.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 transition-all duration-200 shadow-md shadow-emerald-500/10">
                    <i class="fa-solid fa-cart-shopping mr-2"></i> Launch POS Register
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            {{-- Search Input --}}
            <div>
                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search Invoice</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Invoice #, Client name..." 
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors placeholder:text-slate-400">
                </div>
            </div>

            {{-- Payment Mode --}}
            <div>
                <label for="payment_method" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Method</label>
                <select name="payment_method" id="payment_method" 
                        class="w-full py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="upi" {{ request('payment_method') === 'upi' ? 'selected' : '' }}>UPI</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="net_banking" {{ request('payment_method') === 'net_banking' ? 'selected' : '' }}>Net Banking</option>
                    <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>Store Credit (Udhaar)</option>
                </select>
            </div>

            {{-- Date filters --}}
            <div>
                <label for="start_date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                       class="w-full py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors text-slate-700">
            </div>

            <div>
                <label for="end_date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">End Date</label>
                <div class="flex gap-2">
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="w-full py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors text-slate-700">
                    <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-100 hover:bg-teal-100 transition-colors flex-shrink-0" title="Apply filters">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'payment_method', 'start_date', 'end_date']))
                        <a href="{{ route('sales.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 transition-colors flex-shrink-0" title="Reset Filters">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    {{-- Sales Orders Logs Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Invoice #</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Customer Name</th>
                        <th class="px-6 py-4">Checkout By</th>
                        <th class="px-6 py-4">Payment Method</th>
                        <th class="px-6 py-4 text-right">Tax Collected</th>
                        <th class="px-6 py-4 text-right">Total Amount</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/30 transition-colors text-sm text-slate-700">
                            {{-- Invoice ID --}}
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">
                                <a href="{{ route('sales.show', $sale->id) }}" class="text-teal-600 hover:text-teal-700 transition-colors">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            
                            {{-- Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-medium">
                                {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}
                            </td>

                            {{-- Customer details --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800">{{ $sale->customer_name ?? 'Walk-in Customer' }}</span>
                                    @if($sale->customer)
                                        <span class="text-3xs text-slate-400 font-semibold mt-0.5"><i class="fa-solid fa-id-badge mr-1"></i>Reg Client</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Operator Sourced --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <span class="text-slate-550 font-semibold">{{ $sale->user->name ?? 'System' }}</span>
                            </td>

                            {{-- Payment method badge --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-2xs font-bold uppercase tracking-wider 
                                    @if($sale->payment_method === 'cash') bg-emerald-50 text-emerald-700 border border-emerald-100
                                    @elseif($sale->payment_method === 'upi') bg-indigo-50 text-indigo-700 border border-indigo-100
                                    @elseif($sale->payment_method === 'card') bg-blue-50 text-blue-700 border border-blue-100
                                    @elseif($sale->payment_method === 'credit') bg-amber-50 text-amber-700 border border-amber-100
                                    @else bg-slate-50 text-slate-700 border border-slate-150 @endif">
                                    <i class="fa-solid 
                                        @if($sale->payment_method === 'cash') fa-wallet
                                        @elseif($sale->payment_method === 'upi') fa-qrcode
                                        @elseif($sale->payment_method === 'card') fa-credit-card
                                        @elseif($sale->payment_method === 'credit') fa-handshake-angle
                                        @else fa-circle-dollar-to-slot @endif mr-1 text-3xs"></i>
                                    {{ $sale->payment_method }}
                                </span>
                            </td>

                            {{-- Tax --}}
                            <td class="px-6 py-4 text-right whitespace-nowrap text-xs font-semibold text-slate-500">
                                ₹{{ number_format($sale->tax_amount, 2) }}
                            </td>

                            {{-- Grand Total --}}
                            <td class="px-6 py-4 text-right whitespace-nowrap font-black text-slate-900 text-sm">
                                ₹{{ number_format($sale->total_amount, 2) }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-teal-50 hover:text-teal-600 border border-slate-200 hover:border-teal-100 transition-all" title="View details & Invoice">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('invoices.pdf', $sale->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 border border-slate-200 hover:border-indigo-100 transition-all" title="Download PDF Invoice">
                                        <i class="fa-regular fa-file-pdf text-xs"></i>
                                    </a>
                                    @if(auth()->user()->isOwner())
                                        <form method="POST" action="{{ route('sales.destroy', $sale->id) }}" onsubmit="return confirm('Cancel this sale? This will RESTORE items to stock inventory and void Udhaar records. This action is irreversible.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-rose-50 hover:text-rose-600 border border-slate-200 hover:border-rose-100 transition-all" title="Cancel/Void Sale">
                                                <i class="fa-solid fa-ban text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                        <i class="fa-solid fa-cart-arrow-down text-lg text-slate-450"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">No sale orders found</p>
                                    <p class="text-xs text-slate-400 mt-1">Generate a checkout order at POS to view records</p>
                                    @if(auth()->user()->isOwner() || auth()->user()->isStaff())
                                        <a href="{{ route('sales.create') }}" class="mt-4 inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-teal-400 hover:bg-teal-500 transition-colors shadow-sm">
                                            Launch POS Register
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($sales->hasPages())
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
