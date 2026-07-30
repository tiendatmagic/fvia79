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

    public function index(Request $request)
    {
        try {
            $page = max(1, (int) $request->query('page', 1));
            $limit = min(20, max(1, (int) $request->query('limit', 10)));
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
                    'uid' => $user->getUid(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'phone' => $user->getPhone(),
                    'role' => $user->getRole(),
                    'balance' => $user->getBalance(),
                    'total_deposited' => $user->getTotalDeposited(),
                    'membership_tier_id' => $user->getMembershipTierId(),
                    'membership_tier' => $user->getMembershipTier(),
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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch admin users.');
        }
    }

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
                    'uid'     => $savedUser->getUid(),
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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to create admin-managed user.');
        }
    }

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

                $targetStatus = $request->status ?? $user->getStatus();
                $isBanned = $targetStatus === 'banned';

                $updatedUser = new User(
                    $user->getId(),
                    $username,
                    $displayName,
                    $request->email,
                    $request->password ? Hash::make($request->password) : $user->getPassword(),
                    $request->role,
                    $user->getBalance(),
                    $isBanned ? null : $user->getRefreshTokenHash(),
                    $isBanned ? null : $user->getRefreshTokenExpiredAt(),
                    $request->phone,
                    $user->getGoogle2faSecret(),
                    $user->isGoogle2faEnabled(),
                    $user->getApiKey(),
                    $targetStatus,
                    $user->getUid()
                );

                if ($request->input('generate_api_key')) {
                    $updatedUser->setApiKey(\App\Infrastructure\Persistence\Eloquent\UserModel::generateUniqueApiKey());
                } elseif ($request->input('revoke_api_key')) {
                    $updatedUser->setApiKey(null);
                }

                $savedUser = $this->userRepository->save($updatedUser);

                if ($isBanned) {
                    \App\Infrastructure\Persistence\Eloquent\UserModel::whereKey($id)->increment('token_version');
                }

                return response()->json([
                    'message' => 'User updated successfully.',
                    'user' => [
                        'id'      => $savedUser->getId(),
                        'uid'     => $savedUser->getUid(),
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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to update admin-managed user.');
        }
    }

    public function adjustBalance(Request $request, $id)
    {
        $request->validate([
            'type'   => 'required|string|in:add,subtract',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
            'affect_cumulative_deposit' => 'nullable|boolean',
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
                $affectCumulative = (bool) $request->input('affect_cumulative_deposit', false);
                $featureTiersEnabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
                if (! $featureTiersEnabled) {
                    $affectCumulative = false;
                }
                $currentBalance = $user->getBalance();

                if ($type === 'subtract' && $currentBalance < $amount) {
                    return response()->json([
                        'message' => 'Insufficient user balance. Cannot subtract more than current balance.'
                    ], 400);
                }

                if ($type === 'add') {
                    $user->addBalance($amount);
                    if ($affectCumulative) {
                        $user->addTotalDeposited($amount);
                    }
                } else {
                    $user->deductBalance($amount);
                    if ($affectCumulative) {
                        $user->deductTotalDeposited($amount);
                    }
                }

                $savedUser = $this->userRepository->save($user);

                $userModel = \App\Infrastructure\Persistence\Eloquent\UserModel::find($savedUser->getId());
                if ($userModel) {
                    app(\App\Application\Services\MembershipTierService::class)->recalculateUserTier($userModel);
                    $savedUser = $this->userRepository->findById($savedUser->getId());
                }

                \Illuminate\Support\Facades\Log::info("Admin [" . Auth::id() . "] adjusted balance for User [{$id}]: Type={$type}, Amount={$amount}, AffectCumulative=" . ($affectCumulative ? 'YES' : 'NO') . ", Reason='{$reason}', NewBalance=" . $savedUser->getBalance() . ", NewTotalDeposited=" . $savedUser->getTotalDeposited());

                return response()->json([
                    'message' => 'User balance adjusted successfully.',
                    'user' => [
                        'id'              => $savedUser->getId(),
                        'uid'             => $savedUser->getUid(),
                        'name'            => $savedUser->getName(),
                        'email'           => $savedUser->getEmail(),
                        'balance'         => $savedUser->getBalance(),
                        'total_deposited' => $savedUser->getTotalDeposited(),
                        'membership_tier' => $savedUser->getMembershipTier(),
                    ]
                ]);
            });
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to adjust user balance.');
        }
    }

    public function adjustCumulativeDeposit(Request $request, $id)
    {
        $request->validate([
            'type'   => 'required|string|in:add,subtract,set',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                $user = $this->userRepository->findByIdForUpdate($id);
                if (!$user) {
                    return response()->json(['message' => 'User not found.'], 404);
                }

                $type   = $request->input('type');
                $amount = (float) $request->input('amount');
                $reason = trim($request->input('reason'));

                if ($type === 'add') {
                    $user->addTotalDeposited($amount);
                } elseif ($type === 'subtract') {
                    $user->deductTotalDeposited($amount);
                } elseif ($type === 'set') {
                    $user->setTotalDeposited($amount);
                }

                $savedUser = $this->userRepository->save($user);

                $userModel = \App\Infrastructure\Persistence\Eloquent\UserModel::find($savedUser->getId());
                if ($userModel) {
                    app(\App\Application\Services\MembershipTierService::class)->recalculateUserTier($userModel);
                    $savedUser = $this->userRepository->findById($savedUser->getId());
                }

                \Illuminate\Support\Facades\Log::info("Admin [" . Auth::id() . "] adjusted cumulative deposit for User [{$id}]: Type={$type}, Amount={$amount}, Reason='{$reason}', NewTotalDeposited=" . $savedUser->getTotalDeposited());

                return response()->json([
                    'message' => 'User cumulative deposit adjusted successfully.',
                    'user' => [
                        'id'              => $savedUser->getId(),
                        'uid'             => $savedUser->getUid(),
                        'name'            => $savedUser->getName(),
                        'email'           => $savedUser->getEmail(),
                        'balance'         => $savedUser->getBalance(),
                        'total_deposited' => $savedUser->getTotalDeposited(),
                        'membership_tier' => $savedUser->getMembershipTier(),
                    ]
                ]);
            });
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to adjust user cumulative deposit.');
        }
    }

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
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'message' => 'Cannot delete user because they have existing transactions (deposits or orders). Please ban the user instead.'
                ], 400);
            }
            return $this->serverError($e, 'Failed to delete admin-managed user.');
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to delete admin-managed user.');
        }
    }

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
                null,
                false,
                $user->getApiKey(),
                $user->getStatus(),
                $user->getUid()
            );

            $this->userRepository->save($updatedUser);

            return response()->json(['message' => '2FA has been disabled for this user.']);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to reset user 2FA.');
        }
    }

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
                $secret,
                true,
                $user->getApiKey(),
                $user->getStatus(),
                $user->getUid()
            );

            $this->userRepository->save($updatedUser);

            $qrCodeUrl = \App\Infrastructure\Security\Google2FA::getQrCodeUrl($user->getEmail(), $secret);

            return response()->json([
                'message' => '2FA has been enabled for this user successfully.',
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to enable user 2FA.');
        }
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'required|string',
            'status'     => 'required|string|in:active,banned',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $currentAdminId = Auth::id();
                $targetIds = array_values(array_filter($request->input('user_ids'), function ($id) use ($currentAdminId) {
                    return $id !== $currentAdminId;
                }));

                if (empty($targetIds)) {
                    return response()->json(['message' => 'No valid target users selected for status update.'], 400);
                }

                $status = $request->input('status');

                if ($status === 'banned') {
                    \App\Infrastructure\Persistence\Eloquent\UserModel::whereIn('id', $targetIds)->update([
                        'status' => 'banned',
                        'refresh_token_hash' => null,
                        'refresh_token_expired_at' => null,
                    ]);
                    \App\Infrastructure\Persistence\Eloquent\UserModel::whereIn('id', $targetIds)->increment('token_version');
                } else {
                    \App\Infrastructure\Persistence\Eloquent\UserModel::whereIn('id', $targetIds)->update([
                        'status' => 'active',
                    ]);
                }

                return response()->json([
                    'message' => 'Bulk user status updated successfully.',
                    'updated_count' => count($targetIds)
                ]);
            });
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to update bulk user status.');
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'required|string',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $currentAdminId = Auth::id();
                $targetIds = array_values(array_filter($request->input('user_ids'), function ($id) use ($currentAdminId) {
                    return $id !== $currentAdminId;
                }));

                if (empty($targetIds)) {
                    return response()->json(['message' => 'No valid target users selected for deletion.'], 400);
                }

                $deletedCount = \App\Infrastructure\Persistence\Eloquent\UserModel::whereIn('id', $targetIds)->delete();

                return response()->json([
                    'message' => 'Bulk user deletion successful.',
                    'deleted_count' => $deletedCount
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'message' => 'Cannot delete user(s) because they have existing transactions (deposits or orders). Please ban the user instead.'
                ], 400);
            }
            return $this->serverError($e, 'Failed to perform bulk user deletion.');
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to perform bulk user deletion.');
        }
    }
}
