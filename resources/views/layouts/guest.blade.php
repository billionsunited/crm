<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CRM Billions') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-900 antialiased bg-slate-100 flex items-center justify-center min-h-screen">
    <div
        class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-xl overflow-hidden sm:rounded-2xl border border-slate-200">
        <div class="flex justify-center mb-8">
            <div class="flex flex-col items-center">
                <div
                    class="w-16 h-16 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-3xl shadow-md mb-4">
                    C
                </div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">CRM Billions</h1>
                <p class="text-slate-500 text-sm mt-1">Dashboard Login</p>
            </div>
        </div>

        @yield('content')
    </div>
</body>

</html>