@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manage Vendor P.O</h1>
                <p class="text-slate-500 mt-1">Generate PO Vouchers and send them to vendors</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="vendorPoForm()">
            <script>
                window.leadsData = {!! $leads->toJson() !!};
            </script>
            <div class="p-8">
                <form action="{{ route('manage_po.vendor_po.store') }}" method="POST" id="vendorPoForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Select Vendor -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Select Vendor / Lead <span class="text-slate-400 font-normal text-xs">(Source: Vendor KYC & P.O)</span></label>
                            <div x-data="{ 
                                        open: false, 
                                        search: '',
                                        get filteredLeads() {
                                            if (this.search === '') return window.leadsData;
                                            return window.leadsData.filter(l => {
                                                const term = this.search.toLowerCase();
                                                const name = (l.customer?.client_name || l.customer_name || '').toLowerCase();
                                                const email = (l.email_id || '').toLowerCase();
                                                const recordId = String(l.record_id || '').toLowerCase();
                                                const customerId = String(l.customer_id || '').toLowerCase();
                                                return name.includes(term) || email.includes(term) || recordId.includes(term) || customerId.includes(term);
                                            });
                                        },
                                        get selectedLabel() {
                                            if (!selectedLeadId) return '-- Choose an Existing Vendor --';
                                            const lead = window.leadsData.find(l => String(l.id) === String(selectedLeadId));
                                            if (!lead) return '-- Choose an Existing Vendor --';
                                            const name = lead.customer?.client_name || lead.customer_name;
                                            return `${name} (${lead.email_id}) - ${lead.customer_id}`;
                                        }
                                    }" @click.outside="open = false" class="relative w-full">

                                <input type="hidden" name="lead_id" x-model="selectedLeadId" required>

                                <!-- Dropdown Trigger -->
                                <button type="button" @click="open = !open"
                                    class="w-full text-left rounded-xl border border-slate-200 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[46px] px-4 flex items-center justify-between bg-slate-50/50 focus:outline-none transition-all"
                                    :class="selectedLeadId ? 'text-slate-900 border-indigo-200 bg-indigo-50/30' : 'text-slate-500'">
                                    <span x-text="selectedLabel" class="truncate block font-medium"></span>
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
                                    <div class="p-2 border-b border-slate-100 bg-slate-50 relative z-10 shadow-sm">
                                        <input type="text" x-model="search" x-ref="searchInput"
                                            placeholder="Search by Vendor Name, Email or ID..."
                                            class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white"
                                            @focus="open = true" @keydown.escape="open = false"
                                            x-init="$watch('open', value => { if (value) setTimeout(() => $refs.searchInput.focus(), 50) })">
                                    </div>
                                    <div class="overflow-y-auto max-h-64 flex-1 p-1">
                                        <template x-for="lead in filteredLeads" :key="'lead-'+lead.id">
                                            <button type="button"
                                                @click="selectedLeadId = lead.id; email_id = lead.email_id; open = false; search = ''"
                                                class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-indigo-50 transition-all focus:outline-none flex items-center justify-between group"
                                                :class="String(selectedLeadId) === String(lead.id) ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700'">
                                                <div class="flex flex-col">
                                                    <span x-text="lead.customer?.client_name || lead.customer_name" class="font-semibold"></span>
                                                    <span x-text="lead.email_id" class="text-xs text-slate-500"></span>
                                                </div>
                                                <span class="text-xs font-mono px-2 py-0.5 rounded-md transition-colors"
                                                    :class="String(selectedLeadId) === String(lead.id) ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'"
                                                    x-text="lead.customer_id"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredLeads.length === 0"
                                            class="px-4 py-6 text-center text-sm text-slate-500 bg-slate-50">
                                            No vendors found matching your search.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('lead_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- PO Number (Automatic) -->
                        <div>
                            <label for="po_number" class="block text-sm font-semibold text-slate-700 mb-2">PO Number <span class="text-slate-400 font-normal text-xs">(Auto-generated)</span></label>
                            <input type="text" id="po_number" value="{{ $nextPoNumber }}" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed outline-none"
                                readonly>
                            <p class="text-[10px] text-slate-400 mt-1">This number is automatically incremented on submission.</p>
                        </div>

                        <!-- Date of PO -->
                        <div>
                            <label for="po_date" class="block text-sm font-semibold text-slate-700 mb-2">Date of PO <span class="text-rose-500">*</span></label>
                            <input type="date" name="po_date" id="po_date" value="{{ old('po_date', date('Y-m-d')) }}" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none bg-slate-50/50"
                                required>
                            @error('po_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email ID -->
                        <div>
                            <label for="email_id" class="block text-sm font-semibold text-slate-700 mb-2">Email ID <span class="text-rose-500">*</span></label>
                            <input type="email" name="email_id" id="email_id" x-model="email_id" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none bg-slate-50/50"
                                placeholder="Enter email address" required>
                            @error('email_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Volume -->
                        <div>
                            <label for="volume_records" class="block text-sm font-semibold text-slate-700 mb-2">Volume (Records) <span class="text-rose-500">*</span></label>
                            <input type="number" name="volume_records" id="volume_records" value="{{ old('volume_records') }}" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none bg-slate-50/50"
                                placeholder="Enter volume" min="1" required>
                            @error('volume_records') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Data Category -->
                        <div class="relative" x-data="{ open: false }">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Data Category <span class="text-rose-500">*</span></label>
                            <button @click="open = !open" type="button" 
                                class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:border-slate-300 transition-all text-left">
                                <span id="dataCategoryText" class="text-slate-600">Select categories</span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" 
                                class="absolute z-10 w-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 p-4 space-y-3">
                                @foreach(['Salaried Individuals', 'Business Owners', 'Car', 'Other'] as $cat)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="data_category[]" value="{{ $cat }}" class="data-category-checkbox w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">{{ $cat }}</span>
                                </label>
                                @endforeach
                                <div id="dataCategoryOtherWrap" class="hidden mt-2">
                                    <input type="text" name="data_category_other_text" id="data_category_other_text" 
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Please specify other category">
                                </div>
                            </div>
                            <div id="dataCategoryPreview" class="text-xs text-indigo-600 font-medium mt-2">No category selected</div>
                            @error('data_category') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Geography Filter -->
                        <div class="relative" x-data="{ open: false }">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Geography / Location Filter <span class="text-rose-500">*</span></label>
                            <button @click="open = !open" type="button" 
                                class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:border-slate-300 transition-all text-left">
                                <span id="geographyText" class="text-slate-600">Select locations</span>
                                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" 
                                class="absolute z-10 w-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 p-4 space-y-3">
                                @foreach(['Pan-India', 'Major Metros', '2 Tier', '3 Tier', 'Other'] as $geo)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="geography_filter[]" value="{{ $geo }}" class="geography-checkbox w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">{{ $geo }}</span>
                                </label>
                                @endforeach
                                <div id="geographyOtherWrap" class="hidden mt-2">
                                    <input type="text" name="geography_filter_other_text" id="geography_filter_other_text" 
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Please specify other location">
                                </div>
                            </div>
                            <div id="geographyPreview" class="text-xs text-indigo-600 font-medium mt-2">No geography selected</div>
                            @error('geography_filter') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Amount -->
                        <div>
                            <label for="excluded_amount" class="block text-sm font-semibold text-slate-700 mb-2">Total Amount (excl. GST) <span class="text-rose-500">*</span></label>
                            <div class="flex items-stretch shadow-sm rounded-xl overflow-hidden">
                                <div class="flex items-center justify-center px-4 bg-slate-50 border border-r-0 border-slate-200 text-slate-400 font-medium">
                                    ₹
                                </div>
                                <input type="number" name="excluded_amount" id="excluded_amount" value="{{ old('excluded_amount') }}" 
                                    class="w-full px-4 py-2.5 border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none bg-slate-50/50 rounded-r-xl"
                                    placeholder="Enter amount" min="1" step="0.01" required>
                            </div>
                            @error('excluded_amount') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                            <span>Submit Vendor PO</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function vendorPoForm() {
    return {
        email_id: '{{ old('email_id') }}',
        selectedLeadId: '{{ old('lead_id', request('lead_id')) }}',
        init() {
            if (this.selectedLeadId) {
                const lead = window.leadsData.find(l => String(l.id) === String(this.selectedLeadId));
                if (lead) {
                    this.email_id = lead.email_id;
                }
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Data Category Multi-select
    const catCheckboxes = document.querySelectorAll('.data-category-checkbox');
    const catPreview = document.getElementById('dataCategoryPreview');
    const catOtherWrap = document.getElementById('dataCategoryOtherWrap');

    function updateCatPreview() {
        const selected = Array.from(catCheckboxes)
            .filter(i => i.checked)
            .map(i => i.value);
        
        catPreview.textContent = selected.length ? selected.join(', ') : 'No category selected';
        catOtherWrap.classList.toggle('hidden', !selected.includes('Other'));
    }

    catCheckboxes.forEach(i => i.addEventListener('change', updateCatPreview));

    // Geography Multi-select
    const geoCheckboxes = document.querySelectorAll('.geography-checkbox');
    const geoPreview = document.getElementById('geographyPreview');
    const geoOtherWrap = document.getElementById('geographyOtherWrap');

    function updateGeoPreview() {
        const selected = Array.from(geoCheckboxes)
            .filter(i => i.checked)
            .map(i => i.value);
        
        geoPreview.textContent = selected.length ? selected.join(', ') : 'No geography selected';
        geoOtherWrap.classList.toggle('hidden', !selected.includes('Other'));
    }

    geoCheckboxes.forEach(i => i.addEventListener('change', updateGeoPreview));
});
</script>
@endsection
