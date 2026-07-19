<?php

namespace App\Application\Services;

use App\Domain\Entities\Deposit;
use App\Domain\Repositories\DepositRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepositService
{
    private DepositRepositoryInterface $depositRepository;
    private UserRepositoryInterface $userRepository;

    private string $adminWalletAddress;
    // API Key của blockchain scanner (cấu hình qua .env, hỗ trợ BscScan/Etherscan...)
    private string $scannerApiKey;
    // Địa chỉ contract của token đối soát (USDT mặc định trên BSC)
    private string $tokenContractAddress;
    // Số chữ số thập phân (decimals) của token đối soát (mặc định USDT trên BSC có 18 decimals)
    private int $tokenDecimals;

    private SystemSettingService $settingService;

    // Bộ nhớ đệm lưu danh sách giao dịch chuỗi trong một phiên làm việc để tránh spam API
    private ?array $cachedTransactions = null;

    public function __construct(
        DepositRepositoryInterface $depositRepository,
        UserRepositoryInterface $userRepository,
        SystemSettingService $settingService
    ) {
        $this->depositRepository = $depositRepository;
        $this->userRepository = $userRepository;
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
     * Lấy ảnh QR Binance Pay của Admin
     */
    public function getAdminBinanceQrSetting(): string
    {
        return $this->settingService->getSetting('admin_binance_qr', '');
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

        // 2. Nếu không cấu hình cố định, tự động lấy tỷ giá từ CoinGecko và Cache trong 10 phút
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
                ->where('amount_usdt', $baseAmount)
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

            // Sinh mã lệnh nạp ngắn gọn tăng dần với lock để tránh trùng code (Vá CVE-5)
            $latest = \App\Infrastructure\Persistence\Eloquent\DepositModel::orderBy('created_at', 'desc')->lockForUpdate()->first();
            $nextCode = $latest ? (string) ((int) $latest->code + 1) : '232';

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
        if (!$deposit || $deposit->getUserId() !== $userId || $deposit->getStatus() !== 'pending_payment') {
            return false;
        }

        // Tự động kiểm tra đối soát lần cuối trước khi chuyển sang trạng thái hủy (Vá CVE-6)
        try {
            $matched = $this->checkTransaction($id, $userId);
            if ($matched) {
                // Đã khớp giao dịch và cộng tiền thành công trên blockchain, không hủy nữa
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to check blockchain before canceling deposit ID {$id}: " . $e->getMessage());
            throw new \RuntimeException("Unable to verify payment status on blockchain right now. Please try again later.");
        }

        $deposit->setStatus('expired');
        $this->depositRepository->save($deposit);
        return true;
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
            $this->adminWalletAddress = $this->settingService->getSetting('admin_wallet_address', '');
            $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');

            if (empty($this->adminWalletAddress)) {
                throw new \RuntimeException("The administrator has not configured the receiving USDT wallet address yet.");
            }
            if (empty($this->scannerApiKey)) {
                throw new \RuntimeException("The administrator has not configured the scanner API key yet.");
            }

            $adminWallet = strtolower($this->adminWalletAddress);
            $tokenContract = strtolower($this->tokenContractAddress);

            // Fetch Moralis API ngoài transaction để tránh giữ lock lâu trong lúc chờ HTTP response
            if ($this->cachedTransactions === null) {
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
                    $this->cachedTransactions = $result['result'] ?? [];
                } else {
                    Log::error("Moralis API HTTP error: " . $response->status() . " - " . $response->body());
                    throw new \RuntimeException("Unable to connect to blockchain scanner API. Please try again later.");
                }
            }

            $transactions = $this->cachedTransactions;

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

                    $user = $this->userRepository->findById($userId);
                    if ($user) {
                        $user->addBalance($deposit->getAmountVnd());
                        $this->userRepository->save($user);
                    }

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

            if ($this->cachedTransactions === null) {
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
                    $this->cachedTransactions = $result['data'] ?? [];
                } else {
                    Log::error("Binance Pay API HTTP error: " . $response->status() . " - " . $response->body());
                    throw new \RuntimeException("Unable to connect to Binance Pay API. Please try again later.");
                }
            }

            $transactions = $this->cachedTransactions;

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

                    $txValueUsdt = (float) ($tx['orderAmount'] ?? 0);
                    $amountToPay = $deposit->getAmountToPay();

                    // So khớp số lượng thực nhận chính xác 100% không dung sai
                    if (round($txValueUsdt, 4) !== round($amountToPay, 4)) {
                        continue;
                    }

                    // Không kiểm tra Binance ID người gửi theo cấu hình mới, đối soát hoàn toàn bằng số tiền lẻ giống BEP-20

                    // Kiểm tra orderId này đã được dùng đối soát chưa
                    $orderId = $tx['orderId'] ?? '';
                    if (empty($orderId) || $this->depositRepository->isTxHashUsed($orderId)) {
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

                    // Giao dịch khớp hoàn toàn!
                    $deposit->setStatus('completed');
                    $deposit->setTxHash($orderId);
                    $this->depositRepository->save($deposit);

                    $user = $this->userRepository->findById($userId);
                    if ($user) {
                        $user->addBalance($deposit->getAmountVnd());
                        $this->userRepository->save($user);
                    }

                    Log::info("Binance Pay Deposit successfully matched and processed: Invoice #{$deposit->getCode()}, Amount: {$deposit->getAmountUsdt()} USDT, Binance Order ID: {$orderId}");
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
        $this->cachedTransactions = null;

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
                        $deposit->update(['status' => 'expired']);
                        if ($logCallback) $logCallback('info', "Deposit code {$deposit->code} has expired (exceeded 15 minutes window + 1 minute grace period).");
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
}