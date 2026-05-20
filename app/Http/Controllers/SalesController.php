<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Credit;
use App\Models\CreditPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Sale::class);

        $query = Sale::with(['customer', 'user']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('total_amount', 'like', "%{$search}%");
            });
        }

        // Payment Method Filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        // Date Filters
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->get('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->get('end_date'));
        }

        $sales = $query->orderBy('date', 'desc')->latest()->paginate(10)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        Gate::authorize('create', Sale::class);

        $customers = Customer::orderBy('name')->get();
        // Load active inventory products with quantity > 0
        $products = Product::where('quantity', '>', 0)->orderBy('name')->get();

        return view('sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Sale::class);

        $request->validate([
            'customer_type' => 'required|in:existing,new,walkin',
            'customer_id' => 'required_if:customer_type,existing|exists:customers,id|nullable',
            'new_customer_name' => 'required_if:customer_type,new|string|max:150|nullable',
            'new_customer_phone' => 'nullable|string|max:20',
            'walkin_customer_name' => 'nullable|string|max:150',
            'payment_method' => 'required|in:cash,card,upi,net_banking,credit',
            'date' => 'required|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // Credit variables if payment is credit
            'credit_paid_amount' => 'required_if:payment_method,credit|numeric|min:0|nullable',
            'credit_due_date' => 'nullable|date',
        ]);

        return DB::transaction(function() use ($request) {
            // 1. Identify or Create Customer
            $customerId = null;
            $customerName = 'Walk-in Customer';
            $customerPhone = null;

            if ($request->customer_type === 'existing') {
                $customer = Customer::findOrFail($request->customer_id);
                $customerId = $customer->id;
                $customerName = $customer->name;
                $customerPhone = $customer->phone;
            } elseif ($request->customer_type === 'new') {
                $customer = Customer::create([
                    'name' => $request->new_customer_name,
                    'phone' => $request->new_customer_phone,
                ]);
                $customerId = $customer->id;
                $customerName = $customer->name;
                $customerPhone = $customer->phone;
            } elseif ($request->customer_type === 'walkin' && $request->filled('walkin_customer_name')) {
                $customerName = $request->walkin_customer_name;
            }

            // 2. Process Cart Items and calculate totals
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Double check stock quantity
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->quantity}");
                }

                $itemTotal = $product->selling_price * $item['quantity'];
                $subtotal += $itemTotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'total_price' => $itemTotal,
                    'product_model' => $product // keep reference to update stock
                ];
            }

            // Calculate tax
            $taxRate = $request->input('tax_rate', 0);
            $taxAmount = ($subtotal * $taxRate) / 100;
            $totalAmount = $subtotal + $taxAmount;

            // 3. Generate Invoice Number
            $latestSale = Sale::orderBy('id', 'desc')->first();
            $nextId = $latestSale ? $latestSale->id + 1 : 1;
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // 4. Create Sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'payment_method' => $request->payment_method,
                'date' => $request->date,
                'user_id' => auth()->id(),
            ]);

            // 5. Create Items & Update Stock
            foreach ($itemsData as $item) {
                // Deduct Product Inventory
                $product = $item['product_model'];
                $product->decrement('quantity', $item['quantity']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // 6. Handle Credit (Udhaar)
            if ($request->payment_method === 'credit') {
                $paidAmt = $request->input('credit_paid_amount', 0);
                $isPaid = $paidAmt >= $totalAmount ? 'paid' : 'pending';

                $credit = Credit::create([
                    'type' => 'customer',
                    'customer_id' => $customerId,
                    'person_name' => $customerName,
                    'phone' => $customerPhone,
                    'amount' => $totalAmount,
                    'paid_amount' => $paidAmt,
                    'due_date' => $request->credit_due_date,
                    'status' => $isPaid,
                    'notes' => "Recorded from Invoice: {$invoiceNumber}."
                ]);

                // Create initial payment record if customer paid something upfront
                if ($paidAmt > 0) {
                    CreditPayment::create([
                        'credit_id' => $credit->id,
                        'amount_paid' => $paidAmt,
                        'payment_date' => $request->date,
                        'payment_method' => 'cash',
                        'notes' => 'Upfront payment during invoice checkout.'
                    ]);
                }
            }

            return redirect()->route('sales.show', $sale->id)->with('success', "Invoice {$invoiceNumber} created successfully!");
        });
    }

    public function show(Sale $sale)
    {
        Gate::authorize('view', $sale);
        $sale->load(['items', 'customer', 'user']);
        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        Gate::authorize('delete', $sale);

        return DB::transaction(function() use ($sale) {
            $sale->load('items');

            // 1. Restore stock quantity for all products
            foreach ($sale->items as $item) {
                // If product is deleted, we can skip or restore if constraint allows
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('quantity', $item->quantity);
                }
            }

            // 2. Delete related credit accounts (if any)
            $invoiceNote = "Invoice: {$sale->invoice_number}";
            Credit::where('notes', 'like', "%{$invoiceNote}%")
                ->orWhere(function($query) use ($sale) {
                    if ($sale->customer_id) {
                        $query->where('customer_id', $sale->customer_id)
                              ->where('amount', $sale->total_amount)
                              ->whereDate('created_at', $sale->created_at->format('Y-m-d'));
                    }
                })->delete();

            // 3. Delete sale
            $sale->delete();

            return redirect()->route('sales.index')->with('success', 'Sale transaction canceled and inventory restored.');
        });
    }
}
