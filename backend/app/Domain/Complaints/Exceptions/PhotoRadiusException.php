<?php

namespace App\Domain\Complaints\Exceptions;

use InvalidArgumentException;

/**
 * Foto bukti pengerjaan diambil di luar radius lokasi aduan.
 */
class PhotoRadiusException extends InvalidArgumentException
{
    public static function outside(float $distanceM, int $radiusM): self
    {
        return new self(sprintf(
            'Foto pengerjaan diambil di luar radius lokasi aduan. Jarak foto %s m, batas yang diizinkan %d m. '
            . 'Ambil foto dari lokasi kerusakan.',
            number_format($distanceM, 0, ',', '.'),
            $radiusM
        ));
    }

    public static function locationMissing(): self
    {
        return new self(
            'Lokasi pengambilan foto tidak terdeteksi. Aktifkan izin lokasi (GPS) pada perangkat, lalu ulangi pengiriman bukti.'
        );
    }
}
