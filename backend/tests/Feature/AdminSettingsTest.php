<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tahap 4: pengaturan ambang AI dari panel Super Admin, dan peringatan
 * di portal operator saat layanan AI terakhir gagal.
 */
class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        Category::create([
            'name' => 'Jalan & Jembatan',
            'department_id' => $department->id,
            'severity_default' => 3,
        ]);
    }

    private function user(string $role): User
    {
        $this->seq++;

        return User::create([
            'name' => "Pengguna {$role} {$this->seq}",
            'email' => strtolower($role) . "{$this->seq}@example.test",
            'phone' => '08126666' . str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => $role,
        ]);
    }

    public function test_super_admin_sees_settings_page_with_current_values(): void
    {
        SystemSetting::set('ai_verify_threshold', 0.85, 'float');

        $admin = $this->user('SUPERADMIN');

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertOk();
        $response->assertSee('Ambang verifikasi otomatis');
        $response->assertSee('value="0.85"', false);
        $response->assertSee('Pengaturan Sistem');
    }

    public function test_non_admin_cannot_open_or_save_settings(): void
    {
        $operator = $this->user('OPERATOR');

        $this->actingAs($operator)->get(route('admin.settings'))->assertForbidden();
        $this->actingAs($operator)
            ->post(route('admin.settings.update'), [
                'ai_verify_threshold' => 0.9,
                'ai_completion_threshold' => 0.9,
            ])
            ->assertForbidden();
    }

    public function test_saving_settings_persists_values_and_writes_audit(): void
    {
        $admin = $this->user('SUPERADMIN');

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'ai_verify_threshold' => 0.92,
            'ai_completion_threshold' => 0.88,
            'photo_radius_require_location' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame(0.92, SystemSetting::get('ai_verify_threshold'));
        $this->assertSame(0.88, SystemSetting::get('ai_completion_threshold'));
        $this->assertTrue(SystemSetting::get('photo_radius_require_location'));

        $log = AuditLog::where('action', 'SETTINGS_UPDATED')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(0.92, (float) $log->after_json['ai_verify_threshold']);
    }

    public function test_out_of_range_threshold_is_rejected(): void
    {
        $admin = $this->user('SUPERADMIN');

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'ai_verify_threshold' => 0.10,
                'ai_completion_threshold' => 0.99,
            ])
            ->assertSessionHasErrors('ai_verify_threshold');

        $this->assertNull(SystemSetting::get('ai_verify_threshold'));
    }

    public function test_operator_dashboard_warns_when_ai_recently_failed(): void
    {
        SystemSetting::set('ai_last_failure_at', now()->subHour()->toIso8601String(), 'string');

        $operator = $this->user('OPERATOR');

        $this->actingAs($operator)
            ->get(route('operator.dashboard'))
            ->assertOk()
            ->assertSee('Klasifikasi AI sedang bermasalah');
    }

    public function test_operator_dashboard_has_no_warning_when_last_failure_is_old(): void
    {
        SystemSetting::set('ai_last_failure_at', now()->subDays(3)->toIso8601String(), 'string');

        $operator = $this->user('OPERATOR');

        $this->actingAs($operator)
            ->get(route('operator.dashboard'))
            ->assertOk()
            ->assertDontSee('Klasifikasi AI sedang bermasalah');
    }
}
