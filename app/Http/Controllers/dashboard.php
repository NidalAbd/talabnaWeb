<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\point_purchase_requests;
use App\Models\ServicePost;
use App\Models\User;
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
        // Count users
        $user = User::count();

        // Get categories with more robust handling
        $categories = Categories::get();

        // Mapping of English names to category IDs
        $categoryMap = [
            'Devices' => $categories->first(fn($cat) =>
                isset($cat->name['en']) && $cat->name['en'] === 'Devices'
            )?->id,
            'Cars' => $categories->first(fn($cat) =>
                isset($cat->name['en']) && $cat->name['en'] === 'Cars'
            )?->id,
            'Jobs' => $categories->first(fn($cat) =>
                isset($cat->name['en']) && $cat->name['en'] === 'Jobs'
            )?->id,
            'Real Estate' => $categories->first(fn($cat) =>
                isset($cat->name['en']) && $cat->name['en'] === 'Real Estate'
            )?->id,
            'Services' => $categories->first(fn($cat) =>
                isset($cat->name['en']) && $cat->name['en'] === 'Services'
            )?->id,
        ];

        // Count service posts
        $allService = ServicePost::count();
        $allPhone = ServicePost::where('categories_id', $categoryMap['Devices'])->count();
        $allCar = ServicePost::where('categories_id', $categoryMap['Cars'])->count();
        $allJobs = ServicePost::where('categories_id', $categoryMap['Jobs'])->count();
        $allRealState = ServicePost::where('categories_id', $categoryMap['Real Estate'])->count();
        $allGeneral = ServicePost::where('categories_id', $categoryMap['Services'])->count();

        // Badge counts
        $allGolden = ServicePost::where('have_badge', 'ذهبي')->count();
        $allDiamond = ServicePost::where('have_badge', 'ماسي')->count();
        $allNormal = ServicePost::where('have_badge', 'عادي')->count();

        // Count purchase requests
        $purchaseRequests = point_purchase_requests::count();

        return view('admin.statistics', compact(
            'allDiamond', 'allNormal', 'allGolden',
            'user', 'allService', 'allPhone', 'allCar',
            'allJobs', 'allRealState', 'allGeneral',
            'purchaseRequests'
        ));
    }    /**
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
