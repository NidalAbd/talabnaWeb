<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ServicePost;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
class ReportController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view reports
        if ($user->hasPermission('report_index')) {
            $reportsQuery = Report::with('reportable')
                ->select('reportable_type', 'reportable_id', DB::raw('count(*) as total'))
                ->groupBy('reportable_type', 'reportable_id')
                ->orderBy(DB::raw('count(*)'), 'desc')
                ->get();

            // Manually create a paginator for the fetched data
            $perPage = 7;
            $currentPage = Paginator::resolveCurrentPage() ?: 1;
            $reports = new LengthAwarePaginator(
                $reportsQuery->forPage($currentPage, $perPage),
                $reportsQuery->count(),
                $perPage,
                $currentPage,
                ['path' => Paginator::resolveCurrentPath()]
            );

            return view('admin.report', compact('reports'));
        } else {
            abort(403, 'Unauthorized action.');
        }
    }


    public function store(Request $request, $reported, $reportedId)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $report = new Report;
        $report->user_id = auth()->user()->id;
        $report->reason = $request->reason;

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
                return back()->withErrors('Invalid report type');
        }

        $report->user_id = auth()->user()->id;

        $report->save();

        return back()->with('success', 'Report submitted successfully' .$report);
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
