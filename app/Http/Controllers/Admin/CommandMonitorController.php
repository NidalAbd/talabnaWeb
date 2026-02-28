<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommandLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommandMonitorController extends Controller
{
    protected array $commands = [
        [
            'name' => 'badges:expire',
            'label' => 'Badge Expiration',
            'schedule' => 'Every minute',
            'icon' => 'mdi-certificate-outline',
            'data_label' => 'badges expired',
        ],
        [
            'name' => 'ai:generate',
            'label' => 'AI Image Generation',
            'schedule' => 'Manual / Cron',
            'icon' => 'mdi-image-auto-adjust',
            'data_label' => 'images generated',
        ],
        [
            'name' => 'ai:posts',
            'label' => 'AI Post Generation',
            'schedule' => 'Manual / Cron',
            'icon' => 'mdi-text-box-plus-outline',
            'data_label' => 'posts created',
        ],
        [
            'name' => 'seo:sync',
            'label' => 'SEO Sync',
            'schedule' => 'Daily',
            'icon' => 'mdi-google-analytics',
            'data_label' => 'records synced',
        ],
        [
            'name' => 'seo:cleanup',
            'label' => 'SEO Cleanup',
            'schedule' => 'Weekly',
            'icon' => 'mdi-broom',
            'data_label' => 'records cleaned',
        ],
        [
            'name' => 'monitor:cleanup',
            'label' => 'Log Cleanup',
            'schedule' => 'Daily',
            'icon' => 'mdi-database-remove-outline',
            'data_label' => 'logs aggregated',
        ],
    ];

    public function index(Request $request): JsonResponse
    {
        $hours = (int) $request->input('hours', 24);
        $from = $hours >= 720
            ? Carbon::now()->subMonth()
            : Carbon::now()->subHours($hours);

        $commands = [];

        foreach ($this->commands as $cmd) {
            $lastRun = CommandLog::forCommand($cmd['name'])
                ->orderBy('started_at', 'desc')
                ->first();

            $stats = CommandLog::forCommand($cmd['name'])
                ->inTimeRange($from)
                ->selectRaw('COUNT(*) as total_runs')
                ->selectRaw("SUM(CASE WHEN status = 'finished' THEN 1 ELSE 0 END) as successful")
                ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
                ->first();

            $dataChanges = CommandLog::forCommand($cmd['name'])
                ->inTimeRange($from)
                ->where('status', 'finished')
                ->sum('records_affected');

            $commands[] = [
                'name' => $cmd['name'],
                'label' => $cmd['label'],
                'schedule' => $cmd['schedule'],
                'icon' => $cmd['icon'],
                'last_run' => $lastRun ? [
                    'status' => $lastRun->status,
                    'started_at' => $lastRun->started_at->toIso8601String(),
                    'duration' => $lastRun->duration,
                    'records_affected' => $lastRun->records_affected,
                ] : null,
                'stats' => [
                    'total_runs' => (int) ($stats->total_runs ?? 0),
                    'successful' => (int) ($stats->successful ?? 0),
                    'failed' => (int) ($stats->failed ?? 0),
                ],
                'data_changes' => [
                    'count' => (int) $dataChanges,
                    'label' => $cmd['data_label'],
                ],
            ];
        }

        $recentLogs = CommandLog::recent(50)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'command' => $log->command,
                'status' => $log->status,
                'started_at' => $log->started_at->toIso8601String(),
                'finished_at' => $log->finished_at?->toIso8601String(),
                'duration' => $log->duration,
                'records_affected' => $log->records_affected,
                'error_message' => $log->error_message,
            ]);

        $recentErrors = CommandLog::failed()
            ->orderBy('started_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'command' => $log->command,
                'started_at' => $log->started_at->toIso8601String(),
                'error_message' => $log->error_message,
                'duration' => $log->duration,
            ]);

        $aiProgress = $this->getAiProgress();

        return response()->json([
            'commands' => $commands,
            'recent_logs' => $recentLogs,
            'recent_errors' => $recentErrors,
            'ai_progress' => $aiProgress,
        ]);
    }

    public function progress(): JsonResponse
    {
        return response()->json([
            'ai_progress' => $this->getAiProgress(),
        ]);
    }

    protected function getAiProgress(): array
    {
        $progress = [];

        // Check for AI image generation progress files
        $imageProgressFiles = [
            'ai_generate_progress_category.json',
            'ai_generate_progress_subcategory.json',
        ];

        foreach ($imageProgressFiles as $file) {
            if (Storage::disk('local')->exists($file)) {
                $content = Storage::disk('local')->get($file);
                $data = json_decode($content, true);
                if ($data) {
                    $progress['ai_images_' . str_replace(['ai_generate_progress_', '.json'], '', $file)] = $data;
                }
            }
        }

        // Check for AI post generation progress files
        $postProgressFiles = Storage::disk('local')->files();
        foreach ($postProgressFiles as $file) {
            if (str_starts_with(basename($file), 'ai_generate_progress_posts_')) {
                $content = Storage::disk('local')->get($file);
                $data = json_decode($content, true);
                if ($data) {
                    $progress['ai_posts_' . str_replace(['ai_generate_progress_posts_', '.json'], '', basename($file))] = $data;
                }
            }
        }

        return $progress;
    }
}
