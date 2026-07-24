<?php

namespace App\Http\Controllers;

use App\Application\Services\MySqlDatabaseBackupService;
use App\Application\Services\SensitiveAdminActionService;
use App\Http\Requests\AuthorizeDatabaseBackupRequest;
use App\Http\Requests\RestoreDatabaseBackupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    public function __construct(
        private readonly MySqlDatabaseBackupService $backupService,
        private readonly SensitiveAdminActionService $authorizationService
    ) {}

    public function status(): JsonResponse
    {
        return response()->json($this->backupService->status());
    }

    public function export(AuthorizeDatabaseBackupRequest $request): BinaryFileResponse|JsonResponse
    {
        $user = $request->user();
        $this->authorizationService->authorize(
            $user,
            $request->string('password')->toString(),
            $request->input('otp_code')
        );

        try {
            $backup = $this->backupService->createDownload($user->id);

            return response()
                ->download($backup['path'], $backup['filename'], ['Content-Type' => 'application/sql'])
                ->deleteFileAfterSend(true);
        } catch (RuntimeException $exception) {
            Log::error('Unable to prepare database export.', ['exception' => $exception->getMessage()]);

            return response()->json(['message' => __('messages.database_backup_unavailable')], 500);
        }
    }

    public function restore(RestoreDatabaseBackupRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->authorizationService->authorize(
            $user,
            $request->string('password')->toString(),
            $request->input('otp_code')
        );

        try {
            return response()->json(
                $this->backupService->restore($request->file('backup_file'), $user->id)
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
