<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_index')) {
            return view('errors.403');
        }

        // Get grouped reports with count, ordered by most reported items first
        $reports = Report::with(['reportable'])
            ->select('reportable_type', 'reportable_id', DB::raw('count(*) as total'))
            ->groupBy('reportable_type', 'reportable_id')
            ->orderBy('total', 'desc')
            ->paginate(10);

        // Get additional statistics for the dashboard
        $stats = [
            'total_reports' => Report::count(),
            'user_reports' => Report::where('reportable_type', User::class)->count(),
            'post_reports' => Report::where('reportable_type', ServicePost::class)->count(),
            'handled_reports' => 0, // Since your model doesn't have status field yet
            'recent_reports' => Report::with(['reportable', 'reporter'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];

        return view('admin.reports.index', compact('reports', 'stats'));
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
    public function destroy($id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->hasPermission('report_manage')) {
            return back()->with('error', 'You do not have permission to perform this action.');
        }

        $report = Report::findOrFail($id);
        $report->delete();

        return back()->with('success', 'Report has been deleted successfully.');
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
}
