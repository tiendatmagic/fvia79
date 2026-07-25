<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Security\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_backup_endpoints_require_an_admin(): void
    {
        $this->getJson('/api/v1/admin/database-backup/status')->assertUnauthorized();

        $customer = $this->createUser('customer');
        $this->withToken($this->tokenFor($customer))
            ->getJson('/api/v1/admin/database-backup/status')
            ->assertForbidden();
    }

    public function test_admin_can_read_backup_capabilities_without_exposing_credentials(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/admin/database-backup/status')
            ->assertOk()
            ->assertJsonStructure([
                'driver',
                'supported',
                'export_available',
                'restore_available',
                'max_upload_megabytes',
                'php_upload_max_filesize',
                'php_post_max_size',
                'requires_otp',
            ]);

        $response->assertJsonMissingPath('password')
            ->assertJsonMissingPath('username')
            ->assertJsonMissingPath('host');
    }

    public function test_export_requires_the_current_admin_password(): void
    {
        $admin = $this->createUser('admin');

        $this->withToken($this->tokenFor($admin))
            ->postJson('/api/v1/admin/database-backup/export', [
                'password' => 'incorrect-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_restore_rejects_non_sql_files_and_requires_explicit_confirmation(): void
    {
        $admin = $this->createUser('admin');

        $this->withToken($this->tokenFor($admin))
            ->post('/api/v1/admin/database-backup/restore', [
                'password' => 'password123',
                'confirmation' => 'RESTORE',
                'backup_file' => UploadedFile::fake()->create('backup.txt', 1, 'text/plain'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('backup_file');

        $this->withToken($this->tokenFor($admin))
            ->post('/api/v1/admin/database-backup/restore', [
                'password' => 'password123',
                'confirmation' => 'confirm',
                'backup_file' => UploadedFile::fake()->create('backup.sql', 1, 'text/plain'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('confirmation');
    }

    private function createUser(string $role): UserModel
    {
        return UserModel::create([
            'username' => 'backup_'.$role,
            'name' => 'Backup '.$role,
            'email' => "backup-{$role}@example.com",
            'password' => 'password123',
            'role' => $role,
            'balance' => 0,
            'google2fa_enabled' => false,
            'status' => 'active',
        ]);
    }

    private function tokenFor(UserModel $user): string
    {
        return app(JwtService::class)->generateToken([
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ver' => $user->token_version,
        ], 1800);
    }
}
