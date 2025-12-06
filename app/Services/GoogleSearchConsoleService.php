<?php

namespace App\Services;

use App\Models\SeoKeyword;
use App\Models\SeoPerformance;
use App\Models\SeoLog;
use App\Models\SeoIssue;
use Carbon\Carbon;
use Exception;
use Google\Client;
use Google\Service\Webmasters;
use Google\Service\Webmasters\SearchAnalyticsQueryRequest;
use Illuminate\Support\Facades\Log;

class GoogleSearchConsoleService
{
    protected ?Client $client = null;
    protected ?Webmasters $webmasters = null;
    protected string $siteUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('seo.google_search_console.enabled', false);
        $this->siteUrl = config('seo.google_search_console.site_url');
    }

    protected function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client();
            $this->client->setApplicationName(config('app.name') . ' SEO Manager');
            $this->client->setScopes([Webmasters::WEBMASTERS_READONLY]);

            $credentialsPath = config('seo.google_search_console.credentials_path');

            if (!file_exists($credentialsPath)) {
                throw new Exception("Google Service Account credentials file not found at: {$credentialsPath}");
            }

            $this->client->setAuthConfig($credentialsPath);
        }

        return $this->client;
    }

    protected function getWebmasters(): Webmasters
    {
        if ($this->webmasters === null) {
            $this->webmasters = new Webmasters($this->getClient());
        }

        return $this->webmasters;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function testConnection(): array
    {
        if (!$this->enabled) {
            return [
                'success' => false,
                'message' => 'Google Search Console integration is disabled',
            ];
        }

        try {
            $service = $this->getWebmasters();
            $sites = $service->sites->listSites();

            $siteFound = false;
            foreach ($sites->getSiteEntry() as $site) {
                if ($site->getSiteUrl() === $this->siteUrl) {
                    $siteFound = true;
                    break;
                }
            }

            return [
                'success' => true,
                'site_found' => $siteFound,
                'site_url' => $this->siteUrl,
                'message' => $siteFound ? 'Connection successful' : 'Site not found in Search Console',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function syncSearchAnalytics(int $days = null): array
    {
        $days = $days ?? config('seo.sync.default_date_range', 28);
        $log = SeoLog::createLog('sync_analytics', 'google_search_console', ['days' => $days]);
        $log->start();

        $startDate = Carbon::now()->subDays($days)->format('Y-m-d');
        $endDate = Carbon::now()->subDay()->format('Y-m-d');

        $processed = 0;
        $created = 0;
        $updated = 0;

        try {
            // Sync keywords data
            $keywordResult = $this->syncKeywords($startDate, $endDate);
            $processed += $keywordResult['processed'];
            $created += $keywordResult['created'];
            $updated += $keywordResult['updated'];

            // Sync page performance data
            $pageResult = $this->syncPagePerformance($startDate, $endDate);
            $processed += $pageResult['processed'];
            $created += $pageResult['created'];
            $updated += $pageResult['updated'];

            $log->complete($processed, $created, $updated);

            return [
                'success' => true,
                'processed' => $processed,
                'created' => $created,
                'updated' => $updated,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
            ];
        } catch (Exception $e) {
            $log->fail($e->getMessage());
            Log::error('GSC Sync Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function syncKeywords(string $startDate, string $endDate): array
    {
        $service = $this->getWebmasters();

        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions(['query', 'date', 'country', 'device']);
        $request->setRowLimit(config('seo.sync.max_rows_per_request', 25000));

        $response = $service->searchanalytics->query($this->siteUrl, $request);

        $processed = 0;
        $created = 0;
        $updated = 0;

        foreach ($response->getRows() as $row) {
            $keys = $row->getKeys();
            $keyword = $keys[0];
            $date = $keys[1];
            $country = $keys[2];
            $device = $keys[3];

            $data = [
                'keyword' => $keyword,
                'date' => $date,
                'country' => strtoupper($country),
                'device' => $device,
                'clicks' => (int) $row->getClicks(),
                'impressions' => (int) $row->getImpressions(),
                'ctr' => $row->getCtr() * 100,
                'position' => $row->getPosition(),
            ];

            $existing = SeoKeyword::where('keyword', $keyword)
                ->where('date', $date)
                ->where('country', strtoupper($country))
                ->where('device', $device)
                ->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                SeoKeyword::create($data);
                $created++;
            }

            $processed++;
        }

        // Calculate trends
        $this->calculateKeywordTrends();

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
        ];
    }

    protected function syncPagePerformance(string $startDate, string $endDate): array
    {
        $service = $this->getWebmasters();

        $request = new SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions(['page', 'date']);
        $request->setRowLimit(config('seo.sync.max_rows_per_request', 25000));

        $response = $service->searchanalytics->query($this->siteUrl, $request);

        $processed = 0;
        $created = 0;
        $updated = 0;

        foreach ($response->getRows() as $row) {
            $keys = $row->getKeys();
            $url = $keys[0];
            $date = $keys[1];

            $pageType = $this->detectPageType($url);
            $entityData = $this->detectEntity($url);

            $data = [
                'url' => $url,
                'date' => $date,
                'page_type' => $pageType,
                'entity_id' => $entityData['id'],
                'entity_type' => $entityData['type'],
                'clicks' => (int) $row->getClicks(),
                'impressions' => (int) $row->getImpressions(),
                'ctr' => $row->getCtr() * 100,
                'position' => $row->getPosition(),
            ];

            $existing = SeoPerformance::where('url', $url)
                ->where('date', $date)
                ->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                SeoPerformance::create($data);
                $created++;
            }

            $processed++;
        }

        // Calculate trends
        $this->calculatePageTrends();

        // Sync top keywords for each page
        $this->syncTopKeywordsForPages($startDate, $endDate);

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
        ];
    }

    protected function syncTopKeywordsForPages(string $startDate, string $endDate): void
    {
        $service = $this->getWebmasters();

        // Get unique pages
        $pages = SeoPerformance::whereBetween('date', [$startDate, $endDate])
            ->distinct('url')
            ->pluck('url');

        foreach ($pages as $pageUrl) {
            $request = new SearchAnalyticsQueryRequest();
            $request->setStartDate($startDate);
            $request->setEndDate($endDate);
            $request->setDimensions(['query']);
            $request->setDimensionFilterGroups([
                [
                    'filters' => [
                        [
                            'dimension' => 'page',
                            'operator' => 'equals',
                            'expression' => $pageUrl,
                        ],
                    ],
                ],
            ]);
            $request->setRowLimit(10);

            try {
                $response = $service->searchanalytics->query($this->siteUrl, $request);

                $topKeywords = [];
                foreach ($response->getRows() as $row) {
                    $topKeywords[] = [
                        'keyword' => $row->getKeys()[0],
                        'clicks' => (int) $row->getClicks(),
                        'impressions' => (int) $row->getImpressions(),
                        'ctr' => round($row->getCtr() * 100, 2),
                        'position' => round($row->getPosition(), 1),
                    ];
                }

                // Update latest performance record for this page
                SeoPerformance::where('url', $pageUrl)
                    ->orderByDesc('date')
                    ->first()
                    ?->update(['top_keywords' => $topKeywords]);
            } catch (Exception $e) {
                Log::warning("Failed to sync top keywords for page: {$pageUrl}. Error: " . $e->getMessage());
            }
        }
    }

    protected function detectPageType(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if ($path === '/' || $path === '') {
            return 'home';
        }

        if (preg_match('/\/category\/\d+/', $path)) {
            return 'category';
        }

        if (preg_match('/\/subcategory\/\d+/', $path) || preg_match('/^\/[^\/]+\/[^\/]+-\d+$/', $path)) {
            return 'subcategory';
        }

        if (preg_match('/\/post\/\d+/', $path) || preg_match('/\/service\/\d+/', $path)) {
            return 'post';
        }

        if (preg_match('/\/city\/\d+/', $path) || preg_match('/^\/city\/[^\/]+$/', $path)) {
            return 'city';
        }

        if (preg_match('/\/country\/\d+/', $path) || preg_match('/^\/country\/[^\/]+$/', $path)) {
            return 'country';
        }

        if (preg_match('/\/user\/\d+/', $path) || preg_match('/\/profile\/\d+/', $path)) {
            return 'user';
        }

        return 'other';
    }

    protected function detectEntity(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        // Match category pattern
        if (preg_match('/\/category\/(\d+)/', $path, $matches)) {
            return ['id' => (int) $matches[1], 'type' => 'App\\Models\\Categories'];
        }

        // Match subcategory pattern (new SEO-friendly URLs)
        if (preg_match('/^\/[^\/]+\/[^\/]+-(\d+)$/', $path, $matches)) {
            return ['id' => (int) $matches[1], 'type' => 'App\\Models\\Sub_categories'];
        }

        // Match old subcategory pattern
        if (preg_match('/\/subcategory\/(\d+)/', $path, $matches)) {
            return ['id' => (int) $matches[1], 'type' => 'App\\Models\\Sub_categories'];
        }

        // Match post pattern
        if (preg_match('/\/post\/(\d+)/', $path, $matches) || preg_match('/\/service\/(\d+)/', $path, $matches)) {
            return ['id' => (int) $matches[1], 'type' => 'App\\Models\\ServicePost'];
        }

        // Match city pattern
        if (preg_match('/\/city\/(\d+)/', $path, $matches)) {
            return ['id' => (int) $matches[1], 'type' => 'App\\Models\\City'];
        }

        return ['id' => null, 'type' => null];
    }

    protected function calculateKeywordTrends(): void
    {
        $cutoffDate = Carbon::now()->subDays(7)->format('Y-m-d');

        // Get unique keywords
        $keywords = SeoKeyword::where('date', '>=', $cutoffDate)
            ->select('keyword', 'country', 'device')
            ->distinct()
            ->get();

        foreach ($keywords as $kw) {
            // Get current period (last 7 days)
            $current = SeoKeyword::where('keyword', $kw->keyword)
                ->where('country', $kw->country)
                ->where('device', $kw->device)
                ->where('date', '>=', $cutoffDate)
                ->selectRaw('AVG(position) as avg_position, SUM(clicks) as total_clicks')
                ->first();

            // Get previous period (8-14 days ago)
            $previousStart = Carbon::now()->subDays(14)->format('Y-m-d');
            $previousEnd = Carbon::now()->subDays(8)->format('Y-m-d');

            $previous = SeoKeyword::where('keyword', $kw->keyword)
                ->where('country', $kw->country)
                ->where('device', $kw->device)
                ->whereBetween('date', [$previousStart, $previousEnd])
                ->selectRaw('AVG(position) as avg_position, SUM(clicks) as total_clicks')
                ->first();

            if ($previous && $previous->avg_position > 0) {
                $positionChange = $previous->avg_position - $current->avg_position;
                $percentChange = ($positionChange / $previous->avg_position) * 100;

                $trend = 'stable';
                if ($positionChange > 1) {
                    $trend = 'up'; // Position improved (lower is better)
                } elseif ($positionChange < -1) {
                    $trend = 'down'; // Position worsened
                }

                // Update latest record for this keyword
                SeoKeyword::where('keyword', $kw->keyword)
                    ->where('country', $kw->country)
                    ->where('device', $kw->device)
                    ->orderByDesc('date')
                    ->first()
                    ?->update([
                        'trend' => $trend,
                        'trend_change' => round($percentChange, 2),
                    ]);
            }
        }
    }

    protected function calculatePageTrends(): void
    {
        $cutoffDate = Carbon::now()->subDays(7)->format('Y-m-d');

        // Get unique pages
        $pages = SeoPerformance::where('date', '>=', $cutoffDate)
            ->distinct('url')
            ->pluck('url');

        foreach ($pages as $url) {
            // Get current period (last 7 days)
            $current = SeoPerformance::where('url', $url)
                ->where('date', '>=', $cutoffDate)
                ->selectRaw('AVG(position) as avg_position, SUM(clicks) as total_clicks')
                ->first();

            // Get previous period (8-14 days ago)
            $previousStart = Carbon::now()->subDays(14)->format('Y-m-d');
            $previousEnd = Carbon::now()->subDays(8)->format('Y-m-d');

            $previous = SeoPerformance::where('url', $url)
                ->whereBetween('date', [$previousStart, $previousEnd])
                ->selectRaw('AVG(position) as avg_position, SUM(clicks) as total_clicks')
                ->first();

            if ($previous && $previous->avg_position > 0) {
                $positionChange = $previous->avg_position - $current->avg_position;
                $percentChange = ($positionChange / $previous->avg_position) * 100;

                $trend = 'stable';
                if ($positionChange > 1) {
                    $trend = 'up';
                } elseif ($positionChange < -1) {
                    $trend = 'down';
                }

                // Update latest record for this page
                SeoPerformance::where('url', $url)
                    ->orderByDesc('date')
                    ->first()
                    ?->update([
                        'trend' => $trend,
                        'trend_change' => round($percentChange, 2),
                    ]);
            }
        }
    }

    public function getTopKeywords(int $limit = 50, string $country = null, int $days = 28): array
    {
        $startDate = Carbon::now()->subDays($days)->format('Y-m-d');

        $query = SeoKeyword::where('date', '>=', $startDate)
            ->select('keyword')
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->groupBy('keyword');

        if ($country) {
            $query->where('country', strtoupper($country));
        }

        return $query->orderByDesc('total_clicks')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTopPages(int $limit = 50, string $pageType = null, int $days = 28): array
    {
        $startDate = Carbon::now()->subDays($days)->format('Y-m-d');

        $query = SeoPerformance::where('date', '>=', $startDate)
            ->select('url', 'page_type')
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->groupBy('url', 'page_type');

        if ($pageType) {
            $query->where('page_type', $pageType);
        }

        return $query->orderByDesc('total_clicks')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getPerformanceOverview(int $days = 28): array
    {
        $startDate = Carbon::now()->subDays($days)->format('Y-m-d');
        $previousStart = Carbon::now()->subDays($days * 2)->format('Y-m-d');
        $previousEnd = Carbon::now()->subDays($days + 1)->format('Y-m-d');

        // Current period
        $current = SeoPerformance::where('date', '>=', $startDate)
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->first();

        // Previous period
        $previous = SeoPerformance::whereBetween('date', [$previousStart, $previousEnd])
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->first();

        return [
            'current' => [
                'clicks' => (int) ($current->total_clicks ?? 0),
                'impressions' => (int) ($current->total_impressions ?? 0),
                'avg_position' => round($current->avg_position ?? 0, 1),
                'avg_ctr' => round($current->avg_ctr ?? 0, 2),
            ],
            'previous' => [
                'clicks' => (int) ($previous->total_clicks ?? 0),
                'impressions' => (int) ($previous->total_impressions ?? 0),
                'avg_position' => round($previous->avg_position ?? 0, 1),
                'avg_ctr' => round($previous->avg_ctr ?? 0, 2),
            ],
            'changes' => [
                'clicks' => $this->calculateChange($previous->total_clicks ?? 0, $current->total_clicks ?? 0),
                'impressions' => $this->calculateChange($previous->total_impressions ?? 0, $current->total_impressions ?? 0),
                'position' => $this->calculateChange($previous->avg_position ?? 0, $current->avg_position ?? 0, true),
                'ctr' => $this->calculateChange($previous->avg_ctr ?? 0, $current->avg_ctr ?? 0),
            ],
            'date_range' => [
                'start' => $startDate,
                'end' => Carbon::now()->format('Y-m-d'),
            ],
        ];
    }

    protected function calculateChange($previous, $current, bool $lowerIsBetter = false): array
    {
        if ($previous == 0) {
            return ['value' => 0, 'direction' => 'stable'];
        }

        $change = (($current - $previous) / $previous) * 100;

        $direction = 'stable';
        if (abs($change) > 1) {
            if ($lowerIsBetter) {
                $direction = $change < 0 ? 'up' : 'down';
            } else {
                $direction = $change > 0 ? 'up' : 'down';
            }
        }

        return [
            'value' => round($change, 2),
            'direction' => $direction,
        ];
    }

    public function getPerformanceByPageType(int $days = 28): array
    {
        $startDate = Carbon::now()->subDays($days)->format('Y-m-d');

        return SeoPerformance::where('date', '>=', $startDate)
            ->select('page_type')
            ->selectRaw('COUNT(DISTINCT url) as pages')
            ->selectRaw('SUM(clicks) as total_clicks')
            ->selectRaw('SUM(impressions) as total_impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->groupBy('page_type')
            ->orderByDesc('total_clicks')
            ->get()
            ->toArray();
    }

    public function getDailyStats(int $days = 28): array
    {
        $startDate = Carbon::now()->subDays($days)->format('Y-m-d');

        return SeoPerformance::where('date', '>=', $startDate)
            ->select('date')
            ->selectRaw('SUM(clicks) as clicks')
            ->selectRaw('SUM(impressions) as impressions')
            ->selectRaw('AVG(position) as avg_position')
            ->selectRaw('AVG(ctr) as avg_ctr')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function cleanupOldData(): array
    {
        $keywordsRetention = config('seo.data_retention.keywords_days', 90);
        $performanceRetention = config('seo.data_retention.performance_days', 90);
        $logsRetention = config('seo.data_retention.logs_days', 30);

        $keywordsDeleted = SeoKeyword::where('date', '<', Carbon::now()->subDays($keywordsRetention))->delete();
        $performanceDeleted = SeoPerformance::where('date', '<', Carbon::now()->subDays($performanceRetention))->delete();
        $logsDeleted = SeoLog::where('created_at', '<', Carbon::now()->subDays($logsRetention))->delete();

        return [
            'keywords_deleted' => $keywordsDeleted,
            'performance_deleted' => $performanceDeleted,
            'logs_deleted' => $logsDeleted,
        ];
    }

    public function getSyncLogs(int $limit = 20): array
    {
        return SeoLog::orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getOpenIssues(): array
    {
        return SeoIssue::unresolved()
            ->orderByRaw("FIELD(severity, 'critical', 'warning', 'info')")
            ->orderByDesc('detected_at')
            ->get()
            ->toArray();
    }

    public function getIssueStats(): array
    {
        return [
            'total' => SeoIssue::count(),
            'open' => SeoIssue::open()->count(),
            'critical' => SeoIssue::critical()->open()->count(),
            'warning' => SeoIssue::bySeverity('warning')->open()->count(),
            'resolved_this_week' => SeoIssue::where('status', 'resolved')
                ->where('resolved_at', '>=', Carbon::now()->subDays(7))
                ->count(),
        ];
    }
}
