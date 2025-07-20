<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Photos;
use App\Models\ServicePost;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{

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
            ->where('isSuspended', false)
            ->paginate(10);

        return view('welcome', compact('categories'));
    }

    public function create(): Application|Factory|View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Create a new category with multilingual name
        $category = new Categories([
            'name' => [
                'ar' => $validatedData['name']['ar'],
                'en' => $validatedData['name']['en']
            ]
        ]);

        $category->save();

        // Handle the photo upload
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $fileName = $image->hashName();

            // First store the image in the public disk
            $storeImage = $image->storeAs("category", $fileName, 'public');

            // Then create the path for the database that includes 'storage/'
            $photoPath = 'storage/' . $storeImage;

            $photo = new Photos([
                'src' => $photoPath,
            ]);

            $category->photos()->save($photo);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show($id): Application|Factory|View
    {
        $category = Categories::with([
            'sub_categories' => function($query) {
                $query->withCount('servicePosts');
            },
            'photos',
            'servicePosts' => function($query) {
                $query->with('photos')
                    ->withCount('favorites')
                    ->orderByRaw("FIELD((SELECT name->'$.ar' FROM levels WHERE levels.id = service_posts.level_id), 'ماسي', 'ذهبي', 'عادي'), id DESC");
            }
        ])->findOrFail($id);

        return view('categories.show', compact('category'));
    }
    public function edit($id): Application|Factory|View
    {
        $category = Categories::with('photos')->findOrFail($id);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isSuspended' => 'nullable|boolean',
        ]);

        $category = Categories::findOrFail($id);

        // Update category details
        $category->name = [
            'ar' => $validatedData['name']['ar'],
            'en' => $validatedData['name']['en']
        ];

        if (isset($validatedData['isSuspended'])) {
            $category->isSuspended = $validatedData['isSuspended'];
        }

        $category->save();

        // Handle the photo upload if a new one is provided
        if ($request->hasFile('photo')) {
            // Delete the old photo if it exists
            $oldPhoto = $category->photos()->first();
            if ($oldPhoto) {
                // Remove 'storage/' from the path when deleting
                $oldPath = str_replace('storage/', '', $oldPhoto->src);
                Storage::disk('public')->delete($oldPath);
                $oldPhoto->delete();
            }

            // Upload and save the new photo
            $image = $request->file('photo');
            $fileName = $image->hashName();

            // First store the image in the public disk
            $storeImage = $image->storeAs("category", $fileName, 'public');

            // Then create the path for the database that includes 'storage/'
            $photoPath = 'storage/' . $storeImage;

            $photo = new Photos([
                'src' => $photoPath,
            ]);

            $category->photos()->save($photo);
        }

        return redirect()->route('categories.edit', $category->id)
            ->with('success', 'Category updated successfully.');
    }
    public function destroy($id): RedirectResponse
    {
        $user = Auth::user();

        // Check if the user has the required permissions
        if (!$user->hasPermission('destroy_permission')) {
            return redirect()->back()->with('error', 'Permission denied for deleting the category.');
        }

        $category = Categories::findOrFail($id);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Delete the photo if it exists
            $photo = $category->photos()->first();
            if ($photo) {
                Storage::delete($photo->src);
                $photo->delete();
            }

            // Get all subcategories and their related service posts
            $subcategories = $category->sub_categories;

            foreach ($subcategories as $subcategory) {
                // Get all service posts for this subcategory
                $servicePosts = $subcategory->servicePosts;

                foreach ($servicePosts as $servicePost) {
                    // Delete all photos for this service post
                    foreach ($servicePost->photos as $photo) {
                        if (Storage::disk('public')->exists($photo->src) &&
                            !in_array($photo->src, [
                                'storage/photos/servicepost1.jpg',
                                'storage/photos/servicepost2.jpg',
                                'storage/photos/servicepost3.jpg',
                                'storage/photos/servicepost4.jpg',
                                'storage/photos/servicepost5.jpg'
                            ])) {
                            Storage::delete($photo->src);
                            $photo->delete();
                        }
                    }

                    // Delete the service post
                    $servicePost->delete();
                }

                // Delete the subcategory
                $subcategory->delete();
            }

            // Delete the category
            $category->delete();

            // Commit the transaction
            DB::commit();

            return redirect()->route('categories.index')
                ->with('success', 'Category and all related subcategories and service posts have been deleted.');
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Log the error
            Log::error("Error deleting Category: " . $e->getMessage());

            return redirect()->back()
                ->with('error', 'An error occurred while deleting the category: ' . $e->getMessage());
        }
    }

    public function toggleSuspend($id): RedirectResponse
    {
        $user = Auth::user();

        // Check if the user has the required permissions
        if (!$user->hasPermission('create_permission')) {
            return redirect()->back()->with('error', 'Permission denied for suspending categories.');
        }

        $category = Categories::findOrFail($id);
        $category->isSuspended = !$category->isSuspended;
        $category->save();

        $status = $category->isSuspended ? 'suspended' : 'unsuspended';

        return redirect()->back()
            ->with('success', "Category {$status} successfully.");
    }
}
