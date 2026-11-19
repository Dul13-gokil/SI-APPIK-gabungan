<?php

namespace App\Domain\Complaints\Enums;

enum ComplaintStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case VERIFICATION = 'VERIFICATION';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
    case VERIFIED = 'VERIFIED';
    case DISPATCHED = 'DISPATCHED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case WAITING_VERIFICATION = 'WAITING_VERIFICATION';
    case REVISION_REQUIRED = 'REVISION_REQUIRED';
    case COMPLETED = 'COMPLETED';
    case CLOSED = 'CLOSED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::SUBMITTED => 'Menunggu',
            self::VERIFICATION => 'Dalam Verifikasi',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::VERIFIED => 'Terverifikasi',
            self::DISPATCHED => 'Ditugaskan',
            self::IN_PROGRESS => 'Dikerjakan',
            self::WAITING_VERIFICATION => 'Menunggu Verifikasi',
            self::REVISION_REQUIRED => 'Perlu Revisi',
            self::COMPLETED => 'Selesai',
            self::CLOSED => 'Ditutup',
        };
    }
}
