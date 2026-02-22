<?php

namespace App\Http\Controllers;

use App\Models\Photos;
use App\Models\User;
use App\Models\Role;
use App\Models\countries;
use App\Models\cities;
use App\Models\palservice_points;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        // Return SPA shell - Vue Router will handle the component
        return view('admin.spa');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        $countries = countries::all();
        $roles = Role::all();

        return view('users.create', compact('countries', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:ذكر,انثى',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'date_of_birth' => 'nullable|date',
            'location_latitudes' => 'nullable|numeric|max:99999999',
            'location_longitudes' => 'nullable|numeric|max:99999999',
            'phones' => 'nullable|string|unique:users',
            'WatsNumber' => 'nullable|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2500',
            'is_active' => 'required|in:active,inactive,banned',
            'auth_type' => 'required|string|in:email,google',
            'data_saver_enabled' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        // Create new user with validated data
        $user = User::create([
            'user_name' => $validatedData['user_name'],
            'name' => $validatedData['name'],
            'gender' => $validatedData['gender'],
            'country_id' => $validatedData['country_id'] ?? null,
            'city_id' => $validatedData['city_id'] ?? null,
            'date_of_birth' => $validatedData['date_of_birth'],
            'location_latitudes' => $validatedData['location_latitudes'],
            'location_longitudes' => $validatedData['location_longitudes'],
            'phones' => $validatedData['phones'] ?? null,
            'WatsNumber' => $validatedData['WatsNumber'] ?? null,
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'is_active' => $validatedData['is_active'],
            'auth_type' => $validatedData['auth_type'],
            'data_saver_enabled' => $request->has('data_saver_enabled'),
        ]);

        // Attach roles if provided, otherwise attach default user role
        $userType = get_class($user);
        if (!empty($validatedData['roles'])) {
            foreach ($validatedData['roles'] as $roleId) {
                $user->roles()->attach($roleId, ['user_type' => $userType]);
            }
        } else {
            $defaultRole = \App\Models\Role::where('name', 'user')->first();
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id, ['user_type' => $userType]);
                $permSync = [];
                foreach ($defaultRole->permissions->pluck('id')->toArray() as $pid) {
                    $permSync[$pid] = ['user_type' => $userType];
                }
                $user->permissions()->sync($permSync);
            }
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $fileName = $image->hashName();
            $photoPath = $image->storeAs('photos/profiles', $fileName);

            $photo = new Photos([
                'src' => 'storage/'.$photoPath,
                'is_external' => false
            ]);

            $user->photos()->save($photo);
        }

        return redirect()->route('users.index')->with('success', 'User has been created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function show($id)
    {
        $user = User::with([
            'servicePosts',
            'photos',
            'roles',
            'followers',
            'following',
            'reports',
            'city',  // Explicitly load city relationship
            'country'  // Explicitly load country relationship
        ])
            ->withCount(['servicePosts', 'followers', 'following', 'reports'])
            ->findOrFail($id);

        // Safely handle location display
        $locationDisplay = 'Not set';
        if ($user->city && $user->country) {
            // Check if name is a JSON string and parse it
            $cityName = is_string($user->city->name)
                ? (json_decode($user->city->name, true) ?? $user->city->name)
                : $user->city->name;

            $countryName = is_string($user->country->name)
                ? (json_decode($user->country->name, true) ?? $user->country->name)
                : $user->country->name;

            // Get the appropriate language version (default to English)
            $cityDisplayName = is_array($cityName)
                ? ($cityName['en'] ?? $cityName['ar'] ?? 'Unknown City')
                : $cityName;

            $countryDisplayName = is_array($countryName)
                ? ($countryName['en'] ?? $countryName['ar'] ?? 'Unknown Country')
                : $countryName;

            $locationDisplay = $cityDisplayName . ', ' . $countryDisplayName;
        }

        // Get user's points history
        $pointsHistory = palservice_points::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('users.show', [
            'user' => $user,
            'pointsHistory' => $pointsHistory,
            'locationDisplay' => $locationDisplay
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function edit($id)
    {
        $user = User::with(['photos', 'roles'])->find($id);

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        $countries = countries::all();
        $cities = cities::where('country_id', $user->country_id)->get();
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('users.edit', compact('user', 'countries', 'cities', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Determine if password is being updated
        $passwordRules = $request->filled('password')
            ? ['required', 'string', 'min:8', 'confirmed']
            : ['nullable'];

        // Validate the request data
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:ذكر,انثى',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'date_of_birth' => 'nullable|date',
            'location_latitudes' => 'nullable|numeric|max:99999999',
            'location_longitudes' => 'nullable|numeric|max:99999999',
            'phones' => 'nullable|string|unique:users,phones,' . $id,
            'WatsNumber' => 'nullable|string|unique:users,WatsNumber,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => $passwordRules,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2500',
            'is_active' => 'required|in:active,inactive,banned',
            'auth_type' => 'required|string|in:email,google',
            'data_saver_enabled' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        // Update user with validated data
        $user->user_name = $validatedData['user_name'];
        $user->name = $validatedData['name'];
        $user->gender = $validatedData['gender'];
        $user->country_id = $validatedData['country_id'] ?? null;
        $user->city_id = $validatedData['city_id'] ?? null;
        $user->date_of_birth = $validatedData['date_of_birth'];
        $user->location_latitudes = $validatedData['location_latitudes'];
        $user->location_longitudes = $validatedData['location_longitudes'];
        $user->phones = $validatedData['phones'] ?? null;
        $user->WatsNumber = $validatedData['WatsNumber'] ?? null;
        $user->email = $validatedData['email'];
        $user->is_active = $validatedData['is_active'];
        $user->auth_type = $validatedData['auth_type'];
        $user->data_saver_enabled = $request->has('data_saver_enabled');

        // Update password only if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($validatedData['password']);
        }

        // Save user changes
        $user->save();

        // Update roles if provided
        if (isset($validatedData['roles'])) {
            $user->syncRoles($validatedData['roles']);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete existing photo if exists
            if ($user->photos && $user->photos->count() > 0) {
                foreach ($user->photos as $existingPhoto) {
                    // Don't delete default avatars
                    if ($existingPhoto->src && !str_contains($existingPhoto->src, 'avatar')) {
                        Storage::delete($existingPhoto->src);
                    }
                    $existingPhoto->delete();
                }
            }

            // Upload new photo
            $image = $request->file('profile_image');
            $fileName = $image->hashName();
            $photoPath = $image->storeAs('photos/profiles', $fileName);

            $photo = new Photos([
                'src' => 'storage/'.$photoPath,
                'is_external' => false
            ]);

            $user->photos()->save($photo);
        }

        return redirect()->route('users.edit', $user->id)->with('success', 'User updated successfully');
    }

    /**
     * Update user status
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'is_active' => 'required|in:active,inactive,banned'
        ]);

        $user->is_active = $validatedData['is_active'];
        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'User status updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Define the list of protected default images that should not be deleted
        $protectedImages = [
            'storage/photos/avatar',
            'storage/photos/servicepost1.jpg',
            'storage/photos/servicepost2.jpg',
            'storage/photos/servicepost3.jpg',
            'storage/photos/servicepost4.jpg',
            'storage/photos/servicepost5.jpg'
        ];

        // Delete user's photos
        if ($user->photos && $user->photos->count() > 0) {
            foreach ($user->photos as $photo) {
                // Check if it's a protected image (using str_contains to match any avatar path)
                $isProtected = false;
                foreach ($protectedImages as $protectedPath) {
                    if (str_contains($photo->src, $protectedPath)) {
                        $isProtected = true;
                        break;
                    }
                }

                // Only delete if it's not a protected image
                if (!$isProtected && Storage::exists($photo->src)) {
                    Storage::delete($photo->src);
                }
                $photo->delete();
            }
        }

        // Get all service posts related to the user
        $servicePosts = $user->servicePosts()->with('photos')->get();

        foreach ($servicePosts as $servicePost) {
            // Delete associated photos for each service post
            foreach ($servicePost->photos as $photo) {
                // Check if it's a protected image
                $isProtected = false;
                foreach ($protectedImages as $protectedPath) {
                    if (str_contains($photo->src, $protectedPath)) {
                        $isProtected = true;
                        break;
                    }
                }

                // Only delete if it's not a protected image
                if (!$isProtected && Storage::disk('public')->exists(str_replace('storage/', '', $photo->src))) {
                    Storage::delete($photo->src);
                }
                $photo->delete();
            }

            // Delete favorites and reports related to this service post
            $servicePost->favorites()->delete();
            $servicePost->reports()->delete();

            // Delete the service post
            $servicePost->delete();
        }

        // Delete user's followers and following relationships
        if (method_exists($user, 'followers')) {
            $user->followers()->detach();
            $user->following()->detach();
        }

        // Delete notifications related to the user
        if (method_exists($user, 'notifications')) {
            $user->notifications()->delete();
        }


        // Delete the user
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User has been deleted successfully');
    }
}
