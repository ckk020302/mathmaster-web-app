<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Chat;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->input('message');

        // Check if the message contains math-related content
        if (Chat::isMathMessage($userMessage)) {
            $reply = "I'm sorry, but I can't help with math questions or calculations. This is a counseling chat designed to provide emotional support and guidance. If you need help with math problems, please consider using a math-specific tool or tutoring service. Is there anything else I can help you with regarding personal matters or counseling?";
        } else {
            // Send non-math messages to Gemini for counseling responses
            $reply = Chat::sendToGemini($userMessage);
        }

        return response()->json([
            'reply' => $reply,
            'is_math_blocked' => Chat::isMathMessage($userMessage)
        ]);
    }

    public function showCounselingChat()
    {
        return view('counseling_chat');
    }
}