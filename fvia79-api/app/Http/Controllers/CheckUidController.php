<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class CheckUidController extends Controller
{
    /**
     * Check list of Facebook UIDs for Live or Die status.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        $rawInput = $request->input('uids');

        if (is_string($rawInput)) {
            $lines = explode("\n", $rawInput);
        } elseif (is_array($rawInput)) {
            $lines = $rawInput;
        } else {
            return response()->json([
                'message' => 'The uids field must be a string or array.',
            ], 422);
        }

        $items = [];
        foreach ($lines as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed === '') {
                continue;
            }
            $items[] = $trimmed;
        }

        if (empty($items)) {
            return response()->json([
                'message' => 'Please provide at least one UID to check.',
            ], 422);
        }

        if (count($items) > 500) {
            return response()->json([
                'message' => 'Maximum 500 UIDs allowed per request.',
            ], 422);
        }

        $parsedList = [];
        foreach ($items as $rawLine) {
            $parts = preg_split('/[|:\s]+/', $rawLine);
            $cleanUid = trim($parts[0] ?? '');

            $parsedList[] = [
                'raw' => $rawLine,
                'uid' => $cleanUid,
            ];
        }

        $responses = Http::pool(function (Pool $pool) use ($parsedList) {
            $requests = [];
            foreach ($parsedList as $index => $item) {
                $uid = $item['uid'];
                if (! empty($uid)) {
                    $requests[$index] = $pool->timeout(5)
                        ->get("https://graph.facebook.com/{$uid}/picture", [
                            'redirect' => 'false',
                        ]);
                }
            }
            return $requests;
        });

        $liveList = [];
        $dieList = [];
        $details = [];

        foreach ($parsedList as $index => $item) {
            $rawLine = $item['raw'];
            $uid = $item['uid'];
            $status = 'die';
            $avatarUrl = null;

            if (! empty($uid) && isset($responses[$index])) {
                $res = $responses[$index];
                if ($res->successful()) {
                    $data = $res->json();
                    if (isset($data['data']['height']) && $data['data']['height'] > 0) {
                        $status = 'live';
                        $avatarUrl = $data['data']['url'] ?? null;
                    }
                }
            }

            if ($status === 'live') {
                $liveList[] = $rawLine;
            } else {
                $dieList[] = $rawLine;
            }

            $details[] = [
                'raw' => $rawLine,
                'uid' => $uid,
                'status' => $status,
                'avatar' => $avatarUrl,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => count($parsedList),
                'live_count' => count($liveList),
                'die_count' => count($dieList),
                'live_uids' => $liveList,
                'die_uids' => $dieList,
                'details' => $details,
            ],
        ]);
    }
}
