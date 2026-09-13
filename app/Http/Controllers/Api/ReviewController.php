<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * Reviews left about $user (the Profile screen's "Reviews" tab),
     * newest first, with the reviewer's basic info attached.
     */
    public function index(User $user): JsonResponse
    {
        $reviews = $user->reviewsReceived()
            ->with(['reviewer' => function ($query) {
                $query->select('id', 'user_name', 'name')->with('photos');
            }])
            ->orderByDesc('created_at')
            ->paginate(15);

        $reviews->getCollection()->transform(function (Review $review) {
            return $this->formatReview($review);
        });

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round((float) $user->reviewsReceived()->avg('rating'), 2),
            'reviews_count' => $user->reviewsReceived()->count(),
        ]);
    }

    /**
     * Shapes a review's `reviewer` the same way `ConversationController`
     * shapes `other_user` ({id, user_name, name, avatar}) so the Flutter
     * app can reuse its existing `ChatUser` model for both.
     */
    private function formatReview(Review $review): array
    {
        $data = $review->toArray();
        $data['reviewer'] = $review->reviewer ? [
            'id' => $review->reviewer->id,
            'user_name' => $review->reviewer->user_name,
            'name' => $review->reviewer->name,
            'avatar' => optional($review->reviewer->photos->first())->src,
        ] : null;

        return $data;
    }

    /**
     * Leave a review for another user. One review per (reviewer, reviewed
     * user, listing) — see the unique index on the reviews table.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reviewed_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::notIn([Auth::id()]),
            ],
            'service_post_id' => ['nullable', 'integer', Rule::exists('service_posts', 'id')],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'reviewed_user_id.not_in' => 'You cannot review yourself.',
        ]);

        $alreadyReviewed = Review::where('reviewer_id', Auth::id())
            ->where('reviewed_user_id', $validated['reviewed_user_id'])
            ->where('service_post_id', $validated['service_post_id'] ?? null)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'error' => 'already_reviewed',
                'message' => 'You have already reviewed this user for this listing.',
            ], 409);
        }

        $review = Review::create([
            'reviewer_id' => Auth::id(),
            'reviewed_user_id' => $validated['reviewed_user_id'],
            'service_post_id' => $validated['service_post_id'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        $review->load(['reviewer' => function ($query) {
            $query->select('id', 'user_name', 'name')->with('photos');
        }]);

        return response()->json(['review' => $this->formatReview($review)], 201);
    }

    /**
     * A reviewer can remove their own review.
     */
    public function destroy(Review $review): JsonResponse
    {
        if ($review->reviewer_id !== Auth::id()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $review->delete();

        return response()->json(['status' => 'success']);
    }
}
