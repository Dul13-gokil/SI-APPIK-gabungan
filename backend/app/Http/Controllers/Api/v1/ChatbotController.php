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
        $userId = $user?->id;
        // Cegah spoofing role dari client: hanya akun terautentikasi yang bisa memakai role non-warga
        $requestedRole = strtoupper((string) $request->input('role', 'WARGA'));
        $role = $user?->role ?? (in_array($requestedRole, ['WARGA', 'TAMU'], true) ? $requestedRole : 'WARGA');

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
            'message' => 'required|string|max:2000',
        ]);

        $session = ChatSession::findOrFail($sessionId);
        $authUser = AuthController::resolveUser($request);

        // Proteksi BOLA/IDOR: sesi milik akun terdaftar tidak boleh diakses pengguna lain
        if ($session->user_id !== null && (!$authUser || (int) $session->user_id !== (int) $authUser->id)) {
            return ApiResponse::error('Anda tidak memiliki izin mengakses sesi percakapan ini.', [], 403);
        }

        $user = $authUser ?? $session->user;
        $requestedRole = strtoupper((string) $request->input('role', 'WARGA'));
        $role = $user?->role ?? (in_array($requestedRole, ['WARGA', 'TAMU'], true) ? $requestedRole : 'WARGA');

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
