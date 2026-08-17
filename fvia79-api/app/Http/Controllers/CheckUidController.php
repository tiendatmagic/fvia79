<?php

namespace App\Http\Controllers;

use App\Application\Services\AccountCheckLiveService;
use Illuminate\Http\Request;

class CheckUidController extends Controller
{
    private AccountCheckLiveService $checkLiveService;

    public function __construct(AccountCheckLiveService $checkLiveService)
    {
        $this->checkLiveService = $checkLiveService;
    }

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

        $checkResults = $this->checkLiveService->checkBatch($items);

        $liveList = [];
        $dieList = [];
        $details = [];

        foreach ($items as $index => $rawLine) {
            $res = $checkResults[$index] ?? ['status' => 'die', 'uid' => null, 'reason' => 'unknown'];
            $status = $res['status'] ?? 'die';

            if ($status === 'live') {
                $liveList[] = $rawLine;
            } else {
                $dieList[] = $rawLine;
            }

            $details[] = [
                'raw' => $rawLine,
                'uid' => $res['uid'] ?? null,
                'status' => $status,
                'reason' => $res['reason'] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => count($items),
                'live_count' => count($liveList),
                'die_count' => count($dieList),
                'live_uids' => $liveList,
                'die_uids' => $dieList,
                'details' => $details,
            ],
        ]);
    }
}