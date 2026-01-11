<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ConversationService
{
    /**
     * Get conversation messages for a specific session (phone number).
     *
     * @param string $sessionId
     * @return Collection
     */
    public function getConversationBySession(string $sessionId): Collection
    {
        return DB::connection('supabase')
            ->table('whatsapp_messages') // Assuming the table name is whatsapp_messages based on context
            ->where('session_id', $sessionId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($message) {
                // Ensure the message field (JSON) is decoded if it's still a string
                // Depending on the driver, it might already be an array/object.
                if (is_string($message->message)) {
                    $decoded = json_decode($message->message, true);
                    $message->message = $decoded ?: $message->message;
                }
                return $message;
            });
    }
}
