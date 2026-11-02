<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApiResponse
{
    /**
     * Standardized Success Response according to SAP Section 14.
     */
    public static function success(mixed $data = null, string $message = 'Sukses', int $status = 200, array $extraMeta = []): JsonResponse
    {
        $meta = array_merge([
            'request_id' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
        ], $extraMeta);

        $payload = [
            'data' => $data,
            'meta' => $meta,
        ];

        if ($message && $status !== 200) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    /**
     * Standardized Error Response according to SAP Section 14.
     */
    public static function error(string $message, array|int $errors = [], int $status = 400, array $extraMeta = []): JsonResponse
    {
        if (is_int($errors)) {
            $status = $errors;
            $errors = [];
        }

        $meta = array_merge([
            'request_id' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
        ], $extraMeta);

        $payload = [
            'message' => $message,
            'errors' => empty($errors) ? (object)[] : $errors,
            'meta' => $meta,
        ];

        return response()->json($payload, $status);
    }
}
