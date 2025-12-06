<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoKeyword;
use App\Models\SeoPerformance;
use App\Models\SeoLog;
use App\Models\SeoIssue;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoApiController extends Controller
{
    protected GoogleSearchConsoleService $gscService;

    public function __construct(GoogleSearchConsoleService $gscService)
    {
        $this->gscService = $gscService;
    }

    public function dashboard(): JsonResponse
    {
        $overview = $this->gscService->getPerformanceOverview(28);
        $performanceByType = $this->gscService->getPerformanceByPageType(28);
        $issueStats = $this->gscService->getIssueStats();
        $recentLogs = $this->gscService->getSyncLogs(5);

        return response()->json([
            'overview' => $overview,
            'performance_by_type' => $performanceByType,
            'issue_stats' => $issueStats,
            'recent_logs' => $recentLogs,
            'gsc_enabled' => $this->gscService->isEnabled(),
        ]);
    }

    public function getStats(): JsonResponse
    {
        $overview = $this->gscService->getPerformanceOverview(28);

        $stats = [
            [
                'label' => 'Total Clicks',
                'value' => number_format($overview['current']['clicks']),
                'icon' => 'fas fa-mouse-pointer',
                'color' => 'primary',
                'change' => $overview['changes']['clicks'],
            ],
            [
                'label' => 'Impressions',
                'value' => number_format($overview['current']['impressions']),
                'icon' => 'fas fa-eye',
                'color' => 'info',
                'change' => $overview['changes']['impressions'],
            ],
            [
                'label' => 'Avg Position',
                'value' => $overview['current']['avg_position'],
                'icon' => 'fas fa-chart-line',
                'color' => 'success',
                'change' => $overview['changes']['position'],
            ],
            [
                'label' => 'CTR',
                'value' => $overview['current']['avg_ctr'] . '%',
                'icon' => 'fas fa-percentage',
                'color' => 'warning',
                'change' => $overview['changes']['ctr'],
            ],
        ];

        $issueStats = $this->gscService->getIssueStats();
        $stats[] = [
            'label' => 'Open Issues',
            'value' => $issueStats['open'],
            'icon' => 'fas fa-exclamation-triangle',
            'color' => $issueStats['critical'] > 0 ? 'danger' : 'secondary',
            'critical' => $issueStats['critical'],
        ];

        return response()->json([
            'stats' => $stats,
            'gsc_enabled' => $this->gscService->isEnabled(),
        ]);
    }

    public function keywords(Request $request): JsonResponse
    {
        $days = $request->get('days', 28);
        $country = $request->get('country');
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'total_clicks');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 50);

        $startDate = now()->subDays($days)->format('Y-m-d');

        $query = SeoKeyword::where('date', '>=', $startDate)
            ->select('keyword')
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->selectRaw('MAX(trend) as trend')
            ->selectRaw('MAX(trend_change) as trend_change')
            ->selectRaw('MAX(top_page) as top_page')
            ->groupBy('keyword');

        if ($country) {
            $query->where('country', strtoupper($country));
        }

        if ($search) {
            $query->where('keyword', 'like', "%{$search}%");
        }

        $keywords = $query->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return response()->json($keywords);
    }

    public function pages(Request $request): JsonResponse
    {
        $days = $request->get('days', 28);
        $pageType = $request->get('page_type');
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'total_clicks');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 50);

        $startDate = now()->subDays($days)->format('Y-m-d');

        $query = SeoPerformance::where('date', '>=', $startDate)
            ->select('url', 'page_type')
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->selectRaw('MAX(trend) as trend')
            ->selectRaw('MAX(trend_change) as trend_change')
            ->groupBy('url', 'page_type');

        if ($pageType) {
            $query->where('page_type', $pageType);
        }

        if ($search) {
            $query->where('url', 'like', "%{$search}%");
        }

        $pages = $query->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return response()->json($pages);
    }

    public function pageDetails(Request $request): JsonResponse
    {
        $url = $request->get('url');
        $days = $request->get('days', 28);

        if (!$url) {
            return response()->json(['error' => 'URL is required'], 400);
        }

        $startDate = now()->subDays($days)->format('Y-m-d');

        // Daily stats
        $dailyStats = SeoPerformance::where('url', $url)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get(['date', 'clicks', 'impressions', 'position', 'ctr']);

        // Latest performance with top keywords
        $latestPerformance = SeoPerformance::where('url', $url)
            ->orderByDesc('date')
            ->first();

        // Summary
        $summary = SeoPerformance::where('url', $url)
            ->where('date', '>=', $startDate)
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->first();

        return response()->json([
            'url' => $url,
            'page_type' => $latestPerformance?->page_type ?? 'other',
            'summary' => $summary,
            'daily_stats' => $dailyStats,
            'top_keywords' => $latestPerformance?->top_keywords ?? [],
            'trend' => $latestPerformance?->trend ?? 'stable',
            'trend_change' => $latestPerformance?->trend_change ?? 0,
        ]);
    }

    public function dailyStats(Request $request): JsonResponse
    {
        $days = $request->get('days', 28);
        $stats = $this->gscService->getDailyStats($days);

        return response()->json($stats);
    }

    public function performanceByType(Request $request): JsonResponse
    {
        $days = $request->get('days', 28);
        $data = $this->gscService->getPerformanceByPageType($days);

        return response()->json($data);
    }

    public function issues(Request $request): JsonResponse
    {
        $status = $request->get('status');
        $severity = $request->get('severity');
        $type = $request->get('type');
        $perPage = $request->get('per_page', 50);

        $query = SeoIssue::query();

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->unresolved();
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($type) {
            $query->where('issue_type', $type);
        }

        $issues = $query->orderByRaw("FIELD(severity, 'critical', 'warning', 'info')")
            ->orderByDesc('detected_at')
            ->paginate($perPage);

        return response()->json($issues);
    }

    public function updateIssue(Request $request, $id): JsonResponse
    {
        $issue = SeoIssue::findOrFail($id);

        $action = $request->get('action');
        $notes = $request->get('notes');

        switch ($action) {
            case 'resolve':
                $issue->markAsResolved($notes);
                break;
            case 'ignore':
                $issue->markAsIgnored($notes);
                break;
            case 'in_progress':
                $issue->markAsInProgress();
                break;
            case 'reopen':
                $issue->update(['status' => 'open', 'resolved_at' => null]);
                break;
        }

        return response()->json([
            'message' => 'Issue updated successfully',
            'issue' => $issue->fresh(),
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 50);
        $action = $request->get('action');
        $status = $request->get('status');

        $query = SeoLog::orderByDesc('created_at');

        if ($action) {
            $query->where('action', $action);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $logs = $query->paginate($limit);

        return response()->json($logs);
    }

    public function sync(Request $request): JsonResponse
    {
        $days = $request->get('days', 28);

        if (!$this->gscService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Google Search Console integration is not enabled. Please configure GSC_ENABLED=true and provide service account credentials.',
            ], 400);
        }

        $result = $this->gscService->syncSearchAnalytics($days);

        return response()->json($result);
    }

    public function testConnection(): JsonResponse
    {
        $result = $this->gscService->testConnection();

        return response()->json($result);
    }

    public function cleanup(): JsonResponse
    {
        $result = $this->gscService->cleanupOldData();

        return response()->json([
            'success' => true,
            'message' => 'Old data cleaned up successfully',
            'deleted' => $result,
        ]);
    }

    public function getConfig(): JsonResponse
    {
        return response()->json([
            'gsc_enabled' => $this->gscService->isEnabled(),
            'site_url' => config('seo.google_search_console.site_url'),
            'data_retention' => config('seo.data_retention'),
            'sync_settings' => config('seo.sync'),
            'alert_thresholds' => config('seo.alerts'),
            'countries' => config('seo.countries'),
            'page_types' => config('seo.page_types'),
        ]);
    }

    public function getCountries(): JsonResponse
    {
        $days = request()->get('days', 28);
        $startDate = now()->subDays($days)->format('Y-m-d');

        $countries = SeoKeyword::where('date', '>=', $startDate)
            ->select('country')
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->groupBy('country')
            ->orderByDesc('total_clicks')
            ->get();

        $countryNames = config('seo.countries', []);

        $result = $countries->map(function ($item) use ($countryNames) {
            return [
                'code' => $item->country,
                'name' => $countryNames[$item->country] ?? $item->country,
                'clicks' => (int) $item->total_clicks,
                'impressions' => (int) $item->total_impressions,
            ];
        });

        return response()->json($result);
    }

    public function getTrendingKeywords(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $direction = $request->get('direction', 'up');

        $keywords = SeoKeyword::where('trend', $direction)
            ->where('is_tracked', true)
            ->orderByDesc('trend_change')
            ->limit($limit)
            ->get();

        return response()->json($keywords);
    }

    public function getDecliningingPages(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $days = $request->get('days', 28);

        $startDate = now()->subDays($days)->format('Y-m-d');

        $pages = SeoPerformance::where('date', '>=', $startDate)
            ->where('trend', 'down')
            ->select('url', 'page_type', 'trend_change')
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('AVG(position) as avg_position')
            ->groupBy('url', 'page_type', 'trend_change')
            ->orderByDesc('trend_change')
            ->limit($limit)
            ->get();

        return response()->json($pages);
    }
}
