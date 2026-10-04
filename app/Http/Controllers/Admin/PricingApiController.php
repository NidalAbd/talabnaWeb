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
            // How phone / WhatsApp numbers are verified (2026-10-04). WhatsApp (Meta Cloud API)
            // becomes selectable once WHATSAPP_CLOUD_TOKEN is configured.
            'verification' => [
                'method' => AppSetting::get('verification.method', 'sms'),
                'codes_per_day' => (int) AppSetting::get('verification.codes_per_day', 5),
                'change_cooldown_days' => (int) AppSetting::get('verification.change_cooldown_days', 30),
                'whatsapp_available' => (bool) config('services.whatsapp_cloud.token'),
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
            'verification.method' => 'nullable|in:sms,whatsapp',
            'verification.codes_per_day' => 'nullable|integer|min:1|max:10',
            'verification.change_cooldown_days' => 'nullable|integer|min:1|max:365',
        ]);
        if (($data['verification']['method'] ?? 'sms') === 'whatsapp' && ! config('services.whatsapp_cloud.token')) {
            return response()->json(['message' => 'WhatsApp verification needs a Meta WhatsApp Business account first.'], 422);
        }
        foreach ($data['ai_features'] ?? [] as $f) {
            AiFeature::where('key', $f['key'])->update(['points_cost' => $f['points_cost'], 'enabled' => $f['enabled']]);
        }
        foreach (['free', 'max', 'extra_points'] as $k) {
            AppSetting::put("media.$k", $data['media'][$k]);
        }
        AppSetting::put('ai.daily_media_per_user', $data['limits']['daily_media_per_user']);
        AppSetting::put('ai.confirm_from', $data['limits']['confirm_from'] ?? 1);
        if (isset($data['verification'])) {
            AppSetting::put('verification.method', $data['verification']['method'] ?? 'sms');
            AppSetting::put('verification.codes_per_day', $data['verification']['codes_per_day'] ?? 5);
            AppSetting::put('verification.change_cooldown_days', $data['verification']['change_cooldown_days'] ?? 30);
        }
        return $this->index();
    }
}
