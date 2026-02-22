<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServicePost;
use App\Models\Categories;
use App\Models\countries;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\point_purchase_requests;
use App\Models\BadgeType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsApiController extends Controller
{
    /**
     * Unified analytics endpoint.
     * GET /api/admin/analytics?days=30&tab=users
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        try {
            $days = (int) $request->input('days', 30);
            $tab  = $request->input('tab', 'users');

            $now   = Carbon::now();
            $start = $now->copy()->subDays($days)->startOfDay();
            $prev  = $start->copy()->subDays($days);

            // --- Overview cards (always returned) ---
            $overview = $this->buildOverview($start, $prev, $days);

            // --- Tab-specific data ---
            $tabData = match ($tab) {
                'users'  => $this->usersTab($start, $prev, $days),
                'points' => $this->pointsTab($start, $prev, $days),
                'posts'  => $this->postsTab($start, $prev, $days),
                default  => [],
            };

            return response()->json([
                'overview' => $overview,
                'tab'      => $tab,
                'days'     => $days,
                'data'     => $tabData,
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics Error: ' . $e->getMessage());

            return response()->json([
                'error'   => 'Failed to load analytics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* ------------------------------------------------
     *  Overview — 4 stat cards with % change
     * ------------------------------------------------ */
    private function buildOverview(Carbon $start, Carbon $prev, int $days): array
    {
        $now = Carbon::now();

        // New users
        $newUsers     = User::whereBetween('created_at', [$start, $now])->count();
        $prevNewUsers = User::whereBetween('created_at', [$prev, $start])->count();

        // New posts
        $newPosts     = ServicePost::whereBetween('created_at', [$start, $now])->count();
        $prevNewPosts = ServicePost::whereBetween('created_at', [$prev, $start])->count();

        // Points volume (all transactions)
        $pointsVol     = point_transactions::whereBetween('created_at', [$start, $now])->sum('point');
        $prevPointsVol = point_transactions::whereBetween('created_at', [$prev, $start])->sum('point');

        // Engagement rate = users who created posts / total active users
        $totalActive  = User::where('is_active', 'active')->count();
        $usersPosted  = ServicePost::whereBetween('created_at', [$start, $now])
                            ->distinct('user_id')->count('user_id');
        $engagementRate = $totalActive > 0 ? round(($usersPosted / $totalActive) * 100, 1) : 0;

        $prevUsersPosted = ServicePost::whereBetween('created_at', [$prev, $start])
                               ->distinct('user_id')->count('user_id');
        $prevEngagement  = $totalActive > 0 ? round(($prevUsersPosted / $totalActive) * 100, 1) : 0;

        return [
            [
                'label'   => 'New Users',
                'value'   => $newUsers,
                'change'  => $this->pctChange($newUsers, $prevNewUsers),
                'icon'    => 'fas fa-user-plus',
                'color'   => 'primary',
            ],
            [
                'label'   => 'New Posts',
                'value'   => $newPosts,
                'change'  => $this->pctChange($newPosts, $prevNewPosts),
                'icon'    => 'fas fa-file-alt',
                'color'   => 'success',
            ],
            [
                'label'   => 'Points Volume',
                'value'   => $pointsVol,
                'change'  => $this->pctChange($pointsVol, $prevPointsVol),
                'icon'    => 'fas fa-coins',
                'color'   => 'warning',
            ],
            [
                'label'   => 'Engagement Rate',
                'value'   => $engagementRate . '%',
                'change'  => round($engagementRate - $prevEngagement, 1),
                'icon'    => 'fas fa-chart-line',
                'color'   => 'info',
            ],
        ];
    }

    /* ------------------------------------------------
     *  Users tab
     * ------------------------------------------------ */
    private function usersTab(Carbon $start, Carbon $prev, int $days): array
    {
        $now = Carbon::now();

        // Line chart: registrations over time
        $registrations = User::whereBetween('created_at', [$start, $now])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Pie chart: user status
        $statusCounts = User::select('is_active', DB::raw('COUNT(*) as count'))
            ->groupBy('is_active')
            ->pluck('count', 'is_active');

        // Bar chart: users by country (top 10)
        $byCountry = User::select('country_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country_id')
            ->groupBy('country_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $countryIds = $byCountry->pluck('country_id')->toArray();
        $countryNames = countries::whereIn('id', $countryIds)->pluck('name', 'id');

        $countryLabels = [];
        $countryCounts = [];
        foreach ($byCountry as $row) {
            $countryLabels[] = $countryNames[$row->country_id] ?? 'Unknown';
            $countryCounts[] = $row->count;
        }

        // Engagement metrics
        $totalUsers     = User::count();
        $activeUsers    = User::where('is_active', 'active')->count();
        $usersWithPosts = User::whereHas('servicePosts')->count();

        return [
            'registrations_chart' => [
                'labels'   => $registrations->pluck('date')->toArray(),
                'datasets' => [[
                    'label'           => 'Registrations',
                    'data'            => $registrations->pluck('count')->toArray(),
                    'borderColor'     => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.1)',
                    'fill'            => true,
                    'tension'         => 0.3,
                ]],
            ],
            'status_chart' => [
                'labels'   => array_map('ucfirst', array_keys($statusCounts->toArray())),
                'datasets' => [[
                    'data'            => array_values($statusCounts->toArray()),
                    'backgroundColor' => ['#22c55e', '#ef4444', '#94a3b8'],
                ]],
            ],
            'country_chart' => [
                'labels'   => $countryLabels,
                'datasets' => [[
                    'label'           => 'Users',
                    'data'            => $countryCounts,
                    'backgroundColor' => '#3b82f6',
                ]],
            ],
            'metrics' => [
                'total_users'      => $totalUsers,
                'active_users'     => $activeUsers,
                'users_with_posts' => $usersWithPosts,
                'engagement_pct'   => $totalUsers > 0 ? round(($usersWithPosts / $totalUsers) * 100, 1) : 0,
            ],
        ];
    }

    /* ------------------------------------------------
     *  Points tab
     * ------------------------------------------------ */
    private function pointsTab(Carbon $start, Carbon $prev, int $days): array
    {
        $now = Carbon::now();

        // Line chart: inflow vs outflow over time
        $inflow = point_transactions::whereBetween('created_at', [$start, $now])
            ->where('type', 'added')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(point) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $outflow = point_transactions::whereBetween('created_at', [$start, $now])
            ->where('type', 'used')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(point) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Merge dates
        $allDates = collect(array_merge($inflow->keys()->toArray(), $outflow->keys()->toArray()))
            ->unique()->sort()->values()->toArray();

        // Pie chart: transaction types
        $typeCounts = point_transactions::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');

        // Line chart: revenue trend (approved purchase requests)
        $revenue = point_purchase_requests::where('status', 'approved')
            ->whereBetween('created_at', [$start, $now])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(points_requested) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Points health metrics
        $totalInSystem   = palservice_points::sum('point');
        $pendingRequests = point_purchase_requests::where('status', 'pending')->count();
        $totalTransactions = point_transactions::count();

        $periodInflow  = point_transactions::whereBetween('created_at', [$start, $now])->where('type', 'added')->sum('point');
        $periodOutflow = point_transactions::whereBetween('created_at', [$start, $now])->where('type', 'used')->sum('point');

        return [
            'flow_chart' => [
                'labels'   => $allDates,
                'datasets' => [
                    [
                        'label'       => 'Inflow',
                        'data'        => array_map(fn($d) => $inflow[$d] ?? 0, $allDates),
                        'borderColor' => '#22c55e',
                        'backgroundColor' => 'rgba(34,197,94,0.1)',
                        'fill'        => true,
                        'tension'     => 0.3,
                    ],
                    [
                        'label'       => 'Outflow',
                        'data'        => array_map(fn($d) => $outflow[$d] ?? 0, $allDates),
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239,68,68,0.1)',
                        'fill'        => true,
                        'tension'     => 0.3,
                    ],
                ],
            ],
            'type_chart' => [
                'labels'   => array_map('ucfirst', array_keys($typeCounts->toArray())),
                'datasets' => [[
                    'data'            => array_values($typeCounts->toArray()),
                    'backgroundColor' => ['#22c55e', '#ef4444', '#f97316', '#3b82f6', '#8b5cf6'],
                ]],
            ],
            'revenue_chart' => [
                'labels'   => $revenue->pluck('date')->toArray(),
                'datasets' => [[
                    'label'       => 'Revenue (Points)',
                    'data'        => $revenue->pluck('total')->toArray(),
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249,115,22,0.1)',
                    'fill'        => true,
                    'tension'     => 0.3,
                ]],
            ],
            'metrics' => [
                'total_in_system'    => $totalInSystem,
                'pending_requests'   => $pendingRequests,
                'total_transactions' => $totalTransactions,
                'period_inflow'      => $periodInflow,
                'period_outflow'     => $periodOutflow,
                'net_flow'           => $periodInflow - $periodOutflow,
            ],
        ];
    }

    /* ------------------------------------------------
     *  Posts tab
     * ------------------------------------------------ */
    private function postsTab(Carbon $start, Carbon $prev, int $days): array
    {
        $now = Carbon::now();

        // Line chart: post creation over time
        $creations = ServicePost::whereBetween('created_at', [$start, $now])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Pie chart: post type (offer/request based on 'type' field)
        $typeCounts = ServicePost::select('type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('type')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Bar chart: posts by category (all categories)
        $byCategory = ServicePost::select('categories_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('categories_id')
            ->groupBy('categories_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $catIds = $byCategory->pluck('categories_id')->toArray();
        $catNames = Categories::whereIn('id', $catIds)->get()->mapWithKeys(function ($cat) {
            $name = is_array($cat->name) ? ($cat->name['en'] ?? $cat->name['ar'] ?? 'Unknown') : $cat->name;
            return [$cat->id => $name];
        });

        $categoryLabels = [];
        $categoryCounts = [];
        foreach ($byCategory as $row) {
            $categoryLabels[] = $catNames[$row->categories_id] ?? 'Unknown';
            $categoryCounts[] = $row->count;
        }

        // Metrics: avg views/favorites/comments
        $avgViews     = ServicePost::avg('view_count') ?? 0;
        $avgFavorites = ServicePost::avg('favorites_count') ?? 0;
        $totalPosts   = ServicePost::count();
        $publishedPosts = ServicePost::where('state', 'published')->count();

        return [
            'creation_chart' => [
                'labels'   => $creations->pluck('date')->toArray(),
                'datasets' => [[
                    'label'           => 'Posts Created',
                    'data'            => $creations->pluck('count')->toArray(),
                    'borderColor'     => '#8b5cf6',
                    'backgroundColor' => 'rgba(139,92,246,0.1)',
                    'fill'            => true,
                    'tension'         => 0.3,
                ]],
            ],
            'type_chart' => [
                'labels'   => array_map('ucfirst', array_keys($typeCounts->toArray())),
                'datasets' => [[
                    'data'            => array_values($typeCounts->toArray()),
                    'backgroundColor' => ['#3b82f6', '#f97316', '#22c55e', '#ef4444'],
                ]],
            ],
            'category_chart' => [
                'labels'   => $categoryLabels,
                'datasets' => [[
                    'label'           => 'Posts',
                    'data'            => $categoryCounts,
                    'backgroundColor' => ['#3b82f6', '#22c55e', '#f97316', '#ef4444', '#8b5cf6', '#06b6d4', '#eab308', '#ec4899', '#14b8a6', '#6366f1'],
                ]],
            ],
            'metrics' => [
                'total_posts'     => $totalPosts,
                'published_posts' => $publishedPosts,
                'avg_views'       => round($avgViews, 1),
                'avg_favorites'   => round($avgFavorites, 1),
            ],
        ];
    }

    /* ------------------------------------------------
     *  Helper: calculate period-over-period % change
     * ------------------------------------------------ */
    private function pctChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
