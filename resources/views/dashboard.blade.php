@extends('layouts.app')
@section('header', 'Dashboard Overview')

@section('content')
    <div class="mb-8 flex justify-between items-end gap-4" style="flex-wrap: wrap;">
        <div>
            <h3 class="text-2xl font-bold text-slate-800 tracking-tight">Welcome back, {{ auth()->user()->name }}</h3>
            <p class="text-slate-500 mt-2 text-sm">Here is a quick snapshot of your business performance.</p>
        </div>
        <div>
            <a href="{{ route('leads.create') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium shadow-sm transition-colors text-sm whitespace-nowrap">
                + New Lead
            </a>
        </div>
    </div>
    
    <!-- Leads by Source - Refined Breakdown -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Leads by Source
            </h4>
            <div class="h-px flex-1 bg-slate-100 ml-4"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- CRM -->
            <a href="{{ route('leads.index', ['creation_source' => 'CRM']) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-indigo-200 transition-all group block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $crmLeads ?? 0 }}</span>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">CRM Leads</p>
            </a>

            <!-- Client KYC -->
            <a href="{{ route('leads.index', ['creation_source' => ['CLIENT KYC', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT REGISTRATION']]) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-purple-200 transition-all group block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $clientKycLeads ?? 0 }}</span>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Client KYC</p>
            </a>

            <!-- Client PO -->
            <!-- <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-amber-200 transition-all group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $clientPoLeads ?? 0 }}</span>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Client PO</p>
            </div> -->

            <!-- Vendor KYC -->
            <a href="{{ route('vendor_leads.kyc') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-teal-200 transition-all group block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $vendorKycLeads ?? 0 }}</span>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Vendor KYC</p>
            </a>

            <!-- Enquiry Campaign -->
            <a href="{{ route('campaign-leads.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-blue-200 transition-all group block">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.297A2.473 2.473 0 019.5 19.5c-1.38 0-2.5-1.12-2.5-2.5V5.5c0-1.38 1.12-2.5 2.5-2.5 1.11 0 2.05.72 2.38 1.711M11 5.882a2.473 2.473 0 001.5-.382M11 5.882c.33-.991 1.27-1.711 2.38-1.711 1.38 0 2.5 1.12 2.5 2.5v11.5c0 1.38-1.12 2.5-2.5 2.5-1.38 0-2.5-1.12-2.5-2.5V5.882M11 5.882a2.473 2.473 0 011.5-.382M15.5 19.5c1.38 0 2.5-1.12 2.5-2.5V5.5a2.5 2.5 0 00-5 0v11.5a2.5 2.5 0 005 0z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $campaignLeadsCount ?? 0 }}</span>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Enquiry Campaign</p>
            </a>

            <!-- Vendor PO -->
            <!-- <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-rose-200 transition-all group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-slate-900">{{ $vendorPoLeads ?? 0 }}</span>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Vendor PO</p>
            </div> -->
        </div>
    </div>
    <!-- Stats Dashboard - Streamlined Professional Look -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Total Leads Stat Card -->
        <div
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col transition-all hover:shadow-md hover:border-indigo-100 group relative overflow-hidden">
            <!-- Decorative background blob -->
            <div
                class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-gradient-to-br from-indigo-50 to-blue-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
            </div>

            <div class="flex items-center justify-between mb-4 relative">
                <div class="p-3.5 rounded-xl bg-indigo-50 text-indigo-600 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="relative">
                <p class="text-sm font-medium text-slate-500 text-sm mb-1">Total Leads</p>
                <div class="flex items-baseline gap-2">
                    <h4 class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalLeads ?? 0) }}
                    </h4>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between relative">
                <a href="{{ route('leads.index') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center group-hover:underline">
                    View all leads
                    <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Active Leads Stat Card -->
        <div
            class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-6 flex flex-col transition-all hover:shadow-md hover:border-emerald-300 group relative overflow-hidden bg-gradient-to-br from-white to-emerald-50/30">
            <!-- Decorative background blob -->
            <div
                class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-gradient-to-br from-emerald-50 to-green-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
            </div>

            <div class="flex items-center justify-between mb-4 relative">
                <div class="p-3.5 rounded-xl bg-emerald-100 text-emerald-600 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                        </path>
                    </svg>
                </div>
                <span
                    class="inline-flex items-center bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-full border border-emerald-200">
                    Active Status
                </span>
            </div>

            <div class="relative">
                <p class="text-sm font-medium text-emerald-800 mb-1">Active Leads</p>
                <div class="flex items-baseline gap-2">
                    <h4 class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ number_format($activeLeads ?? 0) }}
                    </h4>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-emerald-100/50 flex items-center justify-between relative">
                <a href="{{ route('leads.index', ['lead_status' => 'Active']) }}"
                    class="text-sm text-emerald-700 hover:text-emerald-900 font-medium inline-flex items-center group-hover:underline">
                    View active
                    <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Inactive Leads Stat Card -->
        <div
            class="bg-white rounded-2xl shadow-sm border border-rose-200 p-6 flex flex-col transition-all hover:shadow-md hover:border-rose-300 group relative overflow-hidden bg-gradient-to-br from-white to-rose-50/30">
            <!-- Decorative background blob -->
            <div
                class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-gradient-to-br from-rose-50 to-red-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
            </div>

            <div class="flex items-center justify-between mb-4 relative">
                <div class="p-3.5 rounded-xl bg-rose-100 text-rose-600 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span
                    class="inline-flex items-center bg-rose-100 text-rose-800 text-xs font-bold px-2.5 py-1 rounded-full border border-rose-200">
                    Inactive
                </span>
            </div>

            <div class="relative">
                <p class="text-sm font-medium text-rose-800 mb-1">Inactive Leads</p>
                <div class="flex items-baseline gap-2">
                    <h4 class="text-4xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($inactiveLeads ?? 0) }}</h4>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-rose-100/50 flex items-center justify-between relative">
                <a href="{{ route('leads.index', ['lead_status' => 'Non Active']) }}"
                    class="text-sm text-rose-700 hover:text-rose-900 font-medium inline-flex items-center group-hover:underline">
                    View inactive
                    <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
        </div>
    </div>

@endsection