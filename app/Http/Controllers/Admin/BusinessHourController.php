<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusinessHourController extends Controller
{
    public function index()
    {
        $businessHours = auth()->user()->businessHours()->orderBy('day_of_week')->get();
        
        // If no hours, seed defaults provided in frontend or here? 
        // Let's return empty and handle in frontend or seed basic 0-6 days.
        if ($businessHours->isEmpty()) {
            $days = range(0, 6); // 0=Sunday
            $businessHours = collect($days)->map(function($day) {
                return [
                    'day_of_week' => $day,
                    'start_time' => '09:00', // Default
                    'end_time' => '17:00',
                    'is_enabled' => false, // Virtual attribute for frontend
                ];
            });
        }

        return \Inertia\Inertia::render('BusinessHours/Index', [
            'businessHours' => $businessHours
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hours' => 'required|array',
            'hours.*.day_of_week' => 'required|integer|min:0|max:6',
            'hours.*.start_time' => 'nullable|date_format:H:i',
            'hours.*.end_time' => 'nullable|date_format:H:i|after:hours.*.start_time',
        ]);

        $user = auth()->user();

        // Strategy: syncing. Delete all and recreate? Or update existing?
        // Simpler to delete all for this user and recreate based on input.
        // Assuming frontend sends only "enabled" days or all days.
        
        // Transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated) {
            $user->businessHours()->delete();
            
            foreach ($validated['hours'] as $hour) {
                if (!empty($hour['start_time']) && !empty($hour['end_time'])) {
                     $user->businessHours()->create([
                        'day_of_week' => $hour['day_of_week'],
                        'start_time' => $hour['start_time'],
                        'end_time' => $hour['end_time'],
                     ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Horario actualizado correctamente.');
    }
}
