<?php

namespace App\Http\Requests;

class RestoreDatabaseBackupRequest extends AuthorizeDatabaseBackupRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'backup_file' => [
                'required',
                'file',
                'extensions:sql',
                'max:'.config('database_backup.max_upload_kilobytes'),
            ],
            'confirmation' => ['required', 'in:RESTORE'],
        ]);
    }
}
