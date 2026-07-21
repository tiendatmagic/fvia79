<?php

namespace App\Http\Controllers;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * GET /api/v1/admin/users
     * Lấy danh sách thành viên phân trang & tìm kiếm
     */
    public function index(Request $request)
    {
        try {
            $page = (int)$request->query('page', 1);
            $limit = (int)$request->query('limit', 10);
            $search = $request->query('search');
            $role = $request->query('role');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $result = $this->userRepository->findAllPaginated($page, $limit, $search, $role, $startDate, $endDate);
            $users = $result['items'];
            $total = $result['total'];

            $data = array_map(function (User $user) {
                return [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'role' => $user->getRole(),
                    'balance' => $user->getBalance(),
                    'api_key' => $user->getApiKey(),
                    'google2fa_enabled' => $user->isGoogle2faEnabled(),
                    'google2fa_secret' => $user->getGoogle2faSecret(),
                    'status' => $user->getStatus()
                ];
            }, $users);

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'last_page' => (int)ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cannot fetch users: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/admin/users
     * Tạo thành viên mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'name'     => 'nullable|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|string|in:admin,customer',
            'balance'  => 'nullable|numeric|min:0',
            'password' => 'required|string|min:6',
        ]);

        try {
            $apiKey = \App\Infrastructure\Persistence\Eloquent\UserModel::generateUniqueApiKey();
            $displayName = $request->filled('name')
                ? trim($request->input('name'))
                : $request->input('username');

            $user = new User(
                null,
                $request->username,
                $displayName,
                $request->email,
                Hash::make($request->password),
                $request->role,
                (float)($request->balance ?? 0),
                null,
                null,
                $request->phone,
                null,
                false,
                $apiKey
            );

            $savedUser = $this->userRepository->save($user);

            return response()->json([
                'message' => 'User created successfully.',
                'user' => [
                    'id'      => $savedUser->getId(),
                    'username'=> $savedUser->getUsername(),
                    'name'    => $savedUser->getName(),
                    'email'   => $savedUser->getEmail(),
                    'phone'   => $savedUser->getPhone(),
                    'role'    => $savedUser->getRole(),
                    'balance' => $savedUser->getBalance(),
                    'google2fa_enabled' => $savedUser->isGoogle2faEnabled(),
                    'google2fa_secret' => $savedUser->getGoogle2faSecret(),
                    'api_key' => $savedUser->getApiKey()
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/v1/admin/users/{id}
     * Cập nhật thành viên
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'username'         => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $id,
            'name'             => 'nullable|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone'            => 'nullable|string|max:20',
            'role'             => 'required|string|in:admin,customer',
            'balance'          => 'nullable|numeric|min:0',
            'password'         => 'nullable|string|min:6',
            'status'           => 'nullable|string|in:active,banned',
            'generate_api_key' => 'nullable|boolean',
            'revoke_api_key'   => 'nullable|boolean',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                $user = $this->userRepository->findByIdForUpdate($id);
                if (!$user) {
                    return response()->json(['message' => 'User not found.'], 404);
                }

                $username = $request->input('username', $user->getUsername());
                $displayName = $request->filled('name')
                    ? trim($request->input('name'))
                    : $username;

                $updatedUser = new User(
                    $user->getId(),
                    $username,
                    $displayName,
                    $request->email,
                    $request->password ? Hash::make($request->password) : $user->getPassword(),
                    $request->role,
                    $user->getBalance(),
                    $user->getRefreshTokenHash(),
                    $user->getRefreshTokenExpiredAt(),
                    $request->phone,
                    $user->getGoogle2faSecret(),
                    $user->isGoogle2faEnabled(),
                    $user->getApiKey(),
                    $request->status ?? $user->getStatus()
                );

                if ($request->input('generate_api_key')) {
                    $updatedUser->setApiKey(\App\Infrastructure\Persistence\Eloquent\UserModel::generateUniqueApiKey());
                } elseif ($request->input('revoke_api_key')) {
                    $updatedUser->setApiKey(null);
                }

                $savedUser = $this->userRepository->save($updatedUser);

                return response()->json([
                    'message' => 'User updated successfully.',
                    'user' => [
                        'id'      => $savedUser->getId(),
                        'username'=> $savedUser->getUsername(),
                        'name'    => $savedUser->getName(),
                        'email'   => $savedUser->getEmail(),
                        'phone'   => $savedUser->getPhone(),
                        'role'    => $savedUser->getRole(),
                        'balance' => $savedUser->getBalance(),
                        'google2fa_enabled' => $savedUser->isGoogle2faEnabled(),
                        'google2fa_secret' => $savedUser->getGoogle2faSecret(),
                        'api_key' => $savedUser->getApiKey(),
                        'status'  => $savedUser->getStatus()
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/admin/users/{id}/adjust-balance
     * Điều chỉnh số dư thành viên (Cộng / Trừ) có ghi nhận lý do
     */
    public function adjustBalance(Request $request, $id)
    {
        $request->validate([
            'type'   => 'required|string|in:add,subtract',
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                $user = $this->userRepository->findByIdForUpdate($id);
                if (!$user) {
                    return response()->json(['message' => 'User not found.'], 404);
                }

                $type           = $request->input('type');
                $amount         = (float) $request->input('amount');
                $reason         = trim($request->input('reason'));
                $currentBalance = $user->getBalance();

                if ($type === 'subtract' && $currentBalance < $amount) {
                    return response()->json([
                        'message' => 'Insufficient user balance. Cannot subtract more than current balance.'
                    ], 400);
                }

                if ($type === 'add') {
                    $user->addBalance($amount);
                } else {
                    $user->deductBalance($amount);
                }

                $savedUser = $this->userRepository->save($user);

                \Illuminate\Support\Facades\Log::info("Admin [" . Auth::id() . "] adjusted balance for User [{$id}]: Type={$type}, Amount={$amount}, Reason='{$reason}', NewBalance=" . $savedUser->getBalance());

                return response()->json([
                    'message' => 'User balance adjusted successfully.',
                    'user' => [
                        'id'      => $savedUser->getId(),
                        'name'    => $savedUser->getName(),
                        'email'   => $savedUser->getEmail(),
                        'balance' => $savedUser->getBalance(),
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to adjust balance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/v1/admin/users/{id}
     * Xóa thành viên
     */
    public function destroy($id)
    {
        try {
            $currentUserId = Auth::id();
            if ($currentUserId === $id) {
                return response()->json(['message' => 'You cannot delete your own account.'], 400);
            }

            $deleted = $this->userRepository->delete($id);
            if (!$deleted) {
                return response()->json(['message' => 'User not found or delete failed.'], 404);
            }

            return response()->json(['message' => 'User deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/admin/users/{id}/reset-2fa
     * Admin tắt 2FA cho thành viên (khi user mất thiết bị xác thực)
     */
    public function reset2fa($id)
    {
        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $updatedUser = new User(
                $user->getId(),
                $user->getUsername(),
                $user->getName(),
                $user->getEmail(),
                $user->getPassword(),
                $user->getRole(),
                $user->getBalance(),
                $user->getRefreshTokenHash(),
                $user->getRefreshTokenExpiredAt(),
                $user->getPhone(),
                null,  // google2fa_secret
                false, // google2fa_enabled
                $user->getApiKey(),
                $user->getStatus()
            );

            $this->userRepository->save($updatedUser);

            return response()->json(['message' => '2FA has been disabled for this user.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to reset 2FA: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/admin/users/{id}/enable-2fa
     * Admin bật 2FA giùm cho thành viên (sinh secret và kích hoạt luôn)
     */
    public function enable2fa($id)
    {
        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $secret = \App\Infrastructure\Security\Google2FA::generateSecretKey();

            $updatedUser = new User(
                $user->getId(),
                $user->getUsername(),
                $user->getName(),
                $user->getEmail(),
                $user->getPassword(),
                $user->getRole(),
                $user->getBalance(),
                $user->getRefreshTokenHash(),
                $user->getRefreshTokenExpiredAt(),
                $user->getPhone(),
                $secret,  // google2fa_secret
                true,     // google2fa_enabled
                $user->getApiKey(),
                $user->getStatus()
            );

            $this->userRepository->save($updatedUser);

            $qrCodeUrl = \App\Infrastructure\Security\Google2FA::getQrCodeUrl($user->getEmail(), $secret);

            return response()->json([
                'message' => '2FA has been enabled for this user successfully.',
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to enable 2FA: ' . $e->getMessage()], 500);
        }
    }
}
