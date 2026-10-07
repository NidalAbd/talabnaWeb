<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeepLinkController extends Controller
{
    // Allowed routes for validation
    protected $allowedRoutes = [
        'service-post',
        'user',
        'category',
        'profile',
        'reels'
    ];

    public function redirect($route, $id = null)
    {
        // The app's ShareUtils.sharePost uses "post" for a service post.
        if ($route === 'post') {
            $route = 'service-post';
        }

        // Validate route and ID
        $validator = Validator::make(
            ['route' => $route, 'id' => $id],
            [
                'route' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!in_array($value, $this->allowedRoutes)) {
                            $fail("Invalid route: $value");
                        }
                    }
                ],
                'id' => 'nullable|numeric'
            ]
        );

        if ($validator->fails()) {
            Log::warning("Invalid deep link attempt", [
                'route' => $route,
                'id' => $id,
                'errors' => $validator->errors()
            ]);

            return response()->json([
                'error' => 'Invalid deep link',
                'details' => $validator->errors()
            ], 400);
        }

        // Validate content exists before generating deep link
        if ($id && !$this->checkResourceExists($route, $id)) {
            Log::warning("Deep link resource not found", [
                'route' => $route,
                'id' => $id
            ]);

            // Return a better user-friendly error page
            return view('deep-link-error', [
                'route' => $route,
                'id' => $id,
                'message' => "The requested content is no longer available",
                'suggestion' => "The post may have been deleted or made private by its creator.",
                'alternativeLink' => url('/'),  // Redirect to home page
                'alternativeLinkText' => 'Explore other content instead'
            ]);
        }

        // Log the request for debugging with more context
        Log::info("Deep link redirect processed", [
            'route' => $route,
            'id' => $id,
            'ip' => request()->ip()
        ]);

        // Build the deep link URL
        $appScheme = 'talabna://';
        $deepLink = $appScheme . $route;

        if ($id) {
            $deepLink .= '/' . $id;
        }

        // Platform-specific store URLs
        $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.talabna.talabna';
        $appStoreUrl = 'https://apps.apple.com/app/id6814376173';

        // A page that opens the app (or the right store) and gives WhatsApp / Facebook / X a preview card.
        return view('deep-link', [
            'deepLink' => $deepLink,
            'playStoreUrl' => $playStoreUrl,
            'appStoreUrl' => $appStoreUrl,
            'routeName' => $route === 'reels' ? 'reel' : ($route === 'service-post' ? 'post' : $route),
            'preview' => $this->preview($route, $id),
            'pageUrl' => url()->current(),
        ]);
    }

    /** Title, description and image of a shared post or reel, for the link preview. */
    protected function preview(string $route, $id): array
    {
        $preview = ['title' => 'Talabna', 'description' => 'Buy & sell nearby on Talabna.', 'image' => asset('storage/photos/og-image.jpg')];
        if (!$id || !in_array($route, ['service-post', 'reels'], true)) {
            return $preview;
        }
        try {
            $post = ServicePost::with('photos')->find($id);
            if (!$post) {
                return $preview;
            }
            $text = function ($value) {
                if (is_array($value)) {
                    $value = $value['ar'] ?? $value['en'] ?? collect($value)->first(fn ($v) => is_string($v) && $v !== '');
                }
                return is_string($value) ? trim(strip_tags($value)) : '';
            };
            $title = $text($post->getRawOriginal('title') ? json_decode($post->getRawOriginal('title'), true) ?? $post->getRawOriginal('title') : null);
            $description = $text($post->getRawOriginal('description') ? json_decode($post->getRawOriginal('description'), true) ?? $post->getRawOriginal('description') : null);
            if ($title !== '') {
                $preview['title'] = $title.' · Talabna';
            }
            if ($description !== '') {
                $preview['description'] = \Illuminate\Support\Str::limit($description, 180);
            }
            $photo = $post->photos->first(fn ($p) => !preg_match('/\.(mp4|mov|webm|m4v)$/i', (string) $p->src)) ?? null;
            if ($photo && $photo->src) {
                $preview['image'] = str_starts_with($photo->src, 'http') ? $photo->src : url(ltrim($photo->src, '/'));
            }
        } catch (\Throwable $e) {
            Log::warning('deep link preview failed', ['route' => $route, 'id' => $id, 'message' => $e->getMessage()]);
        }

        return $preview;
    }

    /**
     * API endpoint to validate deep link before navigation
     * @param string $route
     * @param string|int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateDeepLinkResource($route, $id)
    {
        if (!in_array($route, $this->allowedRoutes)) {
            return response()->json(['valid' => false], 404);
        }

        $isValid = $this->checkResourceExists($route, $id);

        return response()->json(['valid' => $isValid], $isValid ? 200 : 404);
    }

    /**
     * Check if deep link resource exists
     * @param string $route
     * @param string|int|null $id
     * @return bool
     */
    protected function checkResourceExists($route, $id = null)
    {
        // If no ID is provided, it's likely a general route, so pass validation
        if (!$id) {
            return true;
        }

        try {
            switch ($route) {
                case 'service-post':
                    $exists = ServicePost::where('id', $id)->where('state', 'published')->exists();
                    if (!$exists) {
                        Log::warning("Deep link validation failed: Service post #{$id} does not exist or is not published");
                    }
                    return $exists;

                case 'reels':
                    // For reels, also check if there's a valid post
                    $exists = ServicePost::where('id', $id)->where('state', 'published')->exists();
                    if (!$exists) {
                        Log::warning("Deep link validation failed: Reel/post #{$id} does not exist or is not published");
                    }
                    return $exists;

                case 'user':
                    $exists = User::where('id', $id)->where('is_active', 'active')->exists();
                    if (!$exists) {
                        Log::warning("Deep link validation failed: User #{$id} does not exist or is not active");
                    }
                    return $exists;

                case 'category':
                    $exists = Categories::where('id', $id)->where('is_suspended', false)->exists();
                    if (!$exists) {
                        Log::warning("Deep link validation failed: Category #{$id} does not exist or is suspended");
                    }
                    return $exists;

                default:
                    return true;
            }
        } catch (\Exception $e) {
            Log::error("Exception during deep link validation: " . $e->getMessage(), [
                'route' => $route,
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
