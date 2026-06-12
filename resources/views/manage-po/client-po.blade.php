@extends('layouts.app')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="poForm()">

        <script>
            window.leadsData = {!! $leads->toJson() !!};
        </script>

        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Client P.O Generation</h1>
                <p class="text-sm text-slate-500 mt-1">Select a customer and generate their Statement of Work.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form action="{{ route('manage_po.client_po.store') }}" method="POST" id="purchaseOrderForm">
                @csrf

                <!-- Customer Selection -->
                <div class="mb-8 p-5 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Select Existing Client <span
                            class="text-rose-500">*</span></label>

                    <!-- Searchable Dropdown for Client Selection -->
                    <div x-data="{ 
                                open: false, 
                                search: '',
                                get filteredLeads() {
                                    if (this.search === '') return window.leadsData;
                                    return window.leadsData.filter(l => {
                                        const term = this.search.toLowerCase();
                                        const org = (l.organisation_name || '').toLowerCase();
                                        const client = (l.client_name || '').toLowerCase();
                                        const id = String(l.record_id || '').toLowerCase();
                                        return org.includes(term) || client.includes(term) || id.includes(term);
                                    });
                                },
                                get selectedLabel() {
                                    if (!selectedLeadId) return '-- Choose an Existing Client --';
                                    const lead = window.leadsData.find(l => String(l.id) === String(selectedLeadId));
                                    if (!lead) return '-- Choose an Existing Client --';
                                    const name = lead.organisation_name !== 'None' ? lead.organisation_name : lead.client_name;
                                    return `${name} - ${lead.record_id}`;
                                }
                            }" @click.outside="open = false" class="relative w-full">

                        <input type="hidden" name="customer_id" x-model="selectedCustomerId" required>

                        <!-- Dropdown Trigger Button -->
                        <button type="button" @click="open = !open"
                            class="w-full text-left rounded-lg border border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] px-3 flex items-center justify-between bg-white focus:outline-none"
                            :class="selectedLeadId ? 'text-slate-900 border-indigo-200 bg-indigo-50/30' : 'text-slate-500'">
                            <span x-text="selectedLabel" class="truncate block"></span>
                            <svg class="h-5 w-5 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" x-transition.opacity.duration.200ms
                            class="absolute z-50 mt-2 w-full bg-white rounded-xl border border-slate-200 shadow-2xl overflow-hidden flex flex-col"
                            style="display: none;">
                            <!-- Search Input -->
                            <div class="p-2 border-b border-slate-100 bg-slate-50 relative z-10 shadow-sm">
                                <input type="text" x-model="search" x-ref="searchInput"
                                    placeholder="Search by Client Name or ID..."
                                    class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white"
                                    @focus="open = true" @keydown.escape="open = false"
                                    x-init="$watch('open', value => { if (value) setTimeout(() => $refs.searchInput.focus(), 50) })">
                            </div>
                            <!-- List -->
                            <div class="overflow-y-auto max-h-64 flex-1 p-1">
                                <template x-for="lead in filteredLeads" :key="'lead-'+lead.id">
                                    <button type="button"
                                        @click="selectedLeadId = lead.id; selectedCustomerId = lead.customer_id; handleLeadChange(); open = false; search = ''"
                                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-slate-100 transition-all focus:outline-none flex items-center justify-between group"
                                        :class="String(selectedLeadId) === String(lead.id) ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700'">
                                        <div class="flex flex-col">
                                            <span
                                                x-text="lead.organisation_name !== 'None' ? lead.organisation_name : lead.client_name"></span>
                                        </div>
                                        <span class="text-xs font-mono px-2 py-0.5 rounded-md transition-colors"
                                            :class="String(selectedLeadId) === String(lead.id) ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'"
                                            x-text="lead.record_id"></span>
                                    </button>
                                </template>
                                <div x-show="filteredLeads.length === 0"
                                    class="px-4 py-6 text-center text-sm text-slate-500 bg-slate-50">
                                    No clients found matching your search.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Basic Details -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-100">Basic Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Organization Name <span
                                    class="text-rose-500">*</span></label>
                            <input type="text" name="organization_name" x-model="formData.organization_name"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Contact Person <span
                                    class="text-rose-500">*</span></label>
                            <input type="text" name="contact_person" x-model="formData.contact_person"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Effective Date of SOW <span
                                    class="text-rose-500">*</span></label>
                            <input type="date" name="sow_effective_date"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Place <span
                                    class="text-rose-500">*</span></label>
                            <input type="text" name="client_place" x-model="formData.client_place"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Authorised Recipient Email <span
                                    class="text-rose-500">*</span></label>
                            <input type="email" name="authorised_recipient_email" x-model="formData.email_id"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Registered Address <span
                                    class="text-rose-500">*</span></label>
                            <input type="text" name="registered_address" x-model="formData.registered_address"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                required>
                        </div>
                    </div>
                </div>

                <!-- Scope of Work -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-100">Scope of Work</h4>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Services</label>
                        <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="services[]" value="Database Supply" x-model="services"
                                    class="service-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">Marketing Database</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="services[]" value="SMS Campaign" x-model="services"
                                    class="service-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">SMS Campaign</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="services[]" value="WhatsApp Campaign" x-model="services"
                                    class="service-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">WhatsApp Campaign</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="services[]" value="RCS Campaign" x-model="services"
                                    class="service-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">RCS Campaign</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Database Quantity</label>
                            <input type="number" name="database_quantity"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                :class="!services.includes('Database Supply') ? 'bg-slate-100' : ''"
                                :readonly="!services.includes('Database Supply')" min="0" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">SMS Quantity</label>
                            <input type="number" name="sms_quantity"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                :class="!services.includes('SMS Campaign') ? 'bg-slate-100' : ''"
                                :readonly="!services.includes('SMS Campaign')" min="0" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp Quantity</label>
                            <input type="number" name="whatsapp_quantity"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                :class="!services.includes('WhatsApp Campaign') ? 'bg-slate-100' : ''"
                                :readonly="!services.includes('WhatsApp Campaign')" min="0" value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">RCS Quantity</label>
                            <input type="number" name="rcs_quantity"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                :class="!services.includes('RCS Campaign') ? 'bg-slate-100' : ''"
                                :readonly="!services.includes('RCS Campaign')" min="0" value="0">
                        </div>
                    </div>
                </div>

                <!-- Database Specifications -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-100">Database
                        Specifications</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <!-- Target Segment -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Target Segment</label>
                            <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200"
                                x-data="{ other: false }">
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_segment[]"
                                        value="Salaried"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Salaried</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_segment[]"
                                        value="SME"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">SME</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_segment[]"
                                        value="Car"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Car</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_segment[]"
                                        value="Other" x-model="other"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Other</span></label>

                                <div class="mt-2" x-show="other" x-transition>
                                    <input type="text" name="target_segment_other_text"
                                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                        placeholder="Please specify other target segment">
                                </div>
                            </div>
                        </div>

                        <!-- Target Geography -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Target Geography</label>
                            <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_geography[]"
                                        value="Major metros"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Major metros</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_geography[]"
                                        value="2 Tier"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">2 Tier</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_geography[]"
                                        value="3 Tier"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">3 Tier</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="target_geography[]"
                                        value="Pan India"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Pan India</span></label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tentative Salary / Salary Band
                            Filter</label>
                        <input type="text" name="salary_band_filter"
                            class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                            placeholder="Enter salary range (e.g. 4-7, 10-20, 50+)">
                    </div>
                </div>

                <!-- Charges and Payment -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-100">Charges and Payment
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Database Supply Charge</label>
                            <input type="number" step="0.01" name="database_supply_charge"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">SMS Campaign Charge</label>
                            <input type="number" step="0.01" name="sms_campaign_charge"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">WhatsApp Campaign Charge</label>
                            <input type="number" step="0.01" name="whatsapp_campaign_charge"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                value="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">RCS Campaign Charge</label>
                            <input type="number" step="0.01" name="rcs_campaign_charge"
                                class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                value="0">
                        </div>
                    </div>
                </div>

                <!-- Permitted Purpose and Use -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-100">Permitted Purpose
                        and Use</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product / Service Being Marketed -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Product / Service Being
                                Marketed</label>
                            <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200"
                                x-data="{ other: false }">
                                <label class="flex items-center gap-2"><input type="checkbox"
                                        name="product_service_marketed[]" value="Personal Loan"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Personal Loan</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox"
                                        name="product_service_marketed[]" value="Business Loan"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Business Loan</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox"
                                        name="product_service_marketed[]" value="Insurance"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Insurance</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox"
                                        name="product_service_marketed[]" value="Other" x-model="other"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Other</span></label>

                                <div class="mt-2" x-show="other" x-transition>
                                    <input type="text" name="product_service_other_text"
                                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                                        placeholder="Please specify other product/service">
                                </div>
                            </div>
                        </div>

                        <!-- Marketing Channel -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Marketing Channel</label>
                            <div class="space-y-2 bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <label class="flex items-center gap-2"><input type="checkbox" name="marketing_channel[]"
                                        value="Tele-sales"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">Tele-sales</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="marketing_channel[]"
                                        value="SMS"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">SMS</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="marketing_channel[]"
                                        value="WhatsApp"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">WhatsApp</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="marketing_channel[]"
                                        value="RCS"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"><span
                                        class="text-sm text-slate-700">RCS</span></label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signature & Declaration -->
                <div class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4 space-y-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="use_digital_signature" value="1"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-slate-700">Include Digital Signature</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="include_ip" value="1"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-slate-700">Include IP on Document</span>
                                </label>
                            </div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Customer Digital Signature</label>
                            <div id="signature_display"
                                class="border-2 border-dashed border-slate-300 rounded-lg p-4 flex items-center justify-center bg-slate-50 min-h-[120px]">
                                <template x-if="signaturePath">
                                    <img :src="signaturePath" alt="Signature" class="max-h-[100px] object-contain">
                                </template>
                                <template x-if="!signaturePath">
                                    <span class="text-slate-400 text-sm"
                                        x-text="selectedLeadId ? '' : 'Select a customer to view their signature.'"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label
                            class="flex items-start gap-3 p-4 border border-indigo-100 bg-indigo-50 rounded-lg cursor-pointer hover:bg-indigo-100 transition">
                            <input type="checkbox" name="declaration_confirm" value="1"
                                class="mt-1 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500" required>
                            <span class="text-sm text-indigo-900 font-medium">I confirm that all details are correct and
                                authorize the generation of this Statement of Work.</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-5 border-t border-slate-200">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition">
                        Generate Document & Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('poForm', () => ({
                selectedLeadId: '{{ request('lead_id', '') }}',
                selectedCustomerId: '{{ request('customer_id', '') }}',
                services: [],
                signaturePath: null,
                formData: {
                    organization_name: '',
                    contact_person: '',
                    client_place: '',
                    registered_address: '',
                    email_id: ''
                },

                init() {
                    if (this.selectedLeadId) {
                        this.handleLeadChange();
                    }
                },

                handleLeadChange() {
                    const id = parseInt(this.selectedLeadId);
                    if (!id) {
                        this.resetForm();
                        return;
                    }
                    const lead = window.leadsData.find(l => String(l.id) === String(id));
                    if (lead) {
                        this.formData = {
                            organization_name: lead.organisation_name !== 'None' ? lead.organisation_name : '',
                            contact_person: lead.client_name !== 'None' ? lead.client_name : '',
                            client_place: lead.city !== 'None' ? lead.city : '',
                            registered_address: lead.address !== 'None' ? lead.address : '',
                            email_id: lead.email_id !== 'None' ? lead.email_id : ''
                        };
                        this.signaturePath = lead.signature_path;
                    }
                },

                resetForm() {
                    this.formData = {
                        organization_name: '',
                        contact_person: '',
                        client_place: '',
                        registered_address: '',
                        email_id: ''
                    };
                    this.signaturePath = null;
                }
            }))
        });
    </script>
@endsection