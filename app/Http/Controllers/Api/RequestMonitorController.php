<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestMonitorController extends Controller
{
    /**
     * Overview stats: total requests, unique users, avg response time
     */
    public function overview(Request $request)
    {
        $hours = $request->get('hours', 24);
        $since = now()->subHours($hours);

        $stats = DB::table('api_request_logs')
            ->where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total_requests,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT ip_address) as unique_ips,
                ROUND(AVG(response_time_ms)) as avg_response_ms,
                MAX(response_time_ms) as max_response_ms,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count
            ')
            ->first();

        $requestsPerHour = DB::table('api_request_logs')
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as hour, COUNT(*) as count')
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00")')
            ->orderBy('hour')
            ->get();

        return response()->json([
            'period_hours' => $hours,
            'stats' => $stats,
            'requests_per_hour' => $requestsPerHour,
        ]);
    }

    /**
     * Top endpoints by request count
     */
    public function topEndpoints(Request $request)
    {
        $hours = $request->get('hours', 24);
        $limit = $request->get('limit', 20);
        $since = now()->subHours($hours);

        $endpoints = DB::table('api_request_logs')
            ->where('created_at', '>=', $since)
            ->selectRaw('
                endpoint,
                method,
                COUNT(*) as request_count,
                COUNT(DISTINCT user_id) as unique_users,
                ROUND(AVG(response_time_ms)) as avg_response_ms,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count
            ')
            ->groupBy('endpoint', 'method')
            ->orderByDesc('request_count')
            ->limit($limit)
            ->get();

        return response()->json(['endpoints' => $endpoints]);
    }

    /**
     * Per-user request stats
     */
    public function userStats(Request $request)
    {
        $hours = $request->get('hours', 24);
        $limit = $request->get('limit', 50);
        $since = now()->subHours($hours);

        $users = DB::table('api_request_logs')
            ->leftJoin('users', 'api_request_logs.user_id', '=', 'users.id')
            ->where('api_request_logs.created_at', '>=', $since)
            ->whereNotNull('api_request_logs.user_id')
            ->selectRaw('
                api_request_logs.user_id,
                users.name as user_name,
                COUNT(*) as request_count,
                COUNT(DISTINCT api_request_logs.ip_address) as unique_ips,
                ROUND(AVG(api_request_logs.response_time_ms)) as avg_response_ms,
                MAX(api_request_logs.created_at) as last_request_at,
                SUM(CASE WHEN api_request_logs.status_code >= 400 THEN 1 ELSE 0 END) as error_count
            ')
            ->groupBy('api_request_logs.user_id', 'users.name')
            ->orderByDesc('request_count')
            ->limit($limit)
            ->get();

        return response()->json(['users' => $users]);
    }

    /**
     * Detailed requests for a specific user
     */
    public function userDetail(Request $request, $userId)
    {
        $hours = $request->get('hours', 24);
        $since = now()->subHours($hours);

        $summary = DB::table('api_request_logs')
            ->leftJoin('users', 'api_request_logs.user_id', '=', 'users.id')
            ->where('api_request_logs.user_id', $userId)
            ->where('api_request_logs.created_at', '>=', $since)
            ->selectRaw('
                api_request_logs.user_id,
                users.name as user_name,
                COUNT(*) as total_requests,
                COUNT(DISTINCT api_request_logs.ip_address) as unique_ips,
                ROUND(AVG(api_request_logs.response_time_ms)) as avg_response_ms,
                SUM(CASE WHEN api_request_logs.status_code >= 400 THEN 1 ELSE 0 END) as error_count
            ')
            ->groupBy('api_request_logs.user_id', 'users.name')
            ->first();

        $topEndpoints = DB::table('api_request_logs')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->selectRaw('endpoint, method, COUNT(*) as count, ROUND(AVG(response_time_ms)) as avg_ms')
            ->groupBy('endpoint', 'method')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        $requestsPerHour = DB::table('api_request_logs')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as hour, COUNT(*) as count')
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00")')
            ->orderBy('hour')
            ->get();

        return response()->json([
            'summary' => $summary,
            'top_endpoints' => $topEndpoints,
            'requests_per_hour' => $requestsPerHour,
        ]);
    }

    /**
     * Live activity - recent requests
     */
    public function liveActivity(Request $request)
    {
        $limit = $request->get('limit', 50);

        $recent = DB::table('api_request_logs')
            ->leftJoin('users', 'api_request_logs.user_id', '=', 'users.id')
            ->select(
                'api_request_logs.id',
                'api_request_logs.user_id',
                'users.name as user_name',
                'api_request_logs.ip_address',
                'api_request_logs.method',
                'api_request_logs.endpoint',
                'api_request_logs.status_code',
                'api_request_logs.response_time_ms',
                'api_request_logs.created_at'
            )
            ->orderByDesc('api_request_logs.id')
            ->limit($limit)
            ->get();

        return response()->json(['requests' => $recent]);
    }

    /**
     * Historical daily stats (from aggregated summary tables)
     */
    public function history(Request $request)
    {
        $days = $request->get('days', 30);
        $since = now()->subDays($days)->toDateString();

        $daily = DB::table('api_request_daily_stats')
            ->where('date', '>=', $since)
            ->selectRaw('
                date,
                SUM(request_count) as total_requests,
                SUM(unique_users) as unique_users,
                SUM(error_count) as error_count,
                ROUND(AVG(avg_response_ms)) as avg_response_ms
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topUsers = DB::table('api_request_daily_user_stats')
            ->leftJoin('users', 'api_request_daily_user_stats.user_id', '=', 'users.id')
            ->where('api_request_daily_user_stats.date', '>=', $since)
            ->whereNotNull('api_request_daily_user_stats.user_id')
            ->selectRaw('
                api_request_daily_user_stats.user_id,
                users.name as user_name,
                SUM(api_request_daily_user_stats.request_count) as total_requests,
                SUM(api_request_daily_user_stats.error_count) as error_count
            ')
            ->groupBy('api_request_daily_user_stats.user_id', 'users.name')
            ->orderByDesc('total_requests')
            ->limit(20)
            ->get();

        $dbSize = DB::table('api_request_logs')->count();

        return response()->json([
            'period_days' => $days,
            'daily' => $daily,
            'top_users' => $topUsers,
            'raw_logs_count' => $dbSize,
        ]);
    }

    /**
     * Cleanup old logs (keep last N days)
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 7);
        $deleted = DB::table('api_request_logs')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        return response()->json([
            'message' => "Deleted $deleted records older than $days days",
            'deleted_count' => $deleted,
        ]);
    }
}
