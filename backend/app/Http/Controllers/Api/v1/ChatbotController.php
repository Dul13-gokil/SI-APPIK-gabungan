<?php

namespace App\Http\Controllers\Api\v1;

use App\Domain\Chatbot\Services\ChatbotService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(protected ChatbotService $chatbotService) {}

    public function createSession(Request $request): JsonResponse
    {
        $user = AuthController::resolveUser($request);
        $userId = $user?->id ?? 1;
        $role = $user?->role ?? $request->input('role') ?? 'WARGA';

        $session = ChatSession::create([
            'user_id' => $userId,
            'current_state' => ChatbotService::STATE_START,
            'draft_json' => [],
            'is_completed' => false,
        ]);

        $initial = $this->chatbotService->handleMessage(
            ChatbotService::STATE_START,
            'START',
            [],
            $role
        );

        $session->update([
            'current_state' => $initial['next_state'],
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'BOT',
            'message' => $initial['reply'],
        ]);

        return ApiResponse::success([
            'session_id' => $session->id,
            'state' => $session->current_state,
            'initial_message' => $initial['reply'],
        ], 'Sesi chatbot berhasil dibuat', 201);
    }

    public function sendMessage(Request $request, string $sessionId): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $session = ChatSession::findOrFail($sessionId);
        $user = $session->user ?? AuthController::resolveUser($request);
        $role = $user?->role ?? $request->input('role') ?? 'WARGA';

        // Record User Message
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'USER',
            'message' => $request->message,
        ]);

        // Process via Chatbot Domain Service
        $result = $this->chatbotService->handleMessage(
            $session->current_state,
            $request->message,
            $session->draft_json ?? [],
            $role
        );

        // Update Session State & Draft
        $session->update([
            'current_state' => $result['next_state'],
            'draft_json' => $result['draft'],
            'is_completed' => $result['is_complete'],
        ]);

        // Record Bot Reply
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'BOT',
            'message' => $result['reply'],
            'payload_json' => $result['draft'],
        ]);

        return ApiResponse::success([
            'session_id' => $session->id,
            'current_state' => $session->current_state,
            'reply' => $result['reply'],
            'draft' => $result['draft'],
            'is_completed' => $result['is_complete'],
        ]);
    }
}
