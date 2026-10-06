<?php

namespace Tests\Unit;

use App\Services\ChatSafety;
use PHPUnit\Framework\TestCase;

class ChatSafetyTest extends TestCase
{
    public function test_common_scams_are_flagged_in_english_and_arabic(): void
    {
        $this->assertSame('code', ChatSafety::warningFor('Please send me the code you just got'));
        $this->assertSame('code', ChatSafety::warningFor('ابعتلي الكود اللي وصلك'));
        $this->assertSame('advance', ChatSafety::warningFor('You need to pay first, then I ship'));
        $this->assertSame('advance', ChatSafety::warningFor('لازم عربون قبل ما نلتقي'));
        $this->assertSame('transfer', ChatSafety::warningFor('I can pay with gift cards'));
        $this->assertSame('link', ChatSafety::warningFor('pay here https://fast-delivery-pay.xyz/checkout'));
    }

    public function test_normal_messages_are_not_flagged(): void
    {
        foreach (['Is it still available?', 'هل ما زال متاحاً؟', 'Can we meet tomorrow at 5?', 'The price is 300'] as $m) {
            $this->assertNull(ChatSafety::warningFor($m), $m);
        }
    }
}
