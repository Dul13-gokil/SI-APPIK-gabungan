<?php

namespace App\Http\Controllers\Api\v1;

use App\Domain\AI\Services\AICVService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIServiceController extends Controller
{
    public function __construct(protected AICVService $aiService) {}

    public function classifyImage(Request $request): JsonResponse
    {
        $request->validate([
            'photo'    => 'nullable|file|image|max:10240',
            'photos'   => 'nullable|array',
            'photos.*' => 'file|image|max:10240',
            'road_only' => 'nullable|boolean',
        ]);

        $files = [];
        if ($request->hasFile('photos')) {
            $files = $request->file('photos');
        } elseif ($request->hasFile('photo')) {
            $files = [$request->file('photo')];
        }

        if (empty($files)) {
            return ApiResponse::error('Wajib mengunggah minimal 1 foto bukti fisik.', 422);
        }

        $result = $this->aiService->classifyMultipleImages($files, $request->boolean('road_only'));

        return ApiResponse::success($result, 'Klasifikasi gambar berhasil diproses');
    }
}
