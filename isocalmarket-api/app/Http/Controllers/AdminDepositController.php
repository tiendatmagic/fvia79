<?php

namespace App\Http\Controllers;

use App\Application\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDepositController extends Controller
{
    private DepositService $depositService;

    public function __construct(DepositService $depositService)
    {
        $this->depositService = $depositService;
    }

    /**
     * GET /api/v1/admin/deposits
     * Lấy danh sách toàn bộ các hóa đơn nạp tiền trong hệ thống (phân trang, lọc, tìm kiếm)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        try {
            $page = (int) $request->query('page', 1);
            $limit = (int) $request->query('limit', 10);
            $search = $request->query('search');
            $status = $request->query('status');
            $paymentMethod = $request->query('payment_method');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $result = $this->depositService->getDepositsPaginatedForAdmin(
                $page,
                $limit,
                $search,
                $status,
                $paymentMethod,
                $startDate,
                $endDate
            );

            $result['items'] = array_map(
                fn($d) => $this->formatDeposit($d),
                $result['items']
            );

            return response()->json([
                'data' => $result['items'],
                'meta' => [
                    'total' => $result['total'],
                    'page' => $result['current_page'],
                    'limit' => $result['limit'],
                    'last_page' => $result['last_page']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Cannot fetch deposits list: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/admin/deposits/{id}/approve
     * Duyệt thủ công một lệnh nạp tiền, tự động cập nhật trạng thái và cộng tiền cho người dùng
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'tx_hash' => 'nullable|string|max:255',
        ]);

        try {
            $adminId = Auth::id();
            $txHash = $request->input('tx_hash');

            $success = $this->depositService->approveDepositManually($id, $txHash, $adminId);

            if ($success) {
                return response()->json([
                    'message' => 'Deposit invoice approved successfully. User balance has been credited.'
                ]);
            }

            return response()->json([
                'message' => 'Failed to approve deposit invoice.'
            ], 400);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while approving: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/v1/admin/deposits/{id}/reject
     * Từ chối/hủy một lệnh nạp tiền thủ công
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $adminId = Auth::id();
            $success = $this->depositService->rejectDepositManually($id, $adminId);

            if ($success) {
                return response()->json([
                    'message' => 'Deposit invoice rejected successfully.'
                ]);
            }

            return response()->json([
                'message' => 'Failed to reject deposit invoice.'
            ], 400);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while rejecting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format domain model sang JSON thống nhất
     */
    private function formatDeposit($deposit): array
    {
        return [
            'id' => $deposit->getId(),
            'code' => $deposit->getCode(),
            'amountUsdt' => $deposit->getAmountUsdt(),
            'amountToPay' => $deposit->getAmountToPay(),
            'amountVnd' => $deposit->getAmountVnd(),
            'txHash' => $deposit->getTxHash(),
            'status' => $deposit->getStatus(),
            'paymentMethod' => $deposit->getPaymentMethod(),
            'payerBinanceId' => $deposit->getPayerBinanceId(),
            'adminBinanceId' => $deposit->getAdminBinanceId() ?: $this->depositService->getAdminBinanceIdSetting(),
            'createdAt' => $deposit->getCreatedAt() ? $deposit->getCreatedAt()->format('c') : null,
            'updatedAt' => $deposit->getUpdatedAt() ? $deposit->getUpdatedAt()->format('c') : null,
            'adminWalletAddress' => $deposit->getAdminWalletAddress() ?: $this->depositService->getAdminWalletAddress(),
            'userEmail' => $deposit->getUserEmail(),
            'userName' => $deposit->getUserName()
        ];
    }
}
