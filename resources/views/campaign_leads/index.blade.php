@extends('layouts.app')
@section('header', 'Enquiry Campaign')

@section('content')
    <style>
        /* Force hide default select arrow for all browsers */
        select.appearance-none {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: none !important;
        }

        select.appearance-none::-ms-expand {
            display: none !important;
        }

        /* Desktop default layout for header actions & bulk actions */
        .campaign-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.75rem;
        }
        
        .campaign-actions-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .campaign-bulk-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .campaign-mobile-only {
            display: none !important;
        }

        .campaign-desktop-only {
            display: inline-block !important;
        }

        /* Mobile media query overrides */
        @media (max-width: 639px) {
            .campaign-header-right {
                width: 100% !important;
                align-items: stretch !important;
                gap: 0.75rem !important;
            }


            .campaign-bulk-bar {
                width: 100% !important;
                background-color: #f8fafc !important; /* bg-slate-50 */
                border: 1px solid #e2e8f0 !important; /* border-slate-200 */
                border-radius: 0.75rem !important; /* rounded-xl */
                padding: 0.875rem !important; /* p-3.5 */
                flex-direction: column !important; /* flex-col */
                align-items: stretch !important;
                gap: 0.75rem !important;
            }

            .campaign-bulk-bar-buttons {
                flex-direction: column !important;
                align-items: stretch !important;
                width: 100% !important;
                gap: 0.5rem !important;
            }

            .campaign-bulk-bar-buttons button {
                width: 100% !important;
            }

            .campaign-mobile-only {
                display: flex !important;
            }

            .campaign-desktop-only {
                display: none !important;
            }
        }
    </style>

    <div class="relative">
        <div x-data="{
            selectedIds: [],
            leadData: { 
                @foreach($leads as $lead)
                    '{{ $lead->id }}': { id: '{{ $lead->id }}', name: '{{ addslashes($lead->customer_name) }}', mobile: '{{ $lead->mobile }}' },
                @endforeach
            },
            selectAll: false,
            importModalOpen: false,
            toggleAll() {
                if (this.selectAll) {
                    this.selectedIds = Array.from(document.querySelectorAll('.lead-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedIds = [];
                }
            },
            exportSelected() {
                if (this.selectedIds.length === 0) {
                    alert('Please select at least one lead to export.');
                    return;
                }
                window.location.href = '{{ route('campaign-leads.export') }}?type=selected&ids=' + this.selectedIds.join(',');
            }
        }" class="flex flex-col h-full transition duration-200">

            <!-- Page Header & Actions -->
            <div class="flex items-start justify-between gap-4 mb-6" style="flex-wrap: wrap;">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 tracking-tight">Enquiry Campaign</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage marketing leads and run targeted campaigns.</p>
                </div>

                <div class="campaign-header-right w-full sm:w-auto">
                    <div class="campaign-actions-row w-full sm:w-auto flex items-center gap-2">
                    <div x-data="{ actionsOpen: false }" class="relative inline-block text-left">
                        <button @click="actionsOpen = !actionsOpen" @click.away="actionsOpen = false" type="button"
                            class="inline-flex items-center justify-center gap-2 px-4 h-10 border border-slate-300 rounded-lg shadow-sm font-semibold text-sm text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all whitespace-nowrap">
                            Actions
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="actionsOpen"
                            class="absolute left-0 sm:left-auto sm:right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-[100]"
                            style="display: none;"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95">
                            <div class="py-1">
                                @can('campaign-import')
                                <button type="button" @click="importModalOpen = true; actionsOpen = false;"
                                    class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    Import Leads
                                </button>
                                @endcan

                                @can('campaign-export')
                                <a href="{{ route('campaign-leads.export') }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Export All Leads
                                </a>
                                <a href="{{ route('campaign-leads.export', ['type' => 'filtered'] + request()->all()) }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Export Filtered
                                </a>
                                @endcan

                                @can('campaign-delete')
                                <div class="border-t border-slate-100 my-1"></div>
                                <form action="{{ route('campaign-leads.delete-all') }}" method="POST"
                                    onsubmit="return confirm('{{ request()->anyFilled(['search', 'rate', 'duplicate']) ? 'WARNING: This will permanently delete ALL campaign leads matching the active filters. This action cannot be undone. Are you sure you want to proceed?' : 'WARNING: This will permanently delete ALL campaign leads. This action cannot be undone. Are you sure you want to proceed?' }}') && confirm('Please confirm once more: Do you really want to delete them?')">
                                    @csrf
                                    @if(request()->filled('search'))
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    @if(request()->filled('rate'))
                                        <input type="hidden" name="rate" value="{{ request('rate') }}">
                                    @endif
                                    @if(request()->filled('duplicate'))
                                        <input type="hidden" name="duplicate" value="{{ request('duplicate') }}">
                                    @endif
                                    <button type="submit"
                                        class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-slate-100 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete All {{ request()->anyFilled(['search', 'rate', 'duplicate']) ? 'Filtered' : '' }}
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                    
                    @can('campaign-add')
                    <a href="{{ route('campaign-leads.create') }}"
                        class="inline-flex items-center justify-center gap-2 px-4 h-10 border border-transparent rounded-lg shadow-sm font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all whitespace-nowrap add-lead-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Lead
                    </a>
                    @endcan
                    </div>
 
                    <!-- Bulk Actions -->
                    <div x-show="selectedIds.length > 0" x-transition
                        class="campaign-bulk-bar animate-in fade-in slide-in-from-top-2 duration-200">
                        
                        <!-- Mobile Selection Info/Clear Bar (hidden on desktop) -->
                        <div class="campaign-mobile-only w-full items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-6 px-2.5 bg-indigo-600 text-white rounded-full text-xs font-black flex items-center justify-center shadow-md shadow-indigo-100" x-text="selectedIds.length"></span>
                                <span class="text-xs font-bold text-slate-700">selected</span>
                            </div>
                            <button type="button" @click="selectedIds = []; selectAll = false"
                                class="text-xs font-bold text-rose-600 hover:text-rose-700 underline underline-offset-4 decoration-2">
                                Clear Selection
                            </button>
                        </div>
 
                        <!-- Desktop Selection Text (hidden on mobile) -->
                        <span class="campaign-desktop-only text-xs font-semibold text-slate-500 mr-2 whitespace-nowrap">
                            <span x-text="selectedIds.length"></span> selected:
                        </span>
 
                        <div class="campaign-bulk-bar-buttons flex items-center gap-2">
                            @if(auth()->user()->isAdmin() || auth()->user()->can('campaign-send'))
                            <button type="button" @click="$dispatch('open-messaging-modal', { 
                                type: 'whatsapp', 
                                leadId: selectedIds[0], 
                                leadName: leadData[selectedIds[0]].name + (selectedIds.length > 1 ? ' + ' + (selectedIds.length - 1) + ' others' : ''), 
                                leadMobile: leadData[selectedIds[0]].mobile,
                                bulkIds: selectedIds,
                                campaignRoute: '{{ route('campaign-leads.send-campaign') }}',
                                allContactsRoute: '{{ route('campaign-leads.all-contacts') }}',
                                filteredContactsRoute: '{{ route('campaign-leads.filtered-contacts') }}',
                                isFilteredCampaign: {{ request()->anyFilled(['search', 'rate', 'duplicate']) ? 'true' : 'false' }},
                                filters: {
                                    search: '{{ addslashes(request('search')) }}',
                                    rate: '{{ addslashes(request('rate')) }}',
                                    duplicate: '{{ addslashes(request('duplicate')) }}'
                                }
                            })"
                                class="inline-flex items-center justify-center gap-2 px-4 h-10 rounded-lg shadow-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all text-xs"
                                title="Mobile Marketing">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                                    </path>
                                </svg>
                                Mobile Marketing
                            </button>
                            @endif

                            @can('email-template-send')
                            <button type="button" @click="$dispatch('open-email-modal', { 
                                bulkIds: selectedIds,
                                emailCampaignRoute: '{{ route('campaign-leads.send_email_campaign') }}',
                                isFilteredCampaign: {{ request()->anyFilled(['search', 'rate', 'duplicate']) ? 'true' : 'false' }},
                                filters: {
                                    search: '{{ addslashes(request('search')) }}',
                                    rate: '{{ addslashes(request('rate')) }}',
                                    duplicate: '{{ addslashes(request('duplicate')) }}'
                                }
                            })"
                                class="inline-flex items-center justify-center gap-2 px-4 h-10 rounded-lg shadow-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all text-xs"
                                title="Send Email Marketing">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Send Email Marketing
                            </button>
                            @endcan
 
                            @can('campaign-delete')
                            <button type="button"
                                @click="if(confirm('Delete selected leads?')) { $refs.bulkDeleteForm.submit() }"
                                class="inline-flex items-center justify-center gap-2 px-4 h-10 rounded-lg shadow-sm font-bold text-white bg-rose-600 hover:bg-rose-700 transition-all text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                            <form x-ref="bulkDeleteForm" action="{{ route('campaign-leads.bulk-destroy') }}" method="POST"
                                class="hidden">
                                @csrf
                                <input type="hidden" name="ids" :value="selectedIds.join(',')">
                            </form>
                            @endcan
 
                            <!-- Desktop Clear button (hidden on mobile) -->
                            <button type="button" @click="selectedIds = []; selectAll = false"
                                class="campaign-desktop-only text-[10px] font-medium text-rose-600 hover:text-rose-700 underline underline-offset-4 ml-2">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6">
                <form action="{{ route('campaign-leads.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2 relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="block w-full h-12 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder-slate-400 text-sm"
                            placeholder="Search by name, mobile, company, or place...">
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </div>
                        <select name="rate" onchange="this.form.submit()"
                            class="block w-full h-12 pl-11 pr-10 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm appearance-none cursor-pointer">
                            <option value="">All Lead Types</option>
                            @foreach(App\Models\CampaignLead::LEAD_TYPE_OPTIONS as $option)
                                <option value="{{ $option }}" {{ request('rate') == $option ? 'selected' : '' }}>{{ $option }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <select name="duplicate" onchange="this.form.submit()"
                            class="block w-full h-12 pl-11 pr-10 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all text-sm appearance-none cursor-pointer">
                            <option value="">All Leads</option>
                            <option value="converted" {{ request('duplicate') == 'converted' ? 'selected' : '' }}>Converted to CRM Lead</option>
                            <option value="not_converted" {{ request('duplicate') == 'not_converted' ? 'selected' : '' }}>Not Converted</option>
                            <option value="enquiry_duplicate" {{ request('duplicate') == 'enquiry_duplicate' ? 'selected' : '' }}>Enquiry Duplicate</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex gap-2 md:col-span-4" style="grid-column: 1 / -1;">
                        <button type="submit"
                            class="flex-1 h-12 bg-slate-800 text-white rounded-lg font-bold text-sm hover:bg-slate-900 transition-all shadow-sm">Apply
                            Filter</button>
                        <a href="{{ route('campaign-leads.index') }}"
                            class="px-4 h-12 flex items-center justify-center bg-slate-100 text-slate-600 rounded-lg font-bold text-sm hover:bg-slate-200 transition-all border border-slate-200"
                            title="Reset Filters">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Top Pagination & Scrollbar -->
            <div x-data="topScrollHandler()" x-init="init()" class="flex flex-col gap-2 mt-6">
                @if($leads->hasPages())
                    <div class="bg-slate-50/50 px-6 py-3 border border-slate-200 rounded-xl shadow-sm">
                        {{ $leads->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif

                @include('partials.top-scrollbar')

                <!-- Table -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div x-ref="contentContainer" @scroll="sync($el, $refs.topScrollbar)" class="overflow-x-auto">
                        <table x-ref="mainTable" class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 w-10">
                                    <input type="checkbox" x-model="selectAll" @change="toggleAll"
                                        class="rounded border-slate-300 text-indigo-600">
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-20">
                                    ID</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Lead Info</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Reference</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Mobiles</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Details</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Lead Type</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($leads as $lead)
                                <tr @click="window.location='{{ route('campaign-leads.show', $lead->id) }}'"
                                    class="hover:bg-slate-50 transition-colors cursor-pointer group">
                                    <td class="px-6 py-4" @click.stop>
                                        <input type="checkbox" value="{{ $lead->id }}" x-model="selectedIds"
                                            class="lead-checkbox rounded border-slate-300 text-indigo-600">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" @click.stop>
                                        <div class="flex flex-col items-center w-max gap-2">
                                        <div class="flex items-center gap-2 w-full justify-center">
                                            <span
                                                class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-mono text-xs font-semibold border border-slate-200">
                                                #{{ $lead->id }}
                                            </span>
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('whatsapp-icon'))
                                                @if($lead->mobile)
                                                    @php
                                                        $waMobile = preg_replace('/[^0-9]/', '', $lead->mobile);
                                                        if(strlen($waMobile) == 10) $waMobile = '91' . $waMobile;
                                                    @endphp
                                                    <a href="https://wa.me/{{ $waMobile }}" target="_blank" class="hover:opacity-80 transition-opacity" title="Chat on WhatsApp">
                                                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12.012 2C6.506 2 2.023 6.478 2.022 11.984C2.022 13.734 2.478 15.422 3.356 16.92L2 22L7.233 20.763C8.683 21.545 10.323 21.968 12.008 21.968H12.012C17.518 21.968 22.001 17.49 22.002 11.984C22.002 6.48 17.523 2 12.012 2Z" fill="#25D366"/>
                                                            <path d="M17.472 14.382C17.175 14.233 15.714 13.515 15.442 13.415C15.169 13.316 14.971 13.267 14.772 13.565C14.575 13.862 14.005 14.531 13.832 14.729C13.659 14.928 13.485 14.952 13.188 14.804C12.891 14.654 11.933 14.341 10.798 13.329C10.003 12.621 9.406 11.648 9.233 11.35C9.06 11.053 9.215 10.892 9.363 10.744C9.497 10.611 9.661 10.397 9.809 10.224C9.958 10.05 10.007 9.926 10.107 9.727C10.206 9.529 10.157 9.356 10.082 9.207C10.007 9.058 9.413 7.595 9.166 7.001C8.924 6.422 8.679 6.5 8.497 6.49C8.324 6.49 8.102 6.49 7.83 6.49C7.558 6.49 7.114 6.589 6.842 6.887C6.57 7.184 5.802 7.903 5.802 9.366C5.802 10.828 6.867 12.241 7.015 12.44C7.164 12.638 9.111 15.64 12.092 16.927C12.801 17.233 13.354 17.416 13.786 17.552C14.498 17.779 15.146 17.747 15.657 17.67C16.228 17.585 17.415 16.951 17.663 16.257C17.911 15.563 17.911 14.968 17.836 14.844C17.762 14.72 17.564 14.646 17.266 14.497V14.382Z" fill="white"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                            <div class="flex items-center gap-2"
                                                 x-data="{ 
                                                    flagLevel: {{ $lead->blacklist_flag ?? 0 }},
                                                    loading: false,
                                                    updateFlag(level) {
                                                        if(this.loading) return;
                                                        
                                                        let newLevel = level;
                                                        if (this.flagLevel === level) {
                                                            if (level === 1) {
                                                                newLevel = 0; // Click 1st flag again to completely clear
                                                            } else {
                                                                return; // Click 2nd or 3rd again does nothing
                                                            }
                                                        }

                                                        this.loading = true;
                                                        fetch('{{ route('campaign-leads.blacklist_flag', $lead->id) }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                            },
                                                            body: JSON.stringify({ blacklist_flag: newLevel })
                                                        })
                                                        .then(res => res.json())
                                                        .then(data => {
                                                            if(data.success) {
                                                                this.flagLevel = data.blacklist_flag;
                                                            }
                                                        })
                                                        .finally(() => {
                                                            this.loading = false;
                                                        });
                                                    }
                                                 }">
                                                 <!-- Flag 1 -->
                                                 <button type="button" @click.stop="updateFlag(1)" :disabled="loading"
                                                         class="focus:outline-none transition-colors"
                                                         :class="flagLevel >= 1 ? 'text-red-600' : 'text-slate-300 hover:text-slate-400'"
                                                         title="Level 1 Blacklist">
                                                     <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                         <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
                                                     </svg>
                                                 </button>
                                                 <!-- Flag 2 -->
                                                 <button type="button" @click.stop="updateFlag(2)" :disabled="loading"
                                                         class="focus:outline-none transition-colors"
                                                         :class="flagLevel >= 2 ? 'text-red-600' : 'text-slate-300 hover:text-slate-400'"
                                                         title="Level 2 Blacklist">
                                                     <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                         <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
                                                     </svg>
                                                 </button>
                                                 <!-- Flag 3 -->
                                                 <button type="button" @click.stop="updateFlag(3)" :disabled="loading"
                                                         class="focus:outline-none transition-colors"
                                                         :class="flagLevel >= 3 ? 'text-red-600' : 'text-slate-300 hover:text-slate-400'"
                                                         title="User Blacklisted">
                                                     <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                         <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
                                                     </svg>
                                                 </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 min-w-[150px] max-w-[200px] whitespace-normal break-words">
                                        <div class="text-sm font-bold text-slate-900">{{ $lead->customer_name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-slate-500 truncate" title="{{ $lead->email_id ?? 'No Email' }}">
                                            @can('enquiry-vendor-contact-view')
                                                {{ $lead->email_id ?? 'No Email' }}
                                            @else
                                                ********
                                            @endcan
                                        </div>
                                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $lead->source === 'Website' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : ($lead->source === 'CRM' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }} uppercase tracking-wider">
                                                Source : {{ $lead->source ?? 'CRM' }}
                                            </span>
                                            @if($lead->crm_duplicate)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 uppercase tracking-tighter">
                                                    {{ $lead->crm_duplicate }}
                                                </span>
                                            @endif
                                            @if($lead->campaign_duplicate)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-tighter">
                                                    {{ $lead->campaign_duplicate }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-700">
                                        {{ $lead->reference ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            @can('enquiry-vendor-contact-view')
                                                <div class="text-xs font-medium text-slate-700">M: {{ $lead->mobile ?? 'N/A' }}
                                                </div>
                                                @if($lead->mobile_1)
                                                    <div class="text-[11px] text-slate-500">M1: {{ $lead->mobile_1 }}</div>
                                                @endif
                                                @if($lead->mobile_2)
                                                    <div class="text-[11px] text-slate-500">M2: {{ $lead->mobile_2 }}</div>
                                                @endif
                                            @else
                                                <div class="text-xs font-medium text-slate-700">M: ********</div>
                                                @if($lead->mobile_1)
                                                    <div class="text-[11px] text-slate-500">M1: ********</div>
                                                @endif
                                                @if($lead->mobile_2)
                                                    <div class="text-[11px] text-slate-500">M2: ********</div>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                        <div class="font-medium text-slate-800">{{ $lead->company_name ?? 'No Company' }}
                                        </div>
                                        <div>{{ $lead->place ?? 'No Place' }}</div>
                                        @if($lead->address)
                                            <div class="text-[10px] text-slate-500 italic max-w-[200px] truncate"
                                                title="{{ $lead->address }}">{{ $lead->address }}</div>
                                        @endif
                                        <div class="text-indigo-600">{{ $lead->product_interested }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $lead->rate ?: 'General' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                                        <div class="flex items-center justify-end gap-2">
                                            <div x-data="{ actionOpen: false }" class="relative" :class="actionOpen ? 'z-50' : 'z-0'" @click.stop>
                                                <button @click="actionOpen = !actionOpen" @click.away="actionOpen = false" type="button" class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-300 rounded-md bg-white text-slate-700 hover:bg-slate-50 transition-colors text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    Actions
                                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>

                                                <div x-show="actionOpen" x-transition style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-slate-200 z-[60] overflow-hidden text-left">
                                                    <div class="py-1">
                                                        <!-- View Button -->
                                                        <a href="{{ route('campaign-leads.show', $lead->id) }}"
                                                            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                                            View
                                                        </a>

                                                        <!-- Edit Button -->
                                                        @can('campaign-edit')
                                                        <a href="{{ route('campaign-leads.edit', $lead->id) }}"
                                                            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                                            Edit
                                                        </a>
                                                        @endcan

                                                        <!-- Campaign Button -->
                                                        @if(auth()->user()->isAdmin() || auth()->user()->can('campaign-send'))
                                                        <button type="button" @click="$dispatch('open-messaging-modal', { 
                                                                type: 'whatsapp', 
                                                                leadId: '{{ $lead->id }}', 
                                                                leadName: '{{ addslashes($lead->customer_name) }}', 
                                                                leadMobile: '{{ $lead->mobile }}',
                                                                campaignRoute: '{{ route('campaign-leads.send-campaign') }}',
                                                                allContactsRoute: '{{ route('campaign-leads.all-contacts') }}',
                                                                filteredContactsRoute: '{{ route('campaign-leads.filtered-contacts') }}',
                                                                isFilteredCampaign: {{ request()->anyFilled(['search', 'rate']) ? 'true' : 'false' }},
                                                                filters: {
                                                                    search: '{{ addslashes(request('search')) }}',
                                                                    rate: '{{ addslashes(request('rate')) }}'
                                                                }
                                                            })"
                                                            class="w-full text-left block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors">
                                                            Mobile Marketing
                                                        </button>

                                                        <!-- Send Email Marketing -->
                                                        @can('email-template-send')
                                                        <button type="button" @click="$dispatch('open-email-modal', { 
                                                                bulkIds: ['{{ $lead->id }}'],
                                                                isFilteredCampaign: {{ request()->anyFilled(['search', 'rate', 'duplicate']) ? 'true' : 'false' }},
                                                                filters: {
                                                                    search: '{{ addslashes(request('search')) }}',
                                                                    rate: '{{ addslashes(request('rate')) }}'
                                                                }
                                                            })"
                                                            class="w-full text-left block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                                            Send Email Marketing
                                                        </button>
                                                        @endcan
                                                        @endif

                                                        <!-- Delete Button -->
                                                        @can('campaign-delete')
                                                        <form action="{{ route('campaign-leads.destroy', $lead->id) }}" method="POST"
                                                            onsubmit="return confirm('Are you sure you want to delete this lead?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="w-full text-left block px-4 py-2 text-sm text-rose-600 hover:bg-slate-50 transition-colors font-medium">
                                                                Delete
                                                            </button>
                                                        </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">No campaign leads found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($leads->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $leads->links('partials.pagination') }}
                    </div>
                @endif
            </div>
            </div>

            <!-- Import Modal -->
            <div x-show="importModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                style="display: none;">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8" @click.away="importModalOpen = false">
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Import Campaign Leads</h3>
                    <p class="text-slate-600 mb-6">Upload a CSV file with the columns: Name, Mobile, Mobile 1, Mobile 2,
                        Email, Company Name, Type of Firm, Place, Product Interested, Comment, Lead Type.</p>

                    <a href="{{ route('campaign-leads.sample-csv') }}"
                        class="inline-flex items-center gap-2 text-indigo-600 font-semibold mb-8 hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Download Sample CSV
                    </a>

                    <form action="{{ route('campaign-leads.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Select CSV File</label>
                            <input type="file" name="csv_file" accept=".csv" required
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="importModalOpen = false"
                                class="px-6 py-2 rounded-lg border border-slate-300 font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Cancel</button>
                            <button type="submit"
                                class="px-6 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200">Import
                                Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection