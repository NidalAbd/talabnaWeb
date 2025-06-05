<?php

namespace App\Http\Controllers;

use App\Models\cities;
use App\Models\countries;
use App\Models\Photos;
use App\Http\Requests\StorecountriesRequest;
use App\Http\Requests\UpdatecountriesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;

class CountriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $countries = countries::withCount('cities')
            ->paginate(10);

        return view('countries.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('countries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorecountriesRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorecountriesRequest $request)
    {
        try {
            $country = new countries();
            $country->name = [
                'en' => $request->name_en,
                'ar' => $request->name_ar
            ];
            $country->country_code = $request->country_code;
            $country->currency_code = $request->currency_code;
            $country->currency_name = [
                'en' => $request->currency_name_en,
                'ar' => $request->currency_name_ar
            ];

            $country->save();

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');

                // Use country name for the filename to match your seeding format
                $extension = $image->getClientOriginalExtension();
                $fileName = strtolower($request->name_en) . '.' . $extension;

                // Store the image in the countryFlag directory
                $storeImage = $image->storeAs("countryFlag", $fileName, 'public');

                // Create the path for the database that includes 'storage/'
                $photoPath = 'storage/' . $storeImage;

                // Create and save the photo record
                $photo = new Photos([
                    'src' => $photoPath,
                ]);

                $country->photos()->save($photo);

                // Log success
                Log::info("Country image saved successfully at: " . $photoPath);
            }

            return redirect()->route('cities.create')->with('success', 'Country created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating country: ' . $e->getMessage());
            return redirect()->route('countries.create')
                ->with('error', 'Error creating country: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\countries  $country
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $country = countries::with('cities')->findOrFail($id);
        return view('countries.show', compact('country'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\countries  $country
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $country = countries::findOrFail($id);
        return view('countries.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatecountriesRequest  $request
     * @param  \App\Models\countries  $country
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatecountriesRequest $request, $id)
    {
        try {
            $country = countries::findOrFail($id);

            $country->name = [
                'en' => $request->name_en,
                'ar' => $request->name_ar
            ];
            $country->country_code = $request->country_code;
            $country->currency_code = $request->currency_code;
            $country->currency_name = [
                'en' => $request->currency_name_en,
                'ar' => $request->currency_name_ar
            ];

            $country->save();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete the old photo if it exists
                $oldPhoto = $country->photos()->first();
                if ($oldPhoto) {
                    // Remove 'storage/' from the path when deleting
                    $oldPath = str_replace('storage/', '', $oldPhoto->src);
                    Storage::disk('public')->delete($oldPath);
                    $oldPhoto->delete();
                }

                $image = $request->file('image');

                // Use country name for the filename to match your seeding format
                $extension = $image->getClientOriginalExtension();
                $fileName = strtolower($request->name_en) . '.' . $extension;

                // Store the image in the countryFlag directory
                $storeImage = $image->storeAs("countryFlag", $fileName, 'public');

                // Create the path for the database that includes 'storage/'
                $photoPath = 'storage/' . $storeImage;

                // Create and save the photo record
                $photo = new Photos([
                    'src' => $photoPath,
                ]);

                $country->photos()->save($photo);

                // Log success
                Log::info("Country image updated successfully at: " . $photoPath);
            }

            return redirect()->route('countries.index')
                ->with('success', 'Country updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating country: ' . $e->getMessage());
            return redirect()->route('countries.edit', $id)
                ->with('error', 'Error updating country: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\countries  $country
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $country = countries::findOrFail($id);

            // Delete associated photos
            $photo = $country->photos()->first();
            if ($photo) {
                // Remove 'storage/' from the path when deleting
                $oldPath = str_replace('storage/', '', $photo->src);
                Storage::disk('public')->delete($oldPath);
                $photo->delete();
            }

            $country->delete();

            return redirect()->route('countries.index')
                ->with('success', 'Country deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting country: ' . $e->getMessage());
            return redirect()->route('countries.index')
                ->with('error', 'Error deleting country: ' . $e->getMessage());
        }
    }

    public function CountriesSelected($countries): JsonResponse
    {
        $cities = cities::where('country_id', $countries)->get();
        return response()->json(['cities' => $cities]);
    }

    public function countryList(): JsonResponse
    {
        $countries = countries::all();
        return response()->json(compact('countries'));
    }
}
