@extends('layouts.app')

@section('header', 'Office Timings')

@section('content')
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
                                    <input type="checkbox" name="timings[{{ $index }}][is_working_day]" value="1" class="sr-only peer working-day-toggle" data-index="{{ $index }}" {{ $timing->is_working_day ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
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
