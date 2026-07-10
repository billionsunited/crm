@extends('layouts.app')
@section('header', 'Add Campaign Lead')

@section('content')
<div class="max-w-4xl mx-auto">
    <style>
        @media (max-width: 639px) {
            .campaign-header-mobile {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 1rem !important;
            }
        }
    </style>
    <div class="flex items-center justify-between mb-8 campaign-header-mobile">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Add New Campaign Lead</h1>
            <p class="text-slate-500 mt-1">Fill in the details to add a new lead for campaigns.</p>
        </div>
        <a href="{{ route('campaign-leads.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-semibold transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to List
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('campaign-leads.store') }}" method="POST" class="p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Name -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Customer Name</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                        class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Full Name">
                </div>

                <!-- Reference -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Reference</label>
                    <input type="text" name="reference" value="{{ old('reference') }}"
                        class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Reference (e.g., John Doe)">
                </div>

                <!-- Mobiles -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Primary Mobile</label>
                    @can('enquiry-vendor-contact-view')
                        <input type="text" name="mobile" value="{{ old('mobile') }}"
                            class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            placeholder="9876543210">
                    @else
                        <input type="hidden" name="mobile" value="{{ old('mobile') }}">
                        <input type="text" disabled value="********" class="block w-full h-12 px-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                    @endcan
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Mobile 1</label>
                    @can('enquiry-vendor-contact-view')
                        <input type="text" name="mobile_1" value="{{ old('mobile_1') }}"
                            class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            placeholder="Alternate Mobile">
                    @else
                        <input type="hidden" name="mobile_1" value="{{ old('mobile_1') }}">
                        <input type="text" disabled value="********" class="block w-full h-12 px-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                    @endcan
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Mobile 2</label>
                    @can('enquiry-vendor-contact-view')
                        <input type="text" name="mobile_2" value="{{ old('mobile_2') }}"
                            class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            placeholder="Alternate Mobile">
                    @else
                        <input type="hidden" name="mobile_2" value="{{ old('mobile_2') }}">
                        <input type="text" disabled value="********" class="block w-full h-12 px-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                    @endcan
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                    @can('enquiry-vendor-contact-view')
                        <input type="email" name="email_id" value="{{ old('email_id') }}"
                            class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            placeholder="primary@example.com">
                    @else
                        <input type="hidden" name="email_id" value="{{ old('email_id') }}">
                        <input type="text" disabled value="********" class="block w-full h-12 px-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                    @endcan
                </div>

                <!-- Alternate Email -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Alternate Email</label>
                    @can('enquiry-vendor-contact-view')
                        <input type="email" name="email_id_1" value="{{ old('email_id_1') }}"
                            class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            placeholder="alternate@example.com">
                    @else
                        <input type="hidden" name="email_id_1" value="{{ old('email_id_1') }}">
                        <input type="text" disabled value="********" class="block w-full h-12 px-4 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                    @endcan
                </div>

                <!-- Company -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Acme Corp">
                </div>

                <!-- Firm Type -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Type of Firm</label>
                    <select name="type_of_firm" class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Select Type</option>
                        @foreach(App\Models\CampaignLead::FIRM_TYPE_OPTIONS as $option)
                            <option value="{{ $option }}" {{ old('type_of_firm') == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Place -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Place / City</label>
                    <input type="text" name="place" value="{{ old('place') }}"
                        class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="City Name">
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Address</label>
                    <textarea name="address" rows="2"
                        class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Full Address...">{{ old('address') }}</textarea>
                </div>

                <!-- Product -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Product Interested</label>
                    <input type="text" name="product_interested" value="{{ old('product_interested') }}"
                        class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="SMS, RCS, Whatsapp, etc.">
                </div>

                <!-- Lead Type (Rate) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Lead Type</label>
                    <select name="rate" class="block w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Select Type</option>
                        @foreach(App\Models\CampaignLead::LEAD_TYPE_OPTIONS as $option)
                            <option value="{{ $option }}" {{ old('rate') == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>



                <!-- Comment -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Comment</label>
                    <textarea name="comment" rows="4"
                        class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="Any additional notes...">{{ old('comment') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-8 border-t border-slate-100">
                <button type="reset" class="px-8 py-3 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-all">Reset Form</button>
                <button type="submit" class="px-8 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Save Lead</button>
            </div>
        </form>
    </div>
</div>
@endsection
