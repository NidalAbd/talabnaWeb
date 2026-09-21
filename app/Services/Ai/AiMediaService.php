<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Storage;

/** Image (gpt-image-1) and video (Sora) generation. Files go to the private "local" disk under ai-results/. */
class AiMediaService
{
    public function __construct(private OpenAiClient $openai)
    {
    }

    public function isConfigured(): bool
    {
        return $this->openai->isConfigured();
    }

    /** Generates one image and stores it. @return string storage path */
    public function generateImage(string $prompt, string $uuid): string
    {
        try {
            $response = $this->openai->http(120)->post(OpenAiClient::BASE.'/images/generations', [
                'model' => config('ai.image_model', 'gpt-image-1'),
                'prompt' => 'Realistic, well-lit photo-style picture for a classified ad. No text, no watermark, no logos. '.$prompt,
                'size' => config('ai.image_size', '1024x1024'),
                'quality' => config('ai.image_quality', 'medium'),
                'output_format' => 'jpeg',
                'output_compression' => 82,
                'n' => 1,
            ]);
        } catch (\Throwable $e) {
            $this->openai->unreachable($e, 'image');
        }
        if (! $response->successful()) {
            $this->openai->fail($response, 'image');
        }

        $bytes = base64_decode((string) $response->json('data.0.b64_json'), true);
        if ($bytes === false || strlen($bytes) < 1000) {
            throw new AiProviderException('bad_answer', 'The AI returned no image.', 502);
        }

        return $this->store($uuid.'.jpg', $bytes);
    }

    /** Starts a video job. @return string the provider job id */
    public function startVideo(string $prompt): string
    {
        try {
            $response = $this->openai->http(40)->asMultipart()->post(OpenAiClient::BASE.'/videos', [
                ['name' => 'model', 'contents' => (string) config('ai.video_model', 'sora-2')],
                ['name' => 'prompt', 'contents' => 'Short clip for a classified ad, steady camera, no text or logos. '.$prompt],
                ['name' => 'seconds', 'contents' => (string) config('ai.video_seconds', '4')],
                ['name' => 'size', 'contents' => (string) config('ai.video_size', '720x1280')],
            ]);
        } catch (\Throwable $e) {
            $this->openai->unreachable($e, 'video');
        }
        if (! $response->successful()) {
            $this->openai->fail($response, 'video');
        }
        $id = (string) $response->json('id');
        if ($id === '') {
            throw new AiProviderException('bad_answer', 'The AI did not start the video.', 502);
        }

        return $id;
    }

    /**
     * @return array{state:'running'|'done'|'failed', path?:string, code?:string, message?:string}
     */
    public function pollVideo(string $jobId, string $uuid): array
    {
        try {
            $response = $this->openai->http(30)->get(OpenAiClient::BASE.'/videos/'.$jobId);
        } catch (\Throwable) {
            return ['state' => 'running']; // a blip; the next poll asks again (the stale timeout still applies)
        }
        if ($response->status() >= 500 || $response->status() === 429) {
            return ['state' => 'running'];
        }
        if (! $response->successful()) {
            return ['state' => 'failed', 'code' => 'provider_error', 'message' => 'The AI could not finish the video.'];
        }

        $status = (string) $response->json('status');
        if ($status === 'failed') {
            $code = (string) $response->json('error.code');

            return ['state' => 'failed', 'code' => str_contains($code, 'moderation') ? 'blocked' : 'provider_error',
                'message' => str_contains($code, 'moderation') ? 'This request was blocked by the AI safety rules.' : 'The AI could not finish the video.'];
        }
        if ($status !== 'completed') {
            return ['state' => 'running'];
        }

        try {
            $file = $this->openai->http(120)->get(OpenAiClient::BASE.'/videos/'.$jobId.'/content');
        } catch (\Throwable) {
            return ['state' => 'running']; // finished but the download blipped: try again next poll
        }
        if (! $file->successful() || strlen($file->body()) < 1000) {
            return ['state' => 'running'];
        }

        return ['state' => 'done', 'path' => $this->store($uuid.'.mp4', $file->body())];
    }

    private function store(string $name, string $bytes): string
    {
        $path = 'ai-results/'.$name;
        Storage::disk('local')->put($path, $bytes);

        return $path;
    }
}
