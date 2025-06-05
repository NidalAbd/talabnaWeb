<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Jobs\DeleteFacebookUserDataJob;

class FacebookController extends Controller
{
    /**
     * Handle data deletion request from Facebook
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleDataDeletion(Request $request)
    {
        // Log the received request for debugging
        Log::info('Facebook data deletion request received', [
            'signed_request' => $request->input('signed_request')
        ]);

        // Verify the request is coming from Facebook
        $signedRequest = $request->input('signed_request');

        if (empty($signedRequest)) {
            Log::error('Facebook data deletion: No signed request provided');
            return response()->json(['error' => 'No signed request provided'], 400);
        }

        // Parse the signed request
        $data = $this->parseSignedRequest($signedRequest, config('services.facebook.client_secret'));

        if (!$data) {
            Log::error('Facebook data deletion: Invalid signed request');
            return response()->json(['error' => 'Invalid request'], 400);
        }

        // Extract the user ID from the request
        $userId = $data['user_id'];
        $confirmationCode = md5($userId . time()); // Generate a unique code

        Log::info('Facebook data deletion: Processing request', [
            'facebook_user_id' => $userId,
            'confirmation_code' => $confirmationCode
        ]);

        // Queue a job to delete this user's Facebook-related data
        DeleteFacebookUserDataJob::dispatch($userId, $confirmationCode);

        // Return a confirmation code that Facebook requires
        return response()->json([
            'url' => url('/api/facebook/deletion-status?id=' . $confirmationCode),
            'confirmation_code' => $confirmationCode
        ]);
    }

    /**
     * Get the status of a data deletion request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeletionStatus(Request $request)
    {
        $confirmationCode = $request->query('id');

        if (empty($confirmationCode)) {
            return response()->json(['error' => 'No confirmation code provided'], 400);
        }

        // Check if deletion is complete for this code
        $isDeletionComplete = Cache::has('fb_deletion_' . $confirmationCode);

        Log::info('Facebook data deletion: Status check', [
            'confirmation_code' => $confirmationCode,
            'status' => $isDeletionComplete ? 'complete' : 'pending'
        ]);

        if ($isDeletionComplete) {
            return response()->json(['status' => 'complete']);
        } else {
            return response()->json(['status' => 'pending']);
        }
    }

    /**
     * Parse a signed request from Facebook
     *
     * @param string $signedRequest
     * @param string $secret
     * @return array|null
     */
    private function parseSignedRequest($signedRequest, $secret)
    {
        // Get the parts of the signed request
        $parts = explode('.', $signedRequest, 2);

        if (count($parts) != 2) {
            return null;
        }

        list($encodedSig, $payload) = $parts;

        // Decode the data
        $sig = $this->base64UrlDecode($encodedSig);
        $data = json_decode($this->base64UrlDecode($payload), true);

        // Check the algorithm
        if (!isset($data['algorithm']) || $data['algorithm'] !== 'HMAC-SHA256') {
            return null;
        }

        // Verify the signature
        $expectedSig = hash_hmac('sha256', $payload, $secret, true);
        if ($sig !== $expectedSig) {
            return null;
        }

        return $data;
    }

    /**
     * Base64URL decode a string
     *
     * @param string $input
     * @return string
     */
    private function base64UrlDecode($input)
    {
        // Replace URL safe characters
        $input = strtr($input, '-_', '+/');

        // Add padding if needed
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode($input);
    }
}
