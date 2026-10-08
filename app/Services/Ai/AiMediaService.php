<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;

/**
 * Image and video generation. Files go to the private "local" disk under ai-results/.
 *  - Images: OpenAI gpt-image-1, with Google's Gemini image model as the backup (2026-10-08).
 *  - Video: Google Veo 3.1, with Veo 3.1 Fast as the backup (Sora was shut down on 2026-09-24).
 * One request at a time: the first READY model gets it (AiHealth checks them all in parallel, for free); only when
 * it fails does the request go to the next. Two models never work on the same request at once.
 */
class AiMediaService
{
    private const VEO_PREFIX = 'veo:';

    private const SORA_SHUTDOWN_DATE = '2026-09-24';

    public function __construct(private OpenAiClient $openai, private VeoClient $veo, private GeminiImageClient $geminiImage, private AiHealth $health)
    {
    }

    /**
     * Runs [steps] (model id => closure returning image bytes) one after another in readiness order and returns the
     * first image. A safety block ends it (another model would refuse too, or should not be asked to try).
     */
    private function imageChain(array $steps): string
    {
        $last = null;
        $started = microtime(true);
        foreach ($this->health->order(array_keys($steps)) as $i => $id) {
            // A backup only when there is time left for it: the whole request must end within the web server's limit.
            if ($i > 0 && microtime(true) - $started > 40) {
                break;
            }
            try {
                $bytes = $steps[$id]();
                if ($i > 0) {
                    Log::info('ai.image.served_by_backup', ['model' => $id]);
                }

                return $bytes;
            } catch (AiProviderException $e) {
                if ($e->errorCode === 'blocked') {
                    throw $e;
                }
                $last = $e;
                if ($e->errorCode !== 'bad_answer') {
                    $this->health->markDown($id, $e->errorCode);
                }
            }
        }
        throw $last ?? new AiProviderException('provider_no_access', 'Image generation is not available right now.', 503);
    }

    /** Gemini answers PNG; the app and the store expect JPEG. */
    private static function toJpeg(string $bytes): string
    {
        if (str_starts_with($bytes, "\xFF\xD8") || ! function_exists('imagecreatefromstring')) {
            return $bytes;
        }
        $img = @imagecreatefromstring($bytes);
        if (! $img) {
            return $bytes;
        }
        ob_start();
        imagejpeg($img, null, 86);
        imagedestroy($img);

        return (string) ob_get_clean();
    }

    /** "1024x1536" -> "2:3" for Gemini; null ("auto") keeps the photo's own shape. */
    private static function aspect(?string $size): ?string
    {
        return match ($size) {
            '1024x1536' => '2:3',
            '1536x1024' => '3:2',
            '1024x1024' => '1:1',
            default => null,
        };
    }

    public function isConfigured(): bool
    {
        return $this->openai->isConfigured() || $this->veo->isConfigured();
    }

    /** Generates one image and stores it. @return string storage path */
    public function generateImage(string $prompt, string $uuid): string
    {
        $full = 'Realistic, well-lit photo-style picture for a classified ad. No text, no watermark, no logos. '.$prompt;
        $bytes = $this->imageChain([
            'openai:image' => fn () => $this->openaiGenerate($full),
            'gemini:image' => fn () => self::toJpeg($this->geminiImage->generate($full, null, self::aspect(config('ai.image_size', '1024x1024')), 90)),
        ]);

        return $this->store($uuid.'.jpg', $bytes);
    }

    private function openaiGenerate(string $prompt): string
    {
        try {
            // 90 s: under the web server's own limit, so a slow answer fails cleanly here and the points go
            // back at once (at 120 s the request was cut off first and the refund waited for the settler).
            $response = $this->openai->http(90)->post(OpenAiClient::BASE.'/images/generations', [
                'model' => config('ai.image_model', 'gpt-image-1'),
                'prompt' => $prompt,
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

        return $bytes;
    }

    /**
     * Release B: edits the seller's own photo (Photo Studio). input_fidelity=high keeps the item as it is.
     * @return string storage path
     */
    public function editImage(string $imageBytes, string $mime, string $prompt, string $uuid, string $quality = 'medium', string $size = 'auto'): string
    {
        $bytes = $this->imageChain([
            'openai:image' => fn () => $this->openaiEdit($imageBytes, $mime, $prompt, $quality, $size),
            'gemini:image' => fn () => self::toJpeg($this->geminiImage->generate(
                $prompt.' Keep the item itself exactly as it is in the photo.', ['bytes' => $imageBytes, 'mime' => $mime], self::aspect($size), 140)),
        ]);

        return $this->store($uuid.'.jpg', $bytes);
    }

    private function openaiEdit(string $imageBytes, string $mime, string $prompt, string $quality, string $size): string
    {
        $ext = str_contains($mime, 'png') ? 'png' : (str_contains($mime, 'webp') ? 'webp' : 'jpg');
        try {
            // High-quality edits (cinematic) often take over 90 s; the request itself may run 170 s (AiController::run).
            $response = $this->openai->http(150)
                ->attach('image', $imageBytes, 'photo.'.$ext, ['Content-Type' => $mime])
                ->post(OpenAiClient::BASE.'/images/edits', [
                    'model' => config('ai.image_model', 'gpt-image-1'),
                    'prompt' => $prompt,
                    'size' => $size,
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

        return $bytes;
    }

    /**
     * Starts a video job. With [referenceJpeg] (Release C, cinematic video from the seller's photo) the clip starts
     * from that photo; it must already be the video's exact size (see fitForVideo). @return string the provider job id
     *
     * 2026-10-08: OpenAI shut the Videos API (Sora) down on 2026-09-24, so every video failed. Google Veo (Gemini API)
     * is now the backup: Sora is tried only before its shutdown date, and any Sora failure other than a safety block
     * goes on to Veo. Veo job ids carry a "veo:" prefix so pollVideo asks the right provider.
     */
    /**
     * Video qualities the user picks (2026-10-08): model and resolution behind each, the first one first. A backup is
     * only ever the same quality or better, so nobody pays for a quality they did not get; "pro" has none.
     */
    public const VIDEO_QUALITIES = [
        'normal' => [['veo_lite', '720p'], ['veo_fast', '720p']],
        'hd' => [['veo_fast', '1080p'], ['veo', '1080p']],
        'pro' => [['veo', '1080p']],
    ];

    /** Whether a quality can be made now (its first-choice model or a backup is ready). */
    public function videoQualityReady(string $quality): bool
    {
        foreach (self::VIDEO_QUALITIES[$quality] ?? [] as [$id]) {
            if ($this->veo->isConfigured() && $this->health->ready($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Starts a video job in the chosen quality. With [referenceJpeg] the clip starts from that photo (it must already
     * be the video's size, see fitForVideo). @return string the provider job id ("veo:" + Google's operation name)
     *
     * OpenAI shut the Videos API (Sora) down on 2026-09-24; Sora is tried only before that date.
     */
    public function startVideo(string $prompt, ?string $referenceJpeg = null, ?string $seconds = null, string $quality = 'hd'): string
    {
        if ($this->soraAvailable()) {
            try {
                return $this->startSora($prompt, $referenceJpeg, $seconds);
            } catch (AiProviderException $e) {
                if ($e->errorCode === 'blocked' || ! $this->veo->isConfigured()) {
                    throw $e;
                }
                Log::warning('ai.video.sora_failed_using_veo', ['code' => $e->errorCode]);
            }
        }
        if (! $this->veo->isConfigured()) {
            throw new AiProviderException('provider_no_access', 'Video generation is not available right now.', 503);
        }
        $chain = self::VIDEO_QUALITIES[$quality] ?? self::VIDEO_QUALITIES['hd'];
        $steps = [];
        foreach ($chain as [$id, $resolution]) {
            $steps[$id] = $resolution;
        }
        $models = [
            'veo' => (string) config('ai.veo_model', 'veo-3.1-generate-preview'),
            'veo_fast' => (string) config('ai.veo_fast_model', 'veo-3.1-fast-generate-preview'),
            'veo_lite' => (string) config('ai.veo_lite_model', 'veo-3.1-lite-generate-preview'),
        ];
        // One start at a time, the first ready model first. Starting only queues the job: a failed start costs nothing.
        $last = null;
        foreach ($this->health->order(array_keys($steps)) as $id) {
            try {
                return self::VEO_PREFIX.$this->veo->start($prompt, $referenceJpeg, (int) ($seconds ?? config('ai.video_seconds', '8')), $models[$id], $steps[$id]);
            } catch (AiProviderException $e) {
                if ($e->errorCode === 'blocked') {
                    throw $e;
                }
                $last = $e;
                $this->health->markDown($id, $e->errorCode, 10);
            }
        }
        throw $last ?? new AiProviderException('provider_no_access', 'Video generation is not available right now.', 503);
    }

    private function soraAvailable(): bool
    {
        return $this->openai->isConfigured() && now()->lt(self::SORA_SHUTDOWN_DATE);
    }

    private function startSora(string $prompt, ?string $referenceJpeg = null, ?string $seconds = null, ?string $model = null, ?string $size = null): string
    {
        $model ??= (string) config('ai.video_model', 'sora-2');
        $size ??= (string) config('ai.video_size', '720x1280');
        $parts = [
            ['name' => 'model', 'contents' => $model],
            ['name' => 'prompt', 'contents' => 'Premium vertical product commercial, 2026 look. Smooth gimbal camera, cinematic '
                .'shallow depth of field, clean modern colour grade, crisp detail. No text, captions, logos or watermarks. '.$prompt],
            ['name' => 'seconds', 'contents' => $seconds ?? (string) config('ai.video_seconds', '4')],
            ['name' => 'size', 'contents' => $size],
        ];
        if ($referenceJpeg !== null) {
            $parts[] = ['name' => 'input_reference', 'contents' => $referenceJpeg, 'filename' => 'reference.jpg', 'headers' => ['Content-Type' => 'image/jpeg']];
        }
        try {
            $response = $this->openai->http(40)->asMultipart()->post(OpenAiClient::BASE.'/videos', $parts);
        } catch (\Throwable $e) {
            $this->openai->unreachable($e, 'video');
        }
        // The pro model needs extra access on the OpenAI account (404 without it): fall back to sora-2 at 720p, same
        // length, first frame and brief, instead of failing.
        if ($response->status() === 404 && $model !== 'sora-2') {
            Log::warning('ai.video.pro_unavailable_fallback', ['model' => $model]);

            return $this->startSora($prompt, $referenceJpeg !== null ? self::coverToVideoSize($referenceJpeg, '720x1280') : null, $seconds, 'sora-2', '720x1280');
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
     * The first frame of a photo video (2026-10-07): the seller's photo recomposed by the image model into a clean,
     * full-bleed vertical product shot, then fitted to the video size. The old frame (photo on a darkened, blurred copy
     * of itself) made the clip look cheap. Falls back to [fitForVideo] if the edit fails.
     */
    public function heroFrameForVideo(string $imageBytes, string $mime, ?string $subject, string $uuid): string
    {
        $prompt = 'Recompose this photo as a vertical premium product shot: the exact same item'.($subject ? " ({$subject})" : '')
            .' centred and filling about 60% of the frame, sharp, lit by soft studio key light with a gentle rim light, '
            .'on a tasteful softly blurred setting that suits it. Keep the item exactly as it is: same shape, colours, text, '
            .'labels, wear and marks. Never replace or invent the item. No text, no watermark.';
        $ext = str_contains($mime, 'png') ? 'png' : (str_contains($mime, 'webp') ? 'webp' : 'jpg');
        $response = $this->openai->http(150)
            ->attach('image', $imageBytes, 'photo.'.$ext, ['Content-Type' => $mime])
            ->post(OpenAiClient::BASE.'/images/edits', [
                'model' => config('ai.image_model', 'gpt-image-1'),
                'prompt' => $prompt,
                'size' => '1024x1536',
                'quality' => 'high',
                'input_fidelity' => 'high',
                'output_format' => 'jpeg',
                'n' => '1',
            ]);
        if (! $response->successful()) {
            throw new AiProviderException('provider_error', 'hero frame failed', 502);
        }
        $bytes = base64_decode((string) $response->json('data.0.b64_json'), true);
        if ($bytes === false || strlen($bytes) < 1000) {
            throw new AiProviderException('bad_answer', 'hero frame empty', 502);
        }

        return self::coverToVideoSize($bytes);
    }

    /** Scales an image to cover the video size (config ai.video_size) and crops the centre. */
    public static function coverToVideoSize(string $imageBytes, ?string $size = null): string
    {
        [$w, $h] = array_map('intval', explode('x', $size ?? (string) config('ai.video_size', '1024x1792')));
        $src = @imagecreatefromstring($imageBytes);
        if (! $src) {
            throw new AiProviderException('bad_image', 'This photo could not be read.', 422);
        }
        $sw = imagesx($src);
        $sh = imagesy($src);
        $canvas = imagecreatetruecolor($w, $h);
        $cover = max($w / $sw, $h / $sh);
        $bw = (int) ceil($sw * $cover);
        $bh = (int) ceil($sh * $cover);
        imagecopyresampled($canvas, $src, (int) (($w - $bw) / 2), (int) (($h - $bh) / 2), 0, 0, $bw, $bh, $sw, $sh);
        ob_start();
        imagejpeg($canvas, null, 92);
        $out = (string) ob_get_clean();
        imagedestroy($canvas);
        imagedestroy($src);

        return $out;
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
        if (str_starts_with($jobId, self::VEO_PREFIX)) {
            $poll = $this->veo->poll(substr($jobId, strlen(self::VEO_PREFIX)));

            return isset($poll['bytes']) ? ['state' => 'done', 'path' => $this->store($uuid.'.mp4', $poll['bytes'])] : $poll;
        }
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
