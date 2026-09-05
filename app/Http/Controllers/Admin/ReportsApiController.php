<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ServicePost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsApiController extends Controller
{
    /**
     * Get paginated reports grouped by reportable
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $type = $request->input('type'); // 'user' or 'post'
            $sortBy = $request->input('sort_by', 'total');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            // Get grouped reports with count (exclude reports whose post/user was since deleted)
            $query = Report::with(['reportable'])
                ->whereHasMorph('reportable', [User::class, ServicePost::class])
                ->select('reportable_type', 'reportable_id', DB::raw('count(*) as total'), DB::raw('MAX(created_at) as latest_report'))
                ->groupBy('reportable_type', 'reportable_id');

            // Filter by type
            if ($type) {
                $modelClass = $type === 'user' ? User::class : ServicePost::class;
                $query->where('reportable_type', $modelClass);
            }

            // Apply sorting
            if ($sortBy === 'total') {
                $query->orderBy('total', $sortDirection);
            } elseif ($sortBy === 'latest') {
                $query->orderBy('latest_report', $sortDirection);
            }

            $reports = $query->paginate($perPage);

            // Transform data
            $transformedReports = $reports->getCollection()->map(function ($report) {
                $reportable = $report->reportable;
                $type = $report->reportable_type === User::class ? 'user' : 'post';

                return [
                    'reportable_type' => $type,
                    'reportable_id' => $report->reportable_id,
                    'total_reports' => $report->total,
                    'latest_report' => $report->latest_report,
                    'reportable' => $reportable ? [
                        'id' => $reportable->id,
                        'title' => $type === 'user' ? $reportable->user_name : $reportable->title,
                        'status' => $type === 'user' ? $reportable->is_active : $reportable->state,
                        'image' => $type === 'post' && $reportable->photos ? $reportable->photos->first()?->src : null,
                    ] : null,
                ];
            });

            $reports->setCollection($transformedReports);

            return response()->json([
                'reports' => [
                    'data' => $reports->items(),
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Reports API Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to load reports',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report details for a specific item
     */
    public function show(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $modelClass = $type === 'user' ? User::class : ServicePost::class;

            $reports = Report::with(['reporter'])
                ->where('reportable_type', $modelClass)
                ->where('reportable_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $reportable = $modelClass::findOrFail($id);

            // Transform reports
            $transformedReports = $reports->getCollection()->map(function ($report) {
                return [
                    'id' => $report->id,
                    'reason' => $report->reason,
                    'created_at' => $report->created_at->toISOString(),
                    'reporter' => $report->reporter ? [
                        'id' => $report->reporter->id,
                        'name' => $report->reporter->user_name,
                    ] : null,
                ];
            });

            $reports->setCollection($transformedReports);

            // Transform reportable
            $reportableData = null;
            if ($type === 'user') {
                $reportableData = [
                    'id' => $reportable->id,
                    'name' => $reportable->user_name,
                    'email' => $reportable->email,
                    'status' => $reportable->is_active,
                    'created_at' => $reportable->created_at->toISOString(),
                ];
            } else {
                $reportableData = [
                    'id' => $reportable->id,
                    'title' => $reportable->title,
                    'description' => $reportable->description,
                    'state' => $reportable->state,
                    'report_count' => $reportable->report_count ?? 0,
                    'created_at' => $reportable->created_at->toISOString(),
                    'user' => $reportable->user ? [
                        'id' => $reportable->user->id,
                        'name' => $reportable->user->user_name,
                    ] : null,
                    'image' => $reportable->photos->first()?->src ?? null,
                ];
            }

            return response()->json([
                'reports' => [
                    'data' => $reports->items(),
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                ],
                'reportable' => $reportableData,
                'type' => $type
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load report details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle reported content (suspend, delete, warning)
     */
    public function handleReported(Request $request, string $type, int $id): JsonResponse
    {
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
                            $reportable->is_active = 'banned';
                            $reportable->save();
                            break;

                        case 'delete':
                            return response()->json([
                                'error' => 'User deletion is disabled for safety reasons.'
                            ], 403);

                        case 'warning':
                            // Just log the warning - no status change
                            break;
                    }
                    break;

                case 'post':
                    $reportable = ServicePost::findOrFail($id);

                    switch ($validated['action']) {
                        case 'suspend':
                            $reportable->state = 'archive';
                            $reportable->save();
                            break;

                        case 'delete':
                            $reportable->delete();
                            // Clean up any reports pointing at the now-deleted post
                            Report::where('reportable_type', ServicePost::class)
                                ->where('reportable_id', $id)
                                ->delete();
                            break;

                        case 'warning':
                            $reportable->state = 'not published';
                            $reportable->save();
                            break;
                    }
                    break;

                default:
                    return response()->json([
                        'error' => 'Invalid reportable type.'
                    ], 400);
            }

            $actionMessages = [
                'suspend' => 'suspended',
                'delete' => 'deleted',
                'warning' => 'warned',
            ];

            return response()->json([
                'message' => "The {$type} has been {$actionMessages[$validated['action']]} successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing your request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a report
     */
    public function destroy($id): JsonResponse
    {
        try {
            $report = Report::findOrFail($id);
            $report->delete();

            return response()->json([
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report statistics with charts data
     */
    public function getStats(): JsonResponse
    {
        try {
            $now   = Carbon::now();
            $today = Carbon::today();

            $totalReports = Report::count();
            $reportsToday = Report::whereDate('created_at', $today)->count();

            // Unique reported items
            $uniqueItems = Report::select('reportable_type', 'reportable_id')
                ->distinct()
                ->count(DB::raw('CONCAT(reportable_type, reportable_id)'));

            // Week-over-week trend
            $thisWeek = Report::whereBetween('created_at', [$now->copy()->subDays(7), $now])->count();
            $lastWeek = Report::whereBetween('created_at', [$now->copy()->subDays(14), $now->copy()->subDays(7)])->count();
            $wowChange = $lastWeek > 0
                ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1)
                : ($thisWeek > 0 ? 100.0 : 0.0);

            $stats = [
                [
                    'label'  => 'Total Reports',
                    'value'  => $totalReports,
                    'icon'   => 'fas fa-flag',
                    'color'  => 'danger',
                ],
                [
                    'label'  => 'Unique Items',
                    'value'  => $uniqueItems,
                    'icon'   => 'fas fa-bullseye',
                    'color'  => 'warning',
                ],
                [
                    'label'  => 'Reports Today',
                    'value'  => $reportsToday,
                    'icon'   => 'fas fa-clock',
                    'color'  => 'info',
                ],
                [
                    'label'  => 'Week Trend',
                    'value'  => ($wowChange >= 0 ? '+' : '') . $wowChange . '%',
                    'change' => $wowChange,
                    'icon'   => 'fas fa-chart-line',
                    'color'  => 'primary',
                ],
            ];

            // Pie chart: reports by reason
            $byReason = Report::select('reason', DB::raw('COUNT(*) as count'))
                ->groupBy('reason')
                ->orderByDesc('count')
                ->get();

            $reasonChart = [
                'labels'   => $byReason->pluck('reason')->map(fn($r) => ucfirst(str_replace('_', ' ', $r)))->toArray(),
                'datasets' => [[
                    'data'            => $byReason->pluck('count')->toArray(),
                    'backgroundColor' => ['#ef4444', '#f97316', '#eab308', '#3b82f6', '#8b5cf6'],
                ]],
            ];

            // Line chart: daily report trend (30 days)
            $dailyTrend = Report::whereBetween('created_at', [$now->copy()->subDays(30), $now])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $trendChart = [
                'labels'   => $dailyTrend->pluck('date')->toArray(),
                'datasets' => [[
                    'label'           => 'Reports',
                    'data'            => $dailyTrend->pluck('count')->toArray(),
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.1)',
                    'fill'            => true,
                    'tension'         => 0.3,
                ]],
            ];

            return response()->json([
                'stats'        => $stats,
                'reason_chart' => $reasonChart,
                'trend_chart'  => $trendChart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to load statistics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
