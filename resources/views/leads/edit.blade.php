@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Edit Lead #{{ $lead->record_id }}</h2>
        <p class="text-sm text-slate-500">Update lead details for {{ $lead->customer_name }}.</p>
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" form="edit-lead-form" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition-colors shadow-sm cursor-pointer">
            Update Lead
        </button>
        @if(isset($lead) && $lead->isVendor())
            <a href="{{ route('vendor_leads.kyc', ['page' => request('page')]) }}" class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition-colors shadow-sm">
                Back to Vendor KYC
            </a>
        @else
            <a href="{{ route('leads.index', ['page' => request('page')]) }}" class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition-colors shadow-sm">
                Back to Leads
            </a>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6">
        <form action="{{ route('leads.update', [$lead->id, 'page' => request('page')]) }}" method="POST" enctype="multipart/form-data" id="edit-lead-form">
            @method('PUT')
            @include('leads._form', ['lead' => $lead])
            
            <div class="flex justify-end gap-3 pt-6 mt-4 pb-2">
                @if(isset($lead) && $lead->isVendor())
                    <a href="{{ route('vendor_leads.kyc', ['page' => request('page')]) }}" class="h-12 inline-flex items-center px-8 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold text-base transition-colors">
                        Cancel
                    </a>
                @else
                    <a href="{{ route('leads.index', ['page' => request('page')]) }}" class="h-12 inline-flex items-center px-8 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold text-base transition-colors">
                        Cancel
                    </a>
                @endif
                <button type="submit" class="h-12 inline-flex items-center px-8 bg-indigo-600 rounded-lg text-white hover:bg-indigo-700 font-semibold text-base transition-colors shadow-sm">
                    Update Lead
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
