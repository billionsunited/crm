@extends('layouts.app')
@section('header', 'Customers')

@section('content')
<div class="flex flex-col h-full">
    <!-- Page Header & Actions -->
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 tracking-tight">Customer Management</h1>
            <p class="text-sm text-slate-500 mt-1">Manage and view all your corporate clients and customers.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative flex items-center h-12 w-full max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" class="block w-full h-12 pl-11 pr-4 bg-white border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Search customers...">
            </div>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center px-6 h-12 border border-transparent rounded-lg shadow-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">
                Add Customer
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex-1 mt-6">
        <div class="overflow-x-auto h-full">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/80 backdrop-blur-sm sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Company</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Client Name</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact Info</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Registered</th>
                        <th scope="col" class="relative px-6 py-4">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 bg-indigo-100 text-indigo-700 rounded-lg flex items-center justify-center font-bold text-sm">
                                        {{ substr($customer->company_name ?? 'C', 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-slate-900">{{ $customer->company_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-slate-700 font-medium">{{ $customer->client_name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">
                                    {{ $customer->place ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col text-sm text-slate-600">
                                    <span>{{ $customer->mobile_no ?? 'N/A' }}</span>
                                    <span class="text-slate-500">{{ $customer->email_id ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $customer->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-2 py-1 rounded-md hover:bg-indigo-100 transition-colors">Edit</a>
                                    
                                    <div x-data="{ showModal: false }">
                                        <button @click="showModal = true" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded-md hover:bg-red-100 transition-colors">Delete</button>
                                        
                                        <!-- Delete Modal -->
                                        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;" x-transition>
                                            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center transform transition-all whitespace-normal" @click.away="showModal = false">
                                                <div class="mb-4">
                                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                                                      <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                      </svg>
                                                    </div>
                                                    <h3 class="text-lg font-bold text-slate-800">Delete Customer</h3>
                                                    <p class="text-sm text-slate-500 mt-2 whitespace-normal">Are you sure you want to delete <strong>{{ $customer->client_name }}</strong>? This action cannot be undone.</p>
                                                </div>
                                                <div class="flex justify-center gap-3 w-full mt-6">
                                                    <button @click="showModal = false" type="button" class="w-full justify-center px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg hover:bg-slate-50 font-medium shadow-sm">Cancel</button>
                                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="w-full">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-sm transition-colors">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6">
                                <div class="flex flex-col items-center justify-center py-12 text-center gap-3">
                                    <h3 class="text-sm font-semibold text-slate-900 mb-1">No Customers Found</h3>
                                    <p class="text-sm text-slate-500 mb-4">Get started by creating your first corporate client.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($customers->hasPages())
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
