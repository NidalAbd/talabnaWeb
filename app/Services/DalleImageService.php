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

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->client = new Client([
            'timeout' => 60,
            'connect_timeout' => 15,
        ]);
    }

    /**
     * Generate an AI image for a category and save it as the category photo.
     */
    public function generateForCategory(Categories $category): bool
    {
        $nameEn = $category->name['en'] ?? '';
        $nameAr = $category->name['ar'] ?? '';

        $prompt = "3D illustrated icon for mobile app category '{$nameEn}' (Arabic: '{$nameAr}'). Soft 3D render, vibrant gradients, clean white background, professional digital identity style for app UI. No text in image.";

        try {
            $imageUrl = $this->callDalleApi($prompt);
            if (!$imageUrl) {
                return false;
            }

            // Download the image
            $imageContents = $this->client->get($imageUrl)->getBody()->getContents();
            $fileName = Str::uuid() . '.png';
            $storagePath = "category/{$fileName}";

            // Delete old photo if exists
            $oldPhoto = $category->photos()->first();
            if ($oldPhoto) {
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

            return true;
        } catch (\Exception $e) {
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

        $prompt = "3D illustrated icon for mobile app category '{$nameEn}' (Arabic: '{$nameAr}'). Soft 3D render, vibrant gradients, clean white background, professional digital identity style for app UI. No text in image.";

        try {
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

            // Delete old photo if exists
            $oldPhoto = $subcategory->photos()->first();
            if ($oldPhoto) {
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

            return true;
        } catch (\Exception $e) {
            Log::error("DALL-E generation failed for subcategory {$subcategory->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Call the DALL-E 3 API and return the generated image URL.
     */
    protected function callDalleApi(string $prompt): ?string
    {
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
    }
}
