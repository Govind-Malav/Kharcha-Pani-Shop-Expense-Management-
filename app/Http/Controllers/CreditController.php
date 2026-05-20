<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Credit;
use App\Models\CreditPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Credit::class);

        $query = Credit::with(['customer', 'supplier']);

        // Type Filter (Customer Udhaar vs Supplier Dues)
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Status Filter (pending vs paid)
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('person_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $credits = $query->orderBy('due_date', 'asc')->latest()->paginate(10)->withQueryString();
        
        $customers = Customer::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('credits.index', compact('credits', 'customers', 'suppliers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Credit::class);

        $request->validate([
            'type' => 'required|in:customer,supplier',
            'person_source' => 'required|in:existing,manual',
            'customer_id' => 'required_if:person_source,existing|exists:customers,id|nullable',
            'supplier_id' => 'required_if:person_source,existing|exists:suppliers,id|nullable',
            'person_name' => 'required_if:person_source,manual|string|max:150|nullable',
            'phone' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'paid_amount' => 'required|numeric|min:0|lte:amount',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $data = $request->only(['type', 'amount', 'paid_amount', 'due_date', 'notes']);
            
            if ($request->type === 'customer') {
                if ($request->person_source === 'existing') {
                    $customer = Customer::findOrFail($request->customer_id);
                    $data['customer_id'] = $customer->id;
                    $data['person_name'] = $customer->name;
                    $data['phone'] = $customer->phone;
                } else {
                    $data['person_name'] = $request->person_name;
                    $data['phone'] = $request->phone;
                }
            } else { // supplier
                if ($request->person_source === 'existing') {
                    $supplier = Supplier::findOrFail($request->supplier_id);
                    $data['supplier_id'] = $supplier->id;
                    $data['person_name'] = $supplier->name;
                    $data['phone'] = $supplier->phone;
                } else {
                    $data['person_name'] = $request->person_name;
                    $data['phone'] = $request->phone;
                }
            }

            $data['status'] = $data['paid_amount'] >= $data['amount'] ? 'paid' : 'pending';

            $credit = Credit::create($data);

            // Record initial installment payment if any
            if ($data['paid_amount'] > 0) {
                CreditPayment::create([
                    'credit_id' => $credit->id,
                    'amount_paid' => $data['paid_amount'],
                    'payment_date' => date('Y-m-d'),
                    'payment_method' => 'cash',
                    'notes' => 'Upfront credit payment recorded.'
                ]);
            }
        });

        return redirect()->route('credits.index')->with('success', 'Credit account opened successfully!');
    }

    public function show(Credit $credit)
    {
        Gate::authorize('view', $credit);
        $credit->load('payments');
        return view('credits.show', compact('credit'));
    }

    public function storePayment(Request $request, Credit $credit)
    {
        Gate::authorize('update', $credit);

        $remaining = $credit->amount - $credit->paid_amount;

        $request->validate([
            'amount_paid' => 'required|numeric|min:0.01|max:' . $remaining,
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request, $credit) {
            CreditPayment::create([
                'credit_id' => $credit->id,
                'amount_paid' => $request->amount_paid,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            $newPaidAmount = $credit->paid_amount + $request->amount_paid;
            $newStatus = $newPaidAmount >= $credit->amount ? 'paid' : 'pending';

            $credit->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newStatus,
            ]);
        });

        return redirect()->back()->with('success', 'Installment payment recorded successfully!');
    }

    public function sendReminder(Credit $credit)
    {
        Gate::authorize('view', $credit);

        // Simulated notification reminder. In real systems, this sends SMS, WhatsApp or Email.
        // We will create a database log notification for this!
        $message = "Payment reminder generated for {$credit->person_name}. Balance due: " . ($credit->amount - $credit->paid_amount) . " INR. Due by " . $credit->due_date->format('d M, Y') . ".";
        
        // Push notification in database for display in bell menu
        auth()->user()->notify(new \App\Notifications\GenericAlertNotification([
            'title' => 'Reminder Dispatched',
            'message' => $message,
            'url' => route('credits.show', $credit->id),
            'type' => 'credit_reminder'
        ]));

        return redirect()->back()->with('success', "SMS & WhatsApp reminder template generated and marked in system alerts!");
    }

    public function destroy(Credit $credit)
    {
        Gate::authorize('delete', $credit);
        $credit->delete();
        return redirect()->route('credits.index')->with('success', 'Credit account deleted.');
    }
}
