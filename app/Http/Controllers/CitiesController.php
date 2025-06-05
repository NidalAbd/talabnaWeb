<?php

namespace App\Http\Controllers;

use App\Models\cities;
use App\Models\countries;
use App\Models\Photos;
use App\Http\Requests\StorecityRequest;
use App\Http\Requests\UpdatecityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $cities = cities::with('country')
            ->paginate(10);

        return view('cities.index', compact('cities'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $countries = countries::all();
        return view('cities.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorecityRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorecityRequest $request)
    {
        try {
            $city = new cities();
            $city->name = [
                'en' => $request->name_en,
                'ar' => $request->name_ar
            ];
            $city->country_id = $request->country_id;

            $city->save();

            // Handle image upload
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = 'uploads/cities/';
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path($path), $fileName);

                $photo = new Photos();
                $photo->src = $path . $fileName;
                $photo->photoable_id = $city->id;
                $photo->photoable_type = cities::class;
                $photo->save();
            }

            return redirect()->route('cities.create')->with('success', 'City created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating city: ' . $e->getMessage());
            return redirect()->route('cities.create')
                ->with('error', 'Error creating city: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $city = cities::with('country')->findOrFail($id);
        return view('cities.show', compact('city'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return array
     */
    private function getNameArray($city) {
        if (is_string($city->name)) {
            return json_decode($city->name, true) ?: ['en' => '', 'ar' => ''];
        }
        return is_array($city->name) ? $city->name : ['en' => '', 'ar' => ''];
    }

    public function edit($id) {
        $city = cities::findOrFail($id);
        $countries = countries::all();
        $nameArray = $this->getNameArray($city);

        return view('cities.edit', compact('city', 'countries', 'nameArray'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatecityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatecityRequest $request, $id)
    {
        try {
            $city = cities::findOrFail($id);

            $city->name = [
                'en' => $request->name_en,
                'ar' => $request->name_ar
            ];
            $city->country_id = $request->country_id;

            $city->save();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Remove old image if exists
                if ($city->photos && $city->photos->count() > 0) {
                    foreach($city->photos as $photo) {
                        if (file_exists(public_path($photo->src))) {
                            unlink(public_path($photo->src));
                        }
                        $photo->delete();
                    }
                }

                $file = $request->file('image');
                $path = 'uploads/cities/';
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path($path), $fileName);

                $photo = new Photos();
                $photo->src = $path . $fileName;
                $photo->photoable_id = $city->id;
                $photo->photoable_type = cities::class;
                $photo->save();
            }

            return redirect()->route('cities.index')
                ->with('success', 'City updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating city: ' . $e->getMessage());
            return redirect()->route('cities.edit', $id)
                ->with('error', 'Error updating city: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $city = cities::findOrFail($id);

            // Delete associated photos
            if ($city->photos && $city->photos->count() > 0) {
                foreach($city->photos as $photo) {
                    if (file_exists(public_path($photo->src))) {
                        unlink(public_path($photo->src));
                    }
                }
            }

            $city->delete();

            return redirect()->route('cities.index')
                ->with('success', 'City deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting city: ' . $e->getMessage());
            return redirect()->route('cities.index')
                ->with('error', 'Error deleting city: ' . $e->getMessage());
        }
    }
}
