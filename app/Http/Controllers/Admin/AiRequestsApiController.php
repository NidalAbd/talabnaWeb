<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin: every paid AI action (image, video, text tools) with its outcome - charged, succeeded, failed and refunded,
 * or stuck - so a "points were taken and no image came" complaint can be checked in seconds.
 */
class AiRequestsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = AiRequest::query()->with('user:id,name,user_name,email')
            ->select(['id', 'uuid', 'user_id', 'feature', 'points', 'status', 'charge_transaction_id', 'refund_transaction_id',
                'refund_attempts', 'provider', 'error_code', 'error_message', 'duration_ms', 'completed_at', 'refunded_at', 'created_at']);
        if ($s = $request->input('status')) $q->where('status', $s);
        if ($f = $request->input('feature')) $q->where('feature', $f);
        if ($from = $request->input('from')) $q->where('created_at', '>=', $from);
        if ($to = $request->input('to')) $q->where('created_at', '<=', $to.' 23:59:59');
        if ($search = trim((string) $request->input('search'))) {
            $q->where(function ($w) use ($search) {
                if (ctype_digit($search)) $w->orWhere('user_id', (int) $search)->orWhere('id', (int) $search);
                $w->orWhere('uuid', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        return response()->json($q->orderByDesc('id')->paginate(min(100, (int) $request->input('per_page', 25))));
    }

    public function stats(Request $request): JsonResponse
    {
        $days = in_array((int) $request->input('days'), [1, 7, 30, 90], true) ? (int) $request->input('days') : 7;
        $base = AiRequest::where('created_at', '>=', now()->subDays($days));

        $byStatus = (clone $base)->select('status', DB::raw('count(*) n'))->groupBy('status')->pluck('n', 'status');
        $byFeature = (clone $base)->select('feature',
                DB::raw('count(*) total'),
                DB::raw("sum(status = 'succeeded') succeeded"),
                DB::raw("sum(status = 'failed') failed"),
                DB::raw("sum(status = 'refund_failed') refund_failed"),
                DB::raw("sum(case when status = 'succeeded' then points else 0 end) points_earned"),
                DB::raw("sum(case when status = 'failed' then points else 0 end) points_refunded"))
            ->groupBy('feature')->orderByDesc('total')->get();
        $topErrors = (clone $base)->whereIn('status', [AiRequest::FAILED, AiRequest::REFUND_FAILED])
            ->select(DB::raw('coalesce(error_code, status) reason'), DB::raw('count(*) n'))
            ->groupBy('reason')->orderByDesc('n')->limit(8)->get();

        return response()->json([
            'days' => $days,
            'total' => (int) $byStatus->sum(),
            'by_status' => $byStatus,
            'by_feature' => $byFeature,
            'top_errors' => $topErrors,
            // Still running after their time limit - the settler should close these within a minute.
            'stuck_now' => AiRequest::where('status', AiRequest::PROCESSING)->where('created_at', '<=', now()->subMinutes(10))->count(),
            // Failed AND the points could not be returned: an admin must fix these by hand.
            'needs_admin' => AiRequest::where('status', AiRequest::REFUND_FAILED)->count(),
        ]);
    }
}
