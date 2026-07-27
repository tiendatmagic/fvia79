<?php

namespace App\Http\Controllers;

use App\Application\Services\SystemSettingService;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Eloquent\UserSessionModel;
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

    public function index()
    {
        $settings = $this->settingService->getAllSettings();

        foreach (['scanner_api_key', 'binance_api_key', 'binance_api_secret', 'telegram_bot_token'] as $sensitiveKey) {
            $settings['has_'.$sensitiveKey] = ! empty($settings[$sensitiveKey] ?? null);
            unset($settings[$sensitiveKey]);
        }

        $settings['payment_bep20_enabled'] = filter_var($settings['payment_bep20_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN);
        $settings['payment_binance_enabled'] = filter_var($settings['payment_binance_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN);
        $settings['coupons_enabled'] = filter_var($settings['coupons_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN);

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $request->validate([
            'admin_wallet_address' => ['sometimes', 'required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'scanner_api_key' => 'sometimes|nullable|string|max:1000',
            'token_cleanup_days' => 'sometimes|required|integer|min:1|max:365',
            'usdt_exchange_rate' => 'sometimes|required|numeric|min:0|max:1000000',
            'admin_binance_id' => 'sometimes|nullable|string|max:100',
            'admin_binance_pay_link' => 'sometimes|nullable|string|max:1000',
            'binance_api_key' => 'sometimes|nullable|string|max:200',
            'binance_api_secret' => 'sometimes|nullable|string|max:200',
            'binance_proxy' => 'sometimes|nullable|string|max:500',
            'binance_api_host' => 'sometimes|nullable|string|max:500',
            'telegram_bot_token' => 'sometimes|nullable|string|max:500',
            'telegram_bot_username' => 'sometimes|nullable|string|max:100',
            'telegram_bot_enabled' => 'sometimes|nullable',
            'announcement_enabled' => 'sometimes|nullable',
            'announcement_text' => 'sometimes|nullable|string|max:1000',
            'announcement_text_vi' => 'sometimes|nullable|string|max:1000',
            'announcement_text_en' => 'sometimes|nullable|string|max:1000',
            'announcement_effect' => 'sometimes|nullable|string|in:marquee,fade,slide,bounce',
            'membership_tiers_enabled' => 'sometimes|nullable',
            'coupons_enabled' => 'sometimes|nullable',
            'payment_bep20_enabled' => 'sometimes|nullable',
            'payment_binance_enabled' => 'sometimes|nullable',
            'recent_purchases_enabled' => 'sometimes|nullable',
            'recent_purchases_mode' => 'sometimes|nullable|string|in:demo,real',
            'recent_deposits_enabled' => 'sometimes|nullable',
            'recent_deposits_mode' => 'sometimes|nullable|string|in:demo,real',
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
            $settingsToSave['admin_binance_id'] = (string) $request->input('admin_binance_id');
        }
        if ($request->has('admin_binance_pay_link')) {
            $settingsToSave['admin_binance_pay_link'] = (string) $request->input('admin_binance_pay_link');
        }

        if ($request->filled('binance_api_key')) {
            $settingsToSave['binance_api_key'] = $request->input('binance_api_key');
        }
        if ($request->filled('binance_api_secret')) {
            $settingsToSave['binance_api_secret'] = $request->input('binance_api_secret');
        }
        if ($request->has('binance_proxy')) {
            $settingsToSave['binance_proxy'] = (string) $request->input('binance_proxy');
        }
        if ($request->has('binance_api_host')) {
            $settingsToSave['binance_api_host'] = (string) $request->input('binance_api_host');
        }

        if ($request->filled('telegram_bot_token')) {
            $settingsToSave['telegram_bot_token'] = $request->input('telegram_bot_token');
        }
        if ($request->has('telegram_bot_username')) {
            $settingsToSave['telegram_bot_username'] = (string) $request->input('telegram_bot_username');
        }
        if ($request->has('telegram_bot_enabled')) {
            $settingsToSave['telegram_bot_enabled'] = filter_var($request->input('telegram_bot_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('announcement_enabled')) {
            $settingsToSave['announcement_enabled'] = filter_var($request->input('announcement_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('announcement_text_vi')) {
            $settingsToSave['announcement_text_vi'] = (string) $request->input('announcement_text_vi');
        }
        if ($request->has('announcement_text_en')) {
            $settingsToSave['announcement_text_en'] = (string) $request->input('announcement_text_en');
        }
        if ($request->has('announcement_text')) {
            $settingsToSave['announcement_text'] = (string) $request->input('announcement_text');
            if (! $request->has('announcement_text_vi')) {
                $settingsToSave['announcement_text_vi'] = (string) $request->input('announcement_text');
            }
        }
        if ($request->has('announcement_effect')) {
            $settingsToSave['announcement_effect'] = (string) $request->input('announcement_effect');
        }
        if ($request->has('membership_tiers_enabled')) {
            $settingsToSave['membership_tiers_enabled'] = filter_var($request->input('membership_tiers_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('coupons_enabled')) {
            $settingsToSave['coupons_enabled'] = filter_var($request->input('coupons_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('payment_bep20_enabled')) {
            $settingsToSave['payment_bep20_enabled'] = filter_var($request->input('payment_bep20_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('payment_binance_enabled')) {
            $settingsToSave['payment_binance_enabled'] = filter_var($request->input('payment_binance_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('recent_purchases_enabled')) {
            $settingsToSave['recent_purchases_enabled'] = filter_var($request->input('recent_purchases_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('recent_purchases_mode')) {
            $settingsToSave['recent_purchases_mode'] = (string) $request->input('recent_purchases_mode');
        }
        if ($request->has('recent_deposits_enabled')) {
            $settingsToSave['recent_deposits_enabled'] = filter_var($request->input('recent_deposits_enabled'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        if ($request->has('recent_deposits_mode')) {
            $settingsToSave['recent_deposits_mode'] = (string) $request->input('recent_deposits_mode');
        }

        if (! empty($settingsToSave)) {
            $this->settingService->saveSettings($settingsToSave);
        }

        return response()->json([
            'message' => 'System settings updated successfully.',
        ]);
    }

    public function publicSettings()
    {
        $enabled = $this->settingService->getSetting('announcement_enabled', '1');
        $textVi = $this->settingService->getSetting('announcement_text_vi', $this->settingService->getSetting('announcement_text', ''));
        $textEn = $this->settingService->getSetting('announcement_text_en', '');
        $text = $this->settingService->getSetting('announcement_text', $textVi);
        $effect = $this->settingService->getSetting('announcement_effect', 'marquee');
        $usdtRate = $this->settingService->getSetting('usdt_exchange_rate', '26500');
        $telegramBotUsername = $this->settingService->getSetting('telegram_bot_username', 'fvia79noti_bot');

        $envFeatureEnabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $membershipTiersFeatureEnabled = filter_var($envFeatureEnabled, FILTER_VALIDATE_BOOLEAN);

        $membershipTiersEnabled = $membershipTiersFeatureEnabled
            ? filter_var($this->settingService->getSetting('membership_tiers_enabled', '1'), FILTER_VALIDATE_BOOLEAN)
            : false;

        $couponsEnvFeatureEnabled = filter_var(config('app.feature_coupons_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $couponsFeatureEnabled = filter_var($couponsEnvFeatureEnabled, FILTER_VALIDATE_BOOLEAN);

        $couponsEnabled = $couponsFeatureEnabled
            ? filter_var($this->settingService->getSetting('coupons_enabled', '1'), FILTER_VALIDATE_BOOLEAN)
            : false;

        $paymentBep20Enabled = filter_var($this->settingService->getSetting('payment_bep20_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $paymentBinanceEnabled = filter_var($this->settingService->getSetting('payment_binance_enabled', '1'), FILTER_VALIDATE_BOOLEAN);

        return response()->json([
            'announcement_enabled' => filter_var($enabled, FILTER_VALIDATE_BOOLEAN),
            'announcement_text_vi' => (string) $textVi,
            'announcement_text_en' => (string) $textEn,
            'announcement_text' => (string) $text,
            'announcement_effect' => (string) $effect,
            'usdt_exchange_rate' => (float) $usdtRate,
            'telegram_bot_username' => (string) $telegramBotUsername,
            'membership_tiers_feature_enabled' => $membershipTiersFeatureEnabled,
            'membership_tiers_enabled' => $membershipTiersEnabled,
            'coupons_feature_enabled' => $couponsFeatureEnabled,
            'coupons_enabled' => $couponsEnabled,
            'payment_bep20_enabled' => $paymentBep20Enabled,
            'payment_binance_enabled' => $paymentBinanceEnabled,
        ]);
    }

    public function testTelegramBot(Request $request, \App\Application\Services\TelegramNotificationService $telegramService)
    {
        $overrideToken = $request->input('telegram_bot_token');
        $result = $telegramService->testBotConnection($overrideToken ?: null);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function clearViews()
    {
        Artisan::call('view:clear');

        return response()->json(['success' => true, 'message' => 'Compiled views cleared successfully.']);
    }

    public function clearConfig()
    {
        Artisan::call('config:clear');

        return response()->json(['success' => true, 'message' => 'Config cache cleared successfully.']);
    }

    public function clearRoutes()
    {
        Artisan::call('route:clear');

        return response()->json(['success' => true, 'message' => 'Route cache cleared successfully.']);
    }

    public function optimize()
    {
        Artisan::call('optimize');

        return response()->json(['success' => true, 'message' => 'System optimization completed successfully.']);
    }

    public function clearOptimize()
    {
        Artisan::call('optimize:clear');

        return response()->json(['success' => true, 'message' => 'Optimization cache cleared successfully.']);
    }

    public function clearAllCaches()
    {
        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize');
        Artisan::call('cache:clear');

        // Remove stale cache files that Laravel's cache command may leave behind.
        $cacheDataPath = storage_path('framework/cache/data');
        try {
            if (File::isDirectory($cacheDataPath)) {
                $this->recursiveCleanDirectory($cacheDataPath);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to force-clear the cache data directory: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'All system caches were cleared and optimized successfully.']);
    }

    public function recalculateProfit()
    {
        Artisan::call('profit:recalculate');

        return response()->json(['success' => true, 'message' => 'Profit snapshots and dashboard stats recalculated successfully.']);
    }

    public function resetProfit()
    {
        Artisan::call('profit:reset');

        return response()->json(['success' => true, 'message' => 'Profit reset to 0 successfully.']);
    }

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

    public function cleanTokens(Request $request)
    {
        $deletedCount = UserModel::where('refresh_token_expired_at', '<', now())->update([
            'refresh_token_hash' => null,
            'refresh_token_expired_at' => null,
        ]);

        $deletedSessions = UserSessionModel::where('last_activity_at', '<', now()->subDays(30))->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully cleaned up {$deletedCount} expired refresh tokens and {$deletedSessions} old sessions from database.",
        ]);
    }

    public function cleanAllTokens()
    {
        Artisan::call('auth:force-logout-all');
        UserSessionModel::query()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully revoked all active tokens and sessions. All users logged out.',
        ]);
    }
}
