<?php

namespace App\Http\Controllers;

use App\Application\Services\CouponService;
use App\Domain\Exceptions\CouponException;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function validateCoupon(Request $request)
    {
        try {
            $couponsEnvFeatureEnabled = filter_var(config('app.feature_coupons_enabled', true), FILTER_VALIDATE_BOOLEAN);
            $couponsEnabled = $couponsEnvFeatureEnabled
                ? filter_var(app(\App\Application\Services\SystemSettingService::class)->getSetting('coupons_enabled', '1'), FILTER_VALIDATE_BOOLEAN)
                : false;

            if (! $couponsEnabled) {
                return response()->json(['message' => 'Tính năng mã giảm giá đang bị tạm khóa.'], 400);
            }

            $request->validate([
                'code' => 'required|string',
                'cart_total' => 'required|numeric|min:0'
            ]);

            $code = $request->input('code');
            $cartTotal = (float) $request->input('cart_total');

            $result = $this->couponService->validateCoupon($code, $cartTotal);

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['message' => $ve->getMessage()], 422);
        } catch (CouponException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to validate coupon.');
        }
    }
}
