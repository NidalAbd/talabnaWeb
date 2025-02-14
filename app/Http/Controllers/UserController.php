<?php

namespace App\Http\Controllers;

use App\Models\Photos;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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
    public function index(): View|Factory|Application
    {
        $users = User::withCount('servicePosts')->withCount('reports')
            ->with('photos')->orderBy('id', 'desc')->paginate(10);
        return view('users.index')->with('Users', $users);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(): View|Factory|Application
    {
        return view('users.Create');
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
            'user_name' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'location_latitudes' => 'nullable|numeric|max:99999999',
            'location_longitudes' => 'nullable|numeric|max:99999999',
            'phones' => 'numeric|unique:users',
            'WatsNumber' => 'numeric|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'string|min:8',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2500',
        ]);
        $user = User::create([
            'user_name' => $validatedData['user_name'],
            'name' => $validatedData['name'],
            'gender' => $validatedData['gender'],
            'city' => $validatedData['city'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'location_latitudes' => $validatedData['location_latitudes'],
            'location_longitudes' => $validatedData['location_longitudes'],
            'phones' => $validatedData['phones'],
            'WatsNumber' => $validatedData['WatsNumber'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password'])
        ]);
        $user->attachRole('user');

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $fileName = $image->hashName();
            $photoPath = $image->storeAs('photos/profiles', $fileName);
            $photo = new Photos([
                'src' =>  $photoPath,]);
            $user->photos()->save($photo);

        }
        return redirect()->route('users.create')->with('success', 'User has been created successfully');
    }

    /**redirect()->route('users.edit', $user->id)
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function show($id)
    {
        $user = User::with(['servicePosts', 'photos'])->find($id);

        if (!$user) {
            return redirect('/users')->with('error', 'User not found1');
        }

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Application|Redirector|RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_name' => 'required|string|max:255',
            'photo' => 'image|mimes:jpeg,png,jpg|max:2500',
            'name' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phones' => 'required|numeric',
            'WatsNumber' => 'required|numeric',
            'is_active' => 'required|string',
            'date_of_birth' => 'required',
            'location_latitudes' => 'required|numeric|max:99999999',
            'location_longitudes' => 'required|numeric|max:99999999',
        ]);
        // Get the user record to update
        $user = User::find($id);
        // Update the user record with new data
        $user->user_name = $validatedData['user_name'] ?? $user->user_name;
        if ($request->hasFile('photo')) {
            $userPhoto = $user->photos()->first();
            if(file_exists($userPhoto) && !in_array($userPhoto, ['photos/avatar1.png', 'photos/avatar2.png', 'photos/avatar3.png', 'photos/avatar4.png', 'photos/avatar5.png'])){
                foreach ($userPhoto as $photo) {
                    Storage::delete($photo->src);
                    $photo->delete();
                }
            }else{
                $userPhoto->delete();
            }
            $image = $request->file('photo');
            $fileName = $image->hashName();
            $photoPath = $image->storeAs('photos/profiles', $fileName);
            $photo = new Photos([
                'src' =>  $photoPath,]);
            $user->photos()->save($photo);

        }
        $user->name = $validatedData['name'] ?? $user->name;
        $user->gender = $validatedData['gender'] ?? $user->gender;
        $user->country_id = $validatedData['country']['id'] ?? $user->country_id;
        $user->city_id = $validatedData['city']['id'] ?? $user->city_id;
        $user->date_of_birth = $validatedData['date_of_birth'] ?? $user->date_of_birth;
        $user->location_latitudes = $validatedData['location_latitudes'] ?? $user->location_latitudes;
        $user->location_longitudes = $validatedData['location_longitudes'] ?? $user->location_longitudes;
        $user->phones = $validatedData['اجهزة'] ?? $user->phones;
        $user->WatsNumber = $validatedData['WatsNumber'] ?? $user->WatsNumber;
        $user->is_active = $validatedData['is_active'] ?? $user->is_active;
        $user->email = $validatedData['email'] ?? $user->email;
        // Save the updated user record
        $user->save();

        return redirect()->route('users.edit', $user->id)->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Application|Redirector|RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // Delete the user's photo from the polymorphic relation
        $userPhoto = $user->photos()->first();
        if(file_exists($userPhoto) && !in_array($userPhoto, ['photos/avatar1.png', 'photos/avatar2.png', 'photos/avatar3.png', 'photos/avatar4.png', 'photos/avatar5.png'])){
            foreach ($userPhoto as $photo) {
                Storage::delete($photo->src);
                $photo->delete();
            }
        }else{
            $userPhoto->delete();
        }
        // Get all service posts related to the user and their photos
        $servicePosts = $user->servicePosts()->with('photos')->get();
        foreach ($servicePosts as $servicePost) {
            foreach ($servicePost->photos as $photo) {
                Storage::delete($photo->src);
                $photo->delete();
            }
            // Delete the service post
            $servicePost->delete();
        }
        // Delete the user's photo file from storage
        if ($user->photo && $user->photo !== 'photos/avatar1.png' && $user->photo !== 'photos/avatar2.png' && $user->photo !== 'photos/avatar3.png' && $user->photo !== 'photos/avatar4.png' && $user->photo !== 'photos/avatar5.png') {
            Storage::delete('public/' . $user->photo);
        }
        // Delete the user
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User has been deleted!');
    }


}
