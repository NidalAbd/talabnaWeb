<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\Notification;
use App\Models\Photos;
use App\Models\Role;
use App\Models\Sub_categories;
use App\Notifications\email_changed;
use App\Notifications\new_follower;
use App\Notifications\password_changed;
use App\Notifications\register;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\cities;
use App\Models\countries;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'nullable|string', // Add validation for device_token
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if (!Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $accessToken = $user->createToken('authToken')->accessToken;

        // Update FCM token if provided
        if ($request->has('device_token') && !empty($request->input('device_token'))) {
            $user->fcm_token = $request->input('device_token');
            $user->save();

            Log::info('FCM token updated during login', [
                'user_id' => $user->id,
                'token' => substr($request->input('device_token'), 0, 10) . '...' // Log only part of the token for security
            ]);
        }

        return response()->json([
            'access_token' => $accessToken,
            'user_id' => $user->id,
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_token' => 'nullable|string',
            'referral_code' => 'nullable|string|max:8',
        ]);

        // Check validation
        if ($validator->fails()) {
            Log::warning('Registration validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->except('password', 'password_confirmation')
            ]);

            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 400);
        }

        // Start database transaction for robust error handling
        DB::beginTransaction();
        try {
            // Fetch default country and city
            $country = Countries::first();
            if (!$country) {
                throw new \Exception('No country found for user registration');
            }
            $city = Cities::where('country_id', $country->id)->first();

            // Create user with FCM token
            $user = User::create([
                'name' => $request->input('name'),
                'user_name' => $this->generateUsername($request->input('name')),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'country_id' => $country->id,
                'city_id' => $city ? $city->id : null,
                'is_active' => 'active',
                'gender' => 'ذكر', // Default gender
                'email_verified_at' => now(),
                'fcm_token' => $request->input('device_token'), // Save FCM token during registration
            ]);

            // Assign default profile photo
            $photo = new Photos([
                'src' => fake()->randomElement([
                    'storage/photos/avatar1.png',
                    'storage/photos/avatar2.png',
                    'storage/photos/avatar3.png',
                    'storage/photos/avatar4.png',
                    'storage/photos/avatar5.png'
                ]),
            ]);
            $user->photos()->save($photo);

            // Generate access token
            $accessToken = $user->createToken('authToken')->accessToken;

            // Role and Permissions Assignment
            $role = Role::where('name', 'user')->first();

            // Log role details for debugging
            Log::info('Role assignment details', [
                'user_id' => $user->id,
                'role_found' => $role ? true : false,
                'role_id' => $role ? $role->id : null
            ]);
// Assign role to user
            $role = Role::where('name', 'user')->first();

            if ($role) {
                try {
                    // Manually attach role
                    $user->roles()->attach($role->id, ['user_type' => get_class($user)]);

                    // Get all permissions associated with this role
                    $permissions = $role->permissions;

                    // Sync permissions
                    if ($permissions->isNotEmpty()) {
                        $permissionIds = $permissions->pluck('id')->toArray();
                        $user->permissions()->sync($permissionIds);

                        Log::info('Role and permissions synced', [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                            'permission_count' => count($permissionIds)
                        ]);
                    } else {
                        Log::warning('No permissions found for role', [
                            'role_id' => $role->id,
                            'role_name' => $role->name
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Role and permission sync failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::warning('No default user role found', [
                    'available_roles' => Role::pluck('name')->toArray()
                ]);
            }

            // Welcome Notification
            $message = json_encode([
                'en' => "🎉 Welcome aboard! We're thrilled to have you here.",
                'ar' => "🎉 مرحبًا بك! نحن سعداء بانضمامك إلينا."
            ]);

            $notification = new Notification([
                'message' => $message,
                'user_id' => $user->id,
                'type' => 'login'
            ]);
            $user->notifications()->save($notification);

            // Process referral if code provided
            if ($request->filled('referral_code')) {
                User::processReferral($user, $request->input('referral_code'));
            }

            // Commit transaction
            DB::commit();

            // Return successful response
            return response()->json([
                'success' => true,
                'access_token' => $accessToken,
                'user_id' => $user->id,
                'message' => 'User registered successfully'
            ]);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Log detailed error
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except('password', 'password_confirmation')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to register user',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateDeviceToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $user = Auth::user();

            // Update the FCM token
            $user->fcm_token = $request->input('device_token');
            $user->save();

            Log::info('FCM token updated via dedicated endpoint', [
                'user_id' => $user->id,
                'token' => substr($request->input('device_token'), 0, 10) . '...' // Log only part of the token for security
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device token updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating device token', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update device token'
            ], 500);
        }
    }

// Helper method to generate unique username
    private function generateUsername($name)
    {
        // Convert name to lowercase and remove spaces
        $baseUsername = strtolower(str_replace(' ', '', $name));

        // Remove any non-alphanumeric characters
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);

        // If the username is empty after cleaning, use a default
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        // Add a random number to make it unique
        $username = $baseUsername . rand(100, 9999);

        // Check if username exists
        while (User::where('user_name', $username)->exists()) {
            $username = $baseUsername . rand(100, 9999);
        }

        return $username;
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['status' => 'success', 'message' => 'Logged out successfully']);
    }

    public function check_token(Request $request): JsonResponse
    {
        if ($request->user()) {
            return response()->json(['valid' => true]);
        }

        return response()->json(['valid' => false]);
    }

    public function UserPointBalance(User $user): JsonResponse
    {
        $pointBalance = $user->pointsBalance;
        $userId = $user->id;
        return response()->json([
            'userId' => $userId,
            'pointBalance' => $pointBalance
        ]);
    }



    public function show(User $user)
    {
        $user = Auth::user();
        if($user){
            $userData = $user->withCount('following')->withCount('followers')->withCount('servicePosts')->with('photos')->first();
            return response()->json(compact('userData'));
        }else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function UserProfile(User $user): JsonResponse
    {
        try {
            $userData = $user->loadCount(['following', 'followers', 'servicePosts' => function ($query) {
                $query->where('state', 'published');
            }])->load('photos','country', 'city' , 'roles' );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(compact('userData'));
    }


// Add this method to your UserController class
    public function banUser(Request $request, $userId): JsonResponse
    {
        // Validate if the current user has permission to ban
        $currentUser = Auth::user();
        $hasPermission = false;

        // Check for admin or moderator role
        if ($currentUser->hasRole('admin') || $currentUser->hasRole('moderator')) {
            $hasPermission = true;
        }

        // If no permission, return 403
        if (!$hasPermission) {
            return response()->json([
                'status' => 'error',
                'message' => json_encode([
                    'en' => 'You do not have permission to ban users',
                    'ar' => 'ليس لديك صلاحية حظر المستخدمين'
                ])
            ], 403);
        }

        // Find the target user
        $targetUser = User::find($userId);
        if (!$targetUser) {
            return response()->json([
                'status' => 'error',
                'message' => json_encode([
                    'en' => 'User not found',
                    'ar' => 'المستخدم غير موجود'
                ])
            ], 404);
        }

        // Get the ban reason
        $reason = $request->input('reason', 'Violated community guidelines');

        try {
            // Get device details if available
            $deviceId = $targetUser->device_token ?? null;

            // Prepare device details for ban record
            $deviceDetails = [
                'device_name' => $request->header('User-Agent') ?? null,
                'device_brand' => null,
                'device_model' => null,
                'os_version' => null,
                'ip_address' => $request->ip(),
            ];

            // Ban the user
            $bannedDevice = $targetUser->ban($reason, $deviceId, $deviceDetails, $currentUser->id);

            // Create notification for the banned user
            $message = json_encode([
                'en' => "Your account has been banned due to violation of our guidelines. Reason: {$reason}",
                'ar' => "تم حظر حسابك بسبب انتهاك إرشاداتنا. السبب: {$reason}"
            ]);

            $notification = new Notification([
                'message' => $message,
                'user_id' => $targetUser->id,
                'type' => 'ban'
            ]);

            $targetUser->notifications()->save($notification);

            // Log the ban action
            Log::info("User {$targetUser->id} ({$targetUser->email}) banned by {$currentUser->id} ({$currentUser->email}). Reason: {$reason}");

            return response()->json([
                'status' => 'success',
                'message' => json_encode([
                    'en' => 'User has been banned successfully',
                    'ar' => 'تم حظر المستخدم بنجاح'
                ]),
                'banned_device_id' => $bannedDevice ? $bannedDevice->id : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error banning user: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => json_encode([
                    'en' => 'Failed to ban user: ' . $e->getMessage(),
                    'ar' => 'فشل في حظر المستخدم: ' . $e->getMessage()
                ])
            ], 500);
        }
    }

    public function doFollowUnFollow($userId): JsonResponse
    {
        $followedUser = User::find($userId);
        $currentUser = auth()->user();
        $currentUser->following()->toggle($followedUser);
        $isFollower = $currentUser->following->contains($followedUser);

        if ($isFollower) {
            $message = json_encode([
                'en' => "{$currentUser->username} started following you! You can check your new follower in the profile followers section. 👥",
                'ar' => "بدأ {$currentUser->username} في متابعتك! يمكنك التحقق من المتابع الجديد في قسم المتابعين بالملف الشخصي. 👥"
            ]);

            $notification = new Notification([
                'message' => $message,
                'user_id' => $followedUser->id,
                'type'    => 'follower'
            ]);

            $followedUser->notifications()->save($notification);

            try {
                if (!empty($followedUser->fcm_token)) {
                    $followerName = $currentUser->user_name ?? $currentUser->name ?? 'Someone';
                    $followedUser->notify(new \App\Notifications\NewFollowerNotification($followerName, $currentUser->id));
                }
            } catch (\Exception $e) {
                \Log::warning('FCM follower notification failed: ' . $e->getMessage());
            }
        }

        return response()->json(['is_follower' => $isFollower]);
    }

    public function isFollowingUser(User $user): JsonResponse
    {
        $followedUser = User::find($user->id);
        $currentUser = auth()->user();
        $isFollower = $currentUser->following->contains($followedUser);
        return response()->json(['is_follower' => $isFollower]);

    }

    public function follow(User $user): JsonResponse
    {
        auth()->user()->following()->attach($user->id);
        return response()->json([
            'status' => 'success',
            'message' => 'You are now following '.$user->user_name
        ]);
    }

    public function unfollow(User $user): JsonResponse
    {
        auth()->user()->following()->detach($user->id);
        return response()->json([
            'status' => 'success',
            'message' => 'You have unfollowed '.$user->user_name
        ]);
    }
    public function UserFollowing(User $user): JsonResponse
    {
        if ($user) {
            $followings = $user->following()->select('users.id', 'users.user_name')
                ->with('photos','country', 'city')
                ->paginate(10);

            foreach ($followings as $following) {
                // Check if the current user is following this user
                $follow = $following->followers()->where('follower_id', Auth::id())->first();
                $following->is_follow = (bool)$follow;
            }

            return response()->json($followings);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    public function UserFollower(User $user): JsonResponse
    {
        $followers = $user->followers()->select('users.id', 'users.user_name')
            ->with('photos','country', 'city')
            ->paginate(10);

        foreach ($followers as $following) {
            // Check if the current user is following this user
            $follow = $following->followers()->where('follower_id', Auth::id())->first();
            $following->is_follow = (bool)$follow;
        }

        return response()->json($followers);
    }

    public function UserSeller(): JsonResponse
    {
        $Seller = User::whereHas('roles', function ($query) {
            $query->where('name', 'moderator');
        })->with('photos','country', 'city')
            ->paginate(10);
        foreach ($Seller as $following) {
            // Check if the current user is following this user
            $follow = $following->followers()->where('follower_id', Auth::id())->first();
            $following->is_follow = (bool)$follow;
        }
        return response()->json($Seller);
    }
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }


    public function update(Request $request, User $user): JsonResponse
    {
        Log::info('update user : ' . $user);
        Log::info('Request raw data: ', $request->all());

        try {
            // Check if the phone numbers are already taken by another user
            // This provides a second layer of validation in case Laravel's validation rules miss something
            $phones = $request->input('phones');
            $watsNumber = $request->input('WatsNumber');

            if (!empty($phones)) {
                $existingPhoneUser = User::where('phones', $phones)
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existingPhoneUser) {
                    return response()->json([
                        'status' => 'error',
                        'error_type' => 'unique_constraint',
                        'field' => 'phones',
                        'message' => json_encode([
                            'en' => "This phone number is already registered.",
                            'ar' => "رقم الهاتف هذا مسجل بالفعل."
                        ])
                    ], 422);
                }
            }

            if (!empty($watsNumber)) {
                $existingWatsUser = User::where('WatsNumber', $watsNumber)
                    ->where('id', '!=', $user->id)
                    ->first();

                if ($existingWatsUser) {
                    return response()->json([
                        'status' => 'error',
                        'error_type' => 'unique_constraint',
                        'field' => 'WatsNumber',
                        'message' => json_encode([
                            'en' => "This WhatsApp number is already registered.",
                            'ar' => "رقم الواتساب هذا مسجل بالفعل."
                        ])
                    ], 422);
                }
            }

            // Validate the incoming request
            $validatedData = $request->validate([
                'user_name' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:255',
                'country' => 'nullable',
                'city' => 'nullable',
                'device_token' => 'nullable|string|max:255',
                'phones' => 'nullable|unique:users,phones,'.$user->id,
                'WatsNumber' => 'nullable|unique:users,WatsNumber,'.$user->id,
                'date_of_birth' => 'nullable',
                'location_latitudes' => 'nullable|numeric|max:99999999',
                'location_longitudes' => 'nullable|numeric|max:99999999',
            ]);

            Log::info('Validated data: ', $validatedData);

            $user->user_name = $validatedData['user_name'] ?? $user->user_name;
            $user->gender = $validatedData['gender'] ?? $user->gender;
            $user->fcm_token = $validatedData['device_token'] ?? $user->fcm_token;

            if (isset($validatedData['country']) && is_array($validatedData['country']) && isset($validatedData['country']['id'])) {
                $user->country_id = $validatedData['country']['id'];
            }

            if (isset($validatedData['city']) && is_array($validatedData['city']) && isset($validatedData['city']['id'])) {
                $user->city_id = $validatedData['city']['id'];
            }

            $user->date_of_birth = $validatedData['date_of_birth'] ?? $user->date_of_birth;
            $user->location_latitudes = $validatedData['location_latitudes'] ?? $user->location_latitudes;
            $user->location_longitudes = $validatedData['location_longitudes'] ?? $user->location_longitudes;
            $user->phones = $validatedData['phones'] ?? $user->phones;
            $user->WatsNumber = $validatedData['WatsNumber'] ?? $user->WatsNumber;

            // Save the updated user record
            $user->save();

//            $message = json_encode([
//                'en' => "✅ Your profile has been successfully updated!",
//                'ar' => "✅ تم تحديث ملفك الشخصي بنجاح!"
//            ]);
//
//            $notification = new Notification([
//                'message' => $message,
//                'user_id' => Auth::id(),
//                'type'    => 'user'
//            ]);
//
//            $user->notifications()->save($notification);

            return response()->json(['status' => 'success', 'message' => 'info updated successfully.', 'user' => $user]);
        } catch (ValidationException $e) {
            // Handle validation errors
            $errors = $e->errors();
            $field = array_key_first($errors);
            $errorMessage = json_encode([
                'en' => $errors[$field][0] ?? "Validation error",
                'ar' => $this->getArabicValidationMessage($field, $errors[$field][0] ?? "")
            ]);

            Log::warning('Validation error: ' . json_encode($errors));

            return response()->json([
                'status' => 'error',
                'error_type' => 'validation',
                'field' => $field,
                'message' => $errorMessage
            ], 422);
        } catch (\Illuminate\Database\QueryException $exception) {
            Log::error('Database exception: ' . $exception->getMessage());

            // Check if it's a unique constraint violation
            $errorCode = $exception->errorInfo[1] ?? null;

            // MySQL duplicate entry error code
            if ($errorCode == 1062) {
                $errorMessage = $exception->getMessage();
                $response = [
                    'status' => 'error',
                    'error_type' => 'unique_constraint',
                    'message' => json_encode([
                        'en' => "This information is already in use by another account.",
                        'ar' => "هذه المعلومات قيد الاستخدام بالفعل من قبل حساب آخر."
                    ])
                ];

                // Determine which field caused the error
                if (strpos($errorMessage, 'phones') !== false) {
                    $response['field'] = 'phones';
                    $response['message'] = json_encode([
                        'en' => "This phone number is already registered.",
                        'ar' => "رقم الهاتف هذا مسجل بالفعل."
                    ]);
                } else if (strpos($errorMessage, 'WatsNumber') !== false) {
                    $response['field'] = 'WatsNumber';
                    $response['message'] = json_encode([
                        'en' => "This WhatsApp number is already registered.",
                        'ar' => "رقم الواتساب هذا مسجل بالفعل."
                    ]);
                } else if (strpos($errorMessage, 'email') !== false) {
                    $response['field'] = 'email';
                    $response['message'] = json_encode([
                        'en' => "This email is already registered.",
                        'ar' => "هذا البريد الإلكتروني مسجل بالفعل."
                    ]);
                }

                return response()->json($response, 422);
            }

            // Default database error handling
            return response()->json([
                'status' => 'error',
                'message' => 'Database error occurred: ' . $exception->getMessage()
            ], 500);
        } catch (\Exception $exception) {
            // Default error handling
            Log::error('General exception: ' . $exception->getMessage());
            Log::error('Exception trace: ' . $exception->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $exception->getMessage()
            ], 500);
        }
    }

// Helper method to get Arabic validation messages
    private function getArabicValidationMessage($field, $message) {
        $arabicMessages = [
            'phones.unique' => "رقم الهاتف هذا مسجل بالفعل.",
            'WatsNumber.unique' => "رقم الواتساب هذا مسجل بالفعل.",
            'email.unique' => "هذا البريد الإلكتروني مسجل بالفعل."
        ];

        $key = "$field.unique";
        return $arabicMessages[$key] ?? "خطأ في التحقق من صحة البيانات";
    }

    public function changeEmail(Request $request, $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }
        $oldEmail = $user->email;
        $user->email = $request->email;
        $user->save();
        $message = json_encode([
            'en' => "📩 Your email has been changed. If this wasn’t you, contact support immediately!",
            'ar' => "📩 تم تغيير بريدك الإلكتروني. إذا لم تقم بذلك، يرجى التواصل مع الدعم فورًا!"
        ]);

        $notification = new Notification([
            'message' => $message,
            'user_id' => Auth::id(),
            'type'    => 'email'
        ]);

        $user->notifications()->save($notification);
//        $user->notify(new email_changed($oldEmail));
        return response()->json(['message' => 'Email changed successfully.']);
    }


    public function changePassword(Request $request, $userId): JsonResponse
    {

        $user = User::findOrFail($userId);
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Old password is incorrect.'], 401);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        $message = json_encode([
            'en' => "🔒 Your password has been successfully updated.",
            'ar' => "🔒 تم تحديث كلمة المرور الخاصة بك بنجاح."
        ]);

        $notification = new Notification([
            'message' => $message,
            'user_id' => Auth::id(),
            'type'    => 'password'
        ]);

        $user->notifications()->save($notification);
//        $user->notify(new password_changed($user));
        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function updateProfilePhoto(Request $request, $userId): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);
            if ($request->hasFile('photo')) {
                $userPhotos = $user->photos;
                foreach ($userPhotos as $userPhoto) {
                    if (Storage::disk('public')->exists($userPhoto->src) && !in_array($userPhoto->src, ['photos/avatar1.png', 'photos/avatar2.png', 'photos/avatar3.png', 'photos/avatar4.png', 'photos/avatar5.png'])) {
                        Storage::delete($userPhoto->src);
                        $userPhoto->delete();
                    }else{
                        $userPhoto->delete();
                    }
                }
                $image = $request->file('photo');
                $fileName = $image->hashName();

                $storeImage = $image->storeAs('photos/profiles', $fileName, 'public');

                // Then create the path for the database that includes 'storage/'
                $photoPath = 'storage/' . $storeImage;
                $photo = new Photos([
                    'src' => $photoPath,
                ]);
                $user->photos()->save($photo);
                $message = json_encode([
                    'en' => "📸 Your profile picture has been updated!",
                    'ar' => "📸 تم تحديث صورة ملفك الشخصي!"
                ]);

                $notification = new Notification([
                    'message' => $message,
                    'user_id' => Auth::id(),
                    'type'    => 'photo'
                ]);

                $user->notifications()->save($notification);
                return response()->json(['message' => 'Your profile photo has been updated..' .$photoPath]);
            } else {
                return response()->json(['message' => 'Profile photo not changed.']);
            }
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|integer',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid search parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->input('user_id');
        $email = $request->input('email');
        $phone = $request->input('phone');

        // Create the query
        $query = User::query();

        // Add filters
        if ($userId) {
            $query->where('id', $userId);
        }

        if ($email) {
            $query->where('email', 'like', "%{$email}%");
        }

        if ($phone) {
            $query->where('phones', 'like', "%{$phone}%");
        }

        // Get users with minimal needed fields
        $users = $query->take(10)->get([
            'id', 'name', 'user_name', 'email', 'phones', 'is_active'
        ]);

        return response()->json([
            'success' => true,
            'users' => $users,
            'count' => $users->count(),
        ]);
    }
}
