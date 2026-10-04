<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionAddon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin: top-up packs (extra plan allowance until the period ends). */
class SubscriptionAddonController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'addons' => SubscriptionAddon::orderBy('sort_order')->orderBy('price_points')->get(),
            'features' => SubscriptionAddon::FEATURES,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $addon = SubscriptionAddon::create($this->validated($request));
        return response()->json(['addon' => $addon], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $addon = SubscriptionAddon::findOrFail($id);
        $addon->update($this->validated($request, $addon->id));
        return response()->json(['addon' => $addon]);
    }

    public function destroy(int $id): JsonResponse
    {
        SubscriptionAddon::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'slug' => ['required', 'string', 'max:64', Rule::unique('subscription_addons', 'slug')->ignore($id)],
            'name' => 'required|array',
            'name.en' => 'required|string|max:100',
            'name.ar' => 'required|string|max:100',
            'feature_key' => ['required', Rule::in(SubscriptionAddon::FEATURES)],
            'amount' => 'required|integer|min:1|max:1000',
            'price_points' => 'required|integer|min:0|max:100000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
    }
}
