<?php

namespace App\Services;

use App\Models\Categories;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Makes the card-shaped images for a category: ONE wide 3:2 render from the
 * image API, cut into the exact shapes the Categories screen draws.
 *
 *   banner_src  1440x480  (3:1)    "Most active" hero tile,  328x118dp on a 360dp phone
 *   tile_src     960x620  (1.55:1) "Browse all" grid tile,   158x102dp
 *
 * (The square `photos[0]` is left alone — it still serves the round sidebar
 * avatar and every already-installed app version. See the add_card_images
 * migration for why these are columns, not extra photo rows.)
 *
 * Standalone on purpose: it does not extend DalleImageService, whose square
 * 1024x1024 call and prompts are shared with the other image features.
 */
class CategoryCardImageService
{
    public const BANNER_W = 1440;
    public const BANNER_H = 480;
    public const TILE_W = 960;
    public const TILE_H = 620;

    /** gpt-image-1 wide size; the banner is cut from its middle half, the tile from nearly all of it. */
    public const SOURCE_SIZE = '1536x1024';

    /**
     * What each category is shown as. Mirrors the per-id subjects the square
     * icon generator uses, so a category keeps the same recognisable object.
     */
    private const SUBJECTS = [
        1 => "A professional briefcase with a document and pen beside it, representing '%s' job listings.",
        2 => "Grouped electronic devices — smartphone, laptop, headphones, camera — representing '%s'.",
        3 => "A stylish modern house with a small 'For Sale' sign, representing '%s' real estate listings.",
        4 => "A car key fob with a subtle car silhouette, representing '%s' automotive marketplace.",
        5 => "A toolbox with a wrench and a handshake, representing '%s' services marketplace.",
        8 => "A compassionate emergency-aid symbol — a megaphone beside a medical cross — representing '%s' urgent listings.",
    ];

    /**
     * Composition rules are what make the crops work. The banner keeps only the
     * vertical middle half of the frame, and the app overlays a badge (top-left),
     * an icon chip (top-right) and the category name (bottom-left) on it — so the
     * subject sits right of centre, compact, in the middle band, with calm
     * background elsewhere. The square icons use a WHITE background, which looks
     * washed out behind dark-mode cards, so this asks for a colour gradient.
     */
    private const STYLE = 'Wide banner composition. Modern 3D isometric render, soft gradient lighting, '
        . 'rounded geometric shapes, vibrant color palette, subtle drop shadow, premium app style. '
        . 'Background: a smooth, rich color gradient with a soft glow — NOT white, NOT transparent. '
        . 'Place the subject compactly to the right of center, within the vertical middle half of the frame. '
        . 'Keep the left third and the top and bottom quarters calm, uncluttered background. '
        . 'No text, no letters, no logos.';

    private ?string $lastError = null;

    private Client $client;

    public function __construct(?Client $client = null, private ?string $apiKey = null, private string $quality = 'high')
    {
        $this->client = $client ?? new Client(['timeout' => 180, 'connect_timeout' => 30]);
        $this->apiKey ??= (string) config('services.openai.key');
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function buildPrompt(Categories $category): string
    {
        $name = $category->name['en'] ?? $category->name['ar'] ?? 'Category';
        $subject = isset(self::SUBJECTS[$category->id])
            ? sprintf(self::SUBJECTS[$category->id], $name)
            : "An object clearly representing '{$name}' for a mobile marketplace app.";

        return $subject . ' ' . self::STYLE;
    }

    /**
     * Requests the wide render and returns the raw image bytes.
     *
     * @throws RuntimeException when the API call or its response is unusable
     */
    public function fetchWideImage(string $prompt): string
    {
        if ($this->apiKey === null || $this->apiKey === '') {
            throw new RuntimeException('OpenAI API key is not configured (OPENAI_API_KEY).');
        }

        try {
            $response = $this->client->post('https://api.openai.com/v1/images/generations', [
                'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
                'json' => [
                    'model' => 'gpt-image-1',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => self::SOURCE_SIZE,
                    'quality' => $this->quality,
                ],
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = json_decode((string) $e->getResponse()?->getBody(), true);
            throw new RuntimeException($body['error']['message'] ?? $e->getMessage(), 0, $e);
        }

        $data = json_decode((string) $response->getBody(), true)['data'][0] ?? null;
        if (isset($data['b64_json'])) {
            return base64_decode($data['b64_json'], true) ?: throw new RuntimeException('Image API returned invalid base64.');
        }
        if (isset($data['url'])) {
            return (string) $this->client->get($data['url'])->getBody();
        }

        throw new RuntimeException('Image API response had neither b64_json nor url.');
    }

    /**
     * Cuts one wide render into the two card shapes (JPEG bytes).
     *
     * @return array{banner: string, tile: string}
     */
    public function renderVariants(string $wideImageBytes): array
    {
        return [
            'banner' => CardImageCropper::crop($wideImageBytes, self::BANNER_W, self::BANNER_H),
            'tile' => CardImageCropper::crop($wideImageBytes, self::TILE_W, self::TILE_H),
        ];
    }

    /**
     * Generates, stores and records the card images for one category.
     * Old files are deleted only after the new ones are safely written.
     */
    public function generateForCategory(Categories $category): bool
    {
        $this->lastError = null;

        try {
            $variants = $this->renderVariants($this->fetchWideImage($this->buildPrompt($category)));

            $disk = Storage::disk('public');
            $token = Str::uuid();
            $bannerPath = "category/cards/{$category->id}-banner-{$token}.jpg";
            $tilePath = "category/cards/{$category->id}-tile-{$token}.jpg";
            $disk->put($bannerPath, $variants['banner']);
            $disk->put($tilePath, $variants['tile']);

            $previous = [$category->banner_src, $category->tile_src];
            $category->banner_src = 'storage/' . $bannerPath;
            $category->tile_src = 'storage/' . $tilePath;
            $category->save();

            foreach ($previous as $old) {
                if ($old) {
                    $disk->delete(preg_replace('#^storage/#', '', $old));
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::error("Category card image generation failed for category {$category->id}: " . $e->getMessage());

            return false;
        }
    }
}
