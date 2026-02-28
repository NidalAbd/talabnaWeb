<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenAiContentService
{
    protected Client $client;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->client = new Client([
            'timeout' => 60,
            'connect_timeout' => 30,
        ]);
    }

    /**
     * Generate post content (title, description, price) using GPT-4o-mini.
     */
    public function generatePostContent(string $categoryName, string $subcategoryName, string $type): array
    {
        $prompt = "Generate a realistic marketplace listing for a \"{$subcategoryName}\" item under \"{$categoryName}\" category. Type: {$type}. Return JSON with: title_en, title_ar, description_en, description_ar, price (integer in USD). Title should be a specific product/service name. Description 2-4 sentences, realistic. Return ONLY valid JSON, no markdown.";

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a marketplace listing generator. Always respond with valid JSON only, no markdown formatting.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.8,
                'max_tokens' => 500,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $content = $body['choices'][0]['message']['content'] ?? '';

        // Clean potential markdown code fences
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);

        $data = json_decode($content, true);

        if (!$data) {
            Log::warning("GPT response was not valid JSON: {$content}");
            throw new \RuntimeException('Failed to parse GPT response as JSON');
        }

        return [
            'title' => [
                'en' => $data['title_en'] ?? 'Untitled',
                'ar' => $data['title_ar'] ?? 'بدون عنوان',
            ],
            'description' => [
                'en' => $data['description_en'] ?? '',
                'ar' => $data['description_ar'] ?? '',
            ],
            'price' => (int) ($data['price'] ?? 0),
        ];
    }

    /**
     * Generate a DALL-E prompt for a realistic product/service photo.
     */
    public function generateImagePrompt(string $categoryName, string $subcategoryName, string $title): string
    {
        return "Professional product photography of {$title}, a {$subcategoryName} item in the {$categoryName} category. Realistic, high quality, well-lit, clean background, marketplace listing photo. No text, no watermark, no logo.";
    }
}
