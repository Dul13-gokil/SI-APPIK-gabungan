<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ImageWatermarkService
{
    /**
     * Tempelkan watermark resmi (SI-APPIK Kabupaten, Kode Aduan, Timestamp WITA, Koordinat GPS)
     * pada foto bukti pengerjaan lapangan menggunakan ekstensi PHP GD.
     */
    public function applyFieldWatermark(
        string $absolutePath,
        string $publicCode,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $officerName = null
    ): bool {
        if (!extension_loaded('gd') || !is_file($absolutePath) || !is_readable($absolutePath)) {
            return false;
        }

        // GD tanpa dukungan JPEG (imagejpeg tidak ada) membuat watermark gagal tanpa
        // jejak. Catat sekali sebagai error agar tidak lolos diam-diam.
        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagejpeg')) {
            Log::error('GD terpasang tanpa dukungan JPEG: watermark tanggal/koordinat tidak dapat diterapkan.');

            return false;
        }

        try {
            $info = @getimagesize($absolutePath);
            if (!$info || empty($info[0]) || empty($info[1])) {
                return false;
            }

            [$width, $height, $type] = $info;

            $image = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
                IMAGETYPE_PNG  => @imagecreatefrompng($absolutePath),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
                default        => false,
            };

            if (!$image) {
                return false;
            }

            $code = str_starts_with($publicCode, '#') ? $publicCode : "#{$publicCode}";
            $timestamp = now('Asia/Makassar')->format('d/m/Y H:i') . ' WITA';
            $gpsText = ($latitude !== null && $longitude !== null)
                ? sprintf('GPS: %.5f, %.5f', $latitude, $longitude)
                : 'Kabupaten Kolaka Utara';

            $line1 = "SI-APPIK KABUPATEN  |  {$code}  |  {$timestamp}";
            $line2 = $officerName ? "{$gpsText}  |  Petugas: {$officerName}" : $gpsText;

            $barHeight = max(46, (int) round($height * 0.085));
            $barHeight = min($barHeight, 76);

            imagealphablending($image, true);
            $bgOverlay = imagecolorallocatealpha($image, 15, 23, 42, 32); // #0F172A ~75% opacity
            $accentBar = imagecolorallocate($image, 37, 99, 235);        // #2563EB
            $white     = imagecolorallocate($image, 255, 255, 255);
            $muted     = imagecolorallocate($image, 203, 213, 225);      // #CBD5E1

            $yStart = max(0, $height - $barHeight);
            imagefilledrectangle($image, 0, $yStart, $width, $height, $bgOverlay);
            imagefilledrectangle($image, 0, $yStart, $width, $yStart + 3, $accentBar);

            $font1 = $width >= 720 ? 4 : 2;
            $font2 = $width >= 720 ? 3 : 1;
            $padX  = max(10, (int) round($width * 0.02));
            $y1    = $yStart + (int) round($barHeight * 0.22);
            $y2    = $yStart + (int) round($barHeight * 0.58);

            imagestring($image, $font1, $padX, $y1, $line1, $white);
            imagestring($image, $font2, $padX, $y2, $line2, $muted);

            $saved = match ($type) {
                IMAGETYPE_JPEG => @imagejpeg($image, $absolutePath, 88),
                IMAGETYPE_PNG  => @imagepng($image, $absolutePath, 6),
                IMAGETYPE_WEBP => function_exists('imagewebp') ? @imagewebp($image, $absolutePath, 86) : false,
                default        => false,
            };

            imagedestroy($image);

            return (bool) $saved;
        } catch (\Throwable $e) {
            Log::warning('Gagal menerapkan watermark foto lapangan: ' . $e->getMessage());
            return false;
        }
    }
}
