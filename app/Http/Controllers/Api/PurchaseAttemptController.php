<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The app logs each Buy tap here, then reports how it ended. */
class PurchaseAttemptController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|string|max:64',
            'platform' => 'nullable|in:android,ios',
            'price' => 'nullable|numeric|min:0|max:100000',
            'currency' => 'nullable|string|max:8',
            'app_version' => 'nullable|string|max:20',
        ]);
        $attempt = PurchaseAttempt::create($data + ['user_id' => $request->user()->id]);
        return response()->json(['success' => true, 'id' => $attempt->id]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $attempt = PurchaseAttempt::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (!$attempt) return response()->json(['success' => false], 404);
        $data = $request->validate([
            'status' => 'required|in:pending,completed,cancelled,failed',
            'error_code' => 'nullable|string|max:64',
            'error_message' => 'nullable|string|max:500',
        ]);
        // A server-confirmed purchase is final.
        if ($attempt->status === 'completed') return response()->json(['success' => true]);
        $attempt->update($data + ['resolved_at' => $data['status'] === 'pending' ? null : now()]);
        return response()->json(['success' => true]);
    }
}
