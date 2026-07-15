<?php

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\JwtService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    private UserRepositoryInterface $userRepository;
    private JwtService $jwtService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        JwtService $jwtService
    ) {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }

    public function register(string $name, string $email, string $password): User
    {
        $existing = $this->userRepository->findByEmail($email);
        if ($existing) {
            throw new \Exception('Email already registered.');
        }

        $hashedPassword = Hash::make($password);
        $user = new User(null, $name, $email, $hashedPassword, 'customer');
        return $this->userRepository->save($user);
    }

    public function login(string $login, string $password): ?User
    {
        // Tìm kiếm người dùng bằng cả email hoặc username (name)
        $user = $this->userRepository->findByEmailOrUsername($login);

        if (!$user) {
            return null;
        }

        if (!Hash::check($password, $user->getPassword())) {
            return null;
        }

        return $user;
    }


    public function generateAccessToken(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()
        ];
        // Hết hạn sau 30 phút (1800 giây)
        return $this->jwtService->generateToken($payload, 1800);
    }

    public function generateRefreshToken(User $user): string
    {
        $rawToken = Str::random(64);
        $hash = hash('sha256', $rawToken);
        $expiredAt = (new \DateTime())->modify('+7 days');

        $user->setRefreshToken($hash, $expiredAt);
        $this->userRepository->save($user);

        return $rawToken;
    }

    public function refresh(string $refreshToken): ?array
    {
        $hash = hash('sha256', $refreshToken);
        
        // Tìm user có refresh token hash trùng khớp
        $userModel = \App\Infrastructure\Persistence\Eloquent\UserModel::where('refresh_token_hash', $hash)
            ->where('refresh_token_expired_at', '>', now())
            ->first();

        if (!$userModel) {
            return null;
        }

        $user = \App\Infrastructure\Persistence\Mappers\UserMapper::toDomain($userModel);
        
        // Rotate token: Sinh mới cả access và refresh token
        $newAccessToken = $this->generateAccessToken($user);
        $newRefreshToken = $this->generateRefreshToken($user);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'user' => $user
        ];
    }

    public function logout(string $userId): void
    {
        $user = $this->userRepository->findById($userId);
        if ($user) {
            $user->setRefreshToken(null, null);
            $this->userRepository->save($user);
        }
    }
}
