<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServicePost;
use App\Models\Categories;
use App\Models\Sub_categories;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\point_purchase_requests;
use App\Models\Report;
use App\Models\Level;
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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        // User statistics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', 'active')->count();
        $bannedUsers = User::where('is_active', 'banned')->count();
        $recentUsers = User::latest()->take(10)->get();

        // Post statistics
        $totalPosts = ServicePost::count();
        $publishedPosts = ServicePost::where('state', 'published')->count();
        $notPublishedPosts = ServicePost::where('state', 'not published')->count();
        $archivedPosts = ServicePost::where('state', 'archive')->count();
        $rejectedPosts = ServicePost::where('state', 'rejected')->count();
        $recentPosts = ServicePost::with(['user', 'category'])->latest()->take(10)->get();

        // Point statistics
        $totalPoints = palservice_points::sum('point');
        $totalTransactions = point_transactions::count();
        $pendingPurchaseRequests = point_purchase_requests::where('status', 'pending')->count();
        $recentTransactions = point_transactions::with(['fromUser', 'toUser'])->latest()->take(10)->get();

        // Points used statistics
        $pointsUsedStats = $this->getPointsUsedStatistics();
        $pointsUsedToday = $pointsUsedStats['today'];
        $pointsUsedWeek = $pointsUsedStats['week'];
        $pointsUsedMonth = $pointsUsedStats['month'];
        $pointsUsedYear = $pointsUsedStats['year'];
        $pointsUsedLifetime = $pointsUsedStats['lifetime'];

        // Category statistics
        $totalCategories = Categories::count();
        $totalSubCategories = Sub_categories::count();
        $categoriesWithCounts = Categories::withCount('servicePosts')->get();

        // Report statistics
        $totalReports = Report::count();

        // Post types
        $offerPosts = ServicePost::where('type', 'عرض')->count();
        $requestPosts = ServicePost::where('type', 'طلب')->count();

        // Get level IDs for badge counting
        $goldLevel = Level::where('name->ar', 'ذهبي')->first();
        $diamondLevel = Level::where('name->ar', 'ماسي')->first();
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        
        // Badge stats
        $normalPosts = $regularLevel ? ServicePost::where('level_id', $regularLevel->id)->count() : 0;
        $goldenPosts = $goldLevel ? ServicePost::where('level_id', $goldLevel->id)->count() : 0;
        $diamondPosts = $diamondLevel ? ServicePost::where('level_id', $diamondLevel->id)->count() : 0;

        // Data for charts
        $postsByMonth = $this->getPostsByMonth();
        $usersByMonth = $this->getUsersByMonth();
        $postsByCategory = $this->getPostsByCategory();
        $pointTransactionsByMonth = $this->getPointTransactionsByMonth();

        // Map data
        $mapData = ServicePost::select('id', 'title', 'location_latitudes', 'location_longitudes')
            ->where('state', 'published')
            ->whereNotNull('location_latitudes')
            ->whereNotNull('location_longitudes')
            ->latest()
            ->take(100)
            ->get();

        return view('dashboard', compact(
            'totalUsers', 'activeUsers', 'bannedUsers', 'recentUsers',
            'totalPosts', 'publishedPosts', 'notPublishedPosts', 'archivedPosts', 'rejectedPosts', 'recentPosts',
            'totalPoints', 'totalTransactions', 'pendingPurchaseRequests', 'recentTransactions',
            'pointsUsedStats', 'pointsUsedToday', 'pointsUsedWeek', 'pointsUsedMonth', 'pointsUsedYear', 'pointsUsedLifetime',
            'totalCategories', 'totalSubCategories', 'categoriesWithCounts',
            'totalReports', 'offerPosts', 'requestPosts',
            'normalPosts', 'goldenPosts', 'diamondPosts',
            'postsByMonth', 'usersByMonth', 'postsByCategory', 'pointTransactionsByMonth',
            'mapData', 'user'
        ));
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
        // Get level IDs
        $goldLevel = Level::where('name->ar', 'ذهبي')->first();
        $diamondLevel = Level::where('name->ar', 'ماسي')->first();
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        
        $query = ServicePost::where('badge_duration', '>', 0);

        // Filter out regular level posts
        if ($regularLevel) {
            $query->where('level_id', '!=', $regularLevel->id);
        }

        // Apply date filter if specified
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        // Calculate points based on level and duration
        $goldPoints = 0;
        $diamondPoints = 0;
        
        if ($goldLevel) {
            $goldPosts = clone $query;
            $goldPoints = $goldPosts->where('level_id', $goldLevel->id)->sum(DB::raw('badge_duration * 2'));
        }
        
        if ($diamondLevel) {
            $diamondPosts = clone $query;
            $diamondPoints = $diamondPosts->where('level_id', $diamondLevel->id)->sum(DB::raw('badge_duration * 10'));
        }
        
        return $goldPoints + $diamondPoints;
    }

    /**
     * Get points used from transactions within a time period
     *
     * @param Carbon|null $startDate
     * @return int
     */
    private function getTransactionPointsUsed($startDate = null)
    {
        $query = point_transactions::where('type', 'used');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        return $query->sum('point');
    }

    /**
     * Get posts by month for chart
     *
     * @return array
     */
    private function getPostsByMonth()
    {
        $posts = ServicePost::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $data = array_fill(1, 12, 0);
        foreach ($posts as $post) {
            $data[$post->month] = $post->count;
        }

        return array_values($data);
    }

    /**
     * Get users by month for chart
     *
     * @return array
     */
    private function getUsersByMonth()
    {
        $users = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $data = array_fill(1, 12, 0);
        foreach ($users as $user) {
            $data[$user->month] = $user->count;
        }

        return array_values($data);
    }

    /**
     * Get posts by category for chart
     *
     * @return array
     */
    private function getPostsByCategory()
    {
        $categories = Categories::withCount('servicePosts')
            ->orderBy('service_posts_count', 'desc')
            ->take(10)
            ->get();

        return [
            'labels' => $categories->pluck('name')->toArray(),
            'data' => $categories->pluck('service_posts_count')->toArray()
        ];
    }

    /**
     * Get point transactions by month for chart
     *
     * @return array
     */
    private function getPointTransactionsByMonth()
    {
        $transactions = point_transactions::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $data = array_fill(1, 12, 0);
        foreach ($transactions as $transaction) {
            $data[$transaction->month] = $transaction->count;
        }

        return array_values($data);
    }
}
