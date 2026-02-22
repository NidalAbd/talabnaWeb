<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadgeType;
use App\Models\Categories;
use App\Models\Report;
use App\Models\point_purchase_requests;
use App\Models\point_transactions;
use App\Models\ServicePost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatisticsApiController extends Controller
{
    /**
     * System health snapshot — the "is everything OK right now?" page.
     */
    public function index(): JsonResponse
    {
        try {
            $today = Carbon::today();

            // ── Today's Pulse ────────────────────────────
            $todaysPulse = [
                [
                    'label' => 'New Users Today',
                    'value' => User::whereDate('created_at', $today)->count(),
                    'icon'  => 'fas fa-user-plus',
                    'color' => 'primary',
                ],
                [
                    'label' => 'New Posts Today',
                    'value' => ServicePost::whereDate('created_at', $today)->count(),
                    'icon'  => 'fas fa-file-alt',
                    'color' => 'success',
                ],
                [
                    'label' => 'Transactions Today',
                    'value' => point_transactions::whereDate('created_at', $today)->count(),
                    'icon'  => 'fas fa-exchange-alt',
                    'color' => 'warning',
                ],
                [
                    'label' => 'Reports Today',
                    'value' => Report::whereDate('created_at', $today)->count(),
                    'icon'  => 'fas fa-flag',
                    'color' => 'danger',
                ],
            ];

            // ── Action Required ──────────────────────────
            $actionRequired = [
                [
                    'label' => 'Pending Purchase Requests',
                    'value' => point_purchase_requests::where('status', 'pending')->count(),
                    'icon'  => 'fas fa-shopping-cart',
                    'color' => 'warning',
                    'link'  => '/admin/purchase-requests',
                ],
                [
                    'label' => 'Unresolved Reports',
                    'value' => Report::count(),
                    'icon'  => 'fas fa-exclamation-triangle',
                    'color' => 'danger',
                    'link'  => '/admin/reports',
                ],
                [
                    'label' => 'Pending Posts',
                    'value' => ServicePost::where('state', 'not published')->count(),
                    'icon'  => 'fas fa-clock',
                    'color' => 'info',
                    'link'  => '/admin/posts',
                ],
            ];

            // ── Distribution Charts ─────────────────────

            // 1. User status pie
            $userStatusRaw = User::select('is_active', DB::raw('COUNT(*) as count'))
                ->groupBy('is_active')
                ->pluck('count', 'is_active');

            $userStatusChart = [
                'labels'   => array_map('ucfirst', array_keys($userStatusRaw->toArray())),
                'datasets' => [[
                    'data'            => array_values($userStatusRaw->toArray()),
                    'backgroundColor' => ['#22c55e', '#ef4444', '#94a3b8'],
                ]],
            ];

            // 2. Post state pie
            $postStateRaw = ServicePost::select('state', DB::raw('COUNT(*) as count'))
                ->groupBy('state')
                ->pluck('count', 'state');

            $stateLabels = [
                'published'     => 'Published',
                'not published' => 'Pending',
                'rejected'      => 'Rejected',
                'archive'       => 'Archived',
            ];

            $postStateChart = [
                'labels'   => array_map(fn($s) => $stateLabels[$s] ?? ucfirst($s), array_keys($postStateRaw->toArray())),
                'datasets' => [[
                    'data'            => array_values($postStateRaw->toArray()),
                    'backgroundColor' => ['#22c55e', '#f97316', '#ef4444', '#94a3b8'],
                ]],
            ];

            // 3. Posts by category bar
            $byCategory = ServicePost::select('categories_id', DB::raw('COUNT(*) as count'))
                ->whereNotNull('categories_id')
                ->groupBy('categories_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $catIds   = $byCategory->pluck('categories_id')->toArray();
            $catNames = Categories::whereIn('id', $catIds)->get()->mapWithKeys(function ($cat) {
                $name = is_array($cat->name) ? ($cat->name['en'] ?? $cat->name['ar'] ?? 'Unknown') : $cat->name;
                return [$cat->id => $name];
            });

            $categoryChart = [
                'labels'   => $byCategory->map(fn($r) => $catNames[$r->categories_id] ?? 'Unknown')->toArray(),
                'datasets' => [[
                    'label'           => 'Posts',
                    'data'            => $byCategory->pluck('count')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#22c55e', '#f97316', '#ef4444', '#8b5cf6', '#06b6d4', '#eab308', '#ec4899', '#14b8a6', '#6366f1'],
                ]],
            ];

            // 4. Badge distribution pie
            $badgeTypes = BadgeType::orderBy('priority')->get();
            $badgeChart = ['labels' => [], 'datasets' => [['data' => [], 'backgroundColor' => []]]];

            foreach ($badgeTypes as $badge) {
                $count = ServicePost::where('badge_type_id', $badge->id)->count();
                $name  = is_array($badge->name) ? ($badge->name['en'] ?? $badge->name['ar'] ?? $badge->slug) : $badge->name;
                $badgeChart['labels'][] = $name;
                $badgeChart['datasets'][0]['data'][] = $count;
                $badgeChart['datasets'][0]['backgroundColor'][] = $badge->color ?? '#94a3b8';
            }

            // ── System Health Rates ─────────────────────
            $totalUsers     = User::count();
            $activeUsers    = User::where('is_active', 'active')->count();
            $totalPosts     = ServicePost::count();
            $publishedPosts = ServicePost::where('state', 'published')->count();
            $totalPurchases = point_purchase_requests::count();
            $approvedPurchases = point_purchase_requests::where('status', 'approved')->count();

            $systemRates = [
                [
                    'label' => 'User Engagement',
                    'value' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
                    'color' => 'primary',
                ],
                [
                    'label' => 'Post Approval Rate',
                    'value' => $totalPosts > 0 ? round(($publishedPosts / $totalPosts) * 100, 1) : 0,
                    'color' => 'success',
                ],
                [
                    'label' => 'Purchase Approval Rate',
                    'value' => $totalPurchases > 0 ? round(($approvedPurchases / $totalPurchases) * 100, 1) : 0,
                    'color' => 'warning',
                ],
            ];

            return response()->json([
                'todays_pulse'    => $todaysPulse,
                'action_required' => $actionRequired,
                'charts'          => [
                    'user_status'  => $userStatusChart,
                    'post_state'   => $postStateChart,
                    'by_category'  => $categoryChart,
                    'badge_dist'   => $badgeChart,
                ],
                'system_rates'    => $systemRates,
            ]);
        } catch (\Exception $e) {
            \Log::error('Statistics Error: ' . $e->getMessage());

            return response()->json([
                'error'   => 'Failed to load statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
