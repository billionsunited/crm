@extends('layouts.app')
@section('header', 'Invoices OR')
@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="{ exportModalOpen: false, zipModalOpen: false }">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Invoices OR ✨</h1>
            </div>

            <!-- Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-3">
                @can('invoice-or-export')
                <button type="button" @click="exportModalOpen = true" class="btn bg-emerald-600 hover:bg-emerald-700 text-white flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors shadow-sm cursor-pointer">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Invoices</span>
                </button>
                <button type="button" @click="zipModalOpen = true" class="btn bg-slate-800 hover:bg-slate-900 text-white flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors shadow-sm cursor-pointer">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download ZIP</span>
                </button>
                @endcan
                <a href="{{ route('or-invoices.create') }}" class="btn bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Create Invoice</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-teal-50 border border-teal-200 text-teal-800 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-medium text-sm">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6 mt-6">
            <form action="{{ route('or-invoices.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
                <!-- Search Input -->
                <div class="flex-1 w-full">
                    <label for="search" class="block text-sm font-semibold text-slate-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        placeholder="Search by Invoice #, Client or Reference..." 
                        class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] px-3 bg-white">
                </div>

                <!-- Client Filter Dropdown -->
                <div class="flex-1 w-full" x-data="{ 
                    open: false, 
                    search: '',
                    selectedName: '{{ request('client_filter') }}',
                    clients: [
                        @foreach($clients as $client)
                            { name: '{{ addslashes($client->name) }}', count: {{ $client->invoices_count }} },
                        @endforeach
                    ],
                    get filteredClients() {
                        if (this.search === '') return this.clients;
                        return this.clients.filter(c => c.name.toLowerCase().includes(this.search.toLowerCase()));
                    },
                    get selectedLabel() {
                        const client = this.clients.find(c => c.name === this.selectedName);
                        return client ? `${client.name} (${client.count})` : 'All Clients';
                    }
                }" @click.outside="open = false">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Filter by Client</label>
                    <div class="relative">
                        <input type="hidden" name="client_filter" x-model="selectedName">
                        <button type="button" @click="open = !open"
                            class="w-full text-left rounded-lg border border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] px-3 flex items-center justify-between bg-white focus:outline-none"
                            :class="selectedName ? 'border-indigo-500 ring-1 ring-indigo-500' : ''">
                            <span x-text="selectedLabel" class="truncate"></span>
                            <svg class="h-5 w-5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute z-50 mt-2 w-full bg-white rounded-xl border border-slate-200 shadow-2xl overflow-hidden" style="display: none;">
                            <div class="p-2 border-b border-slate-100 bg-slate-50">
                                <input type="text" x-model="search" placeholder="Search clients..." class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white">
                            </div>
                            <div class="max-h-60 overflow-y-auto p-1">
                                <button type="button" @click="selectedName = ''; open = false; $nextTick(() => $el.closest('form').submit())"
                                    class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 transition-all">
                                    All Clients
                                </button>
                                <template x-for="client in filteredClients" :key="client.name">
                                    <button type="button" @click="selectedName = client.name; open = false; $nextTick(() => $el.closest('form').submit())"
                                        class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 transition-all flex justify-between items-center group">
                                        <span x-text="client.name" class="truncate"></span>
                                        <span class="text-xs bg-slate-100 text-slate-500 group-hover:bg-white px-2 py-0.5 rounded-full" x-text="client.count"></span>
                                    </button>
                                </template>
                                <div x-show="filteredClients.length === 0" class="px-4 py-6 text-center text-sm text-slate-500">
                                    No clients found.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-[42px] px-6 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors flex items-center text-sm cursor-pointer">
                        Search
                    </button>
                    <a href="{{ route('or-invoices.index') }}" class="h-[42px] px-6 bg-slate-100 text-slate-700 rounded-lg font-semibold hover:bg-slate-200 transition-colors flex items-center text-sm">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white shadow-sm border border-slate-200 rounded-xl overflow-hidden">
            @if($invoices->hasPages())
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                {{ $invoices->appends(request()->query())->links('partials.pagination') }}
            </div>
            @endif
            
            <!-- Top scrollbar -->
            <div id="top-scrollbar-container" class="overflow-x-auto overflow-y-hidden w-full border-b border-slate-100" style="display: none;">
                <div id="top-scrollbar-dummy" style="height: 1px;"></div>
            </div>

            <div id="table-container" class="overflow-x-auto w-full">
                <table class="w-full text-left divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Raised Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Client / Org</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Value</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Paid Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($invoices as $invoice)
                        <tr @click="window.location='{{ route('or-invoices.show', [$invoice->id, 'page' => $invoices->currentPage()]) }}'" class="hover:bg-slate-50 transition-colors group cursor-pointer" style="cursor: pointer;">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-slate-900 border border-slate-200 bg-white shadow-sm px-2 py-1 rounded-md">
                                    @if($invoice->is_paid || $invoice->is_cancelled)
                                        {{ $invoice->invoice_number }}
                                    @else
                                        PROFORMA
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">{{ $invoice->invoice_date->format('d M, Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-semibold text-slate-800">{{ $invoice->organisation_name !== 'None' ? $invoice->organisation_name : $invoice->client_name }}</div>
                                    @php $mobileNo = $invoice->customer->mobile_no ?? null; @endphp
                                    @if(auth()->user()->isAdmin() || auth()->user()->can('whatsapp-icon'))
                                        @if($mobileNo && $mobileNo !== 'NONE')
                                            @php
                                                $waMobile = preg_replace('/[^0-9]/', '', $mobileNo);
                                                if(strlen($waMobile) == 10) $waMobile = '91' . $waMobile;
                                            @endphp
                                            <a href="https://wa.me/{{ $waMobile }}" target="_blank" class="hover:opacity-80 transition-opacity" title="Chat on WhatsApp" @click.stop>
                                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.012 2C6.506 2 2.023 6.478 2.022 11.984C2.022 13.734 2.478 15.422 3.356 16.92L2 22L7.233 20.763C8.683 21.545 10.323 21.968 12.008 21.968H12.012C17.518 21.968 22.001 17.49 22.002 11.984C22.002 6.48 17.523 2 12.012 2Z" fill="#25D366"/>
                                                    <path d="M17.472 14.382C17.175 14.233 15.714 13.515 15.442 13.415C15.169 13.316 14.971 13.267 14.772 13.565C14.575 13.862 14.005 14.531 13.832 14.729C13.659 14.928 13.485 14.952 13.188 14.804C12.891 14.654 11.933 14.341 10.798 13.329C10.003 12.621 9.406 11.648 9.233 11.35C9.06 11.053 9.215 10.892 9.363 10.744C9.497 10.611 9.661 10.397 9.809 10.224C9.958 10.05 10.007 9.926 10.107 9.727C10.206 9.529 10.157 9.356 10.082 9.207C10.007 9.058 9.413 7.595 9.166 7.001C8.924 6.422 8.679 6.5 8.497 6.49C8.324 6.49 8.102 6.49 7.83 6.49C7.558 6.49 7.114 6.589 6.842 6.887C6.57 7.184 5.802 7.903 5.802 9.366C5.802 10.828 6.867 12.241 7.015 12.44C7.164 12.638 9.111 15.64 12.092 16.927C12.801 17.233 13.354 17.416 13.786 17.552C14.498 17.779 15.146 17.747 15.657 17.67C16.228 17.585 17.415 16.951 17.663 16.257C17.911 15.563 17.911 14.968 17.836 14.844C17.762 14.72 17.564 14.646 17.266 14.497V14.382Z" fill="white"/>
                                                </svg>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-slate-800">₹{{ number_format($invoice->total_invoice_value, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                                <div class="flex items-center justify-end gap-3">
                                    @if(auth()->user()->isAdmin() || auth()->user()->can('invoice-view'))
                                    <a href="{{ route('or-invoices.show', [$invoice->id, 'page' => $invoices->currentPage()]) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-2 py-1 rounded-md hover:bg-blue-100 transition-colors">View</a>
                                    @endif
                                    
                                    @if(!$invoice->is_cancelled)
                                        @if($invoice->is_paid)
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('invoice-edit'))
                                                <a href="{{ route('or-invoices.edit', [$invoice->id, 'page' => $invoices->currentPage()]) }}" class="text-amber-600 hover:text-amber-900 bg-amber-50 px-2 py-1 rounded-md hover:bg-amber-100 transition-colors">Edit</a>
                                            @endif
                                            @if(auth()->user()->isAdmin())
                                                <a href="{{ route('or-invoices.download', $invoice->id) }}" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 px-2 py-1 rounded-md hover:bg-emerald-100 transition-colors">Download</a>
                                            @endif
                                        @else
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('invoice-mark-paid'))
                                                <form action="{{ route('or-invoices.mark_paid', $invoice->id) }}" method="POST" class="inline-block m-0">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Are you sure you want to mark this as paid? This will generate the final invoice number.')" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 px-2 py-1 rounded-md hover:bg-emerald-100 transition-colors">Mark as Paid</button>
                                                </form>
                                            @endif
                                            @if(auth()->user()->isAdmin() || auth()->user()->can('invoice-cancel'))
                                                <form action="{{ route('or-invoices.cancel', $invoice->id) }}" method="POST" class="inline-block m-0">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this PROFORMA invoice?')" class="text-rose-600 hover:text-rose-900 bg-rose-50 px-2 py-1 rounded-md hover:bg-rose-100 transition-colors">Cancel</button>
                                                </form>
                                            @endif
                                        @endif
                                    @endif

                                    @if(!$invoice->is_paid)
                                        @if(auth()->user()->isAdmin() || auth()->user()->can('invoice-delete'))
                                        <form action="{{ route('or-invoices.destroy', $invoice->id) }}" method="POST" class="inline-block m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Are you sure you want to permanently delete this invoice? This action cannot be undone.')" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded-md hover:bg-red-100 transition-colors">Delete</button>
                                        </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($invoice->paid_at)
                                    <div class="text-sm font-medium text-emerald-600">{{ $invoice->paid_at->format('d M, Y') }}</div>
                                @else
                                    <div class="text-sm text-slate-400">—</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-600">{{ ($invoice->lead?->reference && $invoice->lead?->reference !== 'None') ? $invoice->lead->reference : '—' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6">
                                <div class="flex flex-col items-center justify-center py-12 text-center gap-3">
                                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 relative">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-slate-900 border-none pb-0">No Invoices OR Found</h3>
                                    <p class="text-sm text-slate-500 max-w-sm">No invoices have been generated yet.</p>
                                    <div>
                                        <a href="{{ route('or-invoices.create') }}" class="inline-flex items-center px-6 h-12 border border-transparent shadow-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                            Create Invoice
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        
        @if($invoices->hasPages())
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $invoices->appends(request()->query())->links('partials.pagination') }}
        </div>
        @endif

        <!-- Export Invoices Modal -->
        <div x-show="exportModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6" style="display: none;" x-cloak>
            <!-- Backdrop -->
            <div x-show="exportModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="exportModalOpen = false"></div>

            <!-- Modal box -->
            <div x-show="exportModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 z-10 flex flex-col">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Invoices OR
                    </h3>
                    <button @click="exportModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Form -->
                <form action="{{ route('or-invoices.export') }}" method="GET" @submit="exportModalOpen = false">
                    <div class="px-6 py-6 space-y-4">
                        <p class="text-sm text-slate-600">Select the month and year of the invoices you wish to export in Excel (CSV) format.</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Month Select -->
                            <div>
                                <label for="export_month" class="block text-xs font-bold text-slate-700 uppercase mb-2">Month</label>
                                <select name="month" id="export_month" class="block w-full h-11 px-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm text-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Year Select -->
                            <div>
                                <label for="export_year" class="block text-xs font-bold text-slate-700 uppercase mb-2">Year</label>
                                <select name="year" id="export_year" class="block w-full h-11 px-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm text-sm">
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="exportModalOpen = false" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all shadow-md shadow-emerald-100 flex items-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Download ZIP Modal -->
        @can('invoice-or-export')
        <div x-show="zipModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6" style="display: none;" x-cloak>
            <!-- Backdrop -->
            <div x-show="zipModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="zipModalOpen = false"></div>

            <!-- Modal box -->
            <div x-show="zipModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 z-10 flex flex-col">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download OR Invoices ZIP
                    </h3>
                    <button @click="zipModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-full hover:bg-slate-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Form -->
                <form action="{{ route('or-invoices.download_zip') }}" method="GET" @submit="zipModalOpen = false">
                    <div class="px-6 py-6 space-y-4">
                        <p class="text-sm text-slate-600">Select the month and year to download all OR tax invoices as a compressed ZIP file.</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Month Select -->
                            <div>
                                <label for="zip_month" class="block text-xs font-bold text-slate-700 uppercase mb-2">Month</label>
                                <select name="month" id="zip_month" class="block w-full h-11 px-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm text-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Year Select -->
                            <div>
                                <label for="zip_year" class="block text-xs font-bold text-slate-700 uppercase mb-2">Year</label>
                                <select name="year" id="zip_year" class="block w-full h-11 px-3 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm text-sm">
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="zipModalOpen = false" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-slate-800 hover:bg-slate-900 border border-transparent rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all shadow-md shadow-slate-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download ZIP
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endcan
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const topScroll = document.getElementById('top-scrollbar-container');
            const topScrollDummy = document.getElementById('top-scrollbar-dummy');
            const tableContainer = document.getElementById('table-container');
            
            if (!topScroll || !topScrollDummy || !tableContainer) return;
            
            function updateScrollbar() {
                const scrollWidth = tableContainer.scrollWidth;
                const clientWidth = tableContainer.clientWidth;
                
                if (scrollWidth > clientWidth) {
                    topScroll.style.display = 'block';
                    topScrollDummy.style.width = scrollWidth + 'px';
                } else {
                    topScroll.style.display = 'none';
                }
            }
            
            if (window.ResizeObserver) {
                const observer = new ResizeObserver(updateScrollbar);
                observer.observe(tableContainer);
                const table = tableContainer.querySelector('table');
                if (table) {
                    observer.observe(table);
                }
            } else {
                window.addEventListener('resize', updateScrollbar);
                setTimeout(updateScrollbar, 100);
            }
            
            let isSyncingTop = false;
            let isSyncingTable = false;
            
            topScroll.addEventListener('scroll', function() {
                if (!isSyncingTop) {
                    isSyncingTable = true;
                    tableContainer.scrollLeft = topScroll.scrollLeft;
                }
                isSyncingTop = false;
            });
            
            tableContainer.addEventListener('scroll', function() {
                if (!isSyncingTable) {
                    isSyncingTop = true;
                    topScroll.scrollLeft = tableContainer.scrollLeft;
                }
                isSyncingTable = false;
            });
        });
    </script>
@endsection
