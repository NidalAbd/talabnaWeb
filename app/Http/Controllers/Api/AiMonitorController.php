<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRequest;
use App\Services\Ai\AiLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Admin view of every paid AI action: what was charged, what failed, what was refunded, and what needs attention. */
class AiMonitorController extends Controller
{
    /** GET /api/admin/ai/summary?hours=24 */
    public function summary(Request $request)
    {
        $since = now()->subHours(max(1, min(24 * 90, (int) $request->get('hours', 24))));

        $byFeature = AiRequest::where('created_at', '>=', $since)
            ->selectRaw("feature,
                COUNT(*) as requests,
                SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END) as succeeded,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'refund_failed' THEN 1 ELSE 0 END) as refund_failed,
                SUM(CASE WHEN status = 'succeeded' THEN points ELSE 0 END) as points_earned,
                SUM(CASE WHEN refunded_at IS NOT NULL THEN points ELSE 0 END) as points_refunded")
            ->groupBy('feature')->get();

        $overdue = AiRequest::where('status', AiRequest::PROCESSING)->where('created_at', '<', now()->subMinutes(30))->count();

        return response()->json([
            'period_hours' => (int) $request->get('hours', 24),
            'by_feature' => $byFeature,
            'totals' => [
                'requests' => (int) $byFeature->sum('requests'),
                'succeeded' => (int) $byFeature->sum('succeeded'),
                'failed' => (int) $byFeature->sum('failed'),
                'points_earned' => (int) $byFeature->sum('points_earned'),
                'points_refunded' => (int) $byFeature->sum('points_refunded'),
            ],
            'attention' => [
                'refund_failed' => AiRequest::where('status', AiRequest::REFUND_FAILED)->count(),
                'overdue_processing' => $overdue,
            ],
            'integrity' => $this->integrity(),
        ]);
    }

    /** GET /api/admin/ai/requests?status=&feature=&user_id=&page= */
    public function index(Request $request)
    {
        $q = AiRequest::with('user:id,name,user_name,email')->latest('id');
        foreach (['status', 'feature', 'user_id'] as $f) {
            if ($request->filled($f)) {
                $q->where($f, $request->get($f));
            }
        }

        return response()->json($q->paginate(min(100, (int) $request->get('per_page', 30))));
    }

    /** POST /api/admin/ai/requests/{id}/refund - give the points back for a request that needs it (refund_failed, stuck, complaint). */
    public function refund(Request $request, int $id, AiLedger $ledger)
    {
        $ai = AiRequest::findOrFail($id);
        if ($ai->refunded_at) {
            return response()->json(['error' => 'Already refunded.'], 409);
        }
        if ($ai->status === AiRequest::SUCCEEDED) {
            // A delivered result being refunded is a deliberate admin decision (e.g. the user complained).
            $ai->update(['status' => AiRequest::PROCESSING]);
        }
        $ledger->fail($ai, 'admin_refund', 'Refunded by an administrator.');
        $ai->refresh();

        return response()->json(['status' => $ai->status, 'refunded' => $ai->refunded_at !== null], $ai->refunded_at ? 200 : 500);
    }

    /**
     * Cross-check the two records of every charge. All numbers should be 0.
     *  - charge_without_request: a "used"/ai ledger row that has no ai_requests row
     *  - failed_without_refund : a failed request whose points never came back
     *  - refunded_twice        : more than one refund ledger row for the same request
     */
    private function integrity(): array
    {
        $chargesWithoutRequest = DB::table('point_transactions as t')
            ->where('t.type', 'used')->where('t.metadata', 'like', '%"reason":"ai"%')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('ai_requests as r')->whereColumn('r.charge_transaction_id', 't.id'))
            ->count();

        $failedWithoutRefund = AiRequest::where('status', AiRequest::FAILED)->where('points', '>', 0)->whereNull('refund_transaction_id')->count();

        $refundedTwice = DB::table('point_transactions')->where('metadata', 'like', '%"reason":"ai_failed"%')
            ->selectRaw('metadata')->get()
            ->map(fn ($r) => json_decode($r->metadata, true)['request'] ?? null)->filter()
            ->countBy()->filter(fn ($n) => $n > 1)->count();

        return [
            'charge_without_request' => $chargesWithoutRequest,
            'failed_without_refund' => $failedWithoutRefund,
            'refunded_twice' => $refundedTwice,
        ];
    }
}
