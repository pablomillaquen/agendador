<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'professional_id' => 'required|exists:users,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,confirmed,cancelled,completed',
        ]);

        Appointment::create($validated);

        return redirect()->back()->with('success', 'Cita creada correctamente.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'professional_id' => 'sometimes|exists:users,id',
            'start_at' => 'sometimes|date',
            'end_at' => 'sometimes|date|after:start_at',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:scheduled,confirmed,cancelled,completed',
        ]);

        $appointment->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'appointment' => $appointment]);
        }

        return redirect()->back()->with('success', 'Cita actualizada correctamente.');
    }
}
