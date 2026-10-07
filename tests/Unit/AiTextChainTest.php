<?php

namespace Tests\Unit;

use App\Services\Ai\AiProviderException;
use App\Services\Ai\AiTextChain;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Text AI: small models for small jobs, and a failing provider is replaced at once by the next. */
class AiTextChainTest extends TestCase
{
    private const OPENAI = 'api.openai.com/v1/chat/completions';

    private const CLAUDE = 'api.anthropic.com/v1/messages';

    private const GEMINI = 'generativelanguage.googleapis.com/v1beta/models/*:generateContent';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.openai.key' => 'o', 'services.anthropic.key' => 'a', 'services.gemini.key' => 'g',
            'ai.text_providers' => ['openai', 'claude', 'gemini'],
        ]);
        Cache::flush();
        Http::swap(new Factory());
    }

    private function openaiSays(string $text): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response(['choices' => [['message' => ['content' => $text]]]]);
    }

    private function claudeSays(string $text): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response(['content' => [['type' => 'text', 'text' => $text]], 'stop_reason' => 'end_turn']);
    }

    public function test_openai_answers_with_the_model_of_the_requested_tier(): void
    {
        Http::fake([self::OPENAI => $this->openaiSays('en')]);

        $this->assertSame('en', app(AiTextChain::class)->complete('light', 'sys', 'hello'));

        Http::assertSent(fn ($r) => $r['model'] === 'gpt-4.1-nano');
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'anthropic'));
    }

    public function test_when_openai_fails_claude_answers_and_openai_is_skipped_next_time(): void
    {
        Http::fake([
            self::OPENAI => Http::response(['error' => ['code' => 'insufficient_quota', 'message' => 'quota']], 429),
            self::CLAUDE => $this->claudeSays("```json\n{\"title\": \"Bike\"}\n```"),
        ]);
        $chain = app(AiTextChain::class);

        $this->assertSame(['title' => 'Bike'], $chain->completeJson('standard', 'sys', 'a bike'));
        Http::assertSent(fn ($r) => str_contains($r->url(), 'anthropic') && $r['model'] === 'claude-haiku-5-5'
            && $r->hasHeader('x-api-key', 'a') && str_contains($r['system'], 'JSON object only'));
        $this->assertTrue($chain->isDown('openai'));

        Http::swap(new Factory());
        Http::fake([self::OPENAI => $this->openaiSays('{}'), self::CLAUDE => $this->claudeSays('{"title": "Car"}')]);
        $this->assertSame(['title' => 'Car'], $chain->completeJson('standard', 'sys', 'a car'));
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'openai'));
    }

    public function test_gemini_answers_when_openai_and_claude_are_both_down_and_gets_the_photo(): void
    {
        Http::fake([
            self::OPENAI => Http::response('', 500),
            self::CLAUDE => Http::response(['error' => ['type' => 'overloaded_error', 'message' => 'Overloaded']], 529),
            self::GEMINI => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{"has_item": true}']]], 'finishReason' => 'STOP']]]),
        ]);

        $out = app(AiTextChain::class)->completeJson('light', 'sys', '', ['bytes' => 'JPEG', 'mime' => 'image/jpeg']);

        $this->assertSame(['has_item' => true], $out);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'gemini-3.1-flash-lite:generateContent')
            && $r['contents'][0]['parts'][0]['inline_data'] === ['mime_type' => 'image/jpeg', 'data' => base64_encode('JPEG')]
            && $r['generationConfig']['responseMimeType'] === 'application/json');
    }

    public function test_a_safety_block_is_final_and_no_other_provider_is_asked(): void
    {
        Http::fake([
            self::OPENAI => Http::response(['error' => ['code' => 'content_policy_violation', 'message' => 'blocked']], 400),
            self::CLAUDE => $this->claudeSays('ok'),
        ]);

        try {
            app(AiTextChain::class)->complete('standard', 'sys', 'bad');
            $this->fail('expected a block');
        } catch (AiProviderException $e) {
            $this->assertSame('blocked', $e->errorCode);
        }
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'anthropic'));
    }

    public function test_when_every_provider_fails_the_request_fails(): void
    {
        Http::fake([self::OPENAI => Http::response('', 500), self::CLAUDE => Http::response('', 500), self::GEMINI => Http::response('', 500)]);

        $this->expectException(AiProviderException::class);
        app(AiTextChain::class)->complete('standard', 'sys', 'x');
    }

    public function test_a_provider_without_a_key_is_not_tried(): void
    {
        config(['services.openai.key' => null]);
        Http::fake([self::CLAUDE => $this->claudeSays('hi')]);

        $this->assertSame('hi', app(AiTextChain::class)->complete('standard', 'sys', 'x'));
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'openai'));
    }
}
