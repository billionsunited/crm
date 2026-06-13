@extends('layouts.app')
@section('header', 'Create Invoice')
@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="invoiceForm()">

        <script>
            // Store leads collection securely in the global space for Alpine
            window.leadsData = {!! $leads->toJson() !!};
        </script>

        <div class="mb-8 flex flex-col md:flex-row gap-4 justify-between md:items-center">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Create Invoice ✨</h1>
            <a href="{{ route('invoices.index') }}"
                class="inline-flex h-11 w-full md:w-auto items-center justify-center rounded-xl border border-slate-300 bg-white px-6 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 whitespace-nowrap">Back to List</a>
        </div>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('invoices.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Section: Client Selection -->
            <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Client Details & Invoice
                    Meta</h2>

                <!-- Input selection inline -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Select Lead
                            / Client <span class="text-rose-500">*</span></label>
                        <!-- Custom Searchable Dropdown for Lead Selection -->
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
                                            const reference = (l.reference || '').toLowerCase();
                                            return org.includes(term) || client.includes(term) || id.includes(term) || reference.includes(term);
                                        });
                                    },
                                    get selectedLabel() {
                                        if (!this.selectedLeadId) return '-- Choose an Existing Client --';
                                        const lead = window.leadsData.find(l => String(l.id) === String(this.selectedLeadId));
                                        if (!lead) return '-- Choose an Existing Client --';
                                        const name = lead.organisation_name !== 'None' ? lead.organisation_name : lead.client_name;
                                        return `${name} - ${lead.record_id}`;
                                    }
                                }" @click.outside="open = false" class="relative w-full">

                            <input type="hidden" name="lead_id" x-model="selectedLeadId" required>

                            <!-- Dropdown Trigger Button -->
                            <button type="button" @click="open = !open"
                                class="w-full text-left rounded-lg border border-slate-200 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] px-3 flex items-center justify-between bg-white focus:outline-none"
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
                                class="absolute z-50 mt-2 w-full min-w-[300px] bg-white rounded-xl border border-slate-200 shadow-2xl overflow-hidden flex flex-col"
                                style="display: none;">
                                <!-- Search Input -->
                                <div class="p-2 border-b border-slate-100 bg-slate-50 relative z-10 shadow-sm">
                                    <input type="text" x-model="search" x-ref="searchInput"
                                        placeholder="Search by Client Name, ID or Reference..."
                                        class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white"
                                        @focus="open = true" @keydown.escape="open = false"
                                        x-init="$watch('open', value => { if (value) setTimeout(() => $refs.searchInput.focus(), 50) })">
                                </div>
                                <!-- List -->
                                <div class="overflow-y-auto max-h-64 flex-1 p-1">
                                    <template x-for="lead in filteredLeads" :key="'lead-'+lead.id">
                                        <button type="button"
                                            @click="selectedLeadId = lead.id; handleLeadChange(); open = false; search = ''"
                                            class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-slate-100 transition-all focus:outline-none flex items-center justify-between group"
                                            :class="String(selectedLeadId) === String(lead.id) ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700'">
                                            <div class="flex flex-col">
                                                <span
                                                    x-text="lead.organisation_name !== 'None' ? lead.organisation_name : lead.client_name"></span>
                                                <template x-if="lead.reference && lead.reference !== 'None'">
                                                    <span class="text-xs text-slate-400 font-normal mt-0.5"
                                                        x-text="'Ref: ' + lead.reference"></span>
                                                </template>
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


                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Due
                            Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d') }}" required
                            class="w-full rounded-lg border-slate-200 shadow-sm text-sm h-[42px] focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tax Type
                            Selection <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-4 h-[42px] px-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="tax_type" value="local" x-model="taxType"
                                    class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                <span class="text-sm font-medium text-slate-700">Local <span class="hidden sm:inline">(CGST
                                        + SGST)</span></span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="tax_type" value="outstation" x-model="taxType"
                                    class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                <span class="text-sm font-medium text-slate-700">Outstation <span
                                        class="hidden sm:inline">(IGST)</span></span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Purchase Order (P.O) No.</label>
                        <input type="text" name="purchase_order" x-model="receiver.purchase_order"
                            class="w-full rounded-lg border-slate-200 shadow-sm text-sm h-[42px] focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Enter P.O Number or NA">
                    </div>
                </div>

                <!-- Customer Info Read-only Labels -->
                <div x-show="selectedLeadId" x-transition class="pt-6 mt-6 border-t border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Customer Details Preview</h3>

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50 p-5 rounded-lg border border-slate-100">
                        <!-- Hidden inputs to still send data to backend -->
                        <input type="hidden" name="client_name" :value="receiver.client_name">
                        <input type="hidden" name="organisation_name" :value="receiver.organisation_name">
                        <input type="hidden" name="address" :value="receiver.address">
                        <input type="hidden" name="city" :value="receiver.city">
                        <input type="hidden" name="state" :value="receiver.state">
                        <input type="hidden" name="state_code" :value="receiver.state_code">
                        <input type="hidden" name="udyam_certificate" :value="receiver.udyam_certificate">
                        <input type="hidden" name="pan_no" :value="receiver.pan_no">
                        <input type="hidden" name="aadhar_no" :value="receiver.aadhar_no">
                        <input type="hidden" name="gstin_unique_id" :value="receiver.gstin_unique_id">

                        <!-- Preset generic fields for invoice meta -->
                        <input type="hidden" name="service_description_meta" :value="serviceDescriptionMeta">

                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Client Name</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1" x-text="receiver.client_name || '-'"></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Organisation Name</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1" x-text="receiver.organisation_name || '-'"></span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Address</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1" x-text="receiver.address || '-'"></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">City</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1" x-text="receiver.city || '-'"></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">State & Code</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1" x-text="(receiver.state || 'Karnataka') + ' (' + (receiver.state_code || '29') + ')' "></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">GSTIN</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1 uppercase" x-text="receiver.gstin_unique_id || '-'"></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">PAN No</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1 uppercase" x-text="receiver.pan_no || '-'"></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Aadhar No</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1 uppercase" x-text="receiver.aadhar_no || '-'"></span>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Udyam Est</span>
                            <span class="block text-sm font-medium text-slate-900 mt-1 uppercase" x-text="receiver.udyam_certificate || '-'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Services Configurator -->
            <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Description of Goods /
                    Services</h2>

                <p class="text-xs text-slate-500 mb-4 tracking-wide font-medium">Select a service to auto-fill details, or
                    manually overwrite as needed.</p>

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div
                            class="relative flex flex-col gap-4 p-5 pr-12 border border-slate-200 bg-slate-50 rounded-xl shadow-sm transition-all hover:bg-slate-100">

                            <!-- Absolute Delete Button on top-right -->
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                class="absolute top-4 right-4 text-slate-400 hover:text-rose-500 p-1.5 rounded-lg hover:bg-rose-50 transition-colors focus:outline-none"
                                title="Delete Service Row">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            <!-- Top row: Name and HSN -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-3">
                                    <label
                                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">SERVICE
                                        NAME / DESCRIPTION</label>
                                    <select x-model="item.service_name" @change="handleServiceSelection(index)"
                                        :name="'items['+index+'][service_name]'"
                                        class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px]"
                                        required>
                                        <option value="" disabled>Select a service...</option>
                                        <template x-for="(rate, name) in serviceRates" :key="name">
                                            <option :value="name" x-text="name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">HSN/SAC</label>
                                    <input type="text" x-model="item.hsn_sac" :name="'items['+index+'][hsn_sac]'"
                                        class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px]"
                                        placeholder="998599">
                                </div>
                            </div>

                            <!-- Bottom row: Qty, Rate, Total -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">QTY</label>
                                    <input type="number" x-model="item.qty" @input="handleMathChange(index)"
                                        :name="'items['+index+'][qty]'" min="0" step="0.01"
                                        class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px]">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">RATE
                                        (PER ITEM)</label>
                                    <input type="number" x-model="item.rate" @input="handleMathChange(index)"
                                        :name="'items['+index+'][rate]'" min="0" step="0.01"
                                        class="w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px]">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">TOTAL
                                        AMOUNT</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none"
                                            style="padding-left: 14px;">
                                            <span
                                                class="text-slate-400 font-medium transition-colors group-focus-within:text-indigo-500">&#8377;</span>
                                        </div>
                                        <input type="number" x-model="item.total" @input="handleFlatTotalChange(index)"
                                            :name="'items['+index+'][total]'" min="0" step="0.01"
                                            class="w-full rounded-lg border-slate-300 shadow-sm text-sm h-[42px] focus:border-indigo-500 focus:ring-indigo-500 bg-white font-bold text-slate-900 border-indigo-200"
                                            style="padding-left: 36px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addItem"
                        class="inline-flex items-center gap-2 mt-4 px-6 py-3 text-sm font-bold text-indigo-700 bg-white border-2 border-dashed border-indigo-100 hover:border-indigo-300 hover:bg-indigo-50 rounded-xl transition-all shadow-sm group">
                        <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Another Service Row
                    </button>
                </div>
            </div>

            <!-- Section: Dynamic Total Preview -->
            <div
                class="bg-slate-900 rounded-2xl p-8 lg:p-10 text-white shadow-2xl relative overflow-hidden ring-1 ring-white/10">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-600 rounded-full opacity-20 blur-3xl"></div>
                <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-emerald-600 rounded-full opacity-20 blur-3xl"></div>

                <div class="relative z-10">
                    <div class="flex justify-between items-end border-b border-white/10 pb-6 mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-white tracking-tight uppercase">Tax Summary <span
                                    class="text-indigo-400">Preview</span></h3>
                            <p class="text-sm text-indigo-300/80 mt-2 font-medium tracking-wide">Calculated instantly for
                                compliance verification.</p>
                        </div>
                    </div>

                    <div class="space-y-6 font-mono text-lg">
                        <div class="flex justify-between text-white/70">
                            <span class="uppercase tracking-widest text-xs font-bold leading-relaxed px-1">Taxable
                                Value</span>
                            <span class="font-bold text-slate-100"
                                x-text="'₹ ' + taxableValue.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>

                        <div x-show="taxType === 'local'" class="flex justify-between text-indigo-300">
                            <span class="uppercase tracking-widest text-xs font-bold leading-relaxed px-1">CGST (9%)</span>
                            <span class="font-bold"
                                x-text="'₹ ' + (taxableValue * 0.09).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>

                        <div x-show="taxType === 'local'" class="flex justify-between text-indigo-300">
                            <span class="uppercase tracking-widest text-xs font-bold leading-relaxed px-1">SGST (9%)</span>
                            <span class="font-bold"
                                x-text="'₹ ' + (taxableValue * 0.09).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>

                        <div x-show="taxType === 'outstation'" class="flex justify-between text-rose-300"
                            style="display: none;">
                            <span class="uppercase tracking-widest text-xs font-bold leading-relaxed px-1">IGST (18%)</span>
                            <span class="font-bold"
                                x-text="'₹ ' + (taxableValue * 0.18).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>

                        <div
                            class="flex justify-between text-lg sm:text-2xl lg:text-3xl font-black border-t-2 border-white/20 pt-4 mt-4 sm:pt-8 sm:mt-8 text-white">
                            <span class="uppercase tracking-wider">Grand Total</span>
                            <span class="text-indigo-300 drop-shadow-lg"
                                x-text="'₹ ' + calculateGrandTotal().toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-end items-stretch md:items-center gap-4 mt-12 pb-16">
                <a href="{{ route('invoices.index') }}"
                    class="inline-flex h-12 w-full md:w-auto min-w-[140px] items-center justify-center rounded-xl border border-slate-300 bg-white px-8 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 whitespace-nowrap">
                    Cancel Build
                </a>

                <button type="submit"
                    class="inline-flex h-12 w-full md:w-auto min-w-[220px] items-center justify-center rounded-xl bg-indigo-600 px-8 text-base font-semibold text-white shadow-md transition hover:bg-indigo-700 whitespace-nowrap">
                    <svg class="mr-2 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Finalize &amp; Generate PDF</span>
                </button>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('invoiceForm', () => ({
                taxType: 'local',
                selectedLeadId: '{{ request()->get('lead_id', '') }}',
                init() {
                    if (this.selectedLeadId) {
                        this.handleLeadChange();
                    }
                },
                serviceRates: {
                    "Marketing Services": 3000,
                    "SMS Campaign": 1500,
                    "RCS Campaign": 2500,
                    "Whatsapp Campaign": 4500
                },
                receiver: {
                    client_name: '',
                    organisation_name: '',
                    address: '',
                    city: '',
                    state: '',
                    state_code: '',
                    udyam_certificate: '',
                    pan_no: '',
                    aadhar_no: '',
                    gstin_unique_id: '',
                    purchase_order: '',
                },

                items: [
                    { service_name: '', custom_name: '', hsn_sac: '998599', qty: '', rate: '', total: '', calculatedMode: 'auto' }
                ],

                handleLeadChange() {
                    const id = parseInt(this.selectedLeadId);
                    if (!id) {
                        this.receiver = { client_name: '', organisation_name: '', address: '', city: '', state: '', state_code: '', udyam_certificate: '', pan_no: '', aadhar_no: '', gstin_unique_id: '', purchase_order: '' };
                        return;
                    }
                    const lead = window.leadsData.find(l => l.id === id);
                    if (lead) {
                        this.receiver = {
                            client_name: lead.client_name !== 'None' ? lead.client_name : '',
                            organisation_name: lead.organisation_name !== 'None' ? lead.organisation_name : '',
                            address: lead.address !== 'None' ? lead.address : '',
                            city: lead.city !== 'None' ? lead.city : '',
                            state: lead.state || 'Karnataka',
                            state_code: lead.state_code || '29',
                            udyam_certificate: lead.udyam_certificate !== 'None' ? lead.udyam_certificate : '',
                            pan_no: lead.pan_no !== 'None' ? lead.pan_no : '',
                            aadhar_no: lead.aadhar_no !== 'None' ? lead.aadhar_no : '',
                            gstin_unique_id: lead.gstin_unique_id !== 'None' ? lead.gstin_unique_id : '',
                            purchase_order: this.receiver.purchase_order || '',
                        };

                        const stateName = (this.receiver.state || '').toLowerCase().trim();
                        const stateCode = (this.receiver.state_code || '').trim();
                        if (stateName !== 'karnataka' || stateCode !== '29') {
                            this.taxType = 'outstation';
                        } else {
                            this.taxType = 'local';
                        }
                    }
                },

                handleServiceSelection(index) {
                    const item = this.items[index];
                    if (item.service_name && this.serviceRates[item.service_name]) {
                        item.rate = '';
                        item.qty = '';
                        item.calculatedMode = 'auto';
                        this.handleMathChange(index);
                    }
                },

                addItem() {
                    this.items.push({ service_name: '', custom_name: '', hsn_sac: '998599', qty: '', rate: '', total: '', calculatedMode: 'auto' });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },
                handleMathChange(index) {
                    const item = this.items[index];
                    const q = parseFloat(item.qty);
                    const r = parseFloat(item.rate);

                    if (!isNaN(q) && !isNaN(r)) {
                        item.total = (q * r).toFixed(2);
                    }
                },

                handleFlatTotalChange(index) {
                    // Logic removed to allow user to type freely
                },

                get taxableValue() {
                    return this.items.reduce((sum, item) => {
                        const val = parseFloat(item.total) || 0;
                        return sum + val;
                    }, 0);
                },

                calculateGrandTotal() {
                    const tv = this.taxableValue;
                    const taxMultiplier = 0.18; // Fixed 18% GST (either split or flat)
                    return tv + (tv * taxMultiplier);
                },

                get serviceDescriptionMeta() {
                    const names = [...new Set(this.items
                        .map(item => item.service_name)
                        .filter(name => name))];
                    
                    if (names.length === 0) return 'Marketing Services';
                    if (names.length === 1) return names[0];
                    if (names.length === 2) return names.join(' & ');
                    
                    // For 3 or more: "Service A, Service B & Service C"
                    const last = names.pop();
                    return names.join(', ') + ' & ' + last;
                }
            }))
        })
    </script>
@endsection