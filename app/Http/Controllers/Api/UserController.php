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

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if (!Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = Auth::user();
        $accessToken = $user->createToken('authToken')->accessToken;


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
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        try {
            $country = countries::inRandomOrder()->first();
            $city = cities::where('country_id', $country->id)->inRandomOrder()->first();
            $user = User::create([
                'user_name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'country_id' => $country->id,
                'city_id' => $city->id,
            ]);
            $photo = new Photos([
                'src' => fake()->randomElement(['photos/avatar1.png', 'photos/avatar2.png', 'photos/avatar3.png', 'photos/avatar4.png', 'photos/avatar5.png']),
            ]);

            $user->photos()->save($photo);
            // Generate access token for the newly registered user
            $accessToken = $user->createToken('authToken')->accessToken;

            // Assign role to user
            $role = Role::where('name', 'user')->first();
            $permissions = $role->permissions;
            $user->attachRole($role);
            $user->syncPermissions($permissions);
            $user->notify(new register($user));
            $message = "Welcome to our platform! We're glad to have you join us.";
            $type = "login";
            $notification = new Notification([
                'message' => $message,
                'user_id' => $user->id,
                'type'    => $type,
            ]);
            $user->notifications()->save($notification);
            return response()->json([
                'access_token' => $accessToken,
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Unable to register user'], 500);
        }
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
            }])->load('photos','country', 'city');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(compact('userData'));
    }

    public function doFollowUnFollow($userId): JsonResponse
    {
        $followedUser = User::find($userId);
        $currentUser = auth()->user();
        $currentUser->following()->toggle($followedUser);
        $isFollower = $currentUser->following->contains($followedUser);

        if ($isFollower) {
            $message = "$currentUser->user_name is now following you";
            $notification = new Notification([
                'message' => $message,
                'user_id' => $followedUser->id,
                'type'    => 'follower'
            ]);
            $followedUser->notifications()->save($notification);
            // Send email notification to user
//            $followedUser->notify(new new_follower($currentUser));
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
        })->with('photos')
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
        Log::info('update user : ' .$user);

        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'gender' => 'nullable|string|max:255',
                'country' => 'nullable',
                'city' => 'nullable',
                'device_token' => 'nullable|string|max:255',
                'phones' => 'nullable',
                'WatsNumber' => 'nullable',
                'date_of_birth' => 'nullable',
                'location_latitudes' => 'nullable|numeric|max:99999999',
                'location_longitudes' => 'nullable|numeric|max:99999999',
            ]);
            Log::info('Request' , [$validatedData]);
            $user->user_name = $validatedData['user_name'] ?? $user->user_name;
            $user->gender = $validatedData['gender'] ?? $user->gender;
            $user->fcm_token = $validatedData['device_token'] ?? $user->device_token;
            $user->country_id = $validatedData['country']['id'] ?? $user->country_id;
            $user->city_id = $validatedData['city']['id'] ?? $user->city_id;
            $user->date_of_birth = $validatedData['date_of_birth'] ?? $user->date_of_birth;
            $user->location_latitudes = $validatedData['location_latitudes'] ?? $user->location_latitudes;
            $user->location_longitudes = $validatedData['location_longitudes'] ?? $user->location_longitudes;
            $user->phones = $validatedData['phones'] ?? $user->phones;
            $user->WatsNumber = $validatedData['WatsNumber'] ?? $user->WatsNumber;
            // Save the updated user record
            $user->save();
            $message = "Your profile has been updated successfully";
            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type'    => 'user'
            ]);
            $user->notifications()->save($notification);
            return response()->json(['status' => 'success', 'message' => 'info updated successfully.', 'user' => $user]);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
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
        $message = "Your email address has been changed. If you did not initiate this change, please contact us.";
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
        $message = "Your password has been changed successfully";
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
                $photoPath = $image->storeAs('photos/profiles', $fileName);
                $photo = new Photos([
                    'src' => $photoPath,
                ]);
                $user->photos()->save($photo);
                $message = "Your profile photo has been updated.";
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


}
