@extends('layouts.app')
@section('header', $title)

@section('content')
<style>
    /* Desktop default layout for header actions & bulk actions */
    .leads-header-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.75rem;
    }
    
    .leads-actions-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .leads-bulk-bar {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .leads-mobile-only {
        display: none !important;
    }

    .leads-desktop-only {
        display: inline-block !important;
    }

    /* Mobile media query overrides */
    @media (max-width: 639px) {
        .leads-header-right {
            width: 100% !important;
            align-items: stretch !important;
            gap: 0.75rem !important;
        }

        .leads-actions-row {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }

        .leads-actions-row button,
        .leads-actions-row .relative {
            grid-column: span 1 / span 1 !important;
            width: 100% !important;
        }

        .leads-actions-row a {
            grid-column: span 2 / span 2 !important;
            width: 100% !important;
        }

        .leads-bulk-bar {
            width: 100% !important;
            background-color: #f8fafc !important; /* bg-slate-50 */
            border: 1px solid #e2e8f0 !important; /* border-slate-200 */
            border-radius: 0.75rem !important; /* rounded-xl */
            padding: 0.875rem !important; /* p-3.5 */
            flex-direction: column !important; /* flex-col */
            align-items: stretch !important;
            gap: 0.75rem !important;
        }

        .leads-bulk-bar-buttons {
            flex-direction: column !important;
            align-items: stretch !important;
            width: 100% !important;
            gap: 0.5rem !important;
        }

        .leads-bulk-bar-buttons button {
            width: 100% !important;
        }

        .leads-mobile-only {
            display: flex !important;
        }

        .leads-desktop-only {
            display: none !important;
        }
    }
</style>
<div class="relative">
    <!-- Main Content Wrapper -->
    @php
        $vendorType = ($title === 'Vendor KYC Leads') ? 'kyc' : 'po';
    @endphp
    <div x-data="{
                                                 selectedLids: [],
                                                 leadData: { 
                                                     @foreach($leads as $lead)
                                                         '{{ $lead->id }}': { id: '{{ $lead->id }}', name: '{{ addslashes($lead->customer->client_name ?? $lead->customer_name) }}', mobile: '{{ $lead->mobile }}' },
                                                     @endforeach
                                                 },
                                                 init() { window.leadData = this.leadData; },
                                                 selectAll: false,
                                                 importModalOpen: false,
                                                 toggleAll() {
                                                     if (this.selectAll) {
                                                         this.selectedLids = Array.from(document.querySelectorAll('.lead-checkbox')).map(cb => cb.value);
                                                     } else {
                                                         this.selectedLids = [];
                                                     }
                                                 },
                                                 exportSelected() {
                                                     if (this.selectedLids.length === 0) {
                                                         alert('Please select at least one lead to export.');
                                                         return;
                                                     }
                                                     window.location.href = '{{ route('leads.export') }}?type=selected&ids=' + this.selectedLids.join(',');
                                                 }
                                             }" class="flex flex-col h-full transition duration-200">
        <!-- Page Header & Actions -->
        <div class="flex items-start justify-between gap-4 mb-6" style="flex-wrap: wrap;">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-slate-500 mt-1">Viewing leads received from external API integrations.</p>
            </div>

            <div class="leads-header-right w-full sm:w-auto">
                <div class="leads-actions-row w-full sm:w-auto">
                    <!-- Import Trigger -->
                    <button type="button" @click="importModalOpen = true"
                        class="inline-flex items-center justify-center gap-2 px-4 h-12 border border-slate-300 rounded-lg shadow-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Import
                    </button>

                    <!-- Export Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="inline-flex items-center justify-center gap-2 px-4 h-12 border border-slate-300 rounded-lg shadow-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors whitespace-nowrap w-full">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Export
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <div x-show="open" x-transition style="display: none;"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-50 overflow-hidden">
                            <div class="py-1">
                                <a href="{{ route('leads.export') }}?type=all"
                                    class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                                    Export All Leads
                                </a>
                                <a href="{{ route('leads.export', array_merge(request()->query(), ['type' => 'filtered'])) }}"
                                    class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                                    Export Filtered
                                </a>
                                <button @click="exportSelected" type="button"
                                    class="w-full text-left block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                                    Export Selected
                                </button>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('leads.create') }}"
                        class="inline-flex items-center justify-center px-6 h-12 border border-transparent rounded-lg shadow-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors whitespace-nowrap">
                        Add Lead
                    </a>
                </div>

                <!-- Bulk Action Bar -->
                <div x-show="selectedLids.length > 0" x-transition
                    class="leads-bulk-bar animate-in fade-in slide-in-from-top-2 duration-200">
                    
                    <!-- Mobile Selection Info/Clear Bar (hidden on desktop) -->
                    <div class="leads-mobile-only w-full items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-6 px-2.5 bg-indigo-600 text-white rounded-full text-xs font-black flex items-center justify-center shadow-md shadow-indigo-100" x-text="selectedLids.length"></span>
                            <span class="text-xs font-bold text-slate-700">selected</span>
                        </div>
                        <button type="button" @click="selectedLids = []; selectAll = false"
                            class="text-xs font-bold text-rose-600 hover:text-rose-700 underline underline-offset-4 decoration-2">
                            Clear Selection
                        </button>
                    </div>

                    <!-- Desktop Selection Text (hidden on mobile) -->
                    <span class="leads-desktop-only text-xs font-semibold text-slate-500 mr-2 whitespace-nowrap">
                        <span x-text="selectedLids.length"></span> selected:
                    </span>

                    <div class="leads-bulk-bar-buttons flex items-center gap-2">
                        <!-- Mobile Marketing -->
                        @if(auth()->user()->isAdmin() || auth()->user()->can('campaign-send'))
                            <button type="button" @click="$dispatch('open-messaging-modal', { 
                                                type: 'whatsapp', 
                                                leadId: selectedLids[0], 
                                                leadName: leadData[selectedLids[0]].name + (selectedLids.length > 1 ? ' + ' + (selectedLids.length - 1) + ' others' : ''), 
                                                leadMobile: leadData[selectedLids[0]].mobile,
                                                bulkIds: selectedLids,
                                                campaignRoute: '{{ route("messaging.whatsapp.send") }}',
                                                allContactsRoute: '{{ route("vendor_leads.all_contacts", ["type" => $vendorType]) }}',
                                                filteredContactsRoute: '{{ route("vendor_leads.filtered_contacts", ["type" => $vendorType]) }}',
                                                isFilteredCampaign: {{ request()->hasAny(['search', 'lead_status', 'customer_type', 'industry', 'city', 'assigned_user', 'kyc', 'product', 'date_from', 'date_to']) ? 'true' : 'false' }},
                                                filters: {
                                                    search: '{{ addslashes(request('search')) }}',
                                                    lead_status: '{{ addslashes(request('lead_status')) }}',
                                                    customer_type: '{{ addslashes(request('customer_type')) }}',
                                                    industry: '{{ addslashes(request('industry')) }}',
                                                    city: '{{ addslashes(request('city')) }}',
                                                    assigned_user: '{{ addslashes(request('assigned_user')) }}',
                                                    kyc: '{{ addslashes(request('kyc')) }}',
                                                    product: '{{ addslashes(request('product')) }}',
                                                    date_from: '{{ addslashes(request('date_from')) }}',
                                                    date_to: '{{ addslashes(request('date_to')) }}'
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

                        <!-- Send Email Marketing -->
                        @can('email-template-send')
                            <button type="button" @click="$dispatch('open-email-modal', { 
                                                bulkIds: selectedLids,
                                                emailCampaignRoute: '{{ route("vendor_leads.send_email_campaign", ["type" => $vendorType]) }}',
                                                isFilteredCampaign: {{ request()->hasAny(['search', 'lead_status', 'customer_type', 'industry', 'city', 'assigned_user', 'kyc', 'product', 'date_from', 'date_to']) ? 'true' : 'false' }},
                                                filters: {
                                                    search: '{{ addslashes(request('search')) }}',
                                                    lead_status: '{{ addslashes(request('lead_status')) }}',
                                                    customer_type: '{{ addslashes(request('customer_type')) }}',
                                                    industry: '{{ addslashes(request('industry')) }}',
                                                    city: '{{ addslashes(request('city')) }}',
                                                    assigned_user: '{{ addslashes(request('assigned_user')) }}',
                                                    kyc: '{{ addslashes(request('kyc')) }}',
                                                    product: '{{ addslashes(request('product')) }}',
                                                    date_from: '{{ addslashes(request('date_from')) }}',
                                                    date_to: '{{ addslashes(request('date_to')) }}'
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

                        <!-- Bulk Delete Action -->
                        @can('lead-delete')
                            <button type="button" @click="if(confirm('Are you sure you want to delete ' + selectedLids.length + ' selected leads? This action cannot be undone.')) { $refs.bulkDeleteForm.submit() }"
                                class="inline-flex items-center justify-center gap-2 px-4 h-10 rounded-lg shadow-sm font-bold text-white bg-rose-600 hover:bg-rose-700 transition-all text-xs"
                                title="Delete Selected">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                            <form x-ref="bulkDeleteForm" action="{{ route('leads.bulk_destroy') }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="ids" :value="selectedLids.join(',')">
                            </form>
                        @endcan

                        <!-- Desktop Clear button (hidden on mobile) -->
                        <button type="button" @click="selectedLids = []; selectAll = false"
                            class="leads-desktop-only text-[10px] font-medium text-rose-600 hover:text-rose-700 underline underline-offset-4 ml-2">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6 mt-6">
            <form action="{{ url()->current() }}" method="GET" class="flex flex-col gap-4 mt-2">

                <!-- Top Row: Search & Clear -->
                <div class="flex w-full">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" onchange="this.form.submit()"
                            class="block w-full h-12 pl-11 pr-4 bg-white border border-slate-300 rounded-l-lg border-r-0 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Search...">
                    </div>
                    <a href="{{ url()->current() }}"
                        class="h-12 inline-flex items-center justify-center px-8 border border-slate-300 rounded-r-lg font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 transition-colors shrink-0 m-0 border-l-0">
                        Clear
                    </a>
                    <!-- Hidden submit button to allow Enter key to work in text fields -->
                    <button type="submit" class="hidden">Filter</button>
                </div>

                <!-- Bottom Row: 3 Dropdowns -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <select name="lead_status" onchange="this.form.submit()"
                            class="block w-full h-12 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">All Statuses</option>
                            <option value="Active" {{ request('lead_status') == 'Active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="Non Active" {{ request('lead_status') == 'Non Active' ? 'selected' : '' }}>Non
                                Active</option>
                        </select>
                    </div>

                    <div>
                        <select name="customer_type" onchange="this.form.submit()"
                            class="block w-full h-12 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">All Types</option>
                            <option value="Enquiry" {{ request('customer_type') == 'Enquiry' ? 'selected' : '' }}>Enquiry
                            </option>
                            <option value="1st Time" {{ request('customer_type') == '1st Time' ? 'selected' : '' }}>1st
                                Time
                            </option>
                            <option value="Loyal" {{ request('customer_type') == 'Loyal' ? 'selected' : '' }}>Loyal
                            </option>
                            <option value="Premium" {{ request('customer_type') == 'Premium' ? 'selected' : '' }}>Premium
                            </option>
                            <option value="Discount/Bargain Hunter" {{ request('customer_type') == 'Discount/Bargain Hunter' ? 'selected' : '' }}>Discount</option>
                            <option value="Need Base" {{ request('customer_type') == 'Need Base' ? 'selected' : '' }}>Need
                                Base</option>
                            <option value="Unqualified" {{ request('customer_type') == 'Unqualified' ? 'selected' : '' }}>
                                Unqualified</option>
                        </select>
                    </div>

                    <div>
                        <select name="industry" onchange="this.form.submit()"
                            class="block w-full h-12 border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            <option value="">All Industries</option>
                            <option value="PL" {{ request('industry') == 'PL' ? 'selected' : '' }}>PL</option>
                            <option value="BL" {{ request('industry') == 'BL' ? 'selected' : '' }}>BL</option>
                            <option value="HL" {{ request('industry') == 'HL' ? 'selected' : '' }}>HL</option>
                            <option value="Real Estate" {{ request('industry') == 'Real Estate' ? 'selected' : '' }}>Real
                                Estate</option>
                            <option value="Education" {{ request('industry') == 'Education' ? 'selected' : '' }}>Education
                            </option>
                            <option value="NGO" {{ request('industry') == 'NGO' ? 'selected' : '' }}>NGO</option>
                            <option value="Insurance" {{ request('industry') == 'Insurance' ? 'selected' : '' }}>Insurance
                            </option>
                        </select>
                    </div>
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

            <!-- Table Container -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex-1">
                <div x-ref="contentContainer" @scroll="sync($el, $refs.topScrollbar)" class="overflow-x-auto h-full">
                    <table x-ref="mainTable" class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/80 backdrop-blur-sm sticky top-0 z-10">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-10">
                                    <input type="checkbox" x-model="selectAll" @change="toggleAll"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                </th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Record ID</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Lead Information</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Status & KYC</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Interest & Date</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Contact</th>
                                <th scope="col" class="relative px-6 py-4">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($leads as $lead)
                                <tr @click="window.location='{{ route('leads.show', $lead->id) }}'"
                                    class="hover:bg-slate-50/80 transition-colors group cursor-pointer"
                                    style="cursor: pointer;">
                                    <td class="px-6 py-4" @click.stop>
                                        <input type="checkbox" value="{{ $lead->id }}" x-model="selectedLids"
                                            class="lead-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" @click.stop>
                                        <div class="flex flex-col items-center w-max gap-2">
                                            <div class="flex items-center gap-2 w-full justify-center">
                                            <span
                                                class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-mono text-xs font-semibold border border-slate-200">
                                                #{{ $lead->record_id }}
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
                                                        fetch('{{ route('leads.blacklist_flag', $lead->id) }}', {
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $lead->customer->client_name ?? $lead->customer_name ?? 'Unknown Customer' }}
                                        </div>


                                        <div class="text-xs text-slate-600">
                                            company: {{ $lead->company_name ?? 'No Company' }}
                                        </div>
                                        <div class="mt-1.5 flex flex-col gap-1.5">
                                            <span class="text-xs text-slate-500 font-medium">Type:
                                                {{ $lead->customer_type ?: 'Enquiry' }}</span>
                                            <span class="text-[10px] text-slate-400">Source:
                                                @php
                                                    $sourceMap = [
                                                        'CRM' => 'CRM',
                                                        'CLIENT P.O' => 'Client P.O',
                                                        'CLIENT MSA' => 'Client KYC',
                                                        'CLIENT TERMS' => 'T.C',
                                                        'VENDOR P.O (ADMIN)' => 'Vendor P.O',
                                                        'VENDOR PO API' => 'Vendor P.O',
                                                        'VENDOR KYC API' => 'Vendor KYC',
                                                        'VENDOR REGISTRATION' => 'Vendor Registration',
                                                    ];
                                                    echo $sourceMap[$lead->creation_source] ?? ($lead->creation_source ?: 'Manual');
                                                @endphp
                                            </span>
                                            <span class="text-xs text-blue-600 font-bold uppercase tracking-tight">
                                                {{ $title === 'Vendor KYC Leads' ? 'Vendor ID' : 'Cust. ID' }}: #{{ $lead->customer_id }}
                                            </span>
                                            @php
                                                $originalLead = \App\Models\Lead::where('id', '<', $lead->id)
                                                    ->whereIn('creation_source', ['VENDOR KYC API', 'VENDOR KYC', 'VENDOR REGISTRATION'])
                                                    ->where(function($q) use ($lead) {
                                                        if($lead->email_id) $q->where('email_id', $lead->email_id);
                                                        if($lead->customer_id) $q->orWhere('customer_id', $lead->customer_id);
                                                    })->orderBy('id', 'asc')->first();
                                            @endphp
                                            @if($originalLead)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium w-max bg-rose-100 text-rose-800">
                                                    Duplicate of #{{ $originalLead->record_id }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium w-max {{ $lead->lead_status === 'Active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                                {{ $lead->lead_status }}
                                            </span>
                                            <span
                                                class="text-xs {{ $lead->kyc === 'Done' ? 'text-emerald-600 font-semibold' : 'text-slate-400' }} flex items-center gap-1.5">
                                                @if ($lead->kyc === 'Done')
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                            d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    KYC Done
                                                @else
                                                    <div class="h-1.5 w-1.5 rounded-full bg-slate-300"></div>
                                                    KYC Pending
                                                @endif
                                            </span>
                                            <span
                                                class="text-xs {{ $lead->master_service_agreement_signed ? 'text-emerald-600 font-semibold' : 'text-slate-400' }} flex items-center gap-1.5">
                                                @if ($lead->master_service_agreement_signed)
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                            d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    MSA Signed
                                                @else
                                                    <div class="h-1.5 w-1.5 rounded-full bg-slate-300"></div>
                                                    MSA Pending
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-700 font-medium truncate max-w-[150px]"
                                            title="{{ $lead->initial_product_interest }}">
                                            {{ $lead->initial_product_interest ?? 'No Product Spec' }}
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Follow Up:
                                            {{ $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('M d, Y') : 'Not Set' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        <div class="flex flex-col gap-1">
                                            <span class="flex items-center gap-1.5">
                                                @can('enquiry-vendor-contact-view')
                                                    {{ $lead->mobile ?? 'N/A' }}
                                                @else
                                                    ********
                                                @endcan
                                            </span>
                                            <span class="flex items-center gap-1.5 text-slate-500"
                                                title="{{ $lead->email_id }}">
                                                @can('enquiry-vendor-contact-view')
                                                    {{ \Illuminate\Support\Str::limit($lead->email_id ?? 'N/A', 15) }}
                                                @else
                                                    ********
                                                @endcan
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                                        <div class="flex items-center justify-end gap-3">
                                            @if($lead->creation_source === 'VENDOR KYC API' || $lead->creation_source === 'VENDOR KYC' || $lead->creation_source === 'VENDOR REGISTRATION')
                                                @if($lead->is_agreement_sent)
                                                    <span class="text-emerald-700 bg-emerald-100 px-2 py-1 rounded-md text-xs font-bold flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Sent Agreement
                                                    </span>
                                                @else
                                                    <form action="{{ route('vendor_leads.send_kyc_agreement', $lead->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 px-2 py-1 rounded-md hover:bg-indigo-100 transition-colors flex items-center gap-1.5"
                                                            onclick="return confirm('Send KYC Agreement to {{ $lead->email_id }}?')">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                            </svg>
                                                            Send Agreement
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            <div x-data="{ actionOpen: false, showDeleteModal: false }" class="relative" :class="actionOpen ? 'z-50' : 'z-0'" @click.stop>
                                                <button @click="actionOpen = !actionOpen" @click.away="actionOpen = false" type="button" class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-300 rounded-md bg-white text-slate-700 hover:bg-slate-50 transition-colors text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    Actions
                                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>

                                                <div x-show="actionOpen" x-transition style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-slate-200 z-[60] overflow-hidden text-left">
                                                    <div class="py-1">
                                                        @if(auth()->user()->can('vendor-po-access') && $lead->isVendor())
                                                            <a href="{{ route('manage_po.vendor_po', ['lead_id' => $lead->id]) }}"
                                                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors">
                                                                PO
                                                            </a>
                                                        @endif

                                                        <a href="{{ route('leads.show', $lead->id) }}"
                                                            class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                                            View
                                                        </a>

                                                        @can('lead-edit')
                                                            <a href="{{ route('leads.edit', $lead->id) }}"
                                                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                                                Edit
                                                            </a>
                                                        @endcan

                                                        @if(auth()->user()->isAdmin() || auth()->user()->can('campaign-send'))
                                                            <button type="button" @click="$dispatch('open-messaging-modal', { 
                                                                    type: 'whatsapp', 
                                                                    leadId: '{{ $lead->id }}', 
                                                                    leadName: '{{ addslashes($lead->customer->client_name ?? $lead->customer_name) }}', 
                                                                    leadMobile: '{{ $lead->mobile }}',
                                                                    campaignRoute: '{{ route("messaging.whatsapp.send") }}',
                                                                    allContactsRoute: '{{ route("vendor_leads.all_contacts", ["type" => $vendorType]) }}',
                                                                    filteredContactsRoute: '{{ route("vendor_leads.filtered_contacts", ["type" => $vendorType]) }}',
                                                                    isFilteredCampaign: {{ request()->hasAny(['search', 'lead_status', 'customer_type', 'industry', 'city', 'assigned_user', 'kyc', 'product', 'date_from', 'date_to']) ? 'true' : 'false' }},
                                                                    filters: {
                                                                        search: '{{ addslashes(request('search')) }}',
                                                                        lead_status: '{{ addslashes(request('lead_status')) }}',
                                                                        customer_type: '{{ addslashes(request('customer_type')) }}',
                                                                        industry: '{{ addslashes(request('industry')) }}',
                                                                        city: '{{ addslashes(request('city')) }}',
                                                                        assigned_user: '{{ addslashes(request('assigned_user')) }}',
                                                                        kyc: '{{ addslashes(request('kyc')) }}',
                                                                        product: '{{ addslashes(request('product')) }}',
                                                                        date_from: '{{ addslashes(request('date_from')) }}',
                                                                        date_to: '{{ addslashes(request('date_to')) }}'
                                                                    }
                                                                })"
                                                                class="w-full text-left block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors">
                                                                Mobile Marketing
                                                            </button>
                                                        @endif

                                                        @can('email-template-send')
                                                            <button type="button" @click="$dispatch('open-email-modal', { 
                                                                    bulkIds: ['{{ $lead->id }}'],
                                                                    emailCampaignRoute: '{{ route("vendor_leads.send_email_campaign", ["type" => $vendorType]) }}',
                                                                    isFilteredCampaign: {{ request()->hasAny(['search', 'lead_status', 'customer_type', 'industry', 'city', 'assigned_user', 'kyc', 'product', 'date_from', 'date_to']) ? 'true' : 'false' }},
                                                                    filters: {
                                                                        search: '{{ addslashes(request('search')) }}',
                                                                        lead_status: '{{ addslashes(request('lead_status')) }}',
                                                                        customer_type: '{{ addslashes(request('customer_type')) }}',
                                                                        industry: '{{ addslashes(request('industry')) }}',
                                                                        city: '{{ addslashes(request('city')) }}',
                                                                        assigned_user: '{{ addslashes(request('assigned_user')) }}',
                                                                        kyc: '{{ addslashes(request('kyc')) }}',
                                                                        product: '{{ addslashes(request('product')) }}',
                                                                        date_from: '{{ addslashes(request('date_from')) }}',
                                                                        date_to: '{{ addslashes(request('date_to')) }}'
                                                                    }
                                                                })"
                                                                class="w-full text-left block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                                                                Send Email Marketing
                                                            </button>
                                                        @endcan

                                                        @if(auth()->user()->isAdmin())
                                                            <button @click="showDeleteModal = true; actionOpen = false;" type="button"
                                                                class="w-full text-left block px-4 py-2 text-sm text-rose-600 hover:bg-slate-50 transition-colors font-medium">
                                                                Delete
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Delete Modal -->
                                                @if(auth()->user()->isAdmin())
                                                    <div x-show="showDeleteModal"
                                                        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                                                        style="display: none;" x-transition>
                                                        <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center transform transition-all whitespace-normal"
                                                            @click.away="showDeleteModal = false">
                                                            <div class="mb-4">
                                                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                                                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                    </svg>
                                                                </div>
                                                                <h3 class="text-lg font-bold text-slate-800">Delete Lead</h3>
                                                                <p class="text-sm text-slate-600 mt-2">
                                                                    Are you sure you want to delete lead
                                                                    <strong>#{{ $lead->record_id }}</strong>? Documents will be
                                                                    removed. This action cannot be undone.
                                                                </p>
                                                            </div>
                                                            <div class="flex justify-center gap-3 w-full mt-6">
                                                                <button @click="showDeleteModal = false" type="button"
                                                                    class="w-full justify-center px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg hover:bg-slate-50 font-medium shadow-sm">
                                                                    Cancel
                                                                </button>
                                                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST"
                                                                    class="w-full">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="w-full justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-sm transition-colors">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6">
                                        <div class="flex flex-col items-center justify-center py-12 text-center gap-3">
                                            <div
                                                class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 relative">
                                                <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <h3 class="text-sm font-semibold text-slate-900 border-none pb-0">No Leads Found
                                            </h3>
                                            <p class="text-sm text-slate-500 max-w-sm">No leads match your current criteria.</p>
                                            @can('lead-add')
                                                <div>
                                                    <a href="{{ route('leads.create') }}"
                                                        class="inline-flex items-center px-6 h-12 border border-transparent shadow-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Add Lead
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leads->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        {{ $leads->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Import Modal -->
        @can('lead-import')
        <template x-teleport="body">
            <div x-show="importModalOpen" style="display: none;" class="relative z-[99999]"
                aria-labelledby="modal-title" role="dialog" aria-modal="true">

                <!-- Background backdrop -->
                <div x-show="importModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>

                <!-- Modal positioning -->
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

                        <!-- Modal panel -->
                        <div x-show="importModalOpen" @click.stop x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-slate-200">

                            <div class="bg-white font-sans text-slate-800 p-8 flex flex-col">

                                <!-- Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-2xl font-bold text-slate-900 tracking-tight" id="modal-title">
                                        Import Leads
                                    </h3>
                                    <button type="button" @click="importModalOpen = false"
                                        class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-2 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Instructions -->
                                <div class="text-[15px] leading-relaxed text-slate-600 space-y-3 mb-8 ml-2">
                                    <ul class="list-disc pl-6 space-y-2.5 marker:text-slate-400">
                                        <li>Upload a CSV file using the sample format</li>
                                        <li>Only CSV files are supported</li>
                                        <li>
                                            <span
                                                class="font-mono text-sm font-medium bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-200">customer_name</span>
                                            is required in each row
                                        </li>
                                        <li>Optional fields can remain blank</li>
                                    </ul>
                                </div>

                                <!-- Sample Download -->
                                <div class="mb-8">
                                    <a href="{{ route('leads.sample_csv') }}"
                                        class="inline-flex items-center gap-2.5 text-sm font-semibold text-indigo-700 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-5 py-3 border border-indigo-200/60 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        Download Sample CSV
                                    </a>
                                </div>

                                <!-- Upload Form -->
                                <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-10">
                                        <label class="block text-sm font-bold text-slate-700 mb-3">Select CSV
                                            File</label>
                                        <input type="file" name="csv_file" accept=".csv" required
                                            class="block w-full text-slate-600 file:mr-5 file:py-3 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 focus:outline-none cursor-pointer border border-slate-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    </div>

                                    <!-- Footer Actions -->
                                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                                        <button type="button" @click="importModalOpen = false"
                                            class="px-5 py-2.5 text-[15px] font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200 transition-colors shadow-sm">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="px-6 py-2.5 text-[15px] font-bold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-md shadow-indigo-200">
                                            Import Leads
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        @endif
    </div>
</div>
@endsection