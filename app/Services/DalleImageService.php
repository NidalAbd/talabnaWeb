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
     * Generate an AI image for a category and save it as the category photo.
     */
    public function generateForCategory(Categories $category): bool
    {
        $nameEn = $category->name['en'] ?? '';
        $nameAr = $category->name['ar'] ?? '';

        $prompt = "Single minimal 3D icon representing the main category '{$nameEn}' (Arabic: '{$nameAr}') for a mobile marketplace app. Use a simple broad symbol that represents the entire category, not a specific item. Clean front view, centered composition, smooth matte plastic material. Bold vibrant colors with strong contrast against background. Background: soft neutral light gray studio gradient, low saturation, no pattern, no grid, no frame, no tile. Subtle soft shadow beneath icon. No text, no logo, no watermark.";
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

$prompt = "Single detailed 3D icon representing the specific subcategory '{$nameEn}' (Arabic: '{$nameAr}') for a mobile marketplace app. Use a clear real object that directly represents this subcategory. Front-facing, centered composition, smooth matte plastic material. Vibrant colors with strong contrast against background. Background: soft neutral light gray studio gradient, low saturation, no pattern, no grid, no frame, no tile. Subtle soft shadow beneath object. No text, no logo, no watermark.";
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
