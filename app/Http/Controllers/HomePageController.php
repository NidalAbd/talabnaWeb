<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\point_purchase_requests;
use App\Models\ServicePost;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomePageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        $user = Auth::user();
        $users = DB::table('users')->count();
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
        return view('welcome', compact('allDiamond','allNormal','allGolden','users', 'user', 'allService','allPhone','allCar','allJobs','allRealState','allGeneral','purchaseRequests'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
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
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
