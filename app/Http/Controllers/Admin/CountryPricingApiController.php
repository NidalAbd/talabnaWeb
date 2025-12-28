<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'exchange_rate_to_usd',
                'use_custom_price',
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
                'exchange_rate_to_usd' => 'nullable|numeric|min:0',
                'use_custom_price' => 'nullable|boolean',
                'allow_point_transfers' => 'nullable|boolean',
            ]);

            $updateData = [];

            if (isset($validated['currency_code'])) {
                $updateData['currency_code'] = $validated['currency_code'];
            }
            if (isset($validated['currency_symbol'])) {
                $updateData['currency_symbol'] = $validated['currency_symbol'];
            }
            if (isset($validated['price_per_point'])) {
                $updateData['price_per_point'] = $validated['price_per_point'];
            }
            if (isset($validated['exchange_rate_to_usd'])) {
                $updateData['exchange_rate_to_usd'] = $validated['exchange_rate_to_usd'];
            }
            if (isset($validated['use_custom_price'])) {
                $updateData['use_custom_price'] = $validated['use_custom_price'];
            }
            if (isset($validated['allow_point_transfers'])) {
                $updateData['allow_point_transfers'] = $validated['allow_point_transfers'];
            }

            if (!empty($updateData)) {
                $country->update($updateData);
            }

            $countryName = is_array($country->name) ? ($country->name['en'] ?? 'Unknown') : $country->name;
            Log::info("Country pricing updated for {$countryName} (ID: {$country->id})");

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
     * Apply base USD price to all countries (converts using exchange rate)
     */
    public function applyBasePrice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'base_price_usd' => 'required|numeric|min:0.001',
                'country_ids' => 'nullable|array',
                'country_ids.*' => 'integer|exists:countries,id',
                'override_custom' => 'nullable|boolean',
            ]);

            $basePriceUsd = $validated['base_price_usd'];
            $countryIds = $validated['country_ids'] ?? null;
            $overrideCustom = $validated['override_custom'] ?? false;

            $query = countries::query();

            // Filter by specific countries if provided
            if ($countryIds && count($countryIds) > 0) {
                $query->whereIn('id', $countryIds);
            }

            // Skip custom priced countries unless override is true
            if (!$overrideCustom) {
                $query->where('use_custom_price', false);
            }

            $countries = $query->get();
            $updatedCount = 0;

            DB::beginTransaction();

            foreach ($countries as $country) {
                $exchangeRate = $country->exchange_rate_to_usd ?: 1;
                $localPrice = round($basePriceUsd * $exchangeRate, 2);

                $country->update([
                    'price_per_point' => $localPrice,
                    'use_custom_price' => false,
                ]);

                $updatedCount++;
            }

            DB::commit();

            Log::info("Applied base price USD {$basePriceUsd} to {$updatedCount} countries");

            return response()->json([
                'message' => "Successfully updated {$updatedCount} countries",
                'base_price_usd' => $basePriceUsd,
                'updated_count' => $updatedCount,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying base price: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to apply base price',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update exchange rate for a country
     */
    public function updateExchangeRate(Request $request, $id): JsonResponse
    {
        try {
            $country = countries::findOrFail($id);

            $validated = $request->validate([
                'exchange_rate_to_usd' => 'required|numeric|min:0.000001',
            ]);

            $country->update([
                'exchange_rate_to_usd' => $validated['exchange_rate_to_usd'],
            ]);

            $countryName = is_array($country->name) ? ($country->name['en'] ?? 'Unknown') : $country->name;
            Log::info("Exchange rate updated for {$countryName} to {$validated['exchange_rate_to_usd']}");

            return response()->json([
                'message' => 'Exchange rate updated successfully',
                'country' => $country->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating exchange rate: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update exchange rate',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark country as using custom price
     */
    public function setCustomPrice(Request $request, $id): JsonResponse
    {
        try {
            $country = countries::findOrFail($id);

            $validated = $request->validate([
                'price_per_point' => 'required|numeric|min:0',
            ]);

            $country->update([
                'price_per_point' => $validated['price_per_point'],
                'use_custom_price' => true,
            ]);

            $countryName = is_array($country->name) ? ($country->name['en'] ?? 'Unknown') : $country->name;
            Log::info("Custom price set for {$countryName}: {$validated['price_per_point']}");

            return response()->json([
                'message' => 'Custom price set successfully',
                'country' => $country->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting custom price: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to set custom price',
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
            $countryName = is_array($country->name) ? ($country->name['en'] ?? 'Unknown') : $country->name;
            Log::info("Point transfers {$status} for {$countryName} (ID: {$country->id})");

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
