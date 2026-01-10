<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlockedPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'reason' => 'nullable|string',
        ]);
        
        // Authorization check?
        // For now, assume auth middleware covers it.
        // Ideally: check if auth()->user()->id == user_id OR auth()->user()->role is admin/coordinator.
        
        $currentUser = auth()->user();
        if ($currentUser->role === 'professional' && $currentUser->id != $validated['user_id']) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $blockedPeriod = \App\Models\BlockedPeriod::create($validated);

        return response()->json($blockedPeriod, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blockedPeriod = \App\Models\BlockedPeriod::findOrFail($id);

        $currentUser = auth()->user();
        if ($currentUser->role === 'professional' && $currentUser->id != $blockedPeriod->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $blockedPeriod->delete();

        return response()->noContent();
    }
}
