<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadgeType;
use App\Models\Categories;
use App\Models\point_purchase_requests;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StatisticsApiController extends Controller
{
    /**
     * Get all statistics for the statistics dashboard
     */
    public function index(): JsonResponse
    {
        try {
            // Count users
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', 'active')->count();
            $bannedUsers = User::where('is_active', 'banned')->count();

            // Get categories with robust handling
            $categories = Categories::get();

            // Mapping of English names to category IDs
            $categoryMap = [
                'Devices' => $categories->first(fn($cat) =>
                    isset($cat->name['en']) && $cat->name['en'] === 'Devices'
                )?->id,
                'Cars' => $categories->first(fn($cat) =>
                    isset($cat->name['en']) && $cat->name['en'] === 'Cars'
                )?->id,
                'Jobs' => $categories->first(fn($cat) =>
                    isset($cat->name['en']) && $cat->name['en'] === 'Jobs'
                )?->id,
                'Real Estate' => $categories->first(fn($cat) =>
                    isset($cat->name['en']) && $cat->name['en'] === 'Real Estate'
                )?->id,
                'Services' => $categories->first(fn($cat) =>
                    isset($cat->name['en']) && $cat->name['en'] === 'Services'
                )?->id,
            ];

            // Count service posts
            $allServicePosts = ServicePost::count();
            $phonesPosts = ServicePost::where('categories_id', $categoryMap['Devices'])->count();
            $carsPosts = ServicePost::where('categories_id', $categoryMap['Cars'])->count();
            $jobsPosts = ServicePost::where('categories_id', $categoryMap['Jobs'])->count();
            $realEstatePosts = ServicePost::where('categories_id', $categoryMap['Real Estate'])->count();
            $servicesPosts = ServicePost::where('categories_id', $categoryMap['Services'])->count();

            // Badge counts using badge_type_id or have_badge field
            $goldBadge = BadgeType::where('slug', 'gold')->first();
            $diamondBadge = BadgeType::where('slug', 'diamond')->first();
            $regularBadge = BadgeType::where('slug', 'normal')->orWhere('is_default', true)->first();

            // Count posts by badge_type_id if exists, otherwise by have_badge field
            $goldenPosts = $goldBadge
                ? ServicePost::where('badge_type_id', $goldBadge->id)->count()
                : ServicePost::where('have_badge', 'ذهبي')->count();
            $diamondPosts = $diamondBadge
                ? ServicePost::where('badge_type_id', $diamondBadge->id)->count()
                : ServicePost::where('have_badge', 'ماسي')->count();
            $normalPosts = $regularBadge
                ? ServicePost::where('badge_type_id', $regularBadge->id)->count()
                : ServicePost::where('have_badge', 'عادي')->orWhereNull('have_badge')->count();

            // Post status counts
            $publishedPosts = ServicePost::where('state', 'published')->count();
            $pendingPosts = ServicePost::where('state', 'not published')->count();
            $rejectedPosts = ServicePost::where('state', 'rejected')->count();

            // Purchase requests
            $totalPurchaseRequests = point_purchase_requests::count();
            $pendingRequests = point_purchase_requests::where('status', 'pending')->count();
            $approvedRequests = point_purchase_requests::where('status', 'approved')->count();
            $cancelledRequests = point_purchase_requests::where('status', 'cancelled')->count();

            return response()->json([
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'banned' => $bannedUsers
                ],
                'posts' => [
                    'total' => $allServicePosts,
                    'by_category' => [
                        'devices' => $phonesPosts,
                        'cars' => $carsPosts,
                        'jobs' => $jobsPosts,
                        'real_estate' => $realEstatePosts,
                        'services' => $servicesPosts
                    ],
                    'by_badge' => [
                        'golden' => $goldenPosts,
                        'diamond' => $diamondPosts,
                        'normal' => $normalPosts
                    ],
                    'by_status' => [
                        'published' => $publishedPosts,
                        'pending' => $pendingPosts,
                        'rejected' => $rejectedPosts
                    ]
                ],
                'purchase_requests' => [
                    'total' => $totalPurchaseRequests,
                    'pending' => $pendingRequests,
                    'approved' => $approvedRequests,
                    'cancelled' => $cancelledRequests
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Statistics Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
