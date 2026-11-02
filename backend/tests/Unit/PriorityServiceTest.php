<?php

namespace Tests\Unit;

use App\Domain\Complaints\Services\PriorityService;
use PHPUnit\Framework\TestCase;

class PriorityServiceTest extends TestCase
{
    public function test_calculate_priority_score_and_levels(): void
    {
        $service = new PriorityService();

        // 1. Test Low Severity & Support
        $lowResult = $service->calculate(
            severityScore: 1.0,
            supportCount: 0,
            locationImportance: 0.2,
            ageInHours: 0,
            duplicateCount: 0
        );

        $this->assertIsFloat($lowResult['score']);
        $this->assertEquals('LOW', $lowResult['level']);
        $this->assertLessThan(25.0, $lowResult['score']);

        // 2. Test High/Critical Damage
        $criticalResult = $service->calculate(
            severityScore: 5.0,
            supportCount: 150,
            locationImportance: 1.0,
            ageInHours: 72,
            duplicateCount: 5
        );

        $this->assertGreaterThanOrEqual(75.0, $criticalResult['score']);
        $this->assertEquals('CRITICAL', $criticalResult['level']);
        $this->assertArrayHasKey('factors', $criticalResult);
    }
}
