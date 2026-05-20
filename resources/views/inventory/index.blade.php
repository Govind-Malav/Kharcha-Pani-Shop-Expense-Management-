@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Inventory Stock Manager</h1>
            <p class="text-sm text-slate-500 mt-1">Track store items, margins, barcodes, supplier info, and quantity thresholds</p>
        </div>
        <div class="flex items-center gap-3">
            @if(request('low_stock'))
                <a href="{{ route('inventory.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-colors shadow-sm">
                    <i class="fa-solid fa-boxes-stacked mr-2"></i> Show All Stock
                </a>
            @else
                <a href="{{ route('inventory.index') }}?low_stock=1" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-colors shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-amber-500"></i> Show Low Stock Items
                </a>
            @endif
            @if(auth()->user()->isOwner())
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 transition-all duration-200 shadow-md shadow-emerald-500/10">
                    <i class="fa-solid fa-plus mr-2"></i> Add Product
                </a>
            @endif
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            @if(request('low_stock'))
                <input type="hidden" name="low_stock" value="1">
            @endif
            
            {{-- Search Field --}}
            <div class="flex-1 w-full">
                <label for="search" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Search Products</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Product Name, SKU, Barcode, Supplier..." 
                           class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors placeholder:text-slate-400">
                </div>
            </div>

            {{-- Submit/Reset --}}
            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-100 transition-colors">
                    <i class="fa-solid fa-filter mr-2"></i> Apply Search
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('inventory.index', request()->only('low_stock')) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100 transition-colors flex-shrink-0" title="Reset Search">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- Inventory Grid / Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Product Info</th>
                        <th class="px-6 py-4">SKU / Barcode</th>
                        <th class="px-6 py-4 text-center">In Stock</th>
                        <th class="px-6 py-4 text-right">Cost Price</th>
                        <th class="px-6 py-4 text-right">Selling Price</th>
                        <th class="px-6 py-4 text-center">Margin</th>
                        <th class="px-6 py-4">Supplier</th>
                        @if(auth()->user()->isOwner())
                            <th class="px-6 py-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($products as $product)
                        @php
                            $isLowStock = $product->quantity <= ($product->low_stock_threshold ?? 10);
                            $margin = $product->selling_price - $product->cost_price;
                            $marginPercent = $product->cost_price > 0 ? ($margin / $product->cost_price) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/30 transition-colors text-sm text-slate-700">
                            {{-- Product Info (Thumbnail + Name) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" 
                                             class="w-10 h-10 rounded-xl object-cover border border-slate-100">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center border border-slate-200">
                                            <i class="fa-solid fa-box text-sm"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="block font-bold text-slate-800">{{ $product->name }}</span>
                                        @if($product->description)
                                            <span class="block text-2xs text-slate-450 mt-0.5 max-w-[200px] truncate">{{ $product->description }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- SKU / Barcode --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <div>
                                    <span class="block font-semibold text-slate-600">SKU: {{ $product->sku }}</span>
                                    @if($product->barcode)
                                        <span class="block text-slate-400 mt-0.5"><i class="fa-solid fa-barcode mr-1 text-2xs"></i>{{ $product->barcode }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Stock Count + Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-base font-extrabold {{ $isLowStock ? 'text-rose-600' : 'text-slate-900' }}">
                                        {{ $product->quantity }}
                                    </span>
                                    @if($isLowStock)
                                        <span class="inline-flex items-center px-1.5 py-0.2 mt-1 rounded text-3xs font-extrabold uppercase bg-rose-50 text-rose-600 border border-rose-100">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.2 mt-1 rounded text-3xs font-extrabold uppercase bg-emerald-50 text-emerald-600 border border-emerald-100">
                                            Healthy
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Cost Price --}}
                            <td class="px-6 py-4 text-right font-medium text-slate-600 whitespace-nowrap">
                                ₹{{ number_format($product->cost_price, 2) }}
                            </td>

                            {{-- Selling Price --}}
                            <td class="px-6 py-4 text-right font-bold text-slate-900 whitespace-nowrap">
                                ₹{{ number_format($product->selling_price, 2) }}
                            </td>

                            {{-- Profit Margin Markup --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-xs font-bold text-emerald-600">+₹{{ number_format($margin, 2) }}</span>
                                    <span class="text-3xs text-slate-400 font-semibold mt-0.5">{{ number_format($marginPercent, 0) }}% markup</span>
                                </div>
                            </td>

                            {{-- Supplier Info --}}
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-medium">
                                @if($product->supplier)
                                    <div>
                                        <span class="block text-slate-700 font-semibold">{{ $product->supplier->name }}</span>
                                        @if($product->supplier->phone)
                                            <span class="block text-slate-400 mt-0.5"><i class="fa-solid fa-phone mr-1 text-3xs"></i>{{ $product->supplier->phone }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-350 italic">Direct Sourced</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            @if(auth()->user()->isOwner())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('inventory.edit', $product->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-teal-50 hover:text-teal-600 border border-slate-200 hover:border-teal-100 transition-all" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('inventory.destroy', $product->id) }}" onsubmit="return confirm('Are you sure you want to delete this product? Historical sales will not be affected, but the product will be removed from inventory.')">
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
                            <td colspan="{{ auth()->user()->isOwner() ? 8 : 7 }}" class="py-16 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                        <i class="fa-solid fa-cubes-stacked text-lg text-slate-450"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700">No products found</p>
                                    <p class="text-xs text-slate-400 mt-1">Add items or clear search filters to view inventory</p>
                                    @if(auth()->user()->isOwner())
                                        <a href="{{ route('inventory.create') }}" class="mt-4 inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-slate-950 bg-teal-400 hover:bg-teal-500 transition-colors shadow-sm">
                                            Add First Product
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
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
