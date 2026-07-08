@extends('layouts.app')
@section('header', 'View Campaign Lead')

@section('content')
<style>
@media (max-width: 767px) {
    .mob-header-wrap { flex-direction: column !important; align-items: flex-start !important; gap: 1rem !important; }
    .mob-btn-group { flex-wrap: wrap !important; width: 100% !important; justify-content: space-between !important; }
    .mob-btn-group > * { flex: 1 1 auto !important; justify-content: center !important; }
    .mob-btn-group > form { display: flex !important; width: 100% !important; }
    .mob-btn-group > form > button { width: 100% !important; justify-content: center !important; }
    .mob-card-pad { padding: 1rem !important; }
    .mob-grid-gap { gap: 1rem !important; }
    .mob-space-y > * + * { margin-top: 1rem !important; }
    .mob-subgrid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 1rem !important; padding-top: 1rem !important; }
    .mob-colspan-2 { grid-column: span 2 / span 2 !important; }
    .mob-break { word-break: break-all !important; }
}
</style>

<div class="w-full">
    <div class="flex items-center justify-between mb-8 mob-header-wrap">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Lead Details</h1>
            </div>
            <p class="text-slate-500 mt-1">Detailed information for campaign lead.</p>
            <div class="mt-4">
                <a href="{{ route('campaign-leads.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-semibold transition-colors bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back to List
                </a>
            </div>
        </div>
        <div class="flex items-center gap-3 mob-btn-group">
            @if(auth()->user()->isAdmin() || auth()->user()->can('campaign-send'))
            <button type="button"
                @click="$dispatch('open-messaging-modal', { type: 'whatsapp', leadId: '{{ $campaignLead->id }}', leadName: '{{ addslashes($campaignLead->customer_name) }}', leadMobile: '{{ $campaignLead->mobile }}' })"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg font-bold text-sm hover:bg-indigo-100 transition-all"
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
            <button type="button"
                @click="$dispatch('open-email-modal', { 
                    bulkIds: ['{{ $campaignLead->id }}'],
                    emailCampaignRoute: '{{ route("campaign-leads.send_email_campaign") }}',
                    isFilteredCampaign: false,
                    filters: {}
                })"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg font-bold text-sm hover:bg-indigo-100 transition-all"
                title="Send Email Marketing">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Send Email Marketing
            </button>
            @endcan

            <a href="{{ route('campaign-leads.edit', $campaignLead->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold text-sm hover:bg-indigo-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                Edit Lead
            </a>
            
            @if(auth()->user()->isAdmin() || auth()->user()->can('campaign-delete'))
            <form action="{{ route('campaign-leads.destroy', $campaignLead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 text-white rounded-lg font-bold text-sm hover:bg-rose-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Delete Lead
                </button>
            </form>
            @endif

            @if(auth()->user()->isAdmin() || auth()->user()->can('whatsapp-icon'))
                @if($campaignLead->mobile)
                    @php
                        $waMobile = preg_replace('/[^0-9]/', '', $campaignLead->mobile);
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

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-8 mob-card-pad">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mob-grid-gap">
                <!-- Basic Info -->
                <div class="space-y-6 mob-space-y">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Customer Name</label>
                        <div class="text-lg font-bold text-slate-900">{{ $campaignLead->customer_name ?: 'N/A' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Company Name</label>
                        <div class="text-sm font-semibold text-slate-700">{{ $campaignLead->company_name ?: 'N/A' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Email Address</label>
                        <div class="text-sm font-semibold text-slate-700 mob-break">
                            @can('enquiry-vendor-contact-view')
                                {{ $campaignLead->email_id ?: 'N/A' }}
                            @else
                                ********
                            @endcan
                        </div>
                    </div>
                    @if($campaignLead->email_id_1)
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Alternate Email</label>
                        <div class="text-sm font-semibold text-slate-700 mob-break">
                            @can('enquiry-vendor-contact-view')
                                {{ $campaignLead->email_id_1 }}
                            @else
                                ********
                            @endcan
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Contact & Location -->
                <div class="space-y-6 mob-space-y">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Mobiles</label>
                        <div class="space-y-1">
                            @can('enquiry-vendor-contact-view')
                                <div class="text-sm font-bold text-indigo-600">{{ $campaignLead->mobile ?: 'N/A' }}</div>
                                @if($campaignLead->mobile_1) <div class="text-xs text-slate-500">{{ $campaignLead->mobile_1 }}</div> @endif
                                @if($campaignLead->mobile_2) <div class="text-xs text-slate-500">{{ $campaignLead->mobile_2 }}</div> @endif
                            @else
                                <div class="text-sm font-bold text-indigo-600">********</div>
                                @if($campaignLead->mobile_1) <div class="text-xs text-slate-500">********</div> @endif
                                @if($campaignLead->mobile_2) <div class="text-xs text-slate-500">********</div> @endif
                            @endcan
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Place / City</label>
                        <div class="text-sm font-semibold text-slate-700">{{ $campaignLead->place ?: 'N/A' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Address</label>
                        <div class="text-sm font-semibold text-slate-700 break-words">{{ $campaignLead->address ?: 'N/A' }}</div>
                    </div>
                </div>

                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-slate-100 mob-subgrid">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Type of Firm</label>
                        <div class="inline-flex px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                            {{ $campaignLead->type_of_firm ?: 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Product Interested</label>
                        <div class="text-sm font-bold text-indigo-600">{{ $campaignLead->product_interested ?: 'N/A' }}</div>
                    </div>
                    <div class="mob-colspan-2">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Lead Type</label>
                        <div class="inline-flex px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
                            {{ $campaignLead->rate ?: 'General' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Reference</label>
                        <div class="text-sm font-bold text-slate-700">{{ $campaignLead->reference ?: 'N/A' }}</div>
                    </div>
                </div>

                <div class="md:col-span-2 pt-6 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Comment</label>
                    <div class="p-4 bg-slate-50 rounded-xl text-sm text-slate-600 leading-relaxed border border-slate-100">
                        {{ $campaignLead->comment ?: 'No comments provided.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
