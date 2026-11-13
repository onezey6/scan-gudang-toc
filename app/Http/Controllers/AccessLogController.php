<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AccessLog::with('user');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('device_browser', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($eventType = $request->input('event_type')) {
            $query->where('event_type', $eventType);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('accessed_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('accessed_at', '<=', $endDate);
        }

        $logs = $query->latest('accessed_at')->latest('id')->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get();

        $today = now()->toDateString();
        $summary = [
            'today_logins' => AccessLog::whereDate('accessed_at', $today)->where('event_type', 'login')->count(),
            'today_logouts' => AccessLog::whereDate('accessed_at', $today)->where('event_type', 'logout')->count(),
            'failed_logins' => AccessLog::where('event_type', 'failed_login')->count(),
            'total_activities' => AccessLog::where('event_type', 'activity')->count(),
        ];

        return view('access-logs.index', compact('logs', 'users', 'summary'));
    }
}
