<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\cities;
use App\Models\countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CitiesApiController extends Controller
{
    /**
     * Get all cities with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = cities::with(['photos', 'country'])
            ->select('cities.*');

        // Search in both AR and EN names
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(cities.name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(cities.name, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by country
        if ($request->has('country_id') && $request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'name':
                $query->orderByRaw("JSON_EXTRACT(cities.name, '$.en') {$sortOrder}");
                break;
            case 'country':
                $query->join('countries', 'cities.country_id', '=', 'countries.id')
                      ->orderByRaw("JSON_EXTRACT(countries.name, '$.en') {$sortOrder}");
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $cities = $query->paginate($perPage);

        // Transform data to include image URL and ensure name is properly formatted
        $cities->getCollection()->transform(function ($city) {
            $city->image_url = $city->photos->first()?->src ?? 'countryFlag/placeholder-flag.jpg';

            // Ensure name is properly decoded as array/object
            if (is_string($city->name)) {
                $city->name = json_decode($city->name, true) ?: ['en' => '', 'ar' => ''];
            }

            // Ensure country name is also properly formatted
            if ($city->country && is_string($city->country->name)) {
                $city->country->name = json_decode($city->country->name, true) ?: ['en' => '', 'ar' => ''];
            }

            unset($city->photos);
            return $city;
        });

        return response()->json($cities);
    }

    /**
     * Get statistics for cities
     */
    public function getStats(): JsonResponse
    {
        $total = cities::count();
        $totalCountries = countries::count();
        $countriesWithCities = countries::has('cities')->count();
        $avgCitiesPerCountry = $totalCountries > 0 ? round($total / $totalCountries, 1) : 0;

        $stats = [
            [
                'label' => 'Total Cities',
                'value' => $total,
                'icon' => 'fas fa-city',
                'color' => 'info'
            ],
            [
                'label' => 'Total Countries',
                'value' => $totalCountries,
                'icon' => 'fas fa-globe',
                'color' => 'success'
            ],
            [
                'label' => 'Countries with Cities',
                'value' => $countriesWithCities,
                'icon' => 'fas fa-map-marked-alt',
                'color' => 'warning'
            ],
            [
                'label' => 'Avg Cities/Country',
                'value' => $avgCitiesPerCountry,
                'icon' => 'fas fa-chart-line',
                'color' => 'primary'
            ]
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get single city by ID
     */
    public function show($id): JsonResponse
    {
        $city = cities::with(['photos', 'country'])
            ->findOrFail($id);

        $city->image_url = $city->photos->first()?->url ?? 'countryFlag/placeholder-flag.jpg';

        return response()->json($city);
    }

    /**
     * Create new city
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $city = cities::create([
            'name' => [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ],
            'country_id' => $request->country_id
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('cities/images', 'public');

            $city->photos()->create([
                'src' => $path
            ]);
        }

        $city->load(['photos', 'country']);
        $city->image_url = $city->photos->first()?->url ?? 'countryFlag/placeholder-flag.jpg';

        return response()->json([
            'message' => 'City created successfully',
            'city' => $city
        ], 201);
    }

    /**
     * Update existing city
     */
    public function update(Request $request, $id): JsonResponse
    {
        $city = cities::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name.ar' => 'sometimes|required|string|max:255',
            'name.en' => 'sometimes|required|string|max:255',
            'country_id' => 'sometimes|required|exists:countries,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update name if provided
        if ($request->has('name')) {
            $city->name = [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ];
        }

        // Update country if provided
        if ($request->has('country_id')) {
            $city->country_id = $request->country_id;
        }

        $city->save();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($city->photos()->exists()) {
                $oldImage = $city->photos()->first();
                if ($oldImage && \Storage::disk('public')->exists($oldImage->src)) {
                    \Storage::disk('public')->delete($oldImage->src);
                }
                $oldImage->delete();
            }

            // Upload new image
            $image = $request->file('image');
            $path = $image->store('cities/images', 'public');

            $city->photos()->create([
                'src' => $path
            ]);
        }

        $city->load(['photos', 'country']);
        $city->image_url = $city->photos->first()?->url ?? 'countryFlag/placeholder-flag.jpg';

        return response()->json([
            'message' => 'City updated successfully',
            'city' => $city
        ]);
    }

    /**
     * Delete city
     */
    public function destroy($id): JsonResponse
    {
        $city = cities::findOrFail($id);

        // Delete associated image
        if ($city->photos()->exists()) {
            $image = $city->photos()->first();
            if ($image && \Storage::disk('public')->exists($image->src)) {
                \Storage::disk('public')->delete($image->src);
            }
            $image->delete();
        }

        $city->delete();

        return response()->json([
            'message' => 'City deleted successfully'
        ]);
    }
}
