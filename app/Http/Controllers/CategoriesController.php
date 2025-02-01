<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Application|Factory|View
    {
        $categories = Categories::with(['sub_categories' => function($query) {
            $query->withCount(['servicePosts']);
        }, 'photos'])
            ->withCount(['servicePosts', 'sub_categories as sub_categories_with_service_posts_count' => function ($query) {
                $query->has('servicePosts');
            }])
            ->paginate(10);

        return view('categories.index', compact('categories'));

    }

    public function UserFrontIndex(): Application|Factory|View
    {
        $categories = Categories::with(['sub_categories' => function($query) {
            $query->withCount(['servicePosts']);
        }, 'photos'])
            ->withCount(['servicePosts', 'sub_categories as sub_categories_with_service_posts_count' => function ($query) {
                $query->has('servicePosts');
            }])
            ->paginate(10);

        return view('welcome', compact('categories'));

    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(categories $categories): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(categories $categories): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, categories $categories): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(categories $categories): RedirectResponse
    {
        //
    }
}
