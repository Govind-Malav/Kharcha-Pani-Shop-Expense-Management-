<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kharcha Pani') }} - Shop Expense & Management</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- FontAwesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Tailwind CSS & JS (Vite) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
            .sidebar-gradient {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            }
            .glassmorphism {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </head>
    <body class="h-full font-sans antialiased text-slate-800" x-data="{ sidebarOpen: false }">
        
        <!-- Mobile Sidebar Backdrop -->
        <div class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
             x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             style="display: none;"></div>

        <!-- Sidebar (Desktop and Mobile) -->
        <aside class="fixed inset-y-0 left-0 z-50 flex flex-col w-72 sidebar-gradient text-slate-100 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <!-- Brand Logo Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-500 shadow-lg shadow-teal-500/20 group-hover:scale-105 transition-transform duration-200">
                        <i class="fa-solid fa-wallet text-slate-900 text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">Kharcha Pani</span>
                        <span class="block text-xs font-medium text-teal-400 tracking-wider uppercase">Expense & POS</span>
                    </div>
                </a>
                
                <!-- Close Button Mobile -->
                <button class="text-slate-400 hover:text-white lg:hidden" @click="sidebarOpen = false">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Active User card in sidebar -->
            <div class="px-6 py-4 border-b border-slate-800 bg-slate-950/20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-sm shadow-md">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <h4 class="font-semibold text-sm leading-tight text-white">{{ auth()->user()->name }}</h4>
                        <span class="inline-flex items-center px-2 py-0.5 mt-0.5 rounded-full text-2xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                            {{ auth()->user()->role }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-chart-line w-6 text-lg {{ request()->routeIs('dashboard') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Expenses -->
                <a href="{{ route('expenses.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('expenses.*') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-receipt w-6 text-lg {{ request()->routeIs('expenses.*') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Expenses</span>
                </a>

                <!-- Sales / Checkouts -->
                @if(auth()->user()->isOwner() || auth()->user()->isStaff())
                <a href="{{ route('sales.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('sales.*') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-cart-shopping w-6 text-lg {{ request()->routeIs('sales.*') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Sales (POS)</span>
                </a>
                @endif

                <!-- Inventory -->
                <a href="{{ route('inventory.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('inventory.*') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-boxes-stacked w-6 text-lg {{ request()->routeIs('inventory.*') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Inventory</span>
                </a>

                <!-- Credit System (Udhaar) -->
                @if(auth()->user()->isOwner() || auth()->user()->isStaff())
                <a href="{{ route('credits.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('credits.*') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-handshake-angle w-6 text-lg {{ request()->routeIs('credits.*') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Credits & Udhaar</span>
                </a>
                @endif

                <!-- Reports (Owner / Accountant) -->
                @if(auth()->user()->isOwner() || auth()->user()->isAccountant())
                <div class="pt-4 pb-2">
                    <span class="px-4 text-xs font-semibold tracking-wider text-slate-500 uppercase">Analytics & Ledger</span>
                </div>

                <a href="{{ route('reports.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('reports.*') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-6 text-lg {{ request()->routeIs('reports.*') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Ledger & Reports</span>
                </a>

                <a href="{{ route('analytics.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('analytics.*') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-chart-pie w-6 text-lg {{ request()->routeIs('analytics.*') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Growth Analytics</span>
                </a>
                @endif

                <!-- Settings -->
                @if(auth()->user()->isOwner())
                <div class="pt-4 pb-2">
                    <span class="px-4 text-xs font-semibold tracking-wider text-slate-500 uppercase">Settings</span>
                </div>

                <a href="{{ route('settings.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('settings.*') ? 'bg-teal-500 text-slate-950 font-semibold shadow-lg shadow-teal-500/15' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100' }}">
                    <i class="fa-solid fa-sliders w-6 text-lg {{ request()->routeIs('settings.*') ? 'text-slate-950' : 'text-slate-400 group-hover:text-teal-400' }}"></i>
                    <span>Shop Settings</span>
                </a>
                @endif
            </nav>

            <!-- Bottom Log Out -->
            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-3 text-slate-400 hover:text-white hover:bg-red-500/15 border border-transparent hover:border-red-500/30 rounded-xl transition-all duration-200 font-medium">
                        <i class="fa-solid fa-arrow-right-from-bracket w-6 text-lg group-hover:text-white"></i>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Content Area Wrapper -->
        <div class="lg:pl-72 flex flex-col min-h-screen">
            
            <!-- Top Header Navbar -->
            <header class="sticky top-0 z-30 flex items-center justify-between h-16 px-6 border-b border-slate-200/80 bg-white/80 backdrop-blur-md">
                
                <!-- Menu Toggle Button Mobile -->
                <div class="flex items-center">
                    <button class="p-2 -ml-2 text-slate-600 rounded-lg lg:hidden hover:bg-slate-100 hover:text-slate-900 focus:outline-none"
                            @click="sidebarOpen = true">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Quick Shop Stats or Header Text -->
                    <div class="hidden md:flex items-center space-x-3 ml-2">
                        <span class="text-sm font-semibold text-slate-500">Live Workspace:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <i class="fa-solid fa-store mr-1.5"></i> {{ \App\Models\ShopSetting::first()?->shop_name ?? 'Kharcha Pani' }}
                        </span>
                    </div>
                </div>

                <!-- Right Side Actions: Notification + Profile -->
                <div class="flex items-center space-x-4">
                    
                    <!-- Notification Bell Dropdown -->
                    @php
                        $unreadNotifications = auth()->user()->unreadNotifications;
                        $unreadCount = $unreadNotifications->count();
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl focus:outline-none transition-colors duration-200">
                            <i class="fa-regular fa-bell text-lg"></i>
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 flex items-center justify-center w-5 h-5 text-2xs font-extrabold text-white bg-rose-500 rounded-full animate-bounce">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl shadow-slate-900/10 border border-slate-100 overflow-hidden"
                             style="display: none;">
                            
                            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-100">
                                <span class="font-semibold text-sm text-slate-700">Notifications</span>
                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-teal-600 hover:text-teal-700">Mark all read</button>
                                    </form>
                                @endif
                            </div>

                            <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                @forelse($unreadNotifications->take(5) as $noti)
                                    <div class="p-4 hover:bg-slate-50/80 transition-colors">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center mr-3">
                                                @if(($noti->data['type'] ?? '') === 'low_stock')
                                                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                                                @elseif(($noti->data['type'] ?? '') === 'credit_reminder')
                                                    <i class="fa-regular fa-clock text-blue-500"></i>
                                                @else
                                                    <i class="fa-solid fa-circle-info"></i>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-bold text-slate-800 leading-tight">{{ $noti->data['title'] ?? 'Alert' }}</p>
                                                <p class="text-2xs text-slate-500 mt-1 leading-normal line-clamp-2">{{ $noti->data['message'] ?? '' }}</p>
                                                <span class="block text-3xs text-slate-400 mt-1.5">{{ $noti->created_at->diffForHumans() }}</span>
                                            </div>
                                            <!-- Check Mark to Read -->
                                            <form method="POST" action="{{ route('notifications.read', $noti->id) }}" class="ml-2">
                                                @csrf
                                                <button type="submit" class="text-slate-300 hover:text-teal-500 transition-colors" title="Mark Read">
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center">
                                        <i class="fa-regular fa-bell-slash text-slate-300 text-2xl mb-2 block"></i>
                                        <span class="text-xs font-medium text-slate-400">All caught up! No unread notifications.</span>
                                    </div>
                                @endforelse
                            </div>

                            <a href="{{ route('notifications.index') }}" 
                               class="block py-2.5 bg-slate-50 border-t border-slate-100 text-center text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
                                View all alerts
                            </a>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center space-x-2.5 p-1 rounded-xl hover:bg-slate-50 transition-colors focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-slate-900 flex items-center justify-center font-bold text-white text-xs shadow-md">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <span class="hidden md:inline text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs hidden md:inline"></i>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl shadow-slate-900/10 border border-slate-100 py-1"
                             style="display: none;">
                            
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-regular fa-user mr-2 text-slate-400"></i> My Profile
                            </a>

                            @if(auth()->user()->isOwner())
                            <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-sliders mr-2 text-slate-400"></i> Shop Settings
                            </a>
                            @endif

                            <hr class="my-1 border-slate-100">

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50/50">
                                    <i class="fa-solid fa-arrow-right-from-bracket mr-2 text-rose-400"></i> Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Layout Content -->
            <main class="flex-1 p-6 md:p-8 bg-slate-50">
                <!-- Page Breadcrumb / Dynamic Heading -->
                @isset($header)
                    <div class="mb-6">
                        {{ $header }}
                    </div>
                @endisset

                <!-- Core Yield Content -->
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>

        <!-- Toast Notifications Layer -->
        @if(session('success') || session('error'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-4"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed bottom-5 right-5 z-50 flex items-center p-4 w-full max-w-sm bg-white rounded-2xl shadow-xl shadow-slate-900/10 border {{ session('success') ? 'border-emerald-100' : 'border-rose-100' }} overflow-hidden"
                 style="display: none;">
                
                <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg {{ session('success') ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500' }} mr-3">
                    @if(session('success'))
                        <i class="fa-solid fa-check-double"></i>
                    @else
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    @endif
                </div>

                <div class="flex-1 min-w-0 mr-2">
                    <p class="text-xs font-bold text-slate-800">{{ session('success') ? 'Success' : 'Attention Needed' }}</p>
                    <p class="text-2xs text-slate-500 mt-0.5 leading-normal">{{ session('success') ?? session('error') }}</p>
                </div>

                <button @click="show = false" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        @endif

    </body>
</html>
