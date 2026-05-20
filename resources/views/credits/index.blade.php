@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showAddCreditModal: false, personSource: 'manual', creditType: 'customer' }">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Credits & Udhaar Ledger</h1>
            <p class="text-sm text-slate-500 mt-1">Track pending customer dues (Udhaar) and outstanding supplier payments</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isOwner() || auth()->user()->isStaff())
                <button @click="showAddCreditModal = true" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 transition-all duration-200 shadow-md shadow-emerald-500/10">
                    <i class="fa-solid fa-handshake-angle mr-2"></i> Open Credit Account
                </button>
            @endif
        </div>
    </div>

    {{-- Credit Summary Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        {{-- Total Customer Udhaar outstanding --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Customer Udhaar Pending</span>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Receivables</span>
            </div>
            @php
                $custOutstanding = \App\Models\Credit::where('type', 'customer')->where('status', 'pending')->get()->sum(fn($c) => $c->amount - $c->paid_amount);
            @endphp
            <div class="text-2xl font-extrabold text-slate-900">₹{{ number_format($custOutstanding, 2) }}</div>
            <p class="text-3xs text-slate-400 mt-1">Funds expected to be paid back by shop customers</p>
        </div>

        {{-- Supplier Dues outstanding --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Supplier Dues Outstanding</span>
                <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">Payables</span>
            </div>
            @php
                $suppOutstanding = \App\Models\Credit::where('type', 'supplier')->where('status', 'pending')->get()->sum(fn($c) => $c->amount - $c->paid_amount);
            @endphp
            <div class="text-2xl font-extrabold text-slate-900">₹{{ number_format($suppOutstanding, 2) }}</div>
            <p class="text-3xs text-slate-400 mt-1">Purchase bills due to wholesalers & suppliers</p>
        </div>

        {{-- Overall Ledger Status --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-3xs font-bold text-slate-400 uppercase tracking-wider">Ledger Net Position</span>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">Summary</span>
            </div>
            @php
                $netLedger = $custOutstanding - $suppOutstanding;
            @endphp
            <div class="text-2xl font-extrabold {{ $netLedger >= 0 ? 'text-teal-600' : 'text-rose-600' }}">
                ₹{{ number_format(abs($netLedger), 2) }}
                <span class="text-xs font-bold">{{ $netLedger >= 0 ? 'Credit Net Surplus' : 'Net Deficit (Owed)' }}</span>
            </div>
            <p class="text-3xs text-slate-400 mt-1">Difference between store receivables and payables</p>
        </div>

    </div>

    {{-- Filters Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="GET" action="{{ route('credits.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            {{-- Search Input --}}
            <div>
                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search Account</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, Phone number..." 
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors placeholder:text-slate-400">
                </div>
            </div>

            {{-- Credit Type --}}
            <div>
                <label for="type" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Account Type</label>
                <select name="type" id="type" 
                        class="w-full py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    <option value="">All Accounts</option>
                    <option value="customer" {{ request('type') === 'customer' ? 'selected' : '' }}>Customer (Receivables)</option>
                    <option value="supplier" {{ request('type') === 'supplier' ? 'selected' : '' }}>Supplier (Payables)</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label for="status" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Status</label>
                <select name="status" id="status" 
                        class="w-full py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Unpaid Dues)</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Fully Settled</option>
                </select>
            </div>

            {{-- Filter apply actions --}}
            <div class="flex items-end">
                <div class="flex gap-2 w-full">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-100 transition-colors flex-1 shadow-sm">
                        <i class="fa-solid fa-filter mr-2"></i> Apply filters
                    </button>
                    @if(request()->anyFilled(['search', 'type', 'status']))
                        <a href="{{ route('credits.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 transition-colors flex-shrink-0" title="Reset Filters">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    {{-- Credit Ledger Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Billed Party</th>
                        <th class="px-6 py-4 text-center">Type</th>
                        <th class="px-6 py-4">Repay Due Date</th>
                        <th class="px-6 py-4 text-right">Initial Credit</th>
                        <th class="px-6 py-4 text-right">Amount Settled</th>
                        <th class="px-6 py-4 text-right">Balance Due</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($credits as $credit)
                        @php
                            $balance = $credit->amount - $credit->paid_amount;
                            $isOverdue = $credit->status === 'pending' && \Carbon\Carbon::parse($credit->due_date)->isPast();
                        @endphp
                        <tr class="hover:bg-slate-50/30 transition-colors text-sm text-slate-700">
                            {{-- Party Info --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900">{{ $credit->person_name }}</span>
                                    @if($credit->phone)
                                        <span class="text-xs text-slate-400 mt-0.5"><i class="fa-solid fa-phone mr-1 text-3xs"></i>{{ $credit->phone }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Type badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($credit->type === 'customer')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-3xs font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Client Owe
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-3xs font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-100">
                                        We Owe
                                    </span>
                                @endif
                            </td>

                            {{-- Due date --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                        {{ \Carbon\Carbon::parse($credit->due_date)->format('d M Y') }}
                                    </span>
                                    @if($isOverdue)
                                        <span class="inline-flex px-1.5 py-0.2 rounded text-3xs font-extrabold uppercase bg-rose-100 text-rose-700">Overdue</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Initial amount --}}
                            <td class="px-6 py-4 text-right font-medium text-slate-500 whitespace-nowrap">
                                ₹{{ number_format($credit->amount, 2) }}
                            </td>

                            {{-- Paid amount --}}
                            <td class="px-6 py-4 text-right font-semibold text-slate-655 whitespace-nowrap text-emerald-600">
                                ₹{{ number_format($credit->paid_amount, 2) }}
                            </td>

                            {{-- Balance pending --}}
                            <td class="px-6 py-4 text-right font-black text-slate-900 whitespace-nowrap text-base">
                                ₹{{ number_format($balance, 2) }}
                            </td>

                            {{-- Status badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($credit->status === 'paid')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Paid</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('credits.show', $credit->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-teal-50 hover:text-teal-600 border border-slate-200 hover:border-teal-100 transition-all" title="View details / Installments">
                                        <i class="fa-regular fa-eye text-xs"></i>
                                    </a>
                                    @if($credit->status === 'pending')
                                        <form method="POST" action="{{ route('credits.reminder', $credit->id) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 border border-slate-200 hover:border-indigo-100 transition-all" title="Send SMS/WhatsApp Repay Reminder">
                                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->isOwner())
                                        <form method="POST" action="{{ route('credits.destroy', $credit->id) }}" onsubmit="return confirm('Settle/delete this credit ledger completely? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-rose-50 hover:text-rose-600 border border-slate-200 hover:border-rose-100 transition-all" title="Delete Ledger">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
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
                                        <i class="fa-solid fa-handshake-slash text-lg text-slate-450"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">No credit accounts found</p>
                                    <p class="text-xs text-slate-400 mt-1">Open a credit account or clear filters to view ledger records</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($credits->hasPages())
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                {{ $credits->links() }}
            </div>
        @endif
    </div>

    {{-- Open Credit Account Modal --}}
    @if(auth()->user()->isOwner() || auth()->user()->isStaff())
        <div x-show="showAddCreditModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-lg overflow-hidden" @click.away="showAddCreditModal = false">
                
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-extrabold text-slate-900 flex items-center">
                        <i class="fa-solid fa-handshake-angle text-teal-500 mr-2.5"></i> Open Credit Account Line
                    </h3>
                    <button @click="showAddCreditModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('credits.store') }}" class="p-6 space-y-4">
                    @csrf

                    {{-- Credit Type Selection --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Account Line Type</label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-1 rounded-xl">
                            <label class="flex items-center justify-center py-2 px-1 text-xs font-extrabold rounded-lg cursor-pointer text-center uppercase tracking-wider transition-colors border"
                                   :class="creditType === 'customer' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600'">
                                <input type="radio" name="type" value="customer" x-model="creditType" class="hidden"> Customer Udhaar
                            </label>
                            <label class="flex items-center justify-center py-2 px-1 text-xs font-extrabold rounded-lg cursor-pointer text-center uppercase tracking-wider transition-colors border"
                                   :class="creditType === 'supplier' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600'">
                                <input type="radio" name="type" value="supplier" x-model="creditType" class="hidden"> Supplier Bill Owed
                            </label>
                        </div>
                    </div>

                    {{-- Customer/Supplier Person Selection Source --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Contact Source</label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-1 rounded-xl">
                            <label class="flex items-center justify-center py-1.5 px-1 text-2xs font-bold rounded-lg cursor-pointer text-center uppercase tracking-wider transition-colors border"
                                   :class="personSource === 'existing' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600'">
                                <input type="radio" name="person_source" value="existing" x-model="personSource" class="hidden"> Choose Registered
                            </label>
                            <label class="flex items-center justify-center py-1.5 px-1 text-2xs font-bold rounded-lg cursor-pointer text-center uppercase tracking-wider transition-colors border"
                                   :class="personSource === 'manual' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600'">
                                <input type="radio" name="person_source" value="manual" x-model="personSource" class="hidden"> Enter Manually
                            </label>
                        </div>
                    </div>

                    {{-- Existing Sourced list --}}
                    <div x-show="personSource === 'existing'">
                        <div x-show="creditType === 'customer'">
                            <label for="customer_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Customer *</label>
                            <select name="customer_id" id="customer_id" :required="personSource === 'existing' && creditType === 'customer'"
                                    class="w-full py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                                <option value="" disabled selected>Choose Registered Customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'No Phone' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="creditType === 'supplier'">
                            <label for="supplier_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Supplier *</label>
                            <select name="supplier_id" id="supplier_id" :required="personSource === 'existing' && creditType === 'supplier'"
                                    class="w-full py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                                <option value="" disabled selected>Choose Sourced Supplier</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'No Phone' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Manual Input Sourced --}}
                    <div x-show="personSource === 'manual'" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <label for="person_name" class="block text-xs font-bold text-slate-500 uppercase mb-1">Full Name *</label>
                            <input type="text" name="person_name" id="person_name" :required="personSource === 'manual'" placeholder="e.g. Ramesh Kumar" 
                                   class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 bg-white text-slate-700">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-500 uppercase mb-1">Mobile Phone</label>
                            <input type="text" name="phone" id="phone" placeholder="Contact number" 
                                   class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 bg-white text-slate-700">
                        </div>
                    </div>

                    {{-- Pricing / Finance --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="amount" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Credit Amount Owed *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs font-bold">
                                    ₹
                                </div>
                                <input type="number" name="amount" id="amount" required step="0.01" min="0.01" placeholder="0.00" 
                                       class="w-full pl-7 pr-3 py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                            </div>
                        </div>
                        <div>
                            <label for="paid_amount" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Upfront Paid Installment</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs font-bold">
                                    ₹
                                </div>
                                <input type="number" name="paid_amount" id="paid_amount" required step="0.01" min="0" placeholder="0.00" value="0.00"
                                       class="w-full pl-7 pr-3 py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                            </div>
                        </div>
                    </div>

                    {{-- Due Date --}}
                    <div>
                        <label for="due_date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Expected Settlement Due Date *</label>
                        <input type="date" name="due_date" id="due_date" required 
                               class="w-full px-4 py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Memo / Notes</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Reference invoice, purchase parameters, or agreement notes..." 
                                  class="w-full px-4 py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="showAddCreditModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 bg-slate-50 hover:bg-slate-100 hover:text-slate-700 border border-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-slate-950 bg-teal-400 hover:bg-teal-500 shadow-md shadow-teal-500/10 transition-colors">
                            Open Account
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

</div>
@endsection
