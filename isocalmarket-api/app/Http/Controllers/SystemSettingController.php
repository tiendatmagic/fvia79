<?php

namespace App\Http\Controllers;

use App\Application\Services\SystemSettingService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    private SystemSettingService $settingService;

    public function __construct(SystemSettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    /**
     * Lấy tất cả cấu hình hệ thống
     */
    public function index()
    {
        $settings = $this->settingService->getAllSettings();

        foreach (['scanner_api_key', 'binance_api_key', 'binance_api_secret'] as $sensitiveKey) {
            $settings['has_'.$sensitiveKey] = ! empty($settings[$sensitiveKey] ?? null);
            unset($settings[$sensitiveKey]);
        }

        return response()->json($settings);
    }

    /**
     * Cập nhật cấu hình hệ thống
     */
    public function update(Request $request)
    {
        $request->validate([
            'admin_wallet_address' => ['sometimes', 'required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'scanner_api_key' => 'sometimes|nullable|string|max:1000',
            'token_cleanup_days' => 'sometimes|required|integer|min:1|max:365',
            'usdt_exchange_rate' => 'sometimes|required|numeric|min:0|max:1000000',
            'admin_binance_id' => 'sometimes|nullable|string|max:100',
            'binance_api_key' => 'sometimes|nullable|string|max:200',
            'binance_api_secret' => 'sometimes|nullable|string|max:200',
        ], [
            'admin_wallet_address.regex' => 'The admin wallet address must be a valid BEP20/EVM address starting with 0x followed by 40 hex characters.',
        ]);

        $settingsToSave = [];
        if ($request->has('admin_wallet_address')) {
            $settingsToSave['admin_wallet_address'] = $request->input('admin_wallet_address');
        }
        if ($request->filled('scanner_api_key')) {
            $settingsToSave['scanner_api_key'] = $request->input('scanner_api_key');
        }
        if ($request->has('token_cleanup_days')) {
            $settingsToSave['token_cleanup_days'] = (string) $request->input('token_cleanup_days');
        }
        if ($request->has('usdt_exchange_rate')) {
            $settingsToSave['usdt_exchange_rate'] = (string) $request->input('usdt_exchange_rate');
        }
        if ($request->has('admin_binance_id')) {
            $settingsToSave['admin_binance_id'] = $request->input('admin_binance_id') ?: '';
        }

        if ($request->filled('binance_api_key')) {
            $settingsToSave['binance_api_key'] = $request->input('binance_api_key');
        }
        if ($request->filled('binance_api_secret')) {
            $settingsToSave['binance_api_secret'] = $request->input('binance_api_secret');
        }

        if (! empty($settingsToSave)) {
            $this->settingService->saveSettings($settingsToSave);
        }

        return response()->json([
            'message' => 'System settings updated successfully.',
        ]);
    }

    /**
     * Xóa compiled Blade views
     */
    public function clearViews()
    {
        Artisan::call('view:clear');

        return response()->json(['success' => true, 'message' => 'Compiled views cleared successfully.']);
    }

    /**
     * Xóa config cache
     */
    public function clearConfig()
    {
        Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => 'Config cache cleared successfully.']);
    }

    /**
     * Xóa route cache
     */
    public function clearRoutes()
    {
        Artisan::call('route:clear');

        return response()->json(['success' => true, 'message' => 'Route cache cleared successfully.']);
    }

    /**
     * Tối ưu hóa hiệu năng: cache config, routes, views
     */
    public function optimize()
    {
        Artisan::call('optimize');

        return response()->json(['success' => true, 'message' => 'System optimization completed successfully.']);
    }

    /**
     * Xóa toàn bộ optimization cache
     */
    public function clearOptimize()
    {
        Artisan::call('optimize:clear');

        return response()->json(['success' => true, 'message' => 'Optimization cache cleared successfully.']);
    }

    /**
     * Xóa toàn bộ và tối ưu hóa lại cache hệ thống
     */
    public function clearAllCaches()
    {
        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize');
        Artisan::call('cache:clear');

        // Xóa cứng thư mục cache data để tránh lỗi lock file hoặc cấu hình sai
        $cacheDataPath = storage_path('framework/cache/data');
        try {
            if (File::isDirectory($cacheDataPath)) {
                $this->recursiveCleanDirectory($cacheDataPath);
            }
        } catch (\Throwable $e) {
            Log::error(__('messages.cache_cleanup_log_error', ['error' => $e->getMessage()]));
        }

        return response()->json(['success' => true, 'message' => __('messages.cache_cleanup_success')]);
    }

    /**
     * Đồng bộ và tính toán lại tiền lãi & snapshot giá gốc toàn bộ đơn hàng
     */
    public function recalculateProfit()
    {
        Artisan::call('profit:recalculate');

        return response()->json(['success' => true, 'message' => 'Profit snapshots and dashboard stats recalculated successfully.']);
    }

    /**
     * Thiết lập lại toàn bộ lợi nhuận đơn hàng quá khứ về 0
     */
    public function resetProfit()
    {
        Artisan::call('profit:reset');

        return response()->json(['success' => true, 'message' => 'Profit reset to 0 successfully.']);
    }

    /**
     * Dọn dẹp đệ quy toàn bộ thư mục và tệp tin
     */
    private function recursiveCleanDirectory($dirPath)
    {
        if (! is_dir($dirPath)) {
            return;
        }

        $items = scandir($dirPath);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dirPath.DIRECTORY_SEPARATOR.$item;
            @chmod($path, 0777);

            if (is_dir($path)) {
                $this->recursiveCleanDirectory($path);
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    }

    /**
     * Dọn dẹp các Token hết hạn/cũ thủ công
     */
    public function cleanTokens(Request $request)
    {
        $deletedCount = UserModel::where('refresh_token_expired_at', '<', now())->update([
            'refresh_token_hash' => null,
            'refresh_token_expired_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully cleaned up {$deletedCount} expired refresh tokens from database.",
        ]);
    }

    /**
     * Xóa toàn bộ tokens đăng nhập của mọi user (Force Logout All)
     */
    public function cleanAllTokens()
    {
        Artisan::call('auth:force-logout-all');

        return response()->json([
            'success' => true,
            'message' => 'Successfully revoked all active tokens. All users logged out.',
        ]);
    }
}
