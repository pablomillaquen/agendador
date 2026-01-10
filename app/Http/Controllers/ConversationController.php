<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Conversation::with('client');
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        return $query->latest('started_at')->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     * Supports creating a full conversation with messages if 'messages' array is present.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'channel' => 'string|in:whatsapp',
            'started_at' => 'date',
            'ended_at' => 'nullable|date',
            'messages' => 'nullable|array',
            'messages.*.sender' => 'required_with:messages|in:client,bot',
            'messages.*.content' => 'required_with:messages|string',
            'messages.*.timestamp' => 'nullable|date',
        ]);

        $conversation = \App\Models\Conversation::create([
            'client_id' => $validated['client_id'],
            'channel' => $validated['channel'] ?? 'whatsapp',
            'started_at' => $validated['started_at'] ?? now(),
            'ended_at' => $validated['ended_at'] ?? null,
        ]);

        if (isset($validated['messages']) && is_array($validated['messages'])) {
            foreach ($validated['messages'] as $msg) {
                $conversation->messages()->create([
                    'sender' => $msg['sender'],
                    'content' => $msg['content'],
                    'timestamp' => $msg['timestamp'] ?? now(),
                ]);
            }
        }

        return $conversation->load('messages');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return \App\Models\Conversation::with(['messages', 'client'])->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $conversation = \App\Models\Conversation::findOrFail($id);
        
        $validated = $request->validate([
            'ended_at' => 'date',
        ]);

        $conversation->update($validated); // Mainly for closing the conversation

        return $conversation;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        \App\Models\Conversation::destroy($id);
        return response()->noContent();
    }
}
