<!-- Mobile Sidebar Backdrop (Inline styled to ensure safety) -->
<div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="md:hidden"
    style="position: fixed; inset: 0px; background-color: rgba(15, 23, 42, 0.8); backdrop-filter: blur(4px); display: none; z-index: 35;">
</div>

<aside @click.outside="sidebarOpen = false"
    :style="sidebarOpen ? 'display: flex; position: fixed; top: 0; left: 0; height: 100%; z-index: 40; transform: translateX(0);' : ''"
    class="w-64 bg-slate-900 bg-gradient-to-b bg-linear-to-b from-slate-900 to-indigo-950 border-r border-slate-800 hidden md:flex flex-col h-full shadow-2xl z-20 transition-all duration-300">
    <!-- Logo Area -->
    <div class="flex items-center gap-3 px-4 py-4 border-b border-white/5 bg-black/10">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-full">
            <div
                class="w-10 h-10 rounded-lg bg-indigo-500 shadow-md shadow-indigo-500/30 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                CB
            </div>
            <span class="font-bold text-white text-sm tracking-wide truncate mt-0.5">CRM Billions</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 mt-4 overflow-y-auto custom-scrollbar">
        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-2">Main Menu</div>

        @can('dashboard-access')
        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">
            Dashboard
        </x-nav-link>
        @endcan

        <x-nav-link href="{{ route('leads.index') }}" :active="request()->routeIs('leads.*')">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
            </x-slot>
            Leads
        </x-nav-link>

        @can('campaign-view')
            <x-nav-link href="{{ route('campaign-leads.index') }}" :active="request()->routeIs('campaign-leads.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.297A2.473 2.473 0 019.5 19.5c-1.38 0-2.5-1.12-2.5-2.5V5.5c0-1.38 1.12-2.5 2.5-2.5 1.11 0 2.05.72 2.38 1.711M11 5.882a2.473 2.473 0 001.5-.382M11 5.882c.33-.991 1.27-1.711 2.38-1.711 1.38 0 2.5 1.12 2.5 2.5v11.5c0 1.38-1.12 2.5-2.5 2.5-1.38 0-2.5-1.12-2.5-2.5V5.882M11 5.882a2.473 2.473 0 011.5-.382M15.5 19.5c1.38 0 2.5-1.12 2.5-2.5V5.5a2.5 2.5 0 00-5 0v11.5a2.5 2.5 0 005 0z" />
                    </svg>
                </x-slot>
                Enquiry Campaign
            </x-nav-link>
        @endcan

        @can('invoice-section')
        <x-nav-link href="{{ route('invoices.index') }}" :active="request()->routeIs('invoices.*')">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </x-slot>
            Invoices
        </x-nav-link>
        @endcan

        @can('invoice-or-section')
        <x-nav-link href="{{ route('or-invoices.index') }}" :active="request()->routeIs('or-invoices.*')">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </x-slot>
            Invoices OR
        </x-nav-link>
        @endcan

        @can('email-template-view')
        <x-nav-link href="{{ route('email-templates.index') }}" :active="request()->routeIs('email-templates.*')">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </x-slot>
            Email Templates
        </x-nav-link>
        @endcan
        @can('vendor-section')
        <x-nav-link href="{{ route('vendor_leads.kyc') }}" :active="request()->routeIs('vendor_leads.kyc')">
            <x-slot name="icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </x-slot>
            Vendor KYC
        </x-nav-link>
        @endcan
        @if(auth()->user()->can('client-po-access') || auth()->user()->can('vendor-po-access'))
        <div x-data="{ open: {{ request()->routeIs('manage_po.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-400 font-medium hover:bg-slate-800 hover:text-white transition-colors border border-transparent {{ request()->routeIs('manage_po.*') ? 'bg-slate-800 text-white' : '' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Manage P.O
                </div>
                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-8 space-y-1">
                @can('client-po-access')
                    <a href="{{ route('manage_po.client_po.create') }}"
                        class="block px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('manage_po.client_po.*') ? 'text-indigo-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        Client P.O
                    </a>
                @endcan
                @can('vendor-po-access')
                    <a href="{{ route('manage_po.vendor_po') }}"
                        class="block px-3 py-1.5 text-sm rounded-md transition-colors {{ request()->routeIs('manage_po.vendor_po') ? 'text-indigo-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        Vendor P.O
                    </a>
                @endcan
            </div>
        </div>
        @endif
        @if(auth()->user()->isAdmin())
            <div class="pt-4 mt-4 border-t border-white/10">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-2">Administration</div>
                <x-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')" icon="users">
                    Users
                </x-nav-link>
                <x-nav-link href="{{ route('roles.index') }}" :active="request()->routeIs('roles.*')">
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </x-slot>
                    Manage Roles
                </x-nav-link>
                <x-nav-link href="{{ route('office_timings.index') }}" :active="request()->routeIs('office_timings.*')">
                    <x-slot name="icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot>
                    Office Timings
                </x-nav-link>
            </div>
        @endif

        <div class="pt-4 mt-4 border-t border-white/10">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 px-2">Preferences</div>
            <x-nav-link href="{{ route('profile.edit') }}" :active="request()->routeIs('profile.*')">
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </x-slot>
                Profile
            </x-nav-link>

            <button type="button" onclick="document.getElementById('logoutModalSidebar').classList.remove('hidden')"
                class="w-full mt-2 flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-300 hover:bg-rose-500/10 hover:text-rose-400 group transition-colors border border-transparent">
                <svg class="flex-shrink-0 h-5 w-5 text-slate-400 group-hover:text-rose-400 transition-colors"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
                Logout
            </button>
        </div>
    </nav>
</aside>

<!-- Logout Confirmation Modal (Sidebar) -->
<div id="logoutModalSidebar" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity cursor-pointer" aria-hidden="true"
            onclick="document.getElementById('logoutModalSidebar').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div
            class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full border border-slate-100">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-semibold text-slate-900" id="modal-title">Sign Out</h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500">Are you sure you want to sign out of your account?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto sm:ml-3">
                    @csrf
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent px-4 py-2 bg-rose-600 text-base font-medium text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:w-auto sm:text-sm transition">
                        Sign Out
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('logoutModalSidebar').classList.add('hidden')"
                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 px-4 py-2 bg-white text-base font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>