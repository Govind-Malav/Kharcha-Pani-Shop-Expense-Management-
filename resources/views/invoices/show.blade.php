@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- Breadcrumbs & Top Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
            <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
            <a href="{{ route('sales.index') }}" class="hover:text-slate-600">Sales</a>
            <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
            <span class="text-slate-600">Invoice details</span>
        </div>
        <div>
            <a href="{{ route('sales.create') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                <i class="fa-solid fa-cart-plus mr-1.5 text-teal-500"></i> New Sale Checkout
            </a>
        </div>
    </div>

    {{-- Grid Layout: Invoice on Left, Actions on Right --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Printable Invoice Sheet (Takes 2 Columns) --}}
        <div class="lg:col-span-2 space-y-6">
            <div id="invoice-sheet" class="bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden p-6 md:p-10 space-y-8 bg-[radial-gradient(#f1f5f9_1px,transparent_1px)] [background-size:16px_16px]">
                
                {{-- Invoice Header Ribbon --}}
                <div class="flex flex-col md:flex-row justify-between gap-6 border-b border-slate-100 pb-8">
                    
                    {{-- Shop Details --}}
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-400 to-emerald-400 text-slate-950 flex items-center justify-center font-bold shadow-md">
                                <i class="fa-solid fa-store text-sm"></i>
                            </div>
                            <span class="text-lg font-black text-slate-900 tracking-tight">{{ $shopSettings?->shop_name ?? 'Kharcha Pani Shop' }}</span>
                        </div>
                        <div class="text-xs text-slate-500 space-y-1">
                            <p class="font-medium"><i class="fa-solid fa-location-dot mr-1.5 w-3 text-center text-slate-400"></i>{{ $shopSettings?->shop_address ?? 'Shop Location Details' }}</p>
                            <p class="font-medium"><i class="fa-solid fa-phone mr-1.5 w-3 text-center text-slate-400"></i>{{ $shopSettings?->shop_phone ?? 'Contact Number' }}</p>
                            @if($shopSettings?->gst_number)
                                <p class="font-bold text-slate-700 uppercase tracking-wide mt-1.5">GSTIN: {{ $shopSettings->gst_number }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Invoice Meta --}}
                    <div class="text-left md:text-right space-y-2">
                        <span class="inline-flex px-2.5 py-0.5 rounded text-2xs font-extrabold uppercase bg-teal-50 text-teal-700 border border-teal-100">Retail Tax Invoice</span>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $sale->invoice_number }}</h2>
                        <div class="text-xs text-slate-500 space-y-1">
                            <p class="font-medium"><span class="text-slate-400">Date:</span> {{ \Carbon\Carbon::parse($sale->date)->format('d F Y') }}</p>
                            <p class="font-medium"><span class="text-slate-400">Checkout Mode:</span> <span class="uppercase font-bold text-slate-750">{{ str_replace('_', ' ', $sale->payment_method) }}</span></p>
                            <p class="font-medium"><span class="text-slate-400">Cashier:</span> {{ $sale->user->name ?? 'Store Admin' }}</p>
                        </div>
                    </div>

                </div>

                {{-- Client Info Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <div>
                        <span class="block text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-1.5">Billed To Customer</span>
                        <span class="block text-sm font-bold text-slate-800">{{ $sale->customer_name ?? 'Walk-in Customer' }}</span>
                        @if($sale->customer)
                            <div class="text-xs text-slate-500 mt-1 space-y-0.5 font-medium">
                                @if($sale->customer->phone)
                                    <p><i class="fa-solid fa-phone mr-1.5 text-3xs text-slate-400"></i>{{ $sale->customer->phone }}</p>
                                @endif
                                @if($sale->customer->email)
                                    <p><i class="fa-regular fa-envelope mr-1.5 text-3xs text-slate-400"></i>{{ $sale->customer->email }}</p>
                                @endif
                                @if($sale->customer->address)
                                    <p><i class="fa-solid fa-location-dot mr-1.5 text-3xs text-slate-400"></i>{{ $sale->customer->address }}</p>
                                @endif
                            </div>
                        @else
                            <span class="block text-xs text-slate-400 mt-0.5 italic">Anonymous Checkout</span>
                        @endif
                    </div>
                    
                    <div class="md:text-right flex flex-col justify-between">
                        <div>
                            <span class="block text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-1">Status of payment</span>
                            @if($sale->payment_method === 'credit')
                                <span class="inline-flex px-2 py-0.5 rounded text-2xs font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-100">
                                    Udhaar Pending
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-2xs font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    Fully Settled
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Invoice Line Items Table --}}
                <div class="border border-slate-100 rounded-2xl overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-3xs font-extrabold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-3 text-center w-12">#</th>
                                <th class="px-6 py-3">Item Name</th>
                                <th class="px-6 py-3 text-right w-24">Price</th>
                                <th class="px-6 py-3 text-center w-24">Quantity</th>
                                <th class="px-6 py-3 text-right w-32">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-xs">
                            @foreach($sale->items as $index => $item)
                                <tr class="text-slate-700 hover:bg-slate-50/10">
                                    <td class="px-6 py-3.5 text-center font-bold text-slate-450">{{ $index + 1 }}</td>
                                    <td class="px-6 py-3.5">
                                        <span class="font-bold text-slate-800">{{ $item->product_name }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-medium text-slate-500">₹{{ number_format($item->selling_price, 2) }}</td>
                                    <td class="px-6 py-3.5 text-center font-black text-slate-800">{{ $item->quantity }}</td>
                                    <td class="px-6 py-3.5 text-right font-bold text-slate-900">₹{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Bottom Pricing Totals Breakdown --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    
                    {{-- Terms & Notes --}}
                    <div class="text-xs text-slate-450 space-y-2 max-w-xs leading-normal">
                        <span class="block font-bold text-slate-500 uppercase tracking-wide">Terms & Conditions</span>
                        <p>Goods once sold are non-refundable. For support or ledger queries, please reference invoice number {{ $sale->invoice_number }}. Thanks for shopping with us!</p>
                    </div>

                    {{-- Cost Calculations --}}
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 space-y-3.5 text-xs">
                        
                        <div class="flex items-center justify-between text-slate-550 font-semibold">
                            <span>Taxable Subtotal</span>
                            <span>₹{{ number_format($sale->total_amount - $sale->tax_amount, 2) }}</span>
                        </div>
                        
                        @if($sale->tax_amount > 0)
                            <div class="flex items-center justify-between text-slate-550 font-semibold">
                                <span>GST Sales Tax Sourced</span>
                                <span>₹{{ number_format($sale->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between text-slate-900 font-black text-sm border-t border-slate-200/60 pt-3">
                            <span>Grand Total</span>
                            <span class="text-teal-600 font-extrabold text-base">₹{{ number_format($sale->total_amount, 2) }}</span>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        {{-- Invoice Control Panel (Takes 1 Column) --}}
        <div class="space-y-6">
            
            {{-- Primary Action Card --}}
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md space-y-4">
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight border-b border-slate-100 pb-3">Actions Control</h3>
                
                <div class="flex flex-col gap-2.5">
                    <a href="{{ route('invoices.pdf', $sale->id) }}" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 transition-colors shadow-sm">
                        <i class="fa-regular fa-file-pdf text-sm"></i> Download Official PDF
                    </a>
                    
                    <a href="{{ route('invoices.print', $sale->id) }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 transition-all shadow-md shadow-emerald-500/10">
                        <i class="fa-solid fa-print text-sm"></i> Printable Thermal Sheet
                    </a>
                </div>
            </div>

            {{-- Sharing dispatcher widget --}}
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md space-y-4">
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight border-b border-slate-100 pb-3">Dispatch Receipt</h3>
                
                <form method="POST" action="{{ route('invoices.share', $sale->id) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-3xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Select Share Method</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="border border-slate-200 rounded-xl p-3 flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors relative">
                                <input type="radio" name="channel" value="whatsapp" checked class="sr-only peer">
                                <div class="peer-checked:border-teal-500 absolute inset-0 border-2 border-transparent rounded-xl pointer-events-none"></div>
                                <i class="fa-brands fa-whatsapp text-emerald-500 text-lg"></i>
                                <span class="text-xs font-bold text-slate-700">WhatsApp</span>
                            </label>
                            
                            <label class="border border-slate-200 rounded-xl p-3 flex items-center justify-center gap-2 cursor-pointer hover:bg-slate-50 transition-colors relative">
                                <input type="radio" name="channel" value="email" class="sr-only peer">
                                <div class="peer-checked:border-teal-500 absolute inset-0 border-2 border-transparent rounded-xl pointer-events-none"></div>
                                <i class="fa-regular fa-envelope text-indigo-500 text-lg"></i>
                                <span class="text-xs font-bold text-slate-700">Email Queue</span>
                            </label>
                        </div>
                    </div>

                    @if($sale->customer && $sale->customer->phone)
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-1">
                            <span class="block text-3xs font-bold text-slate-400 uppercase tracking-wide">Target Contact</span>
                            <span class="block text-xs font-semibold text-slate-700"><i class="fa-solid fa-phone mr-1.5 text-slate-400"></i>{{ $sale->customer->phone }}</span>
                        </div>
                    @else
                        <div class="bg-amber-50/50 rounded-2xl p-4 border border-amber-100/50 text-2xs text-amber-800 leading-relaxed font-medium">
                            <i class="fa-solid fa-circle-info mr-1"></i> Checked out as <strong>Walk-in</strong>. Standard text copy link will be compiled.
                        </div>
                    @endif

                    <button type="submit" class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <i class="fa-solid fa-paper-plane text-3xs"></i> Dispatch To Customer
                    </button>
                </form>
            </div>

            {{-- Checkout metadata --}}
            <div class="bg-slate-100/50 rounded-3xl p-6 border border-slate-200/50 space-y-3.5 text-xs text-slate-600">
                <h4 class="font-extrabold text-slate-850 uppercase tracking-wider text-2xs">Invoice Audit Records</h4>
                
                <div class="flex justify-between font-medium">
                    <span>Tax Sourced:</span>
                    <span class="font-bold text-slate-800">₹{{ number_format($sale->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-medium">
                    <span>Items Checked:</span>
                    <span class="font-bold text-slate-800">{{ $sale->items->sum('quantity') }} items</span>
                </div>
                <div class="flex justify-between font-medium">
                    <span>Invoice Ref:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $sale->id }}</span>
                </div>
                <div class="flex justify-between font-medium">
                    <span>Log Time:</span>
                    <span class="font-bold text-slate-800">{{ $sale->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
