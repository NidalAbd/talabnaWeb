<?php

namespace Tests\Unit;

use App\Models\Categories;
use App\Services\CardImageCropper;
use App\Services\CategoryCardImageService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class CategoryCardImageTest extends TestCase
{
    /** 1536x1024 PNG: red top quarter, green middle half, blue bottom quarter. */
    private function stripedWideImage(): string
    {
        $img = imagecreatetruecolor(1536, 1024);
        imagefilledrectangle($img, 0, 0, 1535, 255, imagecolorallocate($img, 255, 0, 0));
        imagefilledrectangle($img, 0, 256, 1535, 767, imagecolorallocate($img, 0, 255, 0));
        imagefilledrectangle($img, 0, 768, 1535, 1023, imagecolorallocate($img, 0, 0, 255));
        ob_start();
        imagepng($img);

        return (string) ob_get_clean();
    }

    private function colorAt(string $jpeg, int $x, int $y): array
    {
        $img = imagecreatefromstring($jpeg);
        $rgb = imagecolorat($img, $x, $y);

        return [($rgb >> 16) & 255, ($rgb >> 8) & 255, $rgb & 255];
    }

    private function serviceWith(array $responses, array &$history = [], string $key = 'test-key'): CategoryCardImageService
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($history));

        return new CategoryCardImageService(new Client(['handler' => $stack]), $key);
    }

    private function apiResponse(string $imageBytes): Response
    {
        return new Response(200, [], json_encode(['data' => [['b64_json' => base64_encode($imageBytes)]]]));
    }

    public function test_crop_produces_exact_size_and_keeps_the_middle_band(): void
    {
        $banner = CardImageCropper::crop($this->stripedWideImage(), 1440, 480);

        [$w, $h] = getimagesizefromstring($banner);
        $this->assertSame([1440, 480], [$w, $h]);

        // 3:1 out of 3:2 keeps the vertical middle half — all green, no red/blue edge.
        foreach ([[10, 5], [720, 240], [1430, 474]] as [$x, $y]) {
            [$r, $g, $b] = $this->colorAt($banner, $x, $y);
            $this->assertGreaterThan(200, $g);
            $this->assertLessThan(60, $r);
            $this->assertLessThan(60, $b);
        }
    }

    public function test_crop_to_tile_shape(): void
    {
        [$w, $h] = getimagesizefromstring(CardImageCropper::crop($this->stripedWideImage(), 960, 620));
        $this->assertSame([960, 620], [$w, $h]);
    }

    public function test_crop_rejects_bytes_that_are_not_an_image(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CardImageCropper::crop('not an image', 100, 100);
    }

    public function test_fetch_wide_image_asks_for_the_wide_size_and_decodes_the_result(): void
    {
        $history = [];
        $service = $this->serviceWith([$this->apiResponse('IMG-BYTES')], $history);

        $this->assertSame('IMG-BYTES', $service->fetchWideImage('a prompt'));

        $sent = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('gpt-image-1', $sent['model']);
        $this->assertSame('1536x1024', $sent['size']);
        $this->assertSame('a prompt', $sent['prompt']);
        $this->assertSame('Bearer test-key', $history[0]['request']->getHeaderLine('Authorization'));
    }

    public function test_fetch_wide_image_surfaces_the_api_error_message(): void
    {
        $service = $this->serviceWith([new Response(400, [], json_encode(['error' => ['message' => 'billing hard limit reached']]))]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('billing hard limit reached');
        $service->fetchWideImage('p');
    }

    public function test_fetch_wide_image_refuses_to_call_without_an_api_key(): void
    {
        $history = [];
        $service = $this->serviceWith([], $history, '');

        $this->expectException(RuntimeException::class);
        $service->fetchWideImage('p');
    }

    public function test_prompt_names_the_category_and_carries_the_composition_rules(): void
    {
        $category = new Categories(['name' => ['en' => 'Real Estate']]);
        $category->id = 3;

        $prompt = (new CategoryCardImageService(apiKey: 'k'))->buildPrompt($category);

        $this->assertStringContainsString("'Real Estate'", $prompt);
        $this->assertStringContainsString('NOT white', $prompt);
        $this->assertStringContainsString('vertical middle half', $prompt);
    }

    public function test_generate_stores_both_images_records_them_and_removes_the_old_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('category/cards/old-banner.jpg', 'old');
        Storage::disk('public')->put('category/cards/old-tile.jpg', 'old');

        $category = new class(['name' => ['en' => 'Jobs']]) extends Categories {
            public function save(array $options = []): bool
            {
                return true; // no database in this test
            }
        };
        $category->id = 1;
        $category->banner_src = 'storage/category/cards/old-banner.jpg';
        $category->tile_src = 'storage/category/cards/old-tile.jpg';

        $service = $this->serviceWith([$this->apiResponse($this->stripedWideImage())]);

        $this->assertTrue($service->generateForCategory($category));

        $disk = Storage::disk('public');
        $this->assertStringStartsWith('storage/category/cards/1-banner-', $category->banner_src);
        $this->assertStringStartsWith('storage/category/cards/1-tile-', $category->tile_src);
        $disk->assertExists(substr($category->banner_src, strlen('storage/')));
        $disk->assertExists(substr($category->tile_src, strlen('storage/')));
        $disk->assertMissing('category/cards/old-banner.jpg');
        $disk->assertMissing('category/cards/old-tile.jpg');
        $this->assertSame([1440, 480], array_slice(getimagesizefromstring($disk->get(substr($category->banner_src, 8))), 0, 2));
    }

    public function test_a_failed_generation_keeps_the_old_images_and_reports_why(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('category/cards/old-banner.jpg', 'old');

        $category = new class(['name' => ['en' => 'Jobs']]) extends Categories {
            public function save(array $options = []): bool
            {
                return true;
            }
        };
        $category->id = 1;
        $category->banner_src = 'storage/category/cards/old-banner.jpg';

        $service = $this->serviceWith([new Response(400, [], json_encode(['error' => ['message' => 'nope']]))]);

        $this->assertFalse($service->generateForCategory($category));
        $this->assertSame('nope', $service->getLastError());
        $this->assertSame('storage/category/cards/old-banner.jpg', $category->banner_src);
        Storage::disk('public')->assertExists('category/cards/old-banner.jpg');
    }
}
