<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Appointment::with('client');

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('date')) {
            $query->whereDate('start_at', $request->date);
        }

        return $query->latest('start_at')->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'status' => 'in:scheduled,canceled,completed',
            'notes' => 'nullable|string',
        ]);

        // Check for overlaps
        if ($this->hasOverlap($validated['start_at'], $validated['end_at'])) {
            return response()->json(['message' => 'The selected time slot is already booked.'], 409);
        }

        return \App\Models\Appointment::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return \App\Models\Appointment::with('client')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
            'start_at' => 'date',
            'end_at' => 'date|after:start_at',
            'status' => 'in:scheduled,canceled,completed',
            'notes' => 'nullable|string',
        ]);

        // Check for overlaps if dates are changing
        if (isset($validated['start_at']) || isset($validated['end_at'])) {
            $start = $validated['start_at'] ?? $appointment->start_at;
            $end = $validated['end_at'] ?? $appointment->end_at;
            
            if ($this->hasOverlap($start, $end, $id)) {
                 return response()->json(['message' => 'The selected time slot is already booked.'], 409);
            }
        }

        $appointment->update($validated);

        return $appointment;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        
        // Soft logic: cancel instead of delete? MVP says delete is allowed but maybe set to canceled
        // User requirements: "Eliminar cita". I'll delete it.
        $appointment->delete();

        return response()->noContent();
    }

    private function hasOverlap($start, $end, $excludeId = null)
    {
        $query = \App\Models\Appointment::where('status', '!=', 'canceled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                  ->orWhereBetween('end_at', [$start, $end])
                  ->orWhere(function ($q) use ($start, $end) {
                      $q->where('start_at', '<', $start)
                        ->where('end_at', '>', $end);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Refined overlap check: (StartA < EndB) and (EndA > StartB)
        // The above whereBetween might be slightly inaccurate for exact boundaries.
        // Let's use the standard formula:
        // Overlap = (StartA < EndB) && (EndA > StartB)
        // But in Eloquent:
        return \App\Models\Appointment::where('status', '!=', 'canceled')
            ->where(function ($q) use ($start, $end) {
                $q->where('start_at', '<', $end)
                  ->where('end_at', '>', $start);
            })
            ->when($excludeId, function ($q) use ($excludeId) {
                return $q->where('id', '!=', $excludeId);
            })
            ->exists();
    }
}
