@extends('layouts.app')
@section('header', 'Create Template')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Create Template ✨</h1>
            <p class="text-sm text-slate-500 mt-1">Design a new reusable email template.</p>
        </div>
        <a href="{{ route('email-templates.index') }}"
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

    <form action="{{ route('email-templates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="bg-white shadow-sm border border-slate-200 rounded-xl p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2" for="name">
                        Template Name <span class="text-rose-500">*</span>
                    </label>
                    <input id="name" name="name" 
                        class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] px-3 bg-white" 
                        type="text" placeholder="e.g. Welcome Email" required value="{{ old('name') }}" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2" for="subject">
                        Email Subject <span class="text-rose-500">*</span>
                    </label>
                    <input id="subject" name="subject" 
                        class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 h-[42px] px-3 bg-white" 
                        type="text" placeholder="e.g. Welcome to CRM Billions!" required value="{{ old('subject') }}" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2" for="body">
                        Email Body (HTML allowed) <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="body" name="body" rows="12"
                        class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 bg-white" 
                        placeholder="Type your email content here..." required>{{ old('body') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2" for="attachment">
                        Attachment (Optional)
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg bg-slate-50 hover:bg-slate-100 transition">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex justify-center text-sm text-slate-600">
                                <label for="attachment" class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="attachment" name="attachment" type="file" class="sr-only">
                                </label>
                            </div>
                            <p class="text-xs text-slate-500">Document or Image up to 15MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('email-templates.index') }}" 
                class="px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 rounded-lg text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                Save Template
            </button>
        </div>
    </form>
</div>
@endsection
