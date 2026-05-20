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

        <!-- Floating Voice Assistant -->
        <div x-data="voiceAssistant()" x-init="init()" class="relative">
            <!-- Wave bounce styles -->
            <style>
                @keyframes waveBounce {
                    0%, 100% { transform: scaleY(0.4); }
                    50% { transform: scaleY(1.2); }
                }
                .wave-bar-1 { animation: waveBounce 0.8s ease-in-out infinite 100ms; transform-origin: bottom; }
                .wave-bar-2 { animation: waveBounce 0.8s ease-in-out infinite 200ms; transform-origin: bottom; }
                .wave-bar-3 { animation: waveBounce 0.8s ease-in-out infinite 300ms; transform-origin: bottom; }
                .wave-bar-4 { animation: waveBounce 0.8s ease-in-out infinite 450ms; transform-origin: bottom; }
                .wave-bar-5 { animation: waveBounce 0.8s ease-in-out infinite 150ms; transform-origin: bottom; }
            </style>

            <!-- Floating Mic Trigger Button -->
            <button @click="toggle()" 
                    class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-gradient-to-tr from-teal-400 to-emerald-450 rounded-full flex items-center justify-center text-slate-905 shadow-xl shadow-teal-500/20 hover:scale-110 active:scale-95 transition-all cursor-pointer focus:outline-none"
                    :class="isListening ? 'ring-4 ring-emerald-400/40' : ''"
                    title="Voice Assistant">
                <i class="fa-solid fa-microphone text-lg" :class="isListening ? 'text-slate-900 animate-pulse' : ''"></i>
            </button>

            <!-- Glassmorphic Console Panel -->
            <div x-show="isOpen" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-10 scale-95"
                 class="fixed bottom-24 right-6 w-80 bg-white/95 backdrop-blur-md border border-slate-100 rounded-2xl shadow-2xl p-5 z-50 space-y-4"
                 style="display: none;">
                
                <!-- Console Title Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                        </div>
                        <span class="font-extrabold text-sm text-slate-800">Voice Assistant</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <!-- Language selector -->
                        <button @click="setLang(lang === 'en-US' ? 'hi-IN' : 'en-US')" 
                                class="px-2 py-0.5 rounded text-3xs font-extrabold tracking-wider bg-slate-100 hover:bg-slate-200 text-slate-600 uppercase border border-slate-200 transition-all">
                            <span x-text="lang === 'en-US' ? 'English' : 'हिंदी'"></span>
                        </button>
                        <button @click="isOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors pl-1">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Animated Recording Status -->
                <div class="flex flex-col items-center justify-center py-4 bg-slate-50/50 rounded-xl border border-slate-100 relative overflow-hidden">
                    <!-- Audio waves -->
                    <div class="flex items-end justify-center space-x-1 h-8 mb-3" x-show="waveActive">
                        <span class="w-1 bg-teal-400 rounded-full wave-bar-1" style="height: 10px"></span>
                        <span class="w-1 bg-emerald-400 rounded-full wave-bar-2" style="height: 22px"></span>
                        <span class="w-1 bg-teal-400 rounded-full wave-bar-3" style="height: 14px"></span>
                        <span class="w-1 bg-emerald-400 rounded-full wave-bar-4" style="height: 26px"></span>
                        <span class="w-1 bg-teal-400 rounded-full wave-bar-5" style="height: 10px"></span>
                    </div>
                    
                    <button @click="isListening ? stopListening() : startListening()" 
                            class="w-12 h-12 rounded-full flex items-center justify-center transition-all focus:outline-none cursor-pointer"
                            :class="isListening ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20' : 'bg-teal-500 text-white shadow-lg shadow-teal-500/20'">
                        <i class="fa-solid text-sm" :class="isListening ? 'fa-stop' : 'fa-microphone'"></i>
                    </button>
                    
                    <p class="text-3xs font-extrabold uppercase tracking-widest text-slate-400 mt-2.5" x-text="status"></p>
                </div>

                <!-- Real-Time Speech Display -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 min-h-[50px] flex items-center justify-center text-center">
                    <p class="text-xs font-semibold leading-relaxed" :class="transcript ? 'text-slate-800 italic' : 'text-slate-400'" 
                       x-text="transcript ? '“' + transcript + '”' : (lang === 'hi-IN' ? 'बोलने के लिए माइक दबाएं...' : 'Tap mic to say a command...')"></p>
                </div>

                <!-- Quick commands help cheat-sheet -->
                <div class="space-y-1.5 text-2xs">
                    <span class="block font-bold text-slate-500 uppercase tracking-wider">Try saying:</span>
                    <div class="divide-y divide-slate-100 bg-slate-50 rounded-xl border border-slate-100 overflow-hidden">
                        <div class="p-2 text-slate-600 flex justify-between items-center hover:bg-slate-100/50">
                            <span class="font-semibold" x-text="lang === 'hi-IN' ? 'किराया खर्चा 15000' : 'Add expense Rent 15000'"></span>
                            <span class="text-slate-450 font-medium">Record Expense</span>
                        </div>
                        <div class="p-2 text-slate-600 flex justify-between items-center hover:bg-slate-100/50">
                            <span class="font-semibold" x-text="lang === 'hi-IN' ? 'बेचा 500' : 'Add sale 500'"></span>
                            <span class="text-slate-450 font-medium">POS Order</span>
                        </div>
                        <div class="p-2 text-slate-600 flex justify-between items-center hover:bg-slate-100/50">
                            <span class="font-semibold" x-text="lang === 'hi-IN' ? 'खर्चा पर जाओ' : 'Go to Expenses'"></span>
                            <span class="text-slate-450 font-medium">Navigation</span>
                        </div>
                        <div class="p-2 text-slate-600 flex justify-between items-center hover:bg-slate-100/50">
                            <span class="font-semibold" x-text="lang === 'hi-IN' ? 'चावल ढूंढो' : 'Search Rice'"></span>
                            <span class="text-slate-450 font-medium">Search stock</span>
                        </div>
                        <!-- POS Page specific hint -->
                        <div x-show="window.location.pathname.includes('/sales/create')" class="p-2 text-indigo-700 bg-indigo-50/40 flex justify-between items-center hover:bg-indigo-50">
                            <span class="font-bold" x-text="lang === 'hi-IN' ? 'मक्खन जोड़ो' : 'Add Butter'"></span>
                            <span class="font-bold text-indigo-500">Cart Command</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            function voiceAssistant() {
                return {
                    isOpen: false,
                    isListening: false,
                    lang: 'en-US',
                    transcript: '',
                    status: 'Ready',
                    recognition: null,
                    synth: window.speechSynthesis,
                    waveActive: false,

                    init() {
                        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                        if (!SpeechRecognition) {
                            this.status = 'Not supported in browser';
                            return;
                        }
                        this.recognition = new SpeechRecognition();
                        this.recognition.continuous = false;
                        this.recognition.interimResults = true;

                        this.recognition.onstart = () => {
                            this.isListening = true;
                            this.waveActive = true;
                            this.status = this.lang === 'hi-IN' ? 'सुन रहा हूँ...' : 'Listening...';
                            this.transcript = '';
                        };

                        this.recognition.onresult = (event) => {
                            let interimTranscript = '';
                            for (let i = event.resultIndex; i < event.results.length; ++i) {
                                if (event.results[i].isFinal) {
                                    this.transcript = event.results[i][0].transcript;
                                    this.processCommand(this.transcript);
                                } else {
                                    interimTranscript += event.results[i][0].transcript;
                                    this.transcript = interimTranscript;
                                }
                            }
                        };

                        this.recognition.onerror = (event) => {
                            console.error(event);
                            this.status = 'Error: ' + event.error;
                            this.isListening = false;
                            this.waveActive = false;
                        };

                        this.recognition.onend = () => {
                            this.isListening = false;
                            this.waveActive = false;
                            if (this.status === 'Listening...' || this.status === 'सुन रहा हूँ...') {
                                this.status = this.lang === 'hi-IN' ? 'तैयार है' : 'Ready';
                            }
                        };
                    },

                    toggle() {
                        this.isOpen = !this.isOpen;
                        if (this.isOpen) {
                            this.speak(this.lang === 'hi-IN' ? 'नमस्ते, मैं आपकी कैसे मदद करूँ?' : 'Hello! How can I help you today?');
                            this.status = this.lang === 'hi-IN' ? 'तैयार है' : 'Ready';
                        } else {
                            this.stopListening();
                        }
                    },

                    startListening() {
                        if (!this.recognition) return;
                        this.recognition.lang = this.lang;
                        try {
                            this.recognition.start();
                        } catch (e) {
                            console.log(e);
                        }
                    },

                    stopListening() {
                        if (this.recognition && this.isListening) {
                            this.recognition.stop();
                        }
                    },

                    setLang(l) {
                        this.lang = l;
                        this.status = l === 'hi-IN' ? 'भाषा: हिंदी' : 'Language: English';
                        this.speak(l === 'hi-IN' ? 'हिंदी भाषा चुनी गई' : 'English mode activated');
                    },

                    speak(text) {
                        if (!this.synth) return;
                        this.synth.cancel();
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang = this.lang;
                        this.synth.speak(utterance);
                    },

                    processCommand(commandText) {
                        const cleanText = commandText.trim().toLowerCase();
                        this.status = this.lang === 'hi-IN' ? 'प्रोसेस कर रहा हूँ...' : 'Processing...';

                        // 1. Expense Command Parser
                        // English: "add expense rent 15000" / "add expense electricity 3200"
                        const expMatchEn = cleanText.match(/(?:add\s+expense|record\s+expense|record\s+spend)\s+(?:for\s+)?(.+?)\s+(?:of\s+)?(?:rs\.?|inr|rupees)?\s*(\d+(?:\.\d+)?)/i);
                        if (expMatchEn) {
                            const title = expMatchEn[1];
                            const amount = expMatchEn[2];
                            this.speak(`Recording expense for ${title} of ${amount} rupees.`);
                            setTimeout(() => {
                                window.location.href = `/expenses/create?title=${encodeURIComponent(title)}&amount=${amount}&ref=voice`;
                            }, 1200);
                            return;
                        }

                        // Hindi: "किराया का खर्चा 15000" / "चाय खर्चा 250" / "250 रुपया चाय खर्चा"
                        const expMatchHi = cleanText.match(/(.+?)\s+(?:का\s+)?खर्चा\s+(?:रुपया|रुपये)?\s*(\d+)/i) || 
                                           cleanText.match(/(\d+)\s*(?:रुपया|रुपये)?\s*(?:का\s+)?(.+?)\s+खर्चा/i);
                        if (expMatchHi) {
                            let title, amount;
                            if (isNaN(expMatchHi[1].trim())) {
                                title = expMatchHi[1].trim();
                                amount = expMatchHi[2].trim();
                            } else {
                                amount = expMatchHi[1].trim();
                                title = expMatchHi[2].trim();
                            }
                            this.speak(`${title} का खर्चा ${amount} रुपये दर्ज कर रहे हैं`);
                            setTimeout(() => {
                                window.location.href = `/expenses/create?title=${encodeURIComponent(title)}&amount=${amount}&ref=voice`;
                            }, 1200);
                            return;
                        }

                        // 2. Navigation Commands
                        // English: "go to dashboard" / "go to sales"
                        const navMatchEn = cleanText.match(/go\s+to\s+(dashboard|sales|pos|expenses|inventory|stock|credits|udhaar|settings)/i);
                        if (navMatchEn) {
                            const target = navMatchEn[1];
                            this.speak("Navigating to " + target);
                            setTimeout(() => this.redirect(target), 1000);
                            return;
                        }

                        // Hindi: "डैशबोर्ड पर जाओ" / "बिक्री पर जाओ"
                        const navMatchHi = cleanText.match(/(डैशबोर्ड|बिक्री|खर्चा|सामान|स्टॉक|उधार|सेटिंग)\s*(?:पर\s+)?जाओ/i);
                        if (navMatchHi) {
                            const target = navMatchHi[1];
                            const engTarget = this.mapHindiNav(target);
                            this.speak(target + " पर जा रहे हैं");
                            setTimeout(() => this.redirect(engTarget), 1000);
                            return;
                        }

                        // 3. POS Add Item (Only active on POS screen)
                        // English: "add rice" / "add butter"
                        const addPosMatchEn = cleanText.match(/(?:add|insert)\s+(.+?)(?:\s+to\s+cart)?$/i);
                        if (addPosMatchEn && window.location.pathname.includes('/sales/create')) {
                            const productName = addPosMatchEn[1];
                            this.speak("Adding " + productName);
                            window.dispatchEvent(new CustomEvent('voice-add-item', { detail: { query: productName } }));
                            this.status = "Added to cart";
                            return;
                        }

                        // Hindi: "चावल जोड़ो" / "मक्खन जोड़ो"
                        const addPosMatchHi = cleanText.match(/(.+?)\s+(?:जोड़ो|ऐड करो|ऐड करो)$/i);
                        if (addPosMatchHi && window.location.pathname.includes('/sales/create')) {
                            const productName = addPosMatchHi[1];
                            this.speak(productName + " जोड़ रहे हैं");
                            window.dispatchEvent(new CustomEvent('voice-add-item', { detail: { query: productName } }));
                            this.status = "कार्ट में जोड़ा गया";
                            return;
                        }

                        // 4. Record Quick Sales
                        // English: "add sale 500" / "sell 500"
                        const saleMatchEn = cleanText.match(/(?:add\s+sale|record\s+sale|sell)\s+(?:of\s+)?(?:rs\.?|inr|rupees)?\s*(\d+(?:\.\d+)?)/i);
                        if (saleMatchEn) {
                            const amount = saleMatchEn[1];
                            this.speak(`Recording walk-in sale of ${amount} rupees.`);
                            setTimeout(() => {
                                window.location.href = `/sales/create?amount=${amount}&ref=voice`;
                            }, 1200);
                            return;
                        }

                        // Hindi: "बिक्री 500" / "बेचा 500"
                        const saleMatchHi = cleanText.match(/(?:बेचा|बिक्री|सेल)\s*(?:रुपया|रुपये)?\s*(\d+)/i);
                        if (saleMatchHi) {
                            const amount = saleMatchHi[1];
                            this.speak(`${amount} रुपये की बिक्री दर्ज कर रहे हैं`);
                            setTimeout(() => {
                                window.location.href = `/sales/create?amount=${amount}&ref=voice`;
                            }, 1200);
                            return;
                        }

                        // 5. Inventory Product Search
                        // English: "search rice" / "search for amul butter"
                        const searchMatchEn = cleanText.match(/(?:search|find|lookup)\s+(?:for\s+)?(.+)/i);
                        if (searchMatchEn) {
                            const query = searchMatchEn[1];
                            this.speak("Searching for " + query);
                            setTimeout(() => {
                                window.location.href = `/inventory?search=${encodeURIComponent(query)}`;
                            }, 1000);
                            return;
                        }

                        // Hindi: "चावल ढूंढो" / "मक्खन सर्च"
                        const searchMatchHi = cleanText.match(/(.+?)\s+(?:सर्च|ढूंढो|खोजो)/i) || cleanText.match(/सर्च\s+(.+)/i);
                        if (searchMatchHi) {
                            const query = searchMatchHi[1];
                            this.speak("स्टॉक में " + query + " खोज रहे हैं");
                            setTimeout(() => {
                                window.location.href = `/inventory?search=${encodeURIComponent(query)}`;
                            }, 1000);
                            return;
                        }

                        // 6. Unknown Command Fallback
                        this.speak(this.lang === 'hi-IN' ? "माफ़ कीजिये, मुझे यह समझ नहीं आया" : "Sorry, I didn't recognize that command.");
                        this.status = this.lang === 'hi-IN' ? "त्रुटि: नहीं समझा" : "Command not recognized";
                    },

                    redirect(target) {
                        const routes = {
                            dashboard: "{{ route('dashboard') }}",
                            sales: "{{ route('sales.create') }}",
                            pos: "{{ route('sales.create') }}",
                            expenses: "{{ route('expenses.index') }}",
                            inventory: "{{ route('inventory.index') }}",
                            stock: "{{ route('inventory.index') }}",
                            credits: "{{ route('credits.index') }}",
                            udhaar: "{{ route('credits.index') }}",
                            settings: "{{ route('settings.index') }}"
                        };
                        if (routes[target]) {
                            window.location.href = routes[target];
                        }
                    },

                    mapHindiNav(hiText) {
                        const mapping = {
                            'डैशबोर्ड': 'dashboard',
                            'बिक्री': 'sales',
                            'खर्चा': 'expenses',
                            'सामान': 'inventory',
                            'स्टॉक': 'inventory',
                            'उधार': 'credits',
                            'सेटिंग': 'settings'
                        };
                        return mapping[hiText] || 'dashboard';
                    }
                };
            }
        </script>

        @stack('scripts')
    </body>
</html>
