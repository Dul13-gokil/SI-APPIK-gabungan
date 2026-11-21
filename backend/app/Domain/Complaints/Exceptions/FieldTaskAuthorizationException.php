<?php

namespace App\Domain\Complaints\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan pengerjaan lapangan (team shield atau kuota tugas).
 * Membawa kode HTTP agar dirender konsisten oleh bootstrap/app.php:
 * 403 untuk regu yang salah, 422 untuk kuota tugas penuh.
 */
class FieldTaskAuthorizationException extends RuntimeException
{
    public function __construct(string $message, public readonly int $statusCode = 403)
    {
        parent::__construct($message);
    }

    public static function wrongTeam(string $message): self
    {
        return new self($message, 403);
    }

    public static function quotaExceeded(int $limit): self
    {
        return new self(
            "Kuota tugas aktif regu Anda sudah penuh ({$limit} tugas berstatus Dikerjakan/Revisi). "
            . 'Selesaikan dan kirim bukti minimal satu tugas terlebih dahulu sebelum mengambil tugas baru.',
            422
        );
    }
}
