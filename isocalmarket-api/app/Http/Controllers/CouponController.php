<?php

namespace App\Http\Controllers;

use App\Application\Services\CouponService;
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
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
