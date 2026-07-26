<?php

return [
    'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
    'mysql_binary' => env('MYSQL_BINARY', 'mysql'),
    'max_upload_kilobytes' => (int) env('DATABASE_BACKUP_MAX_UPLOAD_KB', 102400),
    'process_timeout_seconds' => (int) env('DATABASE_BACKUP_TIMEOUT', 900),
    'retention_days' => (int) env('DATABASE_BACKUP_RETENTION_DAYS', 30),
    'maintenance_retry_seconds' => (int) env('DATABASE_RESTORE_RETRY_SECONDS', 60),
];
