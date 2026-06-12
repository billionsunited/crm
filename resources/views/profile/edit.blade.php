@extends('layouts.app')
@section('header', 'My Profile')

@section('content')
    <div class="max-w-4xl space-y-8">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 rounded-t-xl">
                <h4 class="text-lg font-semibold text-slate-800">Profile Information</h4>
                <p class="text-sm text-slate-500 mt-1">Update your account's profile information and email address.</p>
            </div>

            <div class="p-6">
                <form method="post" action="{{ route('profile.update') }}" class="space-y-6 max-w-xl">
                    @csrf
                    @method('patch')

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                            autocomplete="name"
                            class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                            autocomplete="username"
                            class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                        <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Save Changes
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p class="text-sm text-slate-600">Saved.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection