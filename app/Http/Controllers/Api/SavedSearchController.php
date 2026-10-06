<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use App\Services\PostSearch;
use App\Services\SavedSearchAlerts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Release A: saved searches (list, save, mute, delete) and running one. */
class SavedSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        [$limit, $instant] = SavedSearchAlerts::allowance($uid);

        return response()->json([
            'saved_searches' => SavedSearch::where('user_id', $uid)->latest()->get(),
            'limit' => $limit,
            'instant' => $instant,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'query' => 'nullable|string|max:120',
            'filters' => 'nullable|array',
            'filters.label' => 'nullable|string|max:120',
        ]);
        $uid = $request->user()->id;
        $filters = PostSearch::cleanFilters($data['filters'] ?? []);
        if (! empty($data['filters']['label'])) {
            $filters['label'] = $data['filters']['label'];
        }
        $query = trim((string) ($data['query'] ?? ''));
        if ($query === '' && PostSearch::cleanFilters($filters) === []) {
            return response()->json(['message' => 'Search for something first.', 'code' => 'empty'], 422);
        }
        // Saving the same search twice keeps one.
        $existing = SavedSearch::where('user_id', $uid)->where('query', $query ?: null)->get()
            ->first(fn ($s) => ($s->filters ?? []) == $filters);
        if ($existing) {
            return response()->json(['saved_search' => $existing, 'duplicate' => true]);
        }
        [$limit] = SavedSearchAlerts::allowance($uid);
        if ($limit > 0 && SavedSearch::where('user_id', $uid)->count() >= $limit) {
            return response()->json(['message' => "You can keep {$limit} saved searches on your plan.", 'code' => 'limit', 'limit' => $limit], 422);
        }
        $search = SavedSearch::create(['user_id' => $uid, 'query' => $query ?: null, 'filters' => $filters, 'last_checked_at' => now()]);

        return response()->json(['saved_search' => $search], 201);
    }

    public function update(Request $request, SavedSearch $savedSearch): JsonResponse
    {
        abort_unless((int) $savedSearch->user_id === (int) $request->user()->id, 404);
        $data = $request->validate(['muted' => 'required|boolean']);
        $savedSearch->update(['muted' => $data['muted']]);

        return response()->json(['saved_search' => $savedSearch]);
    }

    public function destroy(Request $request, SavedSearch $savedSearch): JsonResponse
    {
        abort_unless((int) $savedSearch->user_id === (int) $request->user()->id, 404);
        $savedSearch->delete();

        return response()->json(['ok' => true]);
    }
}
