<?php

namespace App\Http\Controllers;

use App\Application\Services\SystemSettingService;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use App\Infrastructure\Persistence\Eloquent\OrderModel;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    private SystemSettingService $settingService;

    public function __construct(SystemSettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function recentPurchases(Request $request)
    {
        $enabled = filter_var($this->settingService->getSetting('recent_purchases_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $mode = $this->settingService->getSetting('recent_purchases_mode', 'demo');

        $data = [];
        if ($mode === 'real') {
            $orders = OrderModel::with(['items.product', 'user'])
                ->latest()
                ->take(10)
                ->get();

            foreach ($orders as $order) {
                $firstItem = $order->items->first();
                $product = $firstItem ? $firstItem->product : null;
                $username = $order->user ? ($order->user->username ?? 'user') : 'user';
                $maskedUsername = strlen($username) <= 4
                    ? substr($username, 0, 1).'****'
                    : substr($username, 0, min(max(1, strlen($username) - 4), 6)).'****';

                $data[] = [
                    'id' => $order->id,
                    'username' => $maskedUsername,
                    'quantity' => $firstItem ? $firstItem->quantity : 1,
                    'product_name_vi' => $product ? ($product->name_vi ?? $product->name) : 'Via Ngoại',
                    'product_name_en' => $product ? ($product->name_en ?? $product->name) : 'Via Foreign',
                    'product_name' => $product ? $product->name : 'Via Ngoại',
                    'total_price' => (float) $order->total_amount,
                    'created_at' => $order->created_at ? $order->created_at->toIso8601String() : now()->toIso8601String(),
                ];
            }
        }

        return response()->json([
            'enabled' => $enabled,
            'mode' => $mode,
            'data' => $data,
        ]);
    }

    public function recentDeposits(Request $request)
    {
        $enabled = filter_var($this->settingService->getSetting('recent_deposits_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $mode = $this->settingService->getSetting('recent_deposits_mode', 'demo');

        $data = [];
        if ($mode === 'real') {
            $deposits = DepositModel::with('user')
                ->where('status', 'completed')
                ->latest()
                ->take(10)
                ->get();

            foreach ($deposits as $deposit) {
                $username = $deposit->user ? ($deposit->user->username ?? 'user') : 'user';
                $maskedUsername = strlen($username) <= 4
                    ? substr($username, 0, 1).'****'
                    : substr($username, 0, min(max(1, strlen($username) - 4), 6)).'****';

                $amountVnd = (float) $deposit->amount;
                $amountUsdt = (float) ($deposit->amount_usdt ?? round($amountVnd / 25400, 2));

                $data[] = [
                    'id' => $deposit->id,
                    'username' => $maskedUsername,
                    'amount_vnd' => $amountVnd,
                    'amount_usdt' => $amountUsdt,
                    'method' => $deposit->payment_method ?? 'usdt_bep20',
                    'created_at' => $deposit->created_at ? $deposit->created_at->toIso8601String() : now()->toIso8601String(),
                ];
            }
        }

        return response()->json([
            'enabled' => $enabled,
            'mode' => $mode,
            'data' => $data,
        ]);
    }
}
