<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiFeature;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Admin: every point price that isn't a plan or badge (those have their own pages). */
class PricingApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'ai_features' => AiFeature::orderBy('key')->get(['key', 'points_cost', 'enabled', 'description']),
            'media' => [
                'free' => (int) AppSetting::get('media.free', config('ai.media.free', 4)),
                'max' => (int) AppSetting::get('media.max', config('ai.media.max', 10)),
                'extra_points' => (int) AppSetting::get('media.extra_points', config('ai.media.extra_points', 1)),
            ],
            'limits' => [
                'daily_media_per_user' => (int) AppSetting::get('ai.daily_media_per_user', config('ai.limits.daily_media_per_user', 30)),
                'confirm_from' => max(1, (int) AppSetting::get('ai.confirm_from', 1)),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ai_features' => 'array',
            'ai_features.*.key' => 'required|string|exists:ai_features,key',
            'ai_features.*.points_cost' => 'required|integer|min:0|max:10000',
            'ai_features.*.enabled' => 'required|boolean',
            'media.free' => 'required|integer|min:0|max:50',
            'media.max' => 'required|integer|min:1|max:50|gte:media.free',
            'media.extra_points' => 'required|integer|min:0|max:1000',
            'limits.daily_media_per_user' => 'required|integer|min:1|max:500',
            'limits.confirm_from' => 'nullable|integer|min:1|max:1000',
        ]);
        foreach ($data['ai_features'] ?? [] as $f) {
            AiFeature::where('key', $f['key'])->update(['points_cost' => $f['points_cost'], 'enabled' => $f['enabled']]);
        }
        foreach (['free', 'max', 'extra_points'] as $k) {
            AppSetting::put("media.$k", $data['media'][$k]);
        }
        AppSetting::put('ai.daily_media_per_user', $data['limits']['daily_media_per_user']);
        AppSetting::put('ai.confirm_from', $data['limits']['confirm_from'] ?? 1);
        return $this->index();
    }
}
