@csrf
@php
    $isAdmin = auth()->user()->isAdmin();
@endphp

<div class="space-y-8">

    <!-- Card 1: Customer Details -->
    <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-100 shadow-sm">
        <h3 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b border-indigo-200 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Customer Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-2 lg:col-span-1">
                <label for="customer_name" class="block text-sm font-medium text-slate-700 mb-1">Customer Name <span
                        class="text-red-500">*</span></label>
                <input type="text" name="customer_name" id="customer_name"
                    value="{{ old('customer_name', $lead->customer_name ?? '') }}" required
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                @error('customer_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="mobile" class="block text-sm font-medium text-slate-700 mb-1">Mobile No</label>
                @can('lead-contact-view')
                    <input type="text" name="mobile" id="mobile" value="{{ old('mobile', $lead->mobile ?? '') }}"
                        class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                    @error('mobile') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                @else
                    <input type="hidden" name="mobile" value="{{ old('mobile', $lead->mobile ?? '') }}">
                    <input type="text" disabled value="********" class="w-full h-11 px-4 rounded-lg border-slate-300 bg-slate-100 text-slate-500 shadow-sm cursor-not-allowed">
                @endcan
            </div>
            <div>
                <label for="email_id" class="block text-sm font-medium text-slate-700 mb-1">Email ID</label>
                @can('lead-contact-view')
                    <input type="email" name="email_id" id="email_id" value="{{ old('email_id', $lead->email_id ?? '') }}"
                        class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                    @error('email_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                @else
                    <input type="hidden" name="email_id" value="{{ old('email_id', $lead->email_id ?? '') }}">
                    <input type="text" disabled value="********" class="w-full h-11 px-4 rounded-lg border-slate-300 bg-slate-100 text-slate-500 shadow-sm cursor-not-allowed">
                @endcan
            </div>
            <div>
                <label for="reference" class="block text-sm font-medium text-slate-700 mb-1">Reference</label>
                <input type="text" name="reference" id="reference"
                    value="{{ old('reference', $lead->reference ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div>
                <label for="city" class="block text-sm font-medium text-slate-700 mb-1">Place/City</label>
                <input type="text" name="city" id="city" value="{{ old('city', $lead->city ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div class="lg:col-span-1">
                <label for="company_type" class="block text-sm font-medium text-slate-700 mb-1">Company Type</label>
                <input type="text" name="company_type" id="company_type"
                    value="{{ old('company_type', $lead->company_type ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div>
                <label for="designation" class="block text-sm font-medium text-slate-700 mb-1">Designation</label>
                <input type="text" name="designation" id="designation"
                    value="{{ old('designation', $lead->designation ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div>
                <label for="contact_person" class="block text-sm font-medium text-slate-700 mb-1">Contact Person</label>
                @php
                    $contactVal = old('contact_person', $lead->contact_person ?? '');
                    $hasMobile = preg_match('/[0-9]{7,}/', preg_replace('/[^0-9]/', '', $contactVal));
                @endphp
                @if($hasMobile && !auth()->user()->can('lead-contact-view'))
                    <input type="hidden" name="contact_person" value="{{ $contactVal }}">
                    <input type="text" disabled value="********" class="w-full h-11 px-4 rounded-lg border-slate-300 bg-slate-100 text-slate-500 shadow-sm cursor-not-allowed">
                @else
                    <input type="text" name="contact_person" id="contact_person"
                        value="{{ $contactVal }}"
                        class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                @endif
            </div>
        </div>
    </div>

    <!-- Card 2: Leads Details -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b border-slate-200 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                </path>
            </svg>
            Leads Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center h-11 mt-6">
                <input type="hidden" name="master_service_agreement_signed" value="0">
                <input type="checkbox" name="master_service_agreement_signed" id="master_service_agreement_signed"
                    value="1" {{ old('master_service_agreement_signed', $lead->master_service_agreement_signed ?? 0) ? 'checked' : '' }}
                    class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-5 w-5">
                <label for="master_service_agreement_signed" class="ml-2 block text-sm font-medium text-slate-700">MSA
                    Signed</label>
            </div>
            <div>
                <label for="lead_status" class="block text-sm font-medium text-slate-700 mb-1">Lead Status</label>
                <select name="lead_status" id="lead_status"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                    <option value="Active" {{ old('lead_status', $lead->lead_status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Non Active" {{ old('lead_status', $lead->lead_status ?? '') == 'Non Active' ? 'selected' : '' }}>Non Active</option>
                </select>
            </div>
            <div>
                <label for="kyc" class="block text-sm font-medium text-slate-700 mb-1">KYC</label>
                <select name="kyc" id="kyc"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                    <option value="Not Done" {{ old('kyc', $lead->kyc ?? '') == 'Not Done' ? 'selected' : '' }}>Not Done
                    </option>
                    <option value="Done" {{ old('kyc', $lead->kyc ?? '') == 'Done' ? 'selected' : '' }}>Done</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Card 3: Contact Info -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b border-slate-200 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
            </svg>
            Contact Info
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="alternate_mobile" class="block text-sm font-medium text-slate-700 mb-1">Alternate
                    Mobile</label>
                @can('lead-contact-view')
                    <input type="text" name="alternate_mobile" id="alternate_mobile"
                        value="{{ old('alternate_mobile', $lead->alternate_mobile ?? '') }}"
                        class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                @else
                    <input type="hidden" name="alternate_mobile" value="{{ old('alternate_mobile', $lead->alternate_mobile ?? '') }}">
                    <input type="text" disabled value="********" class="w-full h-11 px-4 rounded-lg border-slate-300 bg-slate-100 text-slate-500 shadow-sm cursor-not-allowed">
                @endcan
            </div>
            <div>
                <label for="alternate_email_id" class="block text-sm font-medium text-slate-700 mb-1">Alternate
                    Email</label>
                @can('lead-contact-view')
                    <input type="email" name="alternate_email_id" id="alternate_email_id"
                        value="{{ old('alternate_email_id', $lead->alternate_email_id ?? '') }}"
                        class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                @else
                    <input type="hidden" name="alternate_email_id" value="{{ old('alternate_email_id', $lead->alternate_email_id ?? '') }}">
                    <input type="text" disabled value="********" class="w-full h-11 px-4 rounded-lg border-slate-300 bg-slate-100 text-slate-500 shadow-sm cursor-not-allowed">
                @endcan
            </div>
        </div>
    </div>

    <!-- Card 4: Company Info -->
    @can('company-info-section')
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b border-slate-200 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                </path>
            </svg>
            Company Info
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{
            stateName: '{{ old('state_name', $lead->customer->state_name ?? '') }}',
            stateCode: '{{ old('state_code', $lead->customer->state_code ?? '') }}',
            updateStateCode(e) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const code = selectedOption.getAttribute('data-code');
                if (code) {
                    this.stateCode = code;
                } else if (!this.stateName) {
                    this.stateCode = '';
                }
            }
        }">
            <div>
                <label for="company_name" class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                <input type="text" name="company_name" id="company_name"
                    value="{{ old('company_name', $lead->company_name ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div class="md:col-span-2">
                <label for="company_address" class="block text-sm font-medium text-slate-700 mb-1">Company Address</label>
                <textarea name="company_address" id="company_address" rows="1"
                    class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">{{ old('company_address', $lead->company_address ?? '') }}</textarea>
            </div>
            <div>
                <label for="state_name" class="block text-sm font-medium text-slate-700 mb-1">State Name</label>
                <select name="state_name" id="state_name" x-model="stateName" @change="updateStateCode"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                      <option value="">Select State</option>
                      <option value="Jammu and Kashmir" data-code="01">Jammu and Kashmir - 01</option>
                        <option value="Himachal Pradesh" data-code="02">Himachal Pradesh - 02</option>
                        <option value="Punjab" data-code="03">Punjab - 03</option>
                        <option value="Chandigarh" data-code="04">Chandigarh - 04</option>
                        <option value="Uttarakhand" data-code="05">Uttarakhand - 05</option>
                        <option value="Haryana" data-code="06">Haryana - 06</option>
                        <option value="Delhi" data-code="07">Delhi - 07</option>
                        <option value="Rajasthan" data-code="08">Rajasthan - 08</option>
                        <option value="Uttar Pradesh" data-code="09">Uttar Pradesh - 09</option>
                        <option value="Bihar" data-code="10">Bihar - 10</option>
                        <option value="Sikkim" data-code="11">Sikkim - 11</option>
                        <option value="Arunachal Pradesh" data-code="12">Arunachal Pradesh - 12</option>
                        <option value="Nagaland" data-code="13">Nagaland - 13</option>
                        <option value="Manipur" data-code="14">Manipur - 14</option>
                        <option value="Mizoram" data-code="15">Mizoram - 15</option>
                        <option value="Tripura" data-code="16">Tripura - 16</option>
                        <option value="Meghalaya" data-code="17">Meghalaya - 17</option>
                        <option value="Assam" data-code="18">Assam - 18</option>
                        <option value="West Bengal" data-code="19">West Bengal - 19</option>
                        <option value="Jharkhand" data-code="20">Jharkhand - 20</option>
                        <option value="Odisha" data-code="21">Odisha - 21</option>
                        <option value="Chhattisgarh" data-code="22">Chhattisgarh - 22</option>
                        <option value="Madhya Pradesh" data-code="23">Madhya Pradesh - 23</option>
                        <option value="Gujarat" data-code="24">Gujarat - 24</option>
                        <option value="Dadra and Nagar Haveli and Daman and Diu" data-code="26">Dadra and Nagar Haveli and Daman and Diu - 26</option>
                        <option value="Maharashtra" data-code="27">Maharashtra - 27</option>
                        <option value="Karnataka" data-code="29">Karnataka - 29</option>
                        <option value="Goa" data-code="30">Goa - 30</option>
                        <option value="Lakshadweep" data-code="31">Lakshadweep - 31</option>
                        <option value="Kerala" data-code="32">Kerala - 32</option>
                        <option value="Tamil Nadu" data-code="33">Tamil Nadu - 33</option>
                        <option value="Puducherry" data-code="34">Puducherry - 34</option>
                        <option value="Andaman and Nicobar Islands" data-code="35">Andaman and Nicobar Islands - 35</option>
                        <option value="Telangana" data-code="36">Telangana - 36</option>
                        <option value="Andhra Pradesh" data-code="37">Andhra Pradesh - 37</option>
                        <option value="Ladakh" data-code="38">Ladakh - 38</option>
                        <option value="Other Territory" data-code="97">Other Territory - 97</option>
                        <option value="Central Jurisdiction" data-code="99">Central Jurisdiction - 99</option>
                </select>
            </div>
            <div>
                <label for="state_code" class="block text-sm font-medium text-slate-700 mb-1">State Code</label>
                <input type="text" name="state_code" id="state_code" x-model="stateCode"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm uppercase">
            </div>
            <div>
                <label for="website" class="block text-sm font-medium text-slate-700 mb-1">Website</label>
                <input type="text" name="website" id="website" value="{{ old('website', $lead->website ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div>
                <label for="gst_no" class="block text-sm font-medium text-slate-700 mb-1">GST No</label>
                <input type="text" name="gst_no" id="gst_no" value="{{ old('gst_no', $lead->gst_no ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm uppercase">
            </div>
            <div>
                <label for="pan_number" class="block text-sm font-medium text-slate-700 mb-1">PAN Number</label>
                <input type="text" name="pan_number" id="pan_number"
                    value="{{ old('pan_number', $lead->pan_number ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm uppercase">
            </div>
            <div>
                <label for="aadhar_no" class="block text-sm font-medium text-slate-700 mb-1">Aadhar No</label>
                <input type="text" name="aadhar_no" id="aadhar_no"
                    value="{{ old('aadhar_no', $lead->aadhar_no ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm uppercase">
            </div>
            <div>
                <label for="udyam_registration_certificate" class="block text-sm font-medium text-slate-700 mb-1">Udyam Est</label>
                <input type="text" name="udyam_registration_certificate" id="udyam_registration_certificate"
                    value="{{ old('udyam_registration_certificate', $lead->udyam_registration_certificate ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm uppercase">
            </div>
        </div>
    </div>
    @else
        <input type="hidden" name="company_name" value="{{ old('company_name', $lead->company_name ?? '') }}">
        <input type="hidden" name="company_address" value="{{ old('company_address', $lead->company_address ?? '') }}">
        <input type="hidden" name="state_name" value="{{ old('state_name', $lead->customer->state_name ?? '') }}">
        <input type="hidden" name="state_code" value="{{ old('state_code', $lead->customer->state_code ?? '') }}">
        <input type="hidden" name="website" value="{{ old('website', $lead->website ?? '') }}">
        <input type="hidden" name="gst_no" value="{{ old('gst_no', $lead->gst_no ?? '') }}">
        <input type="hidden" name="pan_number" value="{{ old('pan_number', $lead->pan_number ?? '') }}">
        <input type="hidden" name="aadhar_no" value="{{ old('aadhar_no', $lead->aadhar_no ?? '') }}">
        <input type="hidden" name="udyam_registration_certificate" value="{{ old('udyam_registration_certificate', $lead->udyam_registration_certificate ?? '') }}">
    @endcan

    <!-- Card 5: Client Order Info -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b border-slate-200 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
            Client Order Info
        </h3>

        @php
            $predefinedIndustries = ['PL', 'BL', 'HL', 'Real Estate', 'Education', 'NGO', 'Insurance'];
            $currentIndustry = old('nature_of_industry', $lead->nature_of_industry ?? '');
            $isOtherIndustry = !empty($currentIndustry) && !in_array($currentIndustry, $predefinedIndustries);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ 
                 industrySelect: '{{ $isOtherIndustry ? 'Other' : ($currentIndustry ?: '') }}',
                 industryText: '{{ $isOtherIndustry ? $currentIndustry : '' }}'
             }">

            <div class="md:col-span-2 lg:col-span-1 border border-transparent">
                <label for="nature_of_industry_select" class="block text-sm font-medium text-slate-700 mb-1">Nature of
                    Industry</label>
                <select id="nature_of_industry_select" x-model="industrySelect"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm mb-2">
                    <option value="">Select Industry</option>
                    @foreach($predefinedIndustries as $ind)
                        <option value="{{ $ind }}">{{ $ind }}</option>
                    @endforeach
                    <option value="Other">Any Other (Please Specify)</option>
                </select>

                <div x-show="industrySelect === 'Other'" x-transition class="mt-2">
                    <input type="text" x-model="industryText" placeholder="Specify other industry"
                        class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                </div>

                <!-- Hidden input that actually submits the value -->
                <input type="hidden" name="nature_of_industry"
                    :value="industrySelect === 'Other' ? industryText : industrySelect">
            </div>

            @php
                $leadCustomerType = $lead->customer_type ?? null;
                $currentCustomerType = old('customer_type', empty($leadCustomerType) ? 'Enquiry' : $leadCustomerType);
            @endphp
            <div>
                <label for="customer_type" class="block text-sm font-medium text-slate-700 mb-1">Customer Type</label>
                <select name="customer_type" id="customer_type"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
                    <option value="">Select Type</option>
                    <option value="Enquiry" {{ $currentCustomerType == 'Enquiry' ? 'selected' : '' }}>Enquiry</option>
                    <option value="1st Time" {{ $currentCustomerType == '1st Time' ? 'selected' : '' }}>1st Time</option>
                    <option value="Loyal" {{ $currentCustomerType == 'Loyal' ? 'selected' : '' }}>Loyal</option>
                    <option value="Premium" {{ $currentCustomerType == 'Premium' ? 'selected' : '' }}>Premium</option>
                    <option value="Discount/Bargain Hunter" {{ $currentCustomerType == 'Discount/Bargain Hunter' ? 'selected' : '' }}>Discount/Bargain Hunter</option>
                    <option value="Need Base" {{ $currentCustomerType == 'Need Base' ? 'selected' : '' }}>Need Base
                    </option>
                    <option value="Unqualified" {{ $currentCustomerType == 'Unqualified' ? 'selected' : '' }}>Unqualified
                    </option>
                </select>
            </div>
            @php
                $initialProductInterest = old('initial_product_interest', $lead->initial_product_interest ?? '');
                if (is_array($initialProductInterest)) {
                    $selectedProducts = $initialProductInterest;
                } else {
                    $selectedProducts = explode(', ', (string) $initialProductInterest);
                }
                // Filter out empty values
                $selectedProducts = array_filter(array_map('trim', $selectedProducts));
            @endphp
            <div class="md:col-span-1 lg:col-span-1" x-data="{
                open: false,
                options: {{ json_encode(\App\Models\Lead::PRODUCT_INTEREST_OPTIONS) }},
                selected: {{ json_encode(array_values($selectedProducts)) }},
                toggle(option) {
                    if (this.selected.includes(option)) {
                        this.selected = this.selected.filter(i => i !== option);
                    } else {
                        if (option !== '' && !this.selected.includes(option)) this.selected.push(option);
                    }
                },
                get displayText() {
                    return this.selected.filter(i => i !== '').join(', ') || 'Select Products';
                }
            }">
                <label class="block text-sm font-medium text-slate-700 mb-1">Initial Product Interest</label>
                <div class="relative" @click.away="open = false">
                    <button type="button" @click="open = !open"
                        class="w-full h-11 px-4 text-left rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 bg-white flex items-center justify-between shadow-sm">
                        <span class="truncate text-slate-700" x-text="displayText"></span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="open" x-transition
                        class="absolute z-50 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-xl py-1 max-h-60 overflow-y-auto">
                        <template x-for="option in options" :key="option">
                            <label class="flex items-center px-4 py-2.5 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" :value="option" :checked="selected.includes(option)"
                                    @change="toggle(option)"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4.5 w-4.5 transition duration-150">
                                <span class="ml-4 text-sm font-medium text-slate-700" x-text="option"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Explicit hidden inputs for Laravel to pick up the array -->
                <template x-for="item in selected" :key="item">
                    <input type="hidden" name="initial_product_interest[]" :value="item">
                </template>
            </div>
            <div>
                <label for="product_demand" class="block text-sm font-medium text-slate-700 mb-1">Product Demand</label>
                <input type="text" name="product_demand" id="product_demand"
                    value="{{ old('product_demand', $lead->product_demand ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">Quantity</label>
                <input type="text" name="quantity" id="quantity" value="{{ old('quantity', $lead->quantity ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <div class="md:col-span-2 lg:col-span-3">
                <label for="rate" class="block text-sm font-medium text-slate-700 mb-1">Rate (₹)</label>
                <textarea name="rate" id="rate" rows="3"
                    class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">{{ old('rate', $lead->rate ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2 lg:col-span-3 mt-2">
                <label for="comment" class="block text-sm font-medium text-slate-700 mb-1">Lead Comment</label>
                <textarea name="comment" id="comment" rows="6"
                    class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">{{ old('comment', $lead->comment ?? '') }}</textarea>
            </div>

            @if($isAdmin)
                <div class="border-t border-slate-100 pt-6 mt-6 md:col-span-2 lg:col-span-3">
                    <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-4">Admin Only Fields</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="md:col-span-2 lg:col-span-3">
                            <label for="admin_rate" class="block text-sm font-medium text-slate-700 mb-1">Admin Rate (₹)</label>
                            <textarea name="admin_rate" id="admin_rate" rows="3"
                                class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm bg-indigo-50/30">{{ old('admin_rate', $lead->admin_rate ?? '') }}</textarea>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3 mt-2">
                            <label for="admin_comment" class="block text-sm font-medium text-slate-700 mb-1">Admin Lead Comment</label>
                            <textarea name="admin_comment" id="admin_comment" rows="6"
                                class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm bg-indigo-50/30">{{ old('admin_comment', $lead->admin_comment ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Card 6: Tracking Info -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b border-slate-200 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Tracking Info
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="follow_up_date" class="block text-sm font-medium text-slate-700 mb-1">Follow Up Date</label>
                <input type="date" name="follow_up_date" id="follow_up_date"
                    value="{{ old('follow_up_date', isset($lead->follow_up_date) ? \Carbon\Carbon::parse($lead->follow_up_date)->format('Y-m-d') : '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div>
                <label for="previous_deals_and_date" class="block text-sm font-medium text-slate-700 mb-1">Previous
                    Deals Date</label>
                <input type="date" name="previous_deals_and_date" id="previous_deals_and_date"
                    value="{{ old('previous_deals_and_date', isset($lead->previous_deals_and_date) ? \Carbon\Carbon::parse($lead->previous_deals_and_date)->format('Y-m-d') : '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
            <div>
                <label for="records_owner" class="block text-sm font-medium text-slate-700 mb-1">Records Owner</label>
                <input type="text" name="records_owner" id="records_owner"
                    value="{{ old('records_owner', $lead->records_owner ?? '') }}"
                    class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm">
            </div>
        </div>
    </div>

    <!-- Card 7: Document Uploads -->
    @can('document-section')
        <div class="bg-indigo-50 p-6 rounded-xl border border-slate-200 shadow-sm mt-8">
            <h3 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b border-indigo-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Document Uploads
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $isVendor = isset($lead) && str_contains(strtoupper($lead->creation_source ?? ''), 'VENDOR');

                    $documents = [
                        'doc_pan' => 'PAN Card',
                        'doc_aadhar' => 'Aadhar Card',
                        'doc_gst' => 'GST Certificate',
                        'doc_certificate_incorporation_udyam' => 'Udyam Est / Incorporation',
                    ];

                    if (!$isVendor) {
                        $documents['doc_trai_dlt'] = 'Trai DLT';
                        $documents['doc_dsa_license'] = 'DSA License';
                        $documents['doc_company_id_card'] = 'Company ID Card';
                    }

                    $documents['msa_document'] = 'MSA Document';
                @endphp

                @foreach($documents as $field => $label)
                    <div class="bg-white p-4 rounded-lg border border-slate-200 hover:border-indigo-300 transition-colors">
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ $label }}</label>
                        <input type="file" name="{{ $field }}" id="{{ $field }}" class="block w-full text-sm text-slate-500
                                                                    file:mr-4 file:py-2 file:px-4
                                                                    file:rounded-md file:border-0
                                                                    file:text-sm file:font-semibold
                                                                    file:bg-indigo-50 file:text-indigo-700
                                                                    hover:file:bg-indigo-100 cursor-pointer transition-colors
                                                                ">
                        @if(isset($lead) && $lead->$field)
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-emerald-600 flex items-center gap-1"><svg class="w-4 h-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                        </path>
                                    </svg> Uploaded</span>
                                @if(auth()->user()->isAdmin())
                                    @php
                                        $viewUrl = Str::startsWith($lead->$field, ['http://', 'https://']) ? $lead->$field : '';
                                        if (empty($viewUrl)) {
                                            if (request()->getHost() === 'localhost' || request()->getHost() === '127.0.0.1') {
                                                // Local XAMPP path
                                                $viewUrl = '/crm-billions/storage/app/public/' . $lead->$field;
                                            } else {
                                                // Production path
                                                $viewUrl = 'https://billionsunited.com/crm/storage/app/public/' . $lead->$field;
                                            }
                                        }
                                    @endphp
                                    <a href="{{ $viewUrl }}" target="_blank"
                                        class="text-indigo-600 hover:text-indigo-800 underline font-medium">View File</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endcan
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ocrInputs = ['doc_pan', 'doc_aadhar', 'doc_gst', 'doc_certificate_incorporation_udyam'];
    
    ocrInputs.forEach(inputId => {
        const fileInput = document.getElementById(inputId);
        if (!fileInput) return;

        fileInput.addEventListener('change', function(e) {
            if (!this.files || this.files.length === 0) return;
            
            const file = this.files[0];
            const maxSizeBytes = 1024 * 1024; // 1 MB

            // Only show warning for PDF files > 1MB, because images are compressed automatically on the backend
            if (file.type === 'application/pdf' && file.size > maxSizeBytes) {
                alert('Warning: This PDF file is larger than 1 MB. Automatic extraction (OCR) will not work. Please upload an Image (JPG/PNG) instead, or you will need to manually enter the document number.');
            }
        });
    });
});
</script>
