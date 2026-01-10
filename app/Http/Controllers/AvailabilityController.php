<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'duration' => 'integer|min:15|max:120', // duration in minutes, default 30?
        ]);

        $date = $request->input('date');
        $duration = $request->input('duration', 30);
        $dayOfWeek = date('w', strtotime($date));

        // Get Business Hours for this day
        $businessHours = \App\Models\BusinessHour::where('day_of_week', $dayOfWeek)->get();

        if ($businessHours->isEmpty()) {
            return response()->json(['slots' => []]);
        }

        // Get Appointments for this day
        $appointments = \App\Models\Appointment::whereDate('start_at', $date)
            ->where('status', '!=', 'canceled')
            ->orderBy('start_at')
            ->get();

        $availableSlots = [];

        foreach ($businessHours as $hours) {
            $startTime = strtotime($date . ' ' . $hours->start_time);
            $endTime = strtotime($date . ' ' . $hours->end_time);

            while ($startTime + ($duration * 60) <= $endTime) {
                $slotEnd = $startTime + ($duration * 60);
                
                // Check collision
                $isBooked = false;
                foreach ($appointments as $appointment) {
                    $apptStart = strtotime($appointment->start_at);
                    $apptEnd = strtotime($appointment->end_at);

                    // Overlap logic: (StartA < EndB) and (EndA > StartB)
                    if ($startTime < $apptEnd && $slotEnd > $apptStart) {
                        $isBooked = true;
                        break;
                    }
                }

                if (! $isBooked) {
                    $availableSlots[] = date('H:i', $startTime);
                }

                $startTime += ($duration * 60);
            }
        }

        return response()->json(['slots' => $availableSlots]);
    }
}
