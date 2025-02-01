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
        Log::info('Request received', ['request' => $request->all()]);
        $user = Auth::id();
        $CurrentUser = User::find($user);
        $query = $request->input('search');
        $users = User::where(function ($innerQuery) use ($query) {
            $innerQuery->where('user_name', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%');
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
        $posts = ServicePost::where('title', 'LIKE', '%' . $query . '%')
            ->orWhere('description', 'LIKE', '%'.$query.'%')
            ->with('photos')
            ->paginate(10);

        foreach ($posts as $servicePost) {
            $postUser = User::with('photos')->find($servicePost->user_id);
            $servicePost->user_photo = $postUser->photos->first() ? $postUser->photos->first()->src : null;
            $servicePost->user_name = $postUser->user_name; // Add the user's name to the response

            $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
            $servicePost->is_favorited = (bool)$favorite;
            // Get the distance between the service post location and the current user
            $servicePost->distance = round(ServicePost::distance($CurrentUser->location_latitudes, $CurrentUser->location_longitudes, $servicePost->location_latitudes, $servicePost->location_longitudes), 2);
            // Check if the current user follows the service post user
            $follow = $CurrentUser->followers()->where('follower_id',  $postUser->id)->first();
            $servicePost->is_followed = (bool)$follow;
        }
        Log::info(response()->json([
            'users' => $users,
            'posts' => $posts,
        ]));
        return response()->json([
            'users' => $users,
            'posts' => $posts,
        ]);
    }

}
