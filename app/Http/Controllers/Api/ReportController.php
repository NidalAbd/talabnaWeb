<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;


class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $reported, $reportedId , $reason)
    {
//        Log::info("Request: {$reported}");
//        Log::info("Request: {$reportedId}");
//        Log::info("Request: {$reason}");
        try {

            $report = new Report;
            $report->user_id = auth()->user()->id;
            $report->reason = $reason;
            switch ($reported) {
                case 'user':
                    $user = User::findOrFail($reportedId);
                    $report->reportable_id = $user->id;
                    $report->reportable_type = User::class;
                    break;
                case 'service_post':
                    $servicePost = ServicePost::findOrFail($reportedId);
                    $servicePost->report_count = $servicePost->report_count +1;
                    $report->reportable_id = $servicePost->id;
                    $report->reportable_type = ServicePost::class;
                    $servicePost->save();
                    break;
                default:
                    return response()->json(['error' => false, 'Invalid report type' => $report]);
            }
            $report->user_id = auth()->user()->id;
            $report->save();
            $message = json_encode([
                'en' => "We’ve received your report. Our team will review it shortly.",
                'ar' => "لقد استلمنا بلاغك. سيقوم فريقنا بمراجعته قريبًا."
            ]);

            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type'    => 'report',
                // Tapping it opens "My reports" on this report (2026-10-05).
                'target_type' => 'report',
                'target_id' => $report->id,
            ]);

            $notification->save();
            return response()->json(['success' => true, 'report' => $report]);
        }
        catch (\Exception $exception) {
            Log::info("Exception: {$exception}");
            return response()->json(['error' => true, 'Exception' => $exception->getMessage()]);
        }
    }

    /** GET /api/reports/mine — what I reported, its status, and whether I can still withdraw it. */
    public function mine(): \Illuminate\Http\JsonResponse
    {
        $labels = [
            'Spam' => ['en' => 'Spam', 'ar' => 'محتوى مزعج'],
            'inappropriate content' => ['en' => 'Inappropriate content', 'ar' => 'محتوى غير لائق'],
            'Harassment' => ['en' => 'Harassment', 'ar' => 'مضايقة'],
            'false information' => ['en' => 'False information', 'ar' => 'معلومات كاذبة'],
        ];
        $reports = Report::where('user_id', Auth::id())->orderByDesc('id')->limit(100)->get();
        $data = $reports->map(function (Report $r) use ($labels) {
            $isPost = $r->reportable_type === ServicePost::class;
            $target = $isPost ? ServicePost::with('photos')->find($r->reportable_id) : User::with('photos')->find($r->reportable_id);
            $title = null;
            if ($target && $isPost) {
                $t = is_string($target->title) ? json_decode($target->title, true) : $target->title;
                $title = is_array($t) ? ($t[app()->getLocale()] ?? $t['en'] ?? $t['ar'] ?? reset($t)) : $target->title;
            } elseif ($target) {
                $title = $target->name ?: $target->user_name;
            }
            $reason = (string) $r->reason;
            return [
                'id' => $r->id,
                'type' => $isPost ? 'post' : 'user',
                'target_id' => $r->reportable_id,
                'target_available' => (bool) $target,
                'title' => $title,
                'photo' => optional($target?->photos?->first())->src,
                'reason' => $labels[$reason][app()->getLocale()] ?? $labels[$reason]['en'] ?? $reason,
                // Stable key; the app shows it through its own translations (all languages).
                'reason_key' => ['Spam' => 'spam', 'inappropriate content' => 'inappropriate', 'Harassment' => 'harassment', 'false information' => 'false_info'][$reason] ?? 'other',
                'status' => $r->status ?? 'pending',
                'can_withdraw' => ($r->status ?? 'pending') === 'pending',
                'created_at' => $r->created_at?->toIso8601String(),
            ];
        });

        return response()->json(['reports' => $data]);
    }

    /** DELETE /api/reports/{id} — withdraw my own report while it is still pending. */
    public function withdraw($id): \Illuminate\Http\JsonResponse
    {
        $report = Report::where('id', $id)->where('user_id', Auth::id())->first();
        if (! $report) {
            return response()->json(['success' => false, 'message' => 'Report not found'], 404);
        }
        if (($report->status ?? 'pending') !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This report was already reviewed'], 422);
        }
        if ($report->reportable_type === ServicePost::class) {
            ServicePost::where('id', $report->reportable_id)->where('report_count', '>', 0)->decrement('report_count');
        }
        $report->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
