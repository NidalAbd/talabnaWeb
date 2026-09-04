<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersApiController extends Controller
{
    /**
     * Get user statistics for dashboard widgets
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                [
                    'label' => 'Total Users',
                    'value' => User::count(),
                    'icon' => 'fas fa-users',
                    'color' => 'info',
                    'link' => route('users.index')
                ],
                [
                    'label' => 'Active Users',
                    'value' => User::where('is_active', 'active')->count(),
                    'icon' => 'fas fa-user-check',
                    'color' => 'success',
                    'link' => route('users.index', ['status' => 'active'])
                ],
                [
                    'label' => 'Banned Users',
                    'value' => User::where('is_active', 'banned')->count(),
                    'icon' => 'fas fa-user-slash',
                    'color' => 'danger',
                    'link' => route('users.banned')
                ],
                [
                    'label' => 'Total Posts',
                    'value' => DB::table('service_posts')->count(),
                    'icon' => 'fas fa-file-alt',
                    'color' => 'primary',
                    'link' => route('service_posts.index')
                ]
            ];

            return response()->json(['stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated users list with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $status = $request->input('status');
            $role = $request->input('role');
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // Base query
            $query = User::with(['photos', 'roles'])
                ->withCount(['reports', 'servicePosts'])
                ->addSelect([
                    'viewed_posts_count' => \DB::table('post_views')
                        ->selectRaw('count(*)')
                        ->whereColumn('post_views.user_id', 'users.id'),
                    'liked_posts_count' => \DB::table('favorites')
                        ->selectRaw('count(*)')
                        ->whereColumn('favorites.user_id', 'users.id'),
                ]);

            // Apply filters
            if ($status) {
                $query->where('is_active', $status);
            }

            if ($role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('roles.id', $role);
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', $search)
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phones', 'like', "%{$search}%");
                });
            }

            // Get paginated users
            $users = $query->orderBy('id', 'desc')->paginate($perPage);

            // Transform data for Vue
            $transformedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phones' => $user->phones,
                    'is_active' => $user->is_active,
                    'avatar_url' => $user->photos && $user->photos->count() > 0
                        ? ($user->photos->first()->is_external
                            ? $user->photos->first()->src
                            : asset($user->photos->first()->src))
                        : asset('vendor/adminlte/dist/img/user-default.jpg'),
                    'roles' => $user->roles->pluck('display_name')->filter()->values()->toArray(),
                    'reports_count' => $user->reports_count,
                    'service_posts_count' => $user->service_posts_count ?? 0,
                    'viewed_posts_count' => (int) ($user->viewed_posts_count ?? 0),
                    'liked_posts_count' => (int) ($user->liked_posts_count ?? 0),
                    'referral_code' => $user->referral_code,
                    'direct_referrals_count' => $user->direct_referrals_count ?? 0,
                    'total_referrals_count' => $user->total_referrals_count ?? 0,
                ];
            });

            return response()->json([
                'users' => [
                    'data' => $transformedUsers,
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Users API Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to load users',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user details with all related data
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with([
                'photos',
                'roles',
                'permissions',
                'servicePosts',
                'country',
                'city',
                'reports',
                'referrer',
            ])->findOrFail($id);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'gender' => $user->gender,
                    'date_of_birth' => $user->date_of_birth,
                    'phones' => $user->phones,
                    'WatsNumber' => $user->WatsNumber,
                    'auth_type' => $user->auth_type,
                    'data_saver_enabled' => $user->data_saver_enabled,
                    'location_latitudes' => $user->location_latitudes,
                    'location_longitudes' => $user->location_longitudes,
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                    'avatar' => $user->photos && $user->photos->count() > 0
                        ? ($user->photos->first()->is_external
                            ? $user->photos->first()->src
                            : asset($user->photos->first()->src))
                        : asset('vendor/adminlte/dist/img/user-default.jpg'),
                    'country' => $user->country ? [
                        'id' => $user->country->id,
                        'name' => $user->country->name
                    ] : null,
                    'city' => $user->city ? [
                        'id' => $user->city->id,
                        'name' => $user->city->name
                    ] : null,
                    'roles' => $user->roles,
                    'permissions' => $user->permissions,
                    'service_posts_count' => $user->servicePosts->count(),
                    'reports_count' => $user->reports->count(),
                    'points_balance' => $user->points ?? 0,
                    'viewed_posts_count' => \DB::table('post_views')->where('user_id', $user->id)->count(),
                    'recent_views' => \DB::table('post_views')
                        ->join('service_posts', 'service_posts.id', '=', 'post_views.service_post_id')
                        ->where('post_views.user_id', $user->id)
                        ->orderByDesc('post_views.viewed_at')
                        ->limit(10)
                        ->select([
                            'service_posts.id',
                            'service_posts.title',
                            'post_views.viewed_at',
                        ])
                        ->get()
                        ->map(fn($v) => [
                            'post_id' => $v->id,
                            'title' => $v->title,
                            'viewed_at' => $v->viewed_at,
                        ]),
                    'liked_posts_count' => \DB::table('favorites')->where('user_id', $user->id)->count(),
                    'recent_likes' => \DB::table('favorites')
                        ->join('service_posts', 'service_posts.id', '=', 'favorites.favoritable_id')
                        ->where('favorites.user_id', $user->id)
                        ->where('favorites.favoritable_type', 'App\\Models\\ServicePost')
                        ->orderByDesc('favorites.created_at')
                        ->limit(10)
                        ->select([
                            'service_posts.id',
                            'service_posts.title',
                            'favorites.created_at as liked_at',
                        ])
                        ->get()
                        ->map(fn($v) => [
                            'post_id' => $v->id,
                            'title' => $v->title,
                            'liked_at' => $v->liked_at,
                        ]),
                    // Referral data
                    'referral_code' => $user->referral_code,
                    'referred_by' => $user->referrer ? [
                        'id' => $user->referrer->id,
                        'user_name' => $user->referrer->user_name,
                    ] : null,
                    'direct_referrals_count' => $user->direct_referrals_count ?? 0,
                    'total_referrals_count' => $user->total_referrals_count ?? 0,
                    'recent_referrals' => $user->referrals()
                        ->with('photos')
                        ->orderByDesc('created_at')
                        ->limit(10)
                        ->get()
                        ->map(fn($r) => [
                            'id' => $r->id,
                            'user_name' => $r->user_name,
                            'name' => $r->name,
                            'avatar' => $r->photos->first()
                                ? ($r->photos->first()->is_external ? $r->photos->first()->src : asset($r->photos->first()->src))
                                : null,
                            'created_at' => $r->created_at->toISOString(),
                            'total_referrals_count' => $r->total_referrals_count ?? 0,
                        ]),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'User not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Toggle user ban status
     */
    public function toggleBan($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $newStatus = $user->is_active === 'banned' ? 'active' : 'banned';
            $user->is_active = $newStatus;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => $newStatus === 'banned' ? 'User has been banned' : 'User has been unbanned',
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update user status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $id,
                'phones' => 'nullable|string|max:50',
                'is_active' => 'nullable|in:active,inactive,banned',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
            ]);

            // Update basic info
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('phones')) {
                $user->phones = $request->phones;
            }
            if ($request->has('is_active')) {
                $user->is_active = $request->is_active;
            }

            $user->save();

            // Sync roles if provided
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phones' => $user->phones,
                    'is_active' => $user->is_active,
                    'roles' => $user->roles->pluck('display_name')->filter()->values()->toArray(),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting admin users
            if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
                return response()->json([
                    'error' => 'Cannot delete admin users'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all roles for filter dropdown
     */
    public function getRoles(): JsonResponse
    {
        try {
            $roles = Role::orderBy('name')->get(['id', 'name', 'display_name']);

            return response()->json(['roles' => $roles]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load roles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Add/deduct points for a user
     */
    public function adjustPoints(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user = \App\Models\User::findOrFail($id);

            $validated = $request->validate([
                'amount' => 'required|integer',
                'reason' => 'nullable|string|max:255',
                'notify' => 'nullable|boolean',
            ]);

            $amount = $validated['amount'];
            $reason = $validated['reason'] ?? ($amount > 0 ? 'Admin added points' : 'Admin deducted points');
            $notify = $validated['notify'] ?? true;

            \App\Models\palservice_points::create([
                'user_id' => $user->id,
                'point' => $amount,
                'name' => $reason,
                'price' => 0,
                'points' => abs($amount),
            ]);

            $newBalance = \App\Models\palservice_points::where('user_id', $user->id)->sum('point');

            if ($notify) {
                $this->notifyPointsAdjustment($user, $amount, $reason);
            }

            return response()->json([
                'message' => ($amount > 0 ? 'Added' : 'Deducted') . ' ' . abs($amount) . ' points. New balance: ' . $newBalance,
                'balance' => $newBalance,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to adjust points', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Notify a user (in-app + push) about an admin points adjustment.
     */
    private function notifyPointsAdjustment(User $user, int $amount, string $reason): void
    {
        $absAmount = abs($amount);

        $message = json_encode([
            'en' => $amount > 0
                ? "Admin has added $absAmount points to your account. ($reason)"
                : "Admin has deducted $absAmount points from your account. ($reason)",
            'ar' => $amount > 0
                ? "قام المسؤول بإضافة {$absAmount} نقطة إلى حسابك. ({$reason})"
                : "قام المسؤول بخصم {$absAmount} نقطة من حسابك. ({$reason})",
        ]);

        \App\Models\Notification::create([
            'message' => $message,
            'user_id' => $user->id,
            'type' => $amount > 0 ? 'pointIn' : 'pointOut',
        ]);

        // Push notification is only wired up for the "points added" wording;
        // deductions still get the in-app Notification record above.
        if ($amount > 0 && !empty($user->fcm_token)) {
            try {
                $user->notify(new \App\Notifications\point_purchase_notifications('approved', $absAmount));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send admin points adjustment FCM: ' . $e->getMessage());
            }
        }
    }

}
