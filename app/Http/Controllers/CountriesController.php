<?php

namespace App\Http\Controllers;

use App\Models\cities;
use App\Models\countries;
use App\Http\Requests\StorecountriesRequest;
use App\Http\Requests\UpdatecountriesRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class CountriesController extends Controller
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
    public function CountriesSelected( $countries): JsonResponse
    {
        $cities = cities::where('country_id', $countries)->get();
        return response()->json(['cities' => $cities]);
    }
    public function countryList(): JsonResponse
    {
        $countries = countries::all();
        return response()->json(compact('countries'));
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
     * @param  \App\Http\Requests\StorecountriesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorecountriesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\countries  $countries
     * @return \Illuminate\Http\Response
     */
    public function show(countries $countries)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\countries  $countries
     * @return \Illuminate\Http\Response
     */
    public function edit(countries $countries)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatecountriesRequest  $request
     * @param  \App\Models\countries  $countries
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatecountriesRequest $request, countries $countries)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\countries  $countries
     * @return \Illuminate\Http\Response
     */
    public function destroy(countries $countries)
    {
        //
    }
}
