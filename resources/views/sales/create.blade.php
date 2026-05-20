@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="posApp()" x-init="init()" x-on:voice-add-item.window="addToCartFromVoice($event.detail.query)">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Point of Sale (POS)</h1>
            <p class="text-sm text-slate-500 mt-1">Create sales orders, issue invoices, and track payments</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-list mr-2 text-slate-400"></i> View Sales Log
            </a>
        </div>
    </div>

    {{-- Error messages block --}}
    @if($errors->any() || session('error'))
        <div class="bg-rose-50 text-rose-800 p-4 rounded-2xl border border-rose-100 text-xs font-semibold space-y-1">
            <div class="flex items-center text-sm font-bold text-rose-900 mb-1">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> Error Encountered
            </div>
            @if(session('error'))
                <p>{{ session('error') }}</p>
            @endif
            @if($errors->any())
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    {{-- Voice command pre-fill indicator banner --}}
    @if(request('ref') === 'voice' && request('amount'))
        <div class="bg-teal-50/80 border border-teal-100 text-teal-850 p-4 rounded-2xl text-xs font-semibold flex items-start max-w-4xl">
            <i class="fa-solid fa-microphone-lines text-teal-600 text-lg mr-3 mt-0.5 animate-pulse"></i>
            <div>
                <span class="font-extrabold text-teal-900 block text-sm">Voice Command Detected!</span>
                <p class="text-slate-500 mt-1">You requested a sale of <span class="font-bold text-teal-700">₹{{ request('amount') }}</span>. Please select products from the catalog on the left to add them to the cart and complete this sale.</p>
            </div>
        </div>
    @endif

    {{-- Main POS Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        
        {{-- Left: Products Catalogue --}}
        <div class="xl:col-span-7 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Products Sourced</h3>
                <span class="text-2xs text-slate-400 font-semibold" x-text="filteredProducts().length + ' products available'"></span>
            </div>

            {{-- Search & Category Filter inside POS --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" x-model="searchQuery" placeholder="Search product by name, SKU or scan barcode..." 
                       class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 transition-colors placeholder:text-slate-400">
            </div>

            {{-- Catalogue List Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[460px] overflow-y-auto pr-1">
                <template x-for="product in filteredProducts()" :key="product.id">
                    <div class="p-3 bg-slate-50 border border-slate-150 hover:border-teal-400 rounded-xl transition-all flex items-center justify-between gap-3 cursor-pointer group"
                         @click="addToCart(product)">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center flex-shrink-0 group-hover:bg-teal-500 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-box text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-xs font-bold text-slate-800 truncate" x-text="product.name"></span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-3xs text-slate-400" x-text="'Stock: ' + product.quantity"></span>
                                    <span class="inline-flex px-1 rounded text-3xs font-extrabold bg-slate-200 text-slate-655" x-text="product.sku"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="block text-sm font-black text-slate-900" x-text="'₹' + parseFloat(product.selling_price).toFixed(2)"></span>
                            <span class="inline-flex items-center text-3xs font-bold text-teal-600 group-hover:translate-x-0.5 transition-transform">
                                Add <i class="fa-solid fa-chevron-right ml-0.5"></i>
                            </span>
                        </div>
                    </div>
                </template>
                <div x-show="filteredProducts().length === 0" class="col-span-2 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs font-medium">No matching items in active stock</p>
                </div>
            </div>
        </div>

        {{-- Right: Checkout Form / Cart --}}
        <div class="xl:col-span-5 space-y-6">
            
            <form method="POST" action="{{ route('sales.store') }}" class="space-y-6">
                @csrf

                {{-- Hidden Form Fields For Cart Items parsed by Laravel --}}
                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div>
                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                        <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                    </div>
                </template>

                {{-- Cart Card --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                        <h3 class="font-extrabold text-sm tracking-wide uppercase">Shopping Cart</h3>
                        <span class="text-xs font-bold bg-teal-400 text-slate-950 px-2 py-0.5 rounded-full" x-text="cartTotalQty() + ' items'"></span>
                    </div>

                    {{-- Cart Items List --}}
                    <div class="divide-y divide-slate-100 max-h-[220px] overflow-y-auto">
                        <template x-for="item in cart" :key="item.product_id">
                            <div class="p-4 flex items-center justify-between gap-3 text-sm">
                                <div class="min-w-0 flex-1">
                                    <span class="block font-bold text-slate-800 truncate" x-text="item.name"></span>
                                    <span class="block text-3xs text-slate-400 mt-0.5" x-text="'₹' + item.price + ' each'"></span>
                                </div>
                                
                                {{-- Quantity adjust --}}
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button type="button" @click="decreaseQty(item)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-extrabold text-xs">-</button>
                                    <span class="w-8 text-center font-extrabold text-slate-800 text-xs" x-text="item.quantity"></span>
                                    <button type="button" @click="increaseQty(item)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-extrabold text-xs">+</button>
                                </div>

                                {{-- Price & Delete --}}
                                <div class="text-right flex-shrink-0 flex items-center gap-3">
                                    <span class="font-bold text-slate-900 text-sm w-20" x-text="'₹' + (item.price * item.quantity).toFixed(2)"></span>
                                    <button type="button" @click="removeFromCart(item)" class="text-slate-350 hover:text-rose-500 transition-colors">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="cart.length === 0" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-basket-shopping text-3xl mb-2 text-slate-200 animate-pulse"></i>
                            <p class="text-xs font-semibold text-slate-700">POS Cart is Empty</p>
                            <p class="text-2xs text-slate-400 mt-0.5">Click items on the left catalogue to add them</p>
                        </div>
                    </div>

                    {{-- Billing Breakdown --}}
                    <div class="bg-slate-50 p-6 border-t border-slate-100 space-y-3 text-sm">
                        <div class="flex items-center justify-between text-slate-500 font-semibold">
                            <span>Subtotal</span>
                            <span x-text="'₹' + cartSubtotal().toFixed(2)"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500 font-semibold gap-4">
                            <div class="flex items-center gap-1.5">
                                <span>GST Tax Rate</span>
                                <input type="number" name="tax_rate" x-model.number="taxRate" min="0" max="100" class="w-14 px-1.5 py-0.5 text-center text-xs rounded border-slate-250 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                                <span>%</span>
                            </div>
                            <span x-text="'₹' + cartTaxAmount().toFixed(2)"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-900 font-black text-base border-t border-slate-200/60 pt-3">
                            <span>Total Payable</span>
                            <span x-text="'₹' + cartTotal().toFixed(2)" class="text-teal-600 font-extrabold text-lg"></span>
                        </div>
                    </div>
                </div>

                {{-- Customer & Payment Info Card --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2">Customer & Checkout Info</h4>
                    
                    <div class="grid grid-cols-1 gap-4">

                        {{-- Date of order --}}
                        <div>
                            <label for="date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Order Date</label>
                            <input type="date" name="date" id="date" value="{{ now()->format('Y-m-d') }}" required
                                   class="w-full px-4 py-2.5 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                        </div>

                        {{-- Customer Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Customer Type</label>
                            <div class="grid grid-cols-3 gap-2 bg-slate-50 p-1 rounded-xl">
                                <label class="flex items-center justify-center py-2 px-1 text-2xs font-extrabold rounded-lg cursor-pointer text-center uppercase tracking-wider transition-colors border"
                                       :class="custType === 'walkin' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600'">
                                    <input type="radio" name="customer_type" value="walkin" x-model="custType" class="hidden"> Walk-in
                                </label>
                                <label class="flex items-center justify-center py-2 px-1 text-2xs font-extrabold rounded-lg cursor-pointer text-center uppercase tracking-wider transition-colors border"
                                       :class="custType === 'existing' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600'">
                                    <input type="radio" name="customer_type" value="existing" x-model="custType" class="hidden"> Existing
                                </label>
                                <label class="flex items-center justify-center py-2 px-1 text-2xs font-extrabold rounded-lg cursor-pointer text-center uppercase tracking-wider transition-colors border"
                                       :class="custType === 'new' ? 'bg-white border-slate-200 text-slate-900 shadow-sm' : 'border-transparent text-slate-400 hover:text-slate-600'">
                                    <input type="radio" name="customer_type" value="new" x-model="custType" class="hidden"> Create New
                                </label>
                            </div>
                        </div>

                        {{-- Walk-in Name --}}
                        <div x-show="custType === 'walkin'">
                            <label for="walkin_customer_name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Customer Name (Optional)</label>
                            <input type="text" name="walkin_customer_name" id="walkin_customer_name" placeholder="Walk-in Client" 
                                   class="w-full px-4 py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 text-slate-700">
                        </div>

                        {{-- Existing Customer --}}
                        <div x-show="custType === 'existing'">
                            <label for="customer_id" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Select Registered Customer</label>
                            <select name="customer_id" id="customer_id" :required="custType === 'existing'"
                                    class="w-full py-2 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                                <option value="" disabled selected>Choose Registered Customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'No Phone' }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Create New Customer --}}
                        <div x-show="custType === 'new'" class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div>
                                <label for="new_customer_name" class="block text-xs font-bold text-slate-500 uppercase mb-1">New Customer Name *</label>
                                <input type="text" name="new_customer_name" id="new_customer_name" :required="custType === 'new'" placeholder="Full Name" 
                                       class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 text-slate-700">
                            </div>
                            <div>
                                <label for="new_customer_phone" class="block text-xs font-bold text-slate-500 uppercase mb-1">Mobile Phone</label>
                                <input type="text" name="new_customer_phone" id="new_customer_phone" placeholder="Mobile phone number" 
                                       class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50 text-slate-700">
                            </div>
                        </div>

                        {{-- Payment Method Selection --}}
                        <div>
                            <label for="payment_method" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Payment Method</label>
                            <select name="payment_method" id="payment_method" x-model="payMethod" required 
                                    class="w-full py-2.5 text-xs rounded-xl border-slate-200 focus:border-teal-500 focus:ring focus:ring-teal-200/50">
                                <option value="cash">Cash Checkout</option>
                                <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                                <option value="card">Card Payment</option>
                                <option value="net_banking">Net Banking</option>
                                <option value="credit">Udhaar / Store Credit (Pending Balance)</option>
                            </select>
                        </div>

                        {{-- Credit Pending Details --}}
                        <div x-show="payMethod === 'credit'" class="space-y-3 bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                            <span class="block text-3xs font-extrabold text-indigo-500 uppercase tracking-wider"><i class="fa-solid fa-scale-balanced mr-1"></i> Store Udhaar Outstanding</span>
                            <div>
                                <label for="credit_paid_amount" class="block text-xs font-bold text-indigo-700 mb-1">Upfront Paid Amount *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-indigo-400 font-semibold text-xs">
                                        ₹
                                    </div>
                                    <input type="number" name="credit_paid_amount" id="credit_paid_amount" :required="payMethod === 'credit'" step="0.01" min="0" placeholder="0.00" value="0.00"
                                           class="w-full pl-7 pr-3 py-1.5 text-xs rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 text-slate-700 bg-white">
                                </div>
                            </div>
                            <div>
                                <label for="credit_due_date" class="block text-xs font-bold text-indigo-700 mb-1">Expected Repay Due Date</label>
                                <input type="date" name="credit_due_date" id="credit_due_date"
                                       class="w-full px-3 py-1.5 text-xs rounded-lg border-indigo-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 text-slate-700 bg-white">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3">
                    <button type="button" @click="clearCart()" class="w-1/3 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition-colors uppercase tracking-wider">
                        Clear Order
                    </button>
                    <button type="submit" :disabled="cart.length === 0" 
                            class="w-2/3 py-3 rounded-xl text-xs font-black text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 shadow-lg shadow-emerald-500/10 disabled:opacity-50 disabled:pointer-events-none text-center transition-colors uppercase tracking-wider">
                        Confirm & Issue Invoice
                    </button>
                </div>

            </form>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function posApp() {
        return {
            products: @json($products),
            searchQuery: '',
            cart: [],
            custType: 'walkin',
            payMethod: 'cash',
            taxRate: 0,

            init() {
                console.log('POS initialized with ' + this.products.length + ' products.');
            },

            addToCartFromVoice(query) {
                if (!query) return;
                const search = query.toLowerCase().trim();
                const matchedProduct = this.products.find(p => {
                    return p.name.toLowerCase().includes(search) || 
                           p.sku.toLowerCase().includes(search);
                });

                if (matchedProduct) {
                    this.addToCart(matchedProduct);
                } else {
                    alert(`Product "${query}" not found in active inventory.`);
                }
            },

            filteredProducts() {
                if(!this.searchQuery) return this.products;
                const search = this.searchQuery.toLowerCase();
                return this.products.filter(p => {
                    return p.name.toLowerCase().includes(search) || 
                           p.sku.toLowerCase().includes(search) || 
                           (p.barcode && p.barcode.toLowerCase().includes(search));
                });
            },

            addToCart(product) {
                // Find existing in cart
                const existing = this.cart.find(item => item.product_id === product.id);
                if(existing) {
                    if(existing.quantity < product.quantity) {
                        existing.quantity++;
                    } else {
                        alert('Insufficient stock available for this item!');
                    }
                } else {
                    this.cart.push({
                        product_id: product.id,
                        name: product.name,
                        price: parseFloat(product.selling_price),
                        quantity: 1,
                        maxQuantity: product.quantity
                    });
                }
            },

            removeFromCart(item) {
                this.cart = this.cart.filter(c => c.product_id !== item.product_id);
            },

            increaseQty(item) {
                if(item.quantity < item.maxQuantity) {
                    item.quantity++;
                } else {
                    alert('Cannot exceed available stock limit ('+item.maxQuantity+')');
                }
            },

            decreaseQty(item) {
                if(item.quantity > 1) {
                    item.quantity--;
                } else {
                    this.removeFromCart(item);
                }
            },

            cartTotalQty() {
                return this.cart.reduce((sum, item) => sum + item.quantity, 0);
            },

            cartSubtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },

            cartTaxAmount() {
                return (this.cartSubtotal() * this.taxRate) / 100;
            },

            cartTotal() {
                return this.cartSubtotal() + this.cartTaxAmount();
            },

            clearCart() {
                if(confirm('Are you sure you want to discard this shopping cart?')) {
                    this.cart = [];
                }
            }
        }
    }
</script>
@endpush
@endsection
