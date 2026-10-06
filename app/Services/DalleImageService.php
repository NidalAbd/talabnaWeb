<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\Photos;
use App\Models\Sub_categories;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DalleImageService
{
    protected Client $client;
    protected string $apiKey;

    protected ?string $lastError = null;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->client = new Client([
            'timeout' => 120,
            'connect_timeout' => 30,
        ]);
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Shared style suffix for category images — a soft-shadow 3D/isometric
     * render (rounded geometric shapes, gradient lighting, vibrant color)
     * instead of the old flat-illustration look, which reads as dated now
     * that most premium apps have moved to this softer 3D style. Only the
     * rendering style changes here — each category's actual subject (what
     * object represents it) is untouched, so the icon stays recognizable.
     */
    protected const CATEGORY_STYLE_SUFFIX = 'Modern 3D isometric icon render, soft gradient lighting, '
        . 'rounded geometric shapes, vibrant color palette, subtle drop shadow, premium app icon style, '
        . 'centered, white background, no text.';

    /**
     * Category-specific prompt templates.
     * Each produces a clear, instantly recognizable icon.
     */
    protected function getCategoryPrompt(string $nameEn, string $nameAr, int $catId): string
    {
        $subjects = [
            1 => "A professional briefcase with a document and pen beside it, representing '{$nameEn}' job listings.",
            2 => "Grouped electronic devices — smartphone, laptop, headphones, camera — representing '{$nameEn}' category.",
            3 => "A stylish modern house with a small 'For Sale' sign, representing '{$nameEn}' real estate listings.",
            4 => "A car key fob with a subtle car silhouette, representing '{$nameEn}' automotive marketplace.",
            5 => "A toolbox with a wrench and a handshake, representing '{$nameEn}' services marketplace.",
            6 => "A location pin with small nearby shop icons around it, representing '{$nameEn}' nearby services.",
            7 => "A play button with a film reel, representing '{$nameEn}' video content.",
            8 => "A compassionate emergency-aid symbol — a megaphone beside a medical cross — representing '{$nameEn}' urgent/emergency listings.",
        ];

        $subject = $subjects[$catId] ?? "An icon clearly representing '{$nameEn}' (Arabic: '{$nameAr}') for a mobile marketplace app.";
        return "{$subject} " . self::CATEGORY_STYLE_SUFFIX;
    }

    /**
     * Subcategory-specific prompt builder.
     * Handles brands (car logos, device products) and job types differently.
     * Every subcategory uses the same 3D style as the categories (2026-10-06: they were still "clean flat
     * illustration" and looked dated next to the 3D category icons). Car brands stay their real logo.
     */
    protected function getSubcategoryPrompt(string $nameEn, string $nameAr, int $catId, ?string $categoryNameEn): string
    {
        // Known car brands — generate recognizable brand logo style
        $carBrands = ['BMW', 'Mercedes', 'Toyota', 'Honda', 'Audi', 'Porsche', 'Ferrari', 'Lamborghini',
            'Ford', 'Chevrolet', 'Nissan', 'Hyundai', 'Kia', 'Mazda', 'Lexus', 'Jaguar', 'Bentley',
            'Rolls-Royce', 'Bugatti', 'McLaren', 'Maserati', 'Volvo', 'Volkswagen', 'Fiat', 'Peugeot',
            'Renault', 'Citroen', 'Skoda', 'Seat', 'Opel', 'Suzuki', 'Subaru', 'Mitsubishi',
            'Dodge', 'Chrysler', 'Cadillac', 'Lincoln', 'Buick', 'GMC', 'Jeep', 'Hummer',
            'Mini Cooper', 'Smart', 'Daihatsu', 'Isuzu', 'SsangYong', 'MG', 'Chery', 'Tata',
            'Infiniti', 'Aston Martin', 'Lancia', 'Alpha Romeo', 'Saab', 'Rover', 'Proton',
            'Daewoo', 'Dacia', 'Datsun', 'Mercury', 'Plymouth', 'Oldsmobile', 'Morgan',
            'Austin', 'Lada', 'Simca', 'DFSK', 'JAC', 'Asia', 'Saba', 'Classic'];

        // Cars category (id=4)
        if ($catId === 4) {
            if (in_array($nameEn, $carBrands)) {
                return "The official {$nameEn} car brand logo, clean and recognizable, on a white background. High quality, sharp, centered. No extra decoration, no car image, just the brand emblem/logo.";
            }
            // Non-brand car subcategories (Spare Parts, Rental, etc.)
            return "An object clearly representing '{$nameEn}' in an automotive context. " . self::CATEGORY_STYLE_SUFFIX;
        }

        // Devices category (id=2)
        if ($catId === 2) {
            return "A single {$nameEn} product, no brand logo. " . self::CATEGORY_STYLE_SUFFIX;
        }

        // Jobs category (id=1)
        if ($catId === 1) {
            $jobIcons = [
                'Management' => 'a person at a desk with a chart',
                'Programming' => 'a laptop with code brackets </>',
                'Design' => 'a pen tool and color palette',
                'Education and Teaching' => 'a blackboard with a book',
                'Driver' => 'a steering wheel',
                'Engineer' => 'a hard hat with blueprints',
                'Construction' => 'a crane and building',
                'Medicine and Nursing' => 'a stethoscope and medical cross',
                'Accounting' => 'a calculator with coins',
                'Law and Legal' => 'a scales of justice',
                'Customer Service' => 'a headset with speech bubble',
                'Sales and Marketing' => 'a megaphone with a chart going up',
                'Fitness' => 'a dumbbell',
                'Cleaning Workers' => 'a mop and bucket',
                'Delivery Workers' => 'a delivery box on a scooter',
                'Guard and Security' => 'a shield with a checkmark',
                'Childcare' => 'a baby cradle with a heart',
                'Tourism and Restaurants' => 'a chef hat with a plate',
                'Data Entry' => 'a keyboard with a document',
                'Human Resources' => 'people icons with a handshake',
                'Translators' => 'two speech bubbles in different languages',
                'Fine Arts' => 'a paintbrush and canvas',
                'Computer and Networks' => 'a server with network cables',
                'Secretarial' => 'a notepad with a pen',
                'Tailors' => 'scissors and thread',
                'Craftsmen' => 'a hammer and wrench',
                'Fashion' => 'a dress form mannequin',
                'Editors' => 'a pencil with paper',
                'Montage and Editing' => 'a video timeline with scissors',
                'Laborers' => 'a hard hat with gloves',
                'Household Workers' => 'a house with a broom',
                'Public Relations' => 'a microphone with people',
                'Tourism and Travel' => 'a suitcase with a plane',
                'Partnership' => 'two hands shaking',
                'Gardens and Landscapes' => 'a tree with a garden shovel',
                'Decoration and Beauty' => 'a mirror with a lipstick',
            ];
            $icon = $jobIcons[$nameEn] ?? "a person working as {$nameEn}";
            return "{$icon}, representing the job category '{$nameEn}', instantly recognizable. " . self::CATEGORY_STYLE_SUFFIX;
        }

        // Houses category (id=3)
        if ($catId === 3) {
            $houseIcons = [
                'Lands' => 'an empty plot of land with a fence',
                'Apartments' => 'an apartment building',
                'Villas and Houses' => 'a luxury villa with a garden',
                'Shops' => 'a shop storefront with an awning',
                'Offices' => 'an office building with glass windows',
                'Farms' => 'a farm with a barn and field',
                'Hotels and Resorts' => 'a hotel with palm trees',
                'Warehouses' => 'a large warehouse building',
                'Factories' => 'a factory with chimneys',
                'Restaurants and Cafes' => 'a cafe with tables outside',
                'Studio' => 'a small studio apartment interior',
                'Furnished Apartments' => 'a furnished living room',
                'Shared Housing' => 'a house with multiple people icons',
                'Under Construction' => 'a building with a crane',
                'Commercial Complexes' => 'a shopping mall',
                'Event Halls' => 'a large elegant hall',
                'Worker Accommodation' => 'simple housing blocks',
                'Condominiums and Buildings' => 'a tall residential tower',
                'Traditional House' => 'a traditional Arabic house',
            ];
            $icon = $houseIcons[$nameEn] ?? "a building representing {$nameEn}";
            return "{$icon}, representing '{$nameEn}' in real estate. " . self::CATEGORY_STYLE_SUFFIX;
        }

        // Urgent/Emergency category (id=8)
        if ($catId === 8) {
            return "An object clearly representing '{$nameEn}' in an emergency/humanitarian context, compassionate. " . self::CATEGORY_STYLE_SUFFIX;
        }

        // Services category (id=5) and others
        return "An object clearly representing the '{$nameEn}' service, instantly recognizable. No logo. " . self::CATEGORY_STYLE_SUFFIX;
    }

    /**
     * Generate an AI image for a category and save it as the category photo.
     */
    public function generateForCategory(Categories $category): bool
    {
        $nameEn = $category->name['en'] ?? '';
        $nameAr = $category->name['ar'] ?? '';

        $prompt = $this->getCategoryPrompt($nameEn, $nameAr, $category->id);
        try {
            $this->lastError = null;

            if (empty($this->apiKey)) {
                $this->lastError = 'OpenAI API key is not configured. Add OPENAI_API_KEY to your .env file.';
                Log::error("DALL-E: " . $this->lastError);
                return false;
            }

            $imageContents = $this->callImageApi($prompt);
            if (!$imageContents) {
                Log::error("Image generation returned no data for category {$category->id}: " . ($this->lastError ?? 'unknown'));
                return false;
            }

            $fileName = Str::uuid() . '.png';
            $storagePath = "category/{$fileName}";

            // Move old photo to gallery instead of deleting
            $oldPhoto = $category->photos()->first();
            if ($oldPhoto && $oldPhoto->src) {
                $this->copyToGallery('category', $category->id, $oldPhoto->src);
                $oldPath = str_replace('storage/', '', $oldPhoto->src);
                Storage::disk('public')->delete($oldPath);
                $oldPhoto->delete();
            } elseif ($oldPhoto) {
                $oldPhoto->delete();
            }

            // Save new image
            Storage::disk('public')->put($storagePath, $imageContents);

            $photo = new Photos([
                'src' => 'storage/' . $storagePath,
            ]);
            $category->photos()->save($photo);

            // Also save a copy to the gallery
            $this->saveToGallery('category', $category->id, $storagePath);

            return true;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // callImageApi's own catch already set $this->lastError from this
            // exact response — re-reading $e->getResponse()->getBody() here
            // would return an empty string (a PSR-7 stream can only be read
            // once), which is why every failure used to log an empty error.
            Log::error("Image API error for category {$category->id}: " . ($this->lastError ?? $e->getMessage()));
            return false;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error("Image generation failed for category {$category->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate an AI image for a subcategory and save it as the subcategory photo.
     */
    public function generateForSubcategory(Sub_categories $subcategory): bool
    {
        $nameEn = $subcategory->name['en'] ?? '';
        $nameAr = $subcategory->name['ar'] ?? '';
        $category = $subcategory->category;
        $catId = $category ? $category->id : 0;
        $categoryNameEn = $category ? ($category->name['en'] ?? '') : '';

        $prompt = $this->getSubcategoryPrompt($nameEn, $nameAr, $catId, $categoryNameEn);
        try {
            $this->lastError = null;

            if (empty($this->apiKey)) {
                $this->lastError = 'OpenAI API key is not configured. Add OPENAI_API_KEY to your .env file.';
                Log::error("DALL-E: " . $this->lastError);
                return false;
            }

            $imageContents = $this->callImageApi($prompt);
            if (!$imageContents) {
                Log::error("Image generation returned no data for subcategory {$subcategory->id}: " . ($this->lastError ?? 'unknown'));
                return false;
            }

            $fileName = Str::uuid() . '.png';

            // Get category name for folder structure
            $category = $subcategory->category;
            $categoryName = $category ? ($category->name['ar'] ?? 'unknown') : 'unknown';
            $storagePath = "subcategory/{$categoryName}/{$fileName}";

            // Move old photo to gallery instead of deleting
            $oldPhoto = $subcategory->photos()->first();
            if ($oldPhoto && $oldPhoto->src) {
                $this->copyToGallery('subcategory', $subcategory->id, $oldPhoto->src);
                $oldPath = str_replace('storage/', '', $oldPhoto->src);
                Storage::disk('public')->delete($oldPath);
                $oldPhoto->delete();
            } elseif ($oldPhoto) {
                $oldPhoto->delete();
            }

            // Save new image
            Storage::disk('public')->put($storagePath, $imageContents);

            $photo = new Photos([
                'src' => 'storage/' . $storagePath,
            ]);
            $subcategory->photos()->save($photo);

            // Also save a copy to the gallery
            $this->saveToGallery('subcategory', $subcategory->id, $storagePath);

            return true;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error("Image API error for subcategory {$subcategory->id}: " . ($this->lastError ?? $e->getMessage()));
            return false;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error("Image generation failed for subcategory {$subcategory->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Copy an existing photo file to the gallery folder.
     */
    protected function copyToGallery(string $type, int $id, string $src): void
    {
        $sourcePath = str_replace('storage/', '', $src);
        if (!Storage::disk('public')->exists($sourcePath)) {
            return;
        }

        $galleryDir = "ai-gallery/{$type}/{$id}";
        $fileName = Str::uuid() . '.png';
        $galleryPath = "{$galleryDir}/{$fileName}";

        Storage::disk('public')->copy($sourcePath, $galleryPath);
    }

    /**
     * Save a copy of the new image to the gallery folder.
     */
    protected function saveToGallery(string $type, int $id, string $storagePath): void
    {
        if (!Storage::disk('public')->exists($storagePath)) {
            return;
        }

        $galleryDir = "ai-gallery/{$type}/{$id}";
        $fileName = Str::uuid() . '.png';
        $galleryPath = "{$galleryDir}/{$fileName}";

        Storage::disk('public')->copy($storagePath, $galleryPath);
    }

    /**
     * Generate a post photo from a DALL-E prompt and save it.
     * Returns the storage path (e.g. "storage/posts/ai/uuid.png") or null on failure.
     */
    public function generatePostPhoto(string $prompt): ?string
    {
        try {
            $this->lastError = null;

            if (empty($this->apiKey)) {
                $this->lastError = 'OpenAI API key is not configured. Add OPENAI_API_KEY to your .env file.';
                Log::error("DALL-E: " . $this->lastError);
                return null;
            }

            $imageContents = $this->callImageApi($prompt);
            if (!$imageContents) {
                Log::error("Image generation returned no data for post photo: " . ($this->lastError ?? 'unknown'));
                return null;
            }

            $fileName = Str::uuid() . '.png';
            $storagePath = "posts/ai/{$fileName}";

            Storage::disk('public')->put($storagePath, $imageContents);

            return 'storage/' . $storagePath;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error("Image API post photo error: " . ($this->lastError ?? $e->getMessage()));
            return null;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error("Image post photo generation failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Call the OpenAI image generation API and return the raw image bytes.
     *
     * Was hardcoded to 'dall-e-3', which OpenAI has since removed from this
     * account entirely (confirmed via GET /v1/models — a 400
     * "model does not exist" was the actual failure on every call, though a
     * separate bug — re-reading an already-consumed PSR-7 response stream in
     * the callers' catch blocks — was masking it as an empty error message).
     * gpt-image-1 is the replacement; unlike dall-e-3 it returns base64
     * image data (b64_json), not a hosted URL, so this decodes that
     * directly instead of doing a second GET for a url field that no
     * longer exists in the response. Falls back to the url field too, in
     * case a future model reintroduces it.
     */
    protected function callImageApi(string $prompt): ?string
    {
        try {
            $response = $this->client->post('https://api.openai.com/v1/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-image-1',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                    'quality' => 'high',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $data = $body['data'][0] ?? null;

            if ($data === null) {
                $this->lastError = 'Unexpected image API response shape: ' . json_encode($body);
                return null;
            }

            if (isset($data['b64_json'])) {
                return base64_decode($data['b64_json']);
            }

            if (isset($data['url'])) {
                return $this->client->get($data['url'])->getBody()->getContents();
            }

            $this->lastError = 'Image API response had neither b64_json nor url: ' . json_encode($data);
            return null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response';
            $errorData = json_decode($responseBody, true);
            $this->lastError = $errorData['error']['message'] ?? $responseBody;
            Log::error("Image API call failed: " . $this->lastError);
            throw $e;
        }
    }
}
