    @extends('layouts.app')

    @section('header', 'Lead Details')

    @section('content')
    @php $contactPermission = $lead->isVendor() ? 'enquiry-vendor-contact-view' : 'lead-contact-view'; @endphp
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-800">Lead Details #{{ $lead->record_id }}</h2>
            </div>
            <div class="flex flex-wrap gap-2 items-center mt-2">
                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $lead->lead_status == 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $lead->lead_status }}
                </span>
                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $lead->creation_source === 'CRM' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }} border border-transparent">
                    Source: {{ $lead->creation_source ?: 'CRM' }}
                </span>
                <span class="text-sm text-slate-500">Created {{ $lead->created_at->format('M d, Y') }}</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center md:justify-end gap-2 md:gap-3" x-data="{ showUploadModal: false, openActions: false }">
            @if($lead->isVendor())
                <a href="{{ route('vendor_leads.kyc', ['page' => request('page')]) }}" class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition-colors shadow-sm">
                    Back to Vendor KYC
                </a>
            @else
                <a href="{{ route('leads.index', ['page' => request('page')]) }}" class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition-colors shadow-sm">
                    Back to Leads
                </a>
            @endif

            @can('lead-edit')
            <a href="{{ route('leads.edit', [$lead->id, 'page' => request('page')]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition-colors shadow-sm">
                Edit Lead
            </a>
            @endcan

            <div class="relative">
                <button @click="openActions = !openActions" @click.away="openActions = false" type="button" 
                    class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition-colors shadow-sm flex items-center gap-2">
                    <span>Actions</span>
                    <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': openActions}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="openActions" x-transition style="display: none;" 
                    class="absolute left-0 sm:left-auto sm:right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden py-2">
                    
                    @if(auth()->user()->isAdmin() || auth()->user()->can('campaign-send'))
                    <button type="button" @click="$dispatch('open-messaging-modal', { type: 'whatsapp', leadId: '{{ $lead->id }}', leadName: '{{ addslashes($lead->customer_name) }}', leadMobile: '{{ $lead->mobile }}' }); openActions = false;" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                        Mobile Marketing
                    </button>
                    @endif

                    @can('email-template-send')
                    <button type="button" @click="$dispatch('open-email-modal', { bulkIds: ['{{ $lead->id }}'], emailCampaignRoute: '{{ route("leads.send_email_campaign") }}', isFilteredCampaign: false, filters: {} }); openActions = false;" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        Send Email Marketing
                    </button>
                    @endcan

                    @if(auth()->user()->isAdmin() || auth()->user()->can('lead-send-document'))
                    <button @click="showUploadModal = true; openActions = false;" type="button"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Send Document
                    </button>
                    @endif

                    @if($lead->creation_source === 'CLIENT REGISTRATION')
                        <div class="my-1 border-t border-slate-100"></div>
                        @if($lead->is_agreement_sent)
                            <div class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-indigo-700 bg-indigo-50/50 font-bold text-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                Agreement Sent
                            </div>
                        @else
                            <form action="{{ route('leads.send_agreement', $lead->id) }}" method="POST" class="w-full m-0">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors text-left font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Send Agreement
                                </button>
                            </form>
                        @endif
                    @endif

                    @if((auth()->user()->can('raise-invoice-bu') || auth()->user()->can('raise-invoice-or') || ($lead->isClient() && auth()->user()->can('client-po-access')) || ($lead->isVendor() && auth()->user()->can('vendor-po-access'))))
                        <div class="my-1 border-t border-slate-100"></div>
                    @endif

                    @can('raise-invoice-bu')
                    <a href="{{ route('invoices.create', ['lead_id' => $lead->id]) }}" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Raise Invoice BU
                    </a>
                    @endcan

                    @can('raise-invoice-or')
                    <a href="{{ route('or-invoices.create', ['lead_id' => $lead->id]) }}" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Invoice OR
                    </a>
                    @endcan

                    @if($lead->isClient() && auth()->user()->can('client-po-access'))
                    <a href="{{ route('manage_po.client_po.create', ['lead_id' => $lead->id, 'customer_id' => $lead->customer_id]) }}" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Client PO
                    </a>
                    @endif

                    @if($lead->isVendor() && auth()->user()->can('vendor-po-access'))
                    <a href="{{ route('manage_po.vendor_po', ['lead_id' => $lead->id]) }}" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition-colors text-left font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Vendor PO
                    </a>
                    @endif
                </div>
            </div>

            <!-- Upload Modal -->
            <div x-show="showUploadModal" 
                class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                style="display: none;" 
                x-transition x-cloak>
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden" @click.away="showUploadModal = false">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800">Upload & Send Document</h3>
                        <button @click="showUploadModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('leads.send_file', $lead->id) }}" method="POST" enctype="multipart/form-data" 
                            x-data="{ uploading: false }" @submit="uploading = true">
                            @csrf
                            <div class="space-y-4">
                                <div class="relative group">
                                    <input type="file" name="document" id="modal_doc_input" required
                                        class="hidden" 
                                        @change="$refs.modalFileName.innerText = $el.files[0].name">
                                    <label for="modal_doc_input" 
                                        class="flex flex-col items-center justify-center w-full px-4 py-8 border-2 border-dashed border-slate-200 rounded-2xl hover:border-indigo-400 hover:bg-indigo-50/30 transition-all cursor-pointer group">
                                        <svg class="w-10 h-10 text-slate-300 group-hover:text-indigo-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span x-ref="modalFileName" class="text-sm font-medium text-slate-600 group-hover:text-indigo-600">Click to select document</span>
                                        <span class="text-xs text-slate-400 mt-1">Max file size: 15MB</span>
                                    </label>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button type="button" @click="showUploadModal = false" class="flex-1 px-4 py-3 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition-all">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                        :disabled="uploading"
                                        class="flex-[2] bg-indigo-600 text-white px-4 py-3 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <template x-if="!uploading">
                                            <span>Send to Customer</span>
                                        </template>
                                        <template x-if="uploading">
                                            <div class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Sending...
                                            </div>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @can('lead-delete')
                <div x-data="{ showDeleteModal: false }">
                    <button @click="showDeleteModal = true" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-lg font-medium hover:bg-red-100 transition-colors shadow-sm">
                        Delete Lead
                    </button>

                    <!-- Delete Confirmation Modal -->
                    <div x-show="showDeleteModal" 
                        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                        style="display: none;" 
                        x-transition>
                        <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center" @click.away="showDeleteModal = false">
                            <div class="mb-4">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">Delete Lead</h3>
                                <p class="text-sm text-slate-600 mt-2">
                                    Are you sure you want to delete lead <strong>#{{ $lead->record_id }}</strong>? This action cannot be undone.
                                </p>
                            </div>
                            <div class="flex justify-center gap-3 w-full mt-6">
                                <button @click="showDeleteModal = false" type="button" class="w-full justify-center px-4 py-2 border border-slate-300 bg-white text-slate-700 rounded-lg hover:bg-slate-50 font-medium shadow-sm">
                                    Cancel
                                </button>
                                <form action="{{ route('leads.destroy', [$lead->id, 'page' => request('page')]) }}" method="POST" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-sm transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            @if(auth()->user()->isAdmin() || auth()->user()->can('whatsapp-icon'))
                @if($lead->mobile)
                    @php
                        $waMobile = preg_replace('/[^0-9]/', '', $lead->mobile);
                        if(strlen($waMobile) == 10) $waMobile = '91' . $waMobile;
                    @endphp
                    <a href="https://wa.me/{{ $waMobile }}" target="_blank" class="flex items-center justify-center p-2 rounded-lg hover:bg-green-50 transition-colors border border-transparent hover:border-green-200" title="Chat on WhatsApp">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.012 2C6.506 2 2.023 6.478 2.022 11.984C2.022 13.734 2.478 15.422 3.356 16.92L2 22L7.233 20.763C8.683 21.545 10.323 21.968 12.008 21.968H12.012C17.518 21.968 22.001 17.49 22.002 11.984C22.002 6.48 17.523 2 12.012 2Z" fill="#25D366"/>
                            <path d="M17.472 14.382C17.175 14.233 15.714 13.515 15.442 13.415C15.169 13.316 14.971 13.267 14.772 13.565C14.575 13.862 14.005 14.531 13.832 14.729C13.659 14.928 13.485 14.952 13.188 14.804C12.891 14.654 11.933 14.341 10.798 13.329C10.003 12.621 9.406 11.648 9.233 11.35C9.06 11.053 9.215 10.892 9.363 10.744C9.497 10.611 9.661 10.397 9.809 10.224C9.958 10.05 10.007 9.926 10.107 9.727C10.206 9.529 10.157 9.356 10.082 9.207C10.007 9.058 9.413 7.595 9.166 7.001C8.924 6.422 8.679 6.5 8.497 6.49C8.324 6.49 8.102 6.49 7.83 6.49C7.558 6.49 7.114 6.589 6.842 6.887C6.57 7.184 5.802 7.903 5.802 9.366C5.802 10.828 6.867 12.241 7.015 12.44C7.164 12.638 9.111 15.64 12.092 16.927C12.801 17.233 13.354 17.416 13.786 17.552C14.498 17.779 15.146 17.747 15.657 17.67C16.228 17.585 17.415 16.951 17.663 16.257C17.911 15.563 17.911 14.968 17.836 14.844C17.762 14.72 17.564 14.646 17.266 14.497V14.382Z" fill="white"/>
                        </svg>
                    </a>
                @endif
            @endif
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            

            <!-- Customer Details -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-lg font-semibold text-slate-800">Customer Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                    @if(request('from') === 'vendor_kyc' || (request('from') === null && $lead->isVendor()))
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Company Name</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->company_name ?: '-' }}</span>
                    </div>
                    @else
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Customer Name</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->customer_name }}</span>
                    </div>
                     <div>
                        <span class="block text-sm font-medium text-slate-500">Company Name</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->company_name ?: '-' }}</span>
                    </div>
                    @endif
                    @if($lead->contact_person)
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Contact Person</span>
                        <span class="block text-base text-slate-900 mt-1">
                            @php
                                $hasMobile = preg_match('/[0-9]{7,}/', preg_replace('/[^0-9]/', '', $lead->contact_person));
                            @endphp
                            @if($hasMobile && !auth()->user()->can($contactPermission))
                                ********
                            @else
                                {{ $lead->contact_person }}
                            @endif
                        </span>
                    </div>
                    @endif
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Place/City</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->city ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Company Type</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->company_type ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Mobile No</span>
                        <span class="block text-base text-slate-900 mt-1">
                            @can($contactPermission)
                                {{ $lead->mobile }}
                            @else
                                ********
                            @endcan
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Email ID</span>
                        <span class="block text-base text-slate-900 mt-1 break-all">
                            @can($contactPermission)
                                {{ $lead->email_id }}
                            @else
                                ********
                            @endcan
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Reference</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->reference ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Designation</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->designation ?: '-' }}</span>
                    </div>
                    
                </div>
            </div>

            <!-- Contact Info -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-lg font-semibold text-slate-800">Contact Info</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Alternate Mobile</span>
                        <span class="block text-base text-slate-900 mt-1">
                            @can($contactPermission)
                                {{ $lead->alternate_mobile ?: '-' }}
                            @else
                                ********
                            @endcan
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Alternate Mobile 2</span>
                        <span class="block text-base text-slate-900 mt-1">
                            @can($contactPermission)
                                {{ $lead->alternate_mobile_2 ?: '-' }}
                            @else
                                ********
                            @endcan
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Alternate Email</span>
                        <span class="block text-base text-slate-900 mt-1 break-all">
                            @can($contactPermission)
                                {{ $lead->alternate_email_id ?: '-' }}
                            @else
                                ********
                            @endcan
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Alternate Email 2</span>
                        <span class="block text-base text-slate-900 mt-1 break-all">
                            @can($contactPermission)
                                {{ $lead->alternate_email_id_2 ?: '-' }}
                            @else
                                ********
                            @endcan
                        </span>
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            @if(auth()->user()->isAdmin() || auth()->user()->can('company-info-section'))
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-lg font-semibold text-slate-800">Company Info</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Company Name</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->company_name ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Website</span>
                        <span class="block text-base text-slate-900 mt-1">
                            @if($lead->website)
                                <a href="{{ Str::startsWith($lead->website, 'http') ? $lead->website : 'https://'.$lead->website }}" target="_blank" class="text-indigo-600 hover:underline">{{ $lead->website }}</a>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-sm font-medium text-slate-500">Company Address</span>
                        <span class="block text-base text-slate-900 mt-1 break-words">{{ $lead->company_address ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">State Name</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->customer->state_name ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">State Code</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->customer->state_code ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">PAN Number</span>
                        <span class="block text-base text-slate-900 mt-1 uppercase">{{ $lead->pan_number ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Aadhar No</span>
                        <span class="block text-base text-slate-900 mt-1 uppercase">{{ $lead->aadhar_no ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">GST No</span>
                        <span class="block text-base text-slate-900 mt-1 uppercase">{{ $lead->gst_no ?: '-' }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-sm font-medium text-slate-500">Udyam Est</span>
                        <span class="block text-base text-slate-900 mt-1 uppercase">{{ $lead->udyam_registration_certificate ?: '-' }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Client Order Info -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-lg font-semibold text-slate-800">Client Order Info</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Nature of Industry</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->nature_of_industry ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Customer Type</span>
                        <span class="block text-base text-slate-900 mt-1">
                            <span class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-xs font-semibold">{{ $lead->customer_type ?: '-' }}</span>
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Initial Product Interest</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->initial_product_interest ?: '-' }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-sm font-medium text-slate-500">Product Demand</span>
                        <p class="text-base text-slate-900 mt-1 whitespace-pre-wrap">{{ $lead->product_demand ?: '-' }}</p>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Quantity</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->quantity ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Rate</span>
                        <span class="block text-base text-slate-900 mt-1">{{ $lead->rate ?: '-' }}</span>
                    </div>
                    @if($lead->comment)
                    <div class="md:col-span-2">
                        <span class="block text-sm font-medium text-slate-500">Lead Comment</span>
                        <p class="text-base text-slate-700 bg-slate-50 p-4 rounded-lg mt-1 border border-slate-100 italic">"{{ $lead->comment }}"</p>
                    </div>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <div class="md:col-span-2 border-t border-slate-100 pt-4 mt-2">
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-3">Admin Only Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="block text-sm font-medium text-slate-500">Admin Rate</span>
                                <span class="block text-base text-slate-900 mt-1 font-semibold text-indigo-700">{{ $lead->admin_rate ?: '-' }}</span>
                            </div>
                            @if($lead->admin_comment)
                            <div class="md:col-span-2">
                                <span class="block text-sm font-medium text-slate-500">Admin Lead Comment</span>
                                <p class="text-base text-indigo-900 bg-indigo-50/50 p-4 rounded-lg mt-1 border border-indigo-100 italic">"{{ $lead->admin_comment }}"</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right Column: Sidebar info (Leads, Tracking, Documents) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Leads Details -->
            <div class="bg-indigo-50 rounded-xl shadow-sm border border-indigo-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-indigo-200 bg-indigo-100/50">
                    <h3 class="text-lg font-semibold text-indigo-900">Leads Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <span class="block text-sm font-medium text-slate-500">MSA Signed</span>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $lead->master_service_agreement_signed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $lead->master_service_agreement_signed ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Lead Status</span>
                        <span class="inline-block mt-1 text-base font-semibold {{ $lead->lead_status == 'Active' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $lead->lead_status }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-slate-500">KYC Status</span>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $lead->kyc == 'Done' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $lead->kyc }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tracking Info -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-lg font-semibold text-slate-800">Tracking Info</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Follow Up Date</span>
                        @if($lead->follow_up_date)
                            <div class="mt-1 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-base font-semibold {{ \Carbon\Carbon::parse($lead->follow_up_date)->isToday() ? 'text-amber-600' : 'text-slate-900' }}">
                                    {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('M d, Y') }}
                                </span>
                            </div>
                        @else
                            <span class="block text-base text-slate-900 mt-1">-</span>
                        @endif
                    </div>
                    <hr class="border-slate-100">
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Previous Deals Date</span>
                        @if($lead->previous_deals_and_date)
                            <span class="block text-sm text-slate-900 mt-1">{{ \Carbon\Carbon::parse($lead->previous_deals_and_date)->format('M d, Y') }}</span>
                        @else
                            <span class="block text-sm text-slate-900 mt-1">-</span>
                        @endif
                    </div>
                    <hr class="border-slate-100">
                    <div>
                        <span class="block text-sm font-medium text-slate-500">Records Owner</span>
                        @if($lead->records_owner)
                            <div class="mt-2 flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ substr($lead->records_owner, 0, 1) }}
                                </div>
                                <span class="text-sm text-slate-900">{{ $lead->records_owner }}</span>
                            </div>
                        @else
                            <span class="block text-sm text-slate-900 mt-1">-</span>
                        @endif
                    </div>
                </div>
            </div>


            <!-- Documents -->
            @can('document-section')
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">Documents</h3>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </div>
                <div class="p-4 space-y-3">
                    @php
                        $isVendor = str_contains(strtoupper($lead->creation_source ?? ''), 'VENDOR');
                        
                        $docs = [
                            'doc_pan' => 'PAN Card',
                            'doc_aadhar' => 'Aadhar Card',
                            'doc_gst' => 'GST Certificate',
                            'doc_certificate_incorporation_udyam' => 'Certificate / Udyam Est',
                        ];

                        if (!$isVendor) {
                            $docs['doc_trai_dlt'] = 'Trai DLT';
                            $docs['doc_dsa_license'] = 'DSA License';
                            $docs['doc_company_id_card'] = 'Company ID Card';
                        }

                        $docs['msa_document'] = 'MSA Document';
                    @endphp

                    @foreach($docs as $field => $label)
                        <div class="flex items-center justify-between p-3 rounded-lg border {{ $lead->{$field} ? 'border-indigo-100 bg-indigo-50/30' : 'border-slate-100 bg-slate-50/50' }}">
                            <span class="text-sm font-medium {{ $lead->{$field} ? 'text-indigo-900' : 'text-slate-500' }}">{{ $label }}</span>
                            @if($lead->{$field})
                                <a href="{{ Str::startsWith($lead->{$field}, ['http://', 'https://']) 
                                    ? $lead->{$field} 
                                    : 'https://billionsunited.com/crm/storage/app/public/' . $lead->{$field} }}" 
                                    target="_blank" 
                                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium hover:underline flex items-center gap-1">
                                    View
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Not uploaded</span>
                            @endif
                        </div>
                    @endforeach

                    <!-- @foreach($docs as $field => $label)
                    <div class="flex items-center justify-between p-3 rounded-lg border {{ $lead->{$field} ? 'border-indigo-100 bg-indigo-50/30' : 'border-slate-100 bg-slate-50/50' }}">
                        <span class="text-sm font-medium {{ $lead->{$field} ? 'text-indigo-900' : 'text-slate-500' }}">{{ $label }}</span>
                        @if($lead->{$field})
                            <a href="{{ Str::startsWith($lead->{$field}, ['http://', 'https://']) 
                                ? $lead->{$field} 
                                : 'https://billionsunited.com/crm/storage/app/public/' . $lead->{$field} }}" 
                                target="_blank"
                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium hover:underline flex items-center gap-1">
                                View
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        @else
                            <span class="text-xs text-slate-400">Not uploaded</span>
                        @endif
                    </div>
                @endforeach -->
                </div>
            </div>
            @endif           

            <!-- Documents Sent History -->
            @php
                $documentTrackings = $lead->documentTrackings()
                    ->selectRaw('DATE(created_at) as date, MAX(created_at) as last_sent_at, count(*) as count')
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->get();
            @endphp
            @if($documentTrackings->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">Documents Sent History</h3>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                    @foreach($documentTrackings as $tracking)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50/50">
                            <span class="text-sm font-medium text-slate-700">
                                {{ \Carbon\Carbon::parse($tracking->last_sent_at)->timezone('Asia/Kolkata')->format('M d, Y h:i A') }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                {{ $tracking->count }} {{ Str::plural('Document', $tracking->count) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endsection
