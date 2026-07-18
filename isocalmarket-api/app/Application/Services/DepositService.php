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
     * Lấy tỷ giá USDT/VND từ CoinGecko API (Free)
     * Cache kết quả trong 10 phút (600 giây)
     */
    public function getUsdtToVndRate(): float
    {
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
    public function createDeposit(string $userId, float $amountUsdt): Deposit
    {
        $this->adminWalletAddress = $this->settingService->getSetting('admin_wallet_address', '');
        $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');

        if (empty($this->adminWalletAddress)) {
            throw new \RuntimeException("The administrator has not configured the receiving USDT wallet address yet.");
        }
        if (empty($this->scannerApiKey)) {
            throw new \RuntimeException("The administrator has not configured the scanner API key yet.");
        }

        // Chặn tạo nhiều hóa đơn nếu đang có hóa đơn chờ thanh toán chưa giải quyết
        $hasPending = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('user_id', $userId)
            ->where('status', 'pending_payment')
            ->exists();

        if ($hasPending) {
            throw new \RuntimeException("You already have an active deposit order. Please cancel the previous order before creating a new one.");
        }

        $rate = $this->getUsdtToVndRate();
        
        // Làm tròn số tiền nạp gốc về 2 chữ số thập phân
        $baseAmount = round($amountUsdt, 2);

        $activeAmounts = \App\Infrastructure\Persistence\Eloquent\DepositModel::where('status', 'pending_payment')
            ->where('amount_usdt', $baseAmount)
            ->pluck('amount_to_pay')
            ->toArray();

        // Tìm đuôi lẻ tiếp theo (từ .0000, .0001, .0002, ...) chưa bị trùng lắp
        $suffix = 0.0000;
        $step = 0.0001;
        $maxSuffix = 0.0999; // Giới hạn tối đa 1000 người nạp trùng số tiền chẵn trong vòng 15 phút

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

        $amountVnd = round($baseAmount * $rate, 2);

        // Sinh mã lệnh nạp ngắn gọn tăng dần (dạng string số, VD: 232)
        $latest = \App\Infrastructure\Persistence\Eloquent\DepositModel::orderBy('created_at', 'desc')->first();
        $nextCode = $latest ? (string) ((int) $latest->code + 1) : '232';

        $deposit = new Deposit(
            id: null, // Sẽ tự sinh UUIDv7 ở Model
            userId: $userId,
            code: $nextCode,
            amountUsdt: $baseAmount,
            amountToPay: $amountToPay,
            amountVnd: $amountVnd,
            txHash: null,
            status: 'pending_payment'
        );

        return $this->depositRepository->save($deposit);
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

        // Tự động kiểm tra đối soát lần cuối trước khi chuyển sang trạng thái hủy
        try {
            $matched = $this->checkTransaction($id, $userId);
            if ($matched) {
                // Đã khớp giao dịch và cộng tiền thành công trên blockchain, không hủy nữa
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to check blockchain before canceling deposit ID {$id}: " . $e->getMessage());
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
     * BOT tự động đối soát giao dịch chuỗi block (BscScan API)
     * Quét các giao dịch token BEP-20 được gửi đến ví chủ sàn
     */
    public function checkTransaction(string $id, string $userId): bool
    {
        $this->adminWalletAddress = $this->settingService->getSetting('admin_wallet_address', '');
        $this->scannerApiKey = $this->settingService->getSetting('scanner_api_key', '');

        if (empty($this->adminWalletAddress)) {
            throw new \RuntimeException("The administrator has not configured the receiving USDT wallet address yet.");
        }
        if (empty($this->scannerApiKey)) {
            throw new \RuntimeException("The administrator has not configured the scanner API key yet.");
        }

        $deposit = $this->depositRepository->find($id);
        if (!$deposit || $deposit->getUserId() !== $userId || $deposit->getStatus() !== 'pending_payment') {
            Log::warning("Deposit check request invalid for invoice ID: {$id}");
            return false;
        }

        $adminWallet = strtolower($this->adminWalletAddress);
        $tokenContract = strtolower($this->tokenContractAddress);
        
        try {
            $response = Http::timeout(10)->get('https://api.etherscan.io/api', [
                'module' => 'account',
                'action' => 'tokentx',
                'address' => $adminWallet,
                'startblock' => 0,
                'endblock' => 99999999,
                'sort' => 'desc',
                'apikey' => $this->scannerApiKey
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === '1' && is_array($result['result'])) {
                    $transactions = $result['result'];
                    
                    foreach ($transactions as $tx) {
                        // 1. Kiểm tra token có khớp với token đối soát cấu hình không
                        if (strtolower($tx['contractAddress']) !== $tokenContract) {
                            continue;
                        }

                        // 2. Kiểm tra ví nhận có phải ví chủ sàn không
                        if (strtolower($tx['to']) !== $adminWallet) {
                            continue;
                        }

                        // 3. Quy đổi số lượng chuyển khoản từ wei sang giá trị thực dựa trên Decimals cấu hình
                        $txValueUsdt = (float) $tx['value'] / pow(10, $this->tokenDecimals);
                        $amountToPay = $deposit->getAmountToPay();

                        // 4. So khớp số lượng thực nhận (dung sai làm tròn cực nhỏ 0.0001)
                        if (abs($txValueUsdt - $amountToPay) > 0.00009) {
                            continue;
                        }

                        // 5. Kiểm tra mã băm TxHash này đã được dùng đối soát trước đó chưa
                        $txHash = $tx['hash'];
                        if ($this->depositRepository->isTxHashUsed($txHash)) {
                            continue;
                        }

                        // 6. Kiểm tra giao dịch có diễn ra trong vòng 15 phút (900 giây) kể từ lúc tạo hóa đơn không
                        $invoiceCreatedEpoch = $deposit->getCreatedAt()->getTimestamp();
                        $txEpoch = (int) $tx['timeStamp'];
                        $timeDiff = $txEpoch - $invoiceCreatedEpoch;
                        if ($timeDiff < -60 || $timeDiff > 900) { // Cho phép dung sai 60 giây trước khi tạo lệnh do lệch đồng hồ mạng
                            continue;
                        }

                        // Tìm thấy giao dịch khớp hoàn toàn! Tiến hành cộng tiền và hoàn thành hóa đơn
                        $deposit->setStatus('completed');
                        $deposit->setTxHash($txHash);
                        $this->depositRepository->save($deposit);

                        $user = $this->userRepository->find($userId);
                        if ($user) {
                            $user->addBalance($deposit->getAmountUsdt());
                            $this->userRepository->save($user);
                        }

                        Log::info("Deposit successfully matched and processed: Invoice #{$deposit->getCode()}, Amount: {$deposit->getAmountUsdt()} USDT, TxHash: {$txHash}");
                        return true;
                    }
                } else {
                    Log::warning("Etherscan API response status error: " . json_encode($result));
                }
            } else {
                Log::error("Etherscan API HTTP status code error: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Failed to query Etherscan transactions: " . $e->getMessage());
        }

        return false;
    }
}
