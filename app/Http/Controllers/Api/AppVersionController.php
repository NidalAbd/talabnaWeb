<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    /**
     * Public, unauthenticated — checked before login, so no auth:api here.
     */
    public function show(Request $request): JsonResponse
    {
        $platform = $request->query('platform', 'android');

        $version = AppVersion::where('platform', $platform)->first();

        if (!$version) {
            // No config for this platform — never block the app over a
            // missing row.
            return response()->json(['is_mandatory' => false]);
        }

        return response()->json([
            'platform' => $version->platform,
            'latest_version' => $version->latest_version,
            'latest_build_number' => $version->latest_build_number,
            'minimum_build_number' => $version->minimum_build_number,
            'is_mandatory' => $version->is_mandatory,
            'update_url' => $version->update_url,
            'message' => $version->message,
        ]);
    }

    /**
     * Admin-only update — bump minimum_build_number and flip is_mandatory
     * to force everyone below it to update on their next launch.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string'],
            'latest_version' => ['required', 'string'],
            'latest_build_number' => ['required', 'integer', 'min:1'],
            'minimum_build_number' => ['required', 'integer', 'min:1'],
            'is_mandatory' => ['required', 'boolean'],
            'update_url' => ['required', 'string', 'url'],
            'message' => ['nullable', 'string'],
        ]);

        $version = AppVersion::updateOrCreate(
            ['platform' => $validated['platform']],
            $validated
        );

        return response()->json(['app_version' => $version]);
    }
}
