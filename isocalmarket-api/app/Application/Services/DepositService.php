<?php

namespace App\Application\Services;

use App\Domain\Entities\Deposit;
use App\Domain\Repositories\DepositRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepositService
{
    private DepositRepositoryInterface $depositRepository;

    private string $adminWalletAddress;
    // API Key của blockchain scanner (cấu hình qua .env, hỗ trợ BscScan/Etherscan...)
    private string $scannerApiKey;
    // Địa chỉ contract của token đối soát (USDT mặc định trên BSC)
    private string $tokenContractAddress;
    // Số chữ số thập phân (decimals) của token đối soát (mặc định USDT trên BSC có 18 decimals)
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

        // Cấu hình linh hoạt token đối soát (mặc định là USDT BEP20 trên BSC)
        $this->tokenContractAddress = env('TOKEN_CONTRACT_ADDRESS', '0x55d398326f99059ff775485246999027b3197955');
        $this->tokenDecimals = (int) env('TOKEN_DECIMALS', 18);
    }

    /**
     * Lấy ví nhận tiền của Admin
     */
    public function getAdminWalletAddress(): string
    {
        return $this->settingService->getSetting('admin_wallet_address', '');
    }

    /**
     * Lấy Binance ID nhận tiền của Admin
     */
    public function getAdminBinanceIdSetting(): string
    {
        return $this->settingService->getSetting('admin_binance_id', '');
    }

    /**
     * Lấy tỷ giá USDT/VND từ CoinGecko API (Free)
     * Cache kết quả trong 10 phút (600 giây)
     */
    public function getUsdtToVndRate(): float
    {
        // 1. Kiểm tra xem admin có thiết lập tỷ giá cố định trong cài đặt hệ thống không
        $fixedRate = $this->settingService->getSetting('usdt_exchange_rate', '0');
        if (!empty($fixedRate) && (float) $fixedRate > 0) {
            return (float) $fixedRate;
        }

        return Cache::remember('usdt_to_vnd_rate', 600, function () {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['accept' => 'application/json'])
                    ->get('https://api.coingecko.com/api/v3/simple/price', [
                        'ids' => 'tether',
                        'vs_currencies' => 'vnd'
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['tether']['vnd'])) {
                        $rate = (float) $data['tether']['vnd'];
                        Log::info("CoinGecko USDT/VND rate fetched successfully: {$rate}");
                        return $rate;
                    }
                }

                Log::warning("CoinGecko API call succeeded but response format was invalid: " . $response->body());
            } catch (\Exception $e) {
                Log::error("Failed to fetch USDT rate from CoinGecko: " . $e->getMessage());
            }

            // Fallback tỷ giá mặc định nếu API lỗi
            return 26500.0;
        });
    }

    /**
     * Lập lệnh nạp tiền mới cho người dùng
     */
    /**
     * Lập lệnh nạp tiền mới cho người dùng
     */
    public function createDeposit(string $userId, float $amountUsdt, string $paymentMethod = 'bep20', ?string $payerBinanceId = null): Deposit
    {
        $this->adminWalletAddress = $this->settingService->getSetting('admin_wallet_address', '');
        $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');
        $adminBinanceIdSetting = $this->settingService->getSetting('admin_binance_id', '');
        $binanceApiKeySetting = $this->settingService->getSetting('binance_api_key', '');
        $binanceApiSecretSetting = $this->settingService->getSetting('binance_api_secret', '');

        if ($paymentMethod === 'bep20') {
            if (empty($this->adminWalletAddress)) {
                throw new \RuntimeException("The administrator has not configured the receiving USDT wallet address yet.");
            }
            if (empty($this->scannerApiKey)) {
                throw new \RuntimeException("The administrator has not configured the scanner API key yet.");
            }
        } else if ($paymentMethod === 'binance') {
            if (empty($adminBinanceIdSetting)) {
                throw new \RuntimeException("The administrator has not configured the receiving Binance ID yet.");
            }
            if (empty($binanceApiKeySetting) || empty($binanceApiSecretSetting)) {
                throw new \RuntimeException("The administrator has not configured the Binance API Key/Secret yet.");
            }
        }

        if ($amountUsdt < 0.1) {
            throw new \RuntimeException("The deposit amount must be at least 0.1 USDT.");
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $amountUsdt, $paymentMethod, $payerBinanceId, $adminBinanceIdSetting) {
            // Chặn tạo nhiều hóa đơn nếu đang có hóa đơn chờ thanh toán chưa giải quyết (với lock)
            $hasPending = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('user_id', $userId)
                ->where('status', 'pending_payment')
                ->lockForUpdate()
                ->exists();

            if ($hasPending) {
                throw new \RuntimeException("You already have an active deposit order. Please cancel the previous order before creating a new one.");
            }

            $rate = $this->getUsdtToVndRate();
            $baseAmount = round($amountUsdt, 2);

            // Khóa các số tiền lẻ đang chờ thanh toán HOẶC mới bị hủy/hết hạn trong vòng 10 phút
            $activeAmounts = \App\Infrastructure\Persistence\Eloquent\DepositModel::where(function ($q) {
                $q->where('status', 'pending_payment')
                    ->orWhere(function ($sub) {
                        $sub->whereIn('status', ['failed', 'expired'])
                            ->where('updated_at', '>=', now()->subMinutes(10));
                    });
            })
                ->lockForUpdate()
                ->pluck('amount_to_pay')
                ->toArray();

            // Tìm đuôi lẻ tiếp theo (từ .0000, .0001, .0002, ...) chưa bị trùng lắp
            $suffix = 0.0000;
            $step = 0.0001;
            $maxSuffix = 0.0999;

            while (true) {
                $candidateAmount = round($baseAmount + $suffix, 4);

                if (!in_array($candidateAmount, $activeAmounts)) {
                    $amountToPay = $candidateAmount;
                    break;
                }

                $suffix += $step;

                if ($suffix > $maxSuffix) {
                    throw new \RuntimeException("Too many pending transactions for this amount. Please try again in a few minutes.");
                }
            }

            $amountVnd = round($baseAmount * $rate);

            // Sinh mã lệnh nạp gồm 6 ký tự (chữ viết hoa + số) không trùng lặp (loại bỏ 0, O, 1, I để tránh nhầm lẫn)
            $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
            do {
                $nextCode = '';
                for ($i = 0; $i < 6; $i++) {
                    $nextCode .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $exists = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('code', $nextCode)
                    ->lockForUpdate()
                    ->exists();
            } while ($exists);

            $deposit = new Deposit(
                id: null, // Sẽ tự sinh UUIDv7 ở Model
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

    /**
     * Lấy chi tiết một hóa đơn nạp
     */
    public function getInvoice(string $id): ?Deposit
    {
        return $this->depositRepository->find($id);
    }

    /**
     * Hủy lệnh nạp chủ động
     */
    public function cancelInvoice(string $id, string $userId): bool
    {
        $deposit = $this->depositRepository->find($id);
        if (!$deposit || $deposit->getStatus() !== 'pending_payment') {
            return false;
        }

        $user = \App\Infrastructure\Persistence\Eloquent\UserModel::find($userId);
        $isAdmin = $user && $user->role === 'admin';

        if ($deposit->getUserId() !== $userId && !$isAdmin) {
            return false;
        }

        try {
            $matched = $this->checkTransaction($id, $deposit->getUserId());
            if ($matched) {
                // Đã khớp giao dịch và cộng tiền thành công trên blockchain, không hủy nữa
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to check blockchain before canceling deposit ID {$id}: " . $e->getMessage());
            throw new \RuntimeException("Unable to verify payment status on blockchain right now. Please try again later.");
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $userId) {
            $depositModel = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$depositModel || $depositModel->status !== 'pending_payment') {
                return false;
            }

            $user = \App\Infrastructure\Persistence\Eloquent\UserModel::find($userId);
            $isAdmin = $user && $user->role === 'admin';
            if ($depositModel->user_id !== $userId && !$isAdmin) {
                return false;
            }

            $depositModel->status = 'expired';
            $depositModel->save();
            return true;
        });
    }

    /**
     * Lấy lịch sử nạp tiền phân trang và có lọc/tìm kiếm của user
     */
    public function getHistoryPaginated(
        string $userId,
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
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
            'items' => array_map(fn($m) => \App\Infrastructure\Persistence\Mappers\DepositMapper::toDomain($m), $paginator->items()),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'limit' => $limit
        ];
    }

    /**
     * BOT tự động đối soát giao dịch chuỗi block (BscScan/Moralis API)
     * Quét các giao dịch token BEP-20 được gửi đến ví chủ sàn
     */
    public function checkTransaction(string $id, string $userId): bool
    {
        $depositModel = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)->first();
        if (!$depositModel) {
            return false;
        }

        $paymentMethod = $depositModel->payment_method ?: 'bep20';

        if ($paymentMethod === 'bep20') {
            $this->adminWalletAddress = $depositModel->admin_wallet_address
                ?: $this->settingService->getSetting('admin_wallet_address', '');
            $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');

            if (empty($this->adminWalletAddress)) {
                throw new \RuntimeException("The administrator has not configured the receiving USDT wallet address yet.");
            }
            if (empty($this->scannerApiKey)) {
                throw new \RuntimeException("The administrator has not configured the scanner API key yet.");
            }

            $adminWallet = strtolower($this->adminWalletAddress);
            $tokenContract = strtolower($this->tokenContractAddress);

            // Fetch Moralis API ngoài transaction và lưu Cache 15s để tránh dính Rate Limit khi Polling
            $transactions = Cache::remember("bep20_txs_{$adminWallet}", 15, function () use ($adminWallet) {
                $response = Http::timeout(10)
                    ->withHeader('X-API-Key', $this->scannerApiKey)
                    ->get("https://deep-index.moralis.io/api/v2.2/{$adminWallet}/erc20/transfers", [
                        'chain'               => 'bsc',
                        'contract_addresses'  => [$this->tokenContractAddress],
                        'order'               => 'DESC',
                        'limit'               => 100,
                    ]);

                if ($response->successful()) {
                    $result = $response->json();
                    return $result['result'] ?? [];
                }

                Log::error("Moralis API HTTP error: " . $response->status() . " - " . $response->body());
                throw new \RuntimeException("Unable to connect to blockchain scanner API. Please try again later.");
            });

            // Vá CVE-1: Bọc toàn bộ check deposit status, isTxHashUsed và credit tiền trong 1 DB transaction với pessimistic lock (lockForUpdate)
            return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $userId, $adminWallet, $tokenContract, $transactions) {
                $depositModel = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$depositModel) {
                    return false;
                }

                $deposit = \App\Infrastructure\Persistence\Mappers\DepositMapper::toDomain($depositModel);

                if ($deposit->getUserId() !== $userId || $deposit->getStatus() !== 'pending_payment') {
                    Log::warning("Deposit check request invalid or already processed for invoice ID: {$id}");
                    return false;
                }

                foreach ($transactions as $tx) {
                    // 1. Kiểm tra token có khớp với token đối soát cấu hình không
                    if (strtolower($tx['address'] ?? '') !== $tokenContract) {
                        continue;
                    }

                    // 2. Kiểm tra ví nhận có phải ví chủ sàn không
                    if (strtolower($tx['to_address'] ?? '') !== $adminWallet) {
                        continue;
                    }

                    // 3. Quy đổi số lượng chuyển khoản từ wei sang giá trị thực
                    $txValueUsdt = (float) ($tx['value'] ?? 0) / pow(10, $this->tokenDecimals);
                    $amountToPay = $deposit->getAmountToPay();

                    // 4. So khớp số lượng thực nhận chính xác 100% không dung sai (Vá CVE-4)
                    if (round($txValueUsdt, 4) !== round($amountToPay, 4)) {
                        continue;
                    }

                    // 5. Kiểm tra mã băm TxHash này đã được dùng đối soát chưa (Vá CVE-1 & CVE-3 - thực hiện trong lock)
                    $txHash = $tx['transaction_hash'] ?? '';
                    if (empty($txHash) || $this->depositRepository->isTxHashUsed($txHash)) {
                        continue;
                    }

                    // 6. Kiểm tra giao dịch có diễn ra trong vòng 15 phút (900 giây) kể từ lúc tạo hóa đơn không
                    $invoiceCreatedEpoch = $deposit->getCreatedAt()->getTimestamp();
                    $txEpoch = isset($tx['block_timestamp'])
                        ? (new \DateTime($tx['block_timestamp']))->getTimestamp()
                        : 0;
                    $timeDiff = $txEpoch - $invoiceCreatedEpoch;
                    if ($timeDiff < -60 || $timeDiff > 900) {
                        continue;
                    }

                    // Giao dịch khớp hoàn toàn! Cập nhật hóa đơn & cộng tiền user trong cùng transaction lock
                    $deposit->setStatus('completed');
                    $deposit->setTxHash($txHash);
                    $this->depositRepository->save($deposit);

                    $this->creditDepositToUser($deposit);

                    Log::info("Deposit successfully matched and processed: Invoice #{$deposit->getCode()}, Amount: {$deposit->getAmountUsdt()} USDT, TxHash: {$txHash}");
                    return true;
                }

                return false;
            });
        } else if ($paymentMethod === 'binance') {
            $binanceApiKey = $this->settingService->getSetting('binance_api_key', '');
            $binanceApiSecret = $this->settingService->getSetting('binance_api_secret', '');

            if (empty($binanceApiKey) || empty($binanceApiSecret)) {
                throw new \RuntimeException("The administrator has not configured the Binance API Key/Secret yet.");
            }

            $transactions = Cache::remember("binance_pay_txs", 15, function () use ($binanceApiKey, $binanceApiSecret) {
                $timestamp = round(microtime(true) * 1000);
                $queryString = "timestamp=" . $timestamp . "&recvWindow=60000";
                $signature = hash_hmac('sha256', $queryString, $binanceApiSecret);

                $response = Http::timeout(10)
                    ->withHeaders([
                        'X-MBX-APIKEY' => $binanceApiKey
                    ])
                    ->get("https://api.binance.com/sapi/v1/pay/transactions?" . $queryString . "&signature=" . $signature);

                if ($response->successful()) {
                    $result = $response->json();
                    if (($result['success'] ?? false) !== true || ($result['code'] ?? '') !== '000000') {
                        Log::error('Binance Pay API returned an unsuccessful payload: ' . $response->body());
                        throw new \RuntimeException('Binance Pay API rejected the transaction history request.');
                    }
                    return $result['data'] ?? [];
                }

                Log::error("Binance Pay API HTTP error: " . $response->status() . " - " . $response->body());
                throw new \RuntimeException("Unable to connect to Binance Pay API. Please try again later.");
            });

            return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $userId, $transactions) {
                $depositModel = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (!$depositModel) {
                    return false;
                }

                $deposit = \App\Infrastructure\Persistence\Mappers\DepositMapper::toDomain($depositModel);

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

                    // So khớp số lượng thực nhận chính xác 100% không dung sai
                    if ($txValueUsdt <= 0 || round($txValueUsdt, 4) !== round($amountToPay, 4)) {
                        continue;
                    }

                    // Không kiểm tra Binance ID người gửi theo cấu hình mới, đối soát hoàn toàn bằng số tiền lẻ giống BEP-20

                    // Kiểm tra transactionId này đã được dùng đối soát chưa
                    $transactionId = $tx['transactionId'] ?? '';
                    if (empty($transactionId) || $this->depositRepository->isTxHashUsed($transactionId)) {
                        continue;
                    }

                    // Kiểm tra thời gian giao dịch trong vòng 15 phút (900 giây)
                    $invoiceCreatedEpoch = $deposit->getCreatedAt()->getTimestamp();
                    $txEpoch = isset($tx['transactionTime'])
                        ? round((float)$tx['transactionTime'] / 1000)
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

    /**
     * Tự động quét và đối soát toàn bộ hóa đơn nạp USDT BEP20 đang ở trạng thái pending_payment
     *
     * @param \Closure|null $logCallback Hàm callback để in log ra màn hình console khi chạy Artisan
     */
    public function checkAllPendingDeposits(?\Closure $logCallback = null): void
    {
        $pendingDeposits = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('status', 'pending_payment')->get();

        if ($pendingDeposits->isEmpty()) {
            if ($logCallback) $logCallback('info', 'No pending deposits found.');
            return;
        }

        // Xóa cache trước mỗi lần chạy batch (Vá CVE-10)
        $this->cachedBep20TransactionsByWallet = [];
        $this->cachedBinanceTransactions = null;

        foreach ($pendingDeposits as $deposit) {
            try {
                if ($logCallback) $logCallback('info', "Checking deposit code: {$deposit->code} (ID: {$deposit->id})");

                // Đối soát blockchain
                $matched = $this->checkTransaction($deposit->id, $deposit->user_id);

                if ($matched) {
                    if ($logCallback) $logCallback('info', "Successfully matched and completed deposit code: {$deposit->code}");
                } else {
                    // Kiểm tra hết hạn (16 phút = 960 giây)
                    $createdTime = $deposit->created_at->getTimestamp();
                    $now = time();
                    if (($now - $createdTime) > 960) {
                        $expired = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $deposit->id)
                            ->where('status', 'pending_payment')
                            ->update(['status' => 'expired']);
                        if ($expired && $logCallback) {
                            $logCallback('info', "Deposit code {$deposit->code} has expired (exceeded 15 minutes window + 1 minute grace period).");
                        }
                    } else {
                        if ($logCallback) $logCallback('info', "Deposit code {$deposit->code} is still pending within the payment window.");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to check pending deposit ID {$deposit->id}: " . $e->getMessage());
                if ($logCallback) $logCallback('error', "Error checking deposit ID {$deposit->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Lấy danh sách nạp tiền phân trang dành cho Admin
     */
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

    /**
     * Admin duyệt thủ công hóa đơn nạp tiền
     */
    public function approveDepositManually(string $id, string $txHash, string $adminId): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $txHash, $adminId) {
            $depositModel = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$depositModel) {
                throw new \RuntimeException("Deposit invoice not found.");
            }

            $deposit = \App\Infrastructure\Persistence\Mappers\DepositMapper::toDomain($depositModel);

            if (!in_array($deposit->getStatus(), ['pending_payment', 'expired'], true)) {
                throw new \RuntimeException("Only pending or expired deposit invoices can be approved.");
            }

            $manualReference = trim($txHash);
            if ($manualReference === '') {
                throw new \RuntimeException("A transaction ID/hash is required for manual approval.");
            }

            $finalTxHash = 'manual_approved_by_admin_' . $manualReference;
            if (mb_strlen($finalTxHash) > 255) {
                throw new \RuntimeException("Transaction ID/hash is too long.");
            }

            if ($this->depositRepository->isTxHashUsed($finalTxHash)) {
                throw new \RuntimeException("This transaction hash has already been used for another deposit.");
            }

            $deposit->setStatus('completed');
            $deposit->setTxHash($finalTxHash);
            $this->depositRepository->save($deposit);

            $this->creditDepositToUser($deposit);

            Log::info("Admin [{$adminId}] manually approved Deposit ID [{$id}]: Code={$deposit->getCode()}, Amount={$deposit->getAmountUsdt()} USDT ({$deposit->getAmountVnd()} VND), Payer ID/Wallet=" . ($deposit->getPayerBinanceId() ?: $deposit->getAdminWalletAddress()) . ", TxHash={$finalTxHash}");

            return true;
        });
    }

    /**
     * Admin từ chối/hủy hóa đơn nạp tiền
     */
    public function rejectDepositManually(string $id, string $adminId): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $adminId) {
            $depositModel = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$depositModel) {
                throw new \RuntimeException("Deposit invoice not found.");
            }

            $deposit = \App\Infrastructure\Persistence\Mappers\DepositMapper::toDomain($depositModel);

            if ($deposit->getStatus() === 'completed') {
                throw new \RuntimeException("Cannot reject a completed deposit invoice.");
            }

            if ($deposit->getStatus() === 'failed') {
                throw new \RuntimeException("This deposit invoice is already in failed status.");
            }

            $deposit->setStatus('failed');
            $this->depositRepository->save($deposit);

            Log::info("Admin [{$adminId}] manually rejected Deposit ID [{$id}]: Code={$deposit->getCode()}");

            return true;
        });
    }

    /**
     * Credit the invoice's USD balance while holding the user row lock.
     * Callers must already be inside a database transaction.
     */
    private function creditDepositToUser(Deposit $deposit): void
    {
        $userModel = \App\Infrastructure\Persistence\Eloquent\UserModel::where('id', $deposit->getUserId())
            ->lockForUpdate()
            ->first();

        if (!$userModel) {
            throw new \RuntimeException('User not found for this deposit.');
        }

        $userModel->balance = round((float) $userModel->balance + $deposit->getAmountUsdt(), 4);
        $userModel->save();
    }
}
