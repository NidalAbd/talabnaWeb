<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountriesApiController extends Controller
{
    /**
     * Get all countries with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = countries::with(['photos'])
            ->withCount('cities');

        // Search in both AR and EN names
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhere('country_code', 'like', "%{$search}%")
                  ->orWhere('currency_code', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'name':
                $query->orderByRaw("JSON_EXTRACT(name, '$.en') {$sortOrder}");
                break;
            case 'cities':
                $query->orderBy('cities_count', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $countries = $query->paginate($perPage);

        // Transform data to include flag URL
        $countries->getCollection()->transform(function ($country) {
            $flagSrc = $country->photos->first()?->src ?? 'countryFlag/placeholder-flag.jpg';
            // Remove 'storage/' prefix if it exists to avoid double path
            $country->flag_url = preg_replace('#^storage/#', '', $flagSrc);
            unset($country->photos);
            return $country;
        });

        return response()->json($countries);
    }

    /**
     * Get statistics for countries
     */
    public function getStats(): JsonResponse
    {
        $total = countries::count();
        $withCities = countries::has('cities')->count();
        $totalCities = \DB::table('cities')->count();

        $stats = [
            [
                'label' => 'Total Countries',
                'value' => $total,
                'icon' => 'fas fa-globe',
                'color' => 'info'
            ],
            [
                'label' => 'Countries with Cities',
                'value' => $withCities,
                'icon' => 'fas fa-map-marked-alt',
                'color' => 'success'
            ],
            [
                'label' => 'Total Cities',
                'value' => $totalCities,
                'icon' => 'fas fa-city',
                'color' => 'warning'
            ],
            [
                'label' => 'Empty Countries',
                'value' => $total - $withCities,
                'icon' => 'fas fa-globe-americas',
                'color' => 'secondary'
            ]
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get single country by ID
     */
    public function show($id): JsonResponse
    {
        $country = countries::with(['photos', 'cities'])
            ->withCount('cities')
            ->findOrFail($id);

        $flagSrc = $country->photos->first()?->src ?? 'countryFlag/placeholder-flag.jpg';
        // Remove 'storage/' prefix if it exists to avoid double path
        $country->flag_url = preg_replace('#^storage/#', '', $flagSrc);

        return response()->json($country);
    }

    /**
     * Create new country
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'country_code' => 'required|string|max:10|unique:countries,country_code',
            'currency_code' => 'nullable|string|max:10',
            'currency_name.ar' => 'nullable|string|max:255',
            'currency_name.en' => 'nullable|string|max:255',
            'flag' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $country = countries::create([
            'name' => [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ],
            'country_code' => strtoupper($request->country_code),
            'currency_code' => $request->currency_code ? strtoupper($request->currency_code) : null,
            'currency_name' => [
                'ar' => $request->input('currency_name.ar', ''),
                'en' => $request->input('currency_name.en', '')
            ]
        ]);

        // Handle flag upload
        if ($request->hasFile('flag')) {
            $flag = $request->file('flag');
            $path = $flag->store('countryFlag', 'public');

            $country->photos()->create([
                'src' => $path
            ]);
        }

        $country->load('photos');
        $flagSrc = $country->photos->first()?->src ?? 'countryFlag/placeholder-flag.jpg';
        $country->flag_url = preg_replace('#^storage/#', '', $flagSrc);

        return response()->json([
            'message' => 'Country created successfully',
            'country' => $country
        ], 201);
    }

    /**
     * Update existing country
     */
    public function update(Request $request, $id): JsonResponse
    {
        $country = countries::findOrFail($id);

        // Build validation rules
        $rules = [
            'name.ar' => 'nullable|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:10|unique:countries,country_code,' . $id,
            'currency_code' => 'nullable|string|max:10',
            'currency_name.ar' => 'nullable|string|max:255',
            'currency_name.en' => 'nullable|string|max:255',
        ];

        // Only validate flag if file is present
        if ($request->hasFile('flag')) {
            $rules['flag'] = 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::error('Country update validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except('flag'),
                'has_flag' => $request->hasFile('flag')
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update name if provided
        if ($request->has('name')) {
            $country->name = [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ];
        }

        // Update other fields
        if ($request->has('country_code')) {
            $country->country_code = strtoupper($request->country_code);
        }

        if ($request->has('currency_code')) {
            $country->currency_code = $request->currency_code ? strtoupper($request->currency_code) : null;
        }

        if ($request->has('currency_name')) {
            $country->currency_name = [
                'ar' => $request->input('currency_name.ar', ''),
                'en' => $request->input('currency_name.en', '')
            ];
        }

        $country->save();

        // Handle flag upload
        if ($request->hasFile('flag')) {
            // Delete old flag if exists
            if ($country->photos()->exists()) {
                $oldFlag = $country->photos()->first();
                if ($oldFlag && \Storage::disk('public')->exists($oldFlag->src)) {
                    \Storage::disk('public')->delete($oldFlag->src);
                }
                $oldFlag->delete();
            }

            // Upload new flag
            $flag = $request->file('flag');
            $path = $flag->store('countryFlag', 'public');

            $country->photos()->create([
                'src' => $path
            ]);
        }

        $country->load('photos');
        $flagSrc = $country->photos->first()?->src ?? 'countryFlag/placeholder-flag.jpg';
        $country->flag_url = preg_replace('#^storage/#', '', $flagSrc);

        return response()->json([
            'message' => 'Country updated successfully',
            'country' => $country
        ]);
    }

    /**
     * Delete country
     */
    public function destroy($id): JsonResponse
    {
        $country = countries::findOrFail($id);

        // Check if country has cities
        if ($country->cities()->exists()) {
            return response()->json([
                'message' => 'Cannot delete country with existing cities'
            ], 400);
        }

        // Delete associated flag
        if ($country->photos()->exists()) {
            $flag = $country->photos()->first();
            if ($flag && \Storage::disk('public')->exists($flag->src)) {
                \Storage::disk('public')->delete($flag->src);
            }
            $flag->delete();
        }

        $country->delete();

        return response()->json([
            'message' => 'Country deleted successfully'
        ]);
    }
}
