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

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body x-data="{ sidebarOpen: false }"
    class="font-sans antialiased bg-slate-50 text-slate-900 h-screen overflow-hidden flex selection:bg-indigo-500 selection:text-white">

    <!-- Sidebar Navigation -->
    @include('partials.sidebar')

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">

        <!-- Header bar -->
        @include('partials.header')

        <!-- Main Panel -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-[calc(100vh-4rem)] flex flex-col">

                @if (session('status'))
                    <div class="mb-4 bg-emerald-50 text-emerald-800 p-4 rounded-lg border border-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-rose-50 text-rose-800 p-4 rounded-lg border border-rose-200">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex-1">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @include('components.alert')
    @include('components.messaging-modal')
    @include('components.email-messaging-modal')

    <script>
        window.showToast = function(title, message, type = 'success') {
            // Simple alert fallback if no toast system is available
            // In a real app, you'd trigger an Alpine component or a toast library
            alert(title + ': ' + message);
        };
    </script>
</body>

</html>