@extends('layouts.app')

@section('header', 'Notifications')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200 bg-slate-50">
        <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-1/4">
                <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Follow-up Date</label>
                <input type="date" name="date" id="date" value="{{ request('date') }}" class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="w-full md:w-1/4">
                <label for="customer" class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                <input type="text" name="customer" id="customer" value="{{ request('customer') }}" placeholder="Search customer..." class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="w-full md:w-1/4">
                <label for="company" class="block text-sm font-medium text-slate-700 mb-1">Company</label>
                <input type="text" name="company" id="company" value="{{ request('company') }}" placeholder="Search company..." class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="w-full md:w-1/4">
                <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Keyword</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search title or message..." class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="w-full md:w-auto flex gap-2">
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Filter
                </button>
                <a href="{{ route('notifications.index') }}" class="inline-flex justify-center rounded-md border border-slate-300 bg-white py-2 px-4 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold">Title / Message</th>
                    <th class="p-4 font-semibold">Customer</th>
                    <th class="p-4 font-semibold">Company</th>
                    <th class="p-4 font-semibold">Follow-up Date</th>
                    <th class="p-4 font-semibold text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($notifications as $notification)
                    <tr class="hover:bg-slate-50 transition-colors {{ !$notification->is_read ? 'bg-indigo-50/30' : '' }}">
                        <td class="p-4">
                            @if($notification->is_read)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    Read
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                    Unread
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-900">{{ $notification->title }}</div>
                            <div class="text-sm text-slate-500">{{ $notification->message }}</div>
                        </td>
                        <td class="p-4 text-sm text-slate-700">{{ $notification->customer_name ?? '-' }}</td>
                        <td class="p-4 text-sm text-slate-700">{{ $notification->company_name ?? '-' }}</td>
                        <td class="p-4 text-sm text-slate-700">
                            {{ \Carbon\Carbon::parse($notification->follow_up_date)->format('d M, Y') }}
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Lead
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">
                            No notifications found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-200">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
