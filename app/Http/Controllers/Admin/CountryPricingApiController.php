<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CountryPricingApiController extends Controller
{
    /**
     * Get all countries with pricing info
     */
    public function index(): JsonResponse
    {
        try {
            $countries = countries::select([
                'id',
                'name',
                'country_code',
                'currency_code',
                'currency_name',
                'currency_symbol',
                'price_per_point',
                'allow_point_transfers',
            ])->orderBy('id')->get();

            return response()->json([
                'countries' => $countries,
            ]);
        } catch (\Exception $e) {
            Log::error('Country Pricing API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load countries',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update country pricing
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $country = countries::findOrFail($id);

            $validated = $request->validate([
                'currency_code' => 'nullable|string|max:10',
                'currency_symbol' => 'nullable|string|max:10',
                'price_per_point' => 'nullable|numeric|min:0',
                'allow_point_transfers' => 'nullable|boolean',
            ]);

            $country->update([
                'currency_code' => $validated['currency_code'] ?? $country->currency_code,
                'currency_symbol' => $validated['currency_symbol'] ?? $country->currency_symbol,
                'price_per_point' => $validated['price_per_point'] ?? $country->price_per_point,
                'allow_point_transfers' => $validated['allow_point_transfers'] ?? $country->allow_point_transfers,
            ]);

            Log::info("Country pricing updated for {$country->name['en']} (ID: {$country->id})");

            return response()->json([
                'message' => 'Country pricing updated successfully',
                'country' => $country->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating country pricing: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update country pricing',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle point transfers for a country
     */
    public function toggleTransfers($id): JsonResponse
    {
        try {
            $country = countries::findOrFail($id);

            $country->allow_point_transfers = !$country->allow_point_transfers;
            $country->save();

            $status = $country->allow_point_transfers ? 'enabled' : 'disabled';
            Log::info("Point transfers {$status} for {$country->name['en']} (ID: {$country->id})");

            return response()->json([
                'message' => "Point transfers {$status} successfully",
                'allow_point_transfers' => $country->allow_point_transfers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling transfers: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to toggle transfers',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
