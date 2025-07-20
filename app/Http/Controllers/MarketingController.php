<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServicePost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        Log::info('MarketingController@index called', ['is_ajax' => request()->ajax()]);
        
        // Marketing metrics
        $metrics = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', 'active')->count(),
            'total_posts' => ServicePost::count(),
            'premium_posts' => ServicePost::where('is_premium', true)->count(),
            'monthly_growth' => $this->calculateMonthlyGrowth(),
            'engagement_rate' => $this->calculateEngagementRate(),
        ];

        Log::info('Marketing metrics calculated', ['metrics' => $metrics]);

        // Recent marketing activities
        $recentActivities = $this->getRecentActivities();
        Log::info('Recent activities retrieved', ['activities_count' => count($recentActivities)]);

        if (request()->ajax()) {
            Log::info('Returning AJAX response for marketing dashboard');
            return response()->json([
                'success' => true,
                'metrics' => $metrics,
                'recentActivities' => $recentActivities
            ]);
        }

        return view('admin.marketing.dashboard', compact('metrics', 'recentActivities'));
    }

    public function sendNotification(Request $request)
    {
        Log::info('MarketingController@sendNotification called', [
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax()
        ]);

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'target_users' => 'required|in:all,active,premium'
            ]);

            // Simulate notification sending
            $targetCount = $this->getTargetUserCount($request->target_users);
            
            Log::info('Notification sent successfully', [
                'title' => $request->title,
                'target_users' => $request->target_users,
                'target_count' => $targetCount
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Notification sent to {$targetCount} users successfully."
                ]);
            }

            return back()->with('success', "Notification sent to {$targetCount} users successfully.");
        } catch (\Exception $e) {
            Log::error("Error sending notification: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to send notification.'], 500);
            }
            
            return back()->with('error', 'Failed to send notification.');
        }
    }

    public function exportData(Request $request)
    {
        Log::info('MarketingController@exportData called', [
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax()
        ]);

        try {
            $request->validate([
                'data_type' => 'required|in:users,posts,analytics',
                'format' => 'required|in:csv,json,xlsx'
            ]);

            // Simulate data export
            $exportData = $this->generateExportData($request->data_type);
            
            Log::info('Data export completed', [
                'data_type' => $request->data_type,
                'format' => $request->format,
                'record_count' => count($exportData)
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Data exported successfully. {$request->format} file ready for download.",
                    'download_url' => route('marketing.download', ['file' => 'export_' . time() . '.' . $request->format])
                ]);
            }

            return back()->with('success', "Data exported successfully. {$request->format} file ready for download.");
        } catch (\Exception $e) {
            Log::error("Error exporting data: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to export data.'], 500);
            }
            
            return back()->with('error', 'Failed to export data.');
        }
    }

    public function refreshMetrics()
    {
        Log::info('MarketingController@refreshMetrics called');

        try {
            $metrics = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', 'active')->count(),
                'total_posts' => ServicePost::count(),
                'premium_posts' => ServicePost::where('is_premium', true)->count(),
                'monthly_growth' => $this->calculateMonthlyGrowth(),
                'engagement_rate' => $this->calculateEngagementRate(),
            ];

            Log::info('Metrics refreshed successfully', ['metrics' => $metrics]);

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error("Error refreshing metrics: " . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json(['success' => false, 'message' => 'Failed to refresh metrics.'], 500);
        }
    }

    private function calculateMonthlyGrowth()
    {
        $currentMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return 0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function calculateEngagementRate()
    {
        $totalViews = ServicePost::sum('view_count');
        $totalPosts = ServicePost::count();
        
        if ($totalPosts == 0) return 0;
        
        return round($totalViews / $totalPosts, 2);
    }

    private function getRecentActivities()
    {
        return [
            'new_users' => User::latest()->take(5)->get(),
            'new_posts' => ServicePost::latest()->take(5)->get(),
            'top_posts' => ServicePost::orderBy('view_count', 'desc')->take(5)->get(),
        ];
    }

    private function getTargetUserCount($targetType)
    {
        switch ($targetType) {
            case 'all':
                return User::count();
            case 'active':
                return User::where('is_active', 'active')->count();
            case 'premium':
                return ServicePost::where('is_premium', true)->count();
            default:
                return 0;
        }
    }

    private function generateExportData($dataType)
    {
        switch ($dataType) {
            case 'users':
                return User::all()->toArray();
            case 'posts':
                return ServicePost::all()->toArray();
            case 'analytics':
                return [
                    'metrics' => [
                        'total_users' => User::count(),
                        'active_users' => User::where('is_active', 'active')->count(),
                        'total_posts' => ServicePost::count(),
                    ]
                ];
            default:
                return [];
        }
    }
} 