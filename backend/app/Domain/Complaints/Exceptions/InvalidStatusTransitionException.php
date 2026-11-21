<?php

namespace App\Domain\Complaints\Exceptions;

use InvalidArgumentException;

/**
 * Transisi status yang tidak diizinkan state machine.
 * Diturunkan dari InvalidArgumentException agar penanganan lama tetap kompatibel,
 * namun dirender sebagai HTTP 422 (bukan 500) oleh bootstrap/app.php.
 */
class InvalidStatusTransitionException extends InvalidArgumentException
{
    public static function between(string $from, string $to): self
    {
        return new self("Perubahan status tidak sah: dari {$from} ke {$to}.");
    }
}
