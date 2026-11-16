<?php

namespace App\Domain\Complaints\Services;

use App\Domain\Complaints\Enums\ComplaintStatus;
use InvalidArgumentException;

class ComplaintStateMachine
{
    /**
     * Peta transisi status yang sah sesuai SAP Bagian 10.
     *
     * @var array<string, array<string>>
     */
    protected static array $allowedTransitions = [
        'DRAFT' => ['SUBMITTED'],
        'SUBMITTED' => ['VERIFICATION', 'VERIFIED', 'DISPATCHED', 'REJECTED'],
        'VERIFICATION' => ['VERIFIED', 'DISPATCHED', 'REJECTED'],
        'VERIFIED' => ['DISPATCHED', 'IN_PROGRESS', 'REJECTED'],
        'DISPATCHED' => ['IN_PROGRESS', 'WAITING_VERIFICATION'],
        'IN_PROGRESS' => ['WAITING_VERIFICATION'],
        'WAITING_VERIFICATION' => ['COMPLETED', 'REVISION_REQUIRED'],
        'REVISION_REQUIRED' => ['IN_PROGRESS', 'WAITING_VERIFICATION'],
        'COMPLETED' => ['CLOSED'],
        'REJECTED' => ['SUBMITTED'],
        'CLOSED' => [],
    ];

    /**
     * Periksa apakah transisi dari status asal ke status tujuan diizinkan.
     */
    public static function canTransition(string|ComplaintStatus $from, string|ComplaintStatus $to): bool
    {
        $fromKey = $from instanceof ComplaintStatus ? $from->value : $from;
        $toKey = $to instanceof ComplaintStatus ? $to->value : $to;

        if (!isset(self::$allowedTransitions[$fromKey])) {
            return false;
        }

        return in_array($toKey, self::$allowedTransitions[$fromKey], true);
    }

    /**
     * Validasi transisi dan lemparkan exception jika tidak valid.
     */
    public static function validateTransition(string|ComplaintStatus $from, string|ComplaintStatus $to): void
    {
        $fromKey = $from instanceof ComplaintStatus ? $from->value : $from;
        $toKey = $to instanceof ComplaintStatus ? $to->value : $to;

        if (!self::canTransition($fromKey, $toKey)) {
            throw new InvalidArgumentException("Perubahan status tidak sah: dari {$fromKey} ke {$toKey}.");
        }
    }

    /**
     * Validasi penolakan resmi oleh Super Admin dari status non-arsip.
     */
    public static function validateAdminRejection(string|ComplaintStatus $from): void
    {
        $fromKey = $from instanceof ComplaintStatus ? $from->value : $from;

        if ($fromKey === ComplaintStatus::REJECTED->value) {
            throw new InvalidArgumentException('Laporan sudah berada pada status ditolak.');
        }

        if ($fromKey === ComplaintStatus::CLOSED->value) {
            throw new InvalidArgumentException('Laporan yang sudah ditutup permanen tidak dapat ditolak.');
        }
    }
}
