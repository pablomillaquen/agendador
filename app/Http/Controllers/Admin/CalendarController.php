<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $appointments = \App\Models\Appointment::with('client')
            ->where('status', '!=', 'canceled')
            ->get();

        $events = $appointments->map(function ($appt) {
            return [
                'id' => $appt->id,
                'title' => $appt->client->name ?? 'Cita',
                'start' => $appt->start_at,
                'end' => $appt->end_at,
                'backgroundColor' => $appt->status === 'completed' ? '#10B981' : '#3B82F6',
                'borderColor' => $appt->status === 'completed' ? '#10B981' : '#3B82F6',
            ];
        });

        return \Inertia\Inertia::render('Calendar/Index', [
            'events' => $events,
        ]);
    }
}
