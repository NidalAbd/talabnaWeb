<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\point_purchase_requests;
use App\Models\ServicePost;
use App\Models\User;
use App\Models\Level;
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

            // Badge counts using level IDs
            $goldLevel = Level::where('name->ar', 'ذهبي')->first();
            $diamondLevel = Level::where('name->ar', 'ماسي')->first();
            $regularLevel = Level::where('name->ar', 'عادي')->first();

            $goldenPosts = $goldLevel ? ServicePost::where('level_id', $goldLevel->id)->count() : 0;
            $diamondPosts = $diamondLevel ? ServicePost::where('level_id', $diamondLevel->id)->count() : 0;
            $normalPosts = $regularLevel ? ServicePost::where('level_id', $regularLevel->id)->count() : 0;

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
