<?php

namespace App\Application\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class AccountCheckLiveService
{
    public function extractUid(string $secretData): ?string
    {
        $parts = preg_split('/[|:\s]+/', trim($secretData));
        $candidate = trim($parts[0] ?? '');

        return preg_match('/^\d{4,20}$/', $candidate) ? $candidate : null;
    }

    /**
     * @param array<int, string> $secretLines
     * @return array<int, array{status: 'live'|'die', uid: ?string, reason: string}>
     */
    public function checkBatch(array $secretLines): array
    {
        $uids = array_map(fn (string $line) => $this->extractUid($line), $secretLines);

        $responses = Http::pool(function (Pool $pool) use ($uids): void {
            foreach ($uids as $index => $uid) {
                if ($uid === null) {
                    continue;
                }

                $pool->as((string) $index)
                    ->timeout(5)
                    ->retry(2, 200, null, false)
                    ->get("https://graph.facebook.com/{$uid}/picture", [
                        'redirect' => 'false',
                    ]);
            }
        });

        $results = [];
        foreach ($uids as $index => $uid) {
            if ($uid === null) {
                $results[$index] = [
                    'status' => 'die',
                    'uid' => null,
                    'reason' => 'invalid_uid',
                ];
                continue;
            }

            $status = 'die';
            $reason = 'no_response';

            if (isset($responses[$index])) {
                try {
                    $response = $responses[$index];
                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['data']['height']) && (int) $data['data']['height'] > 0) {
                            $status = 'live';
                            $reason = 'positive_profile_picture';
                        } else {
                            $reason = 'missing_profile_height';
                        }
                    } elseif (in_array($response->status(), [400, 404], true)) {
                        $reason = 'not_found';
                    } elseif ($response->status() === 429) {
                        $reason = 'rate_limited';
                    } else {
                        $reason = 'http_error';
                    }
                } catch (\Throwable) {
                    $reason = 'request_failed';
                }
            }

            $results[$index] = [
                'status' => $status,
                'uid' => $uid,
                'reason' => $reason,
            ];
        }

        return $results;
    }
}
