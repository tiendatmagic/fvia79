<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCouponRequest;
use App\Application\Services\CouponService;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    private CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function index(Request $request)
    {
        try {
            $page = $request->query('page');
            $limit = $request->query('limit', 10);
            $search = $request->query('search');

            if ($page) {
                $result = $this->couponService->getCouponsPaginated((int)$page, (int)$limit, $search);
                $coupons = $result['items'];
                $total = $result['total'];

                $data = array_map(fn($c) => [
                    'id' => $c->getId(),
                    'code' => $c->getCode(),
                    'type' => $c->getType(),
                    'value' => $c->getValue(),
                    'min_spend' => $c->getMinSpend(),
                    'start_date' => $c->getStartDate()->format('Y-m-d H:i:s'),
                    'end_date' => $c->getEndDate()->format('Y-m-d H:i:s'),
                    'usage_limit' => $c->getUsageLimit(),
                    'used_count' => $c->getUsedCount(),
                    'created_at' => $c->getCreatedAt()?->format('Y-m-d H:i:s')
                ], $coupons);

                return response()->json([
                    'data' => $data,
                    'meta' => [
                        'total' => $total,
                        'page' => (int)$page,
                        'limit' => (int)$limit,
                        'last_page' => (int)ceil($total / $limit)
                    ]
                ]);
            }

            $coupons = $this->couponService->getAllCoupons();
            $data = array_map(fn($c) => [
                'id' => $c->getId(),
                'code' => $c->getCode(),
                'type' => $c->getType(),
                'value' => $c->getValue(),
                'min_spend' => $c->getMinSpend(),
                'start_date' => $c->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $c->getEndDate()->format('Y-m-d H:i:s'),
                'usage_limit' => $c->getUsageLimit(),
                'used_count' => $c->getUsedCount()
            ], $coupons);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(CreateCouponRequest $request)
    {
        try {
            $coupon = $this->couponService->createCoupon($request->validated());
            return response()->json([
                'message' => 'Coupon created successfully.',
                'coupon' => [
                    'id' => $coupon->getId(),
                    'code' => $coupon->getCode()
                ]
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['message' => 'The coupon code already exists.'], 400);
            }
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        try {
            $coupon = $this->couponService->getCouponById($id);
            if (!$coupon) {
                return response()->json(['message' => 'Coupon not found.'], 404);
            }

            return response()->json([
                'id' => $coupon->getId(),
                'code' => $coupon->getCode(),
                'type' => $coupon->getType(),
                'value' => $coupon->getValue(),
                'min_spend' => $coupon->getMinSpend(),
                'start_date' => $coupon->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $coupon->getEndDate()->format('Y-m-d H:i:s'),
                'usage_limit' => $coupon->getUsageLimit(),
                'used_count' => $coupon->getUsedCount()
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(CreateCouponRequest $request, $id)
    {
        try {
            $coupon = $this->couponService->updateCoupon($id, $request->validated());
            return response()->json([
                'message' => 'Coupon updated successfully.',
                'coupon' => [
                    'id' => $coupon->getId(),
                    'code' => $coupon->getCode()
                ]
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['message' => 'The coupon code already exists.'], 400);
            }
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->couponService->deleteCoupon($id);
            if ($deleted) {
                return response()->json(['message' => 'Coupon deleted successfully.']);
            }
            return response()->json(['message' => 'Coupon not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
