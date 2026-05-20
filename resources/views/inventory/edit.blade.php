@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{ cost: {{ $product->cost_price }}, sell: {{ $product->selling_price }} }">

    {{-- Breadcrumbs & Header --}}
    <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
        <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
        <a href="{{ route('inventory.index') }}" class="hover:text-slate-600">Inventory</a>
        <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
        <span class="text-slate-600">Edit Product</span>
    </div>

    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Product Details</h1>
        <p class="text-sm text-slate-500 mt-1">Update catalog parameters, price tiers, and inventory stock balances.</p>
    </div>

    {{-- Validation Error Alerts --}}
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

    {{-- Form Container --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('inventory.update', $product->id) }}" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Product Name --}}
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required 
                           class="w-full px-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                </div>

                {{-- SKU Bar --}}
                <div x-data="{ sku: '{{ old('sku', $product->sku) }}' }">
                    <label for="sku" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">SKU Stock Code *</label>
                    <div class="relative">
                        <input type="text" name="sku" id="sku" x-model="sku" required 
                               class="w-full pl-4 pr-10 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors uppercase">
                        <button type="button" @click="sku = 'SKU-' + Math.floor(100000 + Math.random() * 900000)" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-teal-600 transition-colors" title="Generate SKU Code">
                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Cost Price --}}
                <div>
                    <label for="cost_price" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cost Price (Purchase) *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-semibold">
                            ₹
                        </div>
                        <input type="number" name="cost_price" id="cost_price" step="0.01" min="0" required 
                               x-model.number="cost"
                               class="w-full pl-8 pr-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    </div>
                </div>

                {{-- Selling Price --}}
                <div>
                    <label for="selling_price" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Selling Price (Retail) *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm font-semibold">
                            ₹
                        </div>
                        <input type="number" name="selling_price" id="selling_price" step="0.01" min="0" required 
                               x-model.number="sell"
                               class="w-full pl-8 pr-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    </div>
                </div>

                {{-- Margin Calculations Preview --}}
                <div class="bg-slate-50 border border-slate-100 rounded-2xl px-4 py-2.5 flex items-center justify-between">
                    <div>
                        <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider">Estimated Profit Margin</span>
                        <span class="block text-sm font-black text-emerald-600 mt-0.5">
                            ₹<span x-text="isNaN(sell - cost) ? '0.00' : (sell - cost).toFixed(2)"></span>
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wider">Markup Margin</span>
                        <span class="block text-2xs font-semibold text-slate-500 mt-1 bg-slate-200/60 px-2 py-0.5 rounded">
                            <span x-text="isNaN(cost) || cost === 0 ? '0' : (((sell - cost) / cost) * 100).toFixed(0)"></span>%
                        </span>
                    </div>
                </div>

                {{-- Initial Quantity --}}
                <div>
                    <label for="quantity" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Stock Quantity *</label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $product->quantity) }}" min="0" required 
                           class="w-full px-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                </div>

                {{-- Low Stock Notify threshold --}}
                <div>
                    <label for="low_stock_threshold" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Low Stock Alert Level</label>
                    <input type="number" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0" 
                           class="w-full px-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                </div>

                {{-- Barcode --}}
                <div>
                    <label for="barcode" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Barcode EAN / UPC</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-barcode text-xs"></i>
                        </div>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}" placeholder="Scan barcode" 
                               class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                    </div>
                </div>

                {{-- Supplier --}}
                <div class="md:col-span-2">
                    <label for="supplier_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Supplier Sourced From</label>
                    <select name="supplier_id" id="supplier_id" 
                            class="w-full py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">
                        <option value="">Direct Sourcing (No Supplier)</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }} ({{ $supplier->phone ?? 'No Phone' }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Image Sizing --}}
                <div x-data="{ imgName: '' }">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Product Photo</label>
                    <div class="flex items-center gap-4">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100 flex-shrink-0">
                        @endif
                        <label for="image" class="flex flex-col items-center justify-center w-full h-16 border-2 border-slate-200 border-dashed rounded-xl cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition-colors flex-1">
                            <div class="flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-sm"></i>
                                <span class="text-3xs font-bold text-slate-500 truncate max-w-[120px]" x-text="imgName ? imgName : 'Change photo'"></span >
                            </div>
                            <input type="file" name="image" id="image" class="hidden" 
                                   @change="imgName = $event.target.files[0] ? $event.target.files[0].name : ''">
                        </label>
                    </div>
                </div>

                {{-- Description --}}
                <div class="md:col-span-3">
                    <label for="description" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" placeholder="Enter details..." 
                              class="w-full px-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors">{{ old('description', $product->description) }}</textarea>
                </div>

            </div>

            {{-- Form buttons --}}
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-500 bg-slate-50 hover:bg-slate-100 hover:text-slate-700 border border-slate-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 shadow-md shadow-teal-500/10 transition-colors">
                    Save Changes
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
