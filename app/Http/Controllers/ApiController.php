<?php

namespace App\Http\Controllers;

use App\Models\countries;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Google\Service\Dfareporting\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * Search users for select2
     */
    public function searchUsers(Request $request)
    {
        $search = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        $users = User::where(function($query) use ($search) {
            $query->where('user_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
        })
            ->whereNotNull('fcm_token')
            ->with('roles')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'more' => $users->hasMorePages()
            ]
        ]);
    }

    /**
     * Filter users by criteria
     */
    public function filterUsers(Request $request)
    {
        $roleId = $request->input('role');
        $countryId = $request->input('country');
        $status = $request->input('status');
        $lastActive = $request->input('lastActive');

        $query = User::query()->whereNotNull('fcm_token');

        // Apply role filter
        if (!empty($roleId)) {
            $query->whereHas('roles', function($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        // Apply country filter
        if (!empty($countryId)) {
            $query->where('country_id', $countryId);
        }

        // Apply status filter
        if (!empty($status)) {
            $query->where('is_active', $status);
        }

        // Apply last active filter (using a placeholder - you'll need to adapt this to your actual last_active field)
        if (!empty($lastActive)) {
            $date = Carbon::now()->subDays($lastActive);
            $query->where('updated_at', '>=', $date);
        }

        // Get users with roles
        $users = $query->with('roles')->limit(100)->get();

        return response()->json($users);
    }

    /**
     * Get all roles
     */
    public function getRoles()
    {
        try {
            // Use the correct model based on your application structure
            $roles = Role::all(['id', 'name', 'display_name']);
            return response()->json($roles);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Role list error: ' . $e->getMessage());

            // Return a meaningful error response
            return response()->json(['error' => 'Unable to retrieve roles'], 500);
        }
    }

    /**
     * Get all countries
     */
    public function getCountries()
    {
        try {
            $countries = countries::all(['id', 'name']);
            return response()->json($countries);
        } catch (\Exception $e) {
            Log::error('Country list error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to retrieve countries'], 500);
        }
    }
}
