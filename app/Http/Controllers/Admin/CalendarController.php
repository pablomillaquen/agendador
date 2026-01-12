<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $professionals = [];
        $selectedProfessionalId = $user->id;

        // Role Logic: Admin/Coordinator can see others
        if (in_array($user->role, ['admin', 'coordinator'])) {
            $professionals = \App\Models\User::where('role', 'professional')
                ->orWhere('id', $user->id) // Include self if admin/coord also takes appts?
                ->get(['id', 'name', 'color']);
            
            if ($request->has('professional_id')) {
                $selectedProfessionalId = $request->input('professional_id');
            } else {
                // Default to first pro or self? 
                // Let's default to self if in list, else first pro.
                $selectedProfessionalId = $professionals->first()->id ?? $user->id;
            }
        } else {
            // Professional can only see self
             $selectedProfessionalId = $user->id;
        }

        // Fetch Appointments
        $appointments = \App\Models\Appointment::with('client')
            ->where('professional_id', $selectedProfessionalId)
            ->where('status', '!=', 'canceled')
            ->get();

        $appointmentEvents = $appointments->map(function ($appt) {
            return [
                'id' => $appt->id,
                'title' => $appt->client->name ?? 'Cita',
                'start' => $appt->start_at,
                'end' => $appt->end_at,
                'backgroundColor' => $appt->status === 'completed' ? '#10B981' : '#3B82F6',
                'borderColor' => $appt->status === 'completed' ? '#10B981' : '#3B82F6',
                'type' => 'appointment',
                'editable' => true,
                'client_id' => $appt->client_id,
                'professional_id' => $appt->professional_id,
                'status' => $appt->status,
                'notes' => $appt->notes,
            ];
        });

        // Fetch Blocked Periods
        $blockedPeriods = \App\Models\BlockedPeriod::where('user_id', $selectedProfessionalId)
            ->get();

        $blockedEvents = $blockedPeriods->map(function ($block) {
            return [
                'id' => $block->id,
                'title' => 'Bloqueado: ' . ($block->reason ?? 'N/A'),
                'start' => $block->start_at,
                'end' => $block->end_at,
                'backgroundColor' => '#6B7280', // Gray
                'borderColor' => '#6B7280',
                'display' => 'background', // Or just a block event? 'background' makes it non-interactive usually.
                // Let's make it a normal event but gray so they can click and delete it.
                'type' => 'blocked',
                'editable' => false, // Can't drag drop blocks for now
            ];
        });

        return \Inertia\Inertia::render('Calendar/Index', [
            'events' => $appointmentEvents->concat($blockedEvents),
            'professionals' => $professionals,
            'clients' => \App\Models\Client::orderBy('name')->get(['id', 'name']),
            'selectedProfessionalId' => (int)$selectedProfessionalId,
        ]);
    }
}
