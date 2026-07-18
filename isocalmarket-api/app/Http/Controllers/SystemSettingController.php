<?php

namespace App\Http\Controllers;

use App\Application\Services\SystemSettingService;
use Illuminate\Http\Request;

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
        return response()->json($settings);
    }

    /**
     * Cập nhật cấu hình hệ thống
     */
    public function update(Request $request)
    {
        $request->validate([
            'admin_wallet_address' => 'required|string|max:255',
            'scanner_api_key' => 'required|string|max:255',
        ]);

        $this->settingService->saveSettings([
            'admin_wallet_address' => $request->input('admin_wallet_address'),
            'scanner_api_key' => $request->input('scanner_api_key'),
        ]);

        return response()->json([
            'message' => 'System settings updated successfully.'
        ]);
    }
}
