<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OfficeTimingController extends Controller
{
    public function index()
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($days as $day) {
            \App\Models\OfficeTiming::firstOrCreate(
                ['day_of_week' => $day],
                ['is_working_day' => true, 'start_time' => '09:00:00', 'end_time' => '18:00:00']
            );
        }

        $timings = \App\Models\OfficeTiming::all();
        return view('office_timings.index', compact('timings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'timings' => 'required|array',
            'timings.*.id' => 'required|exists:office_timings,id',
            'timings.*.is_working_day' => 'nullable|boolean',
            'timings.*.start_time' => 'nullable|date_format:H:i',
            'timings.*.end_time' => 'nullable|date_format:H:i',
        ]);

        foreach ($data['timings'] as $timingData) {
            $timing = \App\Models\OfficeTiming::find($timingData['id']);
            if ($timing) {
                $timing->update([
                    'is_working_day' => isset($timingData['is_working_day']),
                    'start_time' => $timingData['start_time'] ?? null,
                    'end_time' => $timingData['end_time'] ?? null,
                ]);
            }
        }

        return redirect()->route('office_timings.index')->with('success', 'Office timings updated successfully.');
    }
}
