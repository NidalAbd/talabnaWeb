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

        $prompt = "Single minimal 3D icon representing '{$nameEn}' category (Arabic: '{$nameAr}') for a premium mobile marketplace app. Soft rounded geometry, smooth matte plastic material, vibrant modern gradients, centered, isolated object. Transparent background (no white background), no text, no watermark, no border, no extra objects.";

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

        $prompt = "Single minimal 3D icon representing '{$nameEn}' subcategory (Arabic: '{$nameAr}') for a premium mobile marketplace app. Specific object symbolizing the subcategory, soft rounded geometry, smooth matte plastic material, vibrant but balanced modern colors, centered front view, isolated object, transparent background (no white background), subtle soft shadow, clean composition, no text, no letters, no watermark, no border, no extra elements, consistent professional app icon style.";

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
