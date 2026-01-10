<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'duration' => 'integer|min:15|max:120',
            'professional_id' => 'nullable|exists:users,id',
        ]);

        $date = $request->input('date');
        $duration = $request->input('duration', 30);
        $professionalId = $request->input('professional_id');
        
        $dayOfWeek = date('w', strtotime($date));

        // Get Business Hours
        $businessHours = \App\Models\BusinessHour::where('day_of_week', $dayOfWeek)
            ->when($professionalId, function ($query) use ($professionalId) {
                 return $query->where('user_id', $professionalId);
            })
            ->when(!$professionalId, function($query) {
                // If no professional specified, maybe fetch all OR specific logic.
                // For MVP phase 2, let's assume if null, we check global or first user?
                // Or better, if not provided, we check for ALL slots combined?
                // Let's stick to: if not provided, check if ANY professional has availability?
                // For simplicity, let's default to the first user if not provided for now or strict mode?
                // Let's make it optional filter. If null, we get business hours where user_id is null (global) OR all?
                // A better approach for now: If provided, filter. If not, maybe empty or default?
                // Let's assume there is always a professional. For now, let's just not filter if null (returning all slots from everyone mixed - might be messy)
                // Let's enforce stricter check: if null, we might show slots from ANYONE.
            })
            ->get();

        if ($businessHours->isEmpty()) {
             // Fallback: If no specific hours, maybe check for "global" business hours (where user_id is null)
             $businessHours = \App\Models\BusinessHour::where('day_of_week', $dayOfWeek)->whereNull('user_id')->get();
             if ($businessHours->isEmpty()) {
                 return response()->json(['slots' => []]);
             }
        }

        // Get Appointments for this day and professional
        $appointments = \App\Models\Appointment::whereDate('start_at', $date)
            ->where('status', '!=', 'canceled')
            ->when($professionalId, function ($query) use ($professionalId) {
                return $query->where('professional_id', $professionalId);
            })
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
