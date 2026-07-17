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

    // Địa chỉ ví nhận USDT BEP20 của chủ sàn (cấu hình động qua .env hoặc lấy mặc định)
    private string $adminWalletAddress;
    // API Key BscScan (cấu hình qua .env hoặc lấy test key mặc định)
    private string $bscScanApiKey;

    public function __construct(
        DepositRepositoryInterface $depositRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->depositRepository = $depositRepository;
        $this->userRepository = $userRepository;

        $this->adminWalletAddress = env('ADMIN_WALLET_ADDRESS', '0x889812A612c6aDce4dbDb7f54c25B6c3FE5c26F3');
        $this->bscScanApiKey = env('BSCSCAN_API_KEY', '94CWX8UC3A5UI54EA2B9MAHW2VXRGB3G82');
    }

    /**
     * Lấy tỷ giá USDT/VND từ CoinGecko API (Free)
     * Cache kết quả trong 10 phút (600 giây)
     */
    public function getUsdtToVndRate(): float
    {
        return Cache::remember('usdt_to_vnd_rate', 600, function () {
            try {
                // Gọi API CoinGecko
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
        $rate = $this->getUsdtToVndRate();
        
        // Sinh đuôi lẻ đối soát ngẫu nhiên (từ 0.0001 đến 0.0999) để phân biệt các giao dịch chuyển khoản trùng lượng USDT chẵn
        $randomSuffix = rand(1, 999) / 10000;
        $amountToPay = round($amountUsdt + $randomSuffix, 4);
        $amountVnd = round($amountUsdt * $rate, 2);

        // Sinh mã lệnh nạp ngắn gọn tăng dần (dạng string số, VD: 232)
        // Lấy mã code số lớn nhất hiện tại
        $latest = \App\Infrastructure\Persistence\Eloquent\DepositModel::orderBy('created_at', 'desc')->first();
        $nextCode = $latest ? (string) ((int) $latest->code + 1) : '232';

        $deposit = new Deposit(
            id: null, // Sẽ tự sinh UUIDv7 ở Model
            userId: $userId,
            code: $nextCode,
            amountUsdt: $amountUsdt,
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

        // Lọc theo trạng thái
        if ($status) {
            $query->where('status', $status);
        }

        // Lọc theo khoảng ngày tạo
        if ($startDate) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        // Tìm kiếm theo mã lệnh hoặc số tiền
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
        $deposit = $this->depositRepository->find($id);
        if (!$deposit || $deposit->getUserId() !== $userId || $deposit->getStatus() !== 'pending_payment') {
            Log::warning("Deposit check request invalid for invoice ID: {$id}");
            return false;
        }

        $adminWallet = strtolower($this->adminWalletAddress);
        $usdtContract = '0x55d398326f99059ff775485246999027b3197955'; // USDT Contract Address on BSC (BEP-20)
        
        try {
            // Gọi BscScan API lấy danh sách các giao dịch BEP-20 gần đây của địa chỉ ví chủ sàn
            // Etherscan v2 API standard hỗ trợ endpoint này
            $response = Http::timeout(10)->get('https://api.bscscan.com/api', [
                'module' => 'account',
                'action' => 'tokentx',
                'address' => $adminWallet,
                'startblock' => 0,
                'endblock' => 99999999,
                'sort' => 'desc',
                'apikey' => $this->bscScanApiKey
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === '1' && is_array($result['result'])) {
                    $transactions = $result['result'];
                    
                    foreach ($transactions as $tx) {
                        // 1. Kiểm tra token có phải USDT BEP20 không
                        if (strtolower($tx['contractAddress']) !== $usdtContract) {
                            continue;
                        }

                        // 2. Kiểm tra ví nhận có phải ví chủ sàn không
                        if (strtolower($tx['to']) !== $adminWallet) {
                            continue;
                        }

                        // 3. Quy đổi số lượng chuyển khoản từ wei sang USDT (USDT có 18 số lẻ thập phân trên BSC)
                        $txValueUsdt = (float) $tx['value'] / 1e18;
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

                        // Cộng balance vào user
                        $user = $this->userRepository->find($userId);
                        if ($user) {
                            $user->addBalance($deposit->getAmountUsdt());
                            $this->userRepository->save($user);
                        }

                        Log::info("Deposit successfully matched and processed: Invoice #{$deposit->getCode()}, Amount: {$deposit->getAmountUsdt()} USDT, TxHash: {$txHash}");
                        return true;
                    }
                } else {
                    Log::warning("BscScan API response status error: " . json_encode($result));
                }
            } else {
                Log::error("BscScan API HTTP status code error: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Failed to query BscScan transactions: " . $e->getMessage());
        }

        return false;
    }
}
