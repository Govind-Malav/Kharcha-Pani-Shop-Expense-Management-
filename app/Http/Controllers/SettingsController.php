<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class SettingsController extends Controller
{
    public function index()
    {
        // Settings are only accessible by Owners
        if (!auth()->user()->isOwner()) {
            abort(403, 'Unauthorized action. Only Shop Owners can access settings.');
        }

        $settings = ShopSetting::firstOrCreate([
            'id' => 1
        ], [
            'shop_name' => 'My Shop',
            'currency' => 'INR',
            'low_stock_threshold' => 10
        ]);

        $staffUsers = User::where('id', '!=', auth()->id())->get();

        return view('settings.index', compact('settings', 'staffUsers'));
    }

    public function updateShop(Request $request)
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $settings = ShopSetting::first();

        $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_email' => 'nullable|email|max:255',
            'shop_phone' => 'nullable|string|max:20',
            'shop_address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'low_stock_threshold' => 'required|integer|min:1',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('shop', $filename, 'public');
            $data['logo_path'] = $path;
        }

        $settings->update($data);

        return redirect()->route('settings.index')->with('success', 'Shop settings updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('settings.index')->with('success', 'Password changed successfully!');
    }

    // Staff User Management (Owner only)
    public function storeStaff(Request $request)
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role' => 'required|in:staff,accountant',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);

        return redirect()->route('settings.index')->with('success', 'Staff account created successfully!');
    }

    public function toggleStaffStatus(User $user)
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Cannot disable your own administrator account.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return redirect()->route('settings.index')->with('success', "Staff status updated to {$newStatus}!");
    }
}
