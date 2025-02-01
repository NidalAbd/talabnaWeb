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
        Log::info("Request: {$reported}");
        Log::info("Request: {$reportedId}");
        Log::info("Request: {$reason}");
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
            $message = "Your report has been received. We will take the necessary actions after verification.";
            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type'    => 'report'
            ]);
            $notification->save();
            return response()->json(['success' => true, 'report' => $report]);
        }
        catch (\Exception $exception) {
            Log::info("Exception: {$exception}");
            return response()->json(['error' => true, 'Exception' => $exception->getMessage()]);
        }
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
