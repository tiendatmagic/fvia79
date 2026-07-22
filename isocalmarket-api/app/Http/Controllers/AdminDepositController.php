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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to fetch admin deposits.');
        }
    }

    /**
     * POST /api/v1/admin/deposits/{id}/approve
     * Duyệt thủ công một lệnh nạp tiền, tự động cập nhật trạng thái và cộng tiền cho người dùng
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $request->validate([
            // Leave room for the manual_approved_by_admin_ storage prefix.
            'tx_hash' => 'required|string|max:230',
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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to approve deposit.');
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
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to reject deposit.');
        }
    }

    /**
     * POST /api/v1/admin/deposits/{id}/revoke
     * Thu hồi thủ công số tiền nạp từ một đơn nạp tiền đã duyệt (completed)
     */
    public function revoke(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $adminId = Auth::id();
            $reason = $request->input('reason');
            $success = $this->depositService->revokeDepositManually($id, $adminId, $reason);

            if ($success) {
                return response()->json([
                    'message' => 'Deposit invoice revoked successfully. User balance has been deducted.'
                ]);
            }

            return response()->json([
                'message' => 'Failed to revoke deposit invoice.'
            ], 400);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to revoke deposit.');
        }
    }

    /**
     * POST /api/v1/admin/deposits/bulk-revoke
     * Thu hồi thủ công số tiền nạp hàng loạt cho nhiều đơn nạp tiền đã chọn
     */
    public function bulkRevoke(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $adminId = Auth::id();
            $ids = $request->input('ids');
            $reason = $request->input('reason');

            $result = $this->depositService->bulkRevokeDepositsManually($ids, $adminId, $reason);

            return response()->json([
                'data' => $result,
                'message' => $result['message']
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            return $this->serverError($e, 'Failed to perform bulk revoke deposits.');
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
            'userName' => $deposit->getUserName(),
            'userUid' => $deposit->getUserUid()
        ];
    }
}
