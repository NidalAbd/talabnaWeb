<?php

namespace App\Http\Controllers;

use App\Models\point_purchase_requests;
use App\Models\ServicePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class dashboard extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $user = DB::table('users')->count();
        $allService  = ServicePost::count();
        $allPhone  = ServicePost::where('category', 'Devices')->count();
        $allCar  = ServicePost::where('category', 'Cars')->count();
        $allJobs  = ServicePost::where('category', 'Jobs')->count();
        $allRealState  = ServicePost::where('category', 'Real Estate')->count();
        $allGeneral  = ServicePost::where('category', 'Services')->count();
        $allGolden  = ServicePost::where('have_badge', 'ذهبي')->count();
        $allDiamond  = ServicePost::where('have_badge', 'ماسي')->count();
        $allNormal  = ServicePost::where('have_badge', 'عادي')->count();
        $purchaseRequests = point_purchase_requests::count();
        return view('admin.statistics', compact('allDiamond','allNormal','allGolden','user', 'allService','allPhone','allCar','allJobs','allRealState','allGeneral','purchaseRequests'));

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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
