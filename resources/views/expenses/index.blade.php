@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showCategoryModal: false, categoryName: '', categoryDesc: '', message: '', errorMsg: '' }">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Expenses Ledger</h1>
            <p class="text-sm text-slate-500 mt-1">Track and manage store expenditures and supplier payouts</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isOwner())
                <button @click="showCategoryModal = true" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                    <i class="fa-solid fa-tags mr-2 text-slate-400"></i> Categories
                </button>
            @endif
            @if(auth()->user()->isOwner() || auth()->user()->isStaff())
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 transition-all duration-200 shadow-md shadow-emerald-500/10">
                    <i class="fa-solid fa-plus mr-2"></i> Record Expense
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            {{-- Search Input --}}
            <div>
                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Title, amount..." 
                           class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors placeholder:text-slate-400">
                </div>
            </div>

            {{-- Category Filter --}}
            <div>
                <label for="category_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category</label>
                <select name="category_id" id="category_id" 
                        class="w-full py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
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
                    <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-100 hover:bg-teal-100 transition-colors flex-shrink-0" title="Apply Filters">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'category_id', 'start_date', 'end_date']))
                        <a href="{{ route('expenses.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 transition-colors flex-shrink-0" title="Clear Filters">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    {{-- Expenses Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Expense Title</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Recorded By</th>
                        <th class="px-6 py-4">Payment Type</th>
                        <th class="px-6 py-4">Receipt</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                        @if(auth()->user()->isOwner())
                            <th class="px-6 py-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50/30 transition-colors text-sm text-slate-700">
                            <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <span class="font-semibold text-slate-800">{{ $expense->title }}</span>
                                    @if($expense->description)
                                        <p class="text-xs text-slate-400 mt-0.5 max-w-xs truncate" title="{{ $expense->description }}">{{ $expense->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    <i class="fa-solid fa-tag mr-1 text-slate-400 text-2xs"></i>{{ $expense->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-3xs font-extrabold uppercase">
                                        {{ substr($expense->user->name ?? '?', 0, 2) }}
                                    </div>
                                    <span class="text-xs text-slate-500 font-medium">{{ $expense->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-2xs font-bold uppercase tracking-wider 
                                    @if($expense->payment_type === 'cash') bg-emerald-50 text-emerald-700 border border-emerald-100
                                    @elseif($expense->payment_type === 'upi') bg-indigo-50 text-indigo-700 border border-indigo-100
                                    @elseif($expense->payment_type === 'card') bg-blue-50 text-blue-700 border border-blue-100
                                    @elseif($expense->payment_type === 'net_banking') bg-purple-50 text-purple-700 border border-purple-100
                                    @else bg-slate-50 text-slate-700 border border-slate-150 @endif">
                                    {{ str_replace('_', ' ', $expense->payment_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($expense->receipt_image)
                                    <a href="{{ asset('storage/' . $expense->receipt_image) }}" target="_blank" 
                                       class="inline-flex items-center text-xs font-bold text-teal-600 hover:text-teal-700 transition-colors">
                                        <i class="fa-regular fa-image mr-1 text-sm"></i> View File
                                    </a>
                                @else
                                    <span class="text-xs text-slate-350 italic">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900 whitespace-nowrap text-base">
                                ₹{{ number_format($expense->amount, 2) }}
                            </td>
                            @if(auth()->user()->isOwner())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('expenses.edit', $expense->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-teal-50 hover:text-teal-600 border border-slate-200 hover:border-teal-100 transition-all" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}" onsubmit="return confirm('Are you sure you want to delete this expense record? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-rose-50 hover:text-rose-600 border border-slate-200 hover:border-rose-100 transition-all" title="Delete">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isOwner() ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                        <i class="fa-regular fa-folder-open text-lg text-slate-450"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">No expenses recorded</p>
                                    <p class="text-xs text-slate-400 mt-1">Add items or clear filters to see records</p>
                                    @if(auth()->user()->isOwner() || auth()->user()->isStaff())
                                        <a href="{{ route('expenses.create') }}" class="mt-4 inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-teal-400 hover:bg-teal-500 transition-colors">
                                            Add First Expense
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
        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    {{-- Expense Categories Management Modal --}}
    @if(auth()->user()->isOwner())
        <div x-show="showCategoryModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md overflow-hidden" @click.away="showCategoryModal = false">
                
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 flex items-center">
                        <i class="fa-solid fa-tags text-teal-500 mr-2.5"></i> Add Custom Category
                    </h3>
                    <button @click="showCategoryModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="
                    message = ''; errorMsg = '';
                    fetch('{{ route('expenses.categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ name: categoryName, description: categoryDesc })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            message = data.message;
                            categoryName = '';
                            categoryDesc = '';
                            setTimeout(() => { showCategoryModal = false; window.location.reload(); }, 1200);
                        } else {
                            errorMsg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.error || 'Validation error');
                        }
                    })
                    .catch(err => { errorMsg = 'Something went wrong. Please try again.'; })
                " class="p-6 space-y-4">
                    
                    <div x-show="message" class="p-3 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-xl border border-emerald-100" style="display: none;">
                        <i class="fa-solid fa-circle-check mr-1.5"></i> <span x-text="message"></span>
                    </div>

                    <div x-show="errorMsg" class="p-3 bg-rose-50 text-rose-750 text-xs font-semibold rounded-xl border border-rose-100" style="display: none;">
                        <i class="fa-solid fa-circle-exclamation mr-1.5"></i> <span x-text="errorMsg"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Category Name *</label>
                        <input type="text" x-model="categoryName" required placeholder="e.g. Electricity, Marketing" 
                               class="w-full px-4 py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Description</label>
                        <textarea x-model="categoryDesc" rows="3" placeholder="Brief note about expenditures in this category..." 
                                  class="w-full px-4 py-2 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button" @click="showCategoryModal = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-500 bg-slate-50 hover:bg-slate-100 hover:text-slate-700 border border-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-sm font-bold text-slate-950 bg-teal-400 hover:bg-teal-500 shadow-md shadow-teal-500/10 transition-colors">
                            Save Category
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

</div>
@endsection
