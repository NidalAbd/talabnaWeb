<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
                $categories = Categories::with(['sub_categories' => function($query) {
                    $query->withCount(['servicePosts'])->with('photos');
                }, 'photos'])
                    ->withCount(['servicePosts', 'sub_categories as sub_categories_with_service_posts_count' => function ($query) {
                        $query->has('servicePosts');
                    }])
                    ->paginate(10);
                return response()->json(compact('categories'));

    }
    public function categoryList(): JsonResponse
    {
        $user = Auth::user();
        if ($user->hasPermission('add_news')) {
            // User has permission, fetch all categories
            $categories = Categories::all();
        } else {
            // User doesn't have permission, fetch categories where name is not 'اخبار'
            $categories = Categories::where('name', '<>', 'اخبار')->get();
        }
        return response()->json(compact('categories'));
    }

    public function categoryMenu(): JsonResponse
    {
        $categories = Categories::with('photos')->get();
        return response()->json(compact('categories'));
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            // Check if the user is logged in
            if (!Auth::check()) {
                return response()->json(['error' => 'You need to log in'], 401);
            }
            $user = Auth::user();
            // Check if the user has the required permissions to view all service posts
            if (!$user->hasPermission('view_service')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return response()->json(compact('id'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
