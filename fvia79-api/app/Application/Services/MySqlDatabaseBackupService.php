<?php

namespace App\Application\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class MySqlDatabaseBackupService
{
    private const SUPPORTED_DRIVERS = ['mysql', 'mariadb'];

    public function status(): array
    {
        $driver = (string) config('database.default');

        return [
            'driver' => $driver,
            'supported' => in_array($driver, self::SUPPORTED_DRIVERS, true),
            'export_available' => $this->findExecutable((string) config('database_backup.mysqldump_binary')) !== null,
            'restore_available' => $this->findExecutable((string) config('database_backup.mysql_binary')) !== null,
            'max_upload_megabytes' => round((int) config('database_backup.max_upload_kilobytes') / 1024, 1),
            'php_upload_max_filesize' => ini_get('upload_max_filesize') ?: 'N/A',
            'php_post_max_size' => ini_get('post_max_size') ?: 'N/A',
            'requires_otp' => (bool) auth()->user()?->google2fa_enabled,
        ];
    }

    public function createDownload(int|string $adminId): array
    {
        return $this->withOperationLock(function () use ($adminId): array {
            $directory = storage_path('app/private/database-backups/downloads');
            File::ensureDirectoryExists($directory);
            $filename = 'fvia79-'.now()->format('Ymd-His').'.sql';
            $path = $directory.'/'.Str::uuid().'.sql';

            try {
                $this->dumpTo($path);
                Log::notice('Database backup exported.', [
                    'admin_id' => $adminId,
                    'filename' => $filename,
                    'size_bytes' => File::size($path),
                ]);

                return ['path' => $path, 'filename' => $filename];
            } catch (Throwable $exception) {
                File::delete($path);
                Log::error('Database backup export failed.', [
                    'admin_id' => $adminId,
                    'exception' => $exception->getMessage(),
                ]);
                throw $exception;
            }
        });
    }

    public function restore(UploadedFile $upload, int|string $adminId): array
    {
        return $this->withOperationLock(function () use ($upload, $adminId): array {
            $this->assertSupportedDriver();
            $this->requireExecutable((string) config('database_backup.mysql_binary'));
            $this->requireExecutable((string) config('database_backup.mysqldump_binary'));

            $directory = storage_path('app/private/database-backups');
            File::ensureDirectoryExists($directory);
            $preRestorePath = $directory.'/pre-restore-'.now()->format('Ymd-His').'-'.Str::uuid().'.sql';
            $maintenanceEnabled = false;
            $safetyBackupCreated = false;
            $importStarted = false;

            try {
                Artisan::call('down', [
                    '--retry' => (int) config('database_backup.maintenance_retry_seconds'),
                ]);
                $maintenanceEnabled = true;

                $this->dumpTo($preRestorePath);
                $safetyBackupCreated = true;
                $importStarted = true;
                $this->resetDatabase();
                $this->importFrom($upload->getRealPath());
                $this->refreshApplicationState();
                $this->pruneOldBackups($directory);

                Log::warning('Database restored from uploaded SQL backup.', [
                    'admin_id' => $adminId,
                    'uploaded_filename' => $upload->getClientOriginalName(),
                    'uploaded_size_bytes' => $upload->getSize(),
                    'safety_backup' => basename($preRestorePath),
                ]);

                return [
                    'message' => 'Database restored successfully. A safety backup was retained on the server.',
                    'safety_backup' => basename($preRestorePath),
                ];
            } catch (Throwable $restoreException) {
                $rollbackSucceeded = false;

                if ($importStarted && $safetyBackupCreated) {
                    try {
                        $this->resetDatabase();
                        $this->importFrom($preRestorePath);
                        $this->refreshApplicationState();
                        $rollbackSucceeded = true;
                    } catch (Throwable $rollbackException) {
                        Log::critical('Database restore and automatic rollback both failed.', [
                            'admin_id' => $adminId,
                            'restore_exception' => $restoreException->getMessage(),
                            'rollback_exception' => $rollbackException->getMessage(),
                            'safety_backup' => basename($preRestorePath),
                        ]);
                    }
                }

                if (! $importStarted) {
                    File::delete($preRestorePath);
                    Log::error('Database restore aborted because the safety backup failed.', [
                        'admin_id' => $adminId,
                        'exception' => $restoreException->getMessage(),
                    ]);

                    throw new RuntimeException(
                        'Restore was cancelled because the safety backup could not be created. The current database was not changed.',
                        previous: $restoreException
                    );
                }

                Log::error('Database restore failed.', [
                    'admin_id' => $adminId,
                    'exception' => $restoreException->getMessage(),
                    'automatic_rollback_succeeded' => $rollbackSucceeded,
                ]);

                throw new RuntimeException(
                    $rollbackSucceeded
                        ? 'The SQL import failed. The database was automatically restored to its previous state.'
                        : 'The SQL import failed and automatic recovery was unsuccessful. Manual recovery is required.',
                    previous: $restoreException
                );
            } finally {
                if ($maintenanceEnabled) {
                    Artisan::call('up');
                }
            }
        });
    }

    private function dumpTo(string $path): void
    {
        $this->assertSupportedDriver();
        $binary = $this->requireExecutable((string) config('database_backup.mysqldump_binary'));
        $stream = fopen($path, 'wb');

        if ($stream === false) {
            throw new RuntimeException('Unable to create the database backup file.');
        }

        fwrite($stream, "-- Fvia79 MySQL backup\n");
        fwrite($stream, '-- Generated at: '.now()->toIso8601String()."\n\n");

        try {
            $process = new Process([
                $binary,
                '--single-transaction',
                '--quick',
                '--skip-lock-tables',
                '--hex-blob',
                '--default-character-set=utf8mb4',
                ...$this->connectionArguments(),
                $this->databaseName(),
            ], null, $this->processEnvironment());
            $process->setTimeout((int) config('database_backup.process_timeout_seconds'));

            $process->run(function (string $type, string $buffer) use ($stream): void {
                if ($type === Process::OUT) {
                    fwrite($stream, $buffer);
                }
            });

            if (! $process->isSuccessful()) {
                throw new RuntimeException('mysqldump failed: '.$this->safeProcessError($process));
            }
        } finally {
            fclose($stream);
        }
    }

    private function importFrom(string $path): void
    {
        $binary = $this->requireExecutable((string) config('database_backup.mysql_binary'));
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new RuntimeException('Unable to read the SQL backup file.');
        }

        try {
            $process = new Process([
                $binary,
                '--binary-mode=1',
                '--one-database',
                '--default-character-set=utf8mb4',
                ...$this->connectionArguments(),
                $this->databaseName(),
            ], null, $this->processEnvironment());
            $process->setInput($stream);
            $process->setTimeout((int) config('database_backup.process_timeout_seconds'));
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('mysql restore failed: '.$this->safeProcessError($process));
            }
        } finally {
            fclose($stream);
        }
    }

    private function resetDatabase(): void
    {
        $database = $this->databaseName();
        $objects = DB::select(
            'SELECT TABLE_NAME AS object_name, TABLE_TYPE AS object_type
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ?
             ORDER BY CASE WHEN TABLE_TYPE = ? THEN 0 ELSE 1 END',
            [$database, 'VIEW']
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($objects as $object) {
                $name = str_replace('`', '``', (string) $object->object_name);
                $objectType = strtoupper((string) $object->object_type) === 'VIEW'
                    ? 'VIEW'
                    : 'TABLE';
                DB::statement("DROP {$objectType} IF EXISTS `{$name}`");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function connectionArguments(): array
    {
        $connection = $this->connectionConfig();
        $arguments = ['--user='.(string) $connection['username']];

        if (! empty($connection['unix_socket'])) {
            $arguments[] = '--socket='.(string) $connection['unix_socket'];
        } else {
            $arguments[] = '--host='.(string) $connection['host'];
            $arguments[] = '--port='.(string) $connection['port'];
        }

        return $arguments;
    }

    private function processEnvironment(): array
    {
        $environment = [
            'MYSQL_PWD' => (string) ($this->connectionConfig()['password'] ?? ''),
        ];

        if (PHP_OS_FAMILY !== 'Windows') {
            return $environment;
        }

        // MySQL CLI needs the Windows runtime and Winsock provider paths.
        // Explicitly propagate them because PHP's built-in web server may not
        // include all parent environment variables in spawned processes.
        foreach (['SystemRoot', 'WINDIR', 'COMSPEC', 'PATH', 'PATHEXT', 'TEMP', 'TMP'] as $key) {
            $value = getenv($key);
            if (is_string($value) && $value !== '') {
                $environment[$key] = $value;
            }
        }

        return $environment;
    }

    private function connectionConfig(): array
    {
        return (array) config('database.connections.'.config('database.default'));
    }

    private function databaseName(): string
    {
        $database = (string) ($this->connectionConfig()['database'] ?? '');

        if ($database === '') {
            throw new RuntimeException('The MySQL database name is not configured.');
        }

        return $database;
    }

    private function assertSupportedDriver(): void
    {
        if (! in_array(config('database.default'), self::SUPPORTED_DRIVERS, true)) {
            throw new RuntimeException('Database backup only supports MySQL and MariaDB.');
        }
    }

    private function requireExecutable(string $configuredBinary): string
    {
        $binary = $this->findExecutable($configuredBinary);

        if ($binary === null) {
            throw new RuntimeException("Required database executable is unavailable: {$configuredBinary}.");
        }

        return $binary;
    }

    private function findExecutable(string $configuredBinary): ?string
    {
        if (str_contains($configuredBinary, '/') || str_contains($configuredBinary, '\\')) {
            return is_file($configuredBinary) && is_executable($configuredBinary) ? $configuredBinary : null;
        }

        return (new ExecutableFinder)->find($configuredBinary);
    }

    private function safeProcessError(Process $process): string
    {
        $error = trim($process->getErrorOutput());

        return Str::limit($error !== '' ? $error : 'unknown database client error', 1000);
    }

    private function refreshApplicationState(): void
    {
        DB::purge(config('database.default'));
        DB::reconnect(config('database.default'));
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
    }

    private function pruneOldBackups(string $directory): void
    {
        $cutoff = now()->subDays((int) config('database_backup.retention_days'))->getTimestamp();

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() === 'sql' && $file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
            }
        }
    }

    private function withOperationLock(callable $operation): mixed
    {
        $directory = storage_path('app/private/database-backups');
        File::ensureDirectoryExists($directory);
        $handle = fopen($directory.'/.operation.lock', 'c+');

        if ($handle === false || ! flock($handle, LOCK_EX | LOCK_NB)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new RuntimeException('Another database backup or restore operation is already running.');
        }

        try {
            return $operation();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
