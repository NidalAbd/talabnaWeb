<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\point_purchase_requests;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class dashboard extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Get categories for mapping
        $categories = Categories::all();
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

        // Get level IDs for badge counting
        $goldLevel = Level::where('name->ar', 'ذهبي')->first();
        $diamondLevel = Level::where('name->ar', 'ماسي')->first();
        $regularLevel = Level::where('name->ar', 'عادي')->first();
        
        // Badge counts
        $allGolden = $goldLevel ? ServicePost::where('level_id', $goldLevel->id)->count() : 0;
        $allDiamond = $diamondLevel ? ServicePost::where('level_id', $diamondLevel->id)->count() : 0;
        $allNormal = $regularLevel ? ServicePost::where('level_id', $regularLevel->id)->count() : 0;

        // Count purchase requests
        $purchaseRequests = point_purchase_requests::count();

        return view('admin.statistics', compact(
            'allDiamond', 'allNormal', 'allGolden',
            'user', 'allService', 'allPhone', 'allCar',
            'allJobs', 'allRealState', 'allGeneral',
            'purchaseRequests'
        ));
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
