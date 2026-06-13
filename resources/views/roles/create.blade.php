@extends('layouts.app')
@section('header', 'Create Role')

@section('content')
<style>
    @media (max-width: 640px) {
        .mobile-create-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem !important;
        }
        .mobile-create-header a {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">

    <div class="mb-8 flex justify-between items-center mobile-create-header">
        <div>
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Add New Role ✨</h1>
            <p class="text-sm text-slate-500 mt-1">Configure role name and select specific permissions for this role.</p>
        </div>
        <a href="{{ route('roles.index') }}"
            class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-6 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('roles.store') }}" method="POST" class="space-y-8">
        @csrf
        
        <!-- Role Name Section -->
        <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">General Information</h2>
            <div class="max-w-md">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2" for="name">
                    Role Name <span class="text-rose-500">*</span>
                </label>
                <input id="name" name="name" 
                    class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] px-3 bg-white" 
                    type="text" placeholder="e.g. Sales Manager" required value="{{ old('name') }}" />
                <p class="mt-2 text-xs text-slate-400">Use descriptive names for clarity (e.g., 'Accountant', 'Sales Lead').</p>
            </div>
        </div>

        <!-- Permissions Section -->
        <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-2">Role Permissions</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                    $groupedPermissions = [
                        'Dashboard' => ['dashboard-access'],
                        'Lead Management' => ['lead-view', 'lead-edit', 'lead-add', 'lead-delete', 'lead-import', 'lead-export', 'lead-send-document', 'whatsapp-icon', 'lead-contact-view'],
                        'Financials & Invoices' => ['invoice-section', 'invoice-export', 'invoice-view', 'invoice-edit', 'invoice-mark-paid', 'invoice-cancel', 'invoice-delete', 'invoice-or-section', 'invoice-or-export', 'raise-invoice-bu', 'raise-invoice-or', 'email-section'],
                        'Vendors & Partners' => ['vendor-section'],
                        'Marketing' => ['campaign-send'],
                        'Enquiry Campaign' => ['campaign-view', 'campaign-add', 'campaign-edit', 'campaign-delete', 'campaign-import', 'campaign-export', 'enquiry-vendor-contact-view'],
                        'Purchase Orders' => ['client-po-access', 'vendor-po-access'],
                        'Email Templates' => ['email-template-view', 'email-template-add', 'email-template-edit', 'email-template-delete', 'email-template-send'],
                        'Resource Management' => ['document-section', 'company-info-section'],
                    ];
                @endphp

                @foreach($groupedPermissions as $group => $perms)
                <div class="bg-slate-50/50 rounded-xl border border-slate-200 p-5">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2"></span>
                        {{ $group }}
                    </h3>
                    <div class="space-y-3">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-3 group cursor-pointer w-fit">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" 
                                class="form-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5 transition-all cursor-pointer" 
                                {{ is_array(old('permissions')) && in_array($perm, old('permissions')) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900 transition-colors capitalize">
                                @if($perm === 'lead-send-document')
                                    Email Send Document
                                @elseif($perm === 'campaign-send')
                                    Mobile Marketing
                                @elseif(str_starts_with($perm, 'campaign-') && $perm !== 'campaign-send')
                                    {{ str_replace('campaign-', '', $perm) }}
                                @elseif($perm === 'client-po-access')
                                    Client PO
                                @elseif($perm === 'vendor-po-access')
                                    Vendor PO
                                @elseif($perm === 'email-template-send')
                                    Send Email Marketing
                                @elseif(str_starts_with($perm, 'email-template-'))
                                    {{ str_replace('email-template-', '', $perm) }}
                                @elseif($perm === 'email-section')
                                    BU & OR Invoice Email Module
                                @elseif($perm === 'invoice-section')
                                    BU Invoice Module
                                @elseif($perm === 'invoice-export')
                                    BU Invoice Export
                                @elseif($perm === 'invoice-or-section')
                                    OR Invoice Module
                                @elseif($perm === 'invoice-or-export')
                                    OR Invoice Export
                                @elseif($perm === 'raise-invoice-bu')
                                    Raise Invoice BU
                                @elseif($perm === 'raise-invoice-or')
                                    Raise Invoice OR
                                @elseif($perm === 'lead-contact-view')
                                    View Lead Contacts
                                @elseif($perm === 'enquiry-vendor-contact-view')
                                    View Enquiry/Vendor Contacts
                                @else
                                    {{ str_replace(['-', 'section'], [' ', 'Module'], $perm) }}
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
            <a href="{{ route('roles.index') }}" 
                class="px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 rounded-lg text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                Create Role
            </button>
        </div>
    </form>
</div>
@endsection
