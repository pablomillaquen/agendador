<?php

namespace App\Http\Controllers;

use App\Services\ConversationService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Show the conversation for a given session ID (phone number).
     *
     * @param string $sessionId
     * @return \Illuminate\View\View
     */
    public function show(string $sessionId)
    {
        $messages = $this->conversationService->getConversationBySession($sessionId);

        return view('conversations.show', [
            'sessionId' => $sessionId,
            'messages' => $messages,
        ]);
    }
}
