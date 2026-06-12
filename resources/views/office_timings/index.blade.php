@extends('layouts.app')

@section('header', 'Office Timings')

@section('content')
<style>
/* Custom toggle styles to ensure they work without running npm run build on production */
.custom-toggle {
    width: 44px;
    height: 24px;
    background-color: #e2e8f0;
    border-radius: 9999px;
    position: relative;
    transition: background-color 0.2s;
}
.working-day-toggle:checked + .custom-toggle {
    background-color: #4f46e5;
}
.custom-toggle::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background-color: white;
    border: 1px solid #cbd5e1;
    border-radius: 50%;
    transition: transform 0.2s;
}
.working-day-toggle:checked + .custom-toggle::after {
    transform: translateX(20px);
    border-color: white;
}
.working-day-toggle:focus + .custom-toggle {
    box-shadow: 0 0 0 4px rgba(165, 180, 252, 0.5);
}
</style>
<div class="mb-6">
    <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-slate-800">Office Timings</h2>
    </div>
    <p class="text-sm text-slate-500 mt-1">Configure the working days and hours for the CRM. Regular users will not be able to access the system outside of these hours.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <form action="{{ route('office_timings.update') }}" method="POST">
        @csrf
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-sm font-semibold text-slate-600">
                            <th class="pb-3 px-4">Day of Week</th>
                            <th class="pb-3 px-4">Working Day</th>
                            <th class="pb-3 px-4">From Time</th>
                            <th class="pb-3 px-4">To Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($timings as $index => $timing)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-4 px-4 font-medium text-slate-700">
                                {{ $timing->day_of_week }}
                                <input type="hidden" name="timings[{{ $index }}][id]" value="{{ $timing->id }}">
                            </td>
                            <td class="py-4 px-4">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="timings[{{ $index }}][is_working_day]" value="0">
                                    <input type="checkbox" name="timings[{{ $index }}][is_working_day]" value="1" class="sr-only working-day-toggle" data-index="{{ $index }}" {{ $timing->is_working_day ? 'checked' : '' }}>
                                    <div class="custom-toggle"></div>
                                </label>
                            </td>
                            <td class="py-4 px-4">
                                <input type="time" name="timings[{{ $index }}][start_time]" id="start_time_{{ $index }}" value="{{ $timing->start_time ? \Carbon\Carbon::parse($timing->start_time)->format('H:i') : '' }}" class="w-full h-10 px-3 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm text-sm transition-colors" {{ !$timing->is_working_day ? 'disabled' : '' }}>
                            </td>
                            <td class="py-4 px-4">
                                <input type="time" name="timings[{{ $index }}][end_time]" id="end_time_{{ $index }}" value="{{ $timing->end_time ? \Carbon\Carbon::parse($timing->end_time)->format('H:i') : '' }}" class="w-full h-10 px-3 rounded-lg border-slate-300 focus:border-indigo-500 shadow-sm text-sm transition-colors" {{ !$timing->is_working_day ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="submit" class="h-10 px-6 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                Save Timings
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.working-day-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const index = this.getAttribute('data-index');
            const startTimeInput = document.getElementById('start_time_' + index);
            const endTimeInput = document.getElementById('end_time_' + index);
            
            if (this.checked) {
                startTimeInput.disabled = false;
                endTimeInput.disabled = false;
                startTimeInput.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                endTimeInput.classList.remove('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
            } else {
                startTimeInput.disabled = true;
                endTimeInput.disabled = true;
                startTimeInput.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
                endTimeInput.classList.add('bg-slate-100', 'text-slate-400', 'cursor-not-allowed');
            }
        });
        
        toggle.dispatchEvent(new Event('change'));
    });
});
</script>

@endsection
