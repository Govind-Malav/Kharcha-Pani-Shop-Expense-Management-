@extends('layouts.app')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto" x-data="{ staffModalOpen: false }">

    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
                <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
                <span class="text-slate-650">Shop Settings</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-sliders text-teal-500"></i> Settings & Staff Control
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">Manage shop credentials, configure default stock thresholds, and register staff credentials.</p>
        </div>
    </div>

    {{-- Main Core Layout Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Shop Settings form (Takes 2 Columns) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-100 shadow-md">
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight border-b border-slate-100 pb-4 mb-6">
                    <i class="fa-solid fa-store text-teal-500 mr-1.5"></i> Shop Profile Information
                </h3>

                <form method="POST" action="{{ route('settings.shop.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    {{-- Grid 2 col --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Name --}}
                        <div>
                            <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Shop Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="shop_name" value="{{ old('shop_name', $settings->shop_name) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                            @error('shop_name')
                                <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Shop Phone</label>
                            <input type="text" name="shop_phone" value="{{ old('shop_phone', $settings->shop_phone) }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                            @error('shop_phone')
                                <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Shop Email</label>
                            <input type="email" name="shop_email" value="{{ old('shop_email', $settings->shop_email) }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                            @error('shop_email')
                                <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- GSTIN --}}
                        <div>
                            <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">GSTIN / Tax ID</label>
                            <input type="text" name="gst_number" value="{{ old('gst_number', $settings->gst_number) }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800 uppercase">
                            @error('gst_number')
                                <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Currency --}}
                        <div>
                            <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Currency Sign <span class="text-rose-500">*</span></label>
                            <input type="text" name="currency" value="{{ old('currency', $settings->currency) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                            @error('currency')
                                <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Low stock trigger --}}
                        <div>
                            <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Default Low Stock Alert Threshold <span class="text-rose-500">*</span></label>
                            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}" required min="1"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                            @error('low_stock_threshold')
                                <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- Address --}}
                    <div>
                        <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Shop Address</label>
                        <textarea name="shop_address" rows="3"
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">{{ old('shop_address', $settings->shop_address) }}</textarea>
                        @error('shop_address')
                            <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Logo Upload with interactive preview --}}
                    <div x-data="{ logoPreview: '{{ $settings->logo_path ? asset('storage/' . $settings->logo_path) : '' }}' }">
                        <label class="block text-2xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Shop Logo Brand</label>
                        
                        <div class="flex items-center space-x-6 bg-slate-50/50 p-4 rounded-2xl border border-slate-200/50">
                            
                            {{-- Logo Avatar preview --}}
                            <div class="w-16 h-16 rounded-2xl bg-white border border-slate-100 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-sm">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    <i class="fa-solid fa-image text-slate-300 text-xl"></i>
                                </template>
                            </div>

                            {{-- File upload click trigger --}}
                            <div class="space-y-1">
                                <input type="file" name="logo" id="logo-input" class="sr-only" accept="image/*"
                                       @change="const file = $event.target.files[0]; if (file) { logoPreview = URL.createObjectURL(file); }">
                                <label for="logo-input" class="inline-flex items-center px-4 py-2 rounded-xl text-2xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm cursor-pointer">
                                    <i class="fa-solid fa-cloud-arrow-up mr-1.5 text-teal-500"></i> Choose New Logo
                                </label>
                                <span class="block text-3xs text-slate-400">Accepts PNG, JPG, JPEG up to 2MB.</span>
                            </div>

                        </div>
                        @error('logo')
                            <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Submit button --}}
                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-tr from-teal-400 to-emerald-400 hover:from-teal-500 hover:to-emerald-500 transition-all shadow-md shadow-emerald-500/10">
                            <i class="fa-solid fa-circle-check mr-1.5"></i> Update Shop Details
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- Right Panel: Security + Staff control --}}
        <div class="space-y-8">
            
            {{-- Tab 1: Staff management --}}
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-slate-900 text-sm tracking-tight flex items-center gap-1.5">
                        <i class="fa-solid fa-users text-teal-500"></i> Staff Registry
                    </h3>
                    <button @click="staffModalOpen = true" class="inline-flex items-center justify-center p-1.5 rounded-lg text-teal-600 bg-teal-50 hover:bg-teal-100 transition-colors" title="Add new staff member">
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </button>
                </div>

                <div class="space-y-4 max-h-72 overflow-y-auto">
                    @forelse($staffUsers as $staff)
                        <div class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-100 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-slate-900/10 text-slate-700 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ strtoupper(substr($staff->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="block text-xs font-bold text-slate-800 line-clamp-1 leading-snug">{{ $staff->name }}</span>
                                    <span class="block text-3xs font-bold uppercase tracking-wider mt-0.5
                                        @if($staff->role === 'accountant') text-indigo-500
                                        @else text-teal-550
                                        @endif">
                                        {{ $staff->role }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Toggle account status switch --}}
                            <form method="POST" action="{{ route('settings.staff.toggle', $staff->id) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-3xs font-extrabold uppercase border tracking-wide transition-all
                                    @if($staff->status === 'active') bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100
                                    @else bg-rose-50 text-rose-700 border-rose-100 hover:bg-rose-100
                                    @endif">
                                    <i class="fa-solid @if($staff->status === 'active') fa-toggle-on text-emerald-500 @else fa-toggle-off text-rose-500 @endif mr-0.5"></i>
                                    {{ $staff->status }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i class="fa-solid fa-users-slash text-slate-300 text-2xl mb-2 block"></i>
                            <span class="text-xs font-medium text-slate-400">No staff members enrolled yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tab 2: Security Change Password --}}
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md">
                <h3 class="font-extrabold text-slate-900 text-sm tracking-tight border-b border-slate-100 pb-3 mb-5 flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-teal-500"></i> Password Security
                </h3>

                <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-4">
                    @csrf
                    
                    {{-- Current password --}}
                    <div>
                        <label class="block text-3xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Current password</label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                        @error('current_password')
                            <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- New password --}}
                    <div>
                        <label class="block text-3xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">New password</label>
                        <input type="password" name="new_password" required
                               class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                        @error('new_password')
                            <span class="block text-3xs font-bold text-rose-500 mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Confirm password --}}
                    <div>
                        <label class="block text-3xs font-extrabold text-slate-455 uppercase tracking-wider mb-2">Confirm password</label>
                        <input type="password" name="new_password_confirmation" required
                               class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                    </div>

                    <button type="submit" class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                        <i class="fa-solid fa-key text-3xs"></i> Update Password
                    </button>
                </form>
            </div>

        </div>

    </div>

    {{-- Interactive AlpineJS New Staff Enrollment Modal --}}
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
         x-show="staffModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl border border-slate-150 p-6 space-y-5"
             @click.away="staffModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900 tracking-tight">Register Staff Account</h3>
                <button @click="staffModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('settings.staff.store') }}" class="space-y-4">
                @csrf
                
                {{-- Name --}}
                <div>
                    <label class="block text-3xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Full Name</label>
                    <input type="text" name="name" required placeholder="Employee Name"
                           class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-3xs font-extrabold text-slate-455 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" required placeholder="name@shop.com"
                           class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-3xs font-extrabold text-slate-455 uppercase tracking-wider mb-2">Access Password</label>
                    <input type="password" name="password" required placeholder="Secret Keys"
                           class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-xs font-semibold text-slate-800">
                </div>

                {{-- Role select --}}
                <div>
                    <label class="block text-3xs font-extrabold text-slate-450 uppercase tracking-wider mb-2">Store Role Authority</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="border border-slate-200 rounded-xl p-3 flex items-center justify-center gap-1.5 cursor-pointer hover:bg-slate-50 transition-colors relative">
                            <input type="radio" name="role" value="staff" checked class="sr-only peer">
                            <div class="peer-checked:border-teal-500 absolute inset-0 border-2 border-transparent rounded-xl pointer-events-none"></div>
                            <i class="fa-solid fa-clipboard-user text-teal-500"></i>
                            <span class="text-xs font-bold text-slate-700">Staff Agent</span>
                        </label>
                        
                        <label class="border border-slate-200 rounded-xl p-3 flex items-center justify-center gap-1.5 cursor-pointer hover:bg-slate-50 transition-colors relative">
                            <input type="radio" name="role" value="accountant" class="sr-only peer">
                            <div class="peer-checked:border-teal-500 absolute inset-0 border-2 border-transparent rounded-xl pointer-events-none"></div>
                            <i class="fa-solid fa-file-invoice-dollar text-indigo-500"></i>
                            <span class="text-xs font-bold text-slate-700">Accountant</span>
                        </label>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="staffModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-50 hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 transition-colors">
                        Register Account
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
