<?php

namespace App\Application\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class AccountCheckLiveService
{
    /**
     * Trích xuất Facebook UID từ chuỗi thông tin tài khoản (raw line, cookie, pipe-separated, v.v.)
     */
    public function extractUid(string $secretData): ?string
    {
        $trimmed = trim($secretData);
        if ($trimmed === '') {
            return null;
        }

        // Loại bỏ BOM, zero-width chars, NBSP
        $clean = preg_replace('/[\x{FEFF}\x{200B}-\x{200D}]/u', '', $trimmed);
        $clean = str_replace("\xc2\xa0", ' ', $clean ?? $trimmed);
        $clean = trim($clean);

        // 1. Kiểm tra định dạng cookie c_user=...
        if (preg_match('/c_user\s*=\s*(\d{4,20})/', $clean, $matches)) {
            return $matches[1];
        }

        // 2. Kiểm tra UID ở đầu chuỗi (ví dụ 100012345678|pass... hoặc 4|Mark...)
        if (preg_match('/^(\d{1,20})\b/', $clean, $matches)) {
            return $matches[1];
        }

        // 3. Phân tách theo dấu | : hoặc khoảng trắng
        $parts = preg_split('/[|:\s]+/', $clean);
        $candidate = trim($parts[0] ?? '');
        if (preg_match('/^\d{1,20}$/', $candidate)) {
            return $candidate;
        }

        // 4. Fallback tìm chuỗi số 4-20 chữ số bất kỳ
        if (preg_match('/\b(\d{4,20})\b/', $clean, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check live status for a list of secret data lines concurrently using Http::pool.
     * Dựa trên chuẩn API https://graph2.facebook.com/v3.3/{uid}/picture?redirect=0
     *
     * @param array<int, string> $secretLines
     * @return array<int, array{status: 'live'|'die', uid: ?string, reason: string}>
     */
    public function checkBatch(array $secretLines): array
    {
        if (empty($secretLines)) {
            return [];
        }

        $parsed = [];
        foreach ($secretLines as $index => $line) {
            $uid = $this->extractUid((string) $line);
            $parsed[$index] = [
                'raw' => $line,
                'uid' => $uid,
            ];
        }

        // Gửi request song song đến graph2.facebook.com v3.3
        $responses = Http::pool(function (Pool $pool) use ($parsed): void {
            foreach ($parsed as $index => $item) {
                $uid = $item['uid'];
                if ($uid !== null && preg_match('/^\d+$/', $uid)) {
                    $pool->as((string) $index)
                        ->timeout(6)
                        ->retry(1, 100, null, false)
                        ->get("https://graph2.facebook.com/v3.3/{$uid}/picture", [
                            'redirect' => '0',
                        ]);
                }
            }
        });

        $results = [];
        foreach ($parsed as $index => $item) {
            $uid = $item['uid'];

            if ($uid === null) {
                $results[$index] = [
                    'status' => 'die',
                    'uid' => null,
                    'reason' => 'invalid_uid',
                ];
                continue;
            }

            $status = 'die';
            $reason = 'unresolved';

            if (isset($responses[$index])) {
                try {
                    $res = $responses[$index];
                    if ($res->successful()) {
                        $bodyText = $res->body();
                        $hasHeight = preg_match('/"height"\s*:\s*(\d+)/', $bodyText, $hMatch) && ((int) $hMatch[1]) > 0;
                        $hasWidth = preg_match('/"width"\s*:\s*(\d+)/', $bodyText, $wMatch) && ((int) $wMatch[1]) > 0;

                        if ($hasHeight && $hasWidth) {
                            $status = 'live';
                            $reason = 'positive_custom_avatar';
                        } else {
                            $status = 'die';
                            $reason = 'locked_or_inaccessible_profile';
                        }
                    } else {
                        $status = 'die';
                        $reason = 'graph_api_error';
                    }
                } catch (\Throwable) {
                    $status = 'die';
                    $reason = 'graph_api_error';
                }
            } else {
                $status = 'die';
                $reason = 'graph_api_error';
            }

            $results[$index] = [
                'status' => $status,
                'uid' => $uid,
                'reason' => $reason,
            ];
        }

        return $results;
    }

    public function checkSingle(string $secretData): bool
    {
        $res = $this->checkBatch([$secretData]);

        return ($res[0]['status'] ?? 'die') === 'live';
    }
}