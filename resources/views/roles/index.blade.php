@extends('layouts.app')
@section('header', 'Manage Roles')

@section('content')
<style>
    @media (max-width: 640px) {
        .mobile-header-actions {
            flex-direction: column !important;
            width: 100% !important;
            margin-top: 1rem !important;
        }
        .mobile-header-actions > * {
            width: 100% !important;
            justify-content: center !important;
        }

        /* Stack Table for Mobile */
        thead { display: none !important; }
        tbody tr { 
            display: flex !important; 
            flex-direction: column !important; 
            padding: 1.25rem 0 !important;
            gap: 1rem !important;
        }
        tbody td { 
            display: block !important;
            width: 100% !important; 
            padding: 0 1.5rem !important; 
            border: none !important;
            text-align: left !important;
        }
        tbody td:last-child > div { 
            justify-content: flex-start !important; 
            margin-top: 0.5rem !important;
        }

        /* Permissions side-by-side within their row */
        .permissions-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.4rem !important;
            width: 100% !important;
        }
        .permissions-grid span {
            width: 100% !important;
            font-size: 10px !important;
            padding: 8px 6px !important;
            text-align: center !important;
        }
    }
</style>
<div class="relative">
    <div class="flex flex-col h-full transition duration-200">
        <!-- Page Header & Actions -->
        <div class="flex items-center justify-between gap-4 mb-6" style="flex-wrap: wrap;">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 tracking-tight">System Roles ✨</h1>
                <p class="text-sm text-slate-500 mt-1">Define and manage user roles and their associated permissions.</p>
            </div>

            <div class="flex items-center gap-3 mobile-header-actions">
                <a href="{{ route('roles.create') }}"
                    class="inline-flex items-center justify-center px-6 h-12 border border-transparent rounded-lg shadow-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Role
                </a>
            </div>
        </div>

        <!-- Top Scrollbar -->
        <div x-data="topScrollHandler()" x-init="init()" class="flex flex-col gap-2 mt-6">
            @include('partials.top-scrollbar')

            <!-- Table Container -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex-1">
                <div x-ref="contentContainer" @scroll="sync($el, $refs.topScrollbar)" class="overflow-x-auto h-full">
                    <table x-ref="mainTable" class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/80 backdrop-blur-sm sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role Name</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Permissions</th>
                            <th scope="col" class="relative px-6 py-4 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($roles as $role)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm border border-indigo-100">
                                        {{ strtoupper(substr($role->name, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-bold text-slate-900 uppercase tracking-wide">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 w-full max-w-4xl permissions-grid">
                                    @foreach($role->permissions as $permission)
                                    <span class="flex items-center justify-center px-3 py-2 rounded-lg text-xs font-semibold bg-white text-slate-600 border border-slate-200 uppercase tracking-wide shadow-sm text-center">
                                        @if($permission->name === 'campaign-send')
                                            Mobile Marketing
                                        @elseif($permission->name === 'email-section')
                                            Invoice Email Module
                                        @elseif($permission->name === 'lead-send-document')
                                            Email Send Document
                                        @elseif($permission->name === 'client-po-access')
                                            Client PO
                                        @elseif($permission->name === 'vendor-po-access')
                                            Vendor PO
                                        @elseif(str_starts_with($permission->name, 'campaign-'))
                                            Enquiry {{ str_replace('campaign-', '', $permission->name) }}
                                        @elseif($permission->name === 'email-template-send')
                                            Send Email Marketing
                                        @elseif($permission->name === 'raise-invoice-bu')
                                            Raise Invoice BU
                                        @elseif($permission->name === 'raise-invoice-or')
                                            Raise Invoice OR
                                        @elseif(str_starts_with($permission->name, 'email-template-'))
                                            Email Template {{ str_replace('email-template-', '', $permission->name) }}
                                        @else
                                            {{ str_replace(['-', 'section'], [' ', 'Module'], $permission->name) }}
                                        @endif
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    @if($role->name !== 'admin')
                                    <a href="{{ route('roles.edit', $role->id) }}" 
                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition-colors flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    @endif
                                    @if($role->name !== 'admin')
                                    <div x-data="{ showModal: false }" class="inline">
                                        <button @click="showModal = true"
                                            class="text-rose-600 hover:text-rose-900 bg-rose-50 px-3 py-1.5 rounded-lg hover:bg-rose-100 transition-colors flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>

                                        <div x-show="showModal" style="display: none;" 
                                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-transition>
                                            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center transform transition-all whitespace-normal" @click.away="showModal = false">
                                                <div class="mb-4">
                                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 mb-4">
                                                        <svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
                                                    <h3 class="text-lg font-bold text-slate-800">Delete Role</h3>
                                                    <p class="text-sm text-slate-600 mt-2">Are you sure you want to delete the <strong>{{ $role->name }}</strong> role? This action cannot be undone.</p>
                                                </div>
                                                <div class="flex justify-center gap-3 w-full mt-6">
                                                    <button @click="showModal = false" type="button" class="w-full justify-center px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg hover:bg-slate-50 font-medium shadow-sm">Cancel</button>
                                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="w-full">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full justify-center px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 font-medium shadow-sm transition-colors">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
</div>
@endsection
