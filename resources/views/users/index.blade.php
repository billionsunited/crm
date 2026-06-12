@extends('layouts.app')
@section('header', 'Users Management')

@section('content')
    <style>
        @media (max-width: 640px) {
            .actions-wrapper {
                flex-direction: column !important;
                width: 100%;
                margin-top: 1rem;
            }

            .actions-wrapper>* {
                width: 100% !important;
            }

            .actions-wrapper button {
                justify-content: center !important;
            }

            .actions-wrapper .group {
                width: 100% !important;
            }
        }
    </style>
    <div class="relative">
        <div class="flex flex-col h-full transition duration-200">
            <!-- Page Header & Actions -->
            <div class="flex items-center justify-between gap-4 mb-6" style="flex-wrap: wrap;">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 tracking-tight">System Users ✨</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage system access, roles, and security credentials for all
                        team members.</p>
                </div>

                <div class="flex items-center gap-3 actions-wrapper">
                    <button @click="$dispatch('open-create-user-modal')"
                        class="h-12 px-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-lg shadow-indigo-200 flex items-center gap-2 text-sm font-bold transition-all transform active:scale-95">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New User
                    </button>

                    <form action="{{ route('users.index') }}" method="GET" class="flex items-center">
                        <div class="relative group">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
                                class="w-full sm:w-64 h-12 pl-11 pr-10 border border-slate-300 rounded-xl shadow-sm bg-white text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all">

                            @if(request('search'))
                                <a href="{{ route('users.index') }}"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-rose-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Top Pagination & Scrollbar -->
            <div x-data="topScrollHandler()" x-init="init()" class="flex flex-col gap-2 mt-6">
                @if($users->hasPages())
                    <div class="bg-slate-50/50 px-6 py-3 border border-slate-200 rounded-xl shadow-sm">
                        {{ $users->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif

                @include('partials.top-scrollbar')

                <!-- Table Container -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex-1">
                    <div x-ref="contentContainer" @scroll="sync($el, $refs.topScrollbar)" class="overflow-x-auto h-full">
                        <table x-ref="mainTable" class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/80 backdrop-blur-sm sticky top-0 z-10">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    User</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Contact</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Role & Permissions</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Registration</th>
                                <th scope="col"
                                    class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($users as $user)
                                <tr @click="$dispatch('open-edit-user-modal', { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}' })"
                                    class="hover:bg-slate-50/80 transition-colors group cursor-pointer">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-100 mr-3">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-900">{{ $user->name }}</div>
                                                <div class="text-xs text-slate-400">UID:
                                                    #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-600">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $role = $user->roles->first();
                                            $roleName = $role?->name ?? 'none';
                                        @endphp
                                        <div class="flex flex-col gap-1.5">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider w-max {{ $roleName === 'admin' ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                                {{ $roleName }}
                                            </span>
                                            @if($role)
                                                <div class="text-[10px] text-slate-400">{{ $role->permissions->count() }}
                                                    specialized permissions</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-600">{{ $user->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-slate-400">{{ $user->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="$dispatch('open-edit-user-modal', { id: {{ $user->id }}, name: '{{ $user->name }}', email: '{{ $user->email }}' })"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-bold transition-all"
                                                title="Edit Profile">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>Edit</span>
                                            </button>
                                            <button
                                                @click="$dispatch('open-role-modal', { id: {{ $user->id }}, name: '{{ $user->name }}', currentRole: '{{ $roleName }}' })"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-bold transition-all"
                                                title="Change Role">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span>Role</span>
                                            </button>
                                            <button
                                                @click="$dispatch('open-password-modal', { id: {{ $user->id }}, name: '{{ $user->name }}' })"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100 rounded-lg text-xs font-bold transition-all"
                                                title="Reset Password">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                                <span>Password</span>
                                            </button>
                                            @if($user->id !== auth()->id())
                                                <button
                                                    @click="$dispatch('open-delete-user-modal', { id: {{ $user->id }}, name: '{{ $user->name }}' })"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-100 rounded-lg text-xs font-bold transition-all"
                                                    title="Delete User">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span>Delete</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                        No users match your search criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        {{ $users->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>
            </div>

        </div>
    </div>
    </div>

    <!-- Password Change Modal -->
    <div x-data="{ show: false, userId: null, userName: '', actionUrl: '' }"
        @open-password-modal.window="show = true; userId = $event.detail.id; userName = $event.detail.name; actionUrl = '{{ url('/users') }}/' + $event.detail.id + '/password'"
        x-show="show" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">

        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="show = false"></div>

            <div x-show="show" x-transition.scale.95
                class="relative inline-block w-full max-w-sm bg-white rounded-3xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden z-10"
                @click.stop>
                <div class="p-8">
                    <div class="flex flex-col items-center text-center mb-8">
                        <div
                            class="h-16 w-16 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 mb-4 shadow-inner">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Security Update</h3>
                        <p class="text-sm text-slate-500 mt-2">Setting new credentials for <br><span
                                class="font-bold text-indigo-600" x-text="userName"></span></p>
                    </div>

                    <form :action="actionUrl" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">New
                                Password</label>
                            <input type="password" name="password" required autocomplete="new-password"
                                class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                placeholder="Minimum 8 characters">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Confirm
                                New Password</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                placeholder="Repeat for verification">
                        </div>

                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center rounded-2xl bg-indigo-600 px-5 py-4 text-sm font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Update
                                Credentials</button>
                            <button type="button" @click="show = false"
                                class="w-full inline-flex justify-center items-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all">Keep
                                Current</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Change Modal -->
    <div x-data="{ show: false, userId: null, userName: '', currentRole: '', actionUrl: '' }"
        @open-role-modal.window="show = true; userId = $event.detail.id; userName = $event.detail.name; currentRole = $event.detail.currentRole; actionUrl = '{{ url('/users') }}/' + $event.detail.id + '/role'"
        x-show="show" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">

        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="show = false"></div>

            <div x-show="show" x-transition.scale.95
                class="relative inline-block w-full max-w-sm bg-white rounded-3xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden z-10"
                @click.stop>
                <div class="p-8">
                    <div class="flex flex-col items-center text-center mb-8">
                        <div
                            class="h-16 w-16 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 mb-4 shadow-inner">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Access Control</h3>
                        <p class="text-sm text-slate-500 mt-2">Updating role for <br><span class="font-bold text-indigo-600"
                                x-text="userName"></span></p>
                    </div>

                    <form :action="actionUrl" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Select
                                System Role</label>
                            <select name="role"
                                class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-[52px]">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" :selected="currentRole === '{{ $role->name }}'">
                                        {{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center rounded-2xl bg-indigo-600 px-5 py-4 text-sm font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Assign
                                Role</button>
                            <button type="button" @click="show = false"
                                class="w-full inline-flex justify-center items-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Create User Modal -->
    <div x-data="{ show: false }" @open-create-user-modal.window="show = true" x-show="show" x-cloak
        class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="show = false"></div>
            <div x-show="show" x-transition.scale.95
                class="relative inline-block w-full max-w-lg bg-white rounded-3xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden z-10"
                @click.stop>
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">New System User</h3>
                            <p class="text-sm text-slate-500 mt-1">Create a new account with specific access roles.</p>
                        </div>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('users.store') }}" method="POST" class="space-y-5" autocomplete="off">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Full
                                    Name</label>
                                <input type="text" name="name" required autocomplete="off"
                                    class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    placeholder="John Doe">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Email
                                    Address</label>
                                <input type="email" name="email" required autocomplete="off"
                                    class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    placeholder="john@example.com">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Password</label>
                                <input type="password" name="password" required autocomplete="new-password"
                                    class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    placeholder="Min. 8 characters">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Confirm
                                    Password</label>
                                <input type="password" name="password_confirmation" required autocomplete="new-password"
                                    class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                                    placeholder="Repeat password">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">System
                                Role</label>
                            <select name="role" required
                                class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all h-[52px]">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-4 flex items-center gap-3">
                            <button type="submit"
                                class="flex-1 inline-flex justify-center items-center rounded-2xl bg-indigo-600 px-5 py-4 text-sm font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Create
                                Account</button>
                            <button type="button" @click="show = false"
                                class="px-8 py-4 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all border border-transparent">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-data="{ show: false, userId: null, userName: '', userEmail: '', actionUrl: '' }"
        @open-edit-user-modal.window="show = true; userId = $event.detail.id; userName = $event.detail.name; userEmail = $event.detail.email; actionUrl = '{{ url('/users') }}/' + $event.detail.id"
        x-show="show" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="show = false"></div>
            <div x-show="show" x-transition.scale.95
                class="relative inline-block w-full max-w-sm bg-white rounded-3xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden z-10"
                @click.stop>
                <div class="p-8">
                    <div class="flex flex-col items-center text-center mb-8">
                        <div
                            class="h-16 w-16 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 mb-4 shadow-inner">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Edit Profile</h3>
                        <p class="text-sm text-slate-500 mt-2">Update information for <br><span
                                class="font-bold text-indigo-600" x-text="userName"></span></p>
                    </div>

                    <form :action="actionUrl" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Full
                                Name</label>
                            <input type="text" name="name" x-model="userName" required
                                class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Email
                                Address</label>
                            <input type="email" name="email" x-model="userEmail" required
                                class="block w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        </div>

                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center rounded-2xl bg-indigo-600 px-5 py-4 text-sm font-bold text-white shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Save
                                Changes</button>
                            <button type="button" @click="show = false"
                                class="w-full inline-flex justify-center items-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div x-data="{ show: false, userId: null, userName: '', actionUrl: '' }"
        @open-delete-user-modal.window="show = true; userId = $event.detail.id; userName = $event.detail.name; actionUrl = '{{ url('/users') }}/' + $event.detail.id"
        x-show="show" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="show = false"></div>
            <div x-show="show" x-transition.scale.95
                class="relative inline-block w-full max-w-sm bg-white rounded-3xl text-left shadow-2xl transform transition-all border border-slate-200 overflow-hidden z-10"
                @click.stop>
                <div class="p-8">
                    <div class="flex flex-col items-center text-center mb-6">
                        <div
                            class="h-16 w-16 rounded-3xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 mb-4 shadow-inner">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Delete User?</h3>
                        <p class="text-sm text-slate-500 mt-2">Are you sure you want to delete <br><span
                                class="font-bold text-rose-600" x-text="userName"></span>? This action cannot be undone.</p>
                    </div>

                    <form :action="actionUrl" method="POST" class="flex flex-col gap-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full inline-flex justify-center items-center rounded-2xl bg-rose-600 px-5 py-4 text-sm font-bold text-white shadow-xl shadow-rose-100 hover:bg-rose-700 transition-all">Delete
                            Account</button>
                        <button type="button" @click="show = false"
                            class="w-full inline-flex justify-center items-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-400 hover:text-slate-600 transition-all">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection