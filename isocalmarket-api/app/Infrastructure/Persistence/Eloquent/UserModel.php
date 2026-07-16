<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'refresh_token_hash', 'refresh_token_expired_at', 'google2fa_secret', 'google2fa_enabled'])]
#[Hidden(['password', 'remember_token'])]
class UserModel extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'users';

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'refresh_token_expired_at' => 'datetime',
            'google2fa_enabled' => 'boolean',
        ];
    }
}
