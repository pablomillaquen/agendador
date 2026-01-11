<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - {{ $sessionId }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .chat-container {
            background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
            background-repeat: repeat;
            background-attachment: fixed;
        }
    </style>
</head>
<body class="bg-gray-100 h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-[#075e54] text-white p-4 shadow-md flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="javascript:history.back()" class="hover:bg-[#128c7e] p-2 rounded-full transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="font-bold text-lg">{{ $sessionId }}</h1>
                <p class="text-xs text-gray-200">Conversación de WhatsApp</p>
            </div>
        </div>
    </header>

    <!-- Chat Area -->
    <main class="flex-grow overflow-y-auto p-4 space-y-4 chat-container">
        @forelse($messages as $msg)
            @php
                $isAi = ($msg->type === 'ai');
                $messageText = is_array($msg->message) ? ($msg->message['text'] ?? json_encode($msg->message)) : $msg->message;
            @endphp

            <div class="flex {{ $isAi ? 'justify-start' : 'justify-end' }}">
                <div class="max-w-[75%] rounded-lg px-4 py-2 shadow-sm relative {{ $isAi ? 'bg-white text-gray-800 rounded-tl-none' : 'bg-[#dcf8c6] text-gray-800 rounded-tr-none' }}">
                    <p class="text-sm whitespace-pre-wrap">{{ $messageText }}</p>
                    <div class="text-[10px] text-gray-500 mt-1 text-right">
                        {{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="flex justify-center items-center h-full">
                <div class="bg-white px-6 py-4 rounded-lg shadow-md text-gray-500 text-sm">
                    No hay mensajes en esta conversación.
                </div>
            </div>
        @endforelse
    </main>

    <!-- Footer Space (Empty as it's read-only) -->
    <footer class="bg-[#f0f0f0] p-4 border-t border-gray-300 text-center text-xs text-gray-500">
        Esta es una vista de solo lectura del historial de WhatsApp.
    </footer>
</body>
</html>
