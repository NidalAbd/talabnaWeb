<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Admin > Server errors (2026-10-07): the recent problems in storage/logs/laravel*.log, grouped by message, so they are
 * seen without SSH. Reads only the end of the newest files (a few MB). Tokens, keys and e-mail addresses are masked.
 */
class ServerErrorsController extends Controller
{
    private const LEVELS = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING'];

    private const READ_BYTES = 4 * 1024 * 1024;

    public function index(Request $request)
    {
        $level = in_array($request->query('level'), self::LEVELS, true) ? $request->query('level') : null;
        $hours = max(1, min(24 * 14, (int) $request->query('hours', 48)));
        $since = now()->subHours($hours);

        $groups = [];
        $files = collect(glob(storage_path('logs/laravel*.log')) ?: [])->sortByDesc(fn ($f) => filemtime($f))->take(3);
        foreach ($files as $file) {
            foreach ($this->entries($file) as $e) {
                if ($e['time'] < $since || ! in_array($e['level'], self::LEVELS, true)) {
                    continue;
                }
                if ($level && $e['level'] !== $level) {
                    continue;
                }
                $key = $e['level'].'|'.$this->signature($e['message']);
                $g = $groups[$key] ?? ['level' => $e['level'], 'message' => $e['message'], 'count' => 0, 'first' => $e['time'], 'last' => $e['time'], 'sample' => $e['detail']];
                $g['count']++;
                $g['first'] = min($g['first'], $e['time']);
                if ($e['time'] >= $g['last']) {
                    $g['last'] = $e['time'];
                    $g['sample'] = $e['detail'];
                    $g['message'] = $e['message'];
                }
                $groups[$key] = $g;
            }
        }
        $rank = array_flip(self::LEVELS);
        usort($groups, fn ($a, $b) => [$rank[$a['level']], $b['last']] <=> [$rank[$b['level']], $a['last']]);

        if ($request->wantsJson()) {
            return response()->json(['hours' => $hours, 'groups' => array_values($groups)]);
        }

        return view('admin.system.server-errors', ['groups' => $groups, 'hours' => $hours, 'level' => $level, 'levels' => self::LEVELS]);
    }

    /** @return iterable<array{time: \Carbon\Carbon, level: string, message: string, detail: string}> */
    private function entries(string $file): iterable
    {
        $size = filesize($file) ?: 0;
        $h = fopen($file, 'rb');
        if (! $h) {
            return;
        }
        fseek($h, max(0, $size - self::READ_BYTES));
        $text = (string) stream_get_contents($h);
        fclose($h);

        $parts = preg_split('/^(?=\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})/m', $text) ?: [];
        foreach ($parts as $part) {
            if (! preg_match('/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})[^\]]*\]\s+\w+\.(\w+):\s?(.*)$/s', $part, $m)) {
                continue;
            }
            [$first] = explode("\n", $m[3], 2) + [''];
            yield [
                'time' => \Carbon\Carbon::parse($m[1]),
                'level' => strtoupper($m[2]),
                'message' => $this->mask(mb_substr(trim($first), 0, 300)),
                'detail' => $this->mask(mb_substr(trim($m[3]), 0, 2500)),
            ];
        }
    }

    /** Same problem with different ids/numbers groups together. */
    private function signature(string $message): string
    {
        $s = preg_replace('/\{.*$/s', '', $message);
        $s = preg_replace('/[0-9a-f]{8}-[0-9a-f-]{27,}/i', '#', (string) $s);

        return (string) preg_replace('/\d+/', '#', (string) $s);
    }

    private function mask(string $text): string
    {
        $text = (string) preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '***@***', $text);
        $text = (string) preg_replace('/(Bearer\s+|token["\']?\s*[:=]\s*["\']?|key["\']?\s*[:=]\s*["\']?|sk-)[A-Za-z0-9._\-|]{12,}/i', '$1***', $text);

        return (string) preg_replace('/eyJ[A-Za-z0-9._\-]{20,}/', '***', $text);
    }
}
