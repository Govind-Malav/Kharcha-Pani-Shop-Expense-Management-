<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Product::class);

        $query = Product::with('supplier');

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Low Stock Filter
        if ($request->boolean('low_stock')) {
            // Need to retrieve all and filter dynamically or subquery using shop settings.
            // Let's filter in DB for speed: where quantity <= low_stock_threshold or quantity <= 10 (default)
            $query->where(function($q) {
                $q->whereRaw('quantity <= low_stock_threshold')
                  ->orWhere(function($sub) {
                      $sub->whereNull('low_stock_threshold')
                          ->whereRaw('quantity <= 10'); // Default shop setting fallback
                  });
            });
        }

        $products = $query->orderBy('name')->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('inventory.index', compact('products', 'suppliers'));
    }

    public function create()
    {
        Gate::authorize('create', Product::class);
        $suppliers = Supplier::orderBy('name')->get();
        return view('inventory.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Product::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:cost_price',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'barcode' => 'nullable|string|max:100',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products', $filename, 'public');
            $data['image_path'] = $path;
        }

        Product::create($data);

        return redirect()->route('inventory.index')->with('success', 'Product added to inventory!');
    }

    public function edit(Product $product)
    {
        Gate::authorize('update', $product);
        $suppliers = Supplier::orderBy('name')->get();
        return view('inventory.edit', compact('product', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        Gate::authorize('update', $product);

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:cost_price',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'barcode' => 'nullable|string|max:100',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $file = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products', $filename, 'public');
            $data['image_path'] = $path;
        }

        $product->update($data);

        return redirect()->route('inventory.index')->with('success', 'Product details updated!');
    }

    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);

        // Check if product is tied to sales
        if ($product->saleItems()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete product as it is associated with historical sales records. Try setting quantity to 0 instead.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('inventory.index')->with('success', 'Product deleted from database.');
    }
}
