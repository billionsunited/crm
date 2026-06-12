<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 h-screen flex border-t-4 border-rose-500 selection:bg-rose-500 selection:text-white">
    <div class="m-auto w-full max-w-lg px-6 py-12 bg-white rounded-2xl shadow-xl border border-slate-200 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-rose-50 text-rose-500 mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Access Denied</h1>
        <p class="text-slate-500 mb-8 max-w-sm mx-auto">You do not have the required permissions or roles to view this page.</p>
        
        <div class="flex items-center justify-center gap-4">
            <a href="javascript:history.back()" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 font-medium rounded-lg hover:bg-slate-50 transition-colors focus:ring-4 focus:ring-slate-100">
                Go Back
            </a>
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors focus:ring-4 focus:ring-indigo-100">
                Dashboard
            </a>
        </div>
    </div>
</body>
</html>
