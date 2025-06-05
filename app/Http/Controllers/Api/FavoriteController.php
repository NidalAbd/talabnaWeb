<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, )
    {
        $post = ServicePost::findOrFail($request->post_id);
        $favorite = $post->favorites()->where('user_id', Auth::id())->first();

        if (!$favorite) {
            $favorite = new Favorite();
            $favorite->user_id = Auth::id();
            $post->favorites()->save($favorite);
            $isFavorited = true;
        } else {
            $favorite->delete();
            $isFavorited = false;
        }

        $count = $post->favorites()->count();

        return response()->json([
            'is_favorited' => $isFavorited,
            'count' => $count,
        ]);
    }
    public function doFavourite($servicePost ): JsonResponse
    {

        $post = ServicePost::findOrFail($servicePost);
        $favorite = $post->favorites()->where('user_id', Auth::id())->first();

        if (!$favorite) {
            $favorite = new Favorite();
            $favorite->user_id = Auth::id();
            $post->favorites()->save($favorite);
            $isFavorited = true;
        } else {
            $favorite->delete();
            $isFavorited = false;
        }

        $count = $post->favorites()->count();

        return response()->json([
            'is_favorited' => $isFavorited,
            'count' => $count,
        ]);
    }
    public function getFavourite(Request $request, $service_post_id): JsonResponse
    {
        $post = ServicePost::find($service_post_id);
        $favorite = $post->favorites()->where('user_id', Auth::id())->first();
        $isFavorited = (bool)$favorite;
        return response()->json([
            'is_favorited' => $isFavorited,
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
