@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- Breadcrumbs & Top actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-400 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Home</a>
                <span><i class="fa-solid fa-chevron-right text-3xs"></i></span>
                <span class="text-slate-650">Store Alerts & Notifications</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-regular fa-bell text-teal-500"></i> Store Alerts Inbox
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-medium font-sans">Review automated alerts about low inventory thresholds, customer due credits, and store events.</p>
        </div>

        @if($notifications->whereNull('read_at')->count() > 0)
            <div class="flex-shrink-0">
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-sm">
                        <i class="fa-solid fa-check-double mr-1.5 text-teal-500"></i> Mark All As Read
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Inbox Panel --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
        
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $noti)
                <div class="p-5 hover:bg-slate-50/40 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $noti->read_at ? 'opacity-70' : 'bg-teal-50/5' }}">
                    
                    {{-- Left Details --}}
                    <div class="flex items-start space-x-4">
                        
                        {{-- Icon badge based on notification type --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                            @if(($noti->data['type'] ?? '') === 'low_stock') bg-amber-50 text-amber-500 border border-amber-100/50
                            @elseif(($noti->data['type'] ?? '') === 'credit_reminder') bg-blue-50 text-blue-500 border border-blue-100/50
                            @else bg-teal-50 text-teal-550 border border-teal-100/50
                            @endif">
                            @if(($noti->data['type'] ?? '') === 'low_stock')
                                <i class="fa-solid fa-triangle-exclamation text-base"></i>
                            @elseif(($noti->data['type'] ?? '') === 'credit_reminder')
                                <i class="fa-regular fa-clock text-base"></i>
                            @else
                                <i class="fa-solid fa-circle-info text-base"></i>
                            @endif
                        </div>

                        {{-- Message context --}}
                        <div class="min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="block text-xs font-bold text-slate-800 leading-snug">{{ $noti->data['title'] ?? 'Alert' }}</span>
                                @if(!$noti->read_at)
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-4xs font-extrabold uppercase tracking-wide bg-teal-100 text-teal-850">New</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-normal max-w-xl font-medium">{{ $noti->data['message'] ?? '' }}</p>
                            <span class="block text-3xs text-slate-400 mt-1.5 font-bold"><i class="fa-regular fa-calendar mr-1"></i>{{ $noti->created_at->format('d M Y, h:i A') }} ({{ $noti->created_at->diffForHumans() }})</span>
                        </div>

                    </div>

                    {{-- Right Actions --}}
                    <div class="flex items-center gap-2 self-end sm:self-center">
                        @if(!$noti->read_at)
                            <form method="POST" action="{{ route('notifications.read', $noti->id) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 hover:border-teal-200 hover:bg-teal-50/20 text-slate-400 hover:text-teal-600 transition-all" title="Mark read">
                                    <i class="fa-solid fa-check text-xs"></i>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('notifications.destroy', $noti->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl border border-slate-200 hover:border-rose-200 hover:bg-rose-50/20 text-slate-400 hover:text-rose-600 transition-all" title="Remove notification">
                                <i class="fa-regular fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="py-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4 border border-slate-100">
                        <i class="fa-regular fa-bell-slash text-slate-350 text-2xl"></i>
                    </div>
                    <h3 class="font-extrabold text-slate-900 tracking-tight text-sm">Inbox is completely clear</h3>
                    <p class="text-3xs text-slate-450 mt-1 font-semibold">You don't have any pending shop warnings or notices right now.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination footer wrapper --}}
        @if($notifications->hasPages())
            <div class="p-5 bg-slate-50 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
