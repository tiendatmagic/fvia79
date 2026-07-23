<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\UserSessionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSessionController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        $currentSessionId = $request->attributes->get('current_session_id');

        $paginator = UserSessionModel::where('user_id', $userId)
            ->orderByDesc('last_activity_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $items = collect($paginator->items())->map(function (UserSessionModel $session) use ($currentSessionId) {
            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address ?? 'Không xác định',
                'user_agent' => $session->user_agent,
                'device_name' => $session->device_name ?? UserSessionModel::parseDeviceName($session->user_agent),
                'last_activity_at' => $session->last_activity_at ? $session->last_activity_at->toIso8601String() : null,
                'created_at' => $session->created_at ? $session->created_at->toIso8601String() : null,
                'is_current' => ($session->id === $currentSessionId),
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $currentSessionId = $request->attributes->get('current_session_id');

        if ($id === $currentSessionId) {
            return response()->json(['message' => 'Cannot revoke current active session.'], 400);
        }

        $session = UserSessionModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Session not found or already revoked.'], 404);
        }

        $session->delete();

        return response()->json(['message' => 'Session revoked successfully.']);
    }
}
