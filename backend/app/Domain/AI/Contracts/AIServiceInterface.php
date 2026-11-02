<?php

namespace App\Domain\AI\Contracts;

use Illuminate\Http\UploadedFile;

interface AIServiceInterface
{
    /**
     * Klasifikasikan foto kerusakan infrastruktur.
     *
     * @param UploadedFile|string $file File foto atau path
     * @return array{
     *   category_label: string,
     *   confidence: float,
     *   severity_level: int,
     *   model_version: string,
     *   is_valid_infrastructure: bool
     * }
     */
    public function classifyInfrastructureDamage(UploadedFile|string $file): array;
}
