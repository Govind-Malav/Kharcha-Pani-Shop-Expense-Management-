@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    {{-- Breadcrumbs & Header --}}
    <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
        <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
        <a href="{{ route('expenses.index') }}" class="hover:text-slate-600">Expenses</a>
        <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
        <span class="text-slate-600">Record Expense</span>
    </div>

    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Record Store Expense</h1>
        <p class="text-sm text-slate-500 mt-1">Deduct operational payouts and bill payments directly from store cashflows.</p>
    </div>

    {{-- Error messages block --}}
    @if($errors->any())
        <div class="bg-rose-50 text-rose-800 p-4 rounded-2xl border border-rose-100 text-xs font-semibold space-y-1">
            <div class="flex items-center text-sm font-bold text-rose-900 mb-1">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> Validation Failed
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Title --}}
                <div class="md:col-span-2">
                    <label for="title" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Expense Title *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Electric bill April, Tea & Snacks" 
                           class="w-full px-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                </div>

                {{-- Amount --}}
                <div>
                    <label for="amount" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Amount (INR) *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-semibold">
                            ₹
                        </div>
                        <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required placeholder="0.00" 
                               class="w-full pl-8 pr-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    </div>
                </div>

                {{-- Category --}}
                <div>
                    <label for="category_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category *</label>
                    <select name="category_id" id="category_id" required 
                            class="w-full py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                        <option value="" disabled selected>Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date --}}
                <div>
                    <label for="date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Date *</label>
                    <input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required 
                           class="w-full py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors text-slate-700">
                </div>

                {{-- Payment Type --}}
                <div>
                    <label for="payment_type" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Mode *</label>
                    <select name="payment_type" id="payment_type" required 
                            class="w-full py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                        <option value="cash" {{ old('payment_type', 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ old('payment_type') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="upi" {{ old('payment_type') === 'upi' ? 'selected' : '' }}>UPI (GPay / PhonePe / Paytm)</option>
                        <option value="net_banking" {{ old('payment_type') === 'net_banking' ? 'selected' : '' }}>Net Banking</option>
                        <option value="other" {{ old('payment_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Description / Notes</label>
                    <textarea name="description" id="description" rows="3" placeholder="Enter optional details, supplier name, etc..." 
                              class="w-full px-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">{{ old('description') }}</textarea>
                </div>

                {{-- Receipt Upload --}}
                <div class="md:col-span-2" x-data="{ fileName: '' }">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Upload Bill / Receipt Image</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="receipt_image" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-200 border-dashed rounded-2xl cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-2xl mb-2"></i>
                                <p class="text-xs font-bold text-slate-500" x-text="fileName ? fileName : 'Upload bill image (JPEG, PNG, max 2MB)'"></p>
                                <p class="text-2xs text-slate-400 mt-0.5" x-show="!fileName">Drag & drop or click to browse</p>
                            </div>
                            <input type="file" name="receipt_image" id="receipt_image" class="hidden" 
                                   @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                        </label>
                    </div>
                </div>

            </div>

            {{-- Submit Footer --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-500 bg-slate-50 hover:bg-slate-100 hover:text-slate-700 border border-slate-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 shadow-md shadow-emerald-500/10 transition-colors">
                    Save Record
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
