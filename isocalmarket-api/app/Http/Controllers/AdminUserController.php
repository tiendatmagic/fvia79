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
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'role' => $user->getRole(),
                    'balance' => $user->getBalance(),
                    'api_key' => $user->getApiKey(),
                    'google2fa_enabled' => $user->isGoogle2faEnabled(),
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
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,customer',
            'balance' => 'required|numeric|min:0',
            'password' => 'required|string|min:6',
        ]);

        try {
            $apiKey = 'B2B-KEY-' . Str::random(16);

            $user = new User(
                null,
                $request->name,
                $request->email,
                Hash::make($request->password),
                $request->role,
                (float)$request->balance,
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
                    'id' => $savedUser->getId(),
                    'name' => $savedUser->getName(),
                    'email' => $savedUser->getEmail(),
                    'phone' => $savedUser->getPhone(),
                    'role' => $savedUser->getRole(),
                    'balance' => $savedUser->getBalance(),
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
            'name' => 'required|string|max:255|unique:users,name,' . $id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|in:admin,customer',
            'balance' => 'required|numeric|min:0',
            'password' => 'nullable|string|min:6',
            'status' => 'nullable|string|in:active,banned',
            'generate_api_key' => 'nullable|boolean',
            'revoke_api_key' => 'nullable|boolean'
        ]);

        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Edit basic details
            $updatedUser = new User(
                $user->getId(),
                $request->name,
                $request->email,
                $request->password ? Hash::make($request->password) : $user->getPassword(),
                $request->role,
                (float)$request->balance,
                $user->getRefreshTokenHash(),
                $user->getRefreshTokenExpiredAt(),
                $request->phone,
                $user->getGoogle2faSecret(),
                $user->isGoogle2faEnabled(),
                $user->getApiKey(),
                $request->status ?? $user->getStatus()
            );

            if ($request->input('generate_api_key')) {
                $updatedUser->setApiKey('B2B-KEY-' . Str::random(16));
            } elseif ($request->input('revoke_api_key')) {
                $updatedUser->setApiKey(null);
            }

            $savedUser = $this->userRepository->save($updatedUser);

            return response()->json([
                'message' => 'User updated successfully.',
                'user' => [
                    'id' => $savedUser->getId(),
                    'name' => $savedUser->getName(),
                    'email' => $savedUser->getEmail(),
                    'phone' => $savedUser->getPhone(),
                    'role' => $savedUser->getRole(),
                    'balance' => $savedUser->getBalance(),
                    'api_key' => $savedUser->getApiKey(),
                    'status' => $savedUser->getStatus()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user: ' . $e->getMessage()], 500);
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
}
