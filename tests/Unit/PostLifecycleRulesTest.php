<?php

namespace Tests\Unit;

use App\Models\ServicePost;
use App\Notifications\PostActivityNotification;
use App\Services\PostLifecycle;
use Tests\TestCase;

/** Release A: when a post may be renewed, and what owners and savers are told. */
class PostLifecycleRulesTest extends TestCase
{
    private function post(string $state, ?\DateTimeInterface $expires): ServicePost
    {
        $p = new ServicePost();
        $p->state = $state;
        $p->expires_at = $expires;

        return $p;
    }

    public function test_renew_is_allowed_once_ended_or_in_the_last_week_only(): void
    {
        $this->assertTrue(PostLifecycle::canRenew($this->post('expired', now()->subDay())));
        $this->assertTrue(PostLifecycle::canRenew($this->post('published', now()->addDays(3))));
        $this->assertFalse(PostLifecycle::canRenew($this->post('published', now()->addDays(20))));
        $this->assertFalse(PostLifecycle::canRenew($this->post('sold', now()->addDays(3))));
    }

    public function test_pushes_are_written_in_the_users_language(): void
    {
        [$t, $b] = PostActivityNotification::render('price_drop', 'en', ['title' => 'iPhone 15', 'price' => '2,400 AED']);
        $this->assertSame('Price dropped', $t);
        $this->assertSame('"iPhone 15" is now 2,400 AED.', $b);

        [$t, $b] = PostActivityNotification::render('expired', 'ar', ['title' => 'سيارة']);
        $this->assertSame('انتهى إعلانك', $t);
        $this->assertStringContainsString('سيارة', $b);

        // Any other language falls back to English.
        [$t] = PostActivityNotification::render('expiring', 'fr', ['title' => 'x', 'days' => '3']);
        $this->assertSame('Your post ends soon', $t);
    }

    public function test_free_on_request_and_no_price_carry_no_amount(): void
    {
        foreach (['free', 'on_request', 'none'] as $type) {
            $this->assertContains($type, ServicePost::PRICE_TYPES_WITHOUT_AMOUNT);
        }
        $this->assertNotContains('salary', ServicePost::PRICE_TYPES_WITHOUT_AMOUNT);
    }
}
