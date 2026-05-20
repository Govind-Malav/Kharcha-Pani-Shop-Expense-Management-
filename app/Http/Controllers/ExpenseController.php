<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Expense::class);

        $query = Expense::with(['category', 'user']);

        // Staff can only view their own expenses (optional refinement, or all depending on specs. Standard view for staff shows all but limited dashboard. Let's make staff see all since it's a team shop but only owner can manage/delete).
        if (auth()->user()->isStaff()) {
            // Option to restrict if needed. Let's allow staff to see all expenses to align with team tracking, but restrict edit/delete.
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Date Filters
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->get('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->get('end_date'));
        }

        // Sorting & Pagination
        $expenses = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();
        
        $categories = ExpenseCategory::all();

        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        Gate::authorize('create', Expense::class);
        $categories = ExpenseCategory::all();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Expense::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'payment_type' => 'required|in:cash,card,upi,net_banking,other',
            'description' => 'nullable|string',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('receipt_image');
        $data['user_id'] = auth()->id();

        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts', $filename, 'public');
            $data['receipt_image'] = $path;
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function edit(Expense $expense)
    {
        Gate::authorize('update', $expense);
        $categories = ExpenseCategory::all();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        Gate::authorize('update', $expense);

        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:expense_categories,id',
            'date' => 'required|date',
            'payment_type' => 'required|in:cash,card,upi,net_banking,other',
            'description' => 'nullable|string',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('receipt_image');

        if ($request->hasFile('receipt_image')) {
            // Delete old image if exists
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }

            $file = $request->file('receipt_image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts', $filename, 'public');
            $data['receipt_image'] = $path;
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully!');
    }

    public function destroy(Expense $expense)
    {
        Gate::authorize('delete', $expense);

        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully!');
    }

    // Custom categories creation
    public function storeCategory(Request $request)
    {
        // Only owner can manage categories
        if (!auth()->user()->isOwner()) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name',
            'description' => 'nullable|string',
        ]);

        $category = ExpenseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_custom' => true,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category created successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Category created successfully!');
    }
}
