<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

abstract class Controller
{
    protected function serverError(Throwable $exception, string $context): JsonResponse
    {
        return $this->loggedError($exception, $context, 'Server Error', 500);
    }

    protected function upstreamError(Throwable $exception, string $context, string $message): JsonResponse
    {
        return $this->loggedError($exception, $context, $message, 502);
    }

    private function loggedError(Throwable $exception, string $context, string $message, int $status): JsonResponse
    {
        $errorId = (string) Str::uuid();

        Log::error($context, [
            'error_id' => $errorId,
            'exception' => $exception,
        ]);

        return response()->json([
            'message' => $message,
            'error_id' => $errorId,
        ], $status);
    }
}
