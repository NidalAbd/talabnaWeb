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
     * Category-specific prompt templates.
     * Each produces a clear, instantly recognizable icon.
     */
    protected function getCategoryPrompt(string $nameEn, string $nameAr, int $catId): string
    {
        $prompts = [
            1 => "A clean flat illustration of a professional person holding a briefcase and a document, representing '{$nameEn}' job listings. Simple, friendly, modern style. White background. No text.",
            2 => "A clean flat illustration of various electronic devices grouped together: smartphone, laptop, headphones, camera. Representing '{$nameEn}' category. Simple modern style. White background. No text.",
            3 => "A clean flat illustration of a modern house with a 'For Sale' sign in front, representing '{$nameEn}' real estate listings. Simple, friendly style. White background. No text.",
            4 => "A clean flat illustration of a car key with a small car silhouette, representing '{$nameEn}' automotive marketplace. Simple modern style. White background. No text.",
            5 => "A clean flat illustration of a toolbox with a wrench and a handshake, representing '{$nameEn}' services marketplace. Simple modern style. White background. No text.",
            6 => "A clean flat illustration of a map pin with a location marker and nearby shops, representing '{$nameEn}' nearby services. Simple modern style. White background. No text.",
            7 => "A clean flat illustration of a video play button with a film reel, representing '{$nameEn}' video content. Simple modern style. White background. No text.",
            8 => "A clean flat illustration of a red emergency cross with a megaphone, representing '{$nameEn}' urgent/emergency listings. Simple modern style. White background. No text.",
        ];

        return $prompts[$catId] ?? "A clean flat illustration icon representing '{$nameEn}' (Arabic: '{$nameAr}') for a mobile marketplace app. Simple, clear, instantly recognizable. White background. No text, no logo.";
    }

    /**
     * Subcategory-specific prompt builder.
     * Handles brands (car logos, device products) and job types differently.
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
            return "A clean flat illustration representing '{$nameEn}' in automotive context. Simple, clear icon style. White background. No text.";
        }

        // Devices category (id=2)
        if ($catId === 2) {
            return "A clean, realistic photo-style image of a single {$nameEn} product on a white background. Simple, centered, high quality product photography style. No text, no logo.";
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
            return "A clean flat illustration of {$icon}, representing the job category '{$nameEn}'. Simple, friendly, instantly recognizable. White background. No text.";
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
            return "A clean flat illustration of {$icon}, representing '{$nameEn}' in real estate. Simple, clear, friendly style. White background. No text.";
        }

        // Urgent/Emergency category (id=8)
        if ($catId === 8) {
            return "A clean flat illustration representing '{$nameEn}' in an emergency/humanitarian context. Simple, clear, compassionate style. White background. No text.";
        }

        // Services category (id=5) and others
        return "A clean flat illustration clearly representing '{$nameEn}' service. Simple, instantly recognizable icon for a mobile app. White background. No text, no logo.";
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

            $imageUrl = $this->callDalleApi($prompt);
            if (!$imageUrl) {
                return false;
            }

            // Download the image
            $imageContents = $this->client->get($imageUrl)->getBody()->getContents();
            $fileName = Str::uuid() . '.png';
            $storagePath = "category/{$fileName}";

            // Move old photo to gallery instead of deleting
            $oldPhoto = $category->photos()->first();
            if ($oldPhoto) {
                $this->copyToGallery('category', $category->id, $oldPhoto->src);
                $oldPath = str_replace('storage/', '', $oldPhoto->src);
                Storage::disk('public')->delete($oldPath);
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
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response';
            $errorData = json_decode($responseBody, true);
            $this->lastError = $errorData['error']['message'] ?? $responseBody;
            Log::error("DALL-E API error for category {$category->id}: " . $this->lastError);
            return false;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error("DALL-E generation failed for category {$category->id}: " . $e->getMessage());
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

            $imageUrl = $this->callDalleApi($prompt);
            if (!$imageUrl) {
                return false;
            }

            // Download the image
            $imageContents = $this->client->get($imageUrl)->getBody()->getContents();
            $fileName = Str::uuid() . '.png';

            // Get category name for folder structure
            $category = $subcategory->category;
            $categoryName = $category ? ($category->name['ar'] ?? 'unknown') : 'unknown';
            $storagePath = "subcategory/{$categoryName}/{$fileName}";

            // Move old photo to gallery instead of deleting
            $oldPhoto = $subcategory->photos()->first();
            if ($oldPhoto) {
                $this->copyToGallery('subcategory', $subcategory->id, $oldPhoto->src);
                $oldPath = str_replace('storage/', '', $oldPhoto->src);
                Storage::disk('public')->delete($oldPath);
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
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response';
            $errorData = json_decode($responseBody, true);
            $this->lastError = $errorData['error']['message'] ?? $responseBody;
            Log::error("DALL-E API error for subcategory {$subcategory->id}: " . $this->lastError);
            return false;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error("DALL-E generation failed for subcategory {$subcategory->id}: " . $e->getMessage());
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

            $imageUrl = $this->callDalleApi($prompt);
            if (!$imageUrl) {
                return null;
            }

            $imageContents = $this->client->get($imageUrl)->getBody()->getContents();
            $fileName = Str::uuid() . '.png';
            $storagePath = "posts/ai/{$fileName}";

            Storage::disk('public')->put($storagePath, $imageContents);

            return 'storage/' . $storagePath;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response';
            $errorData = json_decode($responseBody, true);
            $this->lastError = $errorData['error']['message'] ?? $responseBody;
            Log::error("DALL-E post photo error: " . $this->lastError);
            return null;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error("DALL-E post photo generation failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Call the DALL-E 3 API and return the generated image URL.
     */
    protected function callDalleApi(string $prompt): ?string
    {
        try {
            $response = $this->client->post('https://api.openai.com/v1/images/generations', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                    'quality' => 'standard',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return $body['data'][0]['url'] ?? null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response';
            $errorData = json_decode($responseBody, true);
            $this->lastError = $errorData['error']['message'] ?? $responseBody;
            Log::error("DALL-E API call failed: " . $this->lastError);
            throw $e;
        }
    }
}
