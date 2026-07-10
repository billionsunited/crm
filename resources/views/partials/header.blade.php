<style>
    @media (max-width: 639px) {
        header.custom-header {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            gap: 0.5rem !important;
        }
        
        header.custom-header .custom-header-left {
            min-width: 0 !important;
            flex: 1 !important;
            gap: 0.5rem !important;
        }
        
        header.custom-header h2.custom-header-title {
            font-size: 0.95rem !important;
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
            width: auto !important;
        }
        
        header.custom-header .custom-header-right-actions {
            gap: 0.5rem !important;
        }

        header.custom-header .profile-btn {
            padding-left: 0.375rem !important;
            padding-right: 0.375rem !important;
            gap: 0px !important;
        }

        .notification-dropdown-mobile {
            width: 300px !important;
            right: -60px !important;
        }
    }
</style>

<header
    class="bg-white shadow-sm border-b border-slate-200 flex items-center justify-between px-8 py-4 z-50 w-full transition-all custom-header">
    <div class="flex items-center gap-4 custom-header-left">
        <!-- Mobile menu button -->
        <button @click.stop="sidebarOpen = !sidebarOpen" type="button"
            class="md:hidden text-slate-500 hover:text-slate-700 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>
        <h2 class="text-xl font-bold text-slate-800 tracking-tight leading-tight custom-header-title">
            @yield('header')
        </h2>
    </div>

    <div class="flex items-center gap-6 custom-header-right-actions">
        <!-- Notifications Dropdown -->
        <div class="relative z-[60]" x-data="{ 
                open: false,
                notifications: [],
                count: 0,
                async fetchNotifications() {
                    try {
                        const response = await fetch('{{ route('notifications.fetch') }}');
                        const data = await response.json();
                        this.notifications = data.notifications;
                        this.count = data.count;
                    } catch (error) {
                        console.error('Error fetching notifications:', error);
                    }
                },
                markAsRead(id) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    const baseUrl = '{{ url('/') }}';
                    form.action = (baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl) + '/notifications/' + id + '/read';
                    
                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = '{{ csrf_token() }}';
                    
                    form.appendChild(token);
                    document.body.appendChild(form);
                    form.submit();
                }
            }" x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 60000)">
            <button @click="open = !open" @click.away="open = false"
                class="flex items-center justify-center p-2 text-slate-500 hover:bg-slate-100 hover:text-indigo-600 rounded-full transition-colors focus:outline-none">
                <div class="relative inline-block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    <template x-if="count > 0">
                        <span x-text="count > 9 ? '9+' : count"
                            class="absolute flex items-center justify-center rounded-full bg-red-600 text-white font-bold ring-2 ring-white shadow-sm"
                            style="height: 20px; min-width: 20px; top: -6px; right: -6px; padding: 0 5px; font-size: 11px; line-height: 1;"></span>
                    </template>
                </div>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-3 bg-white rounded-xl shadow-xl border border-slate-100 py-2 ring-1 ring-black ring-opacity-5 origin-top-right z-[100] flex flex-col notification-dropdown-mobile"
                style="display: none; width: 350px;">

                <div class="px-4 py-2 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-slate-800">Notifications</h3>
                    <a href="{{ route('notifications.index') }}"
                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">View All</a>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <template x-if="notifications.length === 0">
                        <div class="p-6 text-center">
                            <div
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 mb-3 border border-slate-100">
                                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-800">No New Notifications</h3>
                            <p class="text-xs text-slate-500 mt-1">You're all caught up! Check back later.</p>
                        </div>
                    </template>

                    <template x-for="notification in notifications" :key="notification.id">
                        <button @click="markAsRead(notification.id)"
                            class="w-full text-left px-4 py-3 hover:bg-slate-50 border-b border-slate-50 last:border-0 transition-colors flex flex-col gap-1 focus:outline-none focus:bg-slate-50">
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-semibold text-slate-800" x-text="notification.title"></span>
                                <span class="text-[10px] text-slate-400 whitespace-nowrap"
                                    x-text="new Date(notification.follow_up_date).toLocaleDateString()"></span>
                            </div>
                            <p class="text-xs text-slate-600 line-clamp-2" x-text="notification.message"></p>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="h-8 border-l border-slate-200 hidden sm:block"></div>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" type="button"
                class="flex items-center gap-3 pl-4 pr-2 py-1.5 focus:outline-none transition-all group bg-white hover:bg-slate-50 rounded-full border border-slate-200 shadow-sm profile-btn">
                <div class="hidden md:block text-right">
                    <span class="text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                </div>
                <div
                    class="w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold border border-indigo-700 shadow-sm flex-shrink-0">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-colors group-hover:text-slate-600 hidden sm:block" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl border border-slate-100 p-2 space-y-1 ring-1 ring-black ring-opacity-5 origin-top-right z-[100]"
                style="display: none;">
                <div class="px-3 py-2 border-b border-slate-100 mb-2 md:hidden">
                    <p class="text-sm text-slate-500">Signed in as</p>
                    <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->email }}</p>
                </div>

                <a href="{{ route('profile.edit') }}"
                    class="flex items-center px-3 py-2 rounded-md gap-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors {{ request()->routeIs('profile.edit') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    My Profile
                </a>
                <div class="border-t border-slate-100 my-1"></div>

                <button type="button" onclick="document.getElementById('logoutModalSidebar').classList.remove('hidden')"
                    class="w-full flex items-center px-3 py-2 rounded-md gap-2 text-sm text-rose-600 hover:bg-rose-50 font-medium transition-colors text-left">
                    <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Sign out
                </button>
            </div>
        </div>
    </div>
</header>