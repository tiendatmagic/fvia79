<?php

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\UserSessionModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
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

    public function register(string $username, ?string $name, string $email, string $password): User
    {
        $existingEmail = $this->userRepository->findByEmail($email);
        if ($existingEmail) {
            throw new \Exception('Email already registered.');
        }

        $existingUsername = $this->userRepository->findByEmailOrUsername($username);
        if ($existingUsername) {
            throw new \Exception('Username already taken.');
        }

        $displayName = ! empty($name) ? $name : $username;
        $hashedPassword = Hash::make($password);
        $user = new User(null, $username, $displayName, $email, $hashedPassword, 'customer');

        return $this->userRepository->save($user);
    }

    public function login(string $login, string $password): ?User
    {
        $user = $this->userRepository->findByEmailOrUsername($login);

        if (! $user) {
            return null;
        }

        if (! Hash::check($password, $user->getPassword())) {
            return null;
        }

        if ($user->isBanned()) {
            throw new \Exception('Your account has been banned. Please contact administrator.');
        }

        return $user;
    }

    public function createSession(User $user, ?string $ip = null, ?string $userAgent = null): UserSessionModel
    {
        $userSessions = UserSessionModel::where('user_id', $user->getId())
            ->orderBy('last_activity_at', 'asc')
            ->get();

        if ($userSessions->count() >= 10) {
            $toDeleteCount = $userSessions->count() - 9;
            $userSessions->take($toDeleteCount)->each(fn ($s) => $s->delete());
        }

        return UserSessionModel::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->getId(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_name' => UserSessionModel::parseDeviceName($userAgent),
            'last_activity_at' => now(),
        ]);
    }

    public function generateAccessToken(User $user, ?string $sessionId = null): string
    {
        $tokenVersion = (int) UserModel::query()
            ->whereKey($user->getId())
            ->value('token_version');

        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'ver' => $tokenVersion,
        ];

        if ($sessionId) {
            $payload['sid'] = $sessionId;
        }

        return $this->jwtService->generateToken($payload, 1800);
    }

    public function generateRefreshToken(User $user): string
    {
        $rawToken = Str::random(64);
        $hash = hash('sha256', $rawToken);
        $expiredAt = (new \DateTime)->modify('+7 days');

        $user->setRefreshToken($hash, $expiredAt);
        $this->userRepository->save($user);

        return $rawToken;
    }

    public function refresh(string $refreshToken, ?string $sessionId = null, ?string $ip = null, ?string $userAgent = null): ?array
    {
        $hash = hash('sha256', $refreshToken);

        $userModel = UserModel::where('refresh_token_hash', $hash)
            ->where('refresh_token_expired_at', '>', now())
            ->first();

        if (! $userModel || $userModel->status === 'banned') {
            return null;
        }

        if ($sessionId) {
            $session = UserSessionModel::where('id', $sessionId)
                ->where('user_id', $userModel->id)
                ->first();

            if (! $session) {
                return null;
            }

            $session->update(['last_activity_at' => now()]);
        } else {
            $user = UserMapper::toDomain($userModel);
            $session = UserSessionModel::where('user_id', $userModel->id)
                ->orderByDesc('last_activity_at')
                ->first();

            if (! $session) {
                $session = $this->createSession($user, $ip, $userAgent);
            } else {
                $session->update(['last_activity_at' => now()]);
            }
            $sessionId = $session->id;
        }

        $user = UserMapper::toDomain($userModel);

        $newAccessToken = $this->generateAccessToken($user, $sessionId);
        $newRefreshToken = $this->generateRefreshToken($user);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'user' => $user,
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

    public function generateTemp2FaToken(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'scope' => '2fa',
        ];

        return $this->jwtService->generateToken($payload, 300);
    }

    public function validateTemp2FaToken(string $tempToken): ?string
    {
        $payload = $this->jwtService->validateToken($tempToken);
        if (! $payload || empty($payload['sub']) || ($payload['scope'] ?? '') !== '2fa') {
            return null;
        }

        return $payload['sub'];
    }

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
