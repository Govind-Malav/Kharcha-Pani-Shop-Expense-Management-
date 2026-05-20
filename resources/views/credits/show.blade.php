@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ showPaymentModal: false }">

    {{-- Breadcrumbs & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
            <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
            <a href="{{ route('credits.index') }}" class="hover:text-slate-600">Credits</a>
            <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
            <span class="text-slate-600">Ledger details</span>
        </div>
        <div class="flex items-center gap-2">
            @if($credit->status === 'pending')
                <form method="POST" action="{{ route('credits.reminder', $credit->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                        <i class="fa-solid fa-paper-plane mr-1.5 text-slate-400"></i> Dispatch Reminder Alert
                    </button>
                </form>
                <button @click="showPaymentModal = true" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 transition-all shadow-md shadow-emerald-500/10">
                    <i class="fa-solid fa-indian-rupee-sign mr-1.5"></i> Record Installment Payment
                </button>
            @endif
        </div>
    </div>

    {{-- Dues Summary Cards --}}
    @php
        $balance = $credit->amount - $credit->paid_amount;
        $isOverdue = $credit->status === 'pending' && \Carbon\Carbon::parse($credit->due_date)->isPast();
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        {{-- Total Liability Owed --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Credit Limit</span>
            <div class="text-2xl font-black text-slate-900">₹{{ number_format($credit->amount, 2) }}</div>
            <span class="block text-3xs text-slate-400 mt-1">Recorded on {{ $credit->created_at->format('d M Y') }}</span>
        </div>

        {{-- Cleared Balance --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider mb-2">Settled Installments</span>
            <div class="text-2xl font-black text-emerald-600">₹{{ number_format($credit->paid_amount, 2) }}</div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ ($credit->amount > 0 ? ($credit->paid_amount / $credit->amount) : 0) * 100 }}%"></div>
            </div>
        </div>

        {{-- Balance Pending --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden">
            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider mb-2">Outstanding Dues</span>
            <div class="text-2xl font-black text-rose-600">₹{{ number_format($balance, 2) }}</div>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="text-3xs text-slate-400">Due by {{ \Carbon\Carbon::parse($credit->due_date)->format('d M Y') }}</span>
                @if($isOverdue)
                    <span class="inline-flex px-1 rounded text-3xs font-extrabold bg-rose-550 text-rose-600">Overdue</span>
                @endif
            </div>
        </div>

    </div>

    {{-- Detailed Account Parameters --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-6 md:p-8 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
            <h3 class="font-bold text-slate-800 flex items-center">
                <i class="fa-solid fa-id-card text-teal-500 mr-2"></i> Account Parameters
            </h3>
            <span class="inline-flex px-2 py-0.5 rounded text-2xs font-extrabold uppercase bg-slate-100 text-slate-700">
                {{ $credit->type === 'customer' ? 'Customer Account' : 'Supplier Account' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div>
                <span class="block text-3xs font-extrabold text-slate-400 uppercase mb-1">Party Name</span>
                <span class="block font-bold text-slate-800">{{ $credit->person_name }}</span>
            </div>
            <div>
                <span class="block text-3xs font-extrabold text-slate-400 uppercase mb-1">Contact Phone</span>
                <span class="block font-bold text-slate-800">{{ $credit->phone ?? 'Not Available' }}</span>
            </div>
            <div>
                <span class="block text-3xs font-extrabold text-slate-400 uppercase mb-1">Ledger Status</span>
                @if($credit->status === 'paid')
                    <span class="inline-flex items-center text-xs font-bold text-emerald-600"><i class="fa-solid fa-circle-check mr-1.5"></i> Settled / Voided</span>
                @else
                    <span class="inline-flex items-center text-xs font-bold text-amber-600 animate-pulse"><i class="fa-solid fa-circle-exclamation mr-1.5"></i> Pending Settlement</span>
                @endif
            </div>
            @if($credit->notes)
                <div class="md:col-span-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <span class="block text-3xs font-extrabold text-slate-400 uppercase mb-1">Internal Remarks Memo</span>
                    <p class="text-xs text-slate-600 leading-normal">{{ $credit->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Installments Settlement history --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800"><i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-2"></i> Settlement Installments Log</h3>
            <span class="text-xs font-bold text-slate-500" x-text="'{{ count($credit->payments) }} payments recorded'"></span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($credit->payments as $payment)
                <div class="p-5 flex items-center justify-between gap-4 text-sm hover:bg-slate-50/20 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800">₹{{ number_format($payment->amount_paid, 2) }}</span>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-450">
                                <span>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span>
                                <span>•</span>
                                <span class="uppercase font-semibold text-3xs bg-slate-200 text-slate-600 px-1 rounded">{{ $payment->payment_method }}</span>
                            </div>
                        </div>
                    </div>
                    @if($payment->notes)
                        <div class="text-right max-w-xs text-xs text-slate-400 italic">
                            {{ $payment->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-200"></i>
                    <p class="text-xs font-medium">No installment payouts documented yet</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Record Installment Payment --}}
    @if($credit->status === 'pending')
        <div x-show="showPaymentModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md overflow-hidden" @click.away="showPaymentModal = false">
                
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-extrabold text-slate-900 flex items-center">
                        <i class="fa-solid fa-indian-rupee-sign text-teal-500 mr-2.5"></i> Pay Installment
                    </h3>
                    <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('credits.payments.store', $credit->id) }}" class="p-6 space-y-4">
                    @csrf

                    {{-- Outstanding warning --}}
                    <div class="p-3 bg-indigo-50 border border-indigo-150 rounded-xl flex justify-between text-xs font-bold text-indigo-850">
                        <span>Max Balance Pending:</span>
                        <span>₹{{ number_format($balance, 2) }}</span>
                    </div>

                    {{-- Installment Paid Amount --}}
                    <div>
                        <label for="amount_paid" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Installment Payment Amount *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-semibold text-xs">
                                ₹
                            </div>
                            <input type="number" name="amount_paid" id="amount_paid" step="0.01" min="0.01" max="{{ $balance }}" required placeholder="0.00" value="{{ $balance }}"
                                   class="w-full pl-8 pr-4 py-2.5 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                        </div>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label for="payment_date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Date *</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full px-4 py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                    </div>

                    {{-- Mode of pay --}}
                    <div>
                        <label for="payment_method" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Mode *</label>
                        <select name="payment_method" id="payment_method" required 
                                class="w-full py-2.5 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                            <option value="cash">Cash Settlement</option>
                            <option value="upi">UPI / GPay / Paytm / PhonePe</option>
                            <option value="card">Card Checkout</option>
                            <option value="net_banking">Net Banking Transfer</option>
                            <option value="other">Other Mode</option>
                        </select>
                    </div>

                    {{-- Note --}}
                    <div>
                        <label for="payment_notes" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Memo Description</label>
                        <textarea name="notes" id="payment_notes" rows="2" placeholder="e.g. Cleared partial ledger..." 
                                  class="w-full px-4 py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="showPaymentModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 bg-slate-50 hover:bg-slate-100 hover:text-slate-700 border border-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-slate-950 bg-teal-400 hover:bg-teal-500 shadow-md shadow-teal-500/10 transition-colors">
                            Record Installment
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

</div>
@endsection
