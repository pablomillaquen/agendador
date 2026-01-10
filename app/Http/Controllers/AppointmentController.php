<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Appointment::with(['client', 'professional']);

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('professional_id')) {
            $query->where('professional_id', $request->professional_id);
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
            'professional_id' => 'required|exists:users,id',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'status' => 'in:scheduled,canceled,completed',
            'notes' => 'nullable|string',
        ]);

        // Check for overlaps
        if ($this->hasOverlap($validated['start_at'], $validated['end_at'], null, $validated['professional_id'])) {
            return response()->json(['message' => 'The selected time slot is already booked for this professional.'], 409);
        }

        return \App\Models\Appointment::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return \App\Models\Appointment::with(['client', 'professional'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
            'professional_id' => 'exists:users,id',
            'start_at' => 'date',
            'end_at' => 'date|after:start_at',
            'status' => 'in:scheduled,canceled,completed',
            'notes' => 'nullable|string',
        ]);

        // Check for overlaps if dates or professional are changing
        if (isset($validated['start_at']) || isset($validated['end_at']) || isset($validated['professional_id'])) {
            $start = $validated['start_at'] ?? $appointment->start_at;
            $end = $validated['end_at'] ?? $appointment->end_at;
            $professionalId = $validated['professional_id'] ?? $appointment->professional_id;
            
            if ($this->hasOverlap($start, $end, $id, $professionalId)) {
                 return response()->json(['message' => 'The selected time slot is already booked for this professional.'], 409);
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
        $appointment->delete();

        return response()->noContent();
    }

    private function hasOverlap($start, $end, $excludeId = null, $professionalId = null)
    {
        return \App\Models\Appointment::where('status', '!=', 'canceled')
            ->when($professionalId, function ($q) use ($professionalId) {
                return $q->where('professional_id', $professionalId);
            })
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
