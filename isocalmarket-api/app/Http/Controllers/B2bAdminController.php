<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use Illuminate\Support\Facades\Auth;

class B2bAdminController extends Controller
{
    /**
     * GET /api/v1/admin/b2b/users
     * Lấy danh sách toàn bộ đại lý B2B
     */
    public function indexUsers(Request $request)
    {
        try {
            $users = UserModel::orderBy('created_at', 'desc')
                ->get()
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'role' => $u->role,
                        'balance' => (float)$u->balance,
                        'has_api_key' => !empty($u->api_key)
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch B2B users: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/admin/b2b/users/balance
     * Cộng/trừ tiền trực tiếp cho tài khoản thành viên
     */
    public function adjustBalance(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|string',
                'amount' => 'required|numeric'
            ]);

            $user = UserModel::find($request->input('user_id'));
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $amount = (float)$request->input('amount');
            $user->balance += $amount;
            $user->save();

            return response()->json([
                'success' => true,
                'user_id' => $user->id,
                'name' => $user->name,
                'new_balance' => (float)$user->balance
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to adjust balance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v1/admin/b2b/orders
     * Lấy danh sách toàn bộ đơn hàng B2B của tất cả đại lý
     */
    public function indexOrders(Request $request)
    {
        try {
            $page = (int)$request->query('page', 1);
            $limit = (int)$request->query('limit', 10);

            $query = OrderModel::with(['items', 'user'])
                ->orderBy('created_at', 'desc');

            $total = $query->count();
            $orders = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get()
                ->map(function ($order) {
                    $accounts = [];
                    foreach ($order->items as $item) {
                        if (is_array($item->delivered_keys)) {
                            $accounts = array_merge($accounts, $item->delivered_keys);
                        }
                    }
                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'user' => [
                            'id' => $order->user->id,
                            'name' => $order->user->name,
                            'email' => $order->user->email,
                        ],
                        'total_price' => (float)$order->total_price,
                        'status' => $order->status,
                        'created_at' => $order->created_at->toIso8601String(),
                        'accounts_count' => count($accounts)
                    ];
                });

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'last_page' => (int)ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch B2B orders: ' . $e->getMessage()], 500);
        }
    }
}
