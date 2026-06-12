@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Edit Customer</h2>
        <p class="text-sm text-slate-500">Update {{ $customer->client_name }}'s details.</p>
    </div>
    <a href="{{ route('customers.index') }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-medium hover:bg-slate-300 transition-colors">
        Back to Customers
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Client Name -->
                <div>
                    <label for="client_name" class="block text-sm font-medium text-slate-700 mb-1">Client Name <span class="text-red-500">*</span></label>
                    <input type="text" name="client_name" id="client_name" value="{{ old('client_name', $customer->client_name) }}" required 
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('client_name') border-red-500 @enderror">
                    @error('client_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Name -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-slate-700 mb-1">Company Name</label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $customer->company_name) }}" 
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('company_name') border-red-500 @enderror">
                    @error('company_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email ID -->
                <div>
                    <label for="email_id" class="block text-sm font-medium text-slate-700 mb-1">Email ID</label>
                    <input type="email" name="email_id" id="email_id" value="{{ old('email_id', $customer->email_id) }}" 
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('email_id') border-red-500 @enderror">
                    @error('email_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile No -->
                <div>
                    <label for="mobile_no" class="block text-sm font-medium text-slate-700 mb-1">Mobile No</label>
                    <input type="text" name="mobile_no" id="mobile_no" value="{{ old('mobile_no', $customer->mobile_no) }}" 
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('mobile_no') border-red-500 @enderror">
                    @error('mobile_no')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Place -->
                <div>
                    <label for="place" class="block text-sm font-medium text-slate-700 mb-1">Place (City/Location)</label>
                    <input type="text" name="place" id="place" value="{{ old('place', $customer->place) }}" 
                        class="w-full h-11 px-4 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('place') border-red-500 @enderror">
                    @error('place')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Registered Address -->
                <div class="md:col-span-2 space-y-2">
                    <label for="registered_address" class="block text-sm font-medium text-slate-700">Registered Address</label>
                    <textarea name="registered_address" id="registered_address" rows="3" 
                        class="w-full p-4 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('registered_address') border-red-500 @enderror">{{ old('registered_address', $customer->registered_address) }}</textarea>
                    @error('registered_address')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 pb-2">
                <a href="{{ route('customers.index') }}" class="h-12 inline-flex items-center px-8 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold text-base transition-colors">
                    Cancel
                </a>
                <button type="submit" class="h-12 inline-flex items-center px-8 bg-indigo-600 rounded-lg text-white hover:bg-indigo-700 font-semibold text-base transition-colors shadow-sm">
                    Update Customer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
