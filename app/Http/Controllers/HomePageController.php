<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Sub_categories;
use App\Models\point_purchase_requests;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomePageController extends Controller
{
    /**
     * Display the welcome page with all required data
     *
     * @return View
     */
    public function index(): View
    {
        // User and general stats
        $user = Auth::user();
        $users = DB::table('users')->count();
        $allService = ServicePost::where('state', 'published')->count();
        $allPhone = ServicePost::where('categories_id', '1')->where('state', 'published')->count();
        $allCar = ServicePost::where('categories_id', '2')->where('state', 'published')->count();
        $allJobs = ServicePost::where('categories_id', '3')->where('state', 'published')->count();
        $allRealState = ServicePost::where('categories_id', '4')->where('state', 'published')->count();
        $allGeneral = ServicePost::where('categories_id', '5')->where('state', 'published')->count();
        $allGolden = ServicePost::where('have_badge', 'ذهبي')->where('state', 'published')->count();
        $allDiamond = ServicePost::where('have_badge', 'ماسي')->where('state', 'published')->count();
        $allNormal = ServicePost::where('have_badge', 'عادي')->where('state', 'published')->count();
        $purchaseRequests = point_purchase_requests::count();

        // Categories with post counts
        $categories = Categories::withCount(['servicePosts' => function ($query) {
            $query->where('state', 'published');
        }])->get();

        // Featured premium posts (Diamond and Gold) that have photos
        $featuredPosts = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->whereIn('have_badge', ['ماسي', 'ذهبي'])
            ->whereHas('photos')  // Only include posts with photos
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي'), view_count DESC")
            ->limit(6)
            ->get();

        // Latest posts across all categories that have photos
        $latestPosts = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->whereHas('photos')  // Only include posts with photos
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Latest posts by category that have photos
        $latestByCategory = [];
        foreach ($categories as $category) {
            $latestByCategory[$category->id] = ServicePost::with(['photos', 'user.photos', 'subCategory'])
                ->withCount(['favorites', 'comments'])
                ->where('categories_id', $category->id)
                ->where('state', 'published')
                ->whereHas('photos')  // Only include posts with photos
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
        }

        // All subcategories grouped by main category
        $subcategories = [];
        foreach ($categories as $category) {
            $subcategories[$category->id] = Sub_categories::withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published');
            }])
                ->where('categories_id', $category->id)
                ->orderBy('service_posts_count', 'desc')
                ->limit(8)
                ->get();
        }

        // Popular posts (most viewed) that have photos
        $popularPosts = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->whereHas('photos')  // Only include posts with photos
            ->orderBy('view_count', 'desc')
            ->limit(6)
            ->get();

        // Recent activity (recently updated or commented posts)
        $recentlyActive = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->where(function($query) {
                $oneWeekAgo = Carbon::now()->subWeek();
                $query->where('updated_at', '>=', $oneWeekAgo)
                    ->orWhereHas('comments', function($q) use ($oneWeekAgo) {
                        $q->where('created_at', '>=', $oneWeekAgo);
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        // Most active users (users with most posts)
        $mostActiveUsers = User::withCount(['servicePosts' => function ($query) {
            $query->where('state', 'published');
        }])
            ->with('photos')
            ->orderBy('service_posts_count', 'desc')
            ->limit(10)
            ->get();

        return view('welcome', compact(
            'user',
            'users',
            'allService',
            'allPhone',
            'allCar',
            'allJobs',
            'allRealState',
            'allGeneral',
            'allGolden',
            'allDiamond',
            'allNormal',
            'purchaseRequests',
            'categories',
            'featuredPosts',
            'latestPosts',
            'latestByCategory',
            'subcategories',
            'popularPosts',
            'recentlyActive',
            'mostActiveUsers'
        ));
    }
}
