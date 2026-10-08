<?php

namespace Tests\Unit;

use App\Services\Ai\AiHealth;
use App\Services\Ai\AiMediaService;
use App\Services\Ai\AiProviderException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Every model checked in parallel for free; a request goes to one ready model, the next only if it fails. */
class AiHealthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.openai.key' => 'o', 'services.anthropic.key' => 'a', 'services.gemini.key' => 'g']);
        Cache::flush();
        Http::swap(new Factory());
        Storage::fake('local');
    }

    public function test_the_check_asks_every_model_with_free_calls_and_reads_the_answers(): void
    {
        Http::fake([
            'api.openai.com/v1/models/gpt-image-1' => Http::response(['id' => 'gpt-image-1']),
            'api.openai.com/v1/models/*' => Http::response(['id' => 'x']),
            'api.anthropic.com/v1/models/*' => Http::response([], 401),
            'generativelanguage.googleapis.com/v1beta/models/veo-3.1-generate-preview' => Http::response([], 404),
            'generativelanguage.googleapis.com/v1beta/models/*' => Http::response(['name' => 'm']),
        ]);

        $s = app(AiHealth::class)->probe();

        $this->assertSame('ok', $s['openai:image']['status']);
        $this->assertSame('no_access', $s['claude:text']['status']);
        $this->assertSame('no_model', $s['veo']['status']);
        $this->assertSame('ok', $s['veo_fast']['status']);
        $this->assertCount(8, Http::recorded()); // 8 models, Veo Lite included
        Http::assertNotSent(fn ($r) => $r->method() !== 'GET', 'only reads: nothing is generated or charged');
        $this->assertFalse(app(AiHealth::class)->ready('veo'));
        $this->assertSame(['veo_fast'], app(AiHealth::class)->order(['veo', 'veo_fast']));
    }

    public function test_no_check_yet_means_every_model_counts_as_ready_and_a_request_never_waits_for_one(): void
    {
        Http::fake();
        $this->assertTrue(app(AiHealth::class)->ready('openai:image'));
        Http::assertNothingSent();
    }

    public function test_when_openai_fails_the_gemini_image_model_makes_the_picture_once(): void
    {
        $png = $this->png();
        Http::fake([
            'api.openai.com/v1/images/generations' => Http::response(['error' => ['message' => 'server error']], 500),
            'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent' => Http::response(
                ['candidates' => [['content' => ['parts' => [['inlineData' => ['mimeType' => 'image/png', 'data' => base64_encode($png)]]]], 'finishReason' => 'STOP']]]),
        ]);

        $path = app(AiMediaService::class)->generateImage('a red bicycle', 'u1');

        $this->assertStringStartsWith("\xFF\xD8", Storage::disk('local')->get($path), 'stored as JPEG');
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'gemini-2.5-flash-image') && $r['generationConfig']['imageConfig']['aspectRatio'] === '2:3');
        $this->assertTrue(Cache::has('ai_down:openai:image'));
    }

    public function test_a_safety_block_is_final_and_the_backup_is_not_asked(): void
    {
        Http::fake(['api.openai.com/v1/images/generations' => Http::response(['error' => ['message' => 'Your request was rejected by the safety system', 'code' => 'moderation_blocked']], 400)]);

        try {
            app(AiMediaService::class)->generateImage('something not allowed', 'u2');
            $this->fail('expected a block');
        } catch (AiProviderException $e) {
            $this->assertSame('blocked', $e->errorCode);
        }
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'generativelanguage'));
    }

    public function test_a_model_marked_down_is_skipped_while_another_is_ready(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(
            ['candidates' => [['content' => ['parts' => [['inlineData' => ['data' => base64_encode($this->png())]]]]]]])]);
        app(AiHealth::class)->markDown('openai:image', 'provider_busy');

        app(AiMediaService::class)->generateImage('a lamp', 'u3');

        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'api.openai.com'));
        Http::assertSentCount(1);
    }

    private function png(): string
    {
        $img = imagecreatetruecolor(160, 240);
        mt_srand(7);
        for ($x = 0; $x < 160; $x++) {
            for ($y = 0; $y < 240; $y += 2) {
                imagesetpixel($img, $x, $y, imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
            }
        }
        ob_start();
        imagepng($img);

        return (string) ob_get_clean();
    }
}
