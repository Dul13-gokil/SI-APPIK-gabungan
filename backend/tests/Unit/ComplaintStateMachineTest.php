<?php

namespace Tests\Unit;

use App\Domain\Complaints\Services\ComplaintStateMachine;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ComplaintStateMachineTest extends TestCase
{
    public function test_valid_state_transitions(): void
    {
        // Valid transitions according to SAP Section 10
        $this->assertTrue(ComplaintStateMachine::canTransition('DRAFT', 'SUBMITTED'));
        $this->assertTrue(ComplaintStateMachine::canTransition('SUBMITTED', 'VERIFICATION'));
        $this->assertTrue(ComplaintStateMachine::canTransition('VERIFICATION', 'VERIFIED'));
        $this->assertTrue(ComplaintStateMachine::canTransition('VERIFIED', 'DISPATCHED'));
        $this->assertTrue(ComplaintStateMachine::canTransition('DISPATCHED', 'IN_PROGRESS'));
        $this->assertTrue(ComplaintStateMachine::canTransition('IN_PROGRESS', 'WAITING_VERIFICATION'));
        $this->assertTrue(ComplaintStateMachine::canTransition('WAITING_VERIFICATION', 'COMPLETED'));
        $this->assertTrue(ComplaintStateMachine::canTransition('COMPLETED', 'CLOSED'));
    }

    public function test_invalid_state_transition_throws_exception(): void
    {
        $this->assertFalse(ComplaintStateMachine::canTransition('DRAFT', 'COMPLETED'));
        $this->assertFalse(ComplaintStateMachine::canTransition('CLOSED', 'IN_PROGRESS'));

        $this->expectException(InvalidArgumentException::class);
        ComplaintStateMachine::validateTransition('DRAFT', 'COMPLETED');
    }
}
