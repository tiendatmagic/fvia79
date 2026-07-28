<?php

namespace App\Application\Services;

use App\Domain\Entities\Deposit;
use App\Domain\Repositories\DepositRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\DepositModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Infrastructure\Persistence\Mappers\DepositMapper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepositService
{
    private DepositRepositoryInterface $depositRepository;

    private string $adminWalletAddress;

    private string $scannerApiKey;

    private string $tokenContractAddress;

    private int $tokenDecimals;

    private SystemSettingService $settingService;

    private array $cachedBep20TransactionsByWallet = [];

    private ?array $cachedBinanceTransactions = null;

    public function __construct(
        DepositRepositoryInterface $depositRepository,
        SystemSettingService $settingService
    ) {
        $this->depositRepository = $depositRepository;
        $this->settingService = $settingService;

        $this->adminWalletAddress = $this->settingService->getSetting('admin_wallet_address', '');
        $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');

        $this->tokenContractAddress = env('TOKEN_CONTRACT_ADDRESS', '0x55d398326f99059ff775485246999027b3197955');
        $this->tokenDecimals = (int) env('TOKEN_DECIMALS', 18);
    }

    public function getAdminWalletAddress(): string
    {
        return $this->settingService->getSetting('admin_wallet_address', '');
    }

    public function getAdminBinanceIdSetting(): string
    {
        return $this->settingService->getSetting('admin_binance_id', '');
    }

    public function getAdminBinancePayLinkSetting(): string
    {
        return $this->settingService->getSetting('admin_binance_pay_link', '');
    }

    public function getUsdtToVndRate(): float
    {
        $fixedRate = $this->settingService->getSetting('usdt_exchange_rate', '0');
        if (! empty($fixedRate) && (float) $fixedRate > 0) {
            return (float) $fixedRate;
        }

        if (Cache::has('usdt_to_vnd_rate')) {
            $cachedRate = (float) Cache::get('usdt_to_vnd_rate');
            if ($cachedRate > 0) {
                return $cachedRate;
            }
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(['accept' => 'application/json'])
                ->get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => 'tether',
                    'vs_currencies' => 'vnd',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['tether']['vnd'])) {
                    $rate = (float) $data['tether']['vnd'];
                    if ($rate > 0) {
                        Log::info("CoinGecko USDT/VND rate fetched successfully: {$rate}");
                        Cache::put('usdt_to_vnd_rate', $rate, 600);
                        $this->settingService->setSetting('usdt_last_known_rate', (string) $rate);

                        return $rate;
                    }
                }
            }

            Log::warning('CoinGecko API call failed or invalid response: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Failed to fetch USDT rate from CoinGecko: '.$e->getMessage());
        }

        $lastKnownRate = $this->settingService->getSetting('usdt_last_known_rate', '0');
        if (! empty($lastKnownRate) && (float) $lastKnownRate > 0) {
            $rate = (float) $lastKnownRate;
            Log::info("Using last known saved USDT/VND rate: {$rate}");
            Cache::put('usdt_to_vnd_rate', $rate, 600);

            return $rate;
        }

        Log::info('Using default fixed USDT/VND rate: 26500.0');

        return 26500.0;
    }

    public function createDeposit(string $userId, float $amountUsdt, string $paymentMethod = 'bep20', ?string $payerBinanceId = null): Deposit
    {
        $this->adminWalletAddress = $this->settingService->getSetting('admin_wallet_address', '');
        $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');
        $adminBinanceIdSetting = $this->settingService->getSetting('admin_binance_id', '');
        $binanceApiKeySetting = $this->settingService->getSetting('binance_api_key', '');
        $binanceApiSecretSetting = $this->settingService->getSetting('binance_api_secret', '');

        $bep20Enabled = filter_var($this->settingService->getSetting('payment_bep20_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $binanceEnabled = filter_var($this->settingService->getSetting('payment_binance_enabled', '1'), FILTER_VALIDATE_BOOLEAN);

        if ($paymentMethod === 'bep20') {
            if (! $bep20Enabled) {
                throw new \RuntimeException('USDT BEP-20 deposit method is currently disabled.');
            }
            if (empty($this->adminWalletAddress)) {
                throw new \RuntimeException('The administrator has not configured the receiving USDT wallet address yet.');
            }
            if (empty($this->scannerApiKey)) {
                throw new \RuntimeException('The administrator has not configured the scanner API key yet.');
            }
        } elseif ($paymentMethod === 'binance') {
            if (! $binanceEnabled) {
                throw new \RuntimeException('Binance Pay deposit method is currently disabled.');
            }
            if (empty($adminBinanceIdSetting)) {
                throw new \RuntimeException('The administrator has not configured the receiving Binance ID yet.');
            }
            if (empty($binanceApiKeySetting) || empty($binanceApiSecretSetting)) {
                throw new \RuntimeException('The administrator has not configured the Binance API Key/Secret yet.');
            }
        }

        if ($amountUsdt < 0.1) {
            throw new \RuntimeException('The deposit amount must be at least 0.1 USDT.');
        }

        return DB::transaction(function () use ($userId, $amountUsdt, $paymentMethod, $payerBinanceId, $adminBinanceIdSetting) {
            DepositModel::where('user_id', $userId)
                ->where('status', 'pending_payment')
                ->where('created_at', '<=', now()->subMinutes(16))
                ->update(['status' => 'expired']);

            $hasPending = DepositModel::where('user_id', $userId)
                ->where('status', 'pending_payment')
                ->lockForUpdate()
                ->exists();

            if ($hasPending) {
                throw new \RuntimeException('You already have an active deposit order. Please cancel the previous order before creating a new one.');
            }

            $rate = $this->getUsdtToVndRate();
            $baseAmount = round($amountUsdt, 2);

            $activeAmounts = DepositModel::where(function ($q) {
                $q->where('status', 'pending_payment')
                    ->orWhere(function ($sub) {
                        $sub->whereIn('status', ['failed', 'expired'])
                            ->where('updated_at', '>=', now()->subMinutes(10));
                    });
            })
                ->lockForUpdate()
                ->pluck('amount_to_pay')
                ->toArray();

            $suffix = 0.0000;
            $step = 0.0001;
            $maxSuffix = 0.0999;

            while (true) {
                $candidateAmount = round($baseAmount + $suffix, 4);

                if (! in_array($candidateAmount, $activeAmounts)) {
                    $amountToPay = $candidateAmount;
                    break;
                }

                $suffix += $step;

                if ($suffix > $maxSuffix) {
                    throw new \RuntimeException('Too many pending transactions for this amount. Please try again in a few minutes.');
                }
            }

            $amountVnd = round($baseAmount * $rate);

            $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
            do {
                $nextCode = '';
                for ($i = 0; $i < 6; $i++) {
                    $nextCode .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $exists = DepositModel::where('code', $nextCode)
                    ->lockForUpdate()
                    ->exists();
            } while ($exists);

            $deposit = new Deposit(
                id: null,
                userId: $userId,
                code: $nextCode,
                amountUsdt: $baseAmount,
                amountToPay: $amountToPay,
                amountVnd: $amountVnd,
                txHash: null,
                adminWalletAddress: $paymentMethod === 'bep20' ? $this->adminWalletAddress : null,
                status: 'pending_payment',
                paymentMethod: $paymentMethod,
                payerBinanceId: $paymentMethod === 'binance' ? $payerBinanceId : null,
                adminBinanceId: $paymentMethod === 'binance' ? $adminBinanceIdSetting : null
            );

            return $this->depositRepository->save($deposit);
        });
    }

    public function getInvoice(string $id): ?Deposit
    {
        return $this->depositRepository->find($id);
    }

    public function cancelInvoice(string $id, string $userId): bool
    {
        $deposit = $this->depositRepository->find($id);
        if (! $deposit || $deposit->getStatus() !== 'pending_payment') {
            return false;
        }

        $user = UserModel::find($userId);
        $isAdmin = $user && $user->role === 'admin';

        if ($deposit->getUserId() !== $userId && ! $isAdmin) {
            return false;
        }

        try {
            $matched = $this->checkTransaction($id, $deposit->getUserId());
            if ($matched) {
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to check blockchain/binance before canceling deposit ID {$id}: ".$e->getMessage());
        }

        return DB::transaction(function () use ($id, $userId) {
            $depositModel = DepositModel::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (! $depositModel || $depositModel->status !== 'pending_payment') {
                return false;
            }

            $user = UserModel::find($userId);
            $isAdmin = $user && $user->role === 'admin';
            if ($depositModel->user_id !== $userId && ! $isAdmin) {
                return false;
            }

            $depositModel->status = 'expired';
            $depositModel->save();

            return true;
        });
    }

    public function getHistoryPaginated(
        string $userId,
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = DepositModel::where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate.' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate.' 23:59:59');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('amount_usdt', 'like', "%{$search}%")
                    ->orWhere('amount_to_pay', 'like', "%{$search}%")
                    ->orWhere('tx_hash', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return [
            'items' => array_map(fn ($m) => DepositMapper::toDomain($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'limit' => $limit,
        ];
    }

    public function checkTransaction(string $id, string $userId): bool
    {
        $depositModel = DepositModel::where('id', $id)->first();
        if (! $depositModel) {
            return false;
        }

        $paymentMethod = $depositModel->payment_method ?: 'bep20';

        if ($paymentMethod === 'bep20') {
            $this->adminWalletAddress = $depositModel->admin_wallet_address
                ?: $this->settingService->getSetting('admin_wallet_address', '');
            $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');

            if (empty($this->adminWalletAddress)) {
                throw new \RuntimeException('The administrator has not configured the receiving USDT wallet address yet.');
            }
            if (empty($this->scannerApiKey)) {
                throw new \RuntimeException('The administrator has not configured the scanner API key yet.');
            }

            $adminWallet = strtolower($this->adminWalletAddress);
            $tokenContract = strtolower($this->tokenContractAddress);

            $transactions = Cache::remember("bep20_txs_{$adminWallet}", 15, function () use ($adminWallet) {
                $response = Http::timeout(10)
                    ->withHeader('X-API-Key', $this->scannerApiKey)
                    ->get("https://deep-index.moralis.io/api/v2.2/{$adminWallet}/erc20/transfers", [
                        'chain' => 'bsc',
                        'contract_addresses' => [$this->tokenContractAddress],
                        'order' => 'DESC',
                        'limit' => 100,
                    ]);

                if ($response->successful()) {
                    $result = $response->json();

                    return $result['result'] ?? [];
                }

                Log::error('Moralis API HTTP error: '.$response->status().' - '.$response->body());
                throw new \RuntimeException('Unable to connect to blockchain scanner API. Please try again later.');
            });

            return DB::transaction(function () use ($id, $userId, $adminWallet, $tokenContract, $transactions) {
                $depositModel = DepositModel::where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (! $depositModel) {
                    return false;
                }

                $deposit = DepositMapper::toDomain($depositModel);

                if ($deposit->getUserId() !== $userId || $deposit->getStatus() !== 'pending_payment') {
                    Log::warning("Deposit check request invalid or already processed for invoice ID: {$id}");

                    return false;
                }

                foreach ($transactions as $tx) {
                    if (strtolower($tx['address'] ?? '') !== $tokenContract) {
                        continue;
                    }

                    if (strtolower($tx['to_address'] ?? '') !== $adminWallet) {
                        continue;
                    }

                    $txValueUsdt = (float) ($tx['value'] ?? 0) / pow(10, $this->tokenDecimals);
                    $amountToPay = $deposit->getAmountToPay();

                    if (round($txValueUsdt, 4) !== round($amountToPay, 4)) {
                        continue;
                    }

                    $txHash = $tx['transaction_hash'] ?? '';
                    if (empty($txHash) || $this->depositRepository->isTxHashUsed($txHash)) {
                        continue;
                    }

                    $invoiceCreatedEpoch = $deposit->getCreatedAt()->getTimestamp();
                    $txEpoch = isset($tx['block_timestamp'])
                        ? (new \DateTime($tx['block_timestamp']))->getTimestamp()
                        : 0;
                    $timeDiff = $txEpoch - $invoiceCreatedEpoch;
                    if ($timeDiff < -60 || $timeDiff > 900) {
                        continue;
                    }

                    $deposit->setStatus('completed');
                    $deposit->setTxHash($txHash);
                    $this->depositRepository->save($deposit);

                    $this->creditDepositToUser($deposit);

                    Log::info("Deposit successfully matched and processed: Invoice #{$deposit->getCode()}, Amount: {$deposit->getAmountUsdt()} USDT, TxHash: {$txHash}");

                    return true;
                }

                return false;
            });
        } elseif ($paymentMethod === 'binance') {
            $binanceApiKey = $this->settingService->getSetting('binance_api_key', '');
            $binanceApiSecret = $this->settingService->getSetting('binance_api_secret', '');
            $binanceProxy = $this->settingService->getSetting('binance_proxy', '')
                ?: config('services.binance.proxy')
                ?: env('BINANCE_PROXY');

            $customHost = rtrim(
                $this->settingService->getSetting('binance_api_host', '')
                ?: config('services.binance.api_host')
                ?: env('BINANCE_API_HOST', ''),
                '/'
            );

            if (empty($binanceApiKey) || empty($binanceApiSecret)) {
                throw new \RuntimeException('The administrator has not configured the Binance API Key/Secret yet.');
            }

            $hosts = ! empty($customHost)
                ? [$customHost]
                : [
                    'https://api.binance.com',
                    'https://api1.binance.com',
                    'https://api2.binance.com',
                    'https://api3.binance.com',
                    'https://api4.binance.com',
                ];

            $transactions = Cache::remember('binance_pay_txs', 15, function () use ($binanceApiKey, $binanceApiSecret, $binanceProxy, $hosts) {
                $timestamp = round(microtime(true) * 1000);
                $queryString = 'timestamp='.$timestamp.'&recvWindow=60000';
                $signature = hash_hmac('sha256', $queryString, $binanceApiSecret);

                $lastException = null;

                foreach ($hosts as $host) {
                    try {
                        $httpRequest = Http::timeout(6);
                        if (! empty($binanceProxy)) {
                            $httpRequest = $httpRequest->withOptions(['proxy' => $binanceProxy]);
                        }

                        $response = $httpRequest
                            ->withHeaders([
                                'X-MBX-APIKEY' => $binanceApiKey,
                            ])
                            ->get($host.'/sapi/v1/pay/transactions?'.$queryString.'&signature='.$signature);

                        if ($response->successful()) {
                            $result = $response->json();
                            if (($result['success'] ?? false) !== true || ($result['code'] ?? '') !== '000000') {
                                $errorMsg = $result['errorMessage'] ?? $result['msg'] ?? ($result['code'] ?? 'Unknown error');
                                Log::error("Binance Pay API payload error on {$host}: ".$response->body());
                                throw new \RuntimeException("Binance Pay API rejected request: {$errorMsg} (code: ".($result['code'] ?? 'N/A').')');
                            }

                            return $result['data'] ?? [];
                        }

                        $statusCode = $response->status();
                        $responseBody = $response->body();
                        Log::error("Binance Pay API HTTP error {$statusCode} on {$host}: {$responseBody}");

                        $jsonErr = json_decode($responseBody, true);
                        $detailedMsg = $jsonErr['msg'] ?? $jsonErr['message'] ?? $responseBody;

                        $lastException = new \RuntimeException("Unable to connect to Binance Pay API (HTTP {$statusCode}: {$detailedMsg})");
                    } catch (\Throwable $e) {
                        Log::error("Binance Pay API Connection Exception on {$host}: ".$e->getMessage());
                        $lastException = $e;
                    }
                }

                if ($lastException) {
                    throw $lastException;
                }

                throw new \RuntimeException('Unable to connect to Binance Pay API server.');
            });

            return DB::transaction(function () use ($id, $userId, $transactions) {
                $depositModel = DepositModel::where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (! $depositModel) {
                    return false;
                }

                $deposit = DepositMapper::toDomain($depositModel);

                if ($deposit->getUserId() !== $userId || $deposit->getStatus() !== 'pending_payment') {
                    Log::warning("Binance Pay check request invalid or already processed for invoice ID: {$id}");

                    return false;
                }

                foreach ($transactions as $tx) {
                    $currency = $tx['currency'] ?? '';
                    if (strtoupper($currency) !== 'USDT') {
                        continue;
                    }

                    $txValueUsdt = (float) ($tx['amount'] ?? 0);
                    $amountToPay = $deposit->getAmountToPay();

                    if ($txValueUsdt <= 0 || round($txValueUsdt, 4) !== round($amountToPay, 4)) {
                        continue;
                    }

                    $transactionId = $tx['transactionId'] ?? '';
                    if (empty($transactionId) || $this->depositRepository->isTxHashUsed($transactionId)) {
                        continue;
                    }

                    $invoiceCreatedEpoch = $deposit->getCreatedAt()->getTimestamp();
                    $txEpoch = isset($tx['transactionTime'])
                        ? round((float) $tx['transactionTime'] / 1000)
                        : 0;
                    $timeDiff = $txEpoch - $invoiceCreatedEpoch;
                    if ($timeDiff < -60 || $timeDiff > 900) {
                        continue;
                    }

                    $deposit->setStatus('completed');
                    $deposit->setTxHash($transactionId);
                    $this->depositRepository->save($deposit);

                    $this->creditDepositToUser($deposit);

                    Log::info("Binance Pay Deposit successfully matched and processed: Invoice #{$deposit->getCode()}, Amount: {$deposit->getAmountUsdt()} USDT, Binance Transaction ID: {$transactionId}");

                    return true;
                }

                return false;
            });
        }
    }

    public function checkAllPendingDeposits(?\Closure $logCallback = null): void
    {
        $bulkExpired = DepositModel::where('status', 'pending_payment')
            ->where('created_at', '<=', now()->subMinutes(16))
            ->update(['status' => 'expired']);

        if ($bulkExpired > 0 && $logCallback) {
            $logCallback('info', "Auto-expired {$bulkExpired} pending deposit(s) older than 16 minutes.");
        }

        $pendingDeposits = DepositModel::where('status', 'pending_payment')->get();

        if ($pendingDeposits->isEmpty()) {
            if ($logCallback) {
                $logCallback('info', 'No pending deposits found.');
            }

            return;
        }

        $this->cachedBep20TransactionsByWallet = [];
        $this->cachedBinanceTransactions = null;

        foreach ($pendingDeposits as $deposit) {
            try {
                if ($logCallback) {
                    $logCallback('info', "Checking deposit code: {$deposit->code} (ID: {$deposit->id})");
                }

                $matched = $this->checkTransaction($deposit->id, $deposit->user_id);

                if ($matched) {
                    if ($logCallback) {
                        $logCallback('info', "Successfully matched and completed deposit code: {$deposit->code}");
                    }
                } else {
                    $createdTime = $deposit->created_at->getTimestamp();
                    $now = time();
                    if (($now - $createdTime) > 960) {
                        $expired = DepositModel::where('id', $deposit->id)
                            ->where('status', 'pending_payment')
                            ->update(['status' => 'expired']);
                        if ($expired && $logCallback) {
                            $logCallback('info', "Deposit code {$deposit->code} has expired (exceeded 15 minutes window + 1 minute grace period).");
                        }
                    } else {
                        if ($logCallback) {
                            $logCallback('info', "Deposit code {$deposit->code} is still pending within the payment window.");
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to check pending deposit ID {$deposit->id}: ".$e->getMessage());
                if ($logCallback) {
                    $logCallback('error', "Error checking deposit ID {$deposit->id}: ".$e->getMessage());
                }
            }
        }
    }

    public function getDepositsPaginatedForAdmin(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $paymentMethod = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        return $this->depositRepository->findAllPaginated($page, $limit, $search, $status, $paymentMethod, $startDate, $endDate);
    }

    public function approveDepositManually(string $id, string $txHash, string $adminId): bool
    {
        return DB::transaction(function () use ($id, $txHash, $adminId) {
            $depositModel = DepositModel::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (! $depositModel) {
                throw new \RuntimeException('Deposit invoice not found.');
            }

            $deposit = DepositMapper::toDomain($depositModel);

            if (! in_array($deposit->getStatus(), ['pending_payment', 'expired'], true)) {
                throw new \RuntimeException('Only pending or expired deposit invoices can be approved.');
            }

            $manualReference = trim($txHash);
            if ($manualReference === '') {
                throw new \RuntimeException('A transaction ID/hash is required for manual approval.');
            }

            $finalTxHash = 'manual_approved_by_admin_'.$manualReference;
            if (mb_strlen($finalTxHash) > 255) {
                throw new \RuntimeException('Transaction ID/hash is too long.');
            }

            if ($this->depositRepository->isTxHashUsed($finalTxHash)) {
                throw new \RuntimeException('This transaction hash has already been used for another deposit.');
            }

            $deposit->setStatus('completed');
            $deposit->setTxHash($finalTxHash);
            $this->depositRepository->save($deposit);

            $this->creditDepositToUser($deposit);

            Log::info("Admin [{$adminId}] manually approved Deposit ID [{$id}]: Code={$deposit->getCode()}, Amount={$deposit->getAmountUsdt()} USDT ({$deposit->getAmountVnd()} VND), Payer ID/Wallet=".($deposit->getPayerBinanceId() ?: $deposit->getAdminWalletAddress()).", TxHash={$finalTxHash}");

            return true;
        });
    }

    public function rejectDepositManually(string $id, string $adminId): bool
    {
        return DB::transaction(function () use ($id, $adminId) {
            $depositModel = DepositModel::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (! $depositModel) {
                throw new \RuntimeException('Deposit invoice not found.');
            }

            $deposit = DepositMapper::toDomain($depositModel);

            if ($deposit->getStatus() === 'completed') {
                throw new \RuntimeException('Cannot reject a completed deposit invoice.');
            }

            if ($deposit->getStatus() === 'failed') {
                throw new \RuntimeException('This deposit invoice is already in failed status.');
            }

            $deposit->setStatus('failed');
            $this->depositRepository->save($deposit);

            Log::info("Admin [{$adminId}] manually rejected Deposit ID [{$id}]: Code={$deposit->getCode()}");

            return true;
        });
    }

    public function revokeDepositManually(string $id, string $adminId, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($id, $adminId, $reason) {
            $depositModel = DepositModel::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (! $depositModel) {
                throw new \RuntimeException('Deposit invoice not found.');
            }

            if ($depositModel->status !== 'completed') {
                throw new \RuntimeException('Only completed deposit invoices can be revoked.');
            }

            $userModel = UserModel::where('id', $depositModel->user_id)
                ->lockForUpdate()
                ->first();

            if (! $userModel) {
                throw new \RuntimeException('User not found for this deposit.');
            }

            $amountToRevoke = (float) $depositModel->amount_usdt;
            $currentBalance = (float) $userModel->balance;

            if ($currentBalance < $amountToRevoke) {
                throw new \RuntimeException("Cannot revoke deposit invoice #{$depositModel->code}. User current balance ({$currentBalance} USDT) is less than the deposit amount ({$amountToRevoke} USDT).");
            }

            $userModel->balance = round($currentBalance - $amountToRevoke, 8);
            $featureTiersEnabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
            if ($featureTiersEnabled) {
                $userModel->total_deposited = max(0.0, round((float) $userModel->total_deposited - $amountToRevoke, 4));
            }
            $userModel->save();

            app(MembershipTierService::class)->recalculateUserTier($userModel);

            $depositModel->status = 'revoked';
            $depositModel->save();

            Log::info("Admin [{$adminId}] manually revoked Deposit ID [{$id}]: Code={$depositModel->code}, Amount={$amountToRevoke} USDT from User ID [{$userModel->id}]. Reason: ".($reason ?: 'N/A'));

            return true;
        });
    }

    public function bulkRevokeDepositsManually(array $ids, string $adminId, ?string $reason = null): array
    {
        if (empty($ids)) {
            throw new \RuntimeException('No deposit invoice IDs selected for bulk revocation.');
        }

        return DB::transaction(function () use ($ids, $adminId, $reason) {
            $depositModels = DepositModel::whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            $successCount = 0;
            $failedCount = 0;
            $failedDetails = [];

            $completedDeposits = $depositModels->filter(fn ($d) => $d->status === 'completed');

            $unmatchedOrInvalid = array_diff($ids, $completedDeposits->pluck('id')->toArray());
            foreach ($unmatchedOrInvalid as $invalidId) {
                $failedCount++;
                $failedDetails[] = [
                    'id' => $invalidId,
                    'reason' => 'Deposit invoice not found or not in completed status.',
                ];
            }

            $groupedByUser = $completedDeposits->groupBy('user_id');

            foreach ($groupedByUser as $userId => $userDeposits) {
                $userModel = UserModel::where('id', $userId)
                    ->lockForUpdate()
                    ->first();

                if (! $userModel) {
                    foreach ($userDeposits as $d) {
                        $failedCount++;
                        $failedDetails[] = [
                            'id' => $d->id,
                            'code' => $d->code,
                            'reason' => 'User account not found.',
                        ];
                    }

                    continue;
                }

                $totalRevokeAmount = (float) $userDeposits->sum('amount_usdt');
                $currentBalance = (float) $userModel->balance;

                if ($currentBalance < $totalRevokeAmount) {
                    foreach ($userDeposits as $d) {
                        $failedCount++;
                        $failedDetails[] = [
                            'id' => $d->id,
                            'code' => $d->code,
                            'reason' => "User current balance ({$currentBalance} USDT) is insufficient to revoke selected total ({$totalRevokeAmount} USDT).",
                        ];
                    }

                    continue;
                }

                $userModel->balance = round($currentBalance - $totalRevokeAmount, 8);
                $featureTiersEnabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
                if ($featureTiersEnabled) {
                    $userModel->total_deposited = max(0.0, round((float) $userModel->total_deposited - $totalRevokeAmount, 4));
                }
                $userModel->save();

                app(MembershipTierService::class)->recalculateUserTier($userModel);

                foreach ($userDeposits as $d) {
                    $d->status = 'revoked';
                    $d->save();
                    $successCount++;

                    Log::info("Admin [{$adminId}] bulk revoked Deposit ID [{$d->id}]: Code={$d->code}, Amount={$d->amount_usdt} USDT from User ID [{$userId}]. Reason: ".($reason ?: 'Bulk action'));
                }
            }

            return [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'failed_details' => $failedDetails,
                'message' => "Successfully revoked {$successCount} deposit(s).".($failedCount > 0 ? " {$failedCount} deposit(s) failed or skipped due to insufficient balance or invalid status." : ''),
            ];
        });
    }

    private function creditDepositToUser(Deposit $deposit): void
    {
        $userModel = UserModel::where('id', $deposit->getUserId())
            ->lockForUpdate()
            ->first();

        if (! $userModel) {
            throw new \RuntimeException('User not found for this deposit.');
        }

        $amount = (float) $deposit->getAmountUsdt();
        $userModel->balance = round((float) $userModel->balance + $amount, 8);
        $featureTiersEnabled = filter_var(config('app.feature_membership_tiers_enabled', false), FILTER_VALIDATE_BOOLEAN);
        if ($featureTiersEnabled) {
            $userModel->total_deposited = round((float) $userModel->total_deposited + $amount, 4);
        }
        $userModel->save();

        app(MembershipTierService::class)->recalculateUserTier($userModel);
    }
}
