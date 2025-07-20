<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\ServicePost;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_index')) {
            return view('errors.403');
        }

        // Get reports grouped by reportable type and ID, ordered by count
        $reportedItems = Report::select('reportable_type', 'reportable_id')
            ->selectRaw('COUNT(*) as report_count')
            ->selectRaw('MAX(created_at) as latest_report')
            ->with(['reportable', 'user'])
            ->groupBy('reportable_type', 'reportable_id')
            ->orderBy('report_count', 'desc')
            ->orderBy('latest_report', 'desc')
            ->paginate(15);

        // Get statistics
        $stats = [
            'total_reports' => Report::count(),
            'unique_reported_items' => Report::distinct('reportable_type', 'reportable_id')->count(),
            'user_reports' => Report::where('reportable_type', User::class)->count(),
            'post_reports' => Report::where('reportable_type', ServicePost::class)->count(),
            'today_reports' => Report::whereDate('created_at', today())->count(),
            'this_week_reports' => Report::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Get top reported items for quick actions
        $topReportedItems = Report::select('reportable_type', 'reportable_id')
            ->selectRaw('COUNT(*) as report_count')
            ->groupBy('reportable_type', 'reportable_id')
            ->orderBy('report_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.index', compact('reportedItems', 'stats', 'topReportedItems'));
    }
    public function showDetails(string $type, int $id): View
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_index')) {
            return view('errors.403');
        }

        $modelClass = $type === 'user' ? User::class : ServicePost::class;

        $reports = Report::with('reporter')
            ->where('reportable_type', $modelClass)
            ->where('reportable_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $reportable = $modelClass::findOrFail($id);

        return view('admin.reports.details', compact('reports', 'reportable', 'type'));
    }
    public function store(Request $request, string $reported, int $reportedId): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            $reportableModel = null;
            $reportableType = null;

            switch ($reported) {
                case 'user':
                    $reportableModel = User::findOrFail($reportedId);
                    $reportableType = User::class;
                    break;

                case 'service_post':
                case 'post':
                    $reportableModel = ServicePost::findOrFail($reportedId);
                    $reportableType = ServicePost::class;

                    // Increment report counter on the post if the field exists
                    if (isset($reportableModel->report_count)) {
                        $reportableModel->report_count = $reportableModel->report_count + 1;
                        $reportableModel->save();
                    }
                    break;

                default:
                    return back()->with('error', 'Invalid report type specified.');
            }

            // Create the report
            $report = new Report();
            $report->reporter_id = Auth::id(); // Using reporter_id from your model
            $report->reason = $validated['reason'];
            $report->reportable()->associate($reportableModel);
            $report->save();

            return back()->with('success', 'Report submitted successfully. Our team will review it shortly.');
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to submit report: ' . $e->getMessage());
        }
    }
    public function handleReported(Request $request, string $type, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_manage')) {
            return back()->with('error', 'You do not have permission to perform this action.');
        }

        $validated = $request->validate([
            'action' => 'required|string|in:suspend,delete,warning',
            'reason' => 'required|string|max:500',
        ]);

        try {
            switch ($type) {
                case 'user':
                    $reportable = User::findOrFail($id);

                    switch ($validated['action']) {
                        case 'suspend':
                            // Update user status to 'banned' using the is_active field
                            $reportable->is_active = 'banned';
                            $reportable->save();
                            break;

                        case 'delete':
                            // Consider carefully before implementing user deletion
                            // $reportable->delete();
                            return back()->with('warning', 'User deletion is disabled for safety reasons.');
                            break;

                        case 'warning':
                            // Just log the warning or implement notification logic
                            // No status change for warnings
                            break;
                    }
                    break;

                case 'post':
                    $reportable = ServicePost::findOrFail($id);

                    switch ($validated['action']) {
                        case 'suspend':
                            // Update post state to 'archive' using the state field
                            $reportable->state = 'archive';
                            $reportable->save();
                            break;

                        case 'delete':
                            $reportable->delete();
                            break;

                        case 'warning':
                            // Change state to 'not published' as a warning measure
                            $reportable->state = 'not published';
                            $reportable->save();
                            break;
                    }
                    break;

                default:
                    return back()->with('error', 'Invalid reportable type.');
            }

            $actionMessages = [
                'suspend' => 'suspended',
                'delete' => 'deleted',
                'warning' => 'warned',
            ];

            return back()->with('success', "The {$type} has been {$actionMessages[$validated['action']]} successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while processing your request: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_manage')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
        }

        try {
            $report = Report::findOrFail($id);
            $report->delete();

            return response()->json(['success' => true, 'message' => 'Report has been deleted successfully.']);
        } catch (\Exception $e) {
            Log::error("Error deleting report: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete report.'], 500);
        }
    }
    public function statistics(): View
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_index')) {
            return view('errors.403');
        }

        // Daily reports for the last 30 days
        $dailyReports = Report::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Reports by type
        $reportsByType = Report::select('reportable_type', DB::raw('count(*) as total'))
            ->groupBy('reportable_type')
            ->get();

        // Reports by reason (top 5)
        $reportsByReason = Report::select('reason', DB::raw('count(*) as total'))
            ->groupBy('reason')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Since your model doesn't have status field yet, we'll provide placeholder data
        $resolutionStats = [
            'resolved' => 0,
            'pending' => Report::count(),
            'dismissed' => 0,
        ];

        return view('admin.reports.statistics', compact(
            'dailyReports',
            'reportsByType',
            'reportsByReason',
            'resolutionStats'
        ));
    }

    public function export()
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_index')) {
            return view('errors.403');
        }

        $reports = Report::with(['reportable', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'reports_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reports) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, ['ID', 'Type', 'Reported Item', 'Reporter', 'Reason', 'Created At']);
            
            foreach ($reports as $report) {
                $type = class_basename($report->reportable_type);
                $reportedItem = $report->reportable ? $report->reportable->name ?? $report->reportable->title ?? 'Unknown' : 'Not Found';
                $reporter = $report->user ? $report->user->name : 'Unknown';
                
                fputcsv($file, [
                    $report->id,
                    $type,
                    $reportedItem,
                    $reporter,
                    $report->reason,
                    $report->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function banUser(User $user)
    {
        try {
            $admin = Auth::user();
            
            // Simple admin check - you can adjust this based on your needs
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $user->is_active = 'banned';
            $user->save();

            Log::info("User {$user->id} banned by admin {$admin->id}");

            return response()->json([
                'success' => true,
                'message' => 'User has been banned successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error banning user: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while banning the user.'
            ], 500);
        }
    }

    public function unbanUser(User $user)
    {
        try {
            $admin = Auth::user();
            
            // Simple admin check
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $user->is_active = 'active';
            $user->save();

            Log::info("User {$user->id} unbanned by admin {$admin->id}");

            return response()->json([
                'success' => true,
                'message' => 'User has been unbanned successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error unbanning user: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while unbanning the user.'
            ], 500);
        }
    }

    public function deletePost(ServicePost $post)
    {
        try {
            $admin = Auth::user();
            
            // Simple admin check
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $postId = $post->id;
            $post->delete();

            Log::info("Post {$postId} deleted by admin {$admin->id}");

            return response()->json([
                'success' => true,
                'message' => 'Post has been deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting post: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the post.'
            ], 500);
        }
    }
}
