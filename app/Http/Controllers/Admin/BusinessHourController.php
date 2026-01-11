<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class BusinessHourController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id', auth()->id());
        
        // Authorization check: only admin/coordinator can see other users' hours
        if ($userId != auth()->id() && !in_array(auth()->user()->role, ['admin', 'coordinator'])) {
            abort(403);
        }

        $user = User::findOrFail($userId);
        $businessHours = $user->businessHours()->orderBy('day_of_week')->get();
        
        if ($businessHours->isEmpty()) {
            $days = range(0, 6); // 0=Sunday
            $businessHours = collect($days)->map(function($day) {
                return [
                    'day_of_week' => $day,
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'is_enabled' => false,
                ];
            });
        } else {
            // Ensure all 7 days are present even if some aren't in DB
            $days = range(0, 6);
            $existingDays = $businessHours->pluck('day_of_week')->toArray();
            $missingDays = array_diff($days, $existingDays);
            
            foreach ($missingDays as $day) {
                $businessHours->push((object)[
                    'day_of_week' => $day,
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'is_enabled' => false,
                ]);
            }
            $businessHours = $businessHours->sortBy('day_of_week')->values();
        }

        return Inertia::render('BusinessHours/Index', [
            'businessHours' => $businessHours,
            'selectedUserId' => (int)$userId,
            'professionals' => in_array(auth()->user()->role, ['admin', 'coordinator']) 
                ? User::whereIn('role', ['professional', 'coordinator', 'admin'])->orderBy('name')->get(['id', 'name', 'role'])
                : []
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hours' => 'required|array',
            'hours.*.day_of_week' => 'required|integer|min:0|max:6',
            'hours.*.start_time' => 'nullable|date_format:H:i',
            'hours.*.end_time' => 'nullable|date_format:H:i|after:hours.*.start_time',
        ]);

        $userId = $validated['user_id'];
        
        // Authorization check
        if ($userId != auth()->id() && !in_array(auth()->user()->role, ['admin', 'coordinator'])) {
            abort(403);
        }

        $user = User::findOrFail($userId);

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
