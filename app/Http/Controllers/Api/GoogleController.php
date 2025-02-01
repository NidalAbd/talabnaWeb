<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Client;
use Google\Service\CloudBuild\Hash;
use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $client = app(Client::class);

        $url = $client->createAuthUrl();

        return redirect($url);
    }

    /**
     * Handle the Google authentication callback.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setIncludeGrantedScopes(true);

        $code = $request->query('code');
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return response()->json([
                'error' => 'Could not authenticate with Google. Please try again later.',
            ], 401);
        }

        $client->setAccessToken($token['access_token']);
        $service = new Google_Service_Oauth2($client);
        $user = $service->userinfo->get();

        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            Auth::login($existingUser);
        } else {
            $newUser = new User();
            $newUser->name = $user->name;
            $newUser->email = $user->email;
            $newUser->password = Hash::make(Str::random(20));
            $newUser->user_name = $user->user_name;
            $newUser->gender = $user->gender;
            $newUser->phones = $user->phones;
            $newUser->photos = $user->photos;
            $newUser->date_of_birth = $user->date_of_birth;
            $newUser->save();
            Auth::login($newUser);
        }

        return response()->json([
            'message' => 'User authenticated successfully.',
        ]);
    }
}
