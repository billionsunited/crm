<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Closed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100 text-center p-8">
        <div class="w-20 h-20 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Office Hours Completed</h2>
        <p class="text-slate-500 mb-6 leading-relaxed">
            The CRM is currently inaccessible as you are outside of the designated working hours. Please return during standard office timings.
        </p>
        
        @if(isset($nextOpenDay) && isset($formattedTime))
        <div class="bg-indigo-50 rounded-xl p-4 mb-8 border border-indigo-100">
            <p class="text-indigo-800 font-medium">
                The office will reopen <strong>{{ $nextOpenDay }}</strong> at <strong>{{ $formattedTime }}</strong>.
            </p>
        </div>
        @endif
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full h-12 bg-indigo-600 text-white rounded-xl font-semibold shadow-md shadow-indigo-200 hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-300 transition-all focus:ring-4 focus:ring-indigo-100">
                Sign Out
            </button>
        </form>
    </div>
</body>
</html>
