<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\GoogleAuthController;
use PHPUnit\Framework\TestCase;

class GoogleVerifiedEmailTest extends TestCase
{
    public function test_uses_the_email_from_the_signed_token_not_the_client_claim(): void
    {
        $email = GoogleAuthController::verifiedEmail(
            ['email' => 'Attacker@Example.com', 'email_verified' => true],
            'victim@example.com'
        );

        $this->assertSame('attacker@example.com', $email);
    }

    public function test_falls_back_to_the_client_email_only_when_the_token_has_none(): void
    {
        $this->assertSame('a@example.com', GoogleAuthController::verifiedEmail(['sub' => '1'], ' A@Example.com '));
    }

    public function test_rejects_an_unverified_google_email(): void
    {
        $this->assertNull(GoogleAuthController::verifiedEmail(['email' => 'a@example.com', 'email_verified' => false], 'a@example.com'));
        $this->assertNull(GoogleAuthController::verifiedEmail(['email' => 'a@example.com', 'email_verified' => 'false'], 'a@example.com'));
    }

    public function test_rejects_when_no_email_at_all(): void
    {
        $this->assertNull(GoogleAuthController::verifiedEmail([], ''));
    }
}
