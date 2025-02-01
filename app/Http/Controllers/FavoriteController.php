<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $favorites = Favorite::select('favoritable_id', 'favoritable_type', DB::raw('COUNT(*) as favorite_count'))
            ->groupBy('favoritable_id', 'favoritable_type')
            ->paginate(7);

        return view('admin.favorite', compact('favorites'));
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
     * @return JsonResponse
     */
    public function store(Request $request)
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $favorite = Favorite::findOrFail($id);
        $favorite->update($request->all());

        return redirect()->route('favorites.show', $favorite->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $favorite = Favorite::findOrFail($id);
        $favorite->delete();

        return redirect()->route('favorites.index');
    }
}
