<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class SubcategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the category ID from the request
        $category_id = $request->input('category_id');
        // Get the subcategories for the selected category
        $subcategories = Sub_categories::where('categories_id', $category_id)->get();
        // Return the subcategories as JSON
        return response()->json(['subcategories' => $subcategories]);
    }

    /**
     * Display a listing of the resource.
     */
    public function indexSubCategory()
    {
        $subCategories = Sub_categories::with('category', 'photos')
            ->withCount('servicePosts')
            ->having('service_posts_count', '>=', 0)
            ->paginate(7);
        return view('sub_categories.index', compact('subCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Application|Factory|View
    {
        // You can optionally fetch data from your database or other sources and pass it to the view
        $categories = Categories::all(); // Assuming you have a 'Category' model

        return view('sub_categories.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'categories_id' => 'required|exists:categories,id', // Make sure the selected category exists
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Image validation
        ]);

        // Handle the image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sub_category_images', 'public'); // Store the image in the 'public' disk under the 'sub_category_images' directory
            $validatedData['image'] = $imagePath;
        }

        // Create a new Sub_categories instance and save it to the database
        $subCategory = new Sub_categories($validatedData);
        $subCategory->save();

        return redirect()->back()
            ->with('success', 'Sub Category created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sub_categories $subcategories)
    {
        return view('sub_categories.edit', compact('subcategories'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sub_categories $subcategories)
    {
        // Fetch a list of categories for the dropdown
        $categories = Categories::all();

        return view('sub_categories.edit', compact('subcategories', 'categories'));
    }

    public function update(Request $request, Sub_categories $subcategory)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Handle image upload here
        }

        // Update the sub-category
        $subcategory->update($validatedData);

        return redirect()->route('subcategories.index')
            ->with('success', 'Sub Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
     
public function destroy($id)
{
    $user = Auth::user();

    // Check if the user has the required permissions to delete a subcategory
    if (!$user->hasPermission('view_service')) {
        return redirect()->back()->with('error', 'Permission denied for deleting the subcategory.');
    }

    // Retrieve the subcategory by ID
    $subcategories = Sub_categories::find($id);

    // Check if the subcategory exists
    if (!$subcategories) {
        return redirect()->back()->with('error', 'SubCategory not found.');
    }

    // Log for debugging
    Log::info("Deleting SubCategory: " . $subcategories->id);

    // Start a database transaction
    DB::beginTransaction();

    try {
        // Get all service posts related to the subcategory
        $servicePosts = $subcategories->servicePosts;

        // Loop through each service post
        foreach ($servicePosts as $servicePost) {
            // Loop through the associated photos and delete them
            foreach ($servicePost->photos as $photo) {
                if (Storage::disk('public')->exists($photo->src) && !in_array($photo->src, ['photos/servicepost1.jpg', 'photos/servicepost2.jpg', 'photos/servicepost3.jpg', 'photos/servicepost4.jpg', 'photos/servicepost5.jpg'])) {
                    Storage::delete($photo->src);
                    $photo->delete();
                    // Log for debugging
                    Log::info("Deleted photo: " . $photo->src);
                }
            }
            
            $servicePost->delete();
            // Log for debugging
            Log::info("Deleted ServicePost: " . $servicePost->id);
        }

        // Delete the subcategory
        $subcategories->delete();
        // Log for debugging
        Log::info("Deleted SubCategory: " . $subcategories->id);

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


}
