<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatSession;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAndOtpAuditTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;
    protected Category $category;
    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $this->category = Category::create([
            'name' => 'Jalan Rusak',
            'department_id' => $this->department->id,
            'severity_default' => 4,
        ]);
    }

    private function makeUser(string $role, array $attributes = []): User
    {
        $this->sequence++;

        return User::create(array_merge([
            'name' => "Pengguna {$role} {$this->sequence}",
            'email' => strtolower($role) . "{$this->sequence}@example.test",
            'phone' => '08123456' . str_pad((string) $this->sequence, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => $role,
        ], $attributes));
    }

    private function makeComplaint(User $reporter, array $attributes = []): Complaint
    {
        $this->sequence++;

        return Complaint::create(array_merge([
            'public_code' => 'UJI-' . $this->sequence,
            'user_id' => $reporter->id,
            'category_id' => $this->category->id,
            'title' => 'Jalan berlubang',
            'description' => 'Lubang besar di badan jalan poros.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => -3.506,
            'longitude' => 120.887,
            'status' => 'SUBMITTED',
            'priority_score' => 10,
            'priority_level' => 'LOW',
        ], $attributes));
    }

    public function test_unauthenticated_toggle_support_returns_401(): void
    {
        $this->postJson('/api/v1/complaints/1/supports')->assertStatus(401);
    }

    public function test_reporter_phone_masked_for_public(): void
    {
        $reporter = $this->makeUser('WARGA', ['phone' => '081234567890']);
        $complaint = $this->makeComplaint($reporter);

        $response = $this->getJson("/api/v1/complaints/{$complaint->id}");
        $response->assertStatus(200);

        $phone = $response->json('data.reporter_phone');
        $this->assertNotNull($phone);
        $this->assertStringContainsString('****', $phone);
        $this->assertStringNotContainsString('081234567890', $phone);
    }

    public function test_chatbot_send_message_loads_session_properly(): void
    {
        $session = ChatSession::create([
            'user_id' => null,
            'current_state' => 'START',
            'draft_json' => [],
            'is_completed' => false,
        ]);

        $response = $this->postJson("/api/v1/chat/sessions/{$session->id}/messages", [
            'message' => 'Halo bot',
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.reply'));
    }

    public function test_otp_generation_and_registration_flow(): void
    {
        $email = 'audit_warga_' . uniqid() . '@kolakautara.go.id';

        $this->postJson('/api/v1/auth/register/send-otp', ['email' => $email])
            ->assertStatus(200);

        // Kode OTP tidak pernah dikirim ke klien; test membacanya langsung dari tabel.
        $otpCode = OtpVerification::where('email', $email)->latest('id')->value('code');
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $otpCode);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Warga Audit',
            'email' => $email,
            'phone' => '081298765432',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'otp' => '000000',
        ])->assertStatus(422);

        $ok = $this->postJson('/api/v1/auth/register', [
            'name' => 'Warga Audit',
            'email' => $email,
            'phone' => '081298765432',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'otp' => $otpCode,
        ]);

        $ok->assertStatus(201);
        $this->assertNotNull($ok->json('data.token'));
        $this->assertDatabaseHas('users', ['email' => $email, 'role' => 'WARGA']);
    }

    public function test_forgot_password_otp_allowed_for_warga_and_blocked_for_superadmin(): void
    {
        $superadmin = $this->makeUser('SUPERADMIN');

        $this->postJson('/api/v1/auth/forgot-password/send-otp', ['email' => $superadmin->email])
            ->assertStatus(403);

        $warga = $this->makeUser('WARGA');

        $this->postJson('/api/v1/auth/forgot-password/send-otp', ['email' => $warga->email])
            ->assertStatus(200);

        $otp = OtpVerification::where('email', $warga->email)->latest('id')->value('code');

        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $warga->email,
            'otp' => $otp,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertStatus(200);

        $this->assertTrue(Hash::check('PasswordBaru123', $warga->fresh()->password));
    }
}
