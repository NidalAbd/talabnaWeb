<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\User;
use App\Models\ServicePost;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\point_purchase_requests;
use App\Models\Report;
use App\Models\Sub_categories;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        // Return Vue dashboard view
        // All data will be loaded via API endpoint: /admin/api/dashboard
        return view('admin.dashboard');
    }

    /**
     * Calculate points used statistics for different time periods
     *
     * @return array
     */
    private function getPointsUsedStatistics()
    {
        // Set time periods
        $today = Carbon::today();
        $weekStart = Carbon::now()->subDays(7);
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();

        // Initialize stats array
        $stats = [
            'today' => 0,
            'week' => 0,
            'month' => 0,
            'year' => 0,
            'lifetime' => 0
        ];

        // Calculate points used from badge purchases for each time period
        $badgePointsData = [
            'today' => $this->getBadgePointsUsed($today),
            'week' => $this->getBadgePointsUsed($weekStart),
            'month' => $this->getBadgePointsUsed($monthStart),
            'year' => $this->getBadgePointsUsed($yearStart),
            'lifetime' => $this->getBadgePointsUsed(null)
        ];

        // Calculate points used from transactions for each time period
        $transactionPointsData = [
            'today' => $this->getTransactionPointsUsed($today),
            'week' => $this->getTransactionPointsUsed($weekStart),
            'month' => $this->getTransactionPointsUsed($monthStart),
            'year' => $this->getTransactionPointsUsed($yearStart),
            'lifetime' => $this->getTransactionPointsUsed(null)
        ];

        // Combine both sources for each time period
        foreach ($stats as $period => &$value) {
            $value = $badgePointsData[$period] + $transactionPointsData[$period];
        }

        return $stats;
    }

    /**
     * Get points used from badge purchases within a time period
     *
     * @param Carbon|null $startDate
     * @return int
     */
    private function getBadgePointsUsed($startDate = null)
    {
        $query = ServicePost::where('have_badge', '!=', 'عادي')
            ->where('badge_duration', '>', 0);

        // Apply date filter if specified
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        // Calculate points based on badge type and duration
        return $query->sum(DB::raw('CASE
            WHEN have_badge = "ذهبي" THEN badge_duration * 2
            WHEN have_badge = "ماسي" THEN badge_duration * 10
            ELSE 0
          END'));
    }

    /**
     * Get points used from transactions within a time period
     *
     * @param Carbon|null $startDate
     * @return int
     */
    private function getTransactionPointsUsed($startDate = null)
    {
        $query = point_transactions::where('type', 'used')
            ->where('point', '>', 0);

        // Apply date filter if specified
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        return $query->sum('point');
    }

    /**
     * Get posts count by month for the last 12 months
     *
     * @return array
     */
    private function getPostsByMonth()
    {
        $months = [];
        $counts = [];

        $postsData = DB::table('service_posts')
            ->select(DB::raw('COUNT(*) as count'), DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'))
            ->whereRaw('created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        foreach ($postsData as $data) {
            $monthName = date('F', mktime(0, 0, 0, $data->month, 10));
            $months[] = $monthName . ' ' . $data->year;
            $counts[] = $data->count;
        }

        return [
            'labels' => $months,
            'data' => $counts
        ];
    }

    /**
     * Get users count by month for the last 12 months
     *
     * @return array
     */
    private function getUsersByMonth()
    {
        $months = [];
        $counts = [];

        $usersData = DB::table('users')
            ->select(DB::raw('COUNT(*) as count'), DB::raw('MONTH(created_at) as month'), DB::raw('YEAR(created_at) as year'))
            ->whereRaw('created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        foreach ($usersData as $data) {
            $monthName = date('F', mktime(0, 0, 0, $data->month, 10));
            $months[] = $monthName . ' ' . $data->year;
            $counts[] = $data->count;
        }

        return [
            'labels' => $months,
            'data' => $counts
        ];
    }

    /**
     * Get posts count by category
     *
     * @return array
     */
    private function getPostsByCategory()
    {
        $categories = [];
        $counts = [];

        $categoryData = DB::table('service_posts')
            ->join('categories', 'service_posts.categories_id', '=', 'categories.id')
            ->select(DB::raw('COUNT(service_posts.id) as count'), 'categories.id', 'categories.name')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        foreach ($categoryData as $data) {
            $name = is_string($data->name) ? json_decode($data->name, true) : $data->name;
            $categoryName = $name['en'] ?? ($name['ar'] ?? "Category {$data->id}");
            $categories[] = $categoryName;
            $counts[] = $data->count;
        }

        return [
            'labels' => $categories,
            'data' => $counts
        ];
    }

    /**
     * Get point transactions by month for the last 12 months
     *
     * @return array
     */
    private function getPointTransactionsByMonth()
    {
        $months = [];
        $counts = [];
        $points = [];

        $transactionData = DB::table('point_transactions')
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(point) as total_points'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year')
            )
            ->whereRaw('created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        foreach ($transactionData as $data) {
            $monthName = date('F', mktime(0, 0, 0, $data->month, 10));
            $months[] = $monthName . ' ' . $data->year;
            $counts[] = $data->count;
            $points[] = $data->total_points;
        }

        return [
            'labels' => $months,
            'counts' => $counts,
            'points' => $points
        ];
    }
}
