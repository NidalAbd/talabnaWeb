<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $user = Auth::id();
        $CurrentUser = User::find($user);
        $query = $request->input('search');
        $users = User::where(function ($innerQuery) use ($query) {
            $innerQuery->where('id', 'LIKE', '%' . $query . '%')
                ->orWhere('user_name', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%')
                ->orWhere('phones', 'LIKE', '%' . $query . '%');
        })
            ->with('photos', 'country', 'city')
            ->select('users.id', 'users.user_name', 'users.country_id', 'users.city_id')
            ->paginate(10);

        foreach ($users as $following) {
            // Check if the current user is following this user
            $follow = $following->followers()->where('follower_id', Auth::id())->first();
            $following->is_follow = (bool)$follow;

            // Get country and city names based on country_id and city_id
            $country = $following->country;
            $city = $following->city;

            // Assuming you have a default value or a placeholder for null country/city
            $defaultCountry = 'Unknown Country';
            $defaultCity = 'Unknown City';

            // Assign the default values if country or city is null
            $following->country_name = $country ? $country->name : $defaultCountry;
            $following->city_name = $city ? $city->name : $defaultCity;
        }
        // Search for posts
        // Live posts only (the old query had no state filter and an ungrouped OR), newest first; optional filters.
        $filters = (array) $request->input('filters', []);
        $posts = \App\Services\PostSearch::query($query, $filters)
            ->orderByDesc('created_at')
            ->with('photos')
            ->with('subCategory')
            ->with('category')
            ->paginate(10);
        // Release C: nothing for the whole phrase? Show posts matching any of its longer words, and say so.
        $broadened = false;
        if ($posts->total() === 0 && ($words = \App\Services\PostSearch::words((string) $query))) {
            $posts = \App\Services\PostSearch::anyWord($words, $filters)
                ->orderByDesc('created_at')->with('photos')->with('subCategory')->with('category')->paginate(10);
            $broadened = $posts->total() > 0;
        }

        foreach ($posts as $servicePost) {
            $postUser = User::with('photos')->find($servicePost->user_id);
            $servicePost->user_photo = $postUser->photos->first();
            $servicePost->user_name = $postUser->user_name; // Add the user's name to the response

            $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
            $servicePost->is_favorited = (bool)$favorite;
            // Get the distance between the service post location and the current user
            $servicePost->distance = round(ServicePost::distance($CurrentUser->location_latitudes, $CurrentUser->location_longitudes, $servicePost->location_latitudes, $servicePost->location_longitudes), 2);
            // Check if the current user follows the service post user
            $follow = $CurrentUser->followers()->where('follower_id',  $postUser->id)->first();
            $servicePost->is_followed = (bool)$follow;
        }
        // Options with counts for the details of the category searched (2026-10-10): the chosen category, else the one
        // most of the results are in. Only for apps that ask (facets=1).
        $facets = null;
        if ($request->boolean('facets')) {
            $matching = $broadened && ($words = \App\Services\PostSearch::words((string) $query))
                ? \App\Services\PostSearch::anyWord($words, $filters)
                : \App\Services\PostSearch::query($query, $filters);
            $cat = (int) ($filters['category_id'] ?? 0) ?: (int) (clone $matching)->reorder()->setEagerLoads([])
                ->select('categories_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as c'))->groupBy('categories_id')
                ->orderByDesc('c')->limit(1)->value('categories_id');
            if ($cat && \App\Services\PostAttributes::fieldsFor($cat)) {
                $facets = ['category_id' => $cat, 'fields' => \App\Services\PostAttributes::facets($matching, $cat)];
            }
        }

        return response()->json([
            'users' => $users,
            'posts' => $posts,
            'broadened' => $broadened,
            'facets' => $facets,
        ]);
    }

}
