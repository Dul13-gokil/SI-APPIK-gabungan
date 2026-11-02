<?php

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\Complaint;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndOtpAuditTest extends TestCase
{
    public function test_unauthenticated_toggle_support_returns_401(): void
    {
        $complaint = Complaint::first();
        if (!$complaint) {
            $this->markTestSkipped('No complaint in DB');
        }

        $response = $this->postJson("/api/v1/complaints/{$complaint->id}/supports");
        $response->assertStatus(401);
    }

    public function test_reporter_phone_masked_for_public(): void
    {
        $complaint = Complaint::first();
        if (!$complaint) {
            $this->markTestSkipped('No complaint in DB');
        }

        $response = $this->getJson("/api/v1/complaints/{$complaint->id}");
        $response->assertStatus(200);

        $data = $response->json('data');
        if (!empty($data['reporter_phone'])) {
            // For public / non-internal viewer, phone must be masked (contains ****)
            $this->assertStringContainsString('****', $data['reporter_phone']);
        }
    }

    public function test_chatbot_send_message_loads_session_properly(): void
    {
        $session = ChatSession::create([
            'user_id' => 1,
            'current_state' => 'START',
            'draft_json' => [],
            'is_completed' => false,
        ]);

        $response = $this->postJson("/api/v1/chat/sessions/{$session->id}/messages", [
            'message' => 'Halo bot',
        ]);

        $this->assertNotEquals(500, $response->status());
        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.reply'));
    }

    public function test_otp_generation_and_registration_flow(): void
    {
        $testEmail = 'audit_warga_' . time() . '@kolakautara.go.id';

        // 1. Request OTP
        $sendOtpRes = $this->postJson('/api/v1/auth/register/send-otp', [
            'email' => $testEmail,
        ]);
        $sendOtpRes->assertStatus(200);

        $otpCode = $sendOtpRes->json('data.dev_otp');
        $this->assertNotEmpty($otpCode);

        // 2. Register with invalid OTP should fail (422)
        $failRes = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Warga Audit',
            'email'                 => $testEmail,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'otp'                   => '000000',
        ]);
        $failRes->assertStatus(422);

        // 3. Register with correct OTP succeeds (201)
        $okRes = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Warga Audit',
            'email'                 => $testEmail,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'otp'                   => $otpCode,
        ]);
        $okRes->assertStatus(201);
        $this->assertNotNull($okRes->json('data.token'));
    }

    public function test_forgot_password_otp_allowed_for_warga_and_blocked_for_superadmin(): void
    {
        $superadmin = User::where('role', 'SUPERADMIN')->first();
        if ($superadmin) {
            $blockedRes = $this->postJson('/api/v1/auth/forgot-password/send-otp', [
                'email' => $superadmin->email,
            ]);
            $blockedRes->assertStatus(403);
        }

        $warga = User::where('role', 'WARGA')->first();
        if ($warga) {
            $okOtpRes = $this->postJson('/api/v1/auth/forgot-password/send-otp', [
                'email' => $warga->email,
            ]);
            $okOtpRes->assertStatus(200);
            $otp = $okOtpRes->json('data.dev_otp');

            // Reset password
            $resetRes = $this->postJson('/api/v1/auth/forgot-password', [
                'email'                 => $warga->email,
                'otp'                   => $otp,
                'password'              => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);
            $resetRes->assertStatus(200);
        }
    }
}
