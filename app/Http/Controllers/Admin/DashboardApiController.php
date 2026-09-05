<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServicePost;
use App\Models\point_transactions;
use App\Models\Report;
use App\Models\point_purchase_requests;
use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    /**
     * Get dashboard statistics and data
     * Separate from mobile API to avoid conflicts
     */
    public function index(): JsonResponse
    {
        try {
            $data = [
                'stats' => $this->getStats(),
                'recentPosts' => $this->getRecentPosts(),
                'recentUsers' => $this->getRecentUsers(),
                'normalPosts' => $this->getPostsByBadge('normal'),
                'goldenPosts' => $this->getPostsByBadge('golden'),
                'diamondPosts' => $this->getPostsByBadge('diamond'),
                'offerPosts' => $this->getPostsByType('offer'),
                'requestPosts' => $this->getPostsByType('request'),
                'postsByMonth' => $this->getPostsByMonth(),
                'usersByMonth' => $this->getUsersByMonth(),
                'postsByCategory' => $this->getPostsByCategory(),
                'pointTransactions' => $this->getPointTransactionsByMonth(),
                'topUsers' => $this->getTopUsers(),
                'postsByState' => $this->getPostsByState(),
                'recentReports' => $this->getRecentReports(),
                'growthStats' => $this->getGrowthStats(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Dashboard API Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load dashboard data',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get main statistics
     */
    private function getStats(): array
    {
        return [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', 'active')->count(),
            'bannedUsers' => User::where('is_active', 'banned')->count(),
            'totalReports' => Report::count(),
            'totalPosts' => ServicePost::count(),
            'publishedPosts' => ServicePost::where('state', 'published')->count(),
            'notPublishedPosts' => ServicePost::where('state', 'not published')->count(),
            'rejectedPosts' => ServicePost::where('state', 'rejected')->count(),
            'totalPoints' => point_transactions::where('type', 'earned')->sum('point') ?? 0,
            'pointsUsed' => point_transactions::where('type', 'used')->sum('point') ?? 0,
            'pendingPurchaseRequests' => point_purchase_requests::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get recent service posts
     */
    private function getRecentPosts(): array
    {
        return ServicePost::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'user_name' => $post->user->user_name ?? 'N/A',
                    'state' => $post->state,
                    'created_at' => $post->created_at->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent users
     */
    private function getRecentUsers(): array
    {
        return User::latest()
            ->take(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Get posts count by badge type
     */
    private function getPostsByBadge(string $badge): int
    {
        return ServicePost::whereHas('badgeType', function ($query) use ($badge) {
            $query->where('slug', $badge);
        })->count();
    }

    /**
     * Get posts count by post type
     */
    private function getPostsByType(string $type): int
    {
        // Map English to Arabic type values
        $typeMap = [
            'offer' => 'عرض',
            'request' => 'طلب'
        ];

        $arabicType = $typeMap[$type] ?? $type;
        return ServicePost::where('type', $arabicType)->count();
    }

    /**
     * Get posts grouped by month (last 12 months)
     */
    private function getPostsByMonth(): array
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $count = ServicePost::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    /**
     * Get users grouped by month (last 12 months)
     */
    private function getUsersByMonth(): array
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    /**
     * Get posts grouped by category
     */
    private function getPostsByCategory(): array
    {
        $categories = Categories::withCount('servicePosts')
            ->orderByDesc('service_posts_count')
            ->take(10)
            ->get();

        $labels = [];
        $data = [];

        foreach ($categories as $category) {
            // Get category name (it's already an array due to model casting)
            $name = $category->name;
            if (is_array($name)) {
                $name = $name['en'] ?? $name['ar'] ?? $name[array_key_first($name)] ?? 'Unknown';
            }

            $labels[] = $name;
            $data[] = $category->service_posts_count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get point transactions grouped by month (last 12 months)
     */
    private function getPointTransactionsByMonth(): array
    {
        $months = [];
        $counts = [];
        $points = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $transactions = point_transactions::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->selectRaw('COUNT(*) as count, SUM(ABS(point)) as total')
                ->first();

            $counts[] = $transactions->count ?? 0;
            $points[] = $transactions->total ?? 0;
        }

        return [
            'labels' => $months,
            'counts' => $counts,
            'points' => $points,
        ];
    }

    /**
     * Get top users by post count
     */
    private function getTopUsers(): array
    {
        return User::withCount('servicePosts')
            ->orderByDesc('service_posts_count')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'posts_count' => $user->service_posts_count,
                    'is_active' => $user->is_active,
                ];
            })
            ->toArray();
    }

    /**
     * Get posts by state distribution
     */
    private function getPostsByState(): array
    {
        $states = ServicePost::select('state', DB::raw('count(*) as count'))
            ->groupBy('state')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'published' => '#10B981',
            'not published' => '#F59E0B',
            'rejected' => '#EF4444',
            'archive' => '#6B7280'
        ];
        $bgColors = [];

        foreach ($states as $state) {
            $labels[] = ucfirst($state->state);
            $data[] = $state->count;
            $bgColors[] = $colors[$state->state] ?? '#6366F1';
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => $bgColors
        ];
    }

    /**
     * Get recent reports
     */
    private function getRecentReports(): array
    {
        return Report::with(['reporter', 'reportable'])
            ->whereHasMorph('reportable', [User::class, ServicePost::class])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($report) {
                $reportedItem = 'N/A';
                if ($report->reportable) {
                    if ($report->reportable instanceof ServicePost) {
                        $reportedItem = $report->reportable->title ?? 'Service Post';
                    } elseif ($report->reportable instanceof User) {
                        $reportedItem = $report->reportable->user_name ?? 'User';
                    } else {
                        $reportedItem = class_basename($report->reportable_type);
                    }
                }

                return [
                    'id' => $report->id,
                    'reporter_name' => $report->reporter->user_name ?? 'N/A',
                    'reported_item' => $reportedItem,
                    'reason' => $report->reason,
                    'created_at' => $report->created_at->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Get growth statistics (comparing with previous period)
     */
    private function getGrowthStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // This month
        $usersThisMonth = User::where('created_at', '>=', $startOfMonth)->count();
        $postsThisMonth = ServicePost::where('created_at', '>=', $startOfMonth)->count();

        // Last month
        $usersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $postsLastMonth = ServicePost::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        // Calculate growth percentage
        $userGrowth = $usersLastMonth > 0 ? (($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100 : 0;
        $postGrowth = $postsLastMonth > 0 ? (($postsThisMonth - $postsLastMonth) / $postsLastMonth) * 100 : 0;

        return [
            'users' => [
                'current' => $usersThisMonth,
                'previous' => $usersLastMonth,
                'growth' => round($userGrowth, 1)
            ],
            'posts' => [
                'current' => $postsThisMonth,
                'previous' => $postsLastMonth,
                'growth' => round($postGrowth, 1)
            ]
        ];
    }
}
