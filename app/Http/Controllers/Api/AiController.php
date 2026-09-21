<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Models\AiFeature;
use App\Services\Ai\AiPointsCharger;
use App\Services\Ai\AiTextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** AI helpers for creating a post, paid in points. The price always comes from the ai_features table. */
class AiController extends Controller
{
    public function __construct(private AiPointsCharger $charger, private AiTextService $text)
    {
    }

    /** GET /api/ai/pricing - what each action costs, and the caller's balance. */
    public function pricing(Request $request): JsonResponse
    {
        $features = AiFeature::all();

        return response()->json([
            'version' => (int) optional($features->max('updated_at'))->timestamp,
            'features' => $features->mapWithKeys(fn (AiFeature $f) => [
                $f->key => ['points' => $f->points_cost, 'enabled' => $f->enabled],
            ]),
            'balance' => (int) DB::table('palservice_points')->where('user_id', $request->user()->id)->value('point'),
        ]);
    }

    /** POST /api/ai/enhance-text */
    public function enhanceText(Request $request): JsonResponse
    {
        $data = $request->validate([
            'feature' => 'required|in:enhance_title,enhance_description',
            'text' => 'required|string|min:2|max:2000',
            'language' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:120',
            'title' => 'nullable|string|max:200',
        ]);

        $feature = AiFeature::find($data['feature']);
        if (! $feature || ! $feature->enabled) {
            return response()->json(['error' => 'This AI feature is not available right now.'], 403);
        }
        if (! $this->text->isConfigured()) {
            return response()->json(['error' => 'AI is not available right now, try again later.'], 503);
        }

        $user = $request->user();
        $cost = $feature->points_cost;

        try {
            $chargeId = $this->charger->charge($user->id, $cost, $feature->key);
        } catch (InsufficientBalanceException $e) {
            return response()->json([
                'error' => 'Not enough points.',
                'required' => $cost,
                'balance' => $e->getCurrentBalance(),
            ], 402);
        }

        try {
            $improved = $this->text->enhance($feature->key, $data['text'], $data['language'] ?? '', $data['category'] ?? null, $data['title'] ?? null);
        } catch (\RuntimeException $e) {
            $this->charger->refund($user->id, $cost, $feature->key, $chargeId); // never charge for a failure

            return response()->json(['error' => 'The AI could not improve this text, you were not charged. Try again.'], 502);
        }

        return response()->json([
            'text' => $improved,
            'points_charged' => $cost,
            'balance' => (int) DB::table('palservice_points')->where('user_id', $user->id)->value('point'),
        ]);
    }
}
