<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ShopSetting;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Credit;
use App\Models\CreditPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        $owner = User::create([
            'name' => 'Govind Malav (Owner)',
            'email' => 'owner@shop.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $staff = User::create([
            'name' => 'Rahul Verma (Staff)',
            'email' => 'staff@shop.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 'active',
        ]);

        $accountant = User::create([
            'name' => 'Sanjay Mehta (Accountant)',
            'email' => 'accountant@shop.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'status' => 'active',
        ]);

        // 2. Seed Shop Settings
        ShopSetting::create([
            'shop_name' => 'Kharcha Pani Kirana',
            'shop_email' => 'contact@kharchapani.com',
            'shop_phone' => '+91 98765 43210',
            'shop_address' => '12, Sector 5, Hiranandani Market, Powai, Mumbai - 400076',
            'logo_path' => null,
            'gst_number' => '27AAACK1234F1Z5',
            'currency' => 'INR',
            'low_stock_threshold' => 10,
        ]);

        // 3. Seed Default Expense Categories
        $categories = [
            ['name' => 'Rent', 'description' => 'Monthly shop lease payment', 'is_custom' => false],
            ['name' => 'Salary', 'description' => 'Employee and staff monthly wages', 'is_custom' => false],
            ['name' => 'Electricity', 'description' => 'Power utility bills', 'is_custom' => false],
            ['name' => 'Internet', 'description' => 'Wi-Fi & broadband connection', 'is_custom' => false],
            ['name' => 'Transport', 'description' => 'Logistics and product transport costs', 'is_custom' => false],
            ['name' => 'Packaging', 'description' => 'Bags, boxes, and wrappers', 'is_custom' => false],
            ['name' => 'Inventory Purchase', 'description' => 'Buying items from wholesale suppliers', 'is_custom' => false],
            ['name' => 'Marketing', 'description' => 'Advertising and discount flyers', 'is_custom' => false],
            ['name' => 'Repair', 'description' => 'Maintenance of racks, counters, AC, or fridge', 'is_custom' => false],
            ['name' => 'Miscellaneous', 'description' => 'Sundry expenses not categorized elsewhere', 'is_custom' => false],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['name']] = ExpenseCategory::create($cat);
        }

        // 4. Seed Suppliers
        $suppliers = [
            ['name' => 'Mahadev Wholesale Distributors', 'phone' => '9812345670', 'email' => 'orders@mahadev.com', 'address' => 'Gala 4, APMC Market, Vashi, Navi Mumbai'],
            ['name' => 'Amul Dairy Agency', 'phone' => '9822345671', 'email' => 'sales@amuldairy.com', 'address' => 'Mulund West, Mumbai'],
            ['name' => 'Hindustan Unilever Depot', 'phone' => '9832345672', 'email' => 'hul.retail@unilever.com', 'address' => 'Thane Industrial Estate, Thane'],
        ];

        $supplierModels = [];
        foreach ($suppliers as $sup) {
            $supplierModels[] = Supplier::create($sup);
        }

        // 5. Seed Customers
        $customers = [
            ['name' => 'Amit Sharma', 'phone' => '9911223344', 'email' => 'amit.sharma@gmail.com', 'address' => 'Flat 402, Sunshine Heights, Powai'],
            ['name' => 'Priya Patel', 'phone' => '9922334455', 'email' => 'priya.patel@yahoo.com', 'address' => 'B/12, Shivneri Society, Kanjurmarg'],
            ['name' => 'Rajesh Kumar', 'phone' => '9933445566', 'email' => 'rajesh.kumar@rediff.com', 'address' => 'Chawl No. 3, Filterpada, Powai'],
        ];

        $customerModels = [];
        foreach ($customers as $cust) {
            $customerModels[] = Customer::create($cust);
        }

        // 6. Seed Products (Inventory)
        $products = [
            [
                'name' => 'Basmati Rice Premium 5kg',
                'sku' => 'RIC-BAS-5KG',
                'quantity' => 25,
                'cost_price' => 450.00,
                'selling_price' => 580.00,
                'supplier_id' => $supplierModels[0]->id,
                'barcode' => '8901234005112',
                'low_stock_threshold' => 5,
                'description' => 'Long grain double-chabi Basmati rice.'
            ],
            [
                'name' => 'Amul Butter 500g',
                'sku' => 'BUT-AMU-500G',
                'quantity' => 3, // Low stock on purpose for testing stock alerts
                'cost_price' => 210.00,
                'selling_price' => 250.00,
                'supplier_id' => $supplierModels[1]->id,
                'barcode' => '8901262010125',
                'low_stock_threshold' => 8,
                'description' => 'Utterly butterly delicious salted butter.'
            ],
            [
                'name' => 'Fortune Soyabean Oil 1L',
                'sku' => 'OIL-FOR-1L',
                'quantity' => 40,
                'cost_price' => 110.00,
                'selling_price' => 145.00,
                'supplier_id' => $supplierModels[0]->id,
                'barcode' => '8906007281024',
                'low_stock_threshold' => 10,
                'description' => 'Refined healthy soyabean oil.'
            ],
            [
                'name' => 'Surf Excel Easy Wash 1kg',
                'sku' => 'DET-SUR-1KG',
                'quantity' => 15,
                'cost_price' => 130.00,
                'selling_price' => 170.00,
                'supplier_id' => $supplierModels[2]->id,
                'barcode' => '8901030753046',
                'low_stock_threshold' => 5,
                'description' => 'Premium detergent powder.'
            ],
            [
                'name' => 'Tata Salt Iodized 1kg',
                'sku' => 'SAL-TAT-1KG',
                'quantity' => 50,
                'cost_price' => 20.00,
                'selling_price' => 28.00,
                'supplier_id' => $supplierModels[2]->id,
                'barcode' => '8901058002317',
                'low_stock_threshold' => 12,
                'description' => 'Desh ka Namak.'
            ]
        ];

        $productModels = [];
        foreach ($products as $prod) {
            $productModels[] = Product::create($prod);
        }

        // 7. Seed Historical Expenses (Last 30 Days)
        $now = Carbon::now();
        Expense::create([
            'title' => 'Shop Monthly Rent',
            'amount' => 15000.00,
            'category_id' => $categoryModels['Rent']->id,
            'date' => $now->copy()->subDays(15)->format('Y-m-d'),
            'payment_type' => 'net_banking',
            'description' => 'Rent paid to landlord Mr. Joshi for May.',
            'user_id' => $owner->id
        ]);

        Expense::create([
            'title' => 'Salary - Rahul (Staff)',
            'amount' => 8000.00,
            'category_id' => $categoryModels['Salary']->id,
            'date' => $now->copy()->subDays(18)->format('Y-m-d'),
            'payment_type' => 'upi',
            'description' => 'Monthly staff salary helper.',
            'user_id' => $owner->id
        ]);

        Expense::create([
            'title' => 'Electricity Bill April',
            'amount' => 3450.00,
            'category_id' => $categoryModels['Electricity']->id,
            'date' => $now->copy()->subDays(5)->format('Y-m-d'),
            'payment_type' => 'upi',
            'description' => 'MSEDCL electricity power bill.',
            'user_id' => $owner->id
        ]);

        Expense::create([
            'title' => 'Paper bags and plastic packaging',
            'amount' => 600.00,
            'category_id' => $categoryModels['Packaging']->id,
            'date' => $now->copy()->subDays(2)->format('Y-m-d'),
            'payment_type' => 'cash',
            'description' => 'Bulk packaging from local wholesaler.',
            'user_id' => $staff->id
        ]);

        // 8. Seed Sales & SaleItems (Last 10 Days)
        $sale1 = Sale::create([
            'invoice_number' => 'INV-2026-0001',
            'customer_id' => $customerModels[0]->id,
            'customer_name' => $customerModels[0]->name,
            'total_amount' => 1410.00,
            'tax_amount' => 50.00,
            'payment_method' => 'cash',
            'date' => $now->copy()->subDays(3)->format('Y-m-d'),
            'user_id' => $staff->id
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $productModels[0]->id, // Basmati Rice
            'product_name' => $productModels[0]->name,
            'quantity' => 2,
            'cost_price' => $productModels[0]->cost_price,
            'selling_price' => $productModels[0]->selling_price,
            'total_price' => 1160.00
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $productModels[1]->id, // Butter
            'product_name' => $productModels[1]->name,
            'quantity' => 1,
            'cost_price' => $productModels[1]->cost_price,
            'selling_price' => $productModels[1]->selling_price,
            'total_price' => 250.00
        ]);

        // Sale 2: Credit Sale (Udhaar)
        $sale2 = Sale::create([
            'invoice_number' => 'INV-2026-0002',
            'customer_id' => $customerModels[1]->id,
            'customer_name' => $customerModels[1]->name,
            'total_amount' => 750.00,
            'tax_amount' => 0.00,
            'payment_method' => 'credit',
            'date' => $now->copy()->subDays(1)->format('Y-m-d'),
            'user_id' => $staff->id
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $productModels[1]->id, // Butter
            'product_name' => $productModels[1]->name,
            'quantity' => 2,
            'cost_price' => $productModels[1]->cost_price,
            'selling_price' => $productModels[1]->selling_price,
            'total_price' => 500.00
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $productModels[1]->id, // Butter (pretend we sold Butter and Salt)
            'product_name' => $productModels[4]->name, // Tata Salt
            'product_id' => $productModels[4]->id,
            'quantity' => 5,
            'cost_price' => $productModels[4]->cost_price,
            'selling_price' => $productModels[4]->selling_price,
            'total_price' => 140.00
        ]);

        // 9. Seed Credits
        // Customer Credit (matching Sale 2)
        $credit1 = Credit::create([
            'type' => 'customer',
            'customer_id' => $customerModels[1]->id,
            'person_name' => $customerModels[1]->name,
            'phone' => $customerModels[1]->phone,
            'amount' => 750.00,
            'paid_amount' => 200.00, // Customer paid 200 rs upfront
            'due_date' => $now->copy()->addDays(15)->format('Y-m-d'),
            'status' => 'pending',
            'notes' => 'Udhaar for butter & salt. Invoice INV-2026-0002.'
        ]);

        CreditPayment::create([
            'credit_id' => $credit1->id,
            'amount_paid' => 200.00,
            'payment_date' => $now->copy()->subDays(1)->format('Y-m-d'),
            'payment_method' => 'cash',
            'notes' => 'Partial cash paid during checkout.'
        ]);

        // Supplier Due (buying rice & oil in bulk)
        $credit2 = Credit::create([
            'type' => 'supplier',
            'supplier_id' => $supplierModels[0]->id,
            'person_name' => $supplierModels[0]->name,
            'phone' => $supplierModels[0]->phone,
            'amount' => 20000.00,
            'paid_amount' => 15000.00,
            'due_date' => $now->copy()->addDays(10)->format('Y-m-d'),
            'status' => 'pending',
            'notes' => 'Bulk grocery delivery. Need to pay remaining next week.'
        ]);

        CreditPayment::create([
            'credit_id' => $credit2->id,
            'amount_paid' => 15000.00,
            'payment_date' => $now->copy()->subDays(4)->format('Y-m-d'),
            'payment_method' => 'net_banking',
            'notes' => 'First installment via net banking.'
        ]);
    }
}
