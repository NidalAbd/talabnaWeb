<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Admin: who tried to buy, what happened, and where buying breaks. */
class PurchaseAttemptsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = PurchaseAttempt::query()->with('user:id,name,user_name,email');
        if ($s = $request->input('status')) $q->where('status', $s);
        if ($p = $request->input('platform')) $q->where('platform', $p);
        if ($prod = $request->input('product_id')) $q->where('product_id', $prod);
        if ($from = $request->input('from')) $q->where('created_at', '>=', $from);
        if ($to = $request->input('to')) $q->where('created_at', '<=', $to . ' 23:59:59');
        if ($search = trim((string) $request->input('search'))) {
            $q->where(function ($w) use ($search) {
                if (ctype_digit($search)) $w->orWhere('user_id', (int) $search)->orWhere('id', (int) $search);
                $w->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }
        return response()->json($q->orderByDesc('id')->paginate(min(100, (int) $request->input('per_page', 20))));
    }

    public function stats(Request $request): JsonResponse
    {
        $days = in_array((int) $request->input('days'), [1, 7, 30, 90], true) ? (int) $request->input('days') : 7;
        $base = PurchaseAttempt::where('created_at', '>=', now()->subDays($days))->where('platform', '!=', 'unknown');
        $counts = (clone $base)->select('status', DB::raw('count(*) n'))->groupBy('status')->pluck('n', 'status');
        $total = (int) $counts->sum();
        $completed = (int) ($counts['completed'] ?? 0);

        $byProduct = (clone $base)->select('product_id',
                DB::raw('count(*) attempts'),
                DB::raw("sum(status = 'completed') completed"),
                DB::raw("sum(status = 'cancelled') cancelled"),
                DB::raw("sum(status in ('failed','abandoned')) failed"))
            ->groupBy('product_id')->orderByDesc('attempts')->get();

        $byPlatform = (clone $base)->select('platform', DB::raw('count(*) attempts'), DB::raw("sum(status = 'completed') completed"))
            ->groupBy('platform')->get();

        $topErrors = (clone $base)->whereIn('status', ['failed', 'abandoned'])
            ->select(DB::raw("coalesce(error_code, error_message, status) reason"), DB::raw('count(*) n'))
            ->groupBy('reason')->orderByDesc('n')->limit(8)->get();

        // People who tried but never completed a purchase in the window — worth a follow-up.
        $triedNeverBought = (clone $base)->select('user_id')->groupBy('user_id')
            ->havingRaw("sum(status = 'completed') = 0")->get()->count();

        $open = PurchaseAttempt::whereIn('status', PurchaseAttempt::OPEN)
            ->where('created_at', '<=', now()->subMinutes(10))->count();

        return response()->json([
            'days' => $days,
            'total' => $total,
            'by_status' => $counts,
            'conversion_rate' => $total ? round($completed * 100 / $total, 1) : 0,
            'completed_without_attempt_log' => PurchaseAttempt::where('created_at', '>=', now()->subDays($days))->where('platform', 'unknown')->count(),
            'by_product' => $byProduct,
            'by_platform' => $byPlatform,
            'top_errors' => $topErrors,
            'tried_never_bought_users' => $triedNeverBought,
            'stuck_now' => $open,
        ]);
    }
}
