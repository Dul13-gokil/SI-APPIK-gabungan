<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\Contracts\AIServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AICVService implements AIServiceInterface
{
    protected string $modelVersion = 'cv-spatial-opencv-v2.5';

    protected function pythonBinary(): string
    {
        $configured = env('AI_PYTHON_BINARY');
        if (is_string($configured) && $configured !== '') {
            return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configured);
        }

        // Laragon di Windows tidak selalu mewarisi PATH dari pemasang Python.
        // Gunakan instalasi lokal yang ada bila tersedia.
        $windowsPython = getenv('LOCALAPPDATA')
            ? getenv('LOCALAPPDATA') . '\\Programs\\Python\\Python310\\python.exe'
            : '';

        return $windowsPython && file_exists($windowsPython) ? $windowsPython : 'python';
    }

    /**
     * Inferensi klasifikasi dan diagnosa dimensi teknis kerusakan infrastruktur
     * Menggunakan Computer Vision OpenCV & Spatial Geometry Perspective Analysis.
     */
    public function classifyInfrastructureDamage(UploadedFile|string $file): array
    {
        return $this->classifyMultipleImages([$file]);
    }

    /**
     * Inferensi multi-foto gabungan (multi-angle consensus)
     */
    public function classifyMultipleImages(array $files, bool $roadOnly = false): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->getRealPath();
            } elseif (is_string($file) && file_exists($file)) {
                $paths[] = $file;
            }
        }

        if (empty($paths)) {
            return $this->getUnverifiedFallback();
        }

        try {
            $scriptPath = base_path('app/Domain/AI/Scripts/ai_analyzer.py');

            if (file_exists($scriptPath)) {
                $escapedArgs = array_map('escapeshellarg', $paths);
                // Ruas panjang hanya boleh dinilai sebagai kerusakan badan jalan,
                // sehingga PJU/trotoar tidak dapat mengubah kategorinya.
                $command = escapeshellarg($this->pythonBinary()) . ' ' . escapeshellarg($scriptPath)
                    . ' ' . implode(' ', $escapedArgs) . ' 2>&1';
                $output = shell_exec($command);

                if ($output) {
                    $trimmed = trim($output);
                    $json = json_decode($trimmed, true);
                    if (!is_array($json)) {
                        $start = strpos($trimmed, '{');
                        $end = strrpos($trimmed, '}');
                        if ($start !== false && $end !== false && $end > $start) {
                            $json = json_decode(substr($trimmed, $start, $end - $start + 1), true);
                        }
                    }
                    if (is_array($json) && !isset($json['error']) && isset($json['category_id'])) {
                        return $json;
                    }
                    Log::warning("AICVService non-json output: " . substr($output, 0, 500));
                }
            }
        } catch (\Throwable $e) {
            Log::error("AICVService Python Execution failed: " . $e->getMessage());
        }

        // Jangan pernah menebak foto sebagai jalan ketika model gagal dijalankan.
        // Pengguna harus melihat bahwa foto belum dapat diverifikasi.
        return $this->getUnverifiedFallback();
    }

    protected function getUnverifiedFallback(): array
    {
        return [
            'is_valid_infrastructure'  => false,
            'is_damaged'               => false,
            'category_id'              => 1,
            'category_label'           => 'Foto Belum Terverifikasi',
            'confidence'               => 0,
            'severity_level'           => 0,
            'severity_text'            => 'BELUM DAPAT DIVERIFIKASI',
            'damage_type'              => 'Analisis Foto Tidak Tersedia',
            'damage_detail'            => 'Foto tidak dapat diverifikasi oleh AI. Silakan coba kembali atau unggah foto jalan yang jelas.',
            'suggested_title'          => '',
            'suggested_road_condition' => '',
            'llm_description'          => '',
            'model_version'            => $this->modelVersion,
        ];
    }
}
