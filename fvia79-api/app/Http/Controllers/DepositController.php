<?php

namespace App\Http\Controllers;

use App\Application\Services\DepositService;
use App\Http\Requests\CreateDepositRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    private DepositService $depositService;

    public function __construct(DepositService $depositService)
    {
        $this->depositService = $depositService;
    }

    public function getRate(): JsonResponse
    {
        $rate = $this->depositService->getUsdtToVndRate();
        return response()->json([
            'rate' => $rate
        ]);
    }

    public function create(CreateDepositRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $amountUsdt = (float) $request->input('amount_usdt');
        $paymentMethod = $request->input('payment_method', 'bep20');
        $payerBinanceId = $request->input('payer_binance_id');

        try {
            $deposit = $this->depositService->createDeposit($userId, $amountUsdt, $paymentMethod, $payerBinanceId);
        } catch (\RuntimeException $e) {
            $activeInvoice = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('user_id', $userId)
                ->where('status', 'pending_payment')
                ->first();

            $msg = $e->getMessage();
            if (str_contains($msg, 'configured the receiving USDT wallet') || str_contains($msg, 'configured the scanner API key')) {
                $msg = 'System configuration error. Please contact the administrator.';
            }

            return response()->json([
                'message' => $msg,
                'active_invoice_id' => $activeInvoice ? $activeInvoice->id : null
            ], 400);
        }

        return response()->json([
            'message' => 'Created deposit invoice successfully.',
            'invoice' => $this->formatDeposit($deposit)
        ], 201);
    }

    public function showInvoice(string $id): JsonResponse
    {
        $user = Auth::user();
        $deposit = $this->depositService->getInvoice($id);

        $isAdmin = $user && $user->role === 'admin';

        if (!$deposit || (!$isAdmin && $deposit->getUserId() !== $user->id)) {
            return response()->json([
                'message' => 'Deposit invoice not found.'
            ], 404);
        }

        if ($deposit->getStatus() === 'completed' && $deposit->getUserId() === $user->id) {
            \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)->update(['user_notified' => true]);
        }

        return response()->json([
            'invoice' => $this->formatDeposit($deposit)
        ]);
    }

    public function getUnseenSuccessDeposit(): JsonResponse
    {
        $userId = Auth::id();
        $depositModel = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('user_id', $userId)
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where('user_notified', false)->orWhereNull('user_notified');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $depositModel) {
            return response()->json(['deposit' => null]);
        }

        \App\Infrastructure\Persistence\Eloquent\DepositModel::where('user_id', $userId)
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where('user_notified', false)->orWhereNull('user_notified');
            })
            ->update(['user_notified' => true]);

        $deposit = \App\Infrastructure\Persistence\Mappers\DepositMapper::toDomain($depositModel);

        return response()->json([
            'deposit' => $this->formatDeposit($deposit)
        ]);
    }

    public function markDepositSeen(string $id): JsonResponse
    {
        $userId = Auth::id();
        $updated = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)
            ->where('user_id', $userId)
            ->update(['user_notified' => true]);

        return response()->json([
            'success' => $updated > 0
        ]);
    }

    public function cancelInvoice(string $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $success = $this->depositService->cancelInvoice($id, $userId);

            if (! $success) {
                return response()->json([
                    'message' => 'Failed to cancel invoice. Invalid invoice state or unauthorized.'
                ], 400);
            }

            return response()->json([
                'message' => 'Cancelled deposit invoice successfully.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error in cancelInvoice controller for invoice ID {$id}: {$e->getMessage()}");

            return response()->json([
                'message' => $e->getMessage() ?: 'Failed to cancel invoice.'
            ], 400);
        }
    }

    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $userId = Auth::id();
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);
        $search = $request->query('search');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $historyData = $this->depositService->getHistoryPaginated(
            $userId,
            $page,
            $limit,
            $search,
            $status,
            $startDate,
            $endDate
        );

        $historyData['items'] = array_map(
            fn($d) => $this->formatDeposit($d),
            $historyData['items']
        );

        return response()->json($historyData);
    }

    public function checkTransaction(string $id): JsonResponse
    {
        $userId = Auth::id();
        
        try {
            $matched = $this->depositService->checkTransaction($id, $userId);
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'configured the receiving USDT wallet') || str_contains($msg, 'configured the scanner API key')) {
                $msg = 'System configuration error. Please contact the administrator.';
            }

            return response()->json([
                'success' => false,
                'message' => $msg
            ], 400);
        }

        if ($matched) {
            \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)->update(['user_notified' => true]);
            return response()->json([
                'success' => true,
                'message' => 'Matched transaction on BSC block chain. Balance updated.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'We could not detect your USDT payment on BSC blockchain yet. Please make sure you transferred the exact amount (with the fractional suffix) to the correct wallet address within 15 minutes.'
        ], 400);
    }

    private function formatDeposit($deposit): array
    {
        return [
            'id' => $deposit->getId(),
            'userId' => $deposit->getUserId(),
            'code' => $deposit->getCode(),
            'amountUsdt' => $deposit->getAmountUsdt(),
            'amountToPay' => $deposit->getAmountToPay(),
            'amountVnd' => $deposit->getAmountVnd(),
            'txHash' => $deposit->getTxHash(),
            'status' => $deposit->getStatus(),
            'paymentMethod' => $deposit->getPaymentMethod(),
            'payerBinanceId' => $deposit->getPayerBinanceId(),
            'adminBinanceId' => $deposit->getAdminBinanceId() ?: $this->depositService->getAdminBinanceIdSetting(),
            'adminBinancePayLink' => $this->depositService->getAdminBinancePayLinkSetting(),
            'createdAt' => $deposit->getCreatedAt() ? $deposit->getCreatedAt()->format('c') : null,
            'updatedAt' => $deposit->getUpdatedAt() ? $deposit->getUpdatedAt()->format('c') : null,
            'adminWalletAddress' => $deposit->getAdminWalletAddress() ?: $this->depositService->getAdminWalletAddress(),
            'userEmail' => $deposit->getUserEmail(),
            'userName' => $deposit->getUserName(),
            'userUid' => $deposit->getUserUid(),
        ];
    }
}
