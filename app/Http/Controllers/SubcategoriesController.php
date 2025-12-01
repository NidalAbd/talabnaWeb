<?php

namespace App\Http\Controllers;

use App\Models\BadgeType;
use App\Models\Categories;
use App\Models\Photos;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubcategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Get the category ID from the request
        $category_id = $request->input('category_id');

        // Get the subcategories for the selected category
        $subcategories = Sub_categories::where('categories_id', $category_id)
            ->where('isSuspended', false)
            ->get();

        // Return the subcategories as JSON
        return response()->json(['subcategories' => $subcategories]);
    }

    /**
     * Display a listing of all subcategories with related information.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function indexSubCategory(Request $request): Application|Factory|View
    {
        $query = Sub_categories::with(['category', 'photos'])
            ->withCount('servicePosts');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('isSuspended', false);
            } elseif ($request->status === 'suspended') {
                $query->where('isSuspended', true);
            }
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('categories_id', $request->category_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name->en', 'like', "%{$search}%")
                  ->orWhere('name->ar', 'like', "%{$search}%");
            });
        }

        $subCategories = $query->paginate(15);
        $categories = Categories::where('isSuspended', false)->get();

        return view('sub_categories.index', compact('subCategories', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): Application|Factory|View
    {
        // Fetch all categories for the dropdown
        $categories = Categories::where('isSuspended', false)->get();

        return view('sub_categories.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'categories_id' => 'required|exists:categories,id',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the category to include in the subcategory name
        $category = Categories::findOrFail($validatedData['categories_id']);

        // Create a new subcategory
        $subCategory = new Sub_categories([
            'categories_id' => $validatedData['categories_id'],
            'name' => [
                'ar' => $validatedData['name']['ar'],
                'en' => $validatedData['name']['en']
            ]
        ]);

        $subCategory->save();

        // Handle the image upload
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $fileName = $image->hashName();

            // Get category name for the storage path
            $categoryName = $category->name['ar'];

            // First store the image in the public disk
            $storeImage = $image->storeAs("subcategory/{$categoryName}", $fileName, 'public');

            // Then create the path for the database that includes 'storage/'
            $photoPath = 'storage/' . $storeImage;

            $photo = new Photos([
                'src' => $photoPath, // This will be like "storage/subcategory/اجهزة/filename.jpg" in database
            ]);

            $subCategory->photos()->save($photo);
        }

        return redirect()->route('indexSubCategory.index')
            ->with('success', 'Sub Category created successfully');
    }

    public function show($id): Application|Factory|View
    {
        $subcategory = Sub_categories::with(['category', 'photos'])
            ->withCount('servicePosts')
            ->findOrFail($id);

        $servicePosts = ServicePost::where('sub_categories_id', $id)
            ->with('photos')
            ->withCount('favorites')
            ->orderByRaw(BadgeType::getLegacyOrderByClause() . ", id DESC")
            ->paginate(10);

        return view('sub_categories.show', compact('subcategory', 'servicePosts'));
    }

    public function edit($id): Application|Factory|View
    {
        $subcategory = Sub_categories::with('photos')->findOrFail($id);
        $categories = Categories::where('isSuspended', false)->get();

        return view('sub_categories.edit', compact('subcategory', 'categories'));
    }

// For the update method
    public function update(Request $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'categories_id' => 'required|exists:categories,id',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'isSuspended' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
        ]);

        $subcategory = Sub_categories::findOrFail($id);

        // Update subcategory details
        $subcategory->categories_id = $validatedData['categories_id'];
        $subcategory->name = [
            'ar' => $validatedData['name']['ar'],
            'en' => $validatedData['name']['en']
        ];

        if (isset($validatedData['isSuspended'])) {
            $subcategory->isSuspended = $validatedData['isSuspended'];
        }

        $subcategory->is_featured = $request->has('is_featured');
        $subcategory->is_popular = $request->has('is_popular');

        $subcategory->save();

        // Handle the image upload if a new one is provided
        if ($request->hasFile('photo')) {
            // Delete the old photo if it exists
            $oldPhoto = $subcategory->photos()->first();
            if ($oldPhoto) {
                // Remove 'storage/' from the path when deleting
                $oldPath = str_replace('storage/', '', $oldPhoto->src);
                Storage::disk('public')->delete($oldPath);
                $oldPhoto->delete();
            }

            // Get category for the folder structure
            $category = Categories::findOrFail($validatedData['categories_id']);
            $categoryName = $category->name['ar'];

            // Upload and save the new photo
            $image = $request->file('photo');
            $fileName = $image->hashName();

            // First store the image in the public disk
            $storeImage = $image->storeAs("subcategory/{$categoryName}", $fileName, 'public');

            // Then create the path for the database that includes 'storage/'
            $photoPath = 'storage/' . $storeImage;

            $photo = new Photos([
                'src' => $photoPath, // This will be like "storage/subcategory/اجهزة/filename.jpg" in database
            ]);

            $subcategory->photos()->save($photo);
        }

        return redirect()->route('subcategories.edit', $subcategory->id)
            ->with('success', 'Sub Category updated successfully');
    }
    public function destroy($id): RedirectResponse
    {
        $user = Auth::user();

        // Check if the user has the required permissions to delete a subcategory
        if (!$user->hasPermission('destroy_permission')) {
            return redirect()->back()->with('error', 'Permission denied for deleting the subcategory.');
        }

        // Retrieve the subcategory by ID
        $subcategory = Sub_categories::findOrFail($id);

        // Log for debugging
        Log::info("Deleting SubCategory: " . $subcategory->id);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Get all service posts related to the subcategory
            $servicePosts = $subcategory->servicePosts;

            // Loop through each service post
            foreach ($servicePosts as $servicePost) {
                // Loop through the associated photos and delete them
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
                        // Log for debugging
                        Log::info("Deleted photo: " . $photo->src);
                    }
                }

                // Delete all favorites and reports for this service post
                $servicePost->favorites()->delete();
                $servicePost->reports()->delete();

                // Delete the service post
                $servicePost->delete();
                // Log for debugging
                Log::info("Deleted ServicePost: " . $servicePost->id);
            }

            // Delete the subcategory photo if it exists
            $photo = $subcategory->photos()->first();
            if ($photo) {
                Storage::delete($photo->src);
                $photo->delete();
            }

            // Delete the subcategory
            $subcategory->delete();
            // Log for debugging
            Log::info("Deleted SubCategory: " . $subcategory->id);

            // Commit the transaction
            DB::commit();

            return redirect()->back()->with('success', 'Sub Category and associated Service Posts have been deleted.');
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();

            // Log the error
            Log::error("Error deleting SubCategory: " . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while deleting the Sub Category and associated Service Posts.');
        }
    }

    public function toggleSuspend($id): RedirectResponse
    {
        $user = Auth::user();

        // Check if the user has the required permissions
        if (!$user->hasPermission('create_permission')) {
            return redirect()->back()->with('error', 'Permission denied for suspending subcategories.');
        }

        $subcategory = Sub_categories::findOrFail($id);
        $subcategory->isSuspended = !$subcategory->isSuspended;
        $subcategory->save();

        $status = $subcategory->isSuspended ? 'suspended' : 'unsuspended';

        return redirect()->back()
            ->with('success', "Subcategory {$status} successfully.");
    }
}
