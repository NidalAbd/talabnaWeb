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
use Illuminate\Validation\ValidationException;
use App\Notifications\AdminCustomNotification;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user_index')->only(['index']);
        $this->middleware('permission:user_view')->only(['show']);
        $this->middleware('permission:user_create')->only(['create', 'store']);
        $this->middleware('permission:user_edit')->only(['edit', 'update']);
        $this->middleware('permission:user_destroy')->only(['destroy']);
        $this->middleware('permission:users_export')->only(['export']);
        $this->middleware('permission:users_import')->only(['import']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $status = $request->input('status');
        $role = $request->input('role');
        $search = $request->input('search');

        // Base query
        $query = User::with('photos', 'roles')
            ->withCount(['reports', 'servicePosts']);

        // Apply filters
        if ($status) {
            $query->where('is_active', $status);
        }

        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', $search)
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phones', 'like', "%{$search}%");
            });
        }

        // Get paginated users
        $users = $query->orderBy('id', 'desc')->paginate(10);
        // Get all roles for the dropdown
        $roles = Role::all();

        // Get counts for dashboard stats
        $activeUsersCount = User::where('is_active', 'active')->count();
        $bannedUsersCount = User::where('is_active', 'banned')->count();
        $inactiveUsersCount = User::where('is_active', 'inactive')->count();

        return view('users.index', compact('users', 'roles', 'activeUsersCount', 'bannedUsersCount', 'inactiveUsersCount'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $countries = countries::all();
        $cities = cities::all();
        $roles = Role::all();
        return view('users.create', compact('countries', 'cities', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'required|in:ذكر,انثى',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'phones' => 'nullable|string|max:255',
            'WatsNumber' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'location_latitudes' => 'nullable|numeric',
            'location_longitudes' => 'nullable|numeric',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'user_name' => $validatedData['user_name'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'gender' => $validatedData['gender'],
                'country_id' => $validatedData['country_id'],
                'city_id' => $validatedData['city_id'],
                'phones' => $validatedData['phones'],
                'WatsNumber' => $validatedData['WatsNumber'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'location_latitudes' => $validatedData['location_latitudes'],
                'location_longitudes' => $validatedData['location_longitudes'],
                'is_active' => 'active',
            ]);

            // Assign roles if provided
            if (isset($validatedData['roles'])) {
                $user->syncRoles($validatedData['roles']);
            }

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('storage/photos'), $imageName);
                
                $photo = new Photos([
                    'src' => 'storage/photos/' . $imageName,
                ]);
                $user->photos()->save($photo);
            } else {
                // Create default photo
                $photo = new Photos([
                    'src' => 'storage/photos/avatar1.png',
                ]);
                $user->photos()->save($photo);
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return Application|Factory|View
     */
    public function show(User $user)
    {
        $user->load(['photos', 'roles', 'country', 'city']);
        $locationDisplay = ($user->location_latitudes && $user->location_longitudes)
            ? $user->location_latitudes . ', ' . $user->location_longitudes
            : 'Not set';
        
        // Load points history
        $pointsHistory = \App\Models\palservice_points::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('users.show', compact('user', 'locationDisplay', 'pointsHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return Application|Factory|View
     */
    public function edit(User $user)
    {
        $countries = countries::all();
        $cities = cities::where('country_id', $user->country_id)->get();
        $roles = Role::all();
        $user->load(['photos', 'roles']);
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('users.edit', compact('user', 'countries', 'cities', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return Application|Redirector|RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255|unique:users,user_name,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'gender' => 'required|in:ذكر,انثى',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'phones' => 'nullable|string|max:255',
            'WatsNumber' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'location_latitudes' => 'nullable|numeric',
            'location_longitudes' => 'nullable|numeric',
            'is_active' => 'required|in:active,inactive,banned',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        DB::beginTransaction();

        try {
            $updateData = [
                'user_name' => $validatedData['user_name'],
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'gender' => $validatedData['gender'],
                'country_id' => $validatedData['country_id'],
                'city_id' => $validatedData['city_id'],
                'phones' => $validatedData['phones'],
                'WatsNumber' => $validatedData['WatsNumber'],
                'date_of_birth' => $validatedData['date_of_birth'],
                'location_latitudes' => $validatedData['location_latitudes'],
                'location_longitudes' => $validatedData['location_longitudes'],
                'is_active' => $validatedData['is_active'],
            ];

            // Update password only if provided
            if (!empty($validatedData['password'])) {
                $updateData['password'] = Hash::make($validatedData['password']);
            }

            $user->update($updateData);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('storage/photos'), $imageName);
                
                // Delete old photo if exists
                $oldPhoto = $user->photos()->first();
                if ($oldPhoto) {
                    $oldPhoto->delete();
                }
                
                $photo = new Photos([
                    'src' => 'storage/photos/' . $imageName,
                ]);
                $user->photos()->save($photo);
            }

            // Update roles if provided
            if (isset($validatedData['roles'])) {
                $user->syncRoles($validatedData['roles']);
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Application|Redirector|RedirectResponse
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Export users data
     */
    public function export()
    {
        // Implementation for exporting users
        return back()->with('info', 'Export functionality will be implemented');
    }

    /**
     * Import users data
     */
    public function import()
    {
        // Implementation for importing users
        return back()->with('info', 'Import functionality will be implemented');
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $status = $request->input('is_active');
        
        if (!in_array($status, ['active', 'inactive', 'banned'])) {
            return back()->with('error', 'Invalid status');
        }

        $user->update(['is_active' => $status]);
        
        return back()->with('success', 'User status updated successfully');
    }

    /**
     * Ban a user
     */
    public function ban($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['is_active' => 'banned']);
            
            return back()->with('success', 'User has been banned successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to ban user: ' . $e->getMessage());
        }
    }

    /**
     * Unban a user
     */
    public function unban($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['is_active' => 'active']);
            
            return back()->with('success', 'User has been unbanned successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to unban user: ' . $e->getMessage());
        }
    }

    /**
     * Reset the specified user's password and display the new password.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);
            $newPassword = \Str::random(10);
            $user->password = \Hash::make($newPassword);
            $user->save();

            // Optionally, you can email the new password to the user here.

            return redirect()->back()->with('success', 'Password reset successfully! New password: ' . $newPassword);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    /**
     * Send a notification to the specified user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendNotification(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $message = $request->input('message', 'This is a notification from the admin panel.');
            $user->notify(new AdminCustomNotification($message));
            return redirect()->back()->with('success', 'Notification sent successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Get users data for AJAX requests
     */
    public function data(Request $request)
    {
        $query = User::with('roles')->withCount(['reports', 'servicePosts']);

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('user_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        $users = $query->paginate(10);

        return response()->json($users);
    }

    /**
     * Impersonate the specified user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Store current user session
            session(['impersonator_id' => auth()->id()]);
            
            // Login as the target user
            auth()->login($user);
            
            return redirect()->back()->with('success', 'Now impersonating user: ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to impersonate user: ' . $e->getMessage());
        }
    }

    /**
     * Stop impersonation and return to original user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonation()
    {
        try {
            $impersonatorId = session('impersonator_id');
            
            if ($impersonatorId) {
                $impersonator = User::find($impersonatorId);
                if ($impersonator) {
                    auth()->login($impersonator);
                    session()->forget('impersonator_id');
                    return redirect()->back()->with('success', 'Stopped impersonation. Back to admin account.');
                }
            }
            
            return redirect()->back()->with('error', 'No impersonation session found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to stop impersonation: ' . $e->getMessage());
        }
    }

    /**
     * Show login history for the specified user (placeholder).
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function loginHistory($id)
    {
        $user = User::findOrFail($id);
        $logins = []; // Replace with actual login history data
        return view('users.login_history', compact('user', 'logins'));
    }
}
