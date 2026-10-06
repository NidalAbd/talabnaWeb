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
            // 90 s: under the web server's own limit, so a slow answer fails cleanly here and the points go
            // back at once (at 120 s the request was cut off first and the refund waited for the settler).
            $response = $this->openai->http(90)->post(OpenAiClient::BASE.'/images/generations', [
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

    /**
     * Release B: edits the seller's own photo (Photo Studio). input_fidelity=high keeps the item as it is.
     * @return string storage path
     */
    public function editImage(string $imageBytes, string $mime, string $prompt, string $uuid, string $quality = 'medium'): string
    {
        $ext = str_contains($mime, 'png') ? 'png' : (str_contains($mime, 'webp') ? 'webp' : 'jpg');
        try {
            $response = $this->openai->http(90)
                ->attach('image', $imageBytes, 'photo.'.$ext, ['Content-Type' => $mime])
                ->post(OpenAiClient::BASE.'/images/edits', [
                    'model' => config('ai.image_model', 'gpt-image-1'),
                    'prompt' => $prompt,
                    'size' => 'auto',
                    'quality' => $quality,
                    'input_fidelity' => 'high',
                    'output_format' => 'jpeg',
                    'output_compression' => '85',
                    'n' => '1',
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

    /**
     * Starts a video job. With [referenceJpeg] (Release C, cinematic video from the seller's photo) the clip starts
     * from that photo; it must already be the video's exact size (see fitForVideo). @return string the provider job id
     */
    public function startVideo(string $prompt, ?string $referenceJpeg = null, ?string $seconds = null): string
    {
        $parts = [
            ['name' => 'model', 'contents' => (string) config('ai.video_model', 'sora-2')],
            ['name' => 'prompt', 'contents' => 'Short clip for a classified ad, steady camera, no text or logos. '.$prompt],
            ['name' => 'seconds', 'contents' => $seconds ?? (string) config('ai.video_seconds', '4')],
            ['name' => 'size', 'contents' => (string) config('ai.video_size', '720x1280')],
        ];
        if ($referenceJpeg !== null) {
            $parts[] = ['name' => 'input_reference', 'contents' => $referenceJpeg, 'filename' => 'reference.jpg', 'headers' => ['Content-Type' => 'image/jpeg']];
        }
        try {
            $response = $this->openai->http(40)->asMultipart()->post(OpenAiClient::BASE.'/videos', $parts);
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

    /** The photo centred on a canvas of the video's size (config ai.video_size), soft-blurred copy behind it. */
    public static function fitForVideo(string $imageBytes): string
    {
        [$w, $h] = array_map('intval', explode('x', (string) config('ai.video_size', '720x1280')));
        $src = @imagecreatefromstring($imageBytes);
        if (! $src) {
            throw new AiProviderException('bad_image', 'This photo could not be read.', 422);
        }
        $sw = imagesx($src);
        $sh = imagesy($src);
        $canvas = imagecreatetruecolor($w, $h);
        // Background: the photo scaled to cover, blurred.
        $cover = max($w / $sw, $h / $sh);
        $bw = (int) ceil($sw * $cover);
        $bh = (int) ceil($sh * $cover);
        imagecopyresampled($canvas, $src, (int) (($w - $bw) / 2), (int) (($h - $bh) / 2), 0, 0, $bw, $bh, $sw, $sh);
        for ($i = 0; $i < 12; $i++) {
            imagefilter($canvas, IMG_FILTER_GAUSSIAN_BLUR);
        }
        imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -25);
        // Foreground: the whole photo, contained.
        $fit = min($w / $sw, $h / $sh);
        $fw = (int) round($sw * $fit);
        $fh = (int) round($sh * $fit);
        imagecopyresampled($canvas, $src, (int) (($w - $fw) / 2), (int) (($h - $fh) / 2), 0, 0, $fw, $fh, $sw, $sh);
        ob_start();
        imagejpeg($canvas, null, 90);
        $out = (string) ob_get_clean();
        imagedestroy($canvas);
        imagedestroy($src);

        return $out;
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
