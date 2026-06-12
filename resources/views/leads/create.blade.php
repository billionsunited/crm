@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Add Lead</h2>
        <p class="text-sm text-slate-500">Create a new lead and enter all details.</p>
    </div>
    <a href="{{ route('leads.index') }}" class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-50 transition-colors shadow-sm">
        Back to Leads
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6">
        <form action="{{ route('leads.store') }}" method="POST" enctype="multipart/form-data">
            
            @include('leads._form')
            
            <div class="flex justify-end gap-3 pt-6 mt-4 pb-2">
                <a href="{{ route('leads.index') }}" class="h-12 inline-flex items-center px-8 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold text-base transition-colors">
                    Cancel
                </a>
                <button type="submit" class="h-12 inline-flex items-center px-8 bg-indigo-600 rounded-lg text-white hover:bg-indigo-700 font-semibold text-base transition-colors shadow-sm">
                    Save Lead
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
